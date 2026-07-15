<?php
/**
 * 信鸽之家 - 鸽舍展厅详情页（SEO优化版）
 */

require_once dirname(__DIR__) . '/app/config/config.php';

extract($data);

$page_title = $pageTitle ?? (h($shop['name']) . ' | ' . ($shop['province'] ?? '') . ($shop['city'] ?? '') . '鸽舍 | ' . SITE_NAME);

// SEO 元信息
$_shop_location = ($shop['province'] ?? '') . ($shop['city'] ?? '');
$meta_description = h($shop['name']) . '，' . $_shop_location . '优质鸽舍';
if (!empty($shop['description'])) {
    $meta_description .= '，' . mb_substr(strip_tags($shop['description']), 0, 80);
}
$meta_description .= '。查看该鸽舍的铭鸽展厅、血统信息、配对记录等。';

$meta_keywords = h($shop['name']) . ',' . $_shop_location . '鸽舍,' . KEYWORDS_SHOPS;

$og_url = 'https://www.xgjia.com/shop/' . $shop['id'] . '.html';
$og_image = !empty($shop['logo']) ? h($shop['logo']) : '';

// JSON-LD - LocalBusiness
$ld_business = [
    '@context' => 'https://schema.org',
    '@type' => 'LocalBusiness',
    'name' => $shop['name'],
    'description' => $shop['description'] ?? '',
];
if (!empty($shop['province']) || !empty($shop['city'])) {
    $ld_business['address'] = [
        '@type' => 'PostalAddress',
        'addressLocality' => ($shop['city'] ?? '') . ($shop['address'] ?? ''),
        'addressRegion' => $shop['province'] ?? '',
    ];
}
if ($og_image) {
    $ld_business['image'] = $og_image;
}
if (!empty($shop['lat']) && !empty($shop['lng'])) {
    $ld_business['geo'] = [
        '@type' => 'GeoCoordinates',
        'latitude' => floatval($shop['lat']),
        'longitude' => floatval($shop['lng']),
    ];
}
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
    <link rel="canonical" href="<?php echo h($og_url); ?>">
    
    <!-- Open Graph -->
    <meta property="og:type" content="business">
    <meta property="og:title" content="<?php echo h($page_title); ?>">
    <meta property="og:description" content="<?php echo h($meta_description); ?>">
    <meta property="og:url" content="<?php echo h($og_url); ?>">
    <meta property="og:site_name" content="<?php echo SITE_NAME; ?>">
    <?php if ($og_image): ?>
    <meta property="og:image" content="<?php echo h($og_image); ?>">
    <?php endif; ?>
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="<?php echo h($page_title); ?>">
    <meta name="twitter:description" content="<?php echo h($meta_description); ?>">
    
    <!-- JSON-LD -->
    <script type="application/ld+json"><?php echo json_encode($ld_business, JSON_UNESCAPED_UNICODE); ?></script>
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/public/css/b-scheme.css">
<!-- BreadcrumbList -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "BreadcrumbList",
        "itemListElement": [
            {"@type": "ListItem", "position": 1, "name": "首页", "item": "https://www.xgjia.com"},
            {"@type": "ListItem", "position": 2, "name": "铭鸽展厅", "item": "https://www.xgjia.com/shop/"},
            {"@type": "ListItem", "position": 3, "name": "<?php echo h($shop['name'] ?? '商家详情'); ?>"}
        ]
    }
    </script>
</head>
<body>

<?php include __DIR__ . '/_head.php'; ?>

<!-- page-shop wrapper -->
<div class="page-shop">

