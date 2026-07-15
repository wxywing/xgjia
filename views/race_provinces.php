<?php
/**
 * 省份聚合首页 - Phase 2 SEO
 * URL: /race/province/
 * 
 * 展示所有有赛事数据的省份及其统计
 */
$page_title = '省份赛事聚合 | 赛事成绩';
$meta_description = '按省份查看信鸽赛事成绩聚合。各省级公棚赛事排名、参赛羽数、冠军榜。覆盖全国各省市信鸽竞赛数据。';
$meta_keywords = '信鸽赛事,省份赛事,公棚排名,赛鸽成绩,省级比赛';
$og_type = 'website';
$og_image = 'https://www.xgjia.com/public/images/og-cover.png';
$canonical_url = 'https://www.xgjia.com/race/province/';
$ld_json = [
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => '首页', 'item' => 'https://www.xgjia.com'],
                ['@type' => 'ListItem', 'position' => 2, 'name' => '赛事成绩', 'item' => 'https://www.xgjia.com/race/'],
                ['@type' => 'ListItem', 'position' => 3, 'name' => '省份聚合'],
            ],
        ],
        [
            '@type' => 'CollectionPage',
            'name' => '省份赛事聚合',
            'description' => $meta_description,
            'url' => $canonical_url,
            'isPartOf' => ['@type' => 'WebSite', 'name' => '信鸽之家', 'url' => 'https://www.xgjia.com'],
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
.province-wrap { background: #f4f6f9; min-height: 100vh; }
.province-header {
    background: linear-gradient(135deg, #1a5fa8 0%, #0d3b6e 100%);
    color: #fff; padding: 48px 0 36px; text-align: center;
}
.province-header h1 { font-size: 28px; font-weight: 700; margin-bottom: 8px; }
.province-header p { font-size: 15px; opacity: 0.85; }
.province-header .breadcrumb { font-size: 13px; opacity: 0.7; margin-bottom: 8px; }
.province-header .breadcrumb a { color: rgba(255,255,255,0.8); text-decoration: none; }
.province-header .breadcrumb a:hover { text-decoration: underline; }

.province-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; padding: 32px 0; }
.province-card {
    display: block; background: #fff; border-radius: 12px; padding: 20px;
    text-decoration: none; box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    transition: all 0.2s; border: 1px solid transparent;
}
.province-card:hover {
    box-shadow: 0 4px 16px rgba(26,95,168,0.15);
    border-color: #1a5fa8; transform: translateY(-2px);
}
.province-card .province-name { font-size: 18px; font-weight: 700; color: #1a5fa8; margin-bottom: 6px; }
.province-card .province-flag { font-size: 28px; float: right; }
.province-card .province-stats { font-size: 13px; color: #666; display: flex; gap: 12px; flex-wrap: wrap; }
.province-card .province-stats span { white-space: nowrap; }
.province-card .province-stats strong { color: #333; }

.province-index-footer { text-align: center; padding: 20px 0 40px; color: #999; font-size: 13px; }

@media (max-width: 768px) {
    .province-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
    .province-card { padding: 14px; }
    .province-card .province-name { font-size: 16px; }
}
@media (max-width: 480px) {
    .province-grid { grid-template-columns: 1fr; }
}
    </style>
</head>
<body>
<div class="province-wrap">
<?php include __DIR__ . '/_head.php'; ?>

<div class="province-header">
    <div class="container">
        <div class="breadcrumb">
            <a href="/">首页</a> › <a href="/race/">赛事成绩</a> › 省份聚合
        </div>
        <h1><i class="fas fa-map-marked-alt"></i> 省份赛事聚合</h1>
        <p>按省份查看各公棚赛事成绩，覆盖<?php echo count($provinces); ?>个省份</p>
    </div>
</div>

<div class="container">
    <?php if (!empty($provinces)): ?>
    <div class="province-grid">
        <?php foreach ($provinces as $p):
            $provinceName = htmlspecialchars($p['province']);
        ?>
        <a href="/race/province/<?php echo urlencode($p['province']); ?>/" class="province-card">
            <span class="province-flag">🏁</span>
            <div class="province-name"><?php echo $provinceName; ?></div>
            <div class="province-stats">
                <span><strong><?php echo $p['race_count']; ?></strong> 场赛事</span>
                <span><strong><?php echo $p['loft_count']; ?></strong> 个公棚</span>
                <?php if (!empty($p['total_entries'])): ?>
                <span><strong><?php echo number_format($p['total_entries']); ?></strong> 羽参赛</span>
                <?php endif; ?>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div style="text-align:center;padding:60px 0;color:#999;">暂无省份数据</div>
    <?php endif; ?>
</div>

<div class="province-index-footer">
    <p>数据持续更新中 · 来自信鸽之家赛事数据平台</p>
</div>

<?php include __DIR__ . '/_footer.php'; ?>
</div>
</body>
</html>
