<?php
/**
 * 信鸽之家 - 退出登录
 */

require_once dirname(__DIR__) . '/app/config/config.php';

// 销毁session
session_start();
session_destroy();

// 跳转到首页
header('Location: /');
exit;