<!-- 展厅头部 -->
<div class="shop-hero">
    <div class="shop-hero-overlay"></div>
    <div class="container">
        <div class="shop-hero-content">
            <div class="shop-avatar-large">
                <?php if ($shop["avatar"]): ?>
                <img loading="lazy" src="<?php echo h($shop["avatar"]); ?>" alt="<?php echo h($shop["name"]); ?>">
                <?php else: ?>
                <?php echo mb_substr($shop["name"], 0, 1); ?>
                <?php endif; ?>
            </div>
            <div class="shop-hero-info">
                <h1 class="shop-hero-name"><?php echo h($shop["name"]); ?></h1>
                <div class="shop-hero-badges">
                    <?php if ($shop["is_certified"]): ?><span class="badge-certified"><i class="fas fa-certificate"></i> 认证展厅</span><?php endif; ?>
                    <?php if (!empty($isOwner)): ?>
                    <a href="/shop/edit/<?php echo intval($shop['id']); ?>/" class="badge-claim" style="text-decoration: none;"><i class="fas fa-edit"></i> 编辑展厅信息</a>
                    <?php elseif (empty($shop["user_id"]) || $shop["user_id"] == 0): ?>
                    <button type="button" class="badge-claim" onclick="openClaimModal('shop', <?php echo intval($shop['id']); ?>)"><i class="fas fa-hand-point-up"></i> 认领此展厅</button>
                    <?php endif; ?>
                </div>
                <div class="shop-hero-location"><i class="fas fa-map-marker-alt"></i> <?php echo h(($shop["province"] ?? "") . " " . ($shop["city"] ?? "") . " " . ($shop["address"] ?? "")); ?></div>
                <div class="shop-hero-stats">
                    <div class="shop-hero-stat">
                        <div class="shop-hero-stat-value"><?php echo $pigeonTotal; ?></div>
                        <div class="shop-hero-stat-label">展品铭鸽</div>
                    </div>
                    <div class="shop-hero-stat">
                        <div class="shop-hero-stat-value"><?php echo count($categories); ?></div>
                        <div class="shop-hero-stat-label">血系分类</div>
                    </div>
                    <div class="shop-hero-stat">
                        <div class="shop-hero-stat-value"><?php echo h($shop["province"] ?? "-"); ?></div>
                        <div class="shop-hero-stat-label">所在省份</div>
                    </div>
                </div>
                <?php if ($shop["contact_phone"]): ?>
                <button class="shop-contact-btn" onclick="alert('联系电话: <?php echo h(addslashes($shop['contact_phone'])); ?>')"><i class="fas fa-phone"></i> 联系展厅</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <div class="shop-content">
        <!-- 左侧：展品列表 -->
        <div class="main-content">

            <!-- 筛选 -->
            <div class="pigeon-filter">
                <form method="GET" action="/shop/<?php echo $shop["id"]; ?>.html">
                    <label>血系</label>
                    <select name="category" class="filter-select">
                        <option value="">全部血系</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo h($cat["name"]); ?>" <?php echo $filterCategory === $cat["name"] ? "selected" : ""; ?>><?php echo h($cat["name"]); ?> (<?php echo $cat["pigeon_count"]; ?>)</option>
                        <?php endforeach; ?>
                    </select>

                    <label>性别</label>
                    <select name="gender" class="filter-select">
                        <option value="">全部</option>
                        <option value="1" <?php echo $filterGender === "1" ? "selected" : ""; ?>>雄</option>
                        <option value="2" <?php echo $filterGender === "2" ? "selected" : ""; ?>>雌</option>
                    </select>

                    <input type="text" name="bloodline" placeholder="搜索血统..." value="<?php echo h($filterBloodline); ?>" style="width:140px;">

                    <button type="submit" class="pigeon-filter-btn"><i class="fas fa-filter"></i> 筛选</button>
                </form>
            </div>

            <!-- 展品列表 -->
            <?php if (empty($pigeons)): ?>
            <div class="empty-state">
                <i class="fas fa-dove"></i>
                <p style="margin-top:10px;">暂无符合条件的展品</p>
            </div>
            <?php else: ?>
            <div class="pigeon-grid">
                <?php foreach ($pigeons as $p): ?>
                <a href="/pigeon/<?php echo $p["id"]; ?>.html" class="pigeon-card" style="text-decoration:none;color:inherit;">
                    <div class="pigeon-card-img">
                        <?php
                        $images = json_decode($p["images"] ?? "[]", true) ?: [];
                        if (!empty($images[0])): ?>
                        <img loading="lazy" src="<?php echo h($images[0]); ?>" alt="<?php echo h($p["name"]); ?>">
                        <?php else: ?>
                        <i class="fas fa-dove" style="font-size:40px;color:#d1d5db;"></i>
                        <?php endif; ?>
                        <?php
                        $genderLabel = ""; $genderClass = "unknown";
                        $g = intval($p["gender"] ?? 0);
                        if ($g == 1) { $genderLabel = "♂ 雄"; $genderClass = "male"; }
                        elseif ($g == 2) { $genderLabel = "♀ 雌"; $genderClass = "female"; }
                        if ($genderLabel): ?>
                        <span class="gender-badge <?php echo $genderClass; ?>"><?php echo $genderLabel; ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="pigeon-card-body">
                        <div class="pigeon-card-name" title="<?php echo h($p["name"]); ?>"><?php echo h($p["name"] ?? "未命名"); ?></div>
                        <?php if (!empty($p["ring_number"])): ?>
                        <div class="pigeon-card-ring"><i class="fas fa-ring"></i> <?php echo h($p["ring_number"]); ?></div>
                        <?php endif; ?>
                        <?php if (!empty($p["bloodline"])): ?>
                        <div class="pigeon-card-bloodline"><i class="fas fa-dna"></i> <?php echo h($p["bloodline"]); ?></div>
                        <?php endif; ?>
                        <div class="pigeon-card-tags">
                            <?php if (!empty($p["color"])): ?><span class="pigeon-tag color"><?php echo h($p["color"]); ?></span><?php endif; ?>
                            <?php if (!empty($p["eye_type"])): ?><span class="pigeon-tag eye"><?php echo h($p["eye_type"]); ?></span><?php endif; ?>
                            <?php if (!empty($p["category"])): ?><span class="pigeon-tag bloodline"><?php echo h($p["category"]); ?></span><?php endif; ?>
                        </div>
                    </div>
                    <div class="pigeon-card-footer">
                        <span><?php echo h($p["category"] ?? ""); ?></span>
                        <span><?php echo $p["views"] ?? 0; ?><i class="fas fa-eye" style="margin-left:3px;"></i></span>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>

            <?php echo renderPagination($pigeonPage ?? 1, $pigeonPages ?? 1); ?>
            <?php endif; ?>
        </div>

        <!-- 右侧侧边栏 -->
        <div class="sidebar">
            <!-- 简介 -->
            <?php
            $_desc = $shop["description"] ?? '';
            $_desc = preg_replace('/^.*?shop_id:\s*\d+.*$/mu', '', $_desc);
            $_desc = preg_replace('/^Email:\s*\S+.*$/mu', '', $_desc);
            $_desc = trim($_desc);
            if ($_desc): ?>
            <div class="content-card">
                <h2><i class="fas fa-info-circle"></i> 展厅简介</h2>
                <p style="font-size:13px;line-height:1.7;color:#555;"><?php echo h($_desc); ?></p>
            </div>
            <?php endif; ?>

            <!-- 联系方式 -->
            <div class="content-card">
                <h2><i class="fas fa-phone"></i> 联系方式</h2>
                <?php if ($shop["contact_name"]): ?>
                <div class="contact-item"><i class="fas fa-user"></i> <?php echo h($shop["contact_name"]); ?></div>
                <?php endif; ?>
                <?php if ($shop["contact_phone"]): ?>
                <div class="contact-item"><i class="fas fa-phone"></i> <?php echo h($shop["contact_phone"]); ?></div>
                <?php endif; ?>
                <?php if ($shop["address"]): ?>
                <div class="contact-item"><i class="fas fa-map-marker-alt"></i> <?php echo h($shop["address"]); ?></div>
                <?php endif; ?>
                <?php if ($shop["website"]): ?>
                <div class="contact-item"><i class="fas fa-globe"></i> <a href="<?php echo h($shop["website"]); ?>" target="_blank" style="color:var(--shop-primary-light);"><?php echo h($shop["website"]); ?></a></div>
                <?php endif; ?>
            </div>

            <!-- 血系分类 -->
            <?php if (!empty($categories)): ?>
            <div class="content-card">
                <h2><i class="fas fa-dna"></i> 血系分类</h2>
                <div>
                    <a href="/shop/<?php echo $shop["id"]; ?>.html" class="bloodline-tag <?php echo !$filterCategory ? "active" : ""; ?>">全部 (<?php echo $pigeonTotal; ?>)</a>
                    <?php foreach ($categories as $cat): ?>
                    <a href="/shop/<?php echo $shop["id"]; ?>.html?category=<?php echo urlencode($cat["name"]); ?>" class="bloodline-tag <?php echo $filterCategory === $cat["name"] ? "active" : ""; ?>"><?php echo h($cat["name"]); ?> (<?php echo $cat["pigeon_count"]; ?>)</a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- 同省展厅 -->
            <?php if (!empty($relatedShops)): ?>
            <div class="content-card">
                <h2><i class="fas fa-store"></i> 同省展厅</h2>
                <?php foreach ($relatedShops as $rs): ?>
                <?php if ($rs["id"] != $shop["id"]): ?>
                <div class="related-shop">
                    <a href="/shop/<?php echo $rs["id"]; ?>.html">
                        <div class="related-shop-name"><?php echo h($rs["name"]); ?></div>
                        <div class="related-shop-meta"><i class="fas fa-dove"></i> <?php echo $rs["pigeon_count"] ?? 0; ?>羽 · <?php echo h($rs["province"] ?? ""); ?></div>
                    </a>
                </div>
                <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

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

