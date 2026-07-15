<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="开通信鸽之家VIP会员：月付¥29 / 年付¥299，无限发布文章、铭鸽、分类信息，查看联系方式，优先审核。">
    <meta name="keywords" content="升级会员,会员升级,开通会员,信鸽会员,会员特权,VIP会员">
    <meta property="og:title" content="升级VIP会员 - 信鸽之家">
    <meta property="og:description" content="开通信鸽之家VIP会员：月付¥29 / 年付¥299，无限发布文章、铭鸽、分类信息，查看联系方式，优先审核。">
    <meta property="og:image" content="https://www.xgjia.com/public/images/og-cover.png">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="信鸽之家">
    <link rel="canonical" href="https://www.xgjia.com/upgrade">

    <title><?= $pageTitle ?? '信鸽之家' ?></title>
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/public/css/b-scheme.css">
</head>
<body>
    <?php include __DIR__ . '/_head.php'; ?>

    <?php
    // 防御：确保从入口文件传递的变量都有默认值
    if (!isset($planName))  $planName  = 'VIP会员';
    if (!isset($planLabel)) $planLabel = '月付';
    if (!isset($amount))    $amount    = 29;
    if (!isset($planType))  $planType  = 1;
    if (!isset($pageTitle)) $pageTitle = '开通VIP会员 - 信鸽之家';
    ?>

    <div class="container" style="padding: 40px 0;">
        <div class="upgrade-page">
            <h1 class="page-title">升级VIP会员</h1>
            <p class="page-desc">成为VIP会员，享受更多特权</p>
            
            <?php if ($user['member_level'] == 1): ?>
            <div class="member-status">
                <i class="fas fa-crown" style="color: #ffc107;"></i>
                <span>您已是VIP会员</span>
                <?php if ($user['member_expire_at']): ?>
                <span class="expire-date">有效期至：<?= date('Y-m-d', strtotime($user['member_expire_at'])) ?></span>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <!-- 已选方案确认 -->
            <?php if (!empty($pending)): ?>
<div style="max-width:500px;margin:0 auto;text-align:center;padding:40px 20px;">
    <div style="font-size:64px;margin-bottom:16px;color:#f59e0b;"><i class="fas fa-clock"></i></div>
    <h2 style="font-size:22px;font-weight:bold;color:#1f2937;margin-bottom:12px;">订单已提交，等待审核</h2>
    <p style="font-size:15px;color:#6b7280;margin-bottom:8px;">您的订购申请已提交成功，管理员将尽快审核处理。</p>
    <p style="font-size:14px;color:#9ca3af;margin-bottom:32px;">审核通过后您的会员将自动开通，届时可享受全部会员权益。</p>
    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;padding:20px;margin-bottom:32px;text-align:left;font-size:14px;color:#166534;">
        <p style="margin:0 0 8px;font-weight:bold;">💡 如需加急处理，请联系我们：</p>
        <p style="margin:0;">• <a href="/pages/contact/" style="color:#1a5fa8;text-decoration:underline;">联系客服</a>，请提供订单号：</p>
        <?php if (!empty($orderNo)): ?>
        <p style="margin:0;font-size:15px;color:#1a5fa8;font-weight:bold;">📋 订单号：<?php echo h($orderNo); ?></p>
        <?php endif; ?>
    </div>
    <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
        <a href="/user/membership" class="btn btn-primary" style="padding:12px 24px;background:#1a5fa8;color:#fff;text-decoration:none;border-radius:10px;font-weight:600;">返回会员中心</a>
        <a href="/pay/?action=orders" class="btn btn-outline" style="padding:12px 24px;border:2px solid #d1d5db;color:#374151;text-decoration:none;border-radius:10px;font-weight:600;">查看我的订单</a>
    </div>
</div>
<?php else: ?>

