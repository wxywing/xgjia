<?php
/**
 * 信鸽之家 - 铭鸽展厅入口
 * 合并 shops.php + shop.php
 * 
 * SEO URL: /shop/         → 展厅列表
 *           /shop/123.html → 展厅详情
 */

require_once __DIR__ . "/app/bootstrap.php";

$controller = controller("ShopController");

$action = $_GET['action'] ?? '';
$id = intval($_GET['id'] ?? 0);

if ($action === 'edit' && $id) {
    $controller->edit();
} elseif ($action === 'update') {
    $controller->update();
} elseif ($id) {
    $controller->detail();
} else {
    $controller->list();
}
