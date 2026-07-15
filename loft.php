<?php
/**
 * 信鸽之家 - 公棚入口
 * 合并 lofts.php + loft.php
 * 
 * SEO URL: /loft/         → 公棚列表
 *           /loft/123.html → 公棚详情
 */

require_once __DIR__ . '/app/bootstrap.php';

$controller = controller('LoftController');

$action = $_GET['action'] ?? '';
$id = intval($_GET['id'] ?? 0);

if ($action === 'edit' && $id) {
    $controller->edit();
} elseif ($action === 'update') {
    $controller->update();
} elseif ($action === 'province') {
    $controller->provinceIndex();
} elseif ($action === 'city') {
    $controller->cityIndex();
} elseif ($id) {
    $controller->detail();
} elseif ($action === 'compare') {
    $controller->compare();
} else {
    $controller->list();
}
