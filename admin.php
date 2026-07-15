<?php
/**
 * 管理员后台入口文件
 */

require_once __DIR__ . '/app/bootstrap.php';

$controller = controller('AdminController');

$action = $_GET['action'] ?? 'index';

$routes = [
    'index' => 'index',
    'users' => 'users',
    'edit-user' => 'editUser',
    'articles' => 'articles',
    'edit-article' => 'editArticle',
    'pigeons' => 'pigeons',
    'edit-pigeon' => 'editPigeon',
    'listings' => 'listings',
    'edit-listing' => 'editListing',
    'lofts' => 'lofts',
    'save-loft' => 'saveLoft',
    'races' => 'races',
    'save-race' => 'saveRace',
    'dynamics' => 'dynamics',
    'edit-dynamic' => 'editDynamic',
    'orders' => 'orders',
    'update-order' => 'updateOrder',
    'order-detail' => 'orderDetail',
    'orderDetail' => 'orderDetail',
    'claims' => 'claims',
    'ads' => 'ads',
    'save-ad' => 'saveAd',
    'settings' => 'settings',
    'delete' => 'delete',
    'toggle-status' => 'toggleStatus'
];

try {
    if ($action === 'claims') {
        // 认领管理走 ClaimController
        require_once __DIR__ . '/app/controllers/ClaimController.php';
        $pdo = get_pdo();
        $claimController = new ClaimController($pdo);
        $claimController->adminList();
    } elseif (isset($routes[$action])) {
        $method = $routes[$action];
        $controller->$method();
    } else {
        $controller->index();
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    die('系统错误，请稍后重试');
}
