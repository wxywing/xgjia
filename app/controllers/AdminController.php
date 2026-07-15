<?php
/**
 * 管理员控制器
 */

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Article.php';
require_once __DIR__ . '/../models/Pigeon.php';
require_once __DIR__ . '/../models/Listing.php';
require_once __DIR__ . '/../models/Race.php';
require_once __DIR__ . '/../models/Dynamic.php';
require_once __DIR__ . '/../models/Loft.php';

class AdminController extends Controller {

    /**
     * 管理员后台首页(仪表盘)
     */
    public function index() {
        $this->checkAdmin();

        // 统计数据
        $stats = [
            'total_users' => $this->countTable('users'),
            'total_articles' => $this->countTable('articles'),
            'total_pigeons' => $this->countTable('pigeons'),
            'total_listings' => $this->countTable('listings'),
            'total_races' => $this->countTable('races'),
            'total_dynamics' => $this->countTable('dynamics'),
            'total_lofts' => $this->countTable('lofts'),
            'new_users_today' => $this->countTable('users', 'DATE(created_at) = CURDATE()'),
            'total_comments' => $this->countTable('comments'),
        ];

        // 最近数据
        $recentUsers = $this->queryRows("SELECT id, username, email, member_level, status, created_at FROM users ORDER BY created_at DESC LIMIT 5");
        $recentArticles = $this->queryRows("SELECT a.id, a.title, a.status, a.views, a.created_at, u.username as author_name FROM articles a LEFT JOIN users u ON a.user_id = u.id ORDER BY a.created_at DESC LIMIT 5");

        $this->render('admin/index', [
            'stats' => $stats,
            'recentUsers' => $recentUsers,
            'recentArticles' => $recentArticles,
            'pageTitle' => '管理后台 - ' . SITE_NAME
        ]);
    }

    /**
     * 用户管理
     */
    public function users() {
        $this->checkAdmin();

        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        $keyword = $_GET['keyword'] ?? '';

        $where = '';
        $params = [];
        if ($keyword) {
            $where = "WHERE username LIKE ? OR email LIKE ? OR nickname LIKE ?";
            $params[] = "%$keyword%";
            $params[] = "%$keyword%";
            $params[] = "%$keyword%";
        }

        $total = $this->countTable('users', $where ? preg_replace('/^WHERE /', '', $where) : '', $params);
        $totalPages = ceil($total / $perPage);

        $sql = "SELECT * FROM users $where ORDER BY created_at DESC LIMIT $perPage OFFSET $offset";
        $users = $this->queryRows($sql, $params);

        $this->render('admin/users', [
            'users' => $users,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'keyword' => $keyword,
            'pageTitle' => '用户管理 - 管理后台'
        ]);
    }

