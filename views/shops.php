<?php
/**
 * 信鸽之家 - 鸽舍展厅列表页（SEO优化版）
 */

require_once dirname(__DIR__) . '/app/config/config.php';

extract($data);

$page_title = $pageTitle ?? '鸽舍展厅' . (($page ?? 1) > 1 ? ' - 第' . intval($page) . '页' : '') . ' - 优质鸽舍大全 | ' . SITE_NAME;
$total = $total ?? 0;

// SEO 元信息
$meta_description = "浏览全国{$total}家优质鸽舍信息，展示名家血统、获奖铭鸽。支持按地区筛选，找到您心仪的鸽舍。";
$meta_keywords = '鸽舍展厅,名鸽展厅,' . KEYWORDS_SHOPS;
$canonical_url = 'https://www.xgjia.com/shop/' . ($_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '');

// JSON-LD - ItemList
$ld_items = [];
foreach (array_slice($shops ?? [], 0, 10) as $i => $s) {
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
    <style>
        :root {
            --shop-primary: #5b2c8e;
            --shop-primary-light: #7c3aed;
            --shop-accent: #d4a843;
            --shop-accent-light: #f0c75e;
            --bg: #f0f2f5;
            --card-bg: #fff;
            --text: #2c3e50;
            --text-light: #8e99a4;
            --border: #e1e5e9;
            --success: #27ae60;
            --radius: 10px;
            --shadow: 0 2px 12px rgba(91,44,142,.08);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: var(--bg); color: var(--text); }
        .container { max-width: 1200px; margin: 0 auto; padding: 0 16px; }

        /* 导航栏和底部导航使用 b-scheme.css 白底风格 */

        /* 页面头部 */
        .page-hero {
            background: linear-gradient(135deg, #3b1a5e 0%, #5b2c8e 40%, #7c3aed 100%);
            padding: 40px 0 32px;
            color: #fff;
            position: relative;
            overflow: hidden;
        }
        .page-hero::after {
            content: ""; position: absolute; bottom: 0; left: 0; right: 0; height: 3px;
            background: linear-gradient(90deg, var(--shop-accent), var(--shop-accent-light), var(--shop-accent));
        }
        .page-hero h1 { font-size: 30px; font-weight: 700; }
        .page-hero h1 i { margin-right: 8px; color: var(--shop-accent-light); }
        .page-hero p { font-size: 14px; opacity: .7; margin-top: 6px; }

        /* 筛选 */
        .filter-section {
            background: var(--card-bg); border-radius: var(--radius);
            padding: 20px 24px; margin: 20px 0; box-shadow: var(--shadow);
        }
        .filter-row { display: flex; gap: 12px; align-items: center; flex-wrap: wrap; }
        .filter-row label { font-size: 14px; color: var(--text-light); min-width: 50px; }
        .filter-select, .filter-input {
            padding: 9px 14px; border: 1px solid var(--border); border-radius: 6px;
            font-size: 14px; background: #fff; transition: border-color .2s;
        }
        .filter-select:focus, .filter-input:focus { border-color: var(--shop-primary-light); outline: none; }
        .filter-select { min-width: 120px; }
        .filter-input { width: 200px; }
        .filter-btn {
            padding: 9px 22px;
            background: linear-gradient(135deg, var(--shop-primary), var(--shop-primary-light));
            color: #fff; border: none; border-radius: 6px; cursor: pointer;
            font-size: 14px; font-weight: 600; transition: transform .2s;
        }
        .filter-btn:hover { transform: translateY(-1px); }
        .filter-tags { display: flex; gap: 8px; margin-top: 14px; flex-wrap: wrap; }
        .filter-tag {
            padding: 6px 16px; border-radius: 20px; font-size: 13px; cursor: pointer;
            border: 1px solid var(--border); color: var(--text); background: #fff;
            transition: all .2s; text-decoration: none;
        }
        .filter-tag:hover, .filter-tag.active {
            background: var(--shop-primary); color: #fff; border-color: var(--shop-primary);
        }

        /* 热门展厅 */
        .hot-shops { margin: 24px 0; }
        .section-title {
            font-size: 18px; font-weight: 700; margin-bottom: 18px;
            display: flex; align-items: center; gap: 8px;
        }
        .section-title i { color: var(--shop-accent); }
        .hot-shop-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
        .hot-shop-card {
            background: var(--card-bg); border-radius: var(--radius); padding: 18px;
            box-shadow: var(--shadow); cursor: pointer; transition: transform .2s, box-shadow .2s;
        }
        .hot-shop-card:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(91,44,142,.12); }
        .hot-shop-card .shop-avatar {
            width: 48px; height: 48px; border-radius: 50%;
            background: linear-gradient(135deg, var(--shop-primary), var(--shop-primary-light));
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 20px; font-weight: 700; flex-shrink: 0;
        }
        .hot-shop-card .shop-avatar img { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; }
        .hot-shop-card .shop-header { display: flex; gap: 12px; align-items: center; margin-bottom: 8px; }
        .hot-shop-card .shop-name { font-size: 16px; font-weight: 600; }
        .hot-shop-card .shop-meta { font-size: 13px; color: var(--text-light); }
        .hot-shop-card .shop-meta i { color: var(--shop-accent); }
        .hot-shop-card .certified-badge {
            background: linear-gradient(135deg, var(--shop-accent), var(--shop-accent-light));
            color: #1a2a3a; font-size: 12px; padding: 2px 8px; border-radius: 4px; font-weight: 600;
        }

        /* 热门铭鸽 */
        .hot-pigeons { margin: 24px 0; }
        .hot-pigeon-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; }
        .hot-pigeon-card {
            display: flex; align-items: center; gap: 10px; padding: 12px 14px;
            background: var(--card-bg); border-radius: var(--radius);
            box-shadow: var(--shadow); text-decoration: none; color: inherit;
            transition: transform .15s, box-shadow .15s;
        }
        .hot-pigeon-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(91,44,142,.1); }
        .hot-pigeon-thumb { width: 48px; height: 48px; border-radius: 8px; overflow: hidden; background: #f3f4f6; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 24px; }
        .hot-pigeon-thumb img { width: 100%; height: 100%; object-fit: cover; }
        .hot-pigeon-info { flex: 1; min-width: 0; }
        .hot-pigeon-name { font-size: 14px; font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .hot-pigeon-meta { font-size: 12px; color: var(--text-light); margin-top: 2px; }
        @media(max-width:768px) { .hot-pigeon-grid { grid-template-columns: repeat(2, 1fr); } }
        @media(max-width:480px) { .hot-pigeon-grid { grid-template-columns: 1fr; } }

        /* 展厅列表 */
        .shops-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin: 20px 0; }
        .shop-card {
            background: var(--card-bg); border-radius: var(--radius);
            box-shadow: var(--shadow); transition: transform .2s, box-shadow .2s;
            overflow: hidden;
        }
        .shop-card:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(91,44,142,.14); }
        .shop-card-header {
            background: linear-gradient(135deg, #3b1a5e, #5b2c8e 40%, #7c3aed);
            padding: 22px; color: #fff; position: relative;
            display: flex; align-items: center; gap: 14px;
        }
        .shop-card-header .shop-avatar {
            width: 56px; height: 56px; border-radius: 50%;
            background: rgba(255,255,255,.15); border: 2px solid rgba(212,168,67,.4);
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 22px; font-weight: 700; flex-shrink: 0;
        }
        .shop-card-header .shop-avatar img { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; }
        .shop-card-header .shop-name { font-size: 20px; font-weight: 700; }
        .shop-card-header .shop-location { font-size: 14px; opacity: .8; margin-top: 3px; }
        .shop-card-header .type-badge {
            position: absolute; top: 16px; right: 16px;
            background: rgba(212,168,67,.2); border: 1px solid rgba(212,168,67,.4);
            padding: 3px 10px; border-radius: 12px; font-size: 11px; color: var(--shop-accent-light);
        }
        .shop-card-header .claim-badge {
            position: absolute; bottom: 12px; right: 16px;
            background: rgba(212,168,67,.15); border: 1px solid var(--shop-accent);
            padding: 3px 10px; border-radius: 12px; font-size: 11px;
            color: var(--shop-accent-light); text-decoration: none;
        }
        .shop-card-body { padding: 20px; }
        .shop-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 14px; }
        .shop-stat { text-align: center; }
        .shop-stat-value { font-size: 18px; font-weight: 700; color: var(--shop-primary); }
        .shop-stat-label { font-size: 12px; color: var(--text-light); }
        .shop-info-row {
            display: flex; justify-content: space-between;
            padding: 8px 0; border-bottom: 1px solid var(--border); font-size: 14px;
        }
        .shop-info-row:last-child { border-bottom: none; }
        .shop-info-row .label { color: var(--text-light); }
        .shop-info-row .value { font-weight: 500; }
        .shop-card-footer {
            padding: 12px 20px; display: flex; justify-content: space-between;
            align-items: center; border-top: 1px solid var(--border);
        }

        @media (max-width: 768px) {
            .page-hero h1 { font-size: 22px; }
            .shops-grid { grid-template-columns: 1fr; gap: 14px; }
            .hot-shop-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
            .hot-shop-card { padding: 14px; }
            .hot-shop-card .shop-name { font-size: 14px; }
            .hot-shop-card .shop-meta { font-size: 12px; }
            .hot-shop-card .shop-avatar { width: 40px; height: 40px; font-size: 16px; }
            .shop-card-header { padding: 16px; }
            .shop-card-header .shop-name { font-size: 16px; }
            .shop-card-header .shop-location { font-size: 12px; }
            .shop-card-header .shop-avatar { width: 44px; height: 44px; font-size: 18px; }
            .shop-card-header .type-badge { top: 10px; right: 10px; font-size: 10px; padding: 2px 8px; }
            .shop-card-body { padding: 14px; }
            .shop-stats { grid-template-columns: repeat(3, 1fr); gap: 8px; }
            .shop-stat-value { font-size: 15px; }
            .shop-stat-label { font-size: 11px; }
            .filter-row { flex-direction: column; }
            .filter-input { width: 100%; }
            .filter-section { padding: 16px; }
        }
    </style>
