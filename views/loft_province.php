<?php
/**
 * 信鸽之家 - 省份公棚目录页 /loft/province/{province}/
 * P0: SEO Geo — 按省浏览公棚，含统计数据 + 城市列表 + 公棚分页
 */
require_once dirname(__DIR__) . '/app/config/config.php';

extract($data);

$page_title = $province . '公棚大全 | ' . $province . '公棚排名_信鸽之家';
$canonical_url = 'https://www.xgjia.com/loft/province/' . urlencode($province) . '/';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo h($page_title); ?></title>
    <meta name="description" content="<?php echo h($province . '信鸽公棚大全：' . number_format($stats['loft_count'] ?? 0) . '个公棚，覆盖' . number_format($stats['city_count'] ?? 0) . '个城市，' . number_format($stats['race_count'] ?? 0) . '场赛事，' . number_format($stats['total_entries'] ?? 0) . '羽参赛。查看' . $province . '公棚排名、规程、联系方式。'); ?>">
    <meta name="keywords" content="<?php echo h($province . '公棚,' . $province . '信鸽公棚,' . $province . '公棚大全,' . $province . '公棚排名'); ?>">
    <meta property="og:title" content="<?php echo h($page_title); ?>">
    <meta property="og:description" content="<?php echo h($province . '信鸽公棚大全：' . number_format($stats['loft_count'] ?? 0) . '个公棚，覆盖' . number_format($stats['city_count'] ?? 0) . '个城市。'); ?>">
    <meta property="og:image" content="https://www.xgjia.com/public/images/og-cover.png">
    <link rel="canonical" href="<?php echo h($canonical_url); ?>">
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/public/css/b-scheme.css">
    <link rel="shortcut icon" href="/public/images/favicon.ico">

    <!-- BreadcrumbList -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "BreadcrumbList",
        "itemListElement": [
            {"@type": "ListItem", "position": 1, "name": "首页", "item": "https://www.xgjia.com"},
            {"@type": "ListItem", "position": 2, "name": "公棚大全", "item": "https://www.xgjia.com/loft/"},
            {"@type": "ListItem", "position": 3, "name": "<?php echo h($province); ?>公棚"}
        ]
    }
    </script>
</head>
<body>
<?php include __DIR__ . '/_head.php'; ?>

<div class="page-articles">

