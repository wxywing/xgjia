<?php
/**
 * 信鸽之家 - 用户中心
 */

require_once __DIR__ . '/app/bootstrap.php';

$controller = controller('UserController');

// 根据action参数决定调用哪个方法
$action = $_GET['action'] ?? 'index';

switch ($action) {
    case 'my_articles':
        $controller->myArticles();
        break;
    case 'my_pigeons':
        $controller->myPigeons();
        break;
    case 'my_listings':
        $controller->myListings();
        break;
    case 'membership':
        $controller->membership();
        break;
    case 'edit_profile':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->editProfile();
        } else {
            $controller->editProfilePage();
        }
        break;
    case 'change_password':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->changePassword();
        } else {
            $controller->changePasswordPage();
        }
        break;
    case 'upgrade':
        $controller->upgrade();
        break;
    default:
        $controller->index();
}
