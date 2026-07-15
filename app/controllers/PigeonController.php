<?php
require_once __DIR__ . '/../models/Pigeon.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../models/Shop.php';
require_once __DIR__ . '/../core/RingNormalizer.php';

use App\Core\RingNormalizer;

/**
 * 铭鸽控制器
 */
class PigeonController extends Controller {
    
    /**
     * 铭鸽列表
     */
    public function list() {
        $pigeonModel = new Pigeon($this->pdo);
        $categoryModel = new Category($this->pdo);
        
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $categoryId = null;
        $categorySlug = isset($_GET['category']) ? $_GET['category'] : null;
        $limit = 12;
        $offset = ($page - 1) * $limit;
        
        // 搜索关键词
        $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
        
        // 排序方式
        $sort = isset($_GET['sort']) ? $_GET['sort'] : '';
        if (!in_array($sort, ['views', 'newest'])) {
            $sort = '';
        }
        
        // 性别筛选
        $gender = isset($_GET['gender']) ? $_GET['gender'] : '';
        $genderMap = ['male' => 1, 'female' => 2];
        if (isset($genderMap[$gender])) {
            $gender = $genderMap[$gender];
        } elseif ($gender !== '' && $gender !== '0' && $gender !== '1' && $gender !== '2') {
            $gender = '';
        } else {
            $gender = $gender === '' ? '' : (int)$gender;
        }
        
        // 支持分类slug或数字ID
        if ($categorySlug) {
            if (is_numeric($categorySlug)) {
                $categoryId = (int)$categorySlug;
            } else {
                $cat = $categoryModel->findBySlug($categorySlug, 2);
                $categoryId = $cat ? $cat['id'] : null;
            }
        }
        
        $options = [
            'limit' => $limit,
            'offset' => $offset
        ];
        
        if ($categoryId) {
            $options['category_id'] = $categoryId;
        }
        
        if ($keyword) {
            $options['keyword'] = $keyword;
        }
        
        if ($sort) {
            $options['sort'] = $sort;
        }
        
        if ($gender !== '') {
            $options['gender'] = $gender;
        }
        
        // 血统筛选
        $bloodline = isset($_GET['bloodline']) ? trim($_GET['bloodline']) : '';
        if ($bloodline !== '') {
            $options['bloodline'] = $bloodline;
        }
        
        $pigeons = $pigeonModel->getList($options);
        $total = $pigeonModel->getCount($options);
        $totalPages = ceil($total / $limit);
        
        $categories = $categoryModel->getList(['type' => 2]);
        
        // 获取热门血统（用于筛选建议）
        $hotBloodlines = $this->getHotBloodlines();
        
        // 热门铭鸽
        $hotPigeons = $pigeonModel->getHot(8);

        // 热门展厅（内链）
        $shopModel = new Shop($this->pdo);
        $hotShops = $shopModel->getHot(8);

        $data = [
            'pigeons' => $pigeons,
            'categories' => $categories,
            'currentCategory' => $categoryId,
            'currentCategorySlug' => $categorySlug,
            'keyword' => $keyword,
            'sort' => $sort,
            'gender' => $gender,
            'currentBloodline' => $bloodline,
            'hotBloodlines' => $hotBloodlines,
            'hotPigeons' => $hotPigeons,
            'hotShops' => $hotShops,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'pageTitle' => '铭鸽展厅 | ' . SITE_NAME
        ];
        
        $this->loadView('pigeons', $data);
    }
    
