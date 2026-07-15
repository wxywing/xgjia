<?php
/**
 * 信鸽之家 - GeoHub 首页（全国省份枢纽总览）
 * 变量：$provinces (['province','cnt']), $page_title, $page_description, $page_keywords, $canonical, $breadcrumbs
 */
$page_title = $page_title ?: '全国赛鸽地理枢纽 | 信鸽之家 GeoHub';
$canonical = $canonical ?: 'https://www.xgjia.com/geohub/';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <meta name="description" content="<?= htmlspecialchars($page_description) ?>">
    <meta name="keywords" content="<?= htmlspecialchars($page_keywords) ?>">
    <link rel="canonical" href="<?= htmlspecialchars($canonical) ?>">
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/public/css/b-scheme.css?v=2">
    <link rel="stylesheet" href="/public/css/geohub.css?v=1">
    <style>:root{--primary:#1a5fa8;--primary-dark:#154360;--primary-light:#e8f0f8;--accent:#c9a84c;--accent-light:#fef9e7;--white:#fff;--bg:#f4f6f9;--bg-dark:#1e2a3a;--text:#2c3e50;--text-light:#6c7a89;--text-muted:#95a5b8;--border:#e8ecf0;--shadow:0 2px 12px rgba(26,95,168,0.08);--shadow-hover:0 8px 30px rgba(26,95,168,0.12);--radius:12px;--radius-sm:8px}</style>
</head>
<body>
<?php require_once __DIR__ . '/_head.php'; ?>

<div class="geohub-hero">
    <div class="container">
        <h1>全国赛鸽地理枢纽 · GeoHub</h1>
        <p class="geohub-description"><?= htmlspecialchars($page_description) ?></p>
        <div class="geohub-stats">
            <div class="stat-card">
                <div class="stat-number"><?= number_format(count($provinces)) ?></div>
                <div class="stat-label">省级枢纽</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= number_format(array_sum(array_column($provinces, 'cnt'))) ?></div>
                <div class="stat-label">覆盖公棚</div>
            </div>
        </div>
    </div>
</div>

<div class="container geohub-content">
    <div class="geohub-section">
        <h2><i class="fa fa-map-marker-alt"></i> 选择省份赛鸽枢纽</h2>
        <p class="geohub-description" style="margin-bottom:20px;">点击下方省份，查看该省的赛事成绩、公棚排名、鸽主榜单等聚合数据。</p>
        <div class="city-grid">
            <?php foreach ($provinces as $p): ?>
            <a class="city-card" href="/geohub/province/<?= urlencode($p['province']) ?>/">
                <span class="city-name"><?= htmlspecialchars($p['province']) ?></span>
                <span class="city-count"><?= number_format($p['cnt']) ?>家公棚</span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/_footer.php'; ?>
</body>
</html>
