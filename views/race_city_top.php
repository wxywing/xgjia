<?php
/**
 * A方案GEO SEO: 城市赛事深度分析页 /race/city/{city}/top/
 */
$cityName = htmlspecialchars($city ?? '');
$provinceName = htmlspecialchars($cityStats['province'] ?? '');
$page_title = $cityName . '赛鸽排行 | ' . $provinceName . $cityName . '信鸽赛事TOP榜单 - 信鸽之家';
$meta_description = $cityName . '信鸽赛事排行：分速前十赛鸽、鸽主排行榜、公棚赛绩榜。' . number_format($cityStats['race_count'] ?? 0) . '场比赛、' . number_format($cityStats['total_entries'] ?? 0) . '羽数据综合分析。';
$meta_keywords = $cityName . '赛鸽排行,' . $cityName . '信鸽排行,' . $cityName . '鸽主排名,' . $provinceName . $cityName . '赛事,' . $cityName . '公棚排行';
$canonical_url = 'https://www.xgjia.com/race/city/' . urlencode($city ?? '') . '/top/';
$og_image = 'https://www.xgjia.com/public/images/og-cover.png';

// JSON-LD ItemList (TOP榜单)
$ld_itemlist = [
    '@context' => 'https://schema.org',
    '@type' => 'ItemList',
    'name' => $cityName . '信鸽赛事排行榜',
    'description' => $meta_description,
    'url' => $canonical_url,
];
$itemElements = [];
// 分速TOP10
foreach (($topSpeedPigeons ?? []) as $i => $p) {
    $itemElements[] = [
        '@type' => 'ListItem',
        'position' => $i + 1,
        'name' => ($p['ring_number'] ?? '') . ' ' . ($p['owner_name'] ?? ''),
        'description' => '分速: ' . round($p['speed'] ?? 0, 1) . '米/分',
        'url' => '/race/' . intval($p['race_id'] ?? 0) . '.html',
    ];
}
if ($itemElements) {
    $ld_itemlist['itemListElement'] = $itemElements;
}

