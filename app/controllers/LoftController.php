<?php
/**
 * 公棚控制器
 */
class LoftController extends Controller {

    /**
     * 公棚列表页
     */
    public function list() {
        $loftModel = new Loft($this->pdo);
        $categoryModel = new Category($this->pdo);

        $options = [
            'province' => $_GET['province'] ?? '',
            'city' => $_GET['city'] ?? '',
            'race_type' => $_GET['race_type'] ?? '',
            'keyword' => $_GET['keyword'] ?? '',
            'is_certified' => isset($_GET['certified']) ? 1 : '',
            'order_by' => $_GET['order'] ?? '',
            'page' => intval($_GET['page'] ?? 1),
            'per_page' => 12,
        ];

        $lofts = $loftModel->getList($options);
        $total = $loftModel->getCount($options);
        $totalPages = ceil($total / $options['per_page']);
        $provinces = $loftModel->getProvinces();
        $hotLofts = $loftModel->getHot(6);
        $categories = $categoryModel->getTree(4); // type=4 公棚分类

        // 侧边栏数据：2026赛季统计
        $seasonStats = $this->pdo->query("SELECT COUNT(DISTINCT loft_id) as active_lofts, COUNT(*) as total_races FROM races WHERE season_year=2026 AND status=1")->fetch(PDO::FETCH_ASSOC);

        // 侧边栏数据：最新冠军（2026赛季）
        $champions = $this->pdo->query("SELECT r.loft_id, l.name as loft_name, rr.speed, r.name as race_name, r.distance_km FROM race_results rr JOIN races r ON rr.race_id = r.id JOIN lofts l ON r.loft_id = l.id WHERE rr.rank = 1 AND r.season_year = 2026 ORDER BY r.release_time DESC LIMIT 6")->fetchAll(PDO::FETCH_ASSOC);

        // 侧边栏数据：公棚相关资讯
        $loftArticles = $this->pdo->query("SELECT id, title, created_at FROM articles WHERE status=1 AND (title LIKE '%公棚%' OR title LIKE '%赛事%') ORDER BY created_at DESC LIMIT 4")->fetchAll(PDO::FETCH_ASSOC);

        $data = [
            'pageTitle' => '公棚 | ' . SITE_NAME,
            'lofts' => $lofts,
            'total' => $total,
            'totalPages' => $totalPages,
            'page' => $options['page'],
            'provinces' => $provinces,
            'hotLofts' => $hotLofts,
            'categories' => $categories,
            'currentProvince' => $options['province'],
            'currentRaceType' => $options['race_type'],
            'currentKeyword' => $options['keyword'],
            'isCertified' => isset($_GET['certified']),
            'orderBy' => $options['order_by'],
            'seasonStats' => $seasonStats,
            'champions' => $champions,
            'loftArticles' => $loftArticles,
        ];

        $this->render('lofts', $data);
    }

    /**
     * 公棚详情页
     */
    public function detail() {
        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) {
            header('Location: /loft/');
            exit;
        }

        $loftModel = new Loft($this->pdo);
        $loft = $loftModel->findById($id);

        if (!$loft || $loft['status'] != 1) {
            header('Location: /loft/');
            exit;
        }

        // 增加浏览量
        $loftModel->incrementViews($id);

        // 获取参赛记录
        $entryPage = intval($_GET['entry_page'] ?? 1);
        $entries = $loftModel->getEntries($id, ['page' => $entryPage]);
        $entryTotal = $loftModel->getEntryCount($id);

        // 获取评价
        $reviews = $loftModel->getReviews($id, 10);

        // 获取相关公棚（同省份）
        $relatedLofts = $loftModel->getList([
            'province' => $loft['province'],
            'per_page' => 4,
        ]);

        // 获取公棚动态（最新10条）— 兼容迁移前状态
        try {
            $loftNews = $loftModel->getNews($id, 10);
        } catch (PDOException $e) {
            $loftNews = [];
        }

        // 获取公棚相册
        try {
            $loftPhotos = $loftModel->getPhotosGrouped($id);
        } catch (PDOException $e) {
            $loftPhotos = [];
        }

        // 获取赛事列表（数据融合 Phase 2）
        $raceModel = new Race($this->pdo);
        $races = $raceModel->getByLoftId($loft['id']);

