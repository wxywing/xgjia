<?php
/**
 * 信鸽之家 - 认证入口
 * 合并 login.php + register.php + logout.php
 * 
 * URL: /login → action=login
 *      /register → action=register  
 *      /logout → action=logout
 */

require_once __DIR__ . '/app/bootstrap.php';

$controller = controller('AuthController');
$action = $_GET['action'] ?? 'login';

switch ($action) {
    case 'register':
        $_SERVER['REQUEST_METHOD'] === 'POST'
            ? $controller->register()
            : $controller->registerForm();
        break;
    case 'logout':
        $controller->logout();
        break;
    case 'login':
    default:
        $_SERVER['REQUEST_METHOD'] === 'POST'
            ? $controller->login()
            : $controller->loginForm();
        break;
}
