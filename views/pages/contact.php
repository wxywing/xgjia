<?php require_once __DIR__ . "/../../app/config/config.php";
$page_title = '联系我们';
$meta_description = '联系信鸽之家 — 赛鸽数据查询平台。客服电话、微信、邮箱联系方式，专业赛鸽数据服务。';
$meta_keywords = '联系信鸽之家,客服电话,微信,赛鸽数据,信鸽,赛鸽';
$canonical_url = 'https://www.xgjia.com/contact/';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <?php include __DIR__ . '/../_seo_head.php'; ?>
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/public/css/b-scheme.css">
    <style>
        :root { --primary: #1a2a3a; --accent: #d4a843; --bg: #f0f2f5; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: var(--bg); color: #2c3e50; }
        .container { max-width: 900px; margin: 0 auto; padding: 0 16px; }
        .page-header { background: linear-gradient(135deg, #1a2a3a, #2c3e50); color: #fff; padding: 40px 0; text-align: center; }
        .page-header h1 { font-size: 28px; margin-bottom: 8px; }
        .page-header p { opacity: .8; }
        .content-card { background: #fff; border-radius: 10px; padding: 40px; margin: 32px 0; box-shadow: 0 2px 12px rgba(0,0,0,.08); }
        .content-card h2 { font-size: 20px; font-weight: 700; margin: 28px 0 14px; border-left: 3px solid var(--accent); padding-left: 12px; }
        .content-card h2:first-child { margin-top: 0; }
        .info-list { list-style: none; }
        .info-list li { padding: 14px 0; font-size: 15px; display: flex; gap: 12px; border-bottom: 1px solid #f0f0f0; }
        .info-list li:last-child { border-bottom: none; }
        .info-list i { color: var(--accent); width: 24px; flex-shrink: 0; margin-top: 2px; }
        .info-list .label { color: #888; min-width: 80px; flex-shrink: 0; }
        .info-list .value { font-weight: 500; }
        p { line-height: 1.8; color: #555; font-size: 15px; margin: 12px 0; }

        .qr-section { display: flex; gap: 32px; margin-top: 20px; align-items: flex-start; flex-wrap: wrap; }
        .qr-card { text-align: center; background: #f9fafb; border-radius: 10px; padding: 24px; min-width: 180px; }
        .qr-card img { width: 150px; height: 150px; border-radius: 6px; border: 1px solid #e5e7eb; }
        .qr-card .qr-title { font-size: 14px; font-weight: 600; color: #374151; margin-bottom: 12px; }
        .qr-card .qr-sub { font-size: 12px; color: #9ca3af; margin-top: 8px; }
        @media (max-width: 768px) {
            .content-card { padding: 24px 20px; }
            .qr-section { flex-direction: column; align-items: center; }
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/../_head.php'; ?>

<div class="page-header">
    <div class="container">
        <h1>联系我们</h1>
        <p>赛鸽数据查询平台 — 有问题随时联系</p>
    </div>
</div>

<div class="container">
    <div class="content-card">
        <h2><i class="fas fa-info-circle" style="color:var(--accent);margin-right:6px;"></i>联系方式</h2>
        <ul class="info-list">
            <li><i class="fas fa-phone"></i><span class="label">客服电话</span><span class="value">400-000-0000</span></li>
            <li><i class="fas fa-envelope"></i><span class="label">邮箱</span><span class="value">admin@xgjia.com</span></li>
            <li><i class="fas fa-clock"></i><span class="label">工作时间</span><span class="value">周一至周五 9:00 - 18:00</span></li>
            <li><i class="fas fa-map-marker-alt"></i><span class="label">地址</span><span class="value">北京市通州区</span></li>
        </ul>

        <h2><i class="fab fa-weixin" style="color:#07c160;margin-right:6px;"></i>客服微信</h2>
        <p>扫码添加客服微信，咨询产品、反馈问题、申请审批</p>
        <div class="qr-section">
            <div class="qr-card">
                <div class="qr-title">客服微信</div>
                <img src="/public/images/qrcode-wechat.jpg" alt="客服微信二维码" onerror="this.style.display='none';this.parentElement.innerHTML+='<p style=color:#9ca3af;font-size:13px;margin-top:8px>二维码待上传</p>'">
                <div class="qr-sub">微信号：pigeon_cs</div>
            </div>
        </div>

        <h2><i class="fas fa-bullhorn" style="color:var(--accent);margin-right:6px;"></i>商务合作</h2>
        <p>如果您有意与 <?php echo SITE_NAME; ?> 进行商务合作（广告投放、数据合作、产品咨询），请发送邮件至 <strong>admin@xgjia.com</strong>，邮件标题请注明"商务合作"，我们会在 1-3 个工作日内回复。</p>

        <h2><i class="fas fa-envelope" style="color:var(--accent);margin-right:6px;"></i>意见反馈</h2>
        <p>对功能有建议或发现问题？发送邮件至 <strong>admin@xgjia.com</strong>，每一条反馈我们都会认真评估。</p>
    </div>
</div>

<?php include __DIR__ . "/../_footer.php"; ?>
</body>
</html>