<?php
$page_title = '足环号追溯 | 赛事成绩 | 信鸽之家';
$page_desc = '根据足环号查询赛鸽在所有公棚的参赛成绩，跨公棚成绩追溯。';
$canonical_url = 'https://www.xgjia.com/race/search' . (!empty($ring) ? '?ring=' . urlencode($ring) : '');
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo $page_title; ?></title>
    <meta name="description" content="<?php echo $page_desc; ?>">
    <link rel="canonical" href="<?php echo h($canonical_url); ?>">
    <meta name="robots" content="noindex, follow">
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
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "WebSite",
      "name": "信鸽之家",
      "url": "https://www.xgjia.com",
      "potentialAction": {
        "@type": "SearchAction",
        "target": "https://www.xgjia.com/race/search?ring={ring}",
        "query-input": "required name=ring"
      }
    }
    </script>
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/public/css/b-scheme.css">
    <link rel="shortcut icon" href="/public/images/favicon.ico">
    <style>
.race-search-wrap { background: #f4f6f9; min-height: 80vh; }
.search-hero { background: linear-gradient(135deg, #1a5fa8, #0d3b6e); color: #fff; padding: 48px 0; text-align: center; }
.search-hero h1 { font-size: 28px; font-weight: 700; margin-bottom: 8px; }
.search-hero .subtitle { font-size: 15px; opacity: 0.85; margin-bottom: 24px; }
.search-form { display: flex; gap: 0; max-width: 520px; margin: 0 auto; }
.search-form input { flex: 1; padding: 14px 20px; border: 2px solid rgba(255,255,255,0.3); border-right: none; background: rgba(255,255,255,0.1); color: #fff; font-size: 16px; border-radius: 10px 0 0 10px; outline: none; }
.search-form input::placeholder { color: rgba(255,255,255,0.5); }
.search-form button { padding: 14px 28px; background: #c9a84c; color: #fff; border: none; font-size: 16px; font-weight: 600; border-radius: 0 10px 10px 0; cursor: pointer; }
.search-history { display: flex; flex-wrap: wrap; justify-content: center; gap: 8px; margin-top: 16px; }
.search-history .ring-pill { padding: 5px 14px; background: rgba(255,255,255,0.15); border-radius: 16px; color: rgba(255,255,255,0.7); font-size: 13px; text-decoration: none; }
.search-history .ring-pill:hover { background: rgba(255,255,255,0.25); }
.result-list { padding: 24px 0; }
.result-item { background: #fff; border-radius: 10px; padding: 16px 20px; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 1px 6px rgba(0,0,0,0.05); }
.result-item .rank { font-size: 24px; font-weight: 700; color: #1a5fa8; min-width: 50px; text-align: center; }
.result-item .rank.t1 { color: #c62828; }
.result-item .detail h4 { font-size: 15px; margin-bottom: 2px; }
.result-item .detail h4 a { color: #333; text-decoration: none; }
.result-item .detail h4 a:hover { color: #1a5fa8; }
.result-item .detail .extra { font-size: 12px; color: #888; }
.result-item .speed { font-size: 16px; font-weight: 700; color: #e65100; text-align: right; min-width: 100px; }
.result-item .speed small { font-size: 11px; color: #888; display: block; }
.empty-state { text-align: center; padding: 60px 20px; color: #999; }
.empty-state .icon { font-size: 48px; margin-bottom: 16px; }
@media (max-width: 768px) { .result-item { flex-direction: column; text-align: center; gap: 8px; } .result-item .speed { text-align: center; } }
    </style>
</head>
<body>
<div class="race-search-wrap">
<?php include __DIR__ . '/_head.php'; ?>

<div class="search-hero">
    <div class="container">
        <h1>🔍 足环号追溯</h1>
        <p class="subtitle">输入足环号，查询该赛鸽在所有公棚的参赛成绩</p>
        <form class="search-form" method="get" action="/race/search">
            <input type="text" name="ring" placeholder="例如：2024-01-1234567" value="<?php echo htmlspecialchars($ring ?? ''); ?>" required>
            <button type="submit"><i class="fas fa-search"></i> 查询</button>
        </form>
    </div>
</div>

<div class="container">
    <div class="result-list">
        <?php if (!empty($ring) && !empty($results)): ?>
            <?php foreach ($results as $r):
                $rank = intval($r['rank'] ?? 0);
                $rankCls = $rank <= 3 ? ' t1' : '';
                $speed = $r['speed'] ?? 0;
            ?>
            <div class="result-item">
                <div class="rank<?php echo $rankCls; ?>"><?php echo $rank <= 3 ? ['🥇','🥈','🥉'][$rank-1] : '#' . $rank; ?></div>
                <div class="detail">
                    <h4><a href="/race/<?php echo $r['race_id']; ?>.html"><?php echo htmlspecialchars($r['race_name']); ?></a></h4>
                    <div class="extra"><?php echo htmlspecialchars($r['loft_name']); ?> · <?php echo htmlspecialchars($r['province'] ?? ''); ?> · <?php echo htmlspecialchars($r['release_time'] ?? ''); ?></div>
                </div>
                <div class="speed">
                    <?php echo number_format($speed, 2); ?>
                    <small>m/min</small>
                </div>
            </div>
            <?php endforeach; ?>
        <?php elseif (!empty($ring)): ?>
            <div class="empty-state">
                <div class="icon">📭</div>
                <h3>未找到该足环号的参赛记录</h3>
                <p>可能尚未收录该赛鸽的成绩数据。</p>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="icon">🔍</div>
                <h3>输入足环号开始查询</h3>
                <p>支持格式：年份-地区-号码，如 2024-01-1234567</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/_footer.php'; ?>
</div>
</body>
</html>