        // 赛季统计（从 race_results 聚合）
        $seasonStats = null;
        if (!empty($races)) {
            $raceIds = array_column($races, 'id');
            if (!empty($raceIds)) {
                $placeholders = implode(',', array_fill(0, count($raceIds), '?'));
                $stmt = $this->pdo->prepare(
                    "SELECT
                        COUNT(*) as total_results,
                        SUM(CASE WHEN rank = 1 THEN 1 ELSE 0 END) as champion_count,
                        SUM(CASE WHEN rank <= 3 THEN 1 ELSE 0 END) as podium_count,
                        MAX(speed) as best_speed,
                        COUNT(DISTINCT race_id) as races_with_results,
                        COUNT(DISTINCT owner_name) as owner_count
                     FROM race_results
                     WHERE race_id IN ($placeholders)"
                );
                $stmt->execute($raceIds);
                $seasonStats = $stmt->fetch(PDO::FETCH_ASSOC);

                // 赛季年份信息
                $seasonsStmt = $this->pdo->prepare(
                    "SELECT DISTINCT season_year, season_type FROM races WHERE loft_id = ? AND status = 1 ORDER BY season_year DESC LIMIT 3"
                );
                $seasonsStmt->execute([$loft['id']]);
                $seasonStats['seasons'] = $seasonsStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

                // 总参赛羽数
                $entryStmt = $this->pdo->prepare(
                    "SELECT SUM(entry_count) as total_entries FROM races WHERE loft_id = ? AND status = 1"
                );
                $entryStmt->execute([$loft['id']]);
                $entryRow = $entryStmt->fetch(PDO::FETCH_ASSOC);
                $seasonStats['total_entries'] = $entryRow['total_entries'] ?? 0;
            }
        }

        // 生成 AI 风格公棚说明文
        $aiDescription = $this->generateLoftDescription($loft, $races, $seasonStats);

        // P0: 公棚赛事汇总 + 冠军鸽列表
        $raceStats = $raceModel->getLoftRaceStats($loft['id']);
        $loftChampions = $raceModel->getLoftChampions($loft['id'], 8);

        // P2: 公棚深度分析 — 赛季对比 + 鸽主荣誉榜
        $seasonComparison = $raceModel->getLoftSeasonComparison($loft['id']);
        $topOwners = $raceModel->getLoftTopOwners($loft['id'], 8);

        // 检查当前用户是否是公棚所有者
        $isOwner = isset($_SESSION['user_id']) && intval($loft['user_id']) === intval($_SESSION['user_id']);

        $data = [
            'pageTitle' => $loft['name'] . ' | ' . SITE_NAME,
            'loft' => $loft,
            'entries' => $entries,
            'entryTotal' => $entryTotal,
            'reviews' => $reviews,
            'relatedLofts' => $relatedLofts,
            'loftNews' => $loftNews,
            'loftPhotos' => $loftPhotos,
            'races' => $races,
            'isOwner' => $isOwner,
            'seasonStats' => $seasonStats,
            'aiDescription' => $aiDescription,
            'raceStats' => $raceStats,
            'loftChampions' => $loftChampions,
            'seasonComparison' => $seasonComparison,
            'topOwners' => $topOwners,
        ];

