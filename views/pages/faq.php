<?php require_once __DIR__ . "/../../app/config/config.php";
$page_title = '常见问题';
$meta_description = '信鸽之家常见问题：足环号怎么查？血统证书怎么做？公棚对比要收费吗？深度报告包含什么？快速找到你需要的答案。';
$meta_keywords = '信鸽之家常见问题,足环查询,血统证书,公棚对比,深度报告,信鸽,赛鸽';
$canonical_url = 'https://www.xgjia.com/faq/';
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
        .faq-item { border-bottom: 1px solid #f0f0f0; padding: 20px 0; }
        .faq-item:last-child { border-bottom: none; }
        .faq-q { font-weight: 600; font-size: 16px; color: #1a2a3a; margin-bottom: 10px; display: flex; gap: 8px; }
        .faq-q i { color: var(--accent); margin-top: 4px; }
        .faq-a { font-size: 14px; color: #555; line-height: 1.8; padding-left: 28px; }
        @media (max-width: 768px) { .content-card { padding: 24px 20px; } }
    </style>
</head>
<body>
<?php include __DIR__ . '/../_head.php'; ?>

<div class="page-header">
    <div class="container">
        <h1>常见问题</h1>
        <p>关于赛鸽数据查询工具的常见疑问</p>
    </div>
</div>

<div class="container">
    <div class="content-card">

        <div class="faq-item">
            <div class="faq-q"><i class="fas fa-question-circle"></i><span><?php echo SITE_NAME; ?> 是什么平台？</span></div>
            <div class="faq-a"><?php echo SITE_NAME; ?> 是一个赛鸽数据查询工具平台。核心功能包括：足环号赛绩查询（1200万+条记录）、血统证书自动生成、公棚数据对比。不同于内容社区或论坛，我们专注于数据——帮你快速查到鸽子的一切成绩信息。</div>
        </div>

        <div class="faq-item">
            <div class="faq-q"><i class="fas fa-question-circle"></i><span>怎么查鸽子的成绩？</span></div>
            <div class="faq-a">在首页搜索框输入 7 位足环号（纯数字），点击"查鸽子"即可跳转到深度报告页面。报告包含赛季统计、关赛明细、分速分析、同批次对比等内容。第 1 节免费，完整版 ¥9.9。</div>
        </div>

        <div class="faq-item">
            <div class="faq-q"><i class="fas fa-question-circle"></i><span>查不到鸽子成绩怎么办？</span></div>
            <div class="faq-a">数据来源为公开赛事记录，目前覆盖 2026 年赛季 1,444 场赛事。如果你的鸽子没有参加这些赛事，或足环号输入有误，可能查不到。数据持续更新，可联系客服反馈。</div>
        </div>

        <div class="faq-item">
            <div class="faq-q"><i class="fas fa-question-circle"></i><span>血统证书怎么生成？免费吗？</span></div>
            <div class="faq-a">进入血统证书页面，输入足环号即可。系统自动从数据库填入：品系、羽色、眼砂、最佳成绩等信息。预览后按 Ctrl+P / Cmd+P 打印或保存为 PDF。<strong>完全免费。</strong></div>
        </div>

        <div class="faq-item">
            <div class="faq-q"><i class="fas fa-question-circle"></i><span>公棚对比工具怎么用？</span></div>
            <div class="faq-a">进入公棚对比页面，搜索并选择要比较的公棚（最多 4 个），点击"开始对比"查看并排数据表。指标包括参赛费、收鸽数、归巢率、奖金等。基础对比免费预览 5 项，完整对比 ¥19.9 解锁。</div>
        </div>

        <div class="faq-item">
            <div class="faq-q"><i class="fas fa-question-circle"></i><span>怎么支付？支持哪些方式？</span></div>
            <div class="faq-a">当前支持微信支付。购买页面显示收款码 → 扫码支付 → 添加客服微信（pigeon_cs）发截图 → 人工审核激活。我们正在对接 PAYJS 实现自动到账。</div>
        </div>

        <div class="faq-item">
            <div class="faq-q"><i class="fas fa-question-circle"></i><span>付费后多久能解锁？能退款吗？</span></div>
            <div class="faq-a">工作时间内（周一至周五 9:00-18:00）支付后 1 小时内激活，非工作时间次日激活。数字产品一经解锁即视为消费完成，原则上不支持退款。如遇技术问题无法正常使用，请联系客服处理。</div>
        </div>

        <div class="faq-item">
            <div class="faq-q"><i class="fas fa-question-circle"></i><span>需要注册吗？</span></div>
            <div class="faq-a">查询鸽子成绩、浏览公棚信息、生成血统证书均无需注册，完全免费使用。注册账号主要用于：付费购买产品、获取订单记录管理。用户可在需要时注册。</div>
        </div>

        <div class="faq-item">
            <div class="faq-q"><i class="fas fa-question-circle"></i><span>数据来源可靠吗？</span></div>
            <div class="faq-a">全部数据来源于公开赛事记录，经过多个数据源交叉校验和清洗。虽然我们尽力确保准确，但赛鸽数据量庞大，不排除个别疏漏。如发现错误，欢迎反馈。</div>
        </div>

    </div>
</div>

<?php include __DIR__ . "/../_footer.php"; ?>
</body>
</html>