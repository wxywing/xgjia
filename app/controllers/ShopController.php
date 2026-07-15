<?php
require_once __DIR__ . "/../models/Shop.php";
require_once __DIR__ . "/../models/Pigeon.php";

/**
 * 展厅控制器
 */
class ShopController extends Controller {

    /**
     * 展厅列表页
     */
    public function list() {
        $shopModel = new Shop($this->pdo);

        $options = [
            "province" => $_GET["province"] ?? "",
            "keyword" => $_GET["keyword"] ?? "",
            "is_certified" => isset($_GET["certified"]) ? 1 : "",
            "order_by" => $_GET["order"] ?? "",
            "page" => intval($_GET["page"] ?? 1),
            "per_page" => 12,
        ];

        $shops = $shopModel->getList($options);
        $total = $shopModel->getCount($options);
        $totalPages = ceil($total / $options["per_page"]);
        $provinces = $shopModel->getProvinces();
        $hotShops = $shopModel->getHot(6);

        // 热门铭鸽（内链）
        $pigeonModel = new Pigeon($this->pdo);
        $hotPigeons = $pigeonModel->getHot(8);

        $data = [
            "pageTitle" => "铭鸽展厅 | " . SITE_NAME,
            "shops" => $shops,
            "total" => $total,
            "totalPages" => $totalPages,
            "page" => $options["page"],
            "provinces" => $provinces,
            "hotShops" => $hotShops,
            "hotPigeons" => $hotPigeons,
            "currentProvince" => $options["province"],
            "currentKeyword" => $options["keyword"],
            "isCertified" => isset($_GET["certified"]),
            "orderBy" => $options["order_by"],
        ];

        $this->render("shops", $data);
    }

    /**
     * 展厅详情页
     */
    public function detail() {
        $id = intval($_GET["id"] ?? 0);
        if ($id <= 0) {
            header("Location: /shop/");
            exit;
        }

        $shopModel = new Shop($this->pdo);
        $shop = $shopModel->findById($id);

        if (!$shop || $shop["status"] != 1) {
            header("Location: /shop/");
            exit;
        }

        // 增加浏览量
        $shopModel->incrementViews($id);

        // 获取血系分类
        $categories = $shopModel->getCategories($id);

        // 获取展品（支持筛选）
        $filterOptions = [
            "category" => $_GET["category"] ?? "",
            "bloodline" => $_GET["bloodline"] ?? "",
            "gender" => $_GET["gender"] ?? "",
            "page" => intval($_GET["pigeon_page"] ?? 1),
            "per_page" => 24,
        ];
        $pigeons = $shopModel->getPigeons($id, $filterOptions);
        $pigeonTotal = $shopModel->getPigeonCount($id, $filterOptions);
        $pigeonPages = ceil($pigeonTotal / $filterOptions["per_page"]);

        // 获取同省展厅
        $relatedShops = $shopModel->getList([
            "province" => $shop["province"],
            "per_page" => 4,
        ]);

        // 检查当前用户是否是展厅所有者
        $isOwner = isset($_SESSION['user_id']) && intval($shop['user_id']) === intval($_SESSION['user_id']);

        $data = [
            "pageTitle" => $shop["name"] . " - 铭鸽展厅 | " . SITE_NAME,
            "shop" => $shop,
            "categories" => $categories,
            "pigeons" => $pigeons,
            "pigeonTotal" => $pigeonTotal,
            "pigeonPages" => $pigeonPages,
            "pigeonPage" => $filterOptions["page"],
            "filterCategory" => $filterOptions["category"],
            "filterBloodline" => $filterOptions["bloodline"],
            "filterGender" => $filterOptions["gender"],
            "relatedShops" => $relatedShops,
            "isOwner" => $isOwner,
        ];

        $this->render("shop", $data);
    }

    /**
     * 编辑展厅（GET 显示表单）
     */
    public function edit() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) {
            header('Location: /shop/');
            exit;
        }

        $shopModel = new Shop($this->pdo);
        $shop = $shopModel->findById($id);

        if (!$shop) {
            header('Location: /shop/');
            exit;
        }

        // 检查权限：必须是展厅所有者
        if (intval($shop['user_id']) !== intval($_SESSION['user_id'])) {
            die('您没有权限编辑此展厅');
        }

        $provinces = $shopModel->getProvinces();

        $data = [
            'pageTitle' => '编辑展厅 | ' . SITE_NAME,
            'shop' => $shop,
            'provinces' => $provinces,
        ];

        $this->render('shop_edit', $data);
    }

    /**
     * 更新展厅（POST）
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

        $shopModel = new Shop($this->pdo);
        $shop = $shopModel->findById($id);

        if (!$shop) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => '展厅不存在']);
            exit;
        }

        // 检查权限
        if (intval($shop['user_id']) !== intval($_SESSION['user_id'])) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => '您没有权限编辑此展厅']);
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
        ];

        // 基本校验
        if (empty($data['name'])) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => '展厅名称不能为空']);
            exit;
        }

        $result = $shopModel->update($id, $data);

        header("Content-Type: application/json; charset=utf-8");
        echo json_encode([
            'success' => $result,
            'message' => $result ? '保存成功' : '保存失败'
        ]);
        exit;
    }
}
