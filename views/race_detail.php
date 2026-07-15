<?php
/**
 * 赛事分析页 - Phase 2 SEO 增强版
 * URL: /race/{id}.html
 * 
 * SEO 元素: SportsEvent JSON-LD, BreadcrumbList, TDK, og:image, canonical
 * 内容: 赛事信息 + 成绩表 + 同公棚其他赛事
 */
$race_name = htmlspecialchars($race['name'] ?? '');
$loft_name = htmlspecialchars($race['loft_name'] ?? '');
$seasonLabel = '';
if (!empty($race['season_year']) || !empty($race['season_type'])) {
    $seasonLabel = ($race['season_year'] ?? '') . '年' . ($race['season_type'] ?? '');
}
$entryCount = number_format($race['entry_count'] ?? 0);
$returnedCount = number_format($race['returned_count'] ?? 0);

// SEO 变量（供 _seo_head.php 使用）
$page_title = $race_name . ' - ' . $loft_name . ' | 赛事成绩';
$meta_description = $loft_name . $race_name . '成绩表，' . $entryCount . '羽参赛，空距' . ($race['distance_km'] ?? '—') . 'km。查名次、鸽主、分速。';
$meta_keywords = '赛鸽成绩,' . $race_name . ',' . $loft_name . ',信鸽比赛,公棚赛事,足环号查询';
$og_type = 'article';
$og_image = 'https://www.xgjia.com/public/images/og-cover.png';
$canonical_url = 'https://www.xgjia.com/race/' . $race['id'] . '.html';
$ld_json = [
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => '首页', 'item' => 'https://www.xgjia.com'],
                ['@type' => 'ListItem', 'position' => 2, 'name' => '赛事成绩', 'item' => 'https://www.xgjia.com/race/'],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $race['name'] ?? '赛事详情'],
            ],
        ],
        [
            '@type' => 'SportsEvent',
            'name' => $race_name . ' - ' . $loft_name,
            'sport' => '信鸽竞翔',
            'startDate' => $race['release_time'] ?? '',
            'location' => [
                '@type' => 'Place',
                'name' => $loft_name,
                'address' => [
                    '@type' => 'PostalAddress',
                    'addressLocality' => $race['province'] ?? '',
                ],
            ],
            'organizer' => [
                '@type' => 'Organization',
                'name' => $loft_name,
            ],
        ],
    ],
];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <?php include __DIR__ . '/_seo_head.php'; ?>
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/public/css/b-scheme.css">
    <link rel="shortcut icon" href="/public/images/favicon.ico">
    <style>
