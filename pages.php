<?php
/**
 * 信鸽之家 - 静态页面入口
 */

require_once __DIR__ . '/app/config/config.php';

// 获取页面名称
$page = $_GET['page'] ?? 'about';

// 白名单验证
$allowedPages = ['about', 'contact', 'ad', 'help', 'faq', 'agreement', 'privacy'];

if (!in_array($page, $allowedPages)) {
    http_response_code(404);
    echo '页面不存在';
    exit;
}

// 加载对应视图
$viewFile = __DIR__ . "/views/pages/{$page}.php";

if (file_exists($viewFile)) {
    include $viewFile;
} else {
    http_response_code(404);
    echo '页面不存在';
}