    /**
     * 铭鸽详情
     */
    public function detail($id) {
        $pigeonModel = new Pigeon($this->pdo);
        $categoryModel = new Category($this->pdo);
        
        $pigeon = $pigeonModel->findById($id);
        
        if (!$pigeon) {
            http_response_code(404);
            $this->loadView('404', ['pageTitle' => '页面未找到']);
            return;
        }
        
        // 增加浏览次数
        $pigeonModel->incrementViews($id);
        
        // 获取相关铭鸽（同展厅优先，其次同分类）
        $relatedPigeons = [];
        if (!empty($pigeon['shop_id'])) {
            $relatedPigeons = $pigeonModel->getList([
                'shop_id' => $pigeon['shop_id'],
                'limit' => 4
            ]);
        }
        if (count($relatedPigeons) < 4 && $pigeon['category_id']) {
            $more = $pigeonModel->getList([
                'category_id' => $pigeon['category_id'],
                'limit' => 4 - count($relatedPigeons)
            ]);
            $relatedPigeons = array_merge($relatedPigeons, $more);
        }
        
        $categories = $categoryModel->getList(['type' => 2]);
        
        // 品系数据（用于血统链接和品系页跳转）
        $strain = null;
        if (!empty($pigeon['strain_id'])) {
            $stmt = $this->pdo->prepare("SELECT id, name, slug FROM pigeon_strains WHERE id = ?");
            $stmt->execute([$pigeon['strain_id']]);
            $strain = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        // 同品系铭鸽
        $sameStrainPigeons = [];
        if (!empty($pigeon['strain_id'])) {
            $sameStrainPigeons = $pigeonModel->getByStrainId($pigeon['strain_id'], $id, 4);
        }
        
        // 展厅信息（用于展厅名片）
        $shop = null;
        if (!empty($pigeon['shop_id'])) {
            $stmt = $this->pdo->prepare("SELECT id, name, address, contact_phone, description, avatar FROM shops WHERE id = ? AND status = 1");
            $stmt->execute([$pigeon['shop_id']]);
            $shop = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        // 构建卖家信息
        $seller = [
            'nickname' => $pigeon['nickname'] ?? $pigeon['username'] ?? '',
            'username' => $pigeon['username'] ?? '',
            'avatar'   => $pigeon['avatar'] ?? '/public/assets/images/default-avatar.png',
            'phone'    => $pigeon['phone'] ?? '',
            'member_level' => $pigeon['member_level'] ?? 0,
            'rating'   => 98,
        ];
        
        // 联系卖家权限
        $isLoggedIn = isset($_SESSION['user_id']);
        $canViewContact = false;
        if ($isLoggedIn) {
            require_once __DIR__ . '/../core/MembershipGuard.php';
            $canViewContact = MembershipGuard::check($this->pdo, $_SESSION['user_id'], 'canViewContact');
        }
        
        // 数据融合 Phase 3：根据足环号查找参赛成绩
        $raceLinks = [];
        if (!empty($pigeon['ring_number'])) {
            require_once __DIR__ . '/../models/Race.php';
            require_once __DIR__ . '/../core/RingNormalizer.php';
            $raceModel = new Race($this->pdo);
            $normalizedRing = RingNormalizer::normalize($pigeon['ring_number']);
            $raceLinks = $raceModel->getResultsByRing($normalizedRing);
        }
        
        $data = [
            'pigeon' => $pigeon,
            'seller' => $seller,
            'relatedPigeons' => $relatedPigeons,
            'categories' => $categories,
            'raceLinks' => $raceLinks,
            'strain' => $strain,
            'sameStrainPigeons' => $sameStrainPigeons,
            'shop' => $shop,
            'isLoggedIn' => $isLoggedIn,
            'canViewContact' => $canViewContact,
            'pageTitle' => $pigeon['name'] . ' | ' . SITE_NAME
        ];
        
        $this->loadView('pigeon', $data);
    }
    
    /**
     * 发布铭鸽页面
     */
    public function create() {
        // 检查登录
        if (!isset($_SESSION['user_id'])) {
            header('Location: /auth?action=login');
            exit;
        }
        
        // 会员权限检查
        require_once __DIR__ . '/../core/MembershipGuard.php';
        $check = MembershipGuard::check($this->pdo, $_SESSION['user_id'], 'canPublish', 'pigeon');
        
        $categoryModel = new Category($this->pdo);
        $categories = $categoryModel->getList(['type' => 2]);
        
        $data = [
            'categories' => $categories,
            'publish_check' => $check,
            'pageTitle' => '发布铭鸽 | ' . SITE_NAME
        ];
        
        $this->loadView('pigeon_create', $data);
    }
    
    /**
     * 保存铭鸽
     */
    public function store() {
        ob_start();
        try {
            if (!isset($_SESSION['user_id'])) {
                http_response_code(401);
                header("Content-Type: application/json; charset=utf-8");
                echo json_encode(['success' => false, 'message' => '请先登录', 'code' => 'not_logged_in']);
                ob_end_clean();
                exit;
            }

            require_once __DIR__ . '/../core/MembershipGuard.php';
            $check = MembershipGuard::check($this->pdo, $_SESSION['user_id'], 'canPublish', 'pigeon');

            if (!$check['allowed']) {
                http_response_code(403);
                header("Content-Type: application/json; charset=utf-8");
                echo json_encode(['success' => false, 'message' => $check['message'], 'code' => $check['code']]);
                ob_end_clean();
                exit;
            }

            $pigeonModel = new Pigeon($this->pdo);
            $data = [
                'user_id' => $_SESSION['user_id'],
                'category_id' => intval($_POST['category_id']),
                'name' => trim($_POST['name'] ?? ''),
                'ring_number' => trim($_POST['ring_number'] ?? ''),
                'bloodline' => trim($_POST['bloodline'] ?? ''),
                'gender' => intval($_POST['gender'] ?? 0),
                'description' => trim($_POST['description'] ?? ''),
                'images' => $_POST['images'] ?? '',
                'video' => $_POST['video'] ?? '',
                'achievements' => $_POST['achievements'] ?? '',
                'status' => 1
            ];

            $result = $pigeonModel->create($data);

            if ($result) {
                MembershipGuard::check($this->pdo, $_SESSION['user_id'], 'consume', 'pigeon');
                http_response_code(200);
                header("Content-Type: application/json; charset=utf-8");
                echo json_encode(['success' => true, 'message' => '发布成功']);
                ob_end_clean();
                exit;
            } else {
                http_response_code(500);
                header("Content-Type: application/json; charset=utf-8");
                echo json_encode(['success' => false, 'message' => '发布失败']);
                ob_end_clean();
                exit;
            }
        } catch (Throwable $e) {
            ob_end_clean();
            http_response_code(500);
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => '服务器异常: ' . $e->getMessage()]);
            exit;
        }
    }
    