.race-detail-wrap { background: #f4f6f9; padding-bottom: 40px; }
.race-detail-top {
    background: linear-gradient(135deg, #1a5fa8 0%, #0d3b6e 100%);
    color: #fff; padding: 36px 0;
}
.race-detail-top h1 { font-size: 24px; font-weight: 700; margin-bottom: 4px; }
.race-detail-top .season-tag {
    display: inline-block; background: #c9a84c; color: #fff;
    font-size: 12px; padding: 2px 10px; border-radius: 10px;
    margin-left: 10px; vertical-align: middle; font-weight: 600;
}
.race-detail-top .breadcrumb { font-size: 13px; opacity: 0.8; margin-bottom: 4px; }
.race-detail-top .breadcrumb a { color: rgba(255,255,255,0.8); text-decoration: none; }
.race-detail-top .breadcrumb a:hover { text-decoration: underline; }
.race-info-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin: 20px 0 0; }
.race-info-item { background: rgba(255,255,255,0.1); border-radius: 8px; padding: 12px; text-align: center; }
.race-info-item .val { font-size: 18px; font-weight: 700; }
.race-info-item .lbl { font-size: 11px; opacity: 0.7; margin-top: 2px; }

.result-controls { display: flex; justify-content: space-between; align-items: center; margin: 20px 0 12px; flex-wrap: wrap; gap: 10px; }
.result-count { font-size: 14px; color: #666; }
.result-search { display: flex; gap: 0; }
.result-search input { padding: 8px 14px; border: 1px solid #ddd; border-right: none; border-radius: 6px 0 0 6px; width: 200px; font-size: 13px; }
.result-search button { padding: 8px 16px; background: #1a5fa8; color: #fff; border: none; border-radius: 0 6px 6px 0; cursor: pointer; }
.result-table-wrap { overflow-x: auto; background: #fff; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); }
.result-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.result-table th { background: #f8f9fa; color: #555; font-weight: 600; padding: 12px; text-align: left; border-bottom: 2px solid #e0e0e0; white-space: nowrap; position: sticky; top: 0; }
.result-table td { padding: 10px 12px; border-bottom: 1px solid #f0f0f0; }
.result-table tr:hover td { background: #f0f4ff; }
.result-table .rank-1 td { background: #fffde7; font-weight: 600; }
.result-table .rank-2 td { background: #fafafa; }
.result-table .rank-3 td { background: #fff8e1; }
.result-table .rank-badge { display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 50%; font-size: 12px; font-weight: 700; }
.rank-top { background: #c62828; color: #fff; }
.rank-top2 { background: #455a64; color: #fff; }
.rank-top3 { background: #e65100; color: #fff; }
.rank-top10 { background: #fff3e0; color: #e65100; }

.speed-high { color: #2e7d32; font-weight: 600; }
.pagination { display: flex; gap: 6px; justify-content: center; margin: 24px 0; }
.pagination .page-link { padding: 6px 14px; border: 1px solid #ddd; border-radius: 6px; color: #555; text-decoration: none; font-size: 13px; }
.pagination .page-link.active { background: #1a5fa8; color: #fff; border-color: #1a5fa8; }

.pigeon-link-btn { display: inline-flex; align-items: center; gap: 4px; padding: 5px 12px; background: var(--primary); color: #fff; border-radius: 5px; font-size: 12px; font-weight: 600; text-decoration: none; white-space: nowrap; transition: all 0.2s; }
.pigeon-link-btn:hover { background: #15508c; }
.pigeon-search-btn { display: inline-flex; align-items: center; gap: 4px; padding: 5px 12px; background: #f0f4ff; color: var(--primary); border: 1px solid var(--primary); border-radius: 5px; font-size: 12px; text-decoration: none; white-space: nowrap; transition: all 0.2s; }
.pigeon-search-btn:hover { background: #dce6f5; }

/* Other races section */
.other-races { margin-top: 32px; }
.other-races h3 { font-size: 18px; font-weight: 700; color: #1a5fa8; margin-bottom: 16px; padding-bottom: 8px; border-bottom: 2px solid #1a5fa8; }
.other-races-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
.other-race-card {
    display: block; padding: 14px 16px; background: #fff;
    border-radius: 8px; text-decoration: none;
    box-shadow: 0 1px 4px rgba(0,0,0,0.06);
    transition: all 0.2s;
}
.other-race-card:hover { box-shadow: 0 3px 12px rgba(26,95,168,0.15); transform: translateY(-1px); }
.other-race-card .race-name { display: block; font-size: 14px; color: #1a5fa8; font-weight: 600; margin-bottom: 4px; }
.other-race-card .race-meta { display: block; font-size: 12px; color: #888; }

#backToTop { position: fixed; bottom: 30px; right: 30px; width: 44px; height: 44px; border: none; border-radius: 50%; background: var(--primary); color: #fff; font-size: 18px; cursor: pointer; box-shadow: 0 4px 14px rgba(0,0,0,0.2); z-index: 999; display: none; align-items: center; justify-content: center; transition: all 0.3s; }
#backToTop:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(0,0,0,0.25); }

@media (max-width: 768px) {
    .race-info-grid { grid-template-columns: repeat(3, 1fr); }
    .other-races-grid { grid-template-columns: repeat(2, 1fr); }
    .ana-grid { grid-template-columns: 1fr; }
    .result-table { font-size: 12px; }
    .result-table th, .result-table td { padding: 8px 6px; }
}
@media (max-width: 480px) {
    .race-info-grid { grid-template-columns: repeat(2, 1fr); }
    .other-races-grid { grid-template-columns: 1fr; }
}
/* 分速分布 */
.speed-dist-section { background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); margin-top: 20px; }
.speed-dist-section .section-title { font-size: 18px; font-weight: 700; color: #1a5fa8; margin-bottom: 16px; }
.speed-dist-stats { display: flex; gap: 16px; flex-wrap: wrap; margin-bottom: 20px; }
.sd-stat { background: #f0f4ff; padding: 6px 14px; border-radius: 6px; font-size: 13px; color: #555; }
.sd-stat strong { color: #1a5fa8; margin-left: 4px; }
.sd-bar-row { display: flex; align-items: center; gap: 10px; margin-bottom: 8px; }
.sd-bar-label { width: 80px; font-size: 12px; color: #888; text-align: right; flex-shrink: 0; }
.sd-bar-track { flex: 1; background: #f0f0f0; border-radius: 4px; height: 20px; overflow: hidden; }
.sd-bar-fill { height: 100%; border-radius: 4px; transition: width 0.5s; }
.sd-bar-fill.sd-low { background: #ef5350; }
.sd-bar-fill.sd-mid { background: #ff9800; }
.sd-bar-fill.sd-good { background: #42a5f5; }
.sd-bar-fill.sd-fast { background: #66bb6a; }
.sd-bar-fill.sd-elite { background: #1a5fa8; }
.sd-bar-value { width: 120px; font-size: 12px; color: #666; flex-shrink: 0; }
/* 鸽主排行 */
.top-owners-section { background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); margin-top: 20px; }
.top-owners-section .section-title { font-size: 18px; font-weight: 700; color: #1a5fa8; margin-bottom: 16px; }
.top-owners-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; }
.owner-card { display: flex; align-items: center; gap: 12px; padding: 10px 14px; background: #f8f9fa; border-radius: 8px; text-decoration: none; transition: all 0.2s; }
.owner-card:hover { background: #e8f0fe; transform: translateX(4px); }
.owner-rank { width: 32px; height: 32px; border-radius: 50%; background: #e0e0e0; color: #555; font-size: 14px; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.owner-card:first-child .owner-rank { background: #ffd700; color: #8d6e00; }
.owner-card:nth-child(2) .owner-rank { background: #cfd8dc; color: #455a64; }
.owner-card:nth-child(3) .owner-rank { background: #ffcc80; color: #e65100; }
.owner-info { min-width: 0; }
.owner-name { font-size: 14px; font-weight: 600; color: #1a5fa8; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 4px; }
.owner-stats { font-size: 12px; color: #888; display: flex; flex-wrap: wrap; gap: 8px; }
.owner-podium { color: #e65100; font-weight: 600; }
@media (max-width: 768px) {
    .top-owners-grid { grid-template-columns: 1fr; }
}
    </style>
</head>
<body>
<div class="race-detail-wrap">
<?php include __DIR__ . '/_head.php'; ?>

<div class="race-detail-top">
    <div class="container">
        <div class="breadcrumb">
            <a href="/race/">赛事成绩</a> ›
            <a href="/loft/<?php echo $race['loft_id']; ?>.html"><?php echo $loft_name; ?></a> ›
            <?php echo $race_name; ?>
        </div>
        <h1>
            <?php echo $race_name; ?>
            <?php if ($seasonLabel): ?>
            <span class="season-tag"><?php echo htmlspecialchars($seasonLabel); ?></span>
            <?php endif; ?>
        </h1>
        <div class="race-info-grid">
            <div class="race-info-item">
                <div class="val"><?php echo $entryCount; ?></div>
                <div class="lbl">参赛羽数</div>
            </div>
            <div class="race-info-item">
                <div class="val"><?php echo $returnedCount; ?></div>
                <div class="lbl">归巢羽数</div>
            </div>
            <div class="race-info-item">
                <div class="val"><?php echo $race['return_rate'] ?? '—'; ?>%</div>
                <div class="lbl">归巢率</div>
            </div>
            <div class="race-info-item">
                <div class="val"><?php echo $race['distance_km'] ?? '—'; ?>km</div>
                <div class="lbl">空距</div>
            </div>
            <div class="race-info-item">
                <div class="val"><?php echo htmlspecialchars($race['release_location'] ?? '—'); ?></div>
                <div class="lbl">司放地点</div>
            </div>
            <div class="race-info-item">
                <div class="val"><?php echo htmlspecialchars($race['release_time'] ?? '—'); ?></div>
                <div class="lbl">放飞时间</div>
            </div>
            <div class="race-info-item">
                <div class="val"><?php echo htmlspecialchars($race['province'] ?? '—'); ?></div>
                <div class="lbl">所属省份</div>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($difficulty)): ?>
<?php
$lvls = ['EASY' => ['label' => '入门级', 'color' => '#66bb6a', 'bg' => '#e8f5e9', 'icon' => '🟢'],
        'MEDIUM' => ['label' => '进阶级', 'color' => '#ff9800', 'bg' => '#fff3e0', 'icon' => '🟡'],
        'HARD' => ['label' => '挑战级', 'color' => '#ef5350', 'bg' => '#ffebee', 'icon' => '🟠'],
        'EXPERT' => ['label' => '大师级', 'color' => '#7b1fa2', 'bg' => '#f3e5f5', 'icon' => '🔴']];
$lvl = $lvls[$difficulty['level']] ?? $lvls['MEDIUM'];
?>
<div class="container" style="padding: 20px 0 0;">
    <div style="background:<?php echo $lvl['bg']; ?>;border:1px solid <?php echo $lvl['color']; ?>20;border-radius:12px;padding:20px 24px;">
        <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
            <span style="font-size:36px;line-height:1;"><?php echo $lvl['icon']; ?></span>
            <div style="flex:1;min-width:200px;">
                <div style="font-size:13px;color:#888;margin-bottom:4px;">📊 赛事难度评级</div>
                <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                    <span style="display:inline-block;padding:4px 16px;background:<?php echo $lvl['color']; ?>;color:#fff;border-radius:20px;font-size:16px;font-weight:700;"><?php echo $lvl['label']; ?></span>
                    <span style="font-size:24px;font-weight:700;color:<?php echo $lvl['color']; ?>;"><?php echo $difficulty['score']; ?>/100</span>
                </div>
                <div style="display:flex;gap:16px;margin-top:10px;flex-wrap:wrap;font-size:12px;color:#666;">
                    <span>📏 距离: <strong style="color:#1a5fa8;"><?php echo $difficulty['factors']['distance']; ?></strong></span>
                    <span>🏠 归巢: <strong style="color:#1a5fa8;"><?php echo $difficulty['factors']['return_rate']; ?></strong></span>
                    <span>🐦 规模: <strong style="color:#1a5fa8;"><?php echo $difficulty['factors']['scale']; ?></strong></span>
                </div>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <div style="background:#fff;border-radius:8px;padding:8px 12px;text-align:center;min-width:60px;">
                    <div style="font-size:12px;color:#888;">距离</div>
                    <div style="font-size:18px;font-weight:700;color:#1a5fa8;"><?php echo $difficulty['dist_score']; ?></div>
                </div>
                <div style="background:#fff;border-radius:8px;padding:8px 12px;text-align:center;min-width:60px;">
                    <div style="font-size:12px;color:#888;">归巢</div>
                    <div style="font-size:18px;font-weight:700;color:#1a5fa8;"><?php echo $difficulty['ret_score']; ?></div>
                </div>
                <div style="background:#fff;border-radius:8px;padding:8px 12px;text-align:center;min-width:60px;">
                    <div style="font-size:12px;color:#888;">规模</div>
                    <div style="font-size:18px;font-weight:700;color:#1a5fa8;"><?php echo $difficulty['scale_score']; ?></div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
// P2: AI数据解读
$aiText = '';
$rmax = $speedDist['max_speed'] ?? 0;
$ravg = $speedDist['avg_speed'] ?? 0;
$rstd = $speedDist['stddev_speed'] ?? 0;
if ($race_name && $loft_name):
    $aiText = sprintf('「%s」由%s于%s在%s司放', $race_name, $loft_name,
        htmlspecialchars($race['release_time'] ?? '—'), htmlspecialchars($race['release_location'] ?? '未知'));
    if ($race['distance_km'] ?? 0) $aiText .= sprintf('，空距<strong>%d公里</strong>', $race['distance_km']);
    if ($race['entry_count'] ?? 0) $aiText .= sprintf('，共计<strong>%s羽</strong>赛鸽参赛', number_format($race['entry_count']));
    if (($race['returned_count'] ?? 0) > 0) $aiText .= sprintf('，<strong>%s羽</strong>归巢', number_format($race['returned_count']));
    if (($race['return_rate'] ?? 0) > 0) $aiText .= sprintf('（归巢率<strong>%.1f%%</strong>）', $race['return_rate']);
    $aiText .= '。';
    if ($rmax > 0 && $ravg > 0) $aiText .= sprintf('全场最高分速<strong>%.3f m/min</strong>，平均分速<strong>%.1f m/min</strong>', $rmax, $ravg);
    if ($rstd > 0) $aiText .= sprintf('，标准差<strong>%.1f</strong>说明%s', $rstd, $rstd > 200 ? '选手间实力差距明显，赛事竞争激烈' : '整体水平较为接近');
    $aiText .= '。';
    if (!empty($champion['ring_number'])) {
        $aiText .= sprintf('冠军由<a href="/page/owner/%s/" style="color:#1a5fa8;">%s</a>的<strong>%s</strong>夺得',
            urlencode($champion['owner_name'] ?? ''), htmlspecialchars($champion['owner_name'] ?? '—'), htmlspecialchars($champion['ring_number']));
        if (!empty($champion['region'])) $aiText .= sprintf('（%s）', htmlspecialchars($champion['region']));
        $aiText .= '。';
    }
endif;
?>
<?php if ($aiText): ?>
<div class="container" style="padding: 12px 0 0;">
    <div style="background:linear-gradient(135deg,#f8f9fa,#e8f0fe);border-radius:12px;padding:16px 24px;font-size:14px;color:#444;line-height:2.2;">
        <span style="font-size:12px;color:#1a5fa8;font-weight:600;background:#e8f0fe;padding:2px 10px;border-radius:10px;margin-right:8px;">📝 AI赛事解读</span>
        <p style="margin:10px 0 0;"><?php echo $aiText; ?></p>
    </div>
</div>
<?php endif; ?>

<div class="container" style="padding: 20px 0;">

    <?php if (!empty($champion)): ?>
    <?php
        $champRing = htmlspecialchars($champion['ring_number'] ?? '—');
        $champOwner = htmlspecialchars($champion['owner_name'] ?? '—');
        $champRegion = htmlspecialchars($champion['region'] ?? '—');
        $champColor = htmlspecialchars($champion['color'] ?? '—');
        $champArrival = htmlspecialchars($champion['arrival_time'] ?? '—');
        $champSpeed = isset($champion['speed']) ? number_format($champion['speed'], 3) : '—';
    ?>
    <div class="champion-showcase" style="background:linear-gradient(135deg,#fffde7,#fff8e1);border:2px solid #c9a84c;border-radius:12px;padding:20px 24px;display:flex;align-items:center;gap:20px;flex-wrap:wrap;">
        <div style="font-size:48px;flex-shrink:0;">🥇</div>
        <div style="flex:1;min-width:200px;">
            <div style="font-size:13px;color:#8d6e00;font-weight:600;margin-bottom:4px;">🏆 冠军鸽</div>
            <div style="font-size:18px;font-weight:700;color:#1a5fa8;margin-bottom:6px;"><?php echo $champRing; ?></div>
            <div style="font-size:13px;color:#666;line-height:1.8;">
                鸽主：<a href="/page/owner/<?php echo urlencode($champion['owner_name'] ?? ''); ?>/" style="color:#1a5fa8;font-weight:600;"><?php echo $champOwner; ?></a>
                &nbsp;|&nbsp; 地区：<?php echo $champRegion; ?>
                <?php if ($champColor && $champColor !== '—'): ?>&nbsp;|&nbsp; 羽色：<?php echo $champColor; ?><?php endif; ?>
            </div>
            <div style="font-size:13px;color:#666;">
                归巢：<?php echo $champArrival; ?>
                &nbsp;|&nbsp; <strong style="color:#e65100;font-size:15px;">分速 <?php echo $champSpeed; ?> m/min</strong>
            </div>
        </div>
        <?php if (!empty($champion['ring_number'])): ?>
        <a href="/race/ring/<?php echo urlencode($champion['ring_number']); ?>/" style="flex-shrink:0;display:inline-flex;align-items:center;gap:6px;padding:10px 20px;background:#1a5fa8;color:#fff;border-radius:8px;text-decoration:none;font-size:14px;font-weight:600;transition:all 0.2s;" onmouseover="this.style.background='#15508c'" onmouseout="this.style.background='#1a5fa8'">
            <i class="fas fa-chart-line"></i> 查看参赛记录
        </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="result-controls" style="margin-top:20px;">
        <div class="result-count">共 <strong><?php echo number_format($results['total'] ?? 0); ?></strong> 条成绩记录</div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <form class="result-search" method="get" action="/race/<?php echo $race['id']; ?>.html">
                <input type="text" name="q" placeholder="搜索鸽主/足环号..." value="<?php echo htmlspecialchars($keyword ?? ''); ?>">
                <button type="submit"><i class="fas fa-search"></i> 筛选</button>
            </form>
            <form class="result-search" method="get" action="/pigeon/">
                <input type="text" name="q" placeholder="查找铭鸽足环号..." value="">
                <button type="submit"><i class="fas fa-dove"></i> 找铭鸽</button>
            </form>
        </div>
    </div>

    <div class="result-table-wrap">
        <table class="result-table">
            <thead>
                <tr>
                    <th>名次</th>
                    <th>鸽主</th>
                    <th>地区</th>
                    <th>足环号</th>
                    <th>铭鸽</th>
                    <th>羽色</th>
                    <th>归巢时间</th>
                    <th>分速 (m/min)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($results['list'])): ?>
                <?php foreach ($results['list'] as $r):
                    $rank = intval($r['rank'] ?? 0);
                    $badgeCls = '';
                    if ($rank == 1) $badgeCls = 'rank-top';
                    elseif ($rank == 2) $badgeCls = 'rank-top2';
                    elseif ($rank == 3) $badgeCls = 'rank-top3';
                    elseif ($rank <= 10) $badgeCls = 'rank-top10';
                    $rowCls = $rank <= 3 ? ' rank-' . $rank : '';
                    $speedVal = $r['speed'] ?? 0;
                    $speedCls = $speedVal > 0 ? ($speedVal > 1200 ? ' speed-high' : '') : '';
                ?>
                <tr class="<?php echo $rowCls; ?>">
                    <td>
                        <?php if ($rank <= 3): ?>
                            <span class="rank-badge <?php echo $badgeCls; ?>"><?php echo $rank; ?></span>
                        <?php else: ?>
                            <?php echo $rank; ?>
                        <?php endif; ?>
                    </td>
                    <td><a href="/page/owner/<?php echo urlencode($r['owner_name'] ?? ''); ?>/" style="color:#1a5fa8;text-decoration:none;font-weight:500;" title="查看 <?php echo htmlspecialchars($r['owner_name'] ?? ''); ?> 全部赛绩"><?php echo htmlspecialchars($r['owner_name'] ?? '—'); ?></a></td>
                    <td><?php echo htmlspecialchars($r['region'] ?? '—'); ?></td>
                    <td>
                        <span id="ring-<?php echo intval($r['id'] ?? 0); ?>"><?php echo htmlspecialchars($r['ring_number'] ?? '—'); ?></span>
                        <button onclick="copyRing('<?php echo htmlspecialchars(json_encode($r['ring_number'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>')" style="margin-left:6px;padding:2px 8px;font-size:11px;background:#e8f4fd;color:#1a5fa8;border:1px solid #b8d4ef;border-radius:4px;cursor:pointer;" title="复制足环号">复制</button>
                    </td>
                    <td>
                        <?php if (!empty($r['pigeon_id'])): ?>
                        <a href="/pigeon/<?php echo intval($r['pigeon_id']); ?>.html" class="pigeon-link-btn">
                            <i class="fas fa-dove"></i> 查看铭鸽
                        </a>
                        <?php elseif (!empty($r['ring_number'])): ?>
                        <a href="/pigeon/?q=<?php echo urlencode($r['ring_number']); ?>" class="pigeon-search-btn">
                            <i class="fas fa-search"></i> 搜索
                        </a>
                        <?php else: ?>
                        <span style="color:#ccc;">—</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($r['color'] ?? '—'); ?></td>
                    <td><?php echo htmlspecialchars($r['arrival_time'] ?? '—'); ?></td>
                    <td class="<?php echo $speedCls; ?>"><?php echo number_format($speedVal, 3); ?></td>
                </tr>
                <?php endforeach; ?>
                <?php else: ?>
                <tr><td colspan="8" style="text-align:center;padding:40px;color:#999;">暂无成绩数据</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($results['total_pages'] > 1): ?>
    <?php echo renderPagination($results['page'], $results['total_pages']); ?>
    <?php endif; ?>
</div>

<?php $hasSpeed = !empty($speedDist) && ($speedDist['total'] ?? 0) > 0; ?>
<?php $hasTime = !empty($timeDist) && ($timeDist['total'] ?? 0) > 0; ?>
<?php if ($hasSpeed || $hasTime): ?>
<?php
$sd = $speedDist;
$totalWithSpeed = $sd['total'] ?? 1;
$bars = [
    ['label' => '&lt;800', 'count' => $sd['r_lt_800'] ?? 0, 'cls' => 'sd-low'],
    ['label' => '800-1000', 'count' => $sd['r_800_1000'] ?? 0, 'cls' => 'sd-mid'],
    ['label' => '1000-1200', 'count' => $sd['r_1000_1200'] ?? 0, 'cls' => 'sd-good'],
    ['label' => '1200-1400', 'count' => $sd['r_1200_1400'] ?? 0, 'cls' => 'sd-fast'],
    ['label' => '&gt;1400', 'count' => $sd['r_gt_1400'] ?? 0, 'cls' => 'sd-elite'],
];
$td = $timeDist;
$totalWithTime = $td['total'] ?? 1;
$timeBars = [
    ['label' => '&lt;1小时', 'count' => $td['lt_1h'] ?? 0, 'cls' => 'sd-fast'],
    ['label' => '1-2小时', 'count' => $td['h1_2'] ?? 0, 'cls' => 'sd-good'],
    ['label' => '2-4小时', 'count' => $td['h2_4'] ?? 0, 'cls' => 'sd-good'],
    ['label' => '4-6小时', 'count' => $td['h4_6'] ?? 0, 'cls' => 'sd-mid'],
    ['label' => '6-8小时', 'count' => $td['h6_8'] ?? 0, 'cls' => 'sd-mid'],
    ['label' => '&gt;8小时', 'count' => $td['gt_8h'] ?? 0, 'cls' => 'sd-low'],
];
?>
<div class="container" style="padding-bottom: 10px;">
    <div class="ana-grid" style="display:grid;grid-template-columns:repeat(2,1fr);gap:16px;">
        <?php if ($hasSpeed): ?>
        <div class="speed-dist-section" style="margin-top:0;">
            <h2 class="section-title"><i class="fas fa-tachometer-alt"></i> 分速分布</h2>
            <div class="speed-dist-stats">
                <span class="sd-stat">最高 <strong><?php echo number_format($sd['max_speed'] ?? 0); ?></strong> m/min</span>
                <span class="sd-stat">平均 <strong><?php echo number_format($sd['avg_speed'] ?? 0); ?></strong> m/min</span>
                <span class="sd-stat">最低 <strong><?php echo number_format($sd['min_speed'] ?? 0); ?></strong> m/min</span>
                <?php if (($sd['stddev_speed'] ?? 0) > 0): ?>
                <span class="sd-stat">标准差 <strong><?php echo number_format($sd['stddev_speed'], 1); ?></strong></span>
                <?php endif; ?>
            </div>
            <div class="speed-dist-bars">
                <?php foreach ($bars as $bar):
                    $pct = $totalWithSpeed > 0 ? round($bar['count'] / $totalWithSpeed * 100, 1) : 0;
                ?>
                <div class="sd-bar-row">
                    <span class="sd-bar-label"><?php echo $bar['label']; ?></span>
                    <div class="sd-bar-track">
                        <div class="sd-bar-fill <?php echo $bar['cls']; ?>" style="width:<?php echo max($pct, 2); ?>%"></div>
                    </div>
                    <span class="sd-bar-value"><?php echo number_format($bar['count']); ?> 羽 (<?php echo $pct; ?>%)</span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        <?php if ($hasTime): ?>
        <div class="speed-dist-section" style="margin-top:0;">
            <h2 class="section-title"><i class="fas fa-hourglass-half"></i> 归巢时间分布</h2>
            <div class="speed-dist-stats">
                <span class="sd-stat">首羽 <strong><?php echo htmlspecialchars($td['first_arrival'] ?? '—'); ?></strong></span>
                <span class="sd-stat">末羽 <strong><?php echo htmlspecialchars($td['last_arrival'] ?? '—'); ?></strong></span>
                <span class="sd-stat">总计 <strong><?php echo number_format($td['total'] ?? 0); ?></strong> 羽</span>
                <?php
                    $peakBar = null; $peakCnt = 0;
                    foreach ($timeBars as $tb) {
                        if ($tb['count'] > $peakCnt) { $peakCnt = $tb['count']; $peakBar = $tb; }
                    }
                    if ($peakBar && $peakBar['count'] > 0):
                ?>
                <span class="sd-stat">高峰 <strong><?php echo $peakBar['label']; ?></strong></span>
                <?php endif; ?>
            </div>
            <div class="speed-dist-bars">
                <?php foreach ($timeBars as $bar):
                    $pct = $totalWithTime > 0 ? round($bar['count'] / $totalWithTime * 100, 1) : 0;
                ?>
                <div class="sd-bar-row">
                    <span class="sd-bar-label"><?php echo $bar['label']; ?></span>
                    <div class="sd-bar-track">
                        <div class="sd-bar-fill <?php echo $bar['cls']; ?>" style="width:<?php echo max($pct, 2); ?>%"></div>
                    </div>
                    <span class="sd-bar-value"><?php echo number_format($bar['count']); ?> 羽 (<?php echo $pct; ?>%)</span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php if (($speedDist['total'] ?? 0) > 0): ?>
<?php
// P2: 分速分布文字解读
$spAnalysis = '';
$spPctFast = 0;
if (($speedDist['total'] ?? 0) > 0) {
    $spPctFast = round((($speedDist['r_1200_1400'] ?? 0) + ($speedDist['r_gt_1400'] ?? 0)) / $speedDist['total'] * 100, 1);
}
if (($speedDist['avg_speed'] ?? 0) > 0) {
    $spAnalysis .= sprintf('平均分速 <strong>%.1f m/min</strong>', $speedDist['avg_speed']);
    if ($spPctFast > 0) $spAnalysis .= sprintf('，其中<strong>%.1f%%</strong>的鸽子分速超过1200 m/min', $spPctFast);
    if (($speedDist['stddev_speed'] ?? 0) > 0) {
        $spAnalysis .= sprintf('，标准差 <strong>%.1f</strong>说明', $speedDist['stddev_speed']);
        $spAnalysis .= ($speedDist['stddev_speed'] > 200) ? '竞争分化明显，选手实力层次分明' : '整体水平集中，大部分选手分速相近';
    }
    $spAnalysis .= '。';
}
?>
<?php if ($spAnalysis): ?>
<div class="container" style="padding-bottom: 10px;">
    <div style="background:#f0f4ff;border-radius:10px;padding:14px 20px;font-size:13px;color:#555;line-height:2;">
        <span style="color:#1a5fa8;font-weight:600;">📈 数据分析</span> <?php echo $spAnalysis; ?>
    </div>
</div>
<?php endif; ?>
<?php endif; ?>

<?php if (!empty($topOwners)): ?>
<div class="container" style="padding-bottom: 10px;">
    <div class="top-owners-section">
        <h2 class="section-title"><i class="fas fa-medal"></i> 鸽主排行 TOP 10</h2>
        <div class="top-owners-grid">
            <?php foreach ($topOwners as $idx => $owner):
                $medal = '';
                if ($idx == 0) $medal = '🥇';
                elseif ($idx == 1) $medal = '🥈';
                elseif ($idx == 2) $medal = '🥉';
            ?>
            <a href="/page/owner/<?php echo urlencode($owner['owner_name']); ?>/" class="owner-card">
                <div class="owner-rank"><?php echo $medal ?: ($idx + 1); ?></div>
                <div class="owner-info">
                    <div class="owner-name"><?php echo htmlspecialchars($owner['owner_name']); ?></div>
                    <div class="owner-stats">
                        <span>参赛 <?php echo $owner['entry_count']; ?> 羽</span>
                        <?php if ($owner['podium_count'] > 0): ?>
                        <span class="owner-podium">🏆 ×<?php echo $owner['podium_count']; ?></span>
                        <?php endif; ?>
                        <?php if ($owner['top10_count'] > 0): ?>
                        <span>前10 ×<?php echo $owner['top10_count']; ?></span>
                        <?php endif; ?>
                        <span>最佳 <?php echo number_format($owner['best_speed'] ?? 0); ?> m/min</span>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($ownerOverlap)): ?>
<div class="container" style="padding-bottom: 10px;">
    <div class="speed-dist-section" style="margin-top:0;">
        <h2 class="section-title"><i class="fas fa-project-diagram"></i> 鸽主跨赛重叠度 TOP <?php echo count($ownerOverlap); ?></h2>
        <p style="font-size:13px;color:#888;margin-bottom:16px;">以下鸽主同时参加了同省（<?php echo htmlspecialchars($race['province'] ?? ''); ?>）的其他赛事</p>
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr style="border-bottom:2px solid #e0e0e0;">
                    <th style="padding:10px;text-align:left;color:#555;">鸽主</th>
                    <th style="padding:10px;text-align:right;color:#555;">同省参赛场次</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ownerOverlap as $ol): ?>
                <tr style="border-bottom:1px solid #f0f0f0;">
                    <td style="padding:10px;">
                        <a href="/page/owner/<?php echo urlencode($ol['owner_name']); ?>/" style="color:#1a5fa8;font-weight:600;text-decoration:none;"><?php echo htmlspecialchars($ol['owner_name']); ?> →</a>
                    </td>
                    <td style="padding:10px;text-align:right;font-weight:700;color:#c9a84c;"><?php echo $ol['other_race_count']; ?> 场</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($regionDist)): ?>
<?php $regionTotal = array_sum(array_column($regionDist, 'cnt')); ?>
<div class="container" style="padding-bottom: 10px;">
    <div class="speed-dist-section" style="margin-top:0;">
        <h2 class="section-title"><i class="fas fa-map-marked-alt"></i> 鸽主地区分布 TOP <?php echo count($regionDist); ?></h2>
        <div class="speed-dist-bars">
            <?php
            $regionColors = ['sd-elite', 'sd-fast', 'sd-good', 'sd-mid', 'sd-low'];
            foreach ($regionDist as $idx => $r):
                $pct = $regionTotal > 0 ? round($r['cnt'] / $regionTotal * 100, 1) : 0;
                $cls = $regionColors[min($idx, 4)];
            ?>
            <div class="sd-bar-row">
                <span class="sd-bar-label"><?php echo htmlspecialchars($r['region']); ?></span>
                <div class="sd-bar-track">
                    <div class="sd-bar-fill <?php echo $cls; ?>" style="width:<?php echo max($pct, 2); ?>%"></div>
                </div>
                <span class="sd-bar-value"><?php echo number_format($r['cnt']); ?> 羽 (<?php echo $pct; ?>%)</span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($otherRaces)): ?>
<div class="container" style="padding-bottom: 40px;">
    <div class="other-races">
        <h3><i class="fas fa-trophy"></i> 同公棚其他赛事</h3>
        <div class="other-races-grid">
            <?php foreach (array_slice($otherRaces, 0, 6) as $or): ?>
            <a href="/race/<?php echo $or['id']; ?>.html" class="other-race-card">
                <span class="race-name"><?php echo htmlspecialchars($or['name'] ?? '未命名赛事'); ?></span>
                <span class="race-meta">
                    <?php
                    $orSeason = '';
                    if (!empty($or['season_year'])) $orSeason .= $or['season_year'] . '年';
                    if (!empty($or['season_type'])) $orSeason .= $or['season_type'];
                    echo htmlspecialchars($orSeason ?: ($or['release_time'] ?? ''));
                    ?>
                    <?php if (!empty($or['distance_km'])): ?>
                     · <?php echo $or['distance_km']; ?>km
                    <?php endif; ?>
                    <?php if (!empty($or['entry_count'])): ?>
                     · <?php echo number_format($or['entry_count']); ?>羽
                    <?php endif; ?>
                </span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($loftHistory)): ?>
<div class="container" style="padding-bottom: 10px;">
    <div class="other-races" style="margin-top:0;">
        <h3><i class="fas fa-history"></i> 同公棚历史赛事对比</h3>
        <p style="font-size:13px;color:#888;margin-bottom:16px;">同一公棚往年同期(<?php echo htmlspecialchars($race['season_type'] ?? ''); ?>)赛事数据对比</p>
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:13px;background:#fff;">
                <thead>
                    <tr style="border-bottom:2px solid #e0e0e0;">
                        <th style="padding:12px;text-align:left;color:#555;">赛季</th>
                        <th style="padding:12px;text-align:left;color:#555;">赛事名称</th>
                        <th style="padding:12px;text-align:center;color:#555;">参赛/归巢</th>
                        <th style="padding:12px;text-align:center;color:#555;">距离</th>
                        <th style="padding:12px;text-align:center;color:#555;">最高分速</th>
                        <th style="padding:12px;text-align:center;color:#555;">平均分速</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($loftHistory as $hist): ?>
                    <tr style="border-bottom:1px solid #f0f0f0;">
                        <td style="padding:10px 12px;font-weight:700;color:#1a5fa8;">
                            <a href="/race/<?php echo $hist['id']; ?>.html" style="color:#1a5fa8;text-decoration:none;"><?php echo htmlspecialchars($hist['season_year'] ?? ''); ?>年</a>
                        </td>
                        <td style="padding:10px 12px;">
                            <a href="/race/<?php echo $hist['id']; ?>.html" style="color:#333;text-decoration:none;"><?php echo htmlspecialchars($hist['name'] ?? '—'); ?></a>
                        </td>
                        <td style="padding:10px 12px;text-align:center;">
                            <?php
                            $hEntry = $hist['entry_count'] ?? 0;
                            $hRet = $hist['returned_count'] ?? 0;
                            echo $hEntry ? number_format($hEntry) : '—';
                            if ($hRet) echo ' / ' . number_format($hRet);
                            ?>
                        </td>
                        <td style="padding:10px 12px;text-align:center;"><?php echo $hist['distance_km'] ?? '—'; ?>km</td>
                        <td style="padding:10px 12px;text-align:center;font-weight:600;color:#2e7d32;"><?php echo $hist['top_speed'] ? number_format($hist['top_speed'], 3) : '—'; ?></td>
                        <td style="padding:10px 12px;text-align:center;"><?php echo $hist['avg_speed'] ? number_format($hist['avg_speed'], 1) : '—'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($race['province']) || !empty($race['city'])): ?>
<div class="container" style="padding-bottom: 40px;">
    <div class="other-races">
        <h3><i class="fas fa-map-marker-alt"></i> 附近赛事</h3>
        <div class="other-races-grid">
            <?php if (!empty($race['province'])): ?>
            <a href="/race/province/<?php echo urlencode($race['province']); ?>/" class="other-race-card" style="border-left:3px solid #1a5fa8;">
                <span class="race-name" style="font-size:15px;">📊 <?php echo htmlspecialchars($race['province']); ?>赛事汇总</span>
                <span class="race-meta">查看<?php echo htmlspecialchars($race['province']); ?>所有公棚赛事成绩</span>
            </a>
            <?php endif; ?>
            <?php if (!empty($race['city'])): ?>
            <a href="/race/city/<?php echo urlencode($race['city']); ?>/" class="other-race-card" style="border-left:3px solid #c9a84c;">
                <span class="race-name" style="font-size:15px;">🏙️ <?php echo htmlspecialchars($race['city']); ?>赛事中心</span>
                <span class="race-meta">查看<?php echo htmlspecialchars($race['city']); ?>本地公棚赛事</span>
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/_footer.php'; ?>
</div>

<button id="backToTop" onclick="window.scrollTo({top:0,behavior:'smooth'})"><i class="fas fa-arrow-up"></i></button>
<div id="copy-toast" style="position:fixed;bottom:80px;left:50%;transform:translateX(-50%);background:#1a5fa8;color:#fff;padding:10px 24px;border-radius:20px;font-size:14px;display:none;z-index:9999;box-shadow:0 4px 12px rgba(26,95,168,0.3);">已复制足环号</div>
<script>
window.addEventListener('scroll',function(){document.getElementById('backToTop').style.display=window.scrollY>400?'flex':'none';});
function copyRing(ring){
  if(!ring) return;
  if(navigator.clipboard){
    navigator.clipboard.writeText(ring).then(function(){
      showToast('已复制: '+ring);
    });
  } else {
    var ta=document.createElement('textarea');ta.value=ring;document.body.appendChild(ta);ta.select();document.execCommand('copy');document.body.removeChild(ta);
    showToast('已复制: '+ring);
  }
}
function showToast(msg){
  var t=document.getElementById('copy-toast');t.textContent=msg;t.style.display='block';setTimeout(function(){t.style.display='none';},2000);
}
</script>
</body>
</html>