<div class="hero" style="background:linear-gradient(135deg, #1a5fa8 0%, #0d3b6e 100%); border-bottom:none;">
    <div class="hero-inner">
        <div class="breadcrumb" style="font-size:13px; opacity:0.8; margin-bottom:6px;">
            <a href="/" style="color:rgba(255,255,255,0.8);">首页</a> ›
            <a href="/loft/" style="color:rgba(255,255,255,0.8);">公棚大全</a> ›
            <span style="color:#fff;"><?php echo h($province); ?>公棚</span>
        </div>
        <h1 style="color:#fff; font-size:28px; font-weight:700;">
            <i class="fas fa-map-marker-alt" style="color:#c9a84c;"></i> <?php echo h($province); ?>公棚
        </h1>
        <?php if (!empty($stats)): ?>
        <div style="display:flex; gap:20px; margin-top:12px; flex-wrap:wrap;">
            <div style="background:rgba(255,255,255,0.15); padding:8px 16px; border-radius:8px; color:#fff; font-size:14px;">
                <strong style="font-size:20px;"><?php echo number_format($stats['loft_count'] ?? 0); ?></strong> 个公棚
            </div>
            <div style="background:rgba(255,255,255,0.15); padding:8px 16px; border-radius:8px; color:#fff; font-size:14px;">
                <strong style="font-size:20px;"><?php echo number_format($stats['city_count'] ?? 0); ?></strong> 个城市
            </div>
            <div style="background:rgba(255,255,255,0.15); padding:8px 16px; border-radius:8px; color:#fff; font-size:14px;">
                <strong style="font-size:20px;"><?php echo number_format($stats['race_count'] ?? 0); ?></strong> 场赛事
            </div>
            <?php if (($stats['total_entries'] ?? 0) > 0): ?>
            <div style="background:rgba(255,255,255,0.15); padding:8px 16px; border-radius:8px; color:#fff; font-size:14px;">
                <strong style="font-size:20px;"><?php echo number_format($stats['total_entries'] ?? 0); ?></strong> 羽参赛
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="container" style="padding-top:24px; padding-bottom:60px;">
    <div class="content-layout">
        <div class="main-content">

            <?php if (!empty($cities)): ?>
            <!-- 城市导航 -->
            <div style="background:#fff; border-radius:12px; padding:16px 20px; margin-bottom:20px; box-shadow:0 2px 12px rgba(26,95,168,0.06); border:1px solid #e8ecf0;">
                <h3 style="font-size:14px; color:#1a5fa8; margin-bottom:10px;"><i class="fas fa-city"></i> <?php echo h($province); ?>各城市公棚分布</h3>
                <div style="display:flex; flex-wrap:wrap; gap:8px;">
                    <?php foreach ($cities as $city): ?>
                    <a href="/loft/?province=<?php echo urlencode($province); ?>&city=<?php echo urlencode($city['city']); ?>"
                       style="display:inline-flex; align-items:center; gap:4px; padding:5px 14px; background:#f4f6f9; border-radius:16px; font-size:13px; color:#2c3e50; text-decoration:none; border:1px solid #e8ecf0; transition:all 0.2s;">
                        <?php echo h($city['city']); ?>
                        <span style="font-size:11px; color:#6c7a89;">(<?php echo $city['loft_count']; ?>)</span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($lofts)): ?>
            <!-- 公棚列表 -->
            <div class="article-grid">
                <?php foreach ($lofts as $l): ?>
                <div class="article-card">
                    <div class="article-thumb" style="background:linear-gradient(135deg, #e8f4fd 0%, #d0e8f8 100%);">
                        <?php if (!empty($l['logo'])): ?>
                        <img loading="lazy" src="<?php echo h($l['logo']); ?>" alt="<?php echo h($l['name'] ?? ''); ?>">
                        <?php else: ?>
                        <span style="font-size:48px;">🏠</span>
                        <?php endif; ?>
                    </div>
                    <div class="article-body">
                        <div class="article-cat">
                            <?php if (!empty($l['city'])): ?>
                            <i class="fas fa-map-marker-alt"></i> <?php echo h($l['city']); ?>
                            <?php endif; ?>
                            <?php if (!empty($l['race_type'])): ?>
                             · <?php echo h($l['race_type']); ?>
                            <?php endif; ?>
                            <?php if (!empty($l['is_certified'])): ?>
                            <span style="color:#c9a84c;"><i class="fas fa-check-circle"></i> 认证</span>
                            <?php endif; ?>
                        </div>
                        <h2 class="article-title">
                            <a href="/loft/<?php echo intval($l['id']); ?>.html"><?php echo h($l['name'] ?? ''); ?></a>
                        </h2>
                        <p class="article-desc"><?php echo h(mb_substr(strip_tags($l['description'] ?? ''), 0, 100)); ?></p>
                        <div class="article-meta">
                            <?php if (!empty($l['capacity'])): ?>
                            <span><i class="fas fa-feather"></i> 容量 <?php echo number_format($l['capacity']); ?>羽</span>
                            <?php endif; ?>
                            <span><i class="fas fa-eye"></i> <?php echo number_format($l['views'] ?? 0); ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- 分页 -->
            <?php if ($totalPages > 1): ?>
            <div style="display:flex; justify-content:center; gap:7px; margin-top:25px;">
                <?php for ($i = 1; $i <= min($totalPages, 10); $i++): ?>
                <a href="/loft/province/<?php echo urlencode($province); ?>/?page=<?php echo $i; ?>"
                   style="display:inline-block; padding:7px 15px; border-radius:8px; background:<?php echo ($page == $i) ? '#1a5fa8' : '#fff'; ?>; color:<?php echo ($page == $i) ? '#fff' : '#2c3e50'; ?>; text-decoration:none; border:1px solid #e8ecf0; font-size:14px; font-weight:<?php echo ($page == $i) ? '600' : '400'; ?>;">
                    <?php echo $i; ?>
                </a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>

            <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">🏠</div>
                <p class="empty-text"><?php echo h($province); ?>暂无公棚数据</p>
                <a href="/loft/" style="display:inline-block;margin-top:15px;padding:8px 24px;background:#1a5fa8;color:white;border-radius:8px;font-size:14px;text-decoration:none;">查看全部公棚</a>
            </div>
            <?php endif; ?>

        </div>

        <!-- 侧边栏 -->
        <div class="sidebar">
            <!-- 省份导航 -->
            <div class="sidebar-card">
                <h3 class="sidebar-title"><i class="fas fa-globe"></i>按省份浏览</h3>
                <div style="display:flex; flex-wrap:wrap; gap:6px;">
                    <?php if (!empty($allProvinces)): foreach ($allProvinces as $p): ?>
                    <a href="/loft/province/<?php echo urlencode($p); ?>/"
                       style="padding:4px 10px; background:<?php echo ($p === $province) ? '#1a5fa8' : '#f4f6f9'; ?>; color:<?php echo ($p === $province) ? '#fff' : '#2c3e50'; ?>; border-radius:12px; font-size:12px; text-decoration:none; border:1px solid #e8ecf0; white-space:nowrap;">
                        <?php echo h($p); ?>
                    </a>
                    <?php endforeach; endif; ?>
                </div>
            </div>

            <!-- 快速入口 -->
            <div class="sidebar-card">
                <h3 class="sidebar-title"><i class="fas fa-bolt"></i>快速工具</h3>
                <div style="display:flex; flex-direction:column; gap:8px;">
                    <a href="/tools.php?action=top100" style="padding:10px 14px; background:#f4f6f9; border-radius:8px; font-size:13px; color:#2c3e50; text-decoration:none; display:flex; align-items:center; gap:8px;">
                        <span style="font-size:18px;">🏆</span> TOP100 分速排行榜
                    </a>
                    <a href="/tools.php?action=ring_guide" style="padding:10px 14px; background:#f4f6f9; border-radius:8px; font-size:13px; color:#2c3e50; text-decoration:none; display:flex; align-items:center; gap:8px;">
                        <span style="font-size:18px;">🔍</span> 足环代码速查表
                    </a>
                    <a href="/loft/compare/" style="padding:10px 14px; background:#f4f6f9; border-radius:8px; font-size:13px; color:#2c3e50; text-decoration:none; display:flex; align-items:center; gap:8px;">
                        <span style="font-size:18px;">📊</span> 公棚对比工具
                    </a>
                </div>
            </div>

            <div class="sidebar-card" style="background:linear-gradient(135deg,#1a5fa8 0%,#2980b9 100%);border:none;">
                <h3 class="sidebar-title" style="border-bottom-color:rgba(255,255,255,0.2);color:white;"><i class="fas fa-chart-bar"></i><?php echo h($province); ?>赛事</h3>
                <p style="font-size:12px;color:rgba(255,255,255,0.85);line-height:1.8;margin-bottom:10px;">
                    <?php echo h($province); ?>共 <strong><?php echo number_format($stats['race_count'] ?? 0); ?></strong> 场赛事数据，
                    覆盖 <strong><?php echo number_format($stats['loft_count'] ?? 0); ?></strong> 个公棚。
                </p>
                <a href="/race/province/<?php echo urlencode($province); ?>/"
                   style="display:inline-block; padding:6px 16px; background:rgba(255,255,255,0.2); color:#fff; border-radius:20px; font-size:12px; text-decoration:none;">
                    查看赛事明细 →
                </a>
            </div>
        </div>
    </div>
</div>

</div><!-- /page-articles -->

<?php include __DIR__ . '/_footer.php'; ?>
</body>
</html>