    /**
     * 编辑铭鸽页面
     */
    public function edit($id) {
        // 检查登录
        if (!isset($_SESSION['user_id'])) {
            header('Location: /auth?action=login');
            exit;
        }
        
        $pigeonModel = new Pigeon($this->pdo);
        $pigeon = $pigeonModel->findById($id);
        
        // 检查铭鸽是否存在
        if (!$pigeon) {
            http_response_code(404);
            $this->loadView('404', ['pageTitle' => '铭鸽不存在']);
            return;
        }
        
        // 检查权限：只有所有者可以编辑
        if ($pigeon['user_id'] != $_SESSION['user_id']) {
            http_response_code(403);
            $this->loadView('403', ['pageTitle' => '无权访问']);
            return;
        }
        
        $categoryModel = new Category($this->pdo);
        $categories = $categoryModel->getList(['type' => 2]);
        
        $data = [
            'pigeon' => $pigeon,
            'categories' => $categories,
            'pageTitle' => '编辑铭鸽 | ' . SITE_NAME
        ];
        
        $this->loadView('pigeon_edit', $data);
    }
    
    /**
     * 更新铭鸽
     */
    public function update($id) {
        ob_start();
        try {
            if (!isset($_SESSION['user_id'])) {
                http_response_code(401);
                header("Content-Type: application/json; charset=utf-8");
                echo json_encode(['success' => false, 'message' => '请先登录']);
                ob_end_clean();
                exit;
            }

            $pigeonModel = new Pigeon($this->pdo);
            $pigeon = $pigeonModel->findById($id);

            if (!$pigeon) {
                http_response_code(404);
                header("Content-Type: application/json; charset=utf-8");
                echo json_encode(['success' => false, 'message' => '铭鸽不存在']);
                ob_end_clean();
                exit;
            }

            if ($pigeon['user_id'] != $_SESSION['user_id']) {
                http_response_code(403);
                header("Content-Type: application/json; charset=utf-8");
                echo json_encode(['success' => false, 'message' => '无权操作']);
                ob_end_clean();
                exit;
            }

            $data = [
                'category_id' => intval($_POST['category_id'] ?? $pigeon['category_id']),
                'name' => trim($_POST['name'] ?? $pigeon['name']),
                'ring_number' => trim($_POST['ring_number'] ?? $pigeon['ring_number']),
                'bloodline' => trim($_POST['bloodline'] ?? $pigeon['bloodline']),
                'gender' => intval($_POST['gender'] ?? $pigeon['gender']),
                'description' => trim($_POST['description'] ?? $pigeon['description']),
                'images' => $_POST['images'] ?? $pigeon['images'],
                'video' => $_POST['video'] ?? $pigeon['video'],
                'achievements' => $_POST['achievements'] ?? $pigeon['achievements']
            ];

            $result = $pigeonModel->update($id, $data);

            ob_end_clean();
            http_response_code($result ? 200 : 500);
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => (bool)$result, 'message' => $result ? '更新成功' : '更新失败']);
            exit;
        } catch (Throwable $e) {
            ob_end_clean();
            http_response_code(500);
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => '服务器异常: ' . $e->getMessage()]);
            exit;
        }
    }
    
    /**
     * 获取热门血统标签
     */
    private function getHotBloodlines($limit = 25) {
        $sql = "SELECT bloodline, COUNT(*) as cnt FROM pigeons WHERE status = 1 AND bloodline != '' GROUP BY bloodline ORDER BY cnt DESC LIMIT ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([(int)$limit]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // 过滤纯标点/空格等无效血统值
        return array_values(array_filter($rows, function($r) {
            $bl = trim($r['bloodline']);
            return $bl !== '' && !preg_match('/^[,，.。、\s]+$/u', $bl) && mb_strlen($bl) > 1;
        }));
    }
}