// FAQPage Schema (GEO SEO)
$faqs = [
    [
        'question' => '2026年' . $cityName . '有哪些信鸽公棚？',
        'answer' => $cityName . '现有' . intval($cityStats['loft_count'] ?? 0) . '家注册公棚，年均办赛' . intval($cityStats['race_count'] ?? 0) . '场，参赛羽数累计' . number_format($cityStats['total_entries'] ?? 0) . '羽。',
    ],
    [
        'question' => '如何查询' . $cityName . $provinceName . '赛鸽比赛成绩？',
        'answer' => '在信鸽之家' . $cityName . '赛事排行页输入赛鸽足环号，即可查看分速、排名及历次赛绩。',
    ],
    [
        'question' => $cityName . '赛鸽最高分速是多少？',
        'answer' => !empty($topSpeedPigeons) ? ($cityName . '2026年最高分速为' . round($topSpeedPigeons[0]['speed'] ?? 0, 1) . '米/分，由足环号' . ($topSpeedPigeons[0]['ring_number'] ?? '—') . '创下。') : ('暂无' . $cityName . '赛鸽分速数据。'),
    ],
    [
        'question' => $cityName . '哪些公棚赛绩最好？',
        'answer' => !empty($topLofts) ? ('根据入赏羽数统计，' . $cityName . 'TOP公棚榜前列包括' . ($topLofts[0]['loft_name'] ?? '—') . '等知名公棚，参赛羽数均超过' . intval($topLofts[0]['top100_count'] ?? 0) . '羽。') : ('暂无' . $cityName . '公棚赛绩数据。'),
    ],
    [
        'question' => '如何利用足环号追踪' . $cityName . '赛鸽？',
        'answer' => '在中国信鸽协会官网查询足环真伪，在信鸽之家输入完整足环号，可追踪该鸽历次比赛成绩及所在公棚。',
    ],
];
$ld_faqpage = [
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => [],
];
foreach ($faqs as $f) {
    $ld_faqpage['mainEntity'][] = [
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
    <title><?php echo $page_title; ?></title>
    <meta name="description" content="<?php echo $meta_description; ?>">
    <meta name="keywords" content="<?php echo $meta_keywords; ?>">
    <link rel="canonical" href="<?php echo $canonical_url; ?>">
    <meta property="og:title" content="<?php echo $page_title; ?>">
    <meta property="og:description" content="<?php echo $meta_description; ?>">
    <meta property="og:image" content="<?php echo $og_image; ?>">
    <meta property="og:type" content="website">
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/public/css/b-scheme.css">
    <link rel="shortcut icon" href="/public/images/favicon.ico">

    <!-- ItemList JSON-LD -->
    <script type="application/ld+json"><?php echo json_encode($ld_itemlist, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>

    <!-- BreadcrumbList -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "BreadcrumbList",
        "itemListElement": [
            {"@type": "ListItem", "position": 1, "name": "首页", "item": "https://www.xgjia.com"},
            {"@type": "ListItem", "position": 2, "name": "城市赛事中心", "item": "https://www.xgjia.com/race/city/"},
            {"@type": "ListItem", "position": 3, "name": "<?php echo $cityName; ?>赛事", "item": "https://www.xgjia.com/race/city/<?php echo urlencode($city ?? ''); ?>/"},
            {"@type": "ListItem", "position": 4, "name": "<?php echo $cityName; ?>TOP排行"}
        ]
    }
    </script>
<!-- FAQPage JSON-LD -->
    <script type="application/ld+json"><?php echo json_encode($ld_faqpage, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>
    <style>
        .city-top-hero {
            background: linear-gradient(135deg, #1a5fa8 0%, #0d3b6e 100%);
            color: #fff;
            padding: 36px 0;
        }
        .city-top-hero h1 {
            font-size: 26px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        .city-top-hero .subtitle {
            font-size: 14px;
            opacity: 0.85;
            margin-bottom: 16px;
        }
        .city-top-stats {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
        .city-top-stat {
            background: rgba(255,255,255,0.12);
            padding: 10px 18px;
            border-radius: 8px;
            font-size: 14px;
            flex: 1;
            min-width: 120px;
            text-align: center;
        }
        .city-top-stat strong {
            display: block;
            font-size: 22px;
            margin-bottom: 2px;
        }
        .page-wrap { background: #f4f6f9; padding-bottom: 50px; }
        .section-title {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 18px;
            font-weight: 700;
            color: #1a5fa8;
            margin: 28px 0 14px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e8ecf0;
        }
        .section-title .icon { font-size: 20px; }
        .rank-table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 1px 6px rgba(0,0,0,0.06);
        }
        .rank-table th {
            background: #1a5fa8;
            color: #fff;
            padding: 11px 14px;
            font-size: 13px;
            font-weight: 600;
            text-align: left;
        }
        .rank-table td {
            padding: 10px 14px;
            font-size: 13px;
            border-bottom: 1px solid #f0f0f0;
            color: #333;
        }
        .rank-table tr:last-child td { border-bottom: none; }
        .rank-table tr:hover td { background: #f8fbff; }
        .rank-table .rank-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            font-size: 12px;
            font-weight: 700;
            flex-shrink: 0;
        }
        .rank-table .rank-1 { background: #ffd700; color: #7a5f00; }
        .rank-table .rank-2 { background: #c0c0c0; color: #555; }
        .rank-table .rank-3 { background: #cd7f32; color: #fff; }
        .rank-table .rank-n { background: #e8ecf0; color: #666; }
        .rank-table .speed-col { color: #c9a84c; font-weight: 700; }
        .rank-table .owner-col { color: #1a5fa8; font-weight: 600; }
        .rank-table a { color: #1a5fa8; text-decoration: none; }
        .rank-table a:hover { text-decoration: underline; }
        .rank-table .tag {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 4px;
            font-size: 11px;
            margin-left: 4px;
        }
        .rank-table .tag-gold { background: #fff8e1; color: #c9a84c; }
        .rank-table .tag-blue { background: #e3f2fd; color: #1565c0; }
        .rank-table .tag-green { background: #e8f5e9; color: #2e7d32; }

        .city-nav-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px;
            margin-top: 12px;
        }
        .city-nav-card {
            background: #fff;
            border-radius: 8px;
            padding: 12px 14px;
            text-decoration: none;
            color: inherit;
            transition: all 0.2s;
            box-shadow: 0 1px 4px rgba(0,0,0,0.05);
            border-left: 3px solid #e8ecf0;
            display: block;
        }
        .city-nav-card:hover {
            border-left-color: #1a5fa8;
            transform: translateX(3px);
            box-shadow: 0 2px 10px rgba(26,95,168,0.12);
        }
        .city-nav-card .card-title {
            font-size: 13px;
            font-weight: 600;
            color: #1a5fa8;
            margin-bottom: 4px;
        }
        .city-nav-card .card-meta {
            font-size: 12px;
            color: #999;
        }
        .city-nav-card .card-meta strong { color: #333; }
        @media (max-width: 768px) {
            .rank-table { font-size: 12px; min-width: 600px; }
            .rank-table th, .rank-table td { padding: 8px 10px; }
            .rank-table th:nth-child(1), .rank-table td:nth-child(1) { width: 50px; min-width: 50px; }
            .rank-table th:nth-child(2), .rank-table td:nth-child(2) { max-width: 100px; }
            .rank-table th:nth-child(3), .rank-table td:nth-child(3) { max-width: 80px; }
            .rank-table th:nth-child(4), .rank-table td:nth-child(4) { max-width: 70px; white-space: nowrap; }
            .rank-table th:nth-child(5), .rank-table td:nth-child(5) { max-width: 120px; }
            .rank-table th:nth-child(6), .rank-table td:nth-child(6) { max-width: 100px; }
            .rank-table td a { display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
            .rank-table td:nth-child(1) a { display: inline; }
            .city-top-stat { min-width: 90px; padding: 8px 12px; }
            .city-top-stat strong { font-size: 18px; }
        }
        @media (max-width: 480px) {
            .rank-table { font-size: 11px; min-width: 550px; }
            .rank-table th, .rank-table td { padding: 6px 8px; }
            .rank-table .rank-badge { width: 22px; height: 22px; font-size: 11px; }
            .city-top-hero { padding: 20px 12px !important; }
            .city-top-hero h1 { font-size: 18px !important; }
            .city-top-stats { gap: 10px !important; }
            .city-top-stat { min-width: 70px; padding: 6px 10px; }
            .city-top-stat strong { font-size: 16px; }
        }
    </style>
</head>
<body>
<div class="page-wrap">
<?php include __DIR__ . '/_head.php'; ?>

<!-- Hero -->
<div class="city-top-hero">
    <div class="container">
        <div style="font-size:13px;opacity:0.8;margin-bottom:4px;">
            <a href="/race/city/" style="color:rgba(255,255,255,0.7);text-decoration:none;">城市赛事中心</a>
            › <a href="/race/city/<?php echo urlencode($city ?? ''); ?>/" style="color:rgba(255,255,255,0.7);text-decoration:none;"><?php echo $cityName; ?>赛事</a>
            › TOP排行
        </div>
        <h1>🏆 <?php echo $cityName; ?>赛鸽TOP排行榜</h1>
        <div class="subtitle">基于<?php echo number_format($cityStats['race_count'] ?? 0); ?>场比赛 · <?php echo number_format($cityStats['total_entries'] ?? 0); ?>羽赛鸽数据综合排名</div>
        <div class="city-top-stats">
            <div class="city-top-stat">
                <strong><?php echo number_format($cityStats['race_count'] ?? 0); ?></strong>
                场比赛
            </div>
            <div class="city-top-stat">
                <strong><?php echo number_format($cityStats['loft_count'] ?? 0); ?></strong>
                参赛公棚
            </div>
            <div class="city-top-stat">
                <strong><?php echo number_format($cityStats['total_entries'] ?? 0); ?></strong>
                羽参赛
            </div>
            <div class="city-top-stat">
                <strong><?php echo $provinceName; ?></strong>
                所属省份
            </div>
        </div>
    </div>
</div>

<div class="container" style="padding-top:8px;">

<!-- TOP10 分速榜 -->
<div class="section-title">
    <span class="icon">🚀</span>
    <?php echo $cityName; ?>分速TOP10
    <span style="font-size:12px;color:#999;font-weight:400;margin-left:8px;">——按单场最高分速排名</span>
</div>
<?php if (!empty($topSpeedPigeons)): ?>
<table class="rank-table">
    <thead>
        <tr>
            <th style="width:50px;">排名</th>
            <th>足环号</th>
            <th>鸽主</th>
            <th>分速 (米/分)</th>
            <th>赛事</th>
            <th>公棚</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($topSpeedPigeons as $i => $p): ?>
        <?php $rankClass = $i == 0 ? 'rank-1' : ($i == 1 ? 'rank-2' : ($i == 2 ? 'rank-3' : 'rank-n')); ?>
        <?php $tag = $i == 0 ? '<span class="tag tag-gold">🥇</span>' : ($i == 1 ? '<span class="tag tag-gold">🥈</span>' : ($i == 2 ? '<span class="tag tag-gold">🥉</span>' : '')); ?>
        <tr>
            <td><span class="rank-badge <?php echo $rankClass; ?>"><?php echo $i + 1; ?></span></td>
            <td>
                <a href="/race/ring/<?php echo urlencode($p['ring_number'] ?? ''); ?>/"><?php echo htmlspecialchars($p['ring_number'] ?? ''); ?></a>
                <?php echo $tag; ?>
            </td>
            <td class="owner-col"><?php echo htmlspecialchars($p['owner_name'] ?? ''); ?></td>
            <td class="speed-col"><?php echo round($p['speed'] ?? 0, 1); ?></td>
            <td><a href="/race/<?php echo intval($p['race_id'] ?? 0); ?>.html"><?php echo htmlspecialchars(mb_substr($p['race_name'] ?? '', 0, 16)); ?></a></td>
            <td><a href="/loft/<?php echo intval($p['loft_id'] ?? 0); ?>.html"><?php echo htmlspecialchars($p['loft_name'] ?? ''); ?></a></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php else: ?>
<div style="background:#fff;border-radius:10px;padding:40px;text-align:center;color:#999;">暂无分速数据</div>
<?php endif; ?>

<!-- TOP10 鸽主榜 -->
<div class="section-title">
    <span class="icon">👤</span>
    <?php echo $cityName; ?>鸽主TOP10
    <span style="font-size:12px;color:#999;font-weight:400;margin-left:8px;">——按入赏次数（前100名）排名</span>
</div>
<?php if (!empty($topOwners)): ?>
<table class="rank-table">
    <thead>
        <tr>
            <th style="width:50px;">排名</th>
            <th>鸽主</th>
            <th>入赏次数</th>
            <th>最佳分速</th>
            <th>平均分速</th>
            <th>参赛羽数</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($topOwners as $i => $o): ?>
        <?php $rankClass = $i == 0 ? 'rank-1' : ($i == 1 ? 'rank-2' : ($i == 2 ? 'rank-3' : 'rank-n')); ?>
        <?php $tag = $i < 3 ? '<span class="tag tag-blue">TOP3</span>' : ''; ?>
        <tr>
            <td><span class="rank-badge <?php echo $rankClass; ?>"><?php echo $i + 1; ?></span></td>
            <td class="owner-col"><?php echo htmlspecialchars($o['owner_name'] ?? ''); ?> <?php echo $tag; ?></td>
            <td><strong style="color:#1a5fa8;"><?php echo number_format($o['top100_count'] ?? 0); ?></strong> 次</td>
            <td class="speed-col"><?php echo round($o['best_speed'] ?? 0, 1); ?></td>
            <td><?php echo round($o['avg_speed'] ?? 0, 1); ?></td>
            <td><?php echo number_format($o['entry_count'] ?? 0); ?> 羽</td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php else: ?>
<div style="background:#fff;border-radius:10px;padding:40px;text-align:center;color:#999;">暂无鸽主数据</div>
<?php endif; ?>

<!-- TOP10 公棚榜 -->
<div class="section-title">
    <span class="icon">🏠</span>
    <?php echo $cityName; ?>公棚TOP10
    <span style="font-size:12px;color:#999;font-weight:400;margin-left:8px;">——按入赏羽数排名</span>
</div>
<?php if (!empty($topLofts)): ?>
<table class="rank-table">
    <thead>
        <tr>
            <th style="width:50px;">排名</th>
            <th>公棚</th>
            <th>赛事场次</th>
            <th>入赏羽数</th>
            <th>平均分速</th>
            <th>最高分速</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($topLofts as $i => $l): ?>
        <?php $rankClass = $i == 0 ? 'rank-1' : ($i == 1 ? 'rank-2' : ($i == 2 ? 'rank-3' : 'rank-n')); ?>
        <?php $tag = $i < 3 ? '<span class="tag tag-green">🏅</span>' : ''; ?>
        <tr>
            <td><span class="rank-badge <?php echo $rankClass; ?>"><?php echo $i + 1; ?></span></td>
            <td><a href="/loft/<?php echo intval($l['loft_id'] ?? 0); ?>.html"><?php echo htmlspecialchars($l['loft_name'] ?? ''); ?></a> <?php echo $tag; ?></td>
            <td><?php echo number_format($l['race_count'] ?? 0); ?> 场</td>
            <td><strong style="color:#1a5fa8;"><?php echo number_format($l['top100_count'] ?? 0); ?></strong> 羽</td>
            <td><?php echo round($l['avg_speed'] ?? 0, 1); ?></td>
            <td class="speed-col"><?php echo round($l['max_speed'] ?? 0, 1); ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php else: ?>
<div style="background:#fff;border-radius:10px;padding:40px;text-align:center;color:#999;">暂无公棚数据</div>
<?php endif; ?>

<!-- 其他城市快速导航 -->
<div class="section-title" style="margin-top:32px;">
    <span class="icon">🗺️</span>
    探索其他城市赛事
</div>
<div class="city-nav-cards">
    <?php foreach (array_slice($cities ?? [], 0, 12) as $c): ?>
    <?php if (($c['city'] ?? '') === $city) continue; ?>
    <a href="/race/city/<?php echo urlencode($c['city'] ?? ''); ?>/top/" class="city-nav-card">
        <div class="card-title">📍 <?php echo htmlspecialchars($c['city'] ?? ''); ?></div>
        <div class="card-meta">
            <strong><?php echo number_format($c['race_count'] ?? 0); ?></strong> 场 ·
            <strong><?php echo number_format($c['loft_count'] ?? 0); ?></strong> 公棚
        </div>
    </a>
    <?php endforeach; ?>
</div>

<!-- 底部导航 -->
<div style="display:flex;gap:12px;margin:24px 0 0;flex-wrap:wrap;">
    <a href="/race/city/<?php echo urlencode($city ?? ''); ?>/" style="flex:1;min-width:160px;background:#fff;border-radius:10px;padding:14px 16px;text-decoration:none;display:flex;align-items:center;gap:8px;box-shadow:0 1px 6px rgba(0,0,0,0.06);">
        <span style="font-size:20px;">📋</span>
        <div>
            <div style="font-size:14px;font-weight:600;color:#1a5fa8;"><?php echo $cityName; ?>全部赛事</div>
            <div style="font-size:12px;color:#999;">查看<?php echo $cityName; ?>所有<?php echo number_format($cityStats['race_count'] ?? 0); ?>场比赛</div>
        </div>
    </a>
    <a href="/race/" style="flex:1;min-width:160px;background:#fff;border-radius:10px;padding:14px 16px;text-decoration:none;display:flex;align-items:center;gap:8px;box-shadow:0 1px 6px rgba(0,0,0,0.06);">
        <span style="font-size:20px;">🔍</span>
        <div>
            <div style="font-size:14px;font-weight:600;color:#1a5fa8;">赛事成绩搜索</div>
            <div style="font-size:12px;color:#999;">按足环号/鸽主名查询</div>
        </div>
    </a>
    <a href="/race/province/<?php echo urlencode($provinceName); ?>/" style="flex:1;min-width:160px;background:#fff;border-radius:10px;padding:14px 16px;text-decoration:none;display:flex;align-items:center;gap:8px;box-shadow:0 1px 6px rgba(0,0,0,0.06);">
        <span style="font-size:20px;">📊</span>
        <div>
            <div style="font-size:14px;font-weight:600;color:#1a5fa8;"><?php echo $provinceName; ?>赛事汇总</div>
            <div style="font-size:12px;color:#999;">查看<?php echo $provinceName; ?>全境赛事</div>
        </div>
    </a>
</div>

</div>

<?php include __DIR__ . '/_footer.php'; ?>
</div>
</body>
</html>