</head>
<body>

<?php include __DIR__ . '/_head.php'; ?>

<!-- 页面头部 -->
<div class="page-hero">
    <div class="container">
        <h1><i class="fas fa-crown"></i> 铭鸽展厅</h1>
        <p>汇集全国名家铭鸽，血统·赛绩·配对一站查询</p>
    </div>
</div>

<div class="container">

    <!-- 筛选区域 -->
    <div class="filter-section">
        <form method="GET" action="/shop/">
            <div class="filter-row">
                <label>省份</label>
                <select name="province" class="filter-select">
                    <option value="">全部省份</option>
                    <?php foreach ($provinces as $prov): ?>
                    <option value="<?php echo h($prov); ?>" <?php echo $currentProvince === $prov ? "selected" : ""; ?>><?php echo h($prov); ?></option>
                    <?php endforeach; ?>
                </select>

                <input type="text" name="keyword" class="filter-input" placeholder="搜索展厅名称..." value="<?php echo h($currentKeyword); ?>">

                <?php if ($isCertified): ?>
                <input type="hidden" name="certified" value="1">
                <?php endif; ?>

                <button type="submit" class="filter-btn"><i class="fas fa-search"></i> 搜索</button>
            </div>

            <div class="filter-tags">
                <a href="/shop/" class="filter-tag <?php echo !$currentProvince && !$isCertified ? "active" : ""; ?>">全部展厅</a>
                <a href="/shop/?certified=1" class="filter-tag <?php echo $isCertified ? "active" : ""; ?>">✓ 认证展厅</a>
                <?php if (!empty($hotShops)): ?>
                <a href="/shop/?order=pigeon_count" class="filter-tag">展品最多</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- 热门展厅 -->
    <?php if (!empty($hotShops) && !$currentProvince && !$currentKeyword): ?>
    <div class="hot-shops">
        <div class="section-title"><i class="fas fa-fire"></i> 热门展厅推荐</div>
        <div class="hot-shop-grid">
            <?php foreach ($hotShops as $hot): ?>
            <a href="/shop/<?php echo $hot["id"]; ?>.html" class="hot-shop-card" style="text-decoration:none;color:inherit;">
                <div class="shop-header">
                    <div class="shop-avatar">
                        <?php if ($hot["avatar"]): ?>
                        <img loading="lazy" src="<?php echo h($hot["avatar"]); ?>" alt="<?php echo h($hot["name"]); ?> 头像">
                        <?php else: ?>
                        <?php echo mb_substr($hot["name"], 0, 1); ?>
                        <?php endif; ?>
                    </div>
                    <div>
                        <div class="shop-name">
                            <?php if ($hot["is_certified"]): ?><span class="certified-badge">认证</span> <?php endif; ?>
                            <?php echo h($hot["name"]); ?>
                        </div>
                        <div class="shop-meta">
                            <span><i class="fas fa-map-marker-alt"></i> <?php echo h($hot["province"] ?? "未知"); ?></span>
                            <span><i class="fas fa-dove"></i> <?php echo $hot["pigeon_count"] ?? 0; ?>羽</span>
                        </div>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- 展厅列表 -->
    <div class="section-title"><i class="fas fa-store"></i> 展厅列表（共 <?php echo $total; ?> 家）</div>

    <?php if (empty($shops)): ?>
    <div style="text-align:center;padding:60px 0;color:var(--text-light);">
        <i class="fas fa-store" style="font-size:48px;opacity:.4;"></i>
        <p style="margin-top:12px;">暂无符合条件的展厅</p>
    </div>
    <?php else: ?>
    <div class="shops-grid">
        <?php foreach ($shops as $shop): ?>
        <a href="/shop/<?php echo $shop["id"]; ?>.html" class="shop-card" style="text-decoration:none;color:inherit;">
            <div class="shop-card-header">
                <div class="shop-avatar">
                    <?php if ($shop["avatar"]): ?>
                    <img loading="lazy" src="<?php echo h($shop["avatar"]); ?>" alt="<?php echo h($shop["name"]); ?> 头像">
                    <?php else: ?>
                    <?php echo mb_substr($shop["name"], 0, 1); ?>
                    <?php endif; ?>
                </div>
                <div>
                    <div class="shop-name"><?php echo h($shop["name"]); ?></div>
                    <div class="shop-location"><i class="fas fa-map-marker-alt"></i> <?php echo h($shop["province"] . " " . ($shop["city"] ?? "")); ?></div>
                </div>
                <span class="type-badge"><?php echo $shop["pigeon_count"] ?? 0; ?>羽展品</span>
                <?php if (empty($shop["user_id"]) || $shop["user_id"] == 0): ?>
                <span class="claim-badge"><i class="fas fa-hand-point-up"></i> 认领</span>
                <?php endif; ?>
            </div>
            <div class="shop-card-body">
                <div class="shop-stats">
                    <div class="shop-stat">
                        <div class="shop-stat-value"><?php echo $shop["pigeon_count"] ?? 0; ?></div>
                        <div class="shop-stat-label">展品数</div>
                    </div>
                    <div class="shop-stat">
                        <div class="shop-stat-value"><?php echo h($shop["province"] ?? "-"); ?></div>
                        <div class="shop-stat-label">所在省份</div>
                    </div>
                    <div class="shop-stat">
                        <div class="shop-stat-value"><?php echo $shop["is_certified"] ? "是" : "否"; ?></div>
                        <div class="shop-stat-label">认证</div>
                    </div>
                </div>
                <?php
                $_desc = $shop["description"] ?? '';
                // 过滤掉数据来源元信息
                $_desc = preg_replace('/^.*?shop_id:\s*\d+.*$/mu', '', $_desc);
                $_desc = preg_replace('/^Email:\s*\S+.*$/mu', '', $_desc);
                $_desc = trim($_desc);
                if ($_desc): ?>
                <p style="font-size:13px;color:var(--text-light);line-height:1.6;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;"><?php echo h($_desc); ?></p>
                <?php endif; ?>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php echo renderPagination($page, $totalPages); ?>

