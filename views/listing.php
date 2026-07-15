<?php
/**
 * 信鸽之家 - 分类信息详情页（SEO优化版）
 */

require_once dirname(__DIR__) . '/app/config/config.php';

// $data 由 Controller::loadView() 提取
extract($data);

$page_title = $pageTitle ?? (h($listing['title']) . ' | ' . ($typeLabels[$listing['type']] ?? '分类信息') . ' | ' . SITE_NAME);

// 联系方式查看权限（由 Controller 传入）
$canViewContact = $canViewContact ?? false;

// SEO 元信息
$typeLabels = [1 => '出售', 2 => '求购', 3 => '转让', 4 => '配对', 5 => '服务'];
$meta_description = h($listing['title']);
if (!empty($listing['type']) && isset($typeLabels[$listing['type']])) {
    $meta_description .= '，' . $typeLabels[$listing['type']] . '信息';
}
if (!empty($listing['price'])) {
    $meta_description .= '，价格：' . h($listing['price']);
}
$meta_description .= '。查看详细信息、联系方式，信鸽之家分类信息频道。';

$meta_keywords = h($listing['title']) . ',' . ($typeLabels[$listing['type']] ?? '分类信息') . ',信鸽交易,' . KEYWORDS_LISTINGS;

$og_url = 'https://www.xgjia.com/listing/' . $listing['id'] . '.html';
$og_image = !empty($listing['images']) ? h(json_decode($listing['images'], true)[0] ?? '') : '';

// JSON-LD - Product
$ld_product = [
    '@context' => 'https://schema.org',
    '@type' => 'Product',
    'name' => $listing['title'],
    'description' => mb_substr(strip_tags($listing['content'] ?? ''), 0, 200),
];
if ($og_image) {
    $ld_product['image'] = $og_image;
}
if (!empty($listing['price'])) {
    $ld_product['offers'] = [
        '@type' => 'Offer',
        'price' => preg_replace('/[^0-9.]/', '', $listing['price']),
        'priceCurrency' => 'CNY',
        'availability' => 'https://schema.org/InStock',
    ];
}
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
    <link rel="canonical" href="<?php echo h($og_url); ?>">
    
    <!-- Open Graph -->
    <meta property="og:type" content="product">
    <meta property="og:title" content="<?php echo h($page_title); ?>">
    <meta property="og:description" content="<?php echo h($meta_description); ?>">
    <meta property="og:url" content="<?php echo h($og_url); ?>">
    <meta property="og:site_name" content="<?php echo SITE_NAME; ?>">
    <?php if ($og_image): ?>
    <meta property="og:image" content="<?php echo h($og_image); ?>">
    <?php endif; ?>
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo h($page_title); ?>">
    <meta name="twitter:description" content="<?php echo h($meta_description); ?>">
    <?php if ($og_image): ?>
    <meta name="twitter:image" content="<?php echo h($og_image); ?>">
    <?php endif; ?>
    
    <!-- JSON-LD -->
    <script type="application/ld+json"><?php echo json_encode($ld_product, JSON_UNESCAPED_UNICODE); ?></script>

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

        /* 详情页样式 */
        .detail-header {
            background: linear-gradient(135deg, var(--primary) 0%, #1e40af 100%);
            color: white;
            padding: 40px 0;
            margin-bottom: 30px;
        }
        
        .detail-content {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 30px;
        }
        
        @media (max-width: 1024px) {
            .detail-content {
                grid-template-columns: 1fr;
            }
        }
        
        .main-content {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 30px;
        }
        
        .listing-gallery {
            margin-bottom: 30px;
        }
        
        .main-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: var(--border-radius);
            background-color: var(--gray-200);
        }
        
        @media (max-width: 768px) {
            .main-image {
                height: 250px;
            }
        }
        
        .listing-title {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        
        .listing-meta {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }
        
        @media (max-width: 768px) {
            .listing-meta {
                grid-template-columns: 1fr;
            }
        }
        
        .meta-item {
            display: flex;
            padding: 12px;
            background-color: var(--gray-50);
            border-radius: var(--border-radius);
        }
        
        .meta-label {
            color: var(--gray-600);
            min-width: 80px;
        }
        
        .meta-value {
            flex: 1;
            font-weight: bold;
        }
        
        .price-box {
            background-color: var(--primary);
            color: white;
            padding: 20px;
            border-radius: var(--border-radius);
            text-align: center;
            margin-bottom: 30px;
        }
        
        .price-value {
            font-size: 32px;
            font-weight: bold;
        }
        
        .contact-box {
            background-color: var(--gray-50);
            padding: 20px;
            border-radius: var(--border-radius);
            margin-bottom: 30px;
        }
        
        .contact-box h3 {
            margin-bottom: 15px;
            color: var(--primary);
        }
        
        .contact-item {
            margin-bottom: 10px;
        }
        
        .contact-blur {
            filter: blur(5px);
            user-select: none;
        }
        
        .vip-notice {
            background-color: #fef3c7;
            border: 1px solid #fbbf24;
            padding: 15px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
        }
        
        .description {
            line-height: 1.8;
            color: var(--gray-700);
        }
        
        .sidebar {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .sidebar-box {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 20px;
        }
        
        .sidebar-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--primary);
        }
        
        .related-item {
            padding: 12px 0;
            border-bottom: 1px solid var(--gray-200);
        }
        
        .related-item:last-child {
            border-bottom: none;
        }
        
        .related-item a {
            color: var(--gray-800);
            text-decoration: none;
        }
        
        .related-item a:hover {
            color: var(--primary);
        }
        
        .related-meta {
            font-size: 12px;
            color: var(--gray-500);
            margin-top: 5px;
        }
    </style>
