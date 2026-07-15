<?php
/**
 * 信鸽之家 - 鸽友圈
 */

require_once __DIR__ . '/app/bootstrap.php';

$controller = controller('DynamicController');
$action = $_GET['action'] ?? 'list';

switch ($action) {
    case 'create':
        $controller->create();
        break;
    case 'edit':
        $controller->edit();
        break;
    case 'delete':
        $controller->delete();
        break;
    case 'my':
        $controller->my();
        break;
    default:
        $controller->list();
}
