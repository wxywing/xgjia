<?php
/**
 * 信鸽之家 - 数据工具入口
 * 
 * SEO URL: /tools/ring-guide/  → 足环速查表
 *           /tools/top100/      → 春赛TOP100
 */

require_once __DIR__ . '/app/bootstrap.php';

$controller = controller('ToolsController');
$action = $_GET['action'] ?? '';

// 根据 action 分发
if ($action === 'ring-guide') {
    $controller->ringGuide();
} elseif ($action === 'top100' && isset($_GET['warm'])) {
    // cron 预热：/tools.php?action=top100&warm=1
    $controller->top100Warm();
} elseif ($action === 'top100') {
    $controller->top100();
} else {
    // 默认跳转到足环速查表
    $controller->ringGuide();
}
