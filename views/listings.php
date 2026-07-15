<?php
/**
 * 信鸽之家 - 分类信息列表页（SEO优化版）
 */

require_once dirname(__DIR__) . '/app/config/config.php';

// $data 由 Controller::loadView() 提取
extract($data);

$page_title = $pageTitle ?? '分类信息' . (($page ?? 1) > 1 ? ' - 第' . intval($page) . '页' : '') . ' - 信鸽交易·鸽友交流 | ' . SITE_NAME;

// 定义类型标签
$typeLabels = [
    1 => '出售',
    2 => '求购',
    3 => '转让',
    4 => '配对',
    5 => '服务'
];

$typeColors = [
    1 => 'bg-red-100 text-red-800',
    2 => 'bg-blue-100 text-blue-800',
    3 => 'bg-green-100 text-green-800',
    4 => 'bg-purple-100 text-purple-800',
    5 => 'bg-yellow-100 text-yellow-800'
];

// SEO 元信息
$total = $total ?? 0;
$meta_description = "信鸽之家分类信息版块，共{$total}条信息，提供赛鸽出售、鸽具交易、鸽友交流等服务。发布您的信鸽信息，找到合适的买家和鸽友。";
$meta_keywords = '信鸽交易,鸽友交流,' . KEYWORDS_LISTINGS;
$canonical_url = 'https://www.xgjia.com/listing/' . ($_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '');

