<?php require_once __DIR__ . "/../../app/config/config.php";
$page_title = '广告合作';
$meta_description = '与信鸽之家合作推广：精准触达全国赛鸽爱好者。首页推荐、工具页品牌植入、公棚数据合作等多种形式。';
$meta_keywords = '广告合作,信鸽广告,推广,信鸽,赛鸽,公棚合作';
$canonical_url = 'https://www.xgjia.com/ad/';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        p { line-height: 1.8; color: #555; font-size: 15px; margin: 12px 0; }
        .ad-type { background: #f9fafb; border-radius: 8px; padding: 20px; margin: 14px 0; border-left: 3px solid var(--accent); }
        .ad-type h3 { font-size: 16px; font-weight: 600; margin-bottom: 8px; color: #1a2a3a; }
        .ad-type p { margin: 0; font-size: 14px; }
        .contact-box { background: #f0f7ff; border-radius: 8px; padding: 24px; margin-top: 24px; text-align: center; }
        .contact-box p { font-size: 16px; font-weight: 500; color: #1a2a3a; margin: 0; }
        .contact-box a { color: var(--accent); text-decoration: none; }
        @media (max-width: 768px) { .content-card { padding: 24px 20px; } }
    </style>
</head>
<body>
<?php include __DIR__ . '/../_head.php'; ?>

<div class="page-header">
    <div class="container">
        <h1>广告合作</h1>
        <p>精准触达全国赛鸽爱好者</p>
    </div>
</div>

<div class="container">
    <div class="content-card">
        <h2><i class="fas fa-star" style="color:var(--accent);margin-right:6px;"></i>合作价值</h2>
        <p><?php echo SITE_NAME; ?> 是专业的赛鸽数据查询工具平台，汇聚了全国赛鸽运动的核心受众——公棚决策者、职业鸽舍、赛事爱好者。与内容门户不同，我们的用户带着明确查询需求访问，是高意向的精准人群。</p>

        <h2><i class="fas fa-ad" style="color:var(--accent);margin-right:6px;"></i>广告形式</h2>
        <div class="ad-type">
            <h3>🏠 首页工具卡片推荐</h3>
            <p>在首页数据工具区展示您的产品或服务，采用与原生功能卡片一致的展示风格，融入感强、点击转化高。</p>
        </div>
        <div class="ad-type">
            <h3>📊 数据页品牌植入</h3>
            <p>在足环查询报告页、公棚对比页等高频访问页面展示品牌信息（logo + 简介 + 链接），精准触达正在做决策的鸽友。</p>
        </div>
        <div class="ad-type">
            <h3>🔍 公棚数据合作</h3>
            <p>公棚专属页面升级：置顶推荐、联系方式高亮、赛事数据第一时间更新。已收录 550+ 公棚，合作公棚优先展示。</p>
        </div>
        <div class="ad-type">
            <h3>📬 鸽友定向推送</h3>
            <p>基于查询数据定向触达：例如向查询过特定地区公棚的鸽友推送该地区赛事或鸽舍信息。</p>
        </div>

        <h2><i class="fas fa-handshake" style="color:var(--accent);margin-right:6px;"></i>合作流程</h2>
        <p>1. 联系我们说明合作意向 → 2. 沟通目标受众与预算 → 3. 定制方案与报价 → 4. 确认上线与数据复盘</p>

        <div class="contact-box">
            <p><i class="fas fa-envelope" style="margin-right:8px;"></i>有意合作请发送邮件至 <a href="mailto:admin@xgjia.com">admin@xgjia.com</a><br><small style="font-weight:400;font-size:13px;color:#888;">邮件标题请注明"广告合作"</small></p>
        </div>
    </div>
</div>

<?php include __DIR__ . "/../_footer.php"; ?>
</body>
</html>