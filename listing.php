<?php
/**
 * 信鸽之家 - 分类信息入口
 * 合并 listings.php + listing.php + listing_create.php
 * 
 * SEO URL: /listing/             → 全部分类信息
 *           /listing/chushou/    → 分类
 *           /listing/chushou/123.html → 详情
 *           /listing/create      → 发布
 */

require_once __DIR__ . '/app/bootstrap.php';

$controller = controller('ListingController');
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'create':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->store();
        } else {
            $controller->create();
        }
        break;
    default:
        $id = intval($_GET['id'] ?? 0);
        if ($id) {
            $controller->detail($id);
        } else {
            $controller->list();
        }
        break;
}