<script>
    function toggleMenu() {
        document.getElementById("navbarMenu").classList.toggle("active");
    }
    document.addEventListener("click", function(e) {
        var menu = document.getElementById("navbarMenu");
        var toggle = document.querySelector(".navbar-toggle");
        if (!menu.contains(e.target) && !toggle.contains(e.target)) menu.classList.remove("active");
    });

    // ---- 认领弹窗 ----
    function openClaimModal(type, id) {
        <?php if (!isset($_SESSION['user_id'])): ?>
        if (confirm('请先登录后再认领')) location.href = '/login';
        return;
        <?php endif; ?>
        document.getElementById('claimTargetType').value = type;
        document.getElementById('claimTargetId').value = id;
        document.getElementById('claimModal').style.display = 'flex';
    }
    function closeClaimModal() {
        document.getElementById('claimModal').style.display = 'none';
    }
    function submitClaim() {
        var fd = new FormData(document.getElementById('claimForm'));
        fetch('/claim?action=submit', { method: 'POST', body: fd })
            .then(function(r) { return r.json(); })
            .then(function(d) {
                if (d.success) {
                    closeClaimModal();
                    alert(d.message);
                    location.reload();
                } else if (d.code === 'membership_required') {
                    closeClaimModal();
                    if (confirm(d.message + '\n是否前往升级会员？')) location.href = d.redirect;
                } else {
                    alert(d.message);
                }
            })
            .catch(function() { alert('提交失败，请重试'); });
    }
