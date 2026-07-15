<?php
/**
 * 标签列表入口 /tags/
 */
require_once __DIR__ . '/app/bootstrap.php';
controller('TagController');

$pdo = get_pdo();
$controller = new TagController($pdo);
$controller->index();
