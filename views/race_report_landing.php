<?php
/**
 * 足环号深度报告 - 落地页
 * 展示搜索框 + 示例环号快捷入口
 */

// 热门环号示例（从 race_results 取最新批次）
$hotRings = [
    '2025-03-1530783', '2025-07-0309339', '2025-19-1034068',
    '2025-03-2628797', '2025-03-1580792', '2025-22-1364627',
    '2025-05-1191186', '2025-06-0510273',
];

// 数据库总量
$totalRings = 12700000;
?><!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>赛鸽数据 - 一查便知 | 信鸽之家</title>
    <meta name="description" content="全国公棚真实赛事数据，输入足环号查看赛鸽完整赛绩档案。1270万+ 条赛绩，赛季统计、成绩排名、鸽主对比一查便知。">
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/public/css/b-scheme.css">
    <style>
.report-hero { background: linear-gradient(135deg, #0a1628 0%, #1a3a5c 50%, #1a5fa8 100%); padding: 48px 0 40px; text-align: center; color: #fff; }
.report-hero h1 { font-size: 26px; font-weight: 800; margin-bottom: 8px; }
.report-hero .hero-sub { font-size: 14px; color: rgba(255,255,255,0.7); margin-bottom: 24px; }
.report-hero .search-box { display: flex; max-width: 520px; margin: 0 auto 16px; background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 8px 32px rgba(0,0,0,0.3); }
.report-hero .search-box input { flex: 1; border: none; padding: 14px 18px; font-size: 15px; outline: none; color: #2c3e50; min-width: 0; }
.report-hero .search-box input::placeholder { color: #95a5b8; }
.report-hero .search-box button { border: none; background: var(--primary,#1a5fa8); color: #fff; padding: 0 24px; font-size: 15px; font-weight: 600; cursor: pointer; white-space: nowrap; transition: background .2s; }
.report-hero .search-box button:hover { background: #15508a; }
.report-hero .search-hint { font-size: 12px; color: rgba(255,255,255,0.5); }

.report-section { padding: 32px 0; }
.report-section h3 { font-size: 18px; font-weight: 700; margin-bottom: 16px; color: #2c3e50; }
.report-section h3 i { color: var(--primary,#1a5fa8); margin-right: 8px; }

/* 功能卡片 */
.feature-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 16px; }
.feature-card { background: #fff; border-radius: 10px; padding: 20px; box-shadow: 0 1px 6px rgba(26,95,168,0.06); }
.feature-card .fc-icon { font-size: 28px; margin-bottom: 10px; }
.feature-card .fc-title { font-size: 15px; font-weight: 700; margin-bottom: 4px; color: #2c3e50; }
.feature-card .fc-desc { font-size: 13px; color: #7f8c8d; line-height: 1.6; }

/* 快捷环号 */
.ring-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 10px; }
.ring-link { display: flex; align-items: center; gap: 6px; background: #f0f4f8; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 14px; text-decoration: none; color: #2c3e50; font-size: 13px; font-family: 'SF Mono','Menlo',monospace; transition: all .15s; }
.ring-link:hover { background: #e3edf7; border-color: var(--primary,#1a5fa8); color: var(--primary,#1a5fa8); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(26,95,168,0.1); }
.ring-link i { color: #95a5b8; font-size: 11px; }

/* 统计横条 */
.stats-strip { display: flex; gap: 24px; flex-wrap: wrap; padding: 20px 0; border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0; margin: 32px 0; }
.stat-item { text-align: center; flex: 1; min-width: 100px; }
.stat-item .num { font-size: 22px; font-weight: 800; color: var(--primary,#1a5fa8); }
.stat-item .label { font-size: 12px; color: #95a5b8; margin-top: 2px; }

@media (max-width: 768px) {
    .report-hero { padding: 32px 16px 28px; }
    .report-hero h1 { font-size: 22px; }
    .report-hero .search-box { max-width: 100%; }
    .feature-grid { grid-template-columns: 1fr 1fr; }
    .ring-grid { grid-template-columns: 1fr 1fr; }
    .stats-strip { gap: 12px; }
}
</style>
</head>
<body>
<?php include __DIR__ . '/_head.php'; ?>

<!-- Hero 搜索区 -->
<div class="report-hero">
    <h1><i class="fas fa-magnifying-glass-chart" style="margin-right:6px;"></i>赛鸽数据，一查便知</h1>
    <p class="hero-sub">全国公棚真实赛事数据，输入足环号查看完整赛绩档案</p>
    <form class="search-box" action="/race/report/" method="get" onsubmit="var v=this.querySelector('input').value.trim();if(!v){alert('请输入足环号');return false;}this.action='/race/report/'+encodeURIComponent(v)+'/';return true;">
        <input type="text" name="ring" placeholder="输入足环号，如 CHN2025-01-0702812" autocomplete="off" autofocus>
        <button type="submit"><i class="fas fa-search"></i> 查询</button>
    </form>
    <p class="search-hint">支持格式：CHN2025-01-0702812 / 2025-01-0702812 / 2025-03-1530783</p>
</div>

<!-- 统计横条 -->
<div class="container">
    <div class="stats-strip">
        <div class="stat-item">
            <div class="num">1,270万+</div>
            <div class="label">赛绩记录</div>
        </div>
        <div class="stat-item">
            <div class="num">7</div>
            <div class="label">分析维度</div>
        </div>
        <div class="stat-item">
            <div class="num">1444</div>
            <div class="label">收录赛事</div>
        </div>
    </div>
</div>

<!-- 功能亮点 -->
<div class="container">
    <!-- 快捷入口 -->
    <section class="report-section">
        <h3><i class="fas fa-bolt"></i>热门足环号速查</h3>
        <div class="ring-grid">
            <?php foreach ($hotRings as $ring): ?>
            <a href="/race/report/<?php echo urlencode($ring); ?>" class="ring-link">
                <i class="fas fa-chevron-right"></i> <?php echo h($ring); ?>
            </a>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="report-section">
        <h3><i class="fas fa-star"></i>报告包含哪些内容？</h3>
        <div class="feature-grid">
            <div class="feature-card">
                <div class="fc-icon">📊</div>
                <div class="fc-title">赛季总览</div>
                <div class="fc-desc">参赛次数、最好名次、最佳分速、平均速度，一图纵览赛鸽生涯</div>
            </div>
            <div class="feature-card">
                <div class="fc-icon">🏠</div>
                <div class="fc-title">公棚分布</div>
                <div class="fc-desc">在哪些公棚参赛过？各公棚成绩对比，了解鸽子适应能力</div>
            </div>
            <div class="feature-card">
                <div class="fc-icon">📈</div>
                <div class="fc-title">成绩趋势</div>
                <div class="fc-desc">全部排名记录 + 分速走势，判断鸽子状态是上升还是下滑</div>
            </div>
            <div class="feature-card">
                <div class="fc-icon">👥</div>
                <div class="fc-title">鸽主对比</div>
                <div class="fc-desc">同一鸽主的其他鸽子成绩如何？评估鸽主整体育种水平</div>
            </div>
            <div class="feature-card">
                <div class="fc-icon">🎯</div>
                <div class="fc-title">难度评级</div>
                <div class="fc-desc">参赛赛事难度分析：参赛羽数、冠军分速、归巢率综合评估</div>
            </div>
            <div class="feature-card">
                <div class="fc-icon">🏆</div>
                <div class="fc-title">获奖统计</div>
                <div class="fc-desc">入赏次数、前三甲次数、各赛季表现、春秋棚偏好</div>
            </div>
        </div>
    </section>

</div>

<?php include __DIR__ . '/_footer.php'; ?>
</body>
</html>
