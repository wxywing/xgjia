<?php
$page_title = '冠军榜 | 赛事成绩 | 信鸽之家';
$page_desc = '公棚赛事冠军鸽榜单，浏览各公棚决赛冠军成绩。';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo $page_title; ?></title>
    <meta name="description" content="<?php echo $page_desc; ?>">
    <link rel="canonical" href="https://www.xgjia.com/race/champions">
    <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?php echo $page_title; ?>">
    <meta property="og:description" content="<?php echo $page_desc; ?>">
    <meta property="og:url" content="https://www.xgjia.com/race/champions">
    <meta property="og:image" content="https://www.xgjia.com/public/images/og-cover.png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name" content="信鸽之家">
    <meta property="og:locale" content="zh_CN">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@xgjia">
    <meta name="twitter:title" content="<?php echo $page_title; ?>">
    <meta name="twitter:description" content="<?php echo $page_desc; ?>">
    <meta name="twitter:image" content="https://www.xgjia.com/public/images/og-cover.png">
    <?php if (!empty($champions)): ?>
    <?php
    $ld_items = [];
    foreach (array_slice($champions, 0, 10) as $i => $c) {
        $ld_items[] = [
            '@type' => 'ListItem',
            'position' => $i + 1,
            'item' => [
                '@type' => 'Thing',
                'name' => ($c['ring_number'] ?? '') . ' - ' . ($c['owner_name'] ?? ''),
                'url' => 'https://www.xgjia.com/race/' . $c['race_id'] . '.html'
            ]
        ];
    }
    ?>
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "ItemList",
      "name": "冠军榜",
      "description": "<?php echo $page_desc; ?>",
      "numberOfItems": <?php echo count($ld_items); ?>,
      "itemListElement": <?php echo json_encode($ld_items, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>
    }
    </script>
    <?php endif; ?>
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/public/css/b-scheme.css">
    <link rel="shortcut icon" href="/public/images/favicon.ico">
    <style>
.page-champions { background: #f4f6f9; min-height: 80vh; }
.champ-header { background: linear-gradient(135deg, #1a5fa8 0%, #0d3b6e 100%); color: #fff; padding: 40px 0; text-align: center; }
.champ-header h1 { font-size: 28px; font-weight: 700; margin-bottom: 6px; }
.champ-header .sub { font-size: 14px; opacity: 0.85; }
.champ-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; padding: 30px 0; }
.champ-card { background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,0.06); transition: transform .2s; }
.champ-card:hover { transform: translateY(-3px); }
.champ-card .header { padding: 16px 20px 10px; text-align: center; }
.champ-card .header .rank { font-size: 40px; }
.champ-card .header .ring { font-size: 18px; font-weight: 700; color: #333; margin: 4px 0; }
.champ-card .header .owner { font-size: 13px; color: #888; }
.champ-card .body { padding: 10px 20px 16px; border-top: 1px solid #f0f0f0; display: flex; justify-content: space-around; text-align: center; }
.champ-card .body .m { font-size: 18px; font-weight: 700; color: #1a5fa8; }
.champ-card .body .l { font-size: 11px; color: #888; }
.champ-card .footer { padding: 10px 20px 16px; background: #f8f9fa; text-align: center; }
.champ-card .footer a { color: #1a5fa8; text-decoration: none; font-size: 13px; font-weight: 600; }
.champ-card .footer a:hover { text-decoration: underline; }
@media (max-width: 768px) { .champ-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 480px) { .champ-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<div class="page-champions">
<?php include __DIR__ . '/_head.php'; ?>

<div class="champ-header">
    <div class="container">
        <h1>👑 冠军榜</h1>
        <p class="sub">各公棚决赛冠军鸽成绩一览</p>
    </div>
</div>

<div class="container">
    <div class="champ-grid">
        <?php if (!empty($champions)): ?>
        <?php foreach ($champions as $c):
            $speed = $c['speed'] ?? 0;
            $emoji = '🥇';
        ?>
        <div class="champ-card">
            <div class="header">
                <div class="rank"><?php echo $emoji; ?></div>
                <div class="ring"><?php echo htmlspecialchars($c['ring_number'] ?? '—'); ?></div>
                <div class="owner"><?php echo htmlspecialchars($c['owner_name'] ?? '—'); ?></div>
            </div>
            <div class="body">
                <div>
                    <div class="m"><?php echo number_format($speed ?? 0, 1); ?></div>
                    <div class="l">分速 (m/min)</div>
                </div>
                <div>
                    <div class="m"><?php echo htmlspecialchars($c['distance_km'] ?? '—'); ?></div>
                    <div class="l">空距 (km)</div>
                </div>
            </div>
            <div class="footer">
                <a href="/race/<?php echo $c['race_id']; ?>.html">
                    <?php echo htmlspecialchars($c['race_name'] ?? ''); ?>
                </a>
                <?php if (!empty($c['loft_name'])): ?>
                <span style="color:#ccc;margin:0 4px;">·</span>
                <a href="/loft/<?php echo $c['loft_id']; ?>.html">
                    🏠 <?php echo htmlspecialchars($c['loft_name']); ?>
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
        <?php else: ?>
        <div style="grid-column:1/-1;text-align:center;padding:60px 20px;color:#999;">
            <div style="font-size:48px;margin-bottom:16px;">🏆</div>
            <h3>暂无冠军数据</h3>
            <p>数据采集中，敬请期待...</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/_footer.php'; ?>
</div>
</body>
</html>
