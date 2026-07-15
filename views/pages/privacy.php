<?php require_once __DIR__ . "/../../app/config/config.php";
$page_title = '隐私政策';
$meta_description = '信鸽之家隐私政策：我们如何收集、使用和保护您的个人信息。遵守相关法律法规，保障用户隐私安全。';
$meta_keywords = '隐私政策,用户隐私,信鸽之家';
$canonical_url = 'https://www.xgjia.com/privacy/';
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
        <h1>隐私政策</h1>
        <p>我们重视您的隐私，保护您的个人信息</p>
    </div>
</div>

<div class="container">
    <div class="content-card">
        <p class="meta">生效日期：2026年1月1日</p>

        <h2>一、信息收集</h2>
        <p>我们收集您主动提供的信息，包括：</p>
        <ol>
            <li>注册时填写的用户名、邮箱、手机号码等账户信息</li>
            <li>购买产品时填写的相关信息</li>
            <li>联系客服时填写的联系信息</li>
            <li>使用服务过程中自动收集的访问日志、IP地址等</li>
        </ol>

        <h2>二、信息使用</h2>
        <p>我们使用收集的信息用于：</p>
        <ol>
            <li>提供、维护和改进我们的服务</li>
            <li>验证身份、确保账号安全</li>
            <li>向您推送订单状态和服务通知</li>
            <li>处理用户咨询和投诉</li>
            <li>遵守法律法规的要求</li>
        </ol>

        <h2>三、信息共享</h2>
        <p>未经您的同意，我们不会向第三方出售或出租您的个人信息。以下情况除外：</p>
        <ol>
            <li>获得您的明确授权</li>
            <li>为提供服务而与合作伙伴共享（仅限提供服务所必需的范围）</li>
            <li>法律法规要求必须提供的</li>
            <li>保护本站及用户的合法权益</li>
        </ol>

        <h2>四、信息保护</h2>
        <p>我们采取行业标准的安全措施保护您的个人信息，包括数据加密、访问控制、安全审计等。但互联网数据传输无法保证100%安全，请您理解。</p>

        <h2>五、Cookie使用</h2>
        <p>本站使用Cookie技术来改善用户体验，包括记住登录状态、记住您的偏好设置等。您可以在浏览器中禁用Cookie，但这可能影响部分功能的使用。</p>

        <h2>六、您的权利</h2>
        <p>您对您的个人信息享有以下权利：</p>
        <ol>
            <li>查询和了解您的个人信息</li>
            <li>更正不准确的个人信息</li>
            <li>删除您的个人信息（法律另有规定的除外）</li>
            <li>注销您的账号</li>
        </ol>

        <h2>七、未成年人保护</h2>
        <p>我们不建议未满18周岁的未成年人注册并使用本站服务。如您是未成年人，请在监护人的陪同下使用我们的服务。</p>

        <h2>八、联系我们</h2>
        <p>如您对本隐私政策有任何疑问，请联系：<a href="mailto:admin@xgjia.com" style="color:var(--accent);">admin@xgjia.com</a></p>
    </div>
</div>


<?php include __DIR__ . "/../_footer.php"; ?>
