<?php
/**
 * 信鸽之家 - 2026春赛分速TOP100（数据工具）
 */
require_once dirname(__DIR__, 2) . '/app/config/config.php';

extract($data);

$page_title = $pageTitle ?? '2026春赛TOP100 | 信鸽赛事数据';
$page_desc  = $pageDesc  ?? '2026年春季赛鸽分速TOP100排名，数据来自1270万+条赛事记录。';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo h($page_title); ?></title>
    <meta name="description" content="<?php echo h($page_desc); ?>">
    <meta name="keywords" content="赛鸽分速,TOP100,春赛排名,信鸽排行榜,分速排名,信鸽之家">
    <meta property="og:title" content="<?php echo h($page_title); ?>">
    <meta property="og:description" content="<?php echo h($page_desc); ?>">
    <meta property="og:image" content="https://www.xgjia.com/public/images/og-cover.png">
    <link rel="canonical" href="https://www.xgjia.com/tools/top100/">
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/public/css/b-scheme.css">
    <link rel="shortcut icon" href="/public/images/favicon.ico">
    <?php if (!empty($rankings)):
    $_items = [];
    foreach (array_slice($rankings, 0, 50) as $idx => $r) {
        $_items[] = ['@type' => 'ListItem', 'position' => $idx + 1, 'name' => ($r['owner_name'] ?? '') . ' - ' . ($r['ring_number'] ?? '')];
    }
    $_ld_il = ['@context' => 'https://schema.org', '@type' => 'ItemList', 'numberOfItems' => count($_items), 'itemListElement' => $_items];
    ?>
    <script type="application/ld+json"><?php echo json_encode($_ld_il, JSON_UNESCAPED_SLASHES); ?></script>
    <?php endif; ?>
