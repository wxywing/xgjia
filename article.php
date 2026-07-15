<?php
/**
 * 信鸽之家 - 文章入口
 * 合并 articles.php + article.php + article_create.php
 * 
 * SEO URL: /article/           → 全部文章
 *           /article/saishi/   → 分类文章
 *           /article/saishi/123.html → 详情
 *           /article/create    → 发布文章
 */

require_once __DIR__ . '/app/bootstrap.php';

$controller = controller('ArticleController');
$action = $_GET['action'] ?? '';

// 发布页
if ($action === 'create') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->store();
    } else {
        $controller->create();
    }
    exit;
}

// 详情页：有id参数
$id = intval($_GET['id'] ?? 0);
if ($id) {
    $controller->detail($id);
    exit;
}

// 列表页（支持category筛选）
$controller->list();