</script>

<!-- 认领弹窗 -->
<div id="claimModal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.5);align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:12px;width:90%;max-width:480px;max-height:90vh;overflow-y:auto;">
        <div style="padding:20px 24px;border-bottom:1px solid #e5e7eb;display:flex;align-items:center;justify-content:space-between;">
            <h2 style="margin:0;font-size:18px;"><i class="fas fa-hand-holding-heart" style="color:#d4a843;margin-right:8px;"></i>认领申请</h2>
            <button onclick="closeClaimModal()" style="background:none;border:none;font-size:24px;cursor:pointer;color:#9ca3af;">&times;</button>
        </div>
        <form id="claimForm" style="padding:24px;">
            <input type="hidden" id="claimTargetType" name="target_type">
            <input type="hidden" id="claimTargetId" name="target_id">
            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:14px;font-weight:600;margin-bottom:6px;">真实姓名 <span style="color:#ef4444;">*</span></label>
                <input type="text" name="real_name" required style="width:100%;padding:10px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:14px;" placeholder="请填写您的真实姓名">
            </div>
            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:14px;font-weight:600;margin-bottom:6px;">联系电话 <span style="color:#ef4444;">*</span></label>
                <input type="tel" name="phone" required style="width:100%;padding:10px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:14px;" placeholder="请填写联系电话">
            </div>
            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:14px;font-weight:600;margin-bottom:6px;">微信号</label>
                <input type="text" name="wechat" style="width:100%;padding:10px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:14px;" placeholder="选填">
            </div>
            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:14px;font-weight:600;margin-bottom:6px;">申请理由 <span style="color:#ef4444;">*</span></label>
                <textarea name="reason" required minlength="10" rows="3" style="width:100%;padding:10px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:14px;resize:vertical;" placeholder="请说明您为什么是此展厅的商家（不少于10字）"></textarea>
            </div>
            <button type="button" onclick="submitClaim()" style="width:100%;padding:12px;border:none;border-radius:8px;background:linear-gradient(135deg,#5b2c8e,#7c3aed);color:#fff;font-size:16px;font-weight:600;cursor:pointer;">提交认领申请</button>
        </form>
    </div>
</div>


    
</div><!-- /page-shop -->

<?php include __DIR__ . '/_footer.php'; ?>
</body>
</html>