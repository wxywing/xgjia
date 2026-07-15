<?php
/**
 * 商家认领入口
 */

require_once __DIR__ . '/app/bootstrap.php';
require_once __DIR__ . '/app/controllers/ClaimController.php';

$pdo = get_pdo();
$claimController = new ClaimController($pdo);

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'submit':
        $claimController->submit();
        break;
    case 'cancel':
        $claimController->cancel();
        break;
    case 'my_claims':
        $claimController->myClaims();
        break;
    case 'admin_list':
        $claimController->adminList();
        break;
    case 'admin_review':
        $claimController->adminReview();
        break;
    default:
        // 默认跳转到我的认领
        if (isset($_SESSION['user_id'])) {
            header('Location: /claim?action=my_claims');
        } else {
            header('Location: /login');
        }
        exit;
}
