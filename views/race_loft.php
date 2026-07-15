<?php
$loft_name = htmlspecialchars($loftInfo['name'] ?? '');
$page_title = $loft_name . ' · 赛事列表 | 信鸽之家';
$page_desc = $loft_name . '历年赛事成绩汇总，公棚赛绩查询。';
$canonical_url = 'https://www.xgjia.com/race/loft/' . ($loftInfo['id'] ?? '');
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo $page_title; ?></title>
    <meta name="description" content="<?php echo $page_desc; ?>">
    <link rel="canonical" href="<?php echo h($canonical_url); ?>">
    <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?php echo $page_title; ?>">
    <meta property="og:description" content="<?php echo $page_desc; ?>">
    <meta property="og:url" content="<?php echo h($canonical_url); ?>">
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
    <?php if (!empty($races)): ?>
    <?php
    $ld_items = [];
    foreach (array_slice($races, 0, 10) as $i => $r) {
        $ld_items[] = [
            '@type' => 'ListItem',
            'position' => $i + 1,
            'item' => [
                '@type' => 'SportsEvent',
                'name' => $r['name'] ?? '',
                'url' => 'https://www.xgjia.com/race/' . ($r['id'] ?? '') . '.html',
                'startDate' => $r['release_time'] ?? ''
            ]
        ];
    }
    ?>
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "ItemList",
      "name": "<?php echo $loft_name; ?> · 赛事列表",
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
.race-loft-wrap { background: #f4f6f9; }
.loft-race-header { background: linear-gradient(135deg, #1a5fa8, #0d3b6e); color: #fff; padding: 36px 0; }
.loft-race-header .breadcrumb { font-size: 13px; opacity: 0.8; margin-bottom: 8px; }
.loft-race-header .breadcrumb a { color: rgba(255,255,255,0.8); text-decoration: none; }
.loft-race-header h1 { font-size: 24px; font-weight: 700; }
.loft-race-header .desc { font-size: 14px; opacity: 0.85; margin-top: 6px; }
.race-list { display: flex; flex-direction: column; gap: 12px; padding: 24px 0; }
.race-item { background: #fff; border-radius: 10px; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 1px 6px rgba(0,0,0,0.05); transition: transform .15s; }
.race-item:hover { transform: translateX(4px); }
.race-item .info h3 { font-size: 16px; margin-bottom: 4px; }
.race-item .info h3 a { color: #1a5fa8; text-decoration: none; }
.race-item .info h3 a:hover { text-decoration: underline; }
.race-item .info .meta { font-size: 12px; color: #888; }
.race-item .stats { display: flex; gap: 20px; text-align: center; flex-shrink: 0; }
.race-item .stats .s { font-size: 18px; font-weight: 700; color: #1a5fa8; }
.race-item .stats .l { font-size: 11px; color: #888; }
.badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 11px; font-weight: 600; margin-right: 6px; }
.badge-spring { background: #e8f5e9; color: #2e7d32; }
.badge-autumn { background: #fff3e0; color: #e65100; }
@media (max-width: 768px) {
    .race-item { flex-direction: column; align-items: flex-start; gap: 12px; }
    .race-item .stats { width: 100%; justify-content: space-around; }
}
    </style>
</head>
<body>
<div class="race-loft-wrap">
<?php include __DIR__ . '/_head.php'; ?>

<div class="loft-race-header">
    <div class="container">
        <div class="breadcrumb">
            <a href="/">首页</a> › <a href="/race/">赛事成绩</a> › <a href="/loft/<?php echo $loftInfo['id']; ?>.html"><?php echo $loft_name; ?></a>
        </div>
        <h1>🏁 <?php echo $loft_name; ?> · 赛事成绩</h1>
        <div class="desc">共 <?php echo count($races); ?> 个赛季</div>
    </div>
</div>

<div class="container">
    <div class="race-list">
        <?php if (!empty($races)): ?>
        <?php foreach ($races as $r): ?>
        <div class="race-item">
            <div class="info">
                <h3>
                    <span class="badge <?php echo $r['season_type'] == 'spring' ? 'badge-spring' : 'badge-autumn'; ?>"><?php echo $r['season_type'] == 'spring' ? '春' : '秋'; ?></span>
                    <a href="/race/<?php echo $r['id']; ?>.html"><?php echo htmlspecialchars($r['name']); ?></a>
                </h3>
                <div class="meta">📅 <?php echo htmlspecialchars($r['release_time'] ?? '—'); ?></div>
            </div>
            <div class="stats">
                <div><div class="s"><?php echo number_format($r['entry_count'] ?? 0); ?></div><div class="l">参赛</div></div>
                <div><div class="s"><?php echo number_format($r['returned_count'] ?? 0); ?></div><div class="l">归巢</div></div>
                <div><div class="s"><?php echo $r['return_rate'] ?? '—'; ?>%</div><div class="l">归巢率</div></div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php else: ?>
        <div style="text-align:center;padding:40px;color:#999;">暂无赛事数据</div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/_footer.php'; ?>
</div>
</body>
</html>