<div class="plan-confirm-box" style="background:#f0f7ff;border:2px solid #3b82f6;border-radius:12px;padding:24px;margin-bottom:24px;display:flex;align-items:center;gap:20px;">
                <div style="flex-shrink:0;width:60px;height:60px;background:#e0efff;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                    <i class="fas <?php echo $planType == 2 ? 'fa-crown' : 'fa-calendar-alt'; ?>" style="font-size:28px;color:#3b82f6;"></i>
                </div>
                <div style="flex:1;">
                    <div style="font-size:14px;color:#666;margin-bottom:4px;">您选择开通</div>
                    <div style="font-size:22px;font-weight:bold;color:#1a1a2e;margin-bottom:4px;"><?php echo h($planName); ?></div>
                    <div style="font-size:20px;color:#3b82f6;font-weight:bold;">¥<?php echo $amount; ?><span style="font-size:14px;font-weight:normal;color:#666;"> / <?php echo $planLabel; ?></span></div>
                    <?php if ($planType == 2): ?>
                    <div style="font-size:13px;color:#059669;margin-top:4px;">比月付省 40%，相当于 ¥25/月</div>
                    <?php else: ?>
                    <div style="font-size:13px;color:#666;margin-top:4px;">随时可取消，所有高级功能全开</div>
                    <?php endif; ?>
                </div>
                <a href="/user/membership" style="font-size:13px;color:#999;text-decoration:underline;white-space:nowrap;">重新选择</a>
            </div>
            
            <div class="payment-methods">
                <h3>支付方式</h3>
                <div class="payment-options">
                    <label class="payment-option">
                        <input type="radio" name="payment" value="wechat" checked>
                        <i class="fab fa-weixin" style="color: #07c160;"></i>
                        <span>微信支付</span>
                    </label>
                    <label class="payment-option">
                        <input type="radio" name="payment" value="alipay">
                        <i class="fab fa-alipay" style="color: #1677ff;"></i>
                        <span>支付宝</span>
                    </label>
                </div>
            </div>
            
            <div style="margin: 24px 0; text-align: center;">
                <button id="createOrderBtn" onclick="createOrderThenShowModal()" style="
                    background: linear-gradient(135deg, #1a5fa8, #2980b9);
                    color: #fff;
                    border: none;
                    padding: 14px 48px;
                    font-size: 17px;
                    font-weight: bold;
                    border-radius: 8px;
                    cursor: pointer;
                    transition: all 0.3s;
                    box-shadow: 0 4px 12px rgba(26,95,168,0.3);
                " onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 6px 20px rgba(26,95,168,0.4)'" onmouseout="this.style.transform='none';this.style.boxShadow='0 4px 12px rgba(26,95,168,0.3)'">
                    <i class="fas fa-lock" style="margin-right:8px;"></i>确认支付 ¥<?php echo $amount; ?>
                </button>
            </div>
            
            <div id="paymentStatus" class="payment-status" style="display:none; margin-bottom: 16px; padding: 12px 16px; border-radius: 8px; text-align: center;"></div>
            
            <div class="upgrade-faq">
                <h3>常见问题</h3>
                <div class="faq-item">
                    <h4>会员可以发布多少内容？</h4>
                    <p>VIP会员可以无限制发布文章、铭鸽展示和分类信息。</p>
                </div>
                <div class="faq-item">
                    <h4>会员到期后发布的内容会怎样？</h4>
                    <p>会员到期后，已发布的内容不会删除，但无法继续发布新内容。</p>
                </div>
                <div class="faq-item">
                    <h4>可以退款吗？</h4>
                    <p>虚拟商品一经开通不支持退款，请谨慎购买。</p>
                </div>
            </div>
        </div>
    </div>
    
    
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

    .upgrade-page {
        max-width: 900px;
        margin: 0 auto;
    }
    
    .page-title {
        text-align: center;
        font-size: 32px;
        margin-bottom: 10px;
    }
    
    .page-desc {
        text-align: center;
        color: #666;
        margin-bottom: 30px;
    }
    
    .member-status {
        text-align: center;
        padding: 20px;
        background: #fff9e6;
        border-radius: 8px;
        margin-bottom: 30px;
    }
    
    .member-status i {
        font-size: 24px;
        margin-right: 10px;
    }
    
    .expire-date {
        margin-left: 15px;
        color: #666;
    }
    
    .membership-plans {
        display: flex;
        gap: 30px;
        justify-content: center;
        margin-bottom: 40px;
    }
    
    .plan-card {
        flex: 1;
        max-width: 350px;
        background: #fff;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.1);
        position: relative;
    }
    
    .plan-card.recommended {
        border: 2px solid #ffc107;
    }
    
    .recommend-badge {
        position: absolute;
        top: -12px;
        left: 50%;
        transform: translateX(-50%);
        background: #ffc107;
        color: #fff;
        padding: 4px 16px;
        border-radius: 20px;
        font-size: 14px;
    }
    
    .plan-header {
        text-align: center;
        margin-bottom: 20px;
    }
    
    .plan-header h3 {
        font-size: 20px;
        margin-bottom: 10px;
    }
    
    .plan-price {
        font-size: 36px;
        font-weight: bold;
        color: #ff5722;
    }
    
    .plan-price .period {
        font-size: 16px;
        color: #666;
        font-weight: normal;
    }
    
    .save-tip {
        color: #4caf50;
        font-size: 14px;
        margin-top: 5px;
    }
    
    .plan-features {
        list-style: none;
        padding: 0;
        margin-bottom: 20px;
    }
    
    .plan-features li {
        padding: 10px 0;
        border-bottom: 1px solid #eee;
    }
    
    .plan-features li i {
        color: #4caf50;
        margin-right: 10px;
    }
    
    .btn-upgrade {
        width: 100%;
        padding: 12px;
        font-size: 16px;
    }
    
    .payment-methods {
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 30px;
    }
    
    .payment-methods h3 {
        margin-bottom: 15px;
    }
    
    .payment-options {
        display: flex;
        gap: 20px;
    }
    
    .payment-option {
        display: flex;
        align-items: center;
        padding: 15px 20px;
        border: 1px solid #ddd;
        border-radius: 8px;
        cursor: pointer;
    }
    
    .payment-option input {
        margin-right: 10px;
    }
    
    .payment-option i {
        font-size: 24px;
        margin-right: 10px;
    }
    
    .upgrade-faq {
        background: #fff;
        padding: 20px;
        border-radius: 8px;
    }
    
    .upgrade-faq h3 {
        margin-bottom: 15px;
    }
    
    .faq-item {
        margin-bottom: 15px;
    }
    
    .faq-item h4 {
        font-size: 16px;
        margin-bottom: 5px;
    }
    
    .faq-item p {
        color: #666;
        margin: 0;
    }
    
    @media (max-width: 768px) {
        .membership-plans {
            flex-direction: column;
            align-items: center;
        }
        
        .plan-card {
            max-width: 100%;
        }
    }

    /* 支付模态框 */
    .payment-modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.6);
        z-index: 9999;
        justify-content: center;
        align-items: center;
    }
    .payment-modal-overlay.active {
        display: flex;
    }
    .payment-modal {
        background: #fff;
        border-radius: 16px;
        padding: 30px;
        max-width: 400px;
        width: 90%;
        text-align: center;
        position: relative;
    }
    .payment-modal-close {
        position: absolute;
        top: 15px;
        right: 15px;
        font-size: 24px;
        color: #999;
        cursor: pointer;
        background: none;
        border: none;
    }
    .payment-modal-close:hover { color: #333; }
    .payment-modal h3 {
        margin-bottom: 20px;
        font-size: 20px;
    }
    .payment-order-info {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
        text-align: left;
    }
    .payment-order-info p {
        margin: 5px 0;
        font-size: 14px;
        color: #666;
    }
    .payment-order-info strong { color: #333; }
    .payment-qr-box {
        padding: 15px;
        background: #fff;
        border: 2px dashed #ddd;
        border-radius: 8px;
        margin-bottom: 15px;
    }
    .payment-qr-box img {
        width: 200px;
        height: 200px;
    }
    .payment-status {
        padding: 12px;
        border-radius: 8px;
        font-size: 14px;
        margin-bottom: 15px;
    }
    .payment-status.pending {
        background: #fff7e6;
        color: #fa8c16;
        border: 1px solid #ffd591;
    }
    .payment-status.paid {
        background: #f6ffed;
        color: #52c41a;
        border: 1px solid #b7eb8f;
    }
    .payment-status.error {
        background: #fff2f0;
        color: #ff4d4f;
        border: 1px solid #ffccc7;
    }
    .btn-confirm-pay {
        width: 100%;
        padding: 14px;
        font-size: 16px;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        margin-bottom: 10px;
    }
    .btn-confirm-pay.wechat {
        background: #07c160;
        color: #fff;
    }
    .btn-confirm-pay.alipay {
        background: #1677ff;
        color: #fff;
    }
    .btn-simulation {
        background: #f5f5f5;
        color: #666;
        border: 1px solid #ddd;
        width: 100%;
        padding: 10px;
        font-size: 13px;
        border-radius: 6px;
        cursor: pointer;
        margin-top: 10px;
    }
    .btn-simulation:hover { background: #eee; }
    .mock-pay-hint {
        font-size: 12px;
        color: #999;
        margin-top: 8px;
    }
    @media (max-width: 768px) {
        .payment-modal { padding: 20px; }
        .payment-qr-box img { width: 160px; height: 160px; }
    }
        </style>
    
    <script>
    // 创建订单 & 弹窗
    function showQRModal() {
        var simBtn = document.getElementById('qrSimBtn');
        if (simBtn) { simBtn.textContent = '创建订单中…'; simBtn.disabled = true; }
        
        var isWechat = document.querySelector('input[name="payment"]:checked').value === 'wechat';
        var qrIcon = document.getElementById('qrPayIcon');
        if (isWechat) {
            qrIcon.className = 'fab fa-weixin';
            qrIcon.parentElement.style.background = '#07c160';
            document.getElementById('qrPayTitle').textContent = '微信扫码支付';
            document.getElementById('qrPayHint').textContent = '请使用微信扫描二维码完成支付';
        } else {
            qrIcon.className = 'fab fa-alipay';
            qrIcon.parentElement.style.background = '#1677ff';
            document.getElementById('qrPayTitle').textContent = '支付宝扫码支付';
            document.getElementById('qrPayHint').textContent = '请使用支付宝扫描二维码完成支付';
        }
        
        document.getElementById('qrModal').style.display = 'flex';
        createOrderThenShowModal();
    }
    
    function createOrderThenShowModal() {
        var payment = document.querySelector('input[name="payment"]:checked').value;
        var planType = <?= $planType ?>;
        
        var formData = new FormData();
        formData.append('plan_type', planType);
        formData.append('pay_method', payment);
        
        fetch('/payment/create', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                plan_type: planType,
                pay_method: payment,
            }).toString(),
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            var simBtn = document.getElementById('qrSimBtn');
            var qrModal = document.getElementById('qrModal');
            if (data.success) {
                document.getElementById('qrOrderNo').value = data.order_no;
                document.getElementById('qrOrderNoDisplay').textContent = data.order_no;
                document.getElementById('qrAmount').textContent = '¥' + data.amount;
                if (simBtn) { simBtn.textContent = '模拟扫码完成支付'; simBtn.disabled = false; }
                if (qrModal) { qrModal.style.display = 'flex'; }
            } else {
                alert(data.message || '创建订单失败');
                closeQRModal();
            }
        })
        .catch(function(err) {
            alert('网络错误');
            closeQRModal();
        });
    }
    
    function simulatePayment() {
        var simBtn = document.getElementById('qrSimBtn');
        var orderNo = document.getElementById('qrOrderNo').value;
        simBtn.disabled = true;
        simBtn.textContent = '订单已提交，等待审核…';
        setTimeout(function() {
            window.location.href = '/upgrade?plan_type=' + <?= $planType ?> + '&status=pending&order_no=' + encodeURIComponent(orderNo);
        }, 1500);
    }
    
    function closeQRModal() {
        document.getElementById('qrModal').style.display = 'none';
    }
    </script>
    
    <!-- 支付二维码弹窗 -->
