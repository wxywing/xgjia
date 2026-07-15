<?php
require_once dirname(__DIR__) . '/app/config/config.php';
$page_title = $pageTitle ?? '支付结果 | ' . SITE_NAME;
$noindex = true;
$paid = $order && $order['status'] == 1;
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo h($page_title); ?></title>
    <meta name="description" content="信鸽之家支付结果页面，查看您的订单支付状态。">
    <meta name="keywords" content="支付结果,信鸽之家">
    <meta name="robots" content="noindex, nofollow">
    <meta property="og:title" content="支付结果 - 信鸽之家">
    <meta property="og:description" content="信鸽之家支付结果页面">
    <meta property="og:image" content="https://www.xgjia.com/public/images/og-cover.png">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="信鸽之家">
    <link rel="canonical" href="https://www.xgjia.com/pay-result">
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/public/css/b-scheme.css">
    <link rel="shortcut icon" href="/public/images/favicon.ico">
    <style>
            :root {
                --primary: #1a5fa8;
                --primary-light: #2980b9;
                --primary-dark: #154360;
                --accent: #c9a84c;
                --accent-light: #e0c060;
                --bg: #f4f6f9;
                --white: #ffffff;
                --text: #2c3e50;
                --text-light: #6c7a89;
                --border: #e8ecf0;
                --shadow: 0 2px 12px rgba(26,95,168,0.08);
                --shadow-hover: 0 8px 30px rgba(26,95,168,0.15);
                --gold: #d4a843;
                --success: #27ae60;
                --danger: #e74c3c;
                --radius: 12px;
            }

        .result-container { max-width: 500px; margin: 60px auto; padding: 0 20px; text-align: center; }
        .result-icon { font-size: 80px; margin-bottom: 20px; }
        .result-icon.success { color: #10b981; }
        .result-icon.pending { color: #f59e0b; }
        .result-icon.fail { color: #ef4444; }
        .result-title { font-size: 24px; font-weight: bold; margin-bottom: 10px; }
        .result-desc { color: #6b7280; font-size: 14px; margin-bottom: 30px; }
        .result-info { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); text-align: left; margin-bottom: 24px; }
        .result-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f3f4f6; font-size: 14px; }
        .result-row:last-child { border-bottom: none; }
        .result-row .label { color: #6b7280; }
        .btn-group { display: flex; gap: 12px; justify-content: center; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/_head.php'; ?>

    <div class="result-container">
        <?php if (!$order): ?>
        <div class="result-icon fail"><i class="fas fa-times-circle"></i></div>
        <div class="result-title">订单不存在</div>
        <div class="result-desc">请检查订单号是否正确</div>
        <?php elseif ($paid): ?>
        <div class="result-icon success"><i class="fas fa-check-circle"></i></div>
        <div class="result-title">支付成功</div>
        <div class="result-desc">您的会员已升级，感谢支持！</div>
        <?php else: ?>
        <div class="result-icon pending"><i class="fas fa-clock"></i></div>
        <div class="result-title">等待支付</div>
        <div class="result-desc">订单尚未完成支付，请尽快完成</div>
        <?php endif; ?>

        <?php if ($order): ?>
        <div class="result-info">
            <div class="result-row"><span class="label">订单号</span><span><?php echo h($order['order_no']); ?></span></div>
            <div class="result-row"><span class="label">金额</span><span style="color:#d4a843;font-weight:bold;">¥<?php echo number_format($order['amount'], 2); ?></span></div>
            <div class="result-row"><span class="label">状态</span><span><?php echo ['0'=>'待支付','1'=>'已支付','2'=>'已取消','3'=>'已退款'][$order['status']]; ?></span></div>
            <?php if (!empty($order['payment_no'])): ?>
            <div class="result-row"><span class="label">交易号</span><span style="font-size:12px;"><?php echo h($order['payment_no']); ?></span></div>
            <?php endif; ?>
            <?php if (!empty($order['paid_at'])): ?>
            <div class="result-row"><span class="label">支付时间</span><span><?php echo $order['paid_at']; ?></span></div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="btn-group" id="btnGroup">
            <?php if (!$order || $order['status'] != 1): ?>
            <button id="btnConfirmPay" onclick="showPayModal()" class="btn btn-primary" style="padding:12px 24px;display:none;">
                <i class="fas fa-credit-card"></i> 确认支付
            </button>
            <?php endif; ?>
            <a href="/user/membership" class="btn btn-outline" style="padding:12px 24px;">返回会员中心</a>
            <a href="/pay/?action=orders" class="btn btn-outline" style="padding:12px 24px;">我的订单</a>
        </div>
    </div>

    <!-- 支付方式选择弹窗 -->
    <div id="paymentModal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.5);align-items:center;justify-content:center;">
        <div style="background:#fff;border-radius:16px;width:90%;max-width:420px;padding:32px;position:relative;">
            <button onclick="closePayModal()" style="position:absolute;top:16px;right:16px;background:none;border:none;font-size:24px;cursor:pointer;color:#9ca3af;">&times;</button>
            <h3 style="font-size:20px;font-weight:bold;margin-bottom:8px;text-align:center;">选择支付方式</h3>
            <p id="payModalDesc" style="text-align:center;color:#6b7280;font-size:14px;margin-bottom:8px;"></p>
            <p id="payModalAmount" style="text-align:center;font-size:32px;font-weight:bold;color:#d4a843;margin-bottom:20px;"></p>
            <div style="display:flex;gap:16px;margin-bottom:24px;">
                <div id="payAlipay" onclick="selectPayMethod('alipay')" style="flex:1;border:2px solid #e5e7eb;border-radius:12px;padding:20px;text-align:center;cursor:pointer;transition:all .3s;">
                    <div style="font-size:40px;margin-bottom:8px;">💙</div>
                    <div style="font-weight:600;font-size:15px;">支付宝</div>
                    <div style="font-size:12px;color:#9ca3af;margin-top:4px;">Alipay</div>
                </div>
                <div id="payWechat" onclick="selectPayMethod('wechat')" style="flex:1;border:2px solid #e5e7eb;border-radius:12px;padding:20px;text-align:center;cursor:pointer;transition:all .3s;">
                    <div style="font-size:40px;margin-bottom:8px;">🟢</div>
                    <div style="font-weight:600;font-size:15px;">微信支付</div>
                    <div style="font-size:12px;color:#9ca3af;margin-top:4px;">WeChat Pay</div>
                </div>
            </div>
            <button id="confirmPayBtn" onclick="confirmPay()" disabled style="width:100%;padding:14px;border:none;border-radius:10px;background:#1a2a3a;color:#fff;font-size:16px;font-weight:600;cursor:not-allowed;opacity:.5;">请选择支付方式</button>
            <p style="text-align:center;font-size:12px;color:#9ca3af;margin-top:12px;">
                <i class="fas fa-shield-alt"></i> 支付成功后需管理员审核开通
            </p>
        </div>
    </div>

    <script>
        var selectedPayMethod = null;
        var currentOrderNo = '<?php echo isset($order) && $order ? h($order["order_no"]) : ""; ?>';
        var currentAmount = <?php echo isset($order) && $order ? (float)$order["amount"] : 0; ?>;
        var orderStatus = <?php echo isset($order) && $order ? (int)$order["status"] : -1; ?>;

        // Show 确认支付 button if order is pending (status=0)
        document.addEventListener('DOMContentLoaded', function() {
            if (orderStatus === 0) {
                document.getElementById('btnConfirmPay').style.display = '';
            }
            var btnConfirm = document.getElementById('btnConfirmPay');
            if (btnConfirm) {
                btnConfirm.style.display = (orderStatus === 0) ? '' : 'none';
            }
        });

        function showPayModal() {
            selectedPayMethod = null;
            document.getElementById('payModalDesc').textContent = '订单号：' + currentOrderNo;
            document.getElementById('payModalAmount').textContent = currentAmount > 0 ? '¥' + currentAmount.toFixed(2) : '';
            document.getElementById('payAlipay').style.borderColor = '#e5e7eb';
            document.getElementById('payAlipay').style.background = '#fff';
            document.getElementById('payWechat').style.borderColor = '#e5e7eb';
            document.getElementById('payWechat').style.background = '#fff';
            var btn = document.getElementById('confirmPayBtn');
            btn.disabled = true;
            btn.style.cursor = 'not-allowed';
            btn.style.opacity = '0.5';
            btn.textContent = '请选择支付方式';
            document.getElementById('paymentModal').style.display = 'flex';
        }

        function closePayModal() {
            document.getElementById('paymentModal').style.display = 'none';
        }

        function selectPayMethod(method) {
            selectedPayMethod = method;
            document.getElementById('payAlipay').style.borderColor = method === 'alipay' ? '#3b82f6' : '#e5e7eb';
            document.getElementById('payAlipay').style.background = method === 'alipay' ? '#eff6ff' : '#fff';
            document.getElementById('payWechat').style.borderColor = method === 'wechat' ? '#10b981' : '#e5e7eb';
            document.getElementById('payWechat').style.background = method === 'wechat' ? '#ecfdf5' : '#fff';
            var btn = document.getElementById('confirmPayBtn');
            btn.disabled = false;
            btn.style.cursor = 'pointer';
            btn.style.opacity = '1';
            btn.textContent = '确认支付';
        }

        function confirmPay() {
            if (!selectedPayMethod || !currentOrderNo) return;
            var btn = document.getElementById('confirmPayBtn');
            btn.disabled = true;
            btn.textContent = '处理中...';

            var formData = new FormData();
            formData.append('order_no', currentOrderNo);
            formData.append('pay_method', selectedPayMethod);

            fetch('/pay/?action=retry', {
                method: 'POST',
                body: formData
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                btn.disabled = false;
                btn.textContent = '确认支付';
                if (data.success) {
                    closePayModal();
                    // Show success message inline
                    document.getElementById('btnGroup').innerHTML = '<div style="background:#ecfdf5;border:1px solid #10b981;border-radius:10px;padding:20px;text-align:center;color:#10b981;"><i class="fas fa-check-circle" style="font-size:32px;margin-bottom:8px;"></i><p style="font-size:16px;font-weight:bold;margin:0 0 4px;">' + data.message + '</p><p style="font-size:13px;margin:0;">请等待管理员审核，审核通过后自动开通</p></div><a href="/pay/?action=orders" class="btn btn-primary" style="padding:12px 24px;margin-top:16px;">查看我的订单</a>';
                } else {
                    alert(data.message || '操作失败，请重试');
                }
            })
            .catch(function(e) {
                btn.disabled = false;
                btn.textContent = '确认支付';
                alert('网络错误，请重试');
            });
        }
    </script>
    </div>


    <?php include __DIR__ . '/_footer.php'; ?>