    /**
     * 编辑用户(AJAX)
     */
    public function editUser() {
        $this->checkAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = intval($_POST['id'] ?? 0);
            $data = [];

            $allowedFields = ['username', 'email', 'phone', 'nickname', 'member_level', 'status'];
            foreach ($allowedFields as $field) {
                if (isset($_POST[$field])) {
                    $data[$field] = $_POST[$field];
                }
            }

            if ($id > 0 && !empty($data)) {
                $userModel = new User($this->pdo);
                if ($userModel->update($id, $data)) {
                    header("Content-Type: application/json; charset=utf-8");
                    echo json_encode(['success' => true, 'message' => '更新成功']);
                    exit;
                } else {
                    header("Content-Type: application/json; charset=utf-8");
                    echo json_encode(['success' => false, 'message' => '更新失败']);
                    exit;
                }
            } else {
                header("Content-Type: application/json; charset=utf-8");
                echo json_encode(['success' => false, 'message' => '参数错误']);
                exit;
            }
            exit;
        }
    }

    /**
     * 文章管理
     */
    public function articles() {
        $this->checkAdmin();

        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        $status = isset($_GET['status']) ? intval($_GET['status']) : null;

        $where = '';
        $params = [];
        if ($status !== null) {
            $where = "WHERE a.status = ?";
            $params[] = $status;
        }

        $totalSql = "SELECT COUNT(*) FROM articles a $where";
        $stmt = $this->pdo->prepare($totalSql);
        $stmt->execute($params);
        $total = $stmt->fetchColumn();
        $totalPages = ceil($total / $perPage);

        $sql = "SELECT a.id, a.title, a.status, a.views, a.created_at, a.category_id,
                       a.content, u.username as author_name, c.name as category_name
                FROM articles a
                LEFT JOIN users u ON a.user_id = u.id
                LEFT JOIN categories c ON a.category_id = c.id
                $where
                ORDER BY a.created_at DESC
                LIMIT $perPage OFFSET $offset";
        $articles = $this->queryRows($sql, $params);

        $categorySql = "SELECT * FROM categories ORDER BY sort ASC, id ASC";
        $categories = $this->queryRows($categorySql);

        $this->render('admin/articles', [
            'articles' => $articles,
            'categories' => $categories,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'currentStatus' => $status,
            'pageTitle' => '文章管理 - 管理后台'
        ]);
    }

    /**
     * 铭鸽管理
     */
    public function pigeons() {
        $this->checkAdmin();

        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $total = $this->countTable('pigeons');
        $totalPages = ceil($total / $perPage);

        $sql = "SELECT p.*, u.username as owner_name, s.name as shop_name
                FROM pigeons p
                LEFT JOIN users u ON p.user_id = u.id
                LEFT JOIN shops s ON p.shop_id = s.id
                ORDER BY p.created_at DESC
                LIMIT $perPage OFFSET $offset";
        $pigeons = $this->queryRows($sql);

        $this->render('admin/pigeons', [
            'pigeons' => $pigeons,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'pageTitle' => '铭鸽管理 - 管理后台'
        ]);
    }

    /**
     * 分类信息管理
     */
    public function listings() {
        $this->checkAdmin();

        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        $status = isset($_GET['status']) ? intval($_GET['status']) : null;

        $where = '';
        $params = [];
        if ($status !== null) {
            $where = "WHERE l.status = ?";
            $params[] = $status;
        }

        $totalSql = "SELECT COUNT(*) FROM listings l $where";
        $stmt = $this->pdo->prepare($totalSql);
        $stmt->execute($params);
        $total = $stmt->fetchColumn();
        $totalPages = ceil($total / $perPage);

        $sql = "SELECT l.*, u.username as author_name
                FROM listings l
                LEFT JOIN users u ON l.user_id = u.id
                $where
                ORDER BY l.created_at DESC
                LIMIT $perPage OFFSET $offset";
        $listings = $this->queryRows($sql, $params);

        $this->render('admin/listings', [
            'listings' => $listings,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'currentStatus' => $status,
            'pageTitle' => '分类信息管理 - 管理后台'
        ]);
    }

    /**
     * 赛事管理
     */
    public function races() {
        $this->checkAdmin();

        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        $seasonYear = $_GET['season_year'] ?? '';
        $seasonType = $_GET['season_type'] ?? '';
        $raceCategory = $_GET['race_category'] ?? '';
        $keyword = $_GET['keyword'] ?? '';

        $where = 'WHERE 1=1';
        $params = [];
        if ($seasonYear) {
            $where .= ' AND season_year = ?';
            $params[] = $seasonYear;
        }
        if ($seasonType) {
            $where .= ' AND season_type = ?';
            $params[] = $seasonType;
        }
        if ($raceCategory) {
            $where .= ' AND race_category = ?';
            $params[] = $raceCategory;
        }
        if ($keyword) {
            $where .= ' AND (name LIKE ? OR loft_id IN (SELECT id FROM lofts WHERE name LIKE ?))';
            $params[] = "%$keyword%";
            $params[] = "%$keyword%";
        }

        $total = $this->countTable('races', $where, $params);
        $totalPages = ceil($total / $perPage);

        $sql = "SELECT * FROM races $where ORDER BY release_time DESC LIMIT $perPage OFFSET $offset";
        $races = $this->queryRows($sql, $params);

        // 获取年份列表用于筛选下拉
        $years = $this->queryRows("SELECT DISTINCT season_year FROM races WHERE season_year IS NOT NULL ORDER BY season_year DESC");

        $this->render('admin/races', [
            'races' => $races,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'years' => $years,
            'seasonYear' => $seasonYear,
            'seasonType' => $seasonType,
            'raceCategory' => $raceCategory,
            'keyword' => $keyword,
            'pageTitle' => '赛事管理 - 管理后台'
        ]);
    }

    /**
     * 动态管理(鸽友圈)
     */
    public function dynamics() {
        $this->checkAdmin();

        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $total = $this->countTable('dynamics');
        $totalPages = ceil($total / $perPage);

        $sql = "SELECT d.*, u.username, u.avatar
                FROM dynamics d
                LEFT JOIN users u ON d.user_id = u.id
                ORDER BY d.created_at DESC
                LIMIT $perPage OFFSET $offset";
        $dynamics = $this->queryRows($sql);

        $this->render('admin/dynamics', [
            'dynamics' => $dynamics,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'pageTitle' => '动态管理 - 管理后台'
        ]);
    }

    /**
     * 编辑动态（AJAX）
     */
    public function editDynamic() {
        $this->checkAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = intval($_POST['id'] ?? 0);
            $data = [];

            $allowedFields = ['content', 'status'];
            foreach ($allowedFields as $field) {
                if (isset($_POST[$field])) {
                    $data[$field] = $_POST[$field];
                }
            }

            if ($id > 0 && !empty($data)) {
                $fields = [];
                $values = [];
                foreach ($data as $key => $value) {
                    $fields[] = "$key = ?";
                    $values[] = $value;
                }
                $values[] = $id;
                $sql = "UPDATE dynamics SET " . implode(', ', $fields) . ", created_at = created_at WHERE id = ?";
                $stmt = $this->pdo->prepare($sql);
                $result = $stmt->execute($values);
                header("Content-Type: application/json; charset=utf-8");
                echo json_encode(['success' => $result, 'message' => $result ? '更新成功' : '更新失败']);
                exit;
            } else {
                header("Content-Type: application/json; charset=utf-8");
                echo json_encode(['success' => false, 'message' => '参数错误']);
                exit;
            }
            exit;
        }
    }

    /**
     * 广告管理
     */
    public function ads() {
        $this->checkAdmin();

        $ads = $this->queryRows("SELECT * FROM advertisements ORDER BY sort ASC, created_at DESC");

        $this->render('admin/ads', [
            'ads' => $ads,
            'pageTitle' => '广告管理 - 管理后台'
        ]);
    }

    /**
     * 保存广告(AJAX)
     */
    public function saveAd() {
        $this->checkAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = intval($_POST['id'] ?? 0);
            $data = [
                'title' => $_POST['title'] ?? '',
                'position' => $_POST['position'] ?? 'home_banner',
                'image' => $_POST['image'] ?? '',
                'link' => $_POST['link'] ?? '',
                'sort' => intval($_POST['sort'] ?? 0),
                'status' => intval($_POST['status'] ?? 1),
                'start_at' => ($_POST['start_at'] ?? '') ?: null,
                'end_at' => ($_POST['end_at'] ?? '') ?: null,
            ];

            if ($id > 0) {
                // 更新
                $fields = [];
                $values = [];
                foreach ($data as $key => $value) {
                    $fields[] = "$key = ?";
                    $values[] = $value;
                }
                $values[] = $id;
                $sql = "UPDATE advertisements SET " . implode(', ', $fields) . " WHERE id = ?";
                $stmt = $this->pdo->prepare($sql);
                $result = $stmt->execute($values);
            } else {
                // 新增
                $columns = implode(', ', array_keys($data));
                $placeholders = implode(', ', array_fill(0, count($data), '?'));
                $sql = "INSERT INTO advertisements ($columns, created_at) VALUES ($placeholders, NOW())";
                $stmt = $this->pdo->prepare($sql);
                $result = $stmt->execute(array_values($data));
            }

            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => $result, 'message' => $result ? '保存成功' : '保存失败']);
            exit;
            exit;
        }
    }

    /**
     * 保存赛事(AJAX)
     */
    public function saveRace() {
        $this->checkAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = intval($_POST['id'] ?? 0);
            $data = [
                'title' => $_POST['title'] ?? '',
                'race_type' => $_POST['race_type'] ?? '市级',
                'race_date' => $_POST['race_date'] ?? date('Y-m-d'),
                'end_date' => ($_POST['end_date'] ?? '') ?: null,
                'distance' => ($_POST['distance'] ?? '') ?: null,
                'prize_pool' => ($_POST['prize_pool'] ?? '') ?: null,
                'location' => $_POST['location'] ?? '',
                'organizer' => $_POST['organizer'] ?? '',
                'registration_deadline' => ($_POST['registration_deadline'] ?? '') ?: null,
                'description' => $_POST['description'] ?? '',
                'status' => intval($_POST['status'] ?? 1),
            ];

            if ($id > 0) {
                // 更新
                $fields = [];
                $values = [];
                foreach ($data as $key => $value) {
                    $fields[] = "$key = ?";
                    $values[] = $value;
                }
                $values[] = $id;
                $sql = "UPDATE races SET " . implode(', ', $fields) . " WHERE id = ?";
                $stmt = $this->pdo->prepare($sql);
                $result = $stmt->execute($values);
            } else {
                // 新增
                $columns = implode(', ', array_keys($data));
                $placeholders = implode(', ', array_fill(0, count($data), '?'));
                $sql = "INSERT INTO races ($columns, created_at) VALUES ($placeholders, NOW())";
                $stmt = $this->pdo->prepare($sql);
                $result = $stmt->execute(array_values($data));
            }

            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => $result, 'message' => $result ? '保存成功' : '保存失败']);
            exit;
            exit;
        }
    }

    /**
     * 编辑文章(AJAX)
     */
    public function editArticle() {
        $this->checkAdmin();

        // GET: 获取单篇文章内容 (AJAX)
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $id = intval($_GET['id'] ?? 0);
            if ($id > 0) {
                $rows = $this->queryRows(
                    "SELECT id, title, content, category_id, status FROM articles WHERE id = ?",
                    [$id]
                );
                $article = $rows ? $rows[0] : null;
                header("Content-Type: application/json; charset=utf-8");
                echo json_encode($article ?: null, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
                exit;
            }
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(null);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = intval($_POST['id'] ?? 0);
            $data = [];

            $allowedFields = ['title', 'category_id', 'status', 'content'];
            foreach ($allowedFields as $field) {
                if (isset($_POST[$field])) {
                    $data[$field] = $_POST[$field];
                }
            }

            if ($id > 0 && !empty($data)) {
                $fields = [];
                $values = [];
                foreach ($data as $key => $value) {
                    $fields[] = "$key = ?";
                    $values[] = $value;
                }
                $values[] = $id;
                $sql = "UPDATE articles SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ?";
                $stmt = $this->pdo->prepare($sql);
                $result = $stmt->execute($values);
                header("Content-Type: application/json; charset=utf-8");
                echo json_encode(['success' => $result, 'message' => $result ? '更新成功' : '更新失败']);
                exit;
            } else {
                header("Content-Type: application/json; charset=utf-8");
                echo json_encode(['success' => false, 'message' => '参数错误']);
                exit;
            }
            exit;
        }
    }

    /**
     * 编辑铭鸽(AJAX)
     */
    public function editPigeon() {
        $this->checkAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = intval($_POST['id'] ?? 0);
            $data = [];

            $allowedFields = ['name', 'ring_number', 'bloodline', 'gender', 'color', 'eye_color', 'status', 'description', 'is_top'];
            foreach ($allowedFields as $field) {
                if (isset($_POST[$field])) {
                    $data[$field] = $_POST[$field];
                }
            }

            if ($id > 0 && !empty($data)) {
                $fields = [];
                $values = [];
                foreach ($data as $key => $value) {
                    $fields[] = "$key = ?";
                    $values[] = $value;
                }
                $values[] = $id;
                $sql = "UPDATE pigeons SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ?";
                $stmt = $this->pdo->prepare($sql);
                $result = $stmt->execute($values);
                header("Content-Type: application/json; charset=utf-8");
                echo json_encode(['success' => $result, 'message' => $result ? '更新成功' : '更新失败']);
                exit;
            } else {
                header("Content-Type: application/json; charset=utf-8");
                echo json_encode(['success' => false, 'message' => '参数错误']);
                exit;
            }
            exit;
        }
    }

    /**
     * 编辑分类信息(AJAX)
     */
    public function editListing() {
        $this->checkAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = intval($_POST['id'] ?? 0);
            $data = [];

            $allowedFields = ['title', 'type', 'status', 'price', 'location', 'description'];
            foreach ($allowedFields as $field) {
                if (isset($_POST[$field])) {
                    $data[$field] = $_POST[$field];
                }
            }

            if ($id > 0 && !empty($data)) {
                $fields = [];
                $values = [];
                foreach ($data as $key => $value) {
                    $fields[] = "$key = ?";
                    $values[] = $value;
                }
                $values[] = $id;
                $sql = "UPDATE listings SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ?";
                $stmt = $this->pdo->prepare($sql);
                $result = $stmt->execute($values);
                header("Content-Type: application/json; charset=utf-8");
                echo json_encode(['success' => $result, 'message' => $result ? '更新成功' : '更新失败']);
                exit;
            } else {
                header("Content-Type: application/json; charset=utf-8");
                echo json_encode(['success' => false, 'message' => '参数错误']);
                exit;
            }
            exit;
        }
    }

    /**
     * 系统设置
     */
    public function settings() {
        $this->checkAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $settings = $_POST['settings'] ?? [];
            foreach ($settings as $key => $value) {
                $stmt = $this->pdo->prepare("INSERT INTO settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = ?");
                $stmt->execute([$key, $value, $value]);
            }
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => '设置已保存']);
            exit;
            exit;
        }

        $settingsRows = $this->queryRows("SELECT * FROM settings ORDER BY id ASC");
        $settings = [];
        foreach ($settingsRows as $row) {
            $settings[$row['key']] = $row['value'];
        }

        $this->render('admin/settings', [
            'settings' => $settings,
            'pageTitle' => '系统设置 - 管理后台'
        ]);
    }

    /**
     * 通用删除(AJAX)
     */
    public function delete() {
        $this->checkAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $type = $_POST['type'] ?? '';
            $id = intval($_POST['id'] ?? 0);

            $tableMap = [
                'article' => 'articles',
                'pigeon' => 'pigeons',
                'listing' => 'listings',
                'race' => 'races',
                'dynamic' => 'dynamics',
                'comment' => 'comments',
                'ad' => 'advertisements',
                'user' => 'users',
                'loft' => 'lofts'
            ];

            if (!isset($tableMap[$type]) || $id <= 0) {
                header("Content-Type: application/json; charset=utf-8");
                echo json_encode(['success' => false, 'message' => '参数错误']);
                exit;
                exit;
            }

            $table = $tableMap[$type];
            $stmt = $this->pdo->prepare("DELETE FROM $table WHERE id = ?");
            $result = $stmt->execute([$id]);

            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => $result, 'message' => $result ? '删除成功' : '删除失败']);
            exit;
            exit;
        }
    }

    /**
     * 切换状态(AJAX)
     */
    public function toggleStatus() {
        $this->checkAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $type = $_POST['type'] ?? '';
            $id = intval($_POST['id'] ?? 0);
            $status = intval($_POST['status'] ?? 0);

            $tableMap = [
                'article' => 'articles',
                'listing' => 'listings',
                'ad' => 'advertisements',
                'user' => 'users',
                'dynamic' => 'dynamics',
                'loft' => 'lofts',
                'pigeon' => 'pigeons'
            ];

            if (!isset($tableMap[$type]) || $id <= 0) {
                header("Content-Type: application/json; charset=utf-8");
                echo json_encode(['success' => false, 'message' => '参数错误']);
                exit;
                exit;
            }

            $table = $tableMap[$type];
            $stmt = $this->pdo->prepare("UPDATE $table SET status = ? WHERE id = ?");
            $result = $stmt->execute([$status, $id]);

            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => $result, 'message' => $result ? '操作成功' : '操作失败']);
            exit;
            exit;
        }
    }

    /**
     * 检查管理员权限
     */
    private function checkAdmin() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login?redirect=' . urlencode($_SERVER['REQUEST_URI']));
            exit;
        }

        $user = $this->queryRow("SELECT role FROM users WHERE id = ?", [$_SESSION['user_id']]);

        if (!$user || ($user['role'] ?? '') !== 'admin') {
            die('权限不足:仅管理员可访问后台');
        }
    }

    /**
     * 统计表记录数
     */
    private function countTable($table, $where = '', $params = []) {
        $sql = "SELECT COUNT(*) FROM $table";
        if ($where) {
            // 如果 $where 已包含 WHERE 关键字，不再重复添加
            if (stripos(trim($where), 'WHERE') === 0) {
                $sql .= " $where";
            } else {
                $sql .= " WHERE $where";
            }
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return intval($stmt->fetchColumn());
    }

    /**
     * 查询多行
     */
    private function queryRows($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 查询单行
     */
    private function queryRow($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ========== 公棚管理 ==========

    /**
     * 公棚列表
     */
    public function lofts() {
        $this->checkAdmin();

        $page = max(1, intval($_GET['page'] ?? 1));
        $perPage = 15;
        $offset = ($page - 1) * $perPage;

        $status = $_GET['status'] ?? null;
        $where = '';
        $params = [];
        if ($status !== null) {
            $where = ' WHERE status = ?';
            $params[] = intval($status);
        }

        $total = $this->countTable('lofts', $where ? ltrim($where, ' WHERE ') : '', $params);
        $totalPages = ceil($total / $perPage);

        $sql = "SELECT * FROM lofts {$where} ORDER BY is_hot DESC, is_certified DESC, created_at DESC LIMIT {$perPage} OFFSET {$offset}";
        $lofts = $this->queryRows($sql, $params);

        $this->render('admin/lofts', [
            'pageTitle' => '公棚管理',
            'lofts' => $lofts,
            'total' => $total,
            'page' => $page,
            'totalPages' => $totalPages,
        ]);
    }

    /**
     * 保存公棚(新增/编辑)
     */
    public function saveLoft() {
        $this->checkAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = intval($_POST['id'] ?? 0);
            $data = [
                'name' => $_POST['name'] ?? '',
                'province' => $_POST['province'] ?? '',
                'city' => $_POST['city'] ?? '',
                'address' => $_POST['address'] ?? '',
                'contact_name' => $_POST['contact_name'] ?? '',
                'contact_phone' => $_POST['contact_phone'] ?? '',
                'race_type' => $_POST['race_type'] ?? '秋棚',
                'race_distance' => ($_POST['race_distance'] ?? '') ?: null,
                'capacity' => ($_POST['capacity'] ?? '') ?: null,
                'entry_fee' => ($_POST['entry_fee'] ?? '') ?: null,
                'management_fee' => ($_POST['management_fee'] ?? '') ?: null,
                'prize_pool' => ($_POST['prize_pool'] ?? '') ?: null,
                'collect_start' => ($_POST['collect_start'] ?? '') ?: null,
                'collect_end' => ($_POST['collect_end'] ?? '') ?: null,
                'training_start' => ($_POST['training_start'] ?? '') ?: null,
                'race_date' => ($_POST['race_date'] ?? '') ?: null,
                'description' => $_POST['description'] ?? '',
                'rules' => $_POST['rules'] ?? '',
                'status' => intval($_POST['status'] ?? 0),
                'is_certified' => intval($_POST['is_certified'] ?? 0),
                'is_hot' => intval($_POST['is_hot'] ?? 0),
            ];

            if ($id > 0) {
                // 更新
                $fields = [];
                $values = [];
                foreach ($data as $key => $value) {
                    $fields[] = "$key = ?";
                    $values[] = $value;
                }
                $values[] = $id;
                $sql = "UPDATE lofts SET " . implode(', ', $fields) . " WHERE id = ?";
                $stmt = $this->pdo->prepare($sql);
                $result = $stmt->execute($values);
            } else {
                // 新增
                $data['user_id'] = $_SESSION['user_id'] ?? 1;
                $columns = implode(', ', array_keys($data));
                $placeholders = implode(', ', array_fill(0, count($data), '?'));
                $sql = "INSERT INTO lofts ($columns, created_at) VALUES ($placeholders, NOW())";
                $stmt = $this->pdo->prepare($sql);
                $result = $stmt->execute(array_values($data));
            }

            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => $result, 'message' => $result ? '保存成功' : '保存失败']);
            exit;
            exit;
        }
    }

    /**
     * 订单管理列表
     */
    public function orders() {
        $this->checkAdmin();

        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        $status = $_GET['status'] ?? '';
        $keyword = $_GET['keyword'] ?? '';
        $productType = $_GET['product_type'] ?? '';

        $where = 'WHERE 1=1';
        $params = [];
        if ($status !== '') {
            $where .= ' AND o.status = ?';
            $params[] = intval($status);
        }
        if ($productType === 'membership') {
            $where .= ' AND o.product_type = ?';
            $params[] = 'membership';
        } elseif ($productType === 'product') {
            $where .= ' AND o.product_type != ?';
            $params[] = 'membership';
        }

        if ($keyword) {
            $where .= ' AND (o.order_no LIKE ? OR u.username LIKE ? OR u.email LIKE ?)';
            $params[] = "%$keyword%";
            $params[] = "%$keyword%";
            $params[] = "%$keyword%";
        }

        $countSql = "SELECT COUNT(*) FROM member_orders o LEFT JOIN users u ON o.user_id = u.id $where";
        $stmt = $this->pdo->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetchColumn();
        // 统计
        $statsSql = "SELECT
            COUNT(*) as total_cnt,
            SUM(CASE WHEN product_type='membership' THEN 1 ELSE 0 END) as membership_cnt,
            SUM(CASE WHEN product_type!='membership' THEN 1 ELSE 0 END) as product_cnt,
            SUM(CASE WHEN product_type='membership' THEN amount ELSE 0 END) as membership_amount,
            SUM(CASE WHEN product_type!='membership' THEN amount ELSE 0 END) as product_amount,
            SUM(CASE WHEN status=0 THEN 1 ELSE 0 END) as pending_cnt,
            SUM(CASE WHEN status=0 THEN amount ELSE 0 END) as pending_amount,
            SUM(CASE WHEN status=1 THEN amount ELSE 0 END) as paid_amount,
            SUM(CASE WHEN status=1 THEN 1 ELSE 0 END) as paid_cnt,
            SUM(CASE WHEN status=3 THEN amount ELSE 0 END) as approved_amount
            FROM member_orders";
        $statsStmt = $this->pdo->query($statsSql);
        $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

        $totalPages = ceil($total / $perPage);

        $sql = "SELECT o.*, u.username, u.email FROM member_orders o LEFT JOIN users u ON o.user_id = u.id $where ORDER BY o.created_at DESC LIMIT $perPage OFFSET $offset";
        $orders = $this->queryRows($sql, $params);

        $this->render('admin/orders', [
            'orderStats' => $stats,
            'orders' => $orders,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'status' => $status,
            'keyword' => $keyword,
            'productType' => $_GET['product_type'] ?? 'all',
            'pageTitle' => '订单管理 - 管理后台'
        ]);
    }

    /**
     * 更新订单状态(AJAX)
     */
    public function updateOrder() {
        $this->checkAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = intval($_POST['id'] ?? 0);
            $status = intval($_POST['status'] ?? -1);

            if ($id <= 0 || !in_array($status, [0, 1, 2, 3])) {
                header("Content-Type: application/json; charset=utf-8");
                echo json_encode(['success' => false, 'message' => '参数错误']);
                exit;
                exit;
            }

            $data = ['status' => $status];
            if ($status == 1) {
                $data['paid_at'] = date('Y-m-d H:i:s');
            }

            $sets = [];
            $params = [];
            foreach ($data as $k => $v) {
                $sets[] = "$k = ?";
                $params[] = $v;
            }
            $params[] = $id;

            $sql = "UPDATE member_orders SET " . implode(', ', $sets) . " WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute($params);

            // 如果标记为已支付，同步更新用户会员等级
            if ($result && $status == 1) {
                $orderStmt = $this->pdo->prepare("SELECT user_id, to_level, plan_type, months FROM member_orders WHERE id = ?");
                $orderStmt->execute([$id]);
                $order = $orderStmt->fetch();
                if ($order) {
                    // 兼容新旧订单：有months就用months，否则按plan_type推算
                    $months = intval($order['months']);
                    if ($months <= 0) {
                        $months = intval($order['plan_type']) === 2 ? 12 : 1;
                    }
                    $level = intval($order['to_level']);
                    if ($level <= 0) {
                        $level = 1; // 新订单统一为VIP
                    }
                    $expireAt = date('Y-m-d H:i:s', strtotime("+{$months} months"));
                    $this->pdo->prepare("UPDATE users SET member_level = ?, member_expire_at = ? WHERE id = ?")
                        ->execute([$level, $expireAt, $order['user_id']]);
                }
            }

            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => $result, 'message' => $result ? '更新成功' : '更新失败']);
            exit;
            exit;
        }
    }


    /**
     * 订单详情(AJAX)
     */
    public function orderDetail() {
        $this->checkAdmin();
        header("Content-Type: application/json; charset=utf-8");

        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => '参数错误']);
            exit;
        }

        $sql = "SELECT o.*, u.username, u.email, u.member_level as user_level, u.member_expire_at
                FROM member_orders o
                LEFT JOIN users u ON o.user_id = u.id
                WHERE o.id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            echo json_encode(['success' => false, 'message' => '订单不存在']);
            exit;
        }

        echo json_encode(['success' => true, 'order' => $order]);
        exit;
    }

}