<!-- BreadcrumbList -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "BreadcrumbList",
        "itemListElement": [
            {"@type": "ListItem", "position": 1, "name": "首页", "item": "https://www.xgjia.com"},
            {"@type": "ListItem", "position": 2, "name": "分类信息", "item": "https://www.xgjia.com/listing/"},
            {"@type": "ListItem", "position": 3, "name": "<?php echo h($listing['title'] ?? '信息详情'); ?>"}
        ]
    }
    </script>
</head>
<body>
    <?php include __DIR__ . '/_head.php'; ?>
<!-- 详情头部 -->
    <div class="detail-header">
        <div class="container">
            <div class="flex items-center gap-2 mb-2">
                <a href="/listing/" class="text-white hover:text-yellow-300">
                    <i class="fas fa-list mr-1"></i>分类信息
                </a>
                <i class="fas fa-chevron-right text-sm"></i>
                <span>详情</span>
            </div>
            <h1 class="text-3xl font-bold"><?php echo h($listing['title']); ?></h1>
        </div>
    </div>

    <!-- 主内容区 -->
    <div class="container">
        <div class="detail-content">
            <!-- 左侧主内容 -->
            <div class="main-content">
                <!-- 图片 -->
                <?php if (!empty($listing['images'])): ?>
                <div class="listing-gallery">
                    <?php 
                    $images = json_decode($listing['images'] ?? '[]', true) ?: [];
                    $mainImage = $images[0] ?? '/public/images/default-listing.jpg';
                    ?>
                    <img src="<?php echo h($mainImage); ?>" alt="<?php echo h($listing['title'] ?? '详情图片'); ?>" 
                         alt="<?php echo h($listing['title']); ?>" 
                         class="main-image">
                </div>
                <?php endif; ?>

                <!-- 标题 -->
                <h2 class="listing-title"><?php echo h($listing['title']); ?></h2>

                <!-- 基本信息 -->
                <div class="listing-meta">
                    <div class="meta-item">
                        <div class="meta-label">类型：</div>
                        <div class="meta-value">
                            <?php
                            $types = [1 => '信鸽转让', 2 => '鸽具用品', 3 => '鸽舍建设', 4 => '其他'];
                            echo $types[$listing['type']] ?? '其他';
                            ?>
                        </div>
                    </div>
                    
                    <div class="meta-item">
                        <div class="meta-label">地区：</div>
                        <div class="meta-value"><?php echo h($listing['location']); ?></div>
                    </div>
                    
                    <div class="meta-item">
                        <div class="meta-label">发布时间：</div>
                        <div class="meta-value"><?php echo date('Y-m-d', strtotime($listing['created_at'])); ?></div>
                    </div>
                    
                    <div class="meta-item">
                        <div class="meta-label">浏览：</div>
                        <div class="meta-value"><?php echo $listing['views']; ?> 次</div>
                    </div>
                </div>

                <!-- 价格 -->
                <?php if (!empty($listing['price'])): ?>
                <div class="price-box">
                    <div class="text-sm mb-2">价格</div>
                    <div class="price-value">¥<?php echo number_format($listing['price'], 2); ?></div>
                </div>
                <?php endif; ?>

                <!-- 联系方式 -->
                <div class="contact-box">
                    <h3><i class="fas fa-phone mr-2"></i>联系方式</h3>
                    
                    <?php if ($canViewContact): ?>
                        <?php if (!empty($listing['contact_name'])): ?>
                        <div class="contact-item">
                            <strong>联系人：</strong><?php echo h($listing['contact_name']); ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($listing['contact_phone'])): ?>
                        <div class="contact-item">
                            <strong>电话：</strong><?php echo h($listing['contact_phone']); ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($listing['contact_wechat'])): ?>
                        <div class="contact-item">
                            <strong>微信：</strong><?php echo h($listing['contact_wechat']); ?>
                        </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="vip-notice">
                            <i class="fas fa-lock mr-2"></i>
                            <strong>VIP会员专属</strong>
                            <p class="text-sm mt-2">升级VIP会员可查看完整联系方式</p>
                            <a href="/user/member" class="btn btn-warning mt-3">
                                <i class="fas fa-crown mr-1"></i>立即升级
                            </a>
                        </div>
                        
                        <div class="contact-item">
                            <strong>联系人：</strong><span class="contact-blur">张先生</span>
                        </div>
                        <div class="contact-item">
                            <strong>电话：</strong><span class="contact-blur">138****8888</span>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- 描述 -->
                <?php if (!empty($listing['description'])): ?>
                <div class="description">
                    <h3 class="font-bold mb-3">详细描述</h3>
                    <p><?php echo nl2br(h($listing['description'])); ?></p>
                </div>
                <?php endif; ?>
            </div>

            <!-- 右侧边栏 -->
            <div class="sidebar">
                <!-- 相关推荐 -->
                <?php if (!empty($relatedListings)): ?>
                <div class="sidebar-box">
                    <h3 class="sidebar-title">相关推荐</h3>
                    <ul>
                        <?php foreach ($relatedListings as $related): ?>
                        <li class="related-item">
                            <a href="/listing/<?php echo $related['id']; ?>.html">
                                <?php echo h($related['title']); ?>
                            </a>
                            <div class="related-meta">
                                <?php if (!empty($related['price'])): ?>
                                ¥<?php echo number_format($related['price'], 0); ?>
                                <?php endif; ?>
                                | <?php echo h($related['location']); ?>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <!-- 发布信息 -->
                <div class="sidebar-box">
                    <h3 class="sidebar-title">发布信息</h3>
                    <p class="text-gray text-sm">
                        <i class="fas fa-info-circle mr-1"></i>
                        本信息由用户自行发布，请谨慎交易。
                    </p>
                    <a href="/listing/create" class="btn btn-primary btn-sm mt-3">
                        <i class="fas fa-plus mr-1"></i>发布信息
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 底部Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div>
                    <h3 class="footer-title">关于我们</h3>
                    <ul class="footer-links">
                        <li><a href="/pages/about/">网站介绍</a></li>
                        <li><a href="/pages/contact/">联系方式</a></li>
                        <li><a href="/pages/ad/">广告合作</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="footer-title">帮助中心</h3>
                    <ul class="footer-links">
                        <li><a href="/pages/help/">新手指南</a></li>
                        <li><a href="/pages/faq/">常见问题</a></li>
                        <li><a href="/pages/agreement/">用户协议</a></li>
                        <li><a href="/pages/privacy/">隐私政策</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="footer-title">友情链接</h3>
                    <ul class="footer-links">
                        <li><a href="https://www.chinaxinge.com" target="_blank" rel="noopener">中信网</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="footer-title">联系我们</h3>
                    <ul class="footer-links">
                        <li><i class="fas fa-envelope mr-2"></i>admin@xgjia.com</li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2026 <?php echo SITE_NAME; ?> 版权所有</p>
            </div>
        </div>
    </footer>

    <!-- 移动端底部导航 -->
    <nav class="mobile-bottom-nav">
        <div class="nav-items">
            <div class="nav-item" onclick="location.href='/'"><i class="fas fa-home"></i><span>首页</span></div>
            <div class="nav-item" onclick="location.href='/article/'"><i class="fas fa-newspaper"></i><span>资讯</span></div>
            <div class="nav-item" onclick="location.href='/shop/'"><i class="fas fa-dove"></i><span>铭鸽</span></div>
            <div class="nav-item" onclick="location.href='/loft/'"><i class="fas fa-building"></i><span>公棚</span></div>
            <div class="nav-item" onclick="location.href='/dynamics/'"><i class="fas fa-comments"></i><span>鸽友圈</span></div>
        </div>
    </nav>

    <!-- JavaScript -->
    <script>
        function toggleMenu() {
            const menu = document.getElementById('navbarMenu');
            menu.classList.toggle('active');
        }
        
        document.addEventListener('click', function(event) {
            const menu = document.getElementById('navbarMenu');
            const toggle = document.querySelector('.navbar-toggle');
            
            if (!menu.contains(event.target) && !toggle.contains(event.target)) {
                menu.classList.remove('active');
            }
        });
    </script>

    <?php include __DIR__ . '/_footer.php'; ?>
</body>
</html>
