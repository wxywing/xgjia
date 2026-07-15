<?php
/**
 * 商家认领控制器
 */

require_once __DIR__ . '/../models/ClaimRequest.php';
require_once __DIR__ . '/../models/Shop.php';
require_once __DIR__ . '/../models/Loft.php';

class ClaimController extends Controller {

    /**
     * 提交认领申请（AJAX POST）
     */
    public function submit() {
        if (!isset($_SESSION['user_id'])) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'code' => 'not_logged_in', 'message' => '请先登录']);
            exit;
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => '无效请求']);
            exit;
            return;
        }

        $targetType = $_POST['target_type'] ?? '';
        $targetId = intval($_POST['target_id'] ?? 0);

        if (!in_array($targetType, ['shop', 'loft']) || $targetId <= 0) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => '参数错误']);
            exit;
            return;
        }

        // 检查目标是否可认领（user_id = 0 表示待认领）
        if ($targetType === 'shop') {
            $shopModel = new Shop($this->pdo);
            $target = $shopModel->findById($targetId);
            $targetName = $target['name'] ?? '';
        } else {
            $loftModel = new Loft($this->pdo);
            $target = $loftModel->findById($targetId);
            $targetName = $target['name'] ?? '';
        }

        if (!$target) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => '目标不存在']);
            exit;
            return;
        }

        if (intval($target['user_id']) !== 0) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'code' => 'already_claimed', 'message' => '该' . ($targetType === 'shop' ? '展厅' : '公棚') . '已被认领']);
            exit;
            return;
        }

        // 检查会员等级（至少铜牌会员才能认领）
        $userModel = new User($this->pdo);
        $user = $userModel->findById($_SESSION['user_id']);
        if (intval($user['member_level']) < 1) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'code' => 'membership_required', 'message' => '铜牌及以上会员才能认领', 'redirect' => '/user/membership']);
            exit;
            return;
        }

        $claimModel = new ClaimRequest($this->pdo);

        // 检查是否已有待审核申请
        if ($claimModel->hasPending($_SESSION['user_id'], $targetType, $targetId)) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'code' => 'already_pending', 'message' => '您已提交过认领申请，请等待审核']);
            exit;
            return;
        }

        $data = [
            'user_id' => $_SESSION['user_id'],
            'target_type' => $targetType,
            'target_id' => $targetId,
            'real_name' => trim($_POST['real_name'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'wechat' => trim($_POST['wechat'] ?? ''),
            'evidence' => $_POST['evidence'] ?? '',
            'reason' => trim($_POST['reason'] ?? ''),
        ];

        // 基本校验
        if (empty($data['real_name']) || empty($data['phone']) || empty($data['reason'])) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => '请填写必填项（姓名、电话、申请理由）']);
            exit;
            return;
        }

        if (mb_strlen($data['reason']) < 10) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => '申请理由不少于10字']);
            exit;
            return;
        }

        $result = $claimModel->create($data);

        if ($result) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => true, 'message' => '认领申请已提交，我们会在1-3个工作日内审核']);
            exit;
        } else {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => '提交失败，请稍后重试']);
            exit;
        }
    }

    /**
     * 取消认领申请（AJAX POST）
     */
    public function cancel() {
        if (!isset($_SESSION['user_id'])) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => '请先登录']);
            exit;
            return;
        }

        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => '参数错误']);
            exit;
            return;
        }

        $claimModel = new ClaimRequest($this->pdo);
        $result = $claimModel->cancel($id, $_SESSION['user_id']);

        header("Content-Type: application/json; charset=utf-8");
        echo json_encode(['success' => $result, 'message' => $result ? '已取消申请' : '取消失败']);
        exit;
    }

    /**
     * 我的认领申请列表
     */
    public function myClaims() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $claimModel = new ClaimRequest($this->pdo);

        $page = max(1, intval($_GET['page'] ?? 1));
        $status = isset($_GET['status']) ? intval($_GET['status']) : null;

        $claims = $claimModel->getByUser($_SESSION['user_id'], [
            'status' => $status,
            'page' => $page,
            'per_page' => 10
        ]);

        $total = $claimModel->getCountByUser($_SESSION['user_id'], $status);
        $totalPages = ceil($total / 10);

        $data = [
            'claims' => $claims,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'currentStatus' => $status,
            'pageTitle' => '我的认领 | ' . SITE_NAME
        ];

        $this->loadView('my_claims', $data);
    }

    /**
     * 管理员：认领申请列表
     */
    public function adminList() {
        $this->checkAdmin();

        $claimModel = new ClaimRequest($this->pdo);

        $page = max(1, intval($_GET['page'] ?? 1));
        $status = isset($_GET['status']) ? intval($_GET['status']) : null;
        $targetType = $_GET['target_type'] ?? '';

        $options = [
            'page' => $page,
            'per_page' => 15,
            'status' => $status,
            'target_type' => $targetType,
        ];

        $claims = $claimModel->getList($options);
        $total = $claimModel->getCount($options);
        $totalPages = ceil($total / 15);
        $statusCounts = $claimModel->getStatusCounts();

        $this->render('admin/claims', [
            'claims' => $claims,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'currentStatus' => $status,
            'targetType' => $targetType,
            'statusCounts' => $statusCounts,
            'pageTitle' => '认领管理 - 管理后台'
        ]);
    }

    /**
     * 管理员：审核认领申请（AJAX POST）
     */
    public function adminReview() {
        $this->checkAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => '无效请求']);
            exit;
            return;
        }

        $id = intval($_POST['id'] ?? 0);
        $action = $_POST['action'] ?? '';
        $note = trim($_POST['note'] ?? '');

        if ($id <= 0 || !in_array($action, ['approve', 'reject'])) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => '参数错误']);
            exit;
            return;
        }

        $claimModel = new ClaimRequest($this->pdo);
        $adminId = $_SESSION['user_id'];

        if ($action === 'approve') {
            $result = $claimModel->approve($id, $adminId, $note);
        } else {
            $result = $claimModel->reject($id, $adminId, $note);
        }

        header("Content-Type: application/json; charset=utf-8");
        echo json_encode(['success' => $result, 'message' => $result ? '操作成功' : '操作失败']);
        exit;
    }

    /**
     * 检查管理员权限
     */
    private function checkAdmin() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        $userModel = new User($this->pdo);
        $user = $userModel->findById($_SESSION['user_id']);
        if (!$user || ($user['role'] ?? '') !== 'admin') {
            die('权限不足');
        }
    }
}