<div id="qrModal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.55);align-items:center;justify-content:center;">
        <div style="background:#fff;border-radius:16px;width:90%;max-width:400px;padding:32px;position:relative;text-align:center;">
            <button onclick="closeQRModal()" style="position:absolute;top:16px;right:16px;background:none;border:none;font-size:28px;cursor:pointer;color:#9ca3af;">&times;</button>
            <div style="width:64px;height:64px;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:32px;color:#fff;" id="qrPayIconBox"><i class="fab fa-weixin" id="qrPayIcon"></i></div>
            <h3 id="qrPayTitle" style="font-size:20px;font-weight:bold;margin-bottom:8px;">微信扫码支付</h3>
            <p id="qrPayHint" style="font-size:14px;color:#6b7280;margin-bottom:20px;">请使用微信扫描二维码完成支付</p>
            <div style="background:#f9fafb;border-radius:12px;padding:24px;margin-bottom:20px;">
                <img id="qrImg" src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=xgjia_member" alt="支付二维码" style="width:180px;height:180px;">
            </div>
            <div style="background:#f0fdf4;border-radius:10px;padding:16px;margin-bottom:20px;text-align:left;font-size:14px;">
                <div style="display:flex;justify-content:space-between;margin-bottom:6px;"><span style="color:#6b7280;">商品</span><strong><?= h($planName) ?></strong></div>
                <div style="display:flex;justify-content:space-between;margin-bottom:6px;"><span style="color:#6b7280;">金额</span><strong style="color:#d4a843;font-size:20px;" id="qrAmount">¥<?= $amount ?></strong></div>
                <div style="display:flex;justify-content:space-between;"><span style="color:#6b7280;">订单号</span><strong id="qrOrderNoDisplay" style="font-size:12px;color:#6b7280;">创建中…</strong></div>
            </div>
            <input type="hidden" id="qrOrderNo" value="">
            <button id="qrSimBtn" onclick="simulatePayment()" disabled style="width:100%;padding:14px;border:none;border-radius:10px;background:#1a2a3a;color:#fff;font-size:16px;font-weight:600;cursor:not-allowed;opacity:.5;">创建订单中…</button>
            <p style="font-size:12px;color:#9ca3af;margin-top:12px;"><i class="fas fa-shield-alt"></i> 支付成功后需管理员审核开通</p>
        </div>
    </div>
    <?php endif; ?>
    <nav class="mobile-bottom-nav">
        <div class="nav-items">
            <div class="nav-item" onclick="location.href='/'"><i class="fas fa-home"></i><span>首页</span></div>
            <div class="nav-item" onclick="location.href='/article/'"><i class="fas fa-newspaper"></i><span>资讯</span></div>
            <div class="nav-item" onclick="location.href='/shop/'"><i class="fas fa-dove"></i><span>铭鸽</span></div>
            <div class="nav-item" onclick="location.href='/loft/'"><i class="fas fa-building"></i><span>公棚</span></div>
            <div class="nav-item" onclick="location.href='/dynamics/'"><i class="fas fa-comments"></i><span>鸽友圈</span></div>
        </div>
    </nav>

    <!-- JavaScript -->

    <?php include __DIR__ . '/_footer.php'; ?>
</body>
</html>
