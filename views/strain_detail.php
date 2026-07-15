<?php
/**
 * 信鸽之家 - 血统品系详情页（SEO优化版）
 */

require_once dirname(__DIR__) . '/app/config/config.php';

extract($data);

$page_title = $pageTitle ?? (h($strain['name']) . ' - 血统品系介绍 | ' . SITE_NAME);

// SEO 元信息
$total = $total ?? 0;
$meta_description = h($strain['name']) . ' 血统品系介绍，共' . number_format($total) . '羽铭鸽。查看血统特征、代表鸽、配对信息，了解' . h($strain['name']) . '品系的详细信息。';
$meta_keywords = h($strain['name']) . ',血统品系,信鸽血统,' . SITE_KEYWORDS;
$canonical_url = 'https://www.xgjia.com/pedigree/strain/' . urlencode($strain['name']) . '/';

// JSON-LD - CollectionPage
$ld_collection = [
    '@context' => 'https://schema.org',
    '@type' => 'CollectionPage',
    'name' => $strain['name'] . ' 血统品系',
    'description' => $meta_description,
    'url' => $canonical_url,
    'mainEntity' => [
        '@type' => 'ItemList',
        'numberOfItems' => $total,
    ],
];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo h($page_title); ?></title>
    
    <!-- SEO Meta -->
    <meta name="description" content="<?php echo h($meta_description); ?>">
    <meta name="keywords" content="<?php echo h($meta_keywords); ?>">
    <link rel="canonical" href="<?php echo h($canonical_url); ?>">
    
    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?php echo h($page_title); ?>">
        <meta property="og:image" content="https://www.xgjia.com/public/images/og-cover.png">
    <meta property="og:description" content="<?php echo h($meta_description); ?>">
    <meta property="og:url" content="<?php echo h($canonical_url); ?>">
    <meta property="og:site_name" content="<?php echo SITE_NAME; ?>">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="<?php echo h($page_title); ?>">
    <meta name="twitter:description" content="<?php echo h($meta_description); ?>">
    
    <!-- JSON-LD -->
    <script type="application/ld+json"><?php echo json_encode($ld_collection, JSON_UNESCAPED_UNICODE); ?></script>
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/public/css/b-scheme.css">
    <link rel="shortcut icon" href="/public/images/favicon.ico">
    <style>
            :root {
                --primary: #1a5fa8;
                --primary-light: #2980b9;
                --primary-dark: #154360;
                --accent: #c9a84c;
                --accent-light: #e0c060;
                --bg: #f4f6f9;
                --white: #ffffff;
                --text: #2c3e50;
                --text-light: #6c7a89;
                --border: #e8ecf0;
                --shadow: 0 2px 12px rgba(26,95,168,0.08);
                --shadow-hover: 0 8px 30px rgba(26,95,168,0.15);
                --gold: #d4a843;
                --success: #27ae60;
                --danger: #e74c3c;
                --radius: 12px;
            }

        .page-header { background: linear-gradient(135deg, #1a2a3a 0%, #2d4a6a 100%); color: white; padding: 30px 0; margin-bottom: 30px; }
        .breadcrumb { font-size: 13px; color: rgba(255,255,255,0.7); margin-bottom: 8px; }
        .breadcrumb a { color: rgba(255,255,255,0.7); text-decoration: none; }
        .breadcrumb a:hover { color: white; }
        .page-header h1 { font-size: 30px; margin-bottom: 4px; }
        .page-header .meta { font-size: 14px; color: rgba(255,255,255,0.8); }
        .pigeon-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
        @media (max-width: 1024px) { .pigeon-grid { grid-template-columns: repeat(3, 1fr); } }
        @media (max-width: 768px) { .pigeon-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; } .strain-stats > div:last-child { grid-template-columns: 1fr !important; } }
        .pigeon-card { background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); overflow: hidden; transition: all 0.3s; }
        .pigeon-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,0.12); }
        .pigeon-image { width: 100%; height: 200px; object-fit: cover; background: #f3f4f6; }
        @media (max-width: 768px) { .pigeon-image { height: 150px; } }

        .pigeon-info { padding: 14px; }
        .pigeon-name { font-size: 16px; font-weight: bold; margin-bottom: 6px; }
        .pigeon-name a { color: #1a2a3a; text-decoration: none; }
        .pigeon-name a:hover { color: #d4a843; }
    </style>
<!-- BreadcrumbList -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "BreadcrumbList",
        "itemListElement": [
            {"@type": "ListItem", "position": 1, "name": "首页", "item": "https://www.xgjia.com"},
            {"@type": "ListItem", "position": 2, "name": "血统品系", "item": "https://www.xgjia.com/pedigree/"},
            {"@type": "ListItem", "position": 3, "name": "<?php echo h($strain['name'] ?? '血统详情'); ?>"}
        ]
    }
    </script>
</head>
<body>
    <?php include __DIR__ . '/_head.php'; ?>

    <div class="page-header">
        <div class="container">
            <div class="breadcrumb"><a href="/">首页</a> &gt; <a href="/pedigree/">血统品系</a> &gt; <?php echo h($strain['name']); ?></div>
            <h1><i class="fas fa-dna" style="color:#d4a843;"></i> <?php echo h($strain['name']); ?></h1>
            <div class="meta">共 <?php echo number_format($total); ?> 只铭鸽</div>
        </div>
    </div>

    <div style="text-align:center;margin: -10px 0 20px;">
        <a href="/pedigree/strain/<?php echo urlencode($strain['name']); ?>/race-results/" class="btn btn-primary" style="display:inline-flex;align-items:center;gap:8px;padding:10px 24px;font-size:15px;background:linear-gradient(135deg,#1a5fa8,#2563eb);color:#fff;border-radius:8px;text-decoration:none;font-weight:600;">
            <i class="fas fa-trophy"></i> 查看赛事成绩
        </a>
    </div>

    <div class="container">

        <?php if (!empty($colorDist) || !empty($genderStats)): ?>
        <!-- 品系数看板 -->
        <div class="strain-stats" style="background:white;border-radius:12px;box-shadow:0 2px 12px rgba(26,95,168,0.08);padding:24px;margin-bottom:24px;">
            <h2 style="font-size:18px;margin-bottom:16px;color:#1a2a3a;"><i class="fas fa-chart-bar" style="color:#d4a843;"></i> 品系统计</h2>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:20px;">
                <?php if (!empty($colorDist)): ?>
                <div>
                    <h3 style="font-size:14px;color:#6c7a89;margin-bottom:10px;"><i class="fas fa-palette"></i> 羽色分布</h3>
                    <?php foreach (array_slice($colorDist, 0, 5) as $c): ?>
                    <div style="display:flex;align-items:center;margin-bottom:6px;font-size:13px;">
                        <span style="flex:1;color:#2c3e50;"><?php echo h($c['color']); ?></span>
                        <span style="width:100px;background:#f3f4f6;border-radius:4px;height:6px;margin:0 8px;overflow:hidden;">
                            <span style="display:block;height:100%;background:var(--primary);border-radius:4px;width:<?php echo round($c['cnt'] / max($colorDist[0]['cnt'], 1) * 100); ?>%;"></span>
                        </span>
                        <span style="color:#6c7a89;min-width:32px;text-align:right;"><?php echo round($c['cnt'] / $totalPigeons * 100); ?>%</span>
                    </div>
                    <?php endforeach; ?>
                    <?php if (count($colorDist) > 5): ?>
                    <div style="font-size:12px;color:#999;">+<?php echo count($colorDist) - 5; ?> 种羽色</div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                <?php if (!empty($genderStats)): ?>
                <div>
                    <h3 style="font-size:14px;color:#6c7a89;margin-bottom:10px;"><i class="fas fa-venus-mars"></i> 性别分布</h3>
                    <?php
                    $genderCounts = [0 => 0, 1 => 0, 2 => 0];
                    foreach ($genderStats as $g) $genderCounts[(int)$g['gender']] = (int)$g['cnt'];
                    $colors = [0 => '#95a5a6', 1 => '#3498db', 2 => '#e84393'];
                    foreach ([1, 2, 0] as $g): if ($genderCounts[$g] > 0): ?>
                    <div style="display:flex;align-items:center;margin-bottom:6px;font-size:13px;">
                        <span style="flex:1;color:#2c3e50;"><?php echo $genderMap[$g]; ?></span>
                        <span style="width:100px;background:#f3f4f6;border-radius:4px;height:6px;margin:0 8px;overflow:hidden;">
                            <span style="display:block;height:100%;background:<?php echo $colors[$g]; ?>;border-radius:4px;width:<?php echo round($genderCounts[$g] / $totalPigeons * 100); ?>%;"></span>
                        </span>
                        <span style="color:#6c7a89;min-width:44px;text-align:right;"><?php echo $genderCounts[$g]; ?> 羽 (<?php echo round($genderCounts[$g] / $totalPigeons * 100); ?>%)</span>
                    </div>
                    <?php endif; endforeach; ?>
                </div>
                <?php endif; ?>
                <?php if (!empty($eyeDist)): ?>
                <div>
                    <h3 style="font-size:14px;color:#6c7a89;margin-bottom:10px;"><i class="fas fa-eye"></i> 眼砂分布</h3>
                    <?php foreach (array_slice($eyeDist, 0, 5) as $e): ?>
                    <div style="display:flex;align-items:center;margin-bottom:6px;font-size:13px;">
                        <span style="flex:1;color:#2c3e50;"><?php echo h($e['eye_color']); ?></span>
                        <span style="width:100px;background:#f3f4f6;border-radius:4px;height:6px;margin:0 8px;overflow:hidden;">
                            <span style="display:block;height:100%;background:#c9a84c;border-radius:4px;width:<?php echo round($e['cnt'] / max($eyeDist[0]['cnt'], 1) * 100); ?>%;"></span>
                        </span>
                        <span style="color:#6c7a89;min-width:32px;text-align:right;"><?php echo $e['cnt']; ?></span>
                    </div>
                    <?php endforeach; ?>
                    <?php if (count($eyeDist) > 5): ?>
                    <div style="font-size:12px;color:#999;">+<?php echo count($eyeDist) - 5; ?> 种眼砂</div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($pigeons)): ?>
        <div class="pigeon-grid">
            <?php foreach ($pigeons as $pigeon): ?>
            <div class="pigeon-card">
                <?php $images = json_decode($pigeon['images'] ?? '[]', true) ?: []; ?>
                <?php if (!empty($images[0])): ?>
                <a href="/pigeon/<?php echo $pigeon['id']; ?>.html">
                    <img loading="lazy" src="<?php echo h($images[0]); ?>" alt="<?php echo h($pigeon['name']); ?>" class="pigeon-image" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                    <div class="pigeon-image" style="display:none;background:#f3f4f6;display:none;align-items:center;justify-content:center;"><i class="fas fa-dove" style="color:#d1d5db;font-size:48px;"></i></div>
                </a>
                <?php else: ?>
                <div class="pigeon-image" style="background:#f3f4f6;display:flex;align-items:center;justify-content:center;"><i class="fas fa-dove" style="color:#d1d5db;font-size:48px;"></i></div>
                <?php endif; ?>
                <div class="pigeon-info">
                    <div class="pigeon-name"><a href="/pigeon/<?php echo $pigeon['id']; ?>.html"><?php echo h($pigeon['name']); ?></a></div>
                    <?php if (!empty($pigeon['ring_number'])): ?>
                    <div class="pigeon-meta"><i class="fas fa-tag" style="color:#d4a843;margin-right:4px;"></i><?php echo h(mb_strimwidth($pigeon['ring_number'],0,18,'...')); ?></div>
                    <?php endif; ?>
                    <div class="pigeon-meta"><i class="fas fa-eye" style="margin-right:4px;"></i><?php echo number_format($pigeon['views']); ?> 次浏览</div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php echo renderPagination($page, $totalPages); ?>
        <?php else: ?>
        <div class="text-center py-12"><i class="fas fa-dove text-gray-300 text-6xl mb-4"></i><p class="text-gray">该品系暂无铭鸽</p></div>
        <?php endif; ?>
    </div>


    <?php include __DIR__ . '/_footer.php'; ?>
