<?php
/**
 * 信鸽之家 - 省级 GeoHub 聚合页
 * 变量：$province, $description, $stats, $cities, $top_lofts, $recent_races
 */
$page_title = htmlspecialchars($province) . '赛鸽 GeoHub | 信鸽之家';
$canonical = 'https://www.xgjia.com/geohub/province/' . urlencode($province) . '/';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <meta name="description" content="<?= htmlspecialchars($description) ?>">
    <link rel="canonical" href="<?= $canonical ?>">
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/public/css/b-scheme.css?v=2">
    <link rel="stylesheet" href="/public/css/geohub.css?v=1">
    <style>:root{--primary:#1a5fa8;--primary-dark:#154360;--primary-light:#e8f0f8;--accent:#c9a84c;--accent-light:#fef9e7;--white:#fff;--bg:#f4f6f9;--bg-dark:#1e2a3a;--text:#2c3e50;--text-light:#6c7a89;--text-muted:#95a5b8;--border:#e8ecf0;--shadow:0 2px 12px rgba(26,95,168,0.08);--shadow-hover:0 8px 30px rgba(26,95,168,0.12);--radius:12px;--radius-sm:8px}</style>
</head>
<body>
<?php require_once __DIR__ . '/_head.php'; ?>

<div class="geohub-hero">
    <div class="container">
        <h1><?= htmlspecialchars($province) ?>赛鸽 GeoHub</h1>
        <p class="geohub-description"><?= htmlspecialchars($description) ?></p>
        <div class="geohub-stats">
            <div class="stat-card">
                <div class="stat-number"><?= number_format($stats['race_count'] ?? 0) ?></div>
                <div class="stat-label">赛事场次</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= number_format($stats['loft_count'] ?? 0) ?></div>
                <div class="stat-label">公棚数量</div>
            </div>
        </div>
    </div>
</div>

<div class="container geohub-content">
    <div class="geohub-section">
        <h2><i class="fa fa-map-marker"></i> <?= htmlspecialchars($province) ?>主要城市</h2>
        <div class="city-grid">
            <?php foreach ($cities as $c): ?>
            <a href="/geohub/city/<?= urlencode($c['city']) ?>/" class="city-card">
                <div class="city-name"><?= htmlspecialchars($c['city']) ?></div>
                <div class="city-stats">
                    <span><?= $c['race_count'] ?> 场赛事</span>
                    <span><?= $c['loft_count'] ?> 家公棚</span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="geohub-section">
        <h2><i class="fa fa-trophy"></i> <?= htmlspecialchars($province) ?>TOP 公棚</h2>
        <div class="loft-grid">
            <?php foreach ($top_lofts as $loft): ?>
            <div class="loft-card">
                <h3><a href="/loft/<?= $loft['id'] ?>.html"><?= htmlspecialchars($loft['name']) ?></a></h3>
                <div class="loft-meta">
                    <span><i class="fa fa-calendar"></i> <?= $loft['race_count'] ?> 场赛事</span>
                    <span><i class="fa fa-map-marker"></i> <?= htmlspecialchars($loft['city']) ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="geohub-section">
        <h2><i class="fa fa-flag-checkered"></i> 近期赛事</h2>
        <div class="race-list">
            <?php foreach ($recent_races as $race): ?>
            <div class="race-item">
                <a href="/race/<?= $race['id'] ?>.html" class="race-name"><?= htmlspecialchars($race['name']) ?></a>
                <div class="race-meta">
                    <span><?= htmlspecialchars($race['loft_name']) ?></span>
                    <span><?= date('Y-m-d', strtotime($race['release_time'])) ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Place",
    "name": "<?= htmlspecialchars($province) ?>赛鸽",
    "description": "<?= htmlspecialchars($description) ?>",
    "geo": {
        "@type": "GeoCoordinates",
        "addressRegion": "<?= htmlspecialchars($province) ?>"
    },
    "hasMap": "<?= $canonical ?>"
}
</script>

<?php require_once __DIR__ . '/_footer.php'; ?>
</body>
</html>
