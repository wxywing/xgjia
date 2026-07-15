<?php
ob_start();  // 开启输出缓冲，防止任何杂质输出
/**
 * 信鸽之家 - 编辑铭鸽入口
 * 
 * URL: /pigeon_edit.php?id=123
 */

require_once __DIR__ . '/app/bootstrap.php';

// 检查登录
if (!isset($_SESSION['user_id'])) {
    header('Location: /auth?action=login');
    exit;
}

$controller = controller('PigeonController');
$id = intval($_GET['id'] ?? 0);

if (!$id) {
    http_response_code(404);
    echo '铭鸽不存在';
    exit;
}

// POST 请求：更新
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->update($id);
} else {
    // GET 请求：显示编辑页面
    $controller->edit($id);
}
