<?php
/**
 * 信鸽之家 - 血统品系列表页（SEO优化版）
 */

require_once dirname(__DIR__) . '/app/config/config.php';

extract($data);

$page_title = $pageTitle ?? '血统品系大全 - 信鸽血统查询 | ' . SITE_NAME;

// SEO 元信息
$total = $total ?? 0;
$meta_description = "信鸽血统品系大全，{$total}个血统品系，包括詹森、杨阿腾、戈登等名系介绍。查血统、看品系，信鸽之家为您提供最全血统资料。";
$meta_keywords = '血统品系,信鸽血统,血统查询,' . SITE_KEYWORDS;
$canonical_url = 'https://www.xgjia.com/pedigree/strain/' . ($_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '');

// JSON-LD - ItemList
$ld_items = [];
foreach (array_slice($strains ?? [], 0, 10) as $i => $s) {
    $ld_items[] = [
        '@type' => 'ListItem',
        'position' => $i + 1,
        'url' => 'https://www.xgjia.com/strain/' . urlencode($s['name']) . '/',
    ];
}
$ld_itemlist = [
    '@context' => 'https://schema.org',
    '@type' => 'ItemList',
    'name' => '血统品系',
    'numberOfItems' => $total,
    'itemListElement' => $ld_items,
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
    <meta property="og:image" content="https://www.xgjia.com/public/images/og-cover.png">
    <meta property="og:title" content="<?php echo h($page_title); ?>">
    <meta property="og:description" content="<?php echo h($meta_description); ?>">
    <meta property="og:url" content="<?php echo h($canonical_url); ?>">
    <meta property="og:site_name" content="<?php echo SITE_NAME; ?>">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="<?php echo h($page_title); ?>">
    <meta name="twitter:description" content="<?php echo h($meta_description); ?>">
    
    <!-- JSON-LD -->
    <script type="application/ld+json"><?php echo json_encode($ld_itemlist, JSON_UNESCAPED_UNICODE); ?></script>
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

        .page-header { background: linear-gradient(135deg, #1a2a3a 0%, #2d4a6a 100%); color: white; padding: 40px 0; text-align: center; margin-bottom: 30px; }
        .page-header h1 { font-size: 36px; margin-bottom: 10px; }
        @media (max-width: 768px) { .page-header { padding: 30px 0; } .page-header h1 { font-size: 28px; } }
        .strain-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 30px; }
        @media (max-width: 1024px) { .strain-grid { grid-template-columns: repeat(3, 1fr); } }
        @media (max-width: 768px) { .strain-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; } }
        .strain-card { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); transition: all 0.3s; text-decoration: none; display: block; }
        .strain-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,0.12); }
        .strain-name { font-size: 18px; font-weight: bold; color: #1a2a3a; margin-bottom: 6px; }
        .strain-count { font-size: 13px; color: #6b7280; }
        .strain-count i { color: #d4a843; }
        .toolbar { display: flex; justify-content: center; gap: 12px; margin-bottom: 24px; flex-wrap: wrap; }
        .search-box { display: flex; background: white; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; width: 320px; }
        .search-box input { border: none; outline: none; padding: 10px 14px; flex: 1; }
        .search-box button { background: #1a2a3a; color: white; border: none; padding: 10px 16px; cursor: pointer; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/_head.php'; ?>

    <div class="page-header">
        <div class="container">
            <h1><i class="fas fa-dna" style="color:#d4a843;"></i> 血统品系</h1>
            <p>浏览所有血统品系，发现优秀铭鸽</p>
        </div>
    </div>

    <div class="container">
        <?php if (!empty($strains)): ?>
        <div class="strain-grid">
            <?php foreach ($strains as $strain): ?>
            <a href="/pedigree/strain/<?php echo urlencode($strain['slug']); ?>/" class="strain-card">
                <div class="strain-name"><?php echo h($strain['name']); ?></div>
                <div class="strain-count"><i class="fas fa-dove"></i> <?php echo number_format($strain['pigeon_count']); ?> 只铭鸽</div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="text-center py-12"><i class="fas fa-dna text-gray-300 text-6xl mb-4"></i><p class="text-gray">暂无品系数据</p></div>
        <?php endif; ?>
    </div>


    <?php include __DIR__ . '/_footer.php'; ?>
