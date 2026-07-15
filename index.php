<?php
/**
 * 信鸽之家 - 首页入口
 */

require_once __DIR__ . '/app/bootstrap.php';

// 使用 HomeController
$controller = controller('HomeController');
$controller->index();
