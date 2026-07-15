<?php
/**
 * 支付 Controller（统一使用 member_orders 表）
 */
require_once __DIR__ . '/../models/Payment.php';

// 支付接口预留，加载但不实例化
if (file_exists(__DIR__ . '/../services/WechatPay.php')) {
    require_once __DIR__ . '/../services/WechatPay.php';
}
if (file_exists(__DIR__ . '/../services/AlipayService.php')) {
    require_once __DIR__ . '/../services/AlipayService.php';
}

class PaymentController extends Controller {
    private $model;

    public function __construct($pdo) {
        parent::__construct($pdo);
        $this->model = new Payment($pdo);
    }

    // ========== 创建支付订单 ==========
    public function create() {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => '请先登录']);
            exit;
            return;
        }

        $productType = $_POST['product_type'] ?? 'membership';

        // ---- 分支：深度报告支付 ----
        if ($productType === 'report') {
            $this->createReportOrder($userId);
            return;
        }

        // ---- 分支：血统证书支付 ----
        if ($productType === 'certificate') {
            $this->createCertificateOrder($userId);
            return;
        }

        // ---- 分支：公棚对比支付 ----
        if ($productType === 'compare') {
            $this->createCompareOrder($userId);
            return;
        }

        // ---- 原有：会员支付 ----
        $planType = (int)($_POST['plan_type'] ?? 1);  // 1=月费 2=年费
        $payMethod = $_POST['pay_method'] ?? 'wechat';

        if (!in_array($planType, [1, 2])) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => '无效的套餐类型']);
            exit;
        }

        // 固定价格：月付 ¥29，年付 ¥299
        $amounts = [1 => 29, 2 => 299];
        $amount = $amounts[$planType];
        $planNames = [1 => '月度会员', 2 => '年度会员'];
        $planName = $planNames[$planType];

        // 创建订单（level=1 统一为付费会员）
        $order = $this->model->createOrder($userId, 1, $planType, $amount, $payMethod);

        // 模拟支付成功：订单已创建，待后台审核
        header("Content-Type: application/json; charset=utf-8");
        echo json_encode([
            'success' => true,
            'message' => $planName . '订单已创建，请完成支付后联系客服开通',
            'order_no' => $order['order_no'],
            'amount' => $amount,
            'pending' => true
        ]);
        exit;
    }

    // ========== 深度报告支付订单 ==========
    private function createReportOrder($userId) {
        $ring = trim($_POST['ring'] ?? '');
        $payMethod = $_POST['pay_method'] ?? 'wechat';

        if (empty($ring)) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => '足环号不能为空']);
            exit;
            return;
        }

        // 检查是否已解锁
        if ($this->model->isReportUnlocked($userId, $ring)) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => true, 'message' => '已解锁，无需重复支付', 'unlocked' => true]);
            exit;
            return;
        }

        $amount = 9.9;

        // 创建订单（product_type=report, product_ref=足环号）
        $order = $this->model->createOrder($userId, 0, 1, $amount, $payMethod, 'report', $ring);

        // Mock 支付成功（待审核模式）
        header("Content-Type: application/json; charset=utf-8");
        echo json_encode([
            'success'   => true,
            'message'   => '订单已提交，等待管理员审核',
            'order_no'  => $order['order_no'],
            'ring'      => $ring,
            'pending'   => true,
            'auto_unlock' => false,
        ]);
        exit;
    }

    // ========== 血统证书支付订单 ==========
    private function createCertificateOrder($userId) {
        $certId = (int)($_POST['cert_id'] ?? 0);
        $payMethod = $_POST['pay_method'] ?? 'wechat';

        if ($certId <= 0) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => '证书信息不完整']);
            exit;
            return;
        }

        if ($this->model->isProductUnlocked($userId, 'certificate', (string)$certId)) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => true, 'message' => '已解锁，无需重复支付', 'unlocked' => true]);
            exit;
            return;
        }

        $amount = 9.9;
        $order = $this->model->createOrder($userId, 0, 1, $amount, $payMethod, 'certificate', (string)$certId);

        header("Content-Type: application/json; charset=utf-8");
        echo json_encode([
            'success'   => true,
            'message'   => '血统证书订单已创建，模拟支付成功',
            'order_no'  => $order['order_no'],
            'cert_id'   => $certId,
            'pending'   => true,
            'auto_unlock' => false,
        ]);
        exit;
    }

    // ========== 公棚对比支付订单 ==========
    private function createCompareOrder($userId) {
        $loftIds = trim($_POST['loft_ids'] ?? '');
        $payMethod = $_POST['pay_method'] ?? 'wechat';

        if (empty($loftIds)) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => '请先选择要对比的公棚']);
            exit;
            return;
        }

        if ($this->model->isProductUnlocked($userId, 'compare', $loftIds)) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => true, 'message' => '已解锁，无需重复支付', 'unlocked' => true]);
            exit;
            return;
        }

        $amount = 19.9;
        $order = $this->model->createOrder($userId, 0, 1, $amount, $payMethod, 'compare', $loftIds);

        header("Content-Type: application/json; charset=utf-8");
        echo json_encode([
            'success'   => true,
            'message'   => '公棚对比订单已创建，模拟支付成功',
            'order_no'  => $order['order_no'],
            'loft_ids'  => $loftIds,
            'pending'   => true,
            'auto_unlock' => false,
        ]);
        exit;
    }

    // ========== 模拟支付确认（测试用） ==========
    public function mockConfirm() {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => '请先登录']);
            exit;
        }
        
        $orderNo = trim($_POST['order_no'] ?? '');
        if (empty($orderNo)) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => '订单号不能为空']);
            exit;
        }
        
        // 查找订单
        $stmt = $this->pdo->prepare("SELECT * FROM member_orders WHERE order_no = ? AND user_id = ?");
        $stmt->execute([$orderNo, $userId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$order) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => '订单不存在']);
            exit;
        }
        
        if ($order['status'] != 0) {
            $statusMap = [0 => '待支付', 1 => '已支付', 2 => '已取消', 3 => '审核中', 4 => '已完成'];
            $currentStatus = $statusMap[$order['status']] ?? '未知';
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => '订单状态异常：' . $currentStatus]);
            exit;
        }
        
        // 更新订单状态为已支付（待审核）
        $stmt = $this->pdo->prepare("UPDATE member_orders SET status = 1, paid_at = NOW() WHERE id = ?");
        $stmt->execute([$order['id']]);
        
        header("Content-Type: application/json; charset=utf-8");
        echo json_encode([
            'success' => true,
            'message' => '支付成功！您的订单已提交，等待管理员审核开通。'
        ]);
        exit;
    }

    // ========== 微信支付回调（预留） ==========
    public function wechatNotify() {
        $body = file_get_contents('php://input');
        // TODO: 接入真实微信支付后实现验签和处理
        header("Content-Type: application/json; charset=utf-8");
        echo json_encode(['code' => 'FAIL', 'message' => 'NOT_CONFIGURED']);
        exit;
    }

    // ========== 支付宝回调（预留） ==========
    public function alipayNotify() {
        // TODO: 接入真实支付宝后实现验签和处理
        echo 'fail';
    }

    // ========== 支付结果页面 ==========
    public function result() {
        $userId = $_SESSION['user_id'] ?? null;
        $orderNo = $_GET['order_no'] ?? '';

        $order = $this->model->getByOrderNo($orderNo);
        $this->loadView('pay_result', [
            'pageTitle' => '支付结果 | ' . SITE_NAME,
            'order'     => $order,
        ]);
    }

    // ========== 我的订单 ==========
    public function myOrders() {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            header('Location: /auth?action=login');
            return;
        }

        $page = max(1, (int)($_GET['page'] ?? 1));
        $orders = $this->model->getUserOrders($userId, 20, ($page - 1) * 20);
        $stats = $this->model->getOrderStats($userId);

        $this->loadView('my_orders', [
            'pageTitle' => '我的订单 | ' . SITE_NAME,
            'orders'    => $orders,
            'stats'     => $stats,
            'page'      => $page,
        ]);
    }

    // ========== 支付宝同步跳转（预留） ==========
    public function alipayReturn() {
        $orderNo = $_GET['out_trade_no'] ?? '';
        header('Location: /pay/?action=result&order_no=' . urlencode($orderNo));
        exit;
    }

    // ========== 重试支付 /pay/?action=retry ==========
    public function retry() {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => '请先登录']);
            exit;
            return;
        }

        $orderNo = trim($_POST['order_no'] ?? '');
        $payMethod = $_POST['pay_method'] ?? 'wechat';

        if (!$orderNo) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => '订单号无效']);
            exit;
            return;
        }

        // 验证订单属于当前用户且处于待支付状态
        $stmt = $this->pdo->prepare("SELECT * FROM member_orders WHERE order_no = ? AND user_id = ?");
        $stmt->execute([$orderNo, $userId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => '订单不存在']);
            exit;
            return;
        }

        if ($order['status'] == 1) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => '该订单已支付']);
            exit;
            return;
        }

        if ($order['status'] != 0) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => '该订单无法支付']);
            exit;
            return;
        }

        // Mock 模式：直接标记为已支付 + 激活
        $this->model->markPaid($orderNo, strtoupper($payMethod) . '_' . time());
        
        // 根据产品类型激活
        if (($order['product_type'] ?? 'membership') === 'membership') {
            $this->model->activateMembership($orderNo);
        } else {
            $this->model->activateProduct($orderNo);
        }

        header("Content-Type: application/json; charset=utf-8");
        echo json_encode([
            'success' => true,
            'message' => '支付成功！',
        ]);
        exit;
    }

    /**
     * 完成支付（管理员已审批通过后，用户支付）
     * POST /pay/?action=complete
     */
    public function complete() {
        if (!$this->requireLogin()) exit;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => '仅支持 POST']);
            exit;
        }

        $orderNo = trim($_POST['order_no'] ?? '');
        $payMethod = trim($_POST['pay_method'] ?? 'wechat');
        if (!$orderNo) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => '订单号不能为空']);
            exit;
        }

        // 验证订单属于当前用户且状态为已审批(3)
        $order = $this->model->getByOrderNo($orderNo);
        if (!$order || (int)$order['user_id'] !== (int)$_SESSION['user_id']) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => '订单不存在']);
            exit;
        }
        if ((int)$order['status'] !== 3) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => '该订单尚未通过审核，或已完成支付']);
            exit;
        }

        // Mock 模式：标记已支付 + 激活产品
        $this->model->markPaid($orderNo, strtoupper($payMethod) . '_' . time());
        $this->model->activateProduct($orderNo);

        header("Content-Type: application/json; charset=utf-8");
        echo json_encode([
            'success' => true,
            'message' => '购买成功！' . (PAY_MODE === 'sandbox' ? ' [沙箱模式]' : ''),
            'unlocked' => true
        ]);
        exit;
    }

}