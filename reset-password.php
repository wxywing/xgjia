<?php
/**
 * 信鸽之家 - 重置密码入口
 */

require_once __DIR__ . '/app/bootstrap.php';

$controller = controller('PasswordController');
$controller->reset();
