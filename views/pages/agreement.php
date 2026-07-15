<?php require_once __DIR__ . "/../../app/config/config.php";
$page_title = '用户协议';
$meta_description = '信鸽之家用户服务协议：注册账号、使用规范、会员权益、免责声明等条款。使用本平台前请仔细阅读。';
$meta_keywords = '用户协议,服务条款,信鸽之家';
$canonical_url = 'https://www.xgjia.com/agreement/';
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
        .container { max-width: 960px; margin: 0 auto; padding: 0 16px; }
        .page-header { background: linear-gradient(135deg, #1a2a3a, #2c3e50); color: #fff; padding: 40px 0; text-align: center; }
        .page-header h1 { font-size: 28px; margin-bottom: 8px; }
        .page-header p { opacity: .8; }
        .content-card { background: #fff; border-radius: 10px; padding: 40px; margin: 32px 0; box-shadow: 0 2px 12px rgba(0,0,0,.08); }
        .content-card h2 { font-size: 18px; font-weight: 700; margin: 28px 0 12px; color: #1a2a3a; }
        .content-card h2:first-child { margin-top: 0; }
        p { line-height: 1.9; color: #555; font-size: 14px; margin: 10px 0; }
        ol { padding-left: 24px; color: #555; font-size: 14px; line-height: 2; }
        ol li { margin: 6px 0; }
        .meta { font-size: 13px; color: #999; margin-top: 4px; }
    </style>
</head>
<body>
<?php include __DIR__ . '/../_head.php'; ?>

<div class="page-header">
    <div class="container">
        <h1>用户协议</h1>
        <p>使用 <?php echo SITE_NAME; ?> 前请仔细阅读</p>
    </div>
</div>

<div class="container">
    <div class="content-card">
        <p class="meta">生效日期：2026年1月1日</p>

        <h2>一、服务说明</h2>
        <p><?php echo SITE_NAME; ?>（以下简称"本站"）为信鸽行业信息交流平台，提供赛鸽数据查询、足环号赛绩分析、血统证书生成、公棚数据对比、铭鸽浏览等服务。您注册并使用本站，即表示同意遵守本协议。</p>

        <h2>二、用户注册</h2>
        <p>您应提供真实、准确的个人信息进行注册。不得使用虚假信息、冒用他人身份或注册多个账号。本站有权对虚假账号进行封禁处理。</p>

        <h2>三、内容规范</h2>
        <p>您在使用本站服务时，不得发布以下内容：</p>
        <ol>
            <li>违反国家法律法规、公序良俗的内容</li>
            <li>虚假信息、欺诈内容</li>
            <li>侵犯他人知识产权、隐私权的内容</li>
            <li>广告推销、垃圾信息</li>
            <li>其他违反本协议的内容</li>
        </ol>

        <h2>四、知识产权</h2>
        <p>用户在本站发布的原创内容，版权归用户所有。用户在发布时应保证拥有该内容的合法权利，不得侵犯他人权益。本站对用户发布的内容享有使用权。</p>

        <h2>五、免责声明</h2>
        <p>本站不对平台上展示的数据内容、准确性负责。用户之间的交易行为由双方自行负责，因交易产生的纠纷与本站无关。本站有权在不通知用户的情况下对违规内容进行删除处理。</p>

        <h2>六、账号管理</h2>
        <p>用户有责任妥善保管账号和密码。因账号被盗用导致的损失，由用户自行承担责任。本站有权基于安全原因暂停或终止用户账号。</p>

        <h2>七、协议修订</h2>
        <p>本站有权随时修订本协议。修订后的协议在本网站公布后即生效。继续使用服务即表示接受修订后的协议。</p>

        <h2>八、联系我们</h2>
        <p>如您对本协议有任何疑问，请联系：<a href="mailto:admin@xgjia.com" style="color:var(--accent);">admin@xgjia.com</a></p>
    </div>
</div>


<?php include __DIR__ . "/../_footer.php"; ?>