// JSON-LD - ItemList
$ld_items = [];
foreach (array_slice($listings ?? [], 0, 10) as $i => $l) {
    $ld_items[] = [
        '@type' => 'ListItem',
        'position' => $i + 1,
        'url' => 'https://www.xgjia.com/listing/' . $l['id'] . '.html',
    ];
}
$ld_itemlist = [
    '@context' => 'https://schema.org',
    '@type' => 'ItemList',
    'name' => '分类信息',
    'numberOfItems' => $total,
    'itemListElement' => $ld_items,
];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
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
    
    <!-- Favicon -->
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

        /* 页面专属样式 */
        .page-header {
            background: linear-gradient(135deg, var(--primary) 0%, #1e40af 100%);
            color: white;
            padding: 40px 0;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .page-header h1 {
            font-size: 36px;
            margin-bottom: 10px;
        }
        
        @media (max-width: 768px) {
            .page-header {
                padding: 30px 0;
            }
            
            .page-header h1 {
                font-size: 28px;
            }
        }
        
        .filter-bar {
            background-color: white;
            padding: 20px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            margin-bottom: 30px;
        }
        
        .filter-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .filter-row:last-child {
            margin-bottom: 0;
        }
        
        .filter-label {
            font-weight: bold;
            min-width: 80px;
        }
        
        .filter-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        
        .filter-item {
            padding: 6px 16px;
            border-radius: 16px;
            background-color: var(--gray-100);
            color: var(--gray-700);
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            font-size: 14px;
        }
        
        .filter-item:hover {
            background-color: var(--primary);
            color: white;
        }
        
        .filter-item.active {
            background-color: var(--primary);
            color: white;
        }
        
        .listing-list {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }
        
        .listing-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid var(--gray-200);
            transition: all 0.3s;
        }
        
        .listing-item:last-child {
            border-bottom: none;
        }
        
        .listing-item:hover {
            background-color: var(--gray-50);
        }
        
        @media (max-width: 768px) {
            .listing-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }
        
        .listing-info {
            flex: 1;
        }
        
        .listing-type {
            margin-bottom: 8px;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .listing-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 8px;
        }
        
        .listing-title a {
            color: var(--gray-900);
            text-decoration: none;
        }
        
        .listing-title a:hover {
            color: var(--primary);
        }
        
        .listing-meta {
            font-size: 13px;
            color: var(--gray-500);
        }
        
        .listing-price {
            font-size: 24px;
            font-weight: bold;
            color: var(--danger-color);
            text-align: right;
        }
        
        @media (max-width: 768px) {
            .listing-price {
                text-align: left;
                font-size: 20px;
            }
        }
        
        .hot-listings {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .hot-listings h3 {
            font-size: 18px;
            margin-bottom: 15px;
            color: var(--danger-color);
        }
        
        .hot-item {
            padding: 12px 0;
            border-bottom: 1px solid var(--gray-200);
        }
        
        .hot-item:last-child {
            border-bottom: none;
        }
        
        .hot-item a {
            color: var(--gray-800);
            text-decoration: none;
        }
        
        .hot-item a:hover {
            color: var(--primary);
        }
        
        .hot-meta {
            font-size: 12px;
            color: var(--gray-500);
            margin-top: 5px;
        }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/_head.php'; ?>
<!-- 页面标题 -->
    <div class="page-header">
        <div class="container">
            <h1><i class="fas fa-list-alt mr-2"></i>分类信息</h1>
            <p>信鸽交易、鸽具买卖、配对服务</p>
        </div>
    </div>

    <!-- 主内容区 -->
    <div class="container">
        <div class="flex gap-8" style="display: flex;">
            <!-- 左侧主内容 -->
            <div style="flex: 1;">
                <!-- 筛选栏 -->
                <div class="filter-bar">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="font-semibold">
                            <i class="fas fa-filter mr-2"></i>筛选条件
                        </h2>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a href="/listing/create" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus mr-1"></i>发布信息
                            </a>
                        <?php else: ?>
                            <a href="/login" class="btn btn-outline btn-sm">
                                <i class="fas fa-plus mr-1"></i>登录后发布
                            </a>
                        <?php endif; ?>
                    </div>
                    
                    <!-- 类型筛选 -->
                    <div class="filter-row">
                        <div class="filter-label">类型：</div>
                        <div class="filter-list">
                            <a href="/listing/" class="filter-item <?php echo empty($currentType) ? 'active' : ''; ?>">
                                全部
                            </a>
                            <?php foreach ($typeLabels as $typeId => $typeName): ?>
                                <a href="/listing/?type=<?php echo $typeId; ?>" 
                                   class="filter-item <?php echo $currentType == $typeId ? 'active' : ''; ?>">
                                    <?php echo $typeName; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- 地区筛选 -->
                    <div class="filter-row" style="margin-top: 10px;">
                        <div class="filter-label">地区：</div>
                        <div class="filter-list">
                            <a href="/listing/" class="filter-item <?php echo empty($currentLocation) ? 'active' : ''; ?>">
                                全部
                            </a>
                        </div>
                    </div>
                </div>

                <!-- 分类信息列表 -->
                <?php if (!empty($listings)): ?>
                <div class="listing-list">
                    <?php foreach ($listings as $listing): ?>
                    <div class="listing-item">
                        <div class="listing-info">
                            <div class="listing-type">
                                <?php $type = $listing['type'] ?? 1; ?>
                                <span class="badge <?php echo $typeColors[$type] ?? 'bg-gray-100 text-gray-800'; ?>">
                                    <?php echo $typeLabels[$type] ?? '其他'; ?>
                                </span>
                            </div>
                            
                            <h3 class="listing-title">
                                <a href="/listing/<?php echo $listing['id']; ?>.html">
                                    <?php echo h($listing['title']); ?>
                                </a>
                            </h3>
                            
                            <div class="listing-meta">
                                <span><i class="fas fa-map-marker-alt mr-1"></i><?php echo h($listing['location']); ?></span>
                                <span><i class="fas fa-clock mr-1"></i><?php echo date('Y-m-d', strtotime($listing['created_at'])); ?></span>
                                <span><i class="fas fa-eye mr-1"></i><?php echo $listing['views']; ?></span>
                            </div>
                        </div>
                        
                        <?php if (!empty($listing['price'])): ?>
                        <div class="listing-price">
                            ¥<?php echo number_format($listing['price'], 0); ?>
                            <?php if (!empty($listing['negotiable'])): ?>
                            <span style="font-size: 12px; color: var(--gray-500);">可议价</span>
                            <?php endif; ?>
                        </div>
                        <?php else: ?>
                        <div class="listing-price" style="color: var(--gray-500); font-size: 16px;">
                            面议
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- 分页 -->
                <?php echo renderPagination($page, $totalPages); ?>
            </div>
            <?php endif; ?>
            
            <!-- 右侧边栏 -->
            <div style="width: 300px;">
                <!-- 热门信息 -->
                <?php if (!empty($hotListings)): ?>
                <div class="hot-listings">
                    <h2><i class="fas fa-fire mr-2"></i>热门信息</h2>
                    <?php foreach ($hotListings as $hot): ?>
                    <div class="hot-item">
                        <a href="/listing/<?php echo $hot['id']; ?>.html">
                            <?php echo h($hot['title']); ?>
                        </a>
                        <div class="hot-meta">
                            <span><i class="fas fa-eye mr-1"></i><?php echo $hot['views']; ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>


    <?php include __DIR__ . '/_footer.php'; ?>