</head>
<body>
<?php include __DIR__ . '/../_head.php'; ?>
<!-- ===== TOP100 排行榜 ===== -->
<style>
.top100-header {
    background: linear-gradient(135deg, var(--primary) 0%, #0d3b6e 100%);
    color: white; padding: 50px 20px 36px; text-align: center;
}
.top100-header h1 { font-size: 32px; font-weight: 800; margin-bottom: 8px; }
.top100-header p  { font-size: 15px; opacity: 0.85; max-width: 600px; margin: 0 auto; line-height: 1.6; }
.top100-header .header-badge {
    display: inline-block; background: rgba(255,255,255,0.15);
    padding: 5px 14px; border-radius: 20px; font-size: 13px; margin-bottom: 12px;
}
.top100-container { max-width: 1100px; margin: 0 auto; padding: 32px 20px 80px; }
.stats-bar {
    display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 16px; margin-bottom: 28px;
}
.stat-card { background: #fff; border-radius: 12px; padding: 20px; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
.stat-card .stat-value { font-size: 24px; font-weight: 800; color: var(--primary); }
.stat-card .stat-label { font-size: 12px; color: var(--text-light); margin-top: 4px; }
.ranking-table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,0.06); }
.ranking-table thead { background: var(--primary); color: white; }
.ranking-table th { padding: 12px 14px; font-size: 13px; font-weight: 700; text-align: left; white-space: nowrap; }
.ranking-table td { padding: 10px 14px; font-size: 13px; border-bottom: 1px solid var(--border); vertical-align: middle; }
.ranking-table tbody tr:hover { background: #f0f6ff; }
.ranking-table tbody tr:nth-child(even) { background: #fafbfc; }
.ranking-table tbody tr:nth-child(even):hover { background: #f0f6ff; }
.rank-num { font-weight: 800; font-size: 16px; text-align: center; width: 50px; }
.rank-1 .rank-num, .rank-2 .rank-num, .rank-3 .rank-num { font-size: 20px; }
.rank-1 { background: #fffdf0 !important; }
.rank-2 { background: #f8f9fd !important; }
.rank-3 { background: #fdf5f0 !important; }
.speed-cell { font-weight: 700; color: var(--accent); white-space: nowrap; font-size: 14px; }
.owner-link { color: var(--primary); text-decoration: none; font-weight: 600; }
.owner-link:hover { text-decoration: underline; }
.ring-link { font-family: 'Courier New', monospace; font-size: 12px; color: var(--text-light); text-decoration: none; }
.ring-link:hover { color: var(--primary); text-decoration: underline; }
.loft-cell { max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.meta-info { display: flex; gap: 24px; flex-wrap: wrap; margin-bottom: 24px; font-size: 13px; color: var(--text-light); }
.meta-info span { display: flex; align-items: center; gap: 4px; }
.empty-state { text-align: center; padding: 60px 20px; color: var(--text-light); }
.empty-state .empty-icon { font-size: 48px; margin-bottom: 16px; }
.cta-section { text-align: center; margin-top: 48px; padding: 40px; background: linear-gradient(135deg, #f8fafc 0%, #eef2f7 100%); border-radius: 12px; }
.cta-section h3 { font-size: 18px; font-weight: 700; margin-bottom: 8px; }
.cta-section p  { font-size: 14px; color: var(--text-light); margin-bottom: 20px; }
.cta-btn { display: inline-flex; align-items: center; gap: 8px; background: var(--accent); color: white; padding: 12px 32px; border-radius: 28px; font-size: 15px; font-weight: 700; text-decoration: none; transition: all 0.2s; }
.cta-btn:hover { background: #b89237; transform: translateY(-1px); }
@media (max-width: 768px) {
    .top100-header { padding: 36px 16px; }
    .top100-header h1 { font-size: 24px; }
    .ranking-table { font-size: 12px; }
    .ranking-table th, .ranking-table td { padding: 8px 6px; }
    .loft-cell { max-width: 100px; }
    .hide-mobile { display: none; }
}
</style>

<div class="top100-header">
    <span class="header-badge">🏆 数据排行</span>
    <h1>2026 年春赛分速 TOP100</h1>
    <p>数据来源：信鸽之家赛事数据库（1270万+条记录）。按分速(m/min)降序排列，仅含2026年赛事。</p>
</div>

<div class="top100-container">

    <?php if (!empty($rankings)): ?>
    <div class="stats-bar">
        <div class="stat-card">
            <div class="stat-value"><?php echo number_format(count($rankings)); ?></div>
            <div class="stat-label">TOP 赛鸽</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo number_format($stats['total_owners'] ?? 0); ?></div>
            <div class="stat-label">参赛鸽主</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo number_format($stats['total_lofts'] ?? 0); ?></div>
            <div class="stat-label">参赛公棚</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo number_format($stats['max_speed'] ?? 0, 0); ?></div>
            <div class="stat-label">最高分速 (m/min)</div>
        </div>
    </div>

    <div class="meta-info">
        <span><i class="fas fa-clock"></i> 更新时间：<?php echo h($stats['updated_at'] ?? date('Y-m-d H:i')); ?></span>
        <span><i class="fas fa-database"></i> 数据总量：<?php echo number_format($stats['total_birds'] ?? 0); ?> 羽</span>
        <span><i class="fas fa-filter"></i> 筛选条件：2026年 分速>0</span>
    </div>

    <table class="ranking-table">
        <thead>
            <tr>
                <th>#</th><th>足环号</th><th>鸽主</th><th>公棚/赛事</th>
                <th>分速(m/min)</th><th class="hide-mobile">空距(km)</th>
                <th class="hide-mobile">名次</th><th class="hide-mobile">日期</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($rankings as $i => $r): 
            $rank = $i + 1;
            $rowClass = $rank <= 3 ? 'rank-' . $rank : '';
        ?>
            <tr class="<?php echo $rowClass; ?>">
                <td class="rank-num"><?php echo $rank; ?></td>
                <td><a href="/race/ring/<?php echo urlencode($r['ring_number'] ?? ''); ?>/" class="ring-link"><?php echo h($r['ring_number'] ?? ''); ?></a></td>
                <td>
                    <?php if (!empty($r['owner_name'])): ?>
                    <a href="/page/owner/<?php echo urlencode($r['owner_name']); ?>/" class="owner-link"><?php echo h($r['owner_name']); ?></a>
                    <?php else: ?><span style="color:var(--text-light);">—</span><?php endif; ?>
                </td>
                <td class="loft-cell" title="<?php echo h($r['loft_name'] ?? ''); ?>"><?php echo h($r['loft_name'] ?? $r['race_name'] ?? '—'); ?></td>
                <td class="speed-cell"><?php echo number_format($r['speed'] ?? 0, 2); ?></td>
                <td class="hide-mobile"><?php echo !empty($r['distance_km']) ? number_format($r['distance_km'], 0) : '—'; ?></td>
                <td class="hide-mobile"><?php echo !empty($r['rank']) ? '#' . intval($r['rank']) : '—'; ?></td>
                <td class="hide-mobile" style="white-space:nowrap;"><?php echo !empty($r['release_time']) ? date('m-d', strtotime($r['release_time'])) : '—'; ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <?php else: ?>
    <div class="empty-state">
        <div class="empty-icon">📊</div>
        <p style="font-size:16px;font-weight:600;color:var(--text);">暂无排名数据</p>
        <p style="font-size:13px;">2026 年春赛数据正在陆续更新中，请稍后查看。</p>
    </div>
    <?php endif; ?>

    <div class="cta-section">
        <h3>想查任意一只鸽子的详细成绩？</h3>
        <p>输入足环号，获取深度赛事报告</p>
        <a href="/" class="cta-btn">🔍 去查足环成绩 <i class="fas fa-arrow-right"></i></a>
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