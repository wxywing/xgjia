<?php
/**
 * 信鸽之家 - 足环代码速查表（数据工具）
 */
require_once dirname(__DIR__, 2) . '/app/config/config.php';

extract($data);

$page_title = $pageTitle ?? '足环代码对照表 | 信鸽足环号归属速查';
$page_desc  = $pageDesc  ?? '中国信鸽足环号年份省份代码对照表，快速查询足环归属地。';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo h($page_title); ?></title>
    <meta name="description" content="<?php echo h($page_desc); ?>">
    <meta name="keywords" content="足环代码,信鸽足环,足环号查询,信鸽省份代码,鸽子足环归属">
    <meta property="og:title" content="<?php echo h($page_title); ?>">
    <meta property="og:description" content="<?php echo h($page_desc); ?>">
    <meta property="og:image" content="https://www.xgjia.com/public/images/og-cover.png">
    <link rel="canonical" href="https://www.xgjia.com/tools/ring-guide/">
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/public/css/b-scheme.css">
    <link rel="shortcut icon" href="/public/images/favicon.ico">
</head>
<body>
<?php include __DIR__ . '/../_head.php'; ?>
<!-- ===== 足环速查表 ===== -->
<style>
.ring-tools-header {
    background: linear-gradient(135deg, var(--primary) 0%, #0d3b6e 100%);
    color: white;
    padding: 60px 20px;
    text-align: center;
}
.ring-tools-header h1 { font-size: 32px; font-weight: 800; margin-bottom: 12px; }
.ring-tools-header p  { font-size: 16px; opacity: 0.9; max-width: 650px; margin: 0 auto 20px; line-height: 1.7; }
.ring-tools-header .header-badge {
    display: inline-block;
    background: rgba(255,255,255,0.15);
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 13px;
    margin-bottom: 16px;
}
.ring-container { max-width: 960px; margin: 0 auto; padding: 40px 20px 80px; }
.rule-card {
    background: #fff; border-radius: 12px; padding: 28px 32px; margin-bottom: 32px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06); border-left: 4px solid var(--primary);
}
.rule-card h2 { font-size: 18px; font-weight: 700; margin-bottom: 12px; color: var(--text); }
.rule-card p  { font-size: 14px; color: var(--text-light); line-height: 1.8; margin: 0; }
.rule-card code {
    background: #f0f4f8; padding: 2px 8px; border-radius: 4px; font-size: 14px;
    font-weight: 700; color: var(--primary);
}
.year-nav { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 28px; }
.year-nav a {
    padding: 8px 20px; border-radius: 22px; font-size: 13px; font-weight: 600;
    text-decoration: none; background: var(--border); color: var(--text); transition: all 0.2s;
}
.year-nav a:hover, .year-nav a.active { background: var(--primary); color: white; }
.code-table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,0.06); }
.code-table thead { background: var(--primary); color: white; }
.code-table th { padding: 14px 16px; font-size: 14px; font-weight: 700; text-align: left; }
.code-table td { padding: 12px 16px; font-size: 14px; border-bottom: 1px solid var(--border); }
.code-table tbody tr:hover { background: #f8fafc; }
.code-table tbody tr:last-child td { border-bottom: none; }
.code-table .code-cell { font-family: 'Courier New', monospace; font-weight: 700; color: var(--primary); font-size: 16px; }
.code-table .name-cell { font-weight: 600; }
.code-table .pinyin-cell { color: var(--text-light); font-size: 12px; font-style: italic; }
.tips-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-top: 40px; }
.tip-card { background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); }
.tip-card .tip-icon { font-size: 28px; margin-bottom: 12px; }
.tip-card h3 { font-size: 15px; font-weight: 700; margin-bottom: 8px; color: var(--text); }
.tip-card p  { font-size: 13px; color: var(--text-light); line-height: 1.7; margin: 0; }
.faq-section { margin-top: 48px; }
.faq-section h2 { font-size: 22px; font-weight: 800; margin-bottom: 24px; text-align: center; }
.faq-item { background: #fff; border-radius: 12px; padding: 20px 24px; margin-bottom: 12px; box-shadow: 0 1px 6px rgba(0,0,0,0.04); cursor: pointer; }
.faq-item .faq-q { font-size: 15px; font-weight: 700; color: var(--text); display: flex; align-items: center; gap: 8px; }
.faq-item .faq-q::before { content: 'Q'; color: var(--primary); font-weight: 800; font-size: 18px; }
.faq-item .faq-a { font-size: 14px; color: var(--text-light); line-height: 1.7; margin-top: 10px; padding-top: 10px; border-top: 1px solid var(--border); display: none; }
.faq-item.open .faq-a { display: block; }
.cta-section { text-align: center; margin-top: 48px; padding: 40px; background: linear-gradient(135deg, #f8fafc 0%, #eef2f7 100%); border-radius: 12px; }
.cta-section h3 { font-size: 18px; font-weight: 700; margin-bottom: 8px; }
.cta-section p  { font-size: 14px; color: var(--text-light); margin-bottom: 20px; }
.cta-btn { display: inline-flex; align-items: center; gap: 8px; background: var(--accent); color: white; padding: 12px 32px; border-radius: 28px; font-size: 15px; font-weight: 700; text-decoration: none; transition: all 0.2s; }
.cta-btn:hover { background: #b89237; transform: translateY(-1px); }
@media (max-width: 768px) {
    .ring-tools-header { padding: 40px 16px; }
    .ring-tools-header h1 { font-size: 24px; }
    .code-table { font-size: 13px; }
    .code-table th, .code-table td { padding: 8px 10px; }
}
</style>

<div class="ring-tools-header">
    <span class="header-badge">📋 数据工具</span>
    <h1>中国信鸽足环代码对照表</h1>
    <p>信鸽足环号格式为 <code>年份-省份代码-编号</code>（如 <code>2026-01-0123456</code>），年份取后两位，省份代码为01-33。本表涵盖全部33个足环编码对应的省份/单位。</p>
</div>

<div class="ring-container">
    <div class="rule-card">
        <h2>📖 足环编码规则</h2>
        <p>中国信鸽协会统一采用「<code>年份后两位-省份代码-流水号</code>」格式。例如 <code>2026-01-0123456</code> 表示：<strong>2026年 北京 编号0123456</strong>。省份代码 01-31 对应省级行政区，32 为火车头信鸽协会，33 为中国信鸽协会直属。</p>
    </div>

    <div class="year-nav">
        <a href="#year-2026" class="active" onclick="document.getElementById('year-2026').scrollIntoView({behavior:'smooth'});document.querySelectorAll('.year-nav a').forEach(a=>a.classList.remove('active'));event.target.classList.add('active');return false;">2026</a>
        <a href="#year-2025" onclick="document.getElementById('year-2026').scrollIntoView({behavior:'smooth'});return false;">2025</a>
        <a href="#year-2024" onclick="document.getElementById('year-2026').scrollIntoView({behavior:'smooth'});return false;">2024</a>
        <a href="#year-2023" onclick="document.getElementById('year-2026').scrollIntoView({behavior:'smooth'});return false;">2023</a>
    </div>

    <h2 id="year-2026" style="font-size:20px;font-weight:800;margin:32px 0 16px;">🏁 省份代码对照（全部年份通用）</h2>
    <table class="code-table">
        <thead>
            <tr><th>代码</th><th>省份/单位</th><th>拼音</th><th>示例足环</th></tr>
        </thead>
        <tbody>
            <?php foreach ($codes as $code): ?>
            <tr>
                <td class="code-cell"><?php echo h($code['code']); ?></td>
                <td class="name-cell"><?php echo h($code['name']); ?></td>
                <td class="pinyin-cell"><?php echo h($code['pinyin']); ?></td>
                <td><code style="background:#f0f4f8;padding:2px 8px;border-radius:4px;font-size:13px;">2026-<?php echo h($code['code']); ?>-XXXXXXX</code></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="tips-grid">
        <div class="tip-card">
            <div class="tip-icon">🔍</div>
            <h3>快速识别足环归属</h3>
            <p>看到 <code>2026-14-XXXXXXX</code>，查表得知"14"对应<strong>江西</strong>，立即知道这只鸽子来自江西。</p>
        </div>
        <div class="tip-card">
            <div class="tip-icon">📊</div>
            <h3>赛绩数据分析</h3>
            <p>想知道哪个省份的鸽子飞得最快？记住代码后可以直接在<a href="/race/" style="color:var(--primary);">赛事数据</a>中按省份筛选。</p>
        </div>
        <div class="tip-card">
            <div class="tip-icon">🕊️</div>
            <h3>买鸽卖鸽参考</h3>
            <p>看上一只鸽子，瞄一眼足环号就能判断年份和来源，避免被忽悠。老鸽友都懂这个。</p>
        </div>
    </div>

    <div class="faq-section">
        <h2>❓ 常见问题</h2>
        <div class="faq-item" onclick="this.classList.toggle('open')">
            <div class="faq-q">足环号有 7 位数和 8 位数的区别吗？</div>
            <div class="faq-a">流水号理论上 7 位足够（每年每省不到 1000 万只），但部分年份/省份可能会用 8 位。编号位数不影响省份代码的识别。</div>
        </div>
        <div class="faq-item" onclick="this.classList.toggle('open')">
            <div class="faq-q">"??" 代码是什么意思？</div>
            <div class="faq-a">表示数据库记录中省份代码不完整或非标准格式。属于少量边缘数据，不影响整体查询。</div>
        </div>
        <div class="faq-item" onclick="this.classList.toggle('open')">
            <div class="faq-q">火车头信鸽协会怎么会有自己的足环？</div>
            <div class="faq-a">火车头（32）和中鸽协（33）是历史遗留的特殊编码单位。火车头信鸽协会曾是铁道部下属的独立赛鸽组织，拥有独立的足环编码权。</div>
        </div>
        <div class="faq-item" onclick="this.classList.toggle('open')">
            <div class="faq-q">足环上的年份用两位还是四位？</div>
            <div class="faq-a">早期足环年份用后两位（如 25 代表 2025），近年新环已统一用四位年份（如 2026）。足环实物上看年份位置即可判断。</div>
        </div>
    </div>

    <div class="cta-section">
        <h3>查到了省份代码，想查这只鸽子的成绩？</h3>
        <p>输入完整足环号，获取深度赛事报告</p>
        <a href="/" class="cta-btn">
            🔍 去查足环成绩 <i class="fas fa-arrow-right"></i>
        </a>
    </div>
</div>

<script>
function toggleMobileNav() {
    var nav = document.getElementById('mobileNav');
    nav.classList.toggle('active');
    document.body.style.overflow = nav.classList.contains('active') ? 'hidden' : '';
}
document.getElementById('mobileNav').addEventListener('click', function(e) {
    if (e.target === this) toggleMobileNav();
});
</script>
<?php include __DIR__ . '/../_footer.php'; ?>