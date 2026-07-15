<?php
/**
 * 信鸽之家 - 会员升级
 */
require_once __DIR__ . '/app/bootstrap.php';

// 检查登录
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

$pdo = get_pdo();

// 获取 plan_type 参数（1=月付，2=年付）
$planType = isset($_GET['plan_type']) ? (int)$_GET['plan_type'] : 0;

if (!in_array($planType, [1, 2])) {
    header('Location: /user/membership');
    exit;
}

$planNames  = [1 => '月度会员',    2 => '年度会员'];
$planLabels = [1 => '月付',       2 => '年付'];
$amounts    = [1 => 29,           2 => 299];

$planName  = $planNames[$planType];
$planLabel = $planLabels[$planType];
$amount    = $amounts[$planType];

// 审核中状态
$pending = (isset($_GET['status']) && $_GET['status'] === 'pending');
$orderNo = isset($_GET['order_no']) ? trim($_GET['order_no']) : '';

// 获取用户信息（不指定字段，避免 level 列不存在的问题）
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header('Location: /login.php');
    exit;
}

// 传递数据到视图
$data = [
    'user'      => $user,
    'planType'  => $planType,
    'planName'  => $planName,
    'planLabel' => $planLabel,
    'amount'    => $amount,
    'pending'   => $pending,
    'pageTitle' => '开通' . $planName . ' - 信鸽之家',
];

extract($data);
require __DIR__ . '/views/upgrade.php';
