<?php
/**
 * P2: 赛季总结 /race/season/{year}/
 */
$page_title = $year . '年赛季总结 | 赛事成绩 - 信鸽之家';
$meta_description = $year . '年信鸽公棚赛季总结：共' . number_format($summary['race_count'] ?? 0) . '场比赛，' . number_format($summary['loft_count'] ?? 0) . '个公棚，' . number_format($summary['total_entries'] ?? 0) . '羽参赛。最快分速' . number_format($summary['fastest_speed'] ?? 0) . '米/分。';
$meta_keywords = $year . '赛季,赛季总结,信鸽赛季,公棚赛季,赛事回顾';
$og_type = 'website';
$og_image = 'https://www.xgjia.com/public/images/og-cover.png';
$canonical_url = 'https://www.xgjia.com/race/season/' . $year . '/';
$ld_json = [
    '@context' => 'https://schema.org',
    '@type' => 'CollectionPage',
    'name' => $year . '年赛季总结',
    'description' => $meta_description,
    'url' => $canonical_url,
    'isPartOf' => ['@type' => 'WebSite', 'name' => '信鸽之家', 'url' => 'https://www.xgjia.com'],
];
// 赛季总结页 FAQ（GEO SEO）
$season_faqs = [
    [
        'question' => $year . '年中国信鸽公棚赛季整体情况如何？',
        'answer'   => $year . '年全国公棚赛共举办' . number_format($summary['race_count'] ?? 0) . '场，累计' . number_format($summary['total_entries'] ?? 0) . '羽信鸽参赛，覆盖' . number_format($summary['loft_count'] ?? 0) . '个注册公棚，最快分速达' . number_format($summary['fastest_speed'] ?? 0) . '米/分。',
    ],
    [
        'question' => '如何查看' . $year . '年各省赛鸽分速排名？',
        'answer'   => '在信鸽之家城市赛事中心选择目标城市，点击"TOP排行"可查看该城市' . $year . '年赛鸽分速排名，包括冠军鸽主、鸽舍及分速数据。',
    ],
    [
        'question' => $year . '年哪个公棚赛鸽分速最高？',
        'answer'   => '根据信鸽之家收录数据，' . $year . '年最快分速达' . number_format($summary['fastest_speed'] ?? 0) . '米/分。各公棚具体分速数据可在赛事详情页查看。',
    ],
    [
        'question' => '如何查询特定足环号在' . $year . '年的赛绩？',
        'answer'   => '在信鸽之家首页搜索框输入完整足环号，即可追踪该赛鸽在' . $year . '年的所有赛绩记录，包括分速、名次及所在公棚信息。',
    ],
    [
        'question' => '信鸽之家赛季数据包含哪些内容？',
        'answer'   => '信鸽之家赛季总结页收录全年各省市公棚赛事数据，包括参赛羽数、公棚数量、最快分速、省份分布等，并支持按年份、城市、公棚名称多维筛选查询。',
    ],
];
$ld_season_faqpage = [
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => [],
];
foreach ($season_faqs as $f) {
    $ld_season_faqpage['mainEntity'][] = [
        '@type' => 'Question',
        'name' => $f['question'],
        'acceptedAnswer' => [
            '@type' => 'Answer',
            'text' => $f['answer'],
        ],
    ];
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <?php include __DIR__ . '/_seo_head.php'; ?>
    <script type="application/ld+json"><?php echo json_encode($ld_season_faqpage, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/public/css/b-scheme.css">
    <link rel="shortcut icon" href="/public/images/favicon.ico">
    <style>
.season-hero { background: linear-gradient(135deg, #1a5fa8 0%, #0d3b6e 100%); color: #fff; padding: 40px 0; text-align: center; }
.season-hero h1 { font-size: 28px; font-weight: 700; }
.season-hero .subtitle { font-size: 14px; opacity: 0.85; margin-top: 8px; }
.season-switcher { display: flex; gap: 10px; justify-content: center; margin: 16px 0 0; flex-wrap: wrap; }
.season-switcher a { padding: 6px 18px; border-radius: 20px; border: 1px solid rgba(255,255,255,0.4); color: #fff; text-decoration: none; font-size: 13px; transition: all 0.2s; }
.season-switcher a:hover, .season-switcher a.active { background: rgba(255,255,255,0.2); border-color: #fff; }
.season-wrap { background: #f4f6f9; padding-bottom: 40px; }
.stats-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin: 20px 0; }
.stat-card { background: #fff; border-radius: 10px; padding: 16px; text-align: center; box-shadow: 0 1px 6px rgba(0,0,0,0.06); }
.stat-card .value { font-size: 26px; font-weight: 700; color: #1a5fa8; }
.stat-card .value.highlight { color: #c9a84c; }
.stat-card .label { font-size: 12px; color: #999; margin-top: 4px; }
.section-title { font-size: 18px; font-weight: 700; color: #333; margin: 24px 0 14px; padding-left: 12px; border-left: 3px solid #1a5fa8; }
.bar-chart { background: #fff; border-radius: 10px; padding: 20px; box-shadow: 0 1px 6px rgba(0,0,0,0.06); }
.bar-row { display: flex; align-items: center; gap: 12px; margin-bottom: 10px; }
.bar-label { width: 60px; font-size: 12px; color: #666; font-weight: 600; text-align: right; flex-shrink: 0; }
.bar-track { flex: 1; height: 24px; background: #f0f0f0; border-radius: 6px; overflow: hidden; }
.bar-fill { height: 100%; background: linear-gradient(90deg, #1a5fa8, #2980b9); border-radius: 6px; display: flex; align-items: center; padding-left: 8px; font-size: 11px; color: #fff; font-weight: 600; min-width: 40px; transition: width 0.5s ease; }
.bar-count { width: 60px; font-size: 12px; color: #666; flex-shrink: 0; }
.top-table { background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 1px 6px rgba(0,0,0,0.06); }
.top-table table { width: 100%; border-collapse: collapse; font-size: 13px; }
.top-table th { background: #f8f9fa; padding: 10px 14px; text-align: left; font-weight: 600; color: #555; border-bottom: 2px solid #e0e0e0; font-size: 12px; }
.top-table td { padding: 10px 14px; border-bottom: 1px solid #f0f0f0; }
.top-table tr:hover td { background: #f0f4ff; }
.top-table .rank-badge { display: inline-flex; align-items: center; justify-content: center; width: 24px; height: 24px; border-radius: 50%; font-size: 11px; font-weight: 700; }
.top-table .rank-badge.gold { background: #fff3cd; color: #c9a84c; }
.top-table .rank-badge.silver { background: #f0f0f0; color: #888; }
.top-table .rank-badge.bronze { background: #fdf0e0; color: #d4843a; }
.speed-value { color: #2e7d32; font-weight: 600; }
.loft-link, .ring-link { color: #1a5fa8; text-decoration: none; font-weight: 600; }
.loft-link:hover, .ring-link:hover { text-decoration: underline; }
@media (max-width: 768px) {
    .stats-row { grid-template-columns: repeat(2, 1fr); }
    .season-hero h1 { font-size: 22px; }
}
@media (max-width: 480px) {
    .stats-row { grid-template-columns: 1fr 1fr; gap: 8px; }
    .stat-card { padding: 12px; }
    .stat-card .value { font-size: 20px; }
}
    </style>
</head>
<body>
<div class="season-wrap">
<?php include __DIR__ . '/_head.php'; ?>

<div class="season-hero">
    <div class="container">
        <h1><i class="fas fa-chart-bar" style="color:#c9a84c;"></i> <?php echo $year; ?> 赛季总结</h1>
        <div class="subtitle">全国公棚赛事年度数据回顾</div>
        <div class="season-switcher">
            <?php foreach (array_unique(array_column($seasons, 'season_year')) as $sy): ?>
            <a href="/race/season/<?php echo $sy; ?>/" class="<?php echo $sy == $year ? 'active' : ''; ?>">
                <?php echo $sy; ?>赛季
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="container" style="padding: 20px 0;">

    <!-- 核心统计卡 -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="value"><?php echo number_format($summary['race_count'] ?? 0); ?></div>
            <div class="label">比赛场次</div>
        </div>
        <div class="stat-card">
            <div class="value"><?php echo number_format($summary['loft_count'] ?? 0); ?></div>
            <div class="label">参赛公棚</div>
        </div>
        <div class="stat-card">
            <div class="value highlight"><?php echo !empty($summary['total_entries']) ? number_format($summary['total_entries']) : '—'; ?></div>
            <div class="label">参赛羽数</div>
        </div>
        <div class="stat-card">
            <div class="value"><?php echo !empty($summary['fastest_speed']) ? number_format($summary['fastest_speed']) : '—'; ?></div>
            <div class="label">最快分速 (米/分)</div>
        </div>
    </div>

    <?php if (!empty($monthly)): ?>
    <!-- 月度分布柱状图 -->
    <h2 class="section-title"><i class="fas fa-calendar-alt"></i> 月度赛事分布</h2>
    <div class="bar-chart">
        <?php
        $maxCount = max(array_column($monthly, 'race_count'));
        foreach ($monthly as $m):
            $pct = $maxCount > 0 ? round(($m['race_count'] / $maxCount) * 100) : 0;
            $monthName = intval($m['month']) . '月';
        ?>
        <div class="bar-row">
            <span class="bar-label"><?php echo $monthName; ?></span>
            <div class="bar-track">
                <div class="bar-fill" style="width: <?php echo $pct; ?>%;"><?php echo $pct > 15 ? $m['race_count'] . '场' : ''; ?></div>
            </div>
            <span class="bar-count"><?php echo number_format($m['race_count']); ?> 场</span>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- 最佳分速 TOP10 -->
    <?php if (!empty($topFastest)): ?>
    <h2 class="section-title"><i class="fas fa-rocket"></i> 最佳分速 TOP10</h2>
    <div class="top-table">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>足环号</th>
                    <th>鸽主</th>
                    <th>赛事 / 公棚</th>
                    <th>分速</th>
                    <th>名次</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($topFastest as $i => $p): ?>
                <?php $rankClass = $i === 0 ? 'gold' : ($i === 1 ? 'silver' : ($i === 2 ? 'bronze' : '')); ?>
                <tr>
                    <td><span class="rank-badge <?php echo $rankClass; ?>"><?php echo $i + 1; ?></span></td>
                    <td>
                        <?php if (!empty($p['ring_number'])): ?>
                        <a href="/race/ring/<?php echo urlencode($p['ring_number']); ?>" class="ring-link"><?php echo htmlspecialchars($p['ring_number']); ?></a>
                        <?php else: ?>—<?php endif; ?>
                    </td>
                    <td>
                        <a href="/page/owner/<?php echo urlencode($p['owner_name'] ?? ''); ?>/" style="color:#1a5fa8;text-decoration:none;"><?php echo htmlspecialchars($p['owner_name'] ?? '—'); ?></a>
                    </td>
                    <td>
                        <a href="/race/<?php echo intval($p['race_id'] ?? 0); ?>.html" style="color:#333;text-decoration:none;"><?php echo htmlspecialchars($p['race_name'] ?? '—'); ?></a>
                        <span style="font-size:11px;color:#999;display:block;"><?php echo htmlspecialchars($p['loft_name'] ?? ''); ?></span>
                    </td>
                    <td class="speed-value"><?php echo number_format($p['speed'] ?? 0); ?></td>
                    <td><?php echo intval($p['rank'] ?? 0); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <!-- 最多赛事公棚 TOP10 -->
    <?php if (!empty($topLofts)): ?>
    <h2 class="section-title"><i class="fas fa-trophy"></i> 最活跃公棚 TOP10</h2>
    <div class="top-table">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>公棚</th>
                    <th>地区</th>
                    <th>赛事数</th>
                    <th>参赛羽数</th>
                    <th>成绩条数</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($topLofts as $i => $l): ?>
                <?php $rankClass = $i === 0 ? 'gold' : ($i === 1 ? 'silver' : ($i === 2 ? 'bronze' : '')); ?>
                <tr>
                    <td><span class="rank-badge <?php echo $rankClass; ?>"><?php echo $i + 1; ?></span></td>
                    <td><a href="/loft/<?php echo intval($l['loft_id'] ?? 0); ?>.html" class="loft-link"><?php echo htmlspecialchars($l['name'] ?? '—'); ?></a></td>
                    <td style="color:#999;font-size:12px;"><?php echo htmlspecialchars($l['province'] ?? '' . $l['city'] ?? ''); ?></td>
                    <td><?php echo number_format($l['race_count'] ?? 0); ?> 场</td>
                    <td><?php echo !empty($l['total_entries']) ? number_format($l['total_entries']) : '—'; ?> 羽</td>
                    <td><?php echo number_format($l['total_results'] ?? 0); ?> 条</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <!-- P1 内链导航 -->
    <div class="section">
        <h2 class="section-title"><i class="fas fa-compass"></i> 探索更多</h2>
        <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px;">
            <a href="/race/province/" style="display:block; padding:12px 16px; background:#f0f4fa; border-radius:8px; text-decoration:none; color:#1a5fa8; font-weight:600;">
                📊 按省份查看 →
            </a>
            <a href="/race/city/" style="display:block; padding:12px 16px; background:#f0f4fa; border-radius:8px; text-decoration:none; color:#1a5fa8; font-weight:600;">
                🏙️ 按城市查看 →
            </a>
            <a href="/race/champions/" style="display:block; padding:12px 16px; background:#fcf8f0; border-radius:8px; text-decoration:none; color:#c9a84c; font-weight:600;">
                👑 冠军鸽荣誉榜 →
            </a>
            <a href="/race/" style="display:block; padding:12px 16px; background:#f0f4fa; border-radius:8px; text-decoration:none; color:#1a5fa8; font-weight:600;">
                🔍 搜索赛事成绩 →
            </a>
        </div>
    </div>

</div>

<?php include __DIR__ . '/_footer.php'; ?>
</div>
</body>
</html>