        $this->render('loft', $data);
    }

    /**
     * 编辑公棚（GET 显示表单）
     */
    public function edit() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) {
            header('Location: /loft/');
            exit;
        }

        $loftModel = new Loft($this->pdo);
        $loft = $loftModel->findById($id);

        if (!$loft) {
            header('Location: /loft/');
            exit;
        }

        // 检查权限：必须是公棚所有者
        if (intval($loft['user_id']) !== intval($_SESSION['user_id'])) {
            die('您没有权限编辑此公棚');
        }

        $categoryModel = new Category($this->pdo);
        $categories = $categoryModel->getTree(4); // type=4 公棚分类
        $provinces = $loftModel->getProvinces();

        $data = [
            'pageTitle' => '编辑公棚 | ' . SITE_NAME,
            'loft' => $loft,
            'categories' => $categories,
            'provinces' => $provinces,
        ];

        $this->render('loft_edit', $data);
    }

    /**
     * 更新公棚（POST）
     */
    public function update() {
        if (!isset($_SESSION['user_id'])) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => '请先登录']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => '无效请求']);
            exit;
        }

        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => '参数错误']);
            exit;
        }

        $loftModel = new Loft($this->pdo);
        $loft = $loftModel->findById($id);

        if (!$loft) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => '公棚不存在']);
            exit;
        }

        // 检查权限
        if (intval($loft['user_id']) !== intval($_SESSION['user_id'])) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => '您没有权限编辑此公棚']);
            exit;
        }

        // 允许编辑的字段
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'province' => trim($_POST['province'] ?? ''),
            'city' => trim($_POST['city'] ?? ''),
            'address' => trim($_POST['address'] ?? ''),
            'contact_phone' => trim($_POST['phone'] ?? ''),
            'wechat' => trim($_POST['wechat'] ?? ''),
            'description' => trim($_POST['intro'] ?? ''),
            'race_type' => trim($_POST['race_type'] ?? ''),
        ];

        // 基本校验
        if (empty($data['name'])) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => '公棚名称不能为空']);
            exit;
        }

        $result = $loftModel->update($id, $data);

        header("Content-Type: application/json; charset=utf-8");
        echo json_encode([
            'success' => $result,
            'message' => $result ? '保存成功' : '保存失败'
        ]);
        exit;
    }

    /**
     * 生成公棚 AI 风格说明文
     */
    private function generateLoftDescription($loft, $races, $seasonStats)
    {
        $name = $loft['name'] ?? '该公棚';
        $province = $loft['province'] ?? '';
        $city = $loft['city'] ?? '';
        $location = $province . ($city ? $city : '');
        $year = $loft['established'] ?? '';
        $raceType = $loft['race_type'] ?? '';
        $fee = $loft['fee'] ?? 0;
        $prizePool = $loft['prize_pool'] ?? 0;
        $distance = $loft['distance'] ?? '';

        $raceCount = count($races);
        $championCount = $seasonStats['champion_count'] ?? 0;
        $totalResults = $seasonStats['total_results'] ?? 0;
        $ownerCount = $seasonStats['owner_count'] ?? 0;
        $totalEntries = $seasonStats['total_entries'] ?? 0;
        $bestSpeed = $seasonStats['best_speed'] ?? 0;

        $parts = [];

        // 简介开头
        $parts[] = sprintf('%s%s是%s知名的信鸽竞赛公棚',
            $location ? '坐落于' . $location . '的' : '',
            $name,
            $province ? $province . '地区' : '国内'
        );

        // 建棚
        if ($year) {
            $parts[] = sprintf('始建于%s年', $year);
        }

        // 赛事类型
        if ($raceType) {
            $parts[] = sprintf('主营%s赛事', $raceType);
        }

        // 参赛费 + 奖金
        if ($fee > 0 && $prizePool > 0) {
            $parts[] = sprintf('每羽参赛费¥%s，赛季总奖金池达¥%s万',
                number_format($fee),
                number_format($prizePool / 10000, 1)
            );
        } elseif ($prizePool > 0) {
            $parts[] = sprintf('赛季总奖金池达¥%s万', number_format($prizePool / 10000, 1));
        } elseif ($fee > 0) {
            $parts[] = sprintf('每羽参赛费¥%s', number_format($fee));
        }

        // 决赛空距
        if ($distance) {
            $parts[] = sprintf('决赛空距%s公里', $distance);
        }

        // 赛事规模
        if ($raceCount > 0) {
            $parts[] = sprintf('本站已记录%d场正式赛事', $raceCount);
        }
        if ($totalEntries > 0) {
            $parts[] = sprintf('累计参赛约%s羽', number_format($totalEntries));
        }

        // 竞技成绩
        if ($totalResults > 0) {
            $parts[] = sprintf('产生%s条成绩记录', number_format($totalResults));
        }
        if ($championCount > 0) {
            $parts[] = sprintf('诞生%d位冠军', $championCount);
        }
        if ($ownerCount > 0) {
            $parts[] = sprintf('吸引了%s位鸽主在此角逐', number_format($ownerCount));
        }
        if ($bestSpeed > 0) {
            $parts[] = sprintf('历史最高分速%s米/分', number_format($bestSpeed, 0));
        }

        // 收尾
        $parts[] = sprintf('%s是广大鸽友交流竞技的优质平台。', $name);

        return implode('，', $parts) . '。';
    }

    /**
     * 公棚对比工具
     */
    public function compare() {
        $loftModel = new Loft($this->pdo);
        $ids = [];
        
        // 解析 ids 参数
        if (!empty($_GET['ids'])) {
            $ids = array_filter(array_map('intval', explode(',', $_GET['ids'])));
            $ids = array_slice($ids, 0, 3); // 最多3个
        }
        
        // 搜索结果（仅搜索时显示）
        $searchResults = [];
        if (!empty($_GET['q'])) {
            $searchResults = $loftModel->search(trim($_GET['q']), 20);
        }
        
        // 热门公棚（始终展示在页面底部）
        $hotLofts = $loftModel->getHot(5);
        
        // 对比数据
        $lofts = [];
        $unlocked = true; // 默认免费（≤2家）
        if (!empty($ids)) {
            $lofts = $loftModel->getCompareData($ids);
            // 超过2家且用户已登录 → 检查付费
            if (count($ids) > 2 && !empty($_SESSION['user_id'])) {
                sort($ids);
                $loftIdsKey = implode(',', $ids);
                require_once __DIR__ . '/../models/Payment.php';
                $pmt = new Payment($this->pdo);
                $unlocked = $pmt->isProductUnlocked((int)$_SESSION['user_id'], 'compare', $loftIdsKey);
            }
        }
        
        $data = [
            'pageTitle' => '公棚对比工具 - 信鸽之家',
            'lofts' => $lofts,
            'ids' => $ids,
            'searchResults' => $searchResults,
            'hotLofts' => $hotLofts,
            'query' => $_GET['q'] ?? '',
            'unlocked' => $unlocked,
        ];
        
        $this->loadView('loft_compare', $data);
    }

    /**
     * 省份公棚目录页 /loft/province/{province}/
     */
    public function provinceIndex()
    {
        $province = trim($_GET['province'] ?? '');
        if (!$province) {
            $this->redirect('/loft/');
            return;
        }

        $loftModel = new Loft($this->pdo);

        $page = max(1, intval($_GET['page'] ?? 1));
        $perPage = 12;

        // 省份统计数据
        $stats = $loftModel->getProvinceStats($province);

        // 该省城市列表
        $cities = $loftModel->getCitiesByProvince($province);

        // 该省公棚列表（分页）
        $lofts = $loftModel->getList([
            'province' => $province,
            'page' => $page,
            'per_page' => $perPage,
        ]);
        $totalLofts = $loftModel->getCount(['province' => $province]);
        $totalPages = ceil($totalLofts / $perPage);

        // 全省份列表（导航用）
        $allProvinces = $loftModel->getProvinces();

        $data = [
            'province' => $province,
            'stats' => $stats,
            'cities' => $cities,
            'lofts' => $lofts,
            'totalLofts' => $totalLofts,
            'totalPages' => $totalPages,
            'page' => $page,
            'allProvinces' => $allProvinces,
        ];

        $this->loadView('loft_province', $data);
    }

    /**
     * 城市公棚目录页 /loft/city/{city}/
     */
    public function cityIndex()
    {
        $city = trim($_GET['city'] ?? '');
        if (!$city) {
            $this->redirect('/loft/');
            return;
        }

        $loftModel = new Loft($this->pdo);

        $page = max(1, intval($_GET['page'] ?? 1));
        $perPage = 12;

        $stats = $loftModel->getCityStats($city);

        $lofts = $loftModel->getList([
            'city' => $city,
            'page' => $page,
            'per_page' => $perPage,
        ]);
        $totalLofts = $loftModel->getCount(['city' => $city]);
        $totalPages = ceil($totalLofts / $perPage);

        $data = [
            'city' => $city,
            'stats' => $stats,
            'lofts' => $lofts,
            'totalLofts' => $totalLofts,
            'totalPages' => $totalPages,
            'page' => $page,
        ];

        $this->loadView('loft_city', $data);
    }
}