</div>

<!-- 热门铭鸽内链 -->
<?php if(!empty($hotPigeons)): ?>
<div class="hot-pigeons">
    <div class="section-title"><i class="fas fa-fire" style="color:#e74c3c;"></i> 热门铭鸽</div>
    <div class="hot-pigeon-grid">
        <?php foreach($hotPigeons as $hp): ?>
        <?php
            $hpid = intval($hp['id']);
            $hImgs = json_decode($hp['images'] ?? '[]', true) ?: [];
            $hImg = $hImgs[0] ?? '';
            $hName = h($hp['name'] ?? '未命名');
            $hBlood = trim($hp['bloodline'] ?? '');
        ?>
        <a href="/pigeon/<?php echo $hpid; ?>.html" class="hot-pigeon-card">
            <div class="hot-pigeon-thumb">
                <?php if($hImg): ?>
                <img src="<?php echo h($hImg); ?>" alt="<?php echo $hName; ?>" loading="lazy">
                <?php else: ?>
                🐦
                <?php endif; ?>
            </div>
            <div class="hot-pigeon-info">
                <div class="hot-pigeon-name"><?php echo $hName; ?></div>
                <?php if($hBlood): ?>
                <div class="hot-pigeon-meta" style="color:var(--shop-accent);font-weight:600;"><?php echo h($hBlood); ?></div>
                <?php endif; ?>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

</div>

    <?php include __DIR__ . '/_footer.php'; ?>
