<?php
/**
 * 标签入口 /tag/{slug}/
 */
require_once __DIR__ . '/app/bootstrap.php';

controller('TagController');

$slug = $_GET['slug'] ?? '';
if (!$slug) {
    header('Location: /tags/');
    exit;
}

$pdo = get_pdo();
$controller = new TagController($pdo);
$controller->detail($slug);
