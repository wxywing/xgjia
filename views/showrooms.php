<?php
/**
 * 信鸽之家 - 展厅列表页
 */

require_once dirname(__DIR__) . '/app/config/config.php';
extract($data);

$page_title = '展厅大全 | ' . SITE_NAME;
$meta_description = "信鸽之家展厅大全，收录全国优质种鸽展厅信息。";
$canonical_url = 'https://www.xgjia.com/showroom/' . ($_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '');
$meta_keywords = '展厅,种鸽,信鸽,' . SITE_KEYWORDS;

// JSON-LD - ItemList
$ld_items = [];
foreach (array_slice($showrooms ?? [], 0, 10) as $i => $s) {
    $ld_items[] = [
        '@type' => 'ListItem',
        'position' => $i + 1,
        'url' => 'https://www.xgjia.com/shop/' . $s['id'] . '.html',
    ];
}
$ld_itemlist = [
    '@context' => 'https://schema.org',
    '@type' => 'ItemList',
    'name' => '鸽舍展厅',
    'numberOfItems' => $total ?? 0,
    'itemListElement' => $ld_items,
];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo h($page_title); ?></title>
    
    <meta name="description" content="<?php echo h($meta_description); ?>">
    <meta name="keywords" content="<?php echo h($meta_keywords); ?>">
    
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <meta property="og:title" content="展厅大全 | 信鸽之家">
    <meta property="og:description" content="信鸽之家展厅大全，收录全国优质种鸽展厅信息。查看各地展厅铭鸽展示、血统介绍、配对记录。">
    <meta property="og:image" content="https://www.xgjia.com/public/images/og-cover.png">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="信鸽之家">
    <link rel="canonical" href="<?php echo h($canonical_url); ?>">
<link rel="stylesheet" href="/public/css/b-scheme.css">
    <script type="application/ld+json"><?php echo json_encode($ld_itemlist, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>
</head>
<body>
    <?php include __DIR__ . '/_head.php'; ?>

<!-- page-showrooms wrapper -->
<div class="page-showrooms">

    <div class="page-header">
        <div class="container">
            <h1><i class="fas fa-store mr-2"></i>展厅大全</h1>
            <p>全国优质种鸽展厅信息</p>
        </div>
    </div>

    <div class="container">
        <?php if (!empty($showrooms)): ?>
        <div class="showroom-grid">
            <?php foreach ($showrooms as $showroom): ?>
            <div class="showroom-card">
                <img loading="lazy" src="/public/uploads/<?php echo h($showroom['photo'] ?? 'default.jpg'); ?>" alt="<?php echo h($showroom['name'] ?? '展厅图片'); ?>" 
                     alt="<?php echo h($showroom['name']); ?>" 
                     class="showroom-img">
                <div class="showroom-body">
                    <h2 class="showroom-name">
                        <a href="/showroom/<?php echo $showroom['id']; ?>.html">
                            <?php echo h($showroom['name']); ?>
                        </a>
                    </h2>
                    <div class="showroom-meta">
                        <?php if (!empty($showroom['owner'])): ?>
                        <div><i class="fas fa-user"></i> <?php echo h($showroom['owner']); ?></div>
                        <?php endif; ?>
                        <?php if (!empty($showroom['address'])): ?>
                        <div><i class="fas fa-map-marker-alt"></i> <?php echo h($showroom['address']); ?></div>
                        <?php endif; ?>
                        <div><i class="fas fa-eye"></i> 浏览量：<?php echo $showroom['views'] ?? 0; ?></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="text-center py-12">
            <i class="fas fa-store text-gray-300" style="font-size: 60px;"></i>
            <p style="color: var(--gray-500); margin-top: 15px;">暂无展厅信息</p>
        </div>
        <?php endif; ?>
    </div>

    
</div><!-- /page-showrooms -->

<?php include __DIR__ . '/_footer.php'; ?>
</body>
</html>