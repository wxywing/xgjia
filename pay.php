<?php
/**
 * 支付入口
 */
require_once __DIR__ . '/app/bootstrap.php';

$action = $_GET['action'] ?? '';
$pdo = get_pdo();

require_once __DIR__ . '/app/controllers/PaymentController.php';
$controller = new PaymentController($pdo);

switch ($action) {
    case 'create':
        $controller->create();
        break;
    case 'notify_wechat':
        $controller->wechatNotify();
        break;
    case 'notify_alipay':
        $controller->alipayNotify();
        break;
    case 'result':
        $controller->result();
        break;
    case 'retry':
        $controller->retry();
        break;
    case 'complete':
        $controller->complete();
        break;
    case 'orders':
        $controller->myOrders();
        break;
    case 'alipay_return':
        $controller->alipayReturn();
        break;
    default:
        header('Location: /user/membership');
        break;
}