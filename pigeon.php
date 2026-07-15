<?php
/**
 * 信鸽之家 - 铭鸽入口
 * 合并 pigeons.php + pigeon.php + pigeon_create.php
 * 
 * SEO URL: /pigeon/           → 全部铭鸽
 *           /pigeon/saige/   → 分类铭鸽
 *           /pigeon/saige/123.html → 详情
 *           /pigeon/create    → 发布铭鸽
 */

require_once __DIR__ . '/app/bootstrap.php';

$controller = controller('PigeonController');
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

// 编辑页
$id = intval($_GET['id'] ?? 0);
if ($action === 'edit' && $id) {
    $controller->edit($id);
    exit;
}

// 更新（POST）
if ($action === 'update') {
    $controller->update($id);
    exit;
}

// 详情页：有id参数
if ($id) {
    $controller->detail($id);
    exit;
}

$controller->list();
