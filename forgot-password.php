<?php
/**
 * 信鸽之家 - 密码重置入口
 * 
 * URL: /forgot-password → 显示忘记密码表单
 *      /reset-password?token=xxx → 显示重置密码表单
 */

require_once __DIR__ . '/app/bootstrap.php';

$controller = controller('PasswordController');

$action = strpos($_SERVER['REQUEST_URI'], 'reset-password') !== false ? 'reset' : 'forgot';

if ($action === 'reset') {
    $_SERVER['REQUEST_METHOD'] === 'POST'
        ? $controller->reset()
        : $controller->reset();
} else {
    $_SERVER['REQUEST_METHOD'] === 'POST'
        ? $controller->forgot()
        : $controller->forgot();
}
