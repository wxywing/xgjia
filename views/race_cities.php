<?php
/**
 * P1: 城市赛事中心 — 城市列表 /race/city/
 */
$page_title = '城市赛事中心 | 按城市查找公棚赛事 - 信鸽之家';
$meta_description = '按城市查找全国公棚赛事，查看各城市赛事数量、公棚数量和累计参赛羽数。';
$meta_keywords = '赛鸽城市,公棚城市,城市赛事,信鸽比赛城市';
$og_type = 'website';
$og_image = 'https://www.xgjia.com/public/images/og-cover.png';
$canonical_url = 'https://www.xgjia.com/race/city/';
$ld_json = [
    '@context' => 'https://schema.org',
    '@type' => 'CollectionPage',
    'name' => '城市赛事中心',
    'description' => $meta_description,
    'url' => $canonical_url,
    'isPartOf' => ['@type' => 'WebSite', 'name' => '信鸽之家', 'url' => 'https://www.xgjia.com'],
];

// FAQPage Schema (GEO SEO)
$total_cities = count($cities ?? []);
$total_races = array_sum(array_column($cities ?? [], 'race_count'));
$total_lofts = array_sum(array_column($cities ?? [], 'loft_count'));
$cities_faqs = [
    [
        'question' => '全国有哪些城市有赛鸽公棚？',
        'answer' => '信鸽之家收录了全国' . number_format($total_cities) . '个城市的公棚赛事数据，包括北京、天津、上海等主要赛鸽城市。',
    ],
    [
        'question' => '哪个城市的赛鸽公棚最多？',
        'answer' => !empty($cities) ? ('按公棚数量排名，' . ($cities[0]['city'] ?? '—') . '以' . number_format($cities[0]['loft_count'] ?? 0) . '个公棚位居前列。') : '各城市公棚数量可在城市详情页查看。',
    ],
    [
        'question' => '如何查看各城市的赛鸽比赛数据？',
        'answer' => '在城市赛事中心选择目标城市，可查看该城市的公棚数量、赛事场次及累计参赛羽数，点击城市卡片进入详情页查看完整数据。',
    ],
    [
        'question' => '全国每年举办多少场信鸽比赛？',
        'answer' => '根据信鸽之家数据统计，全国' . number_format($total_cities) . '个城市年均举办' . number_format($total_races) . '场公棚赛事，累计' . number_format($total_lofts) . '个注册公棚参与。',
    ],
    [
        'question' => '赛鸽热门城市有哪些特点？',
        'answer' => '赛鸽热门城市通常具备以下特点：公棚数量多、赛事规模大、参赛羽数高、分速水平高。可通过城市TOP排行页查看各城市赛鸽分速排名。',
    ],
];
$ld_cities_faqpage = [
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => [],
];
foreach ($cities_faqs as $f) {
    $ld_cities_faqpage['mainEntity'][] = [
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
<!-- FAQPage JSON-LD -->
    <script type="application/ld+json"><?php echo json_encode($ld_cities_faqpage, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/public/css/b-scheme.css">
    <link rel="shortcut icon" href="/public/images/favicon.ico">
    <style>
.city-hero { background: linear-gradient(135deg, #1a5fa8 0%, #0d3b6e 100%); color: #fff; padding: 36px 0; }
.city-hero h1 { font-size: 24px; font-weight: 700; }
.city-hero .subtitle { font-size: 14px; opacity: 0.85; margin-top: 6px; }
.city-wrap { background: #f4f6f9; padding-bottom: 40px; }
.city-list { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-top: 20px; }
.city-card { background: #fff; border-radius: 10px; padding: 18px 16px; text-decoration: none; box-shadow: 0 1px 6px rgba(0,0,0,0.06); transition: all 0.2s; }
.city-card:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(26,95,168,0.15); }
.city-name { font-size: 16px; font-weight: 700; color: #1a5fa8; }
.city-province { font-size: 12px; color: #999; margin-bottom: 8px; }
.city-stats { font-size: 13px; color: #666; }
.city-stats span { margin-right: 10px; }
.city-card { position: relative; }
.city-top-link { position: absolute; top: 12px; right: 12px; display: inline-flex; align-items: center; gap: 3px; background: linear-gradient(135deg, #c9a84c, #e0c060); color: #fff; font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 12px; text-decoration: none; transition: all 0.2s; }
.city-top-link:hover { background: linear-gradient(135deg, #b8923a, #c9a030); transform: scale(1.05); }
@media (max-width: 768px) { .city-list { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 480px) { .city-list { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<div class="city-wrap">
<?php include __DIR__ . '/_head.php'; ?>
<div class="city-hero">
    <div class="container">
        <h1><i class="fas fa-city"></i> 城市赛事中心</h1>
        <div class="subtitle">按城市浏览各地公棚赛事数据</div>
    </div>
</div>
<div class="container" style="padding: 20px 0;">
    <div style="background:#fff;border-radius:12px;padding:20px 24px;margin-bottom:20px;box-shadow:0 1px 6px rgba(0,0,0,0.06);">
        <p style="margin:0;font-size:15px;line-height:1.9;color:#444;text-indent:2em;">
            中国赛鸽运动历史悠久，近年公棚赛蓬勃发展，覆盖北京、上海、江苏、河南、山东、四川等全国主要省市。<strong style="color:#1a5fa8;">信鸽之家</strong>汇聚各城市公棚赛事数据，提供赛事查询、鸽主排名、足环追踪等工具。无论您是查询特定城市的赛鸽成绩，还是比较不同公棚的赛鸽分速，均可在此找到所需信息。
            <a href="/race/" style="color:#1a5fa8;text-decoration:none;font-weight:600;">查看全国赛事 ›</a>
        </p>
    </div>
    <div class="city-list">
        <?php foreach ($cities as $c): ?>
        <div class="city-card" style="cursor:pointer;" onclick="location.href='/race/city/<?php echo urlencode($c['city']); ?>/'">
            <a href="/race/city/<?php echo urlencode($c['city']); ?>/top/" class="city-top-link" onclick="event.stopPropagation()">🏆 TOP</a>
            <div class="city-name"><?php echo htmlspecialchars($c['city']); ?></div>
            <div class="city-province"><?php echo htmlspecialchars($c['province'] ?? ''); ?></div>
            <div class="city-stats">
                <span><i class="fas fa-flag-checkered"></i> <?php echo number_format($c['race_count']); ?> 场赛事</span>
                <span><i class="fas fa-home"></i> <?php echo number_format($c['loft_count']); ?> 个公棚</span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php include __DIR__ . '/_footer.php'; ?>
</div>
</body>
</html>
