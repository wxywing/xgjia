<?php
/**
 * P2: 赛事浏览大全 /race/browse/
 * SEO: 1420 场赛事分页浏览，长尾关键词覆盖
 */
$total = $data['total'] ?? 0;
$totalPages = $data['total_pages'] ?? 1;
$races = $data['list'] ?? [];

// Title 动态化
$titleSuffix = '';
if ($page > 1) $titleSuffix = " - 第{$page}页";
if ($keyword) $titleSuffix .= " - 搜索\"{$keyword}\"";
if ($year) $titleSuffix .= " - {$year}年";
$page_title = '赛事大全' . $titleSuffix . ' | 赛事成绩';
$meta_description = '浏览全部 ' . number_format($total) . ' 场信鸽赛事' . ($year ? "（{$year}年）" : '');
$meta_description .= '，可按年份、类型筛选，支持关键词搜索。查公棚赛事成绩、冠军鸽主、分速排行。';
$meta_keywords = '赛事列表,赛事浏览,全部赛事' . ($year ? ',' . $year . '赛季' : '') . ',信鸽比赛,公棚赛事' . ($keyword ? ',' . $keyword : '');
$canonical_url = 'https://www.xgjia.com/race/browse/' . ($page > 1 ? 'page/' . $page . '/' : '');
$og_type = 'website';

// 赛事浏览页 FAQ（GEO SEO）
$browse_faqs = [
    [
        'question' => '信鸽之家收录了多少场公棚赛事？',
        'answer'   => '信鸽之家收录全国各省市公棚赛事，目前可浏览赛事总数超过1400场，支持按年份、类型、关键词多维筛选。',
    ],
    [
        'question' => '如何筛选特定年份的公棚赛事？',
        'answer'   => '在赛事大全页面左侧边栏选择目标年份（如2026年），即可筛选出该年度所有公棚赛事，支持按春/秋季赛进一步细分。',
    ],
    [
        'question' => '如何搜索特定公棚或关键词的赛事？',
        'answer'   => '在赛事大全顶部搜索框输入公棚名称或关键词，即可快速定位相关赛事，支持模糊匹配，结果实时显示。',
    ],
    [
        'question' => '赛事详情页包含哪些信息？',
        'answer'   => '赛事详情页展示该场比赛完整数据：冠军鸽主及分速、参赛羽数、公棚信息、鸽主历届成绩链，以及参赛鸽分速排名列表。',
    ],
    [
        'question' => '如何查看某个城市或省份的所有赛事？',
        'answer'   => '在城市赛事中心选择目标城市，可按城市聚合查看该城市所有公棚赛事；进入省份聚合页可查看该省全部赛事分布。',
    ],
];
$ld_browse_faqpage = [
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => [],
];
foreach ($browse_faqs as $f) {
    $ld_browse_faqpage['mainEntity'][] = [
        '@type' => 'Question',
        'name' => $f['question'],
        'acceptedAnswer' => [
            '@type' => 'Answer',
            'text' => $f['answer'],
        ],
    ];
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include __DIR__ . '/_seo_head.php'; ?>
    <script type="application/ld+json"><?php echo json_encode($ld_browse_faqpage, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/public/css/b-scheme.css">
    <style>
.browse-wrap { background: #f4f6f9; min-height: 100vh; }
.browse-hero {
    background: linear-gradient(135deg, #1a5fa8, #0d3b6e);
    color: #fff; padding: 32px 0 24px;
}
.browse-hero h1 { font-size: 26px; font-weight: 700; margin-bottom: 4px; }
.browse-hero .subtitle { font-size: 13px; opacity: 0.8; }
.browse-hero .breadcrumb { font-size: 12px; opacity: 0.7; margin-bottom: 6px; }
.browse-hero .breadcrumb a { color: rgba(255,255,255,0.8); text-decoration: none; }
.browse-hero .breadcrumb a:hover { text-decoration: underline; }

.browse-filter { 
    background: #fff; border-radius: 10px; padding: 16px 20px; 
    box-shadow: 0 1px 6px rgba(0,0,0,0.06); margin-top: -12px; position: relative; z-index: 2;
}
.filter-form { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
.filter-form input[type="text"] {
    flex: 1; min-width: 180px; padding: 8px 14px; border: 1px solid #ddd; border-radius: 6px; font-size: 13px;
}
.filter-form select, .filter-form button {
    padding: 8px 14px; border: 1px solid #ddd; border-radius: 6px; font-size: 13px; background: #fff;
}
.filter-form button {
    background: #1a5fa8; color: #fff; border-color: #1a5fa8; cursor: pointer; font-weight: 600;
}
.filter-form button:hover { background: #154a85; }

.browse-content { padding: 24px 0; display: grid; grid-template-columns: 1fr 280px; gap: 20px; }
.race-list { display: flex; flex-direction: column; gap: 10px; }
.race-card {
    display: grid; grid-template-columns: 1fr 120px 90px 100px; gap: 12px; align-items: center;
    background: #fff; border-radius: 8px; padding: 14px 18px; box-shadow: 0 1px 4px rgba(0,0,0,0.05);
    text-decoration: none; transition: all 0.2s;
}
.race-card:hover { box-shadow: 0 2px 12px rgba(26,95,168,0.15); transform: translateY(-1px); }
.race-card .rc-main { min-width: 0; }
.race-card .rc-name { font-size: 14px; font-weight: 600; color: #1a5fa8; margin-bottom: 3px; }
.race-card .rc-meta { font-size: 12px; color: #888; display: flex; gap: 12px; flex-wrap: wrap; }
.race-card .rc-meta span { display: inline-flex; align-items: center; gap: 4px; }
.race-card .rc-stat { text-align: right; }
.race-card .rc-stat .val { font-size: 16px; font-weight: 700; color: #2c3e50; }
.race-card .rc-stat .lbl { font-size: 11px; color: #999; }
.race-card .rc-badge { text-align: center; }
.race-card .season-badge {
    display: inline-block; padding: 2px 10px; border-radius: 10px; font-size: 11px; font-weight: 600;
}
.badge-autumn { background: #fff3e0; color: #e65100; }
.badge-spring { background: #e8f5e9; color: #2e7d32; }
.badge-other { background: #f0f0f0; color: #666; }

.race-card .rc-action { text-align: center; }
.race-card .rc-action a {
    display: inline-block; padding: 4px 14px; border-radius: 4px; font-size: 12px; font-weight: 600;
    background: #e8f0fe; color: #1a5fa8; text-decoration: none; transition: all 0.2s;
}
.race-card .rc-action a:hover { background: #1a5fa8; color: #fff; }

/* Sidebar */
.browse-sidebar { display: flex; flex-direction: column; gap: 16px; }
.sidebar-block { background: #fff; border-radius: 10px; padding: 16px; box-shadow: 0 1px 4px rgba(0,0,0,0.05); }
.sidebar-block h3 { font-size: 14px; font-weight: 700; color: #2c3e50; margin-bottom: 10px; padding-bottom: 8px; border-bottom: 2px solid #f4f6f9; }
.sidebar-block h3 i { color: #c9a84c; }
.sidebar-block .year-grid { display: flex; flex-wrap: wrap; gap: 6px; }
.sidebar-block .year-grid a {
    display: inline-block; padding: 3px 10px; border-radius: 4px; font-size: 12px; 
    background: #f4f6f9; color: #555; text-decoration: none; transition: all 0.2s;
}
.sidebar-block .year-grid a:hover, .sidebar-block .year-grid a.active { background: #1a5fa8; color: #fff; }
.sidebar-block .type-links { display: flex; flex-direction: column; gap: 4px; }
.sidebar-block .type-links a {
    display: block; padding: 6px 10px; border-radius: 4px; font-size: 13px; color: #555; text-decoration: none;
}
.sidebar-block .type-links a:hover, .sidebar-block .type-links a.active { background: #e8f0fe; color: #1a5fa8; font-weight: 600; }

/* Pagination */
.browse-pagination { display: flex; gap: 6px; justify-content: center; margin-top: 20px; }
.browse-pagination a, .browse-pagination span {
    min-width: 36px; padding: 6px 4px; text-align: center; border: 1px solid #ddd; border-radius: 6px;
    font-size: 13px; color: #555; text-decoration: none; transition: all 0.15s;
}
.browse-pagination a:hover { background: #e8f0fe; border-color: #1a5fa8; color: #1a5fa8; }
.browse-pagination .active { background: #1a5fa8; color: #fff; border-color: #1a5fa8; font-weight: 700; }
.browse-pagination .ellipsis { border: none; color: #999; cursor: default; }

.stats-mini { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
.stats-mini .s-item { text-align: center; padding: 8px; background: #f8f9fa; border-radius: 6px; }
.stats-mini .s-item .n { font-size: 18px; font-weight: 700; color: #1a5fa8; }
.stats-mini .s-item .t { font-size: 11px; color: #888; }

@media (max-width: 768px) {
    .browse-content { grid-template-columns: 1fr; }
    .browse-hero { padding: 20px 12px !important; }
    .browse-hero h1 { font-size: 18px !important; }
    .browse-hero .subtitle { font-size: 12px !important; }
    .browse-filter { padding: 12px !important; }
    .filter-form { flex-direction: column; gap: 8px !important; }
    .filter-form select, .filter-form input[type="text"] { width: 100% !important; }
    .race-card { grid-template-columns: 1fr; gap: 6px; padding: 12px; }
    .race-card .rc-stat { text-align: left; display: flex; gap: 16px; }
    .race-card .rc-action { text-align: left; }
    .race-list { overflow-x: auto; -webkit-overflow-scrolling: touch; padding-bottom: 8px; }
    .browse-pagination { font-size: 12px; gap: 4px; flex-wrap: wrap; justify-content: center; }
    .browse-pagination a, .browse-pagination span { min-width: 32px; height: 32px; line-height: 32px; }
}
@media (max-width: 480px) {
    .race-card .rc-main { flex-direction: column; gap: 4px; }
    .race-card .rc-badge { position: static; margin-top: 4px; }
    .browse-pagination { gap: 2px; }
    .browse-pagination a, .browse-pagination span { min-width: 28px; height: 28px; line-height: 28px; font-size: 11px; }
}
    </style>
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "BreadcrumbList",
        "itemListElement": [
            {"@type": "ListItem", "position": 1, "name": "首页", "item": "https://www.xgjia.com"},
            {"@type": "ListItem", "position": 2, "name": "赛事成绩", "item": "https://www.xgjia.com/race/"},
            {"@type": "ListItem", "position": 3, "name": "赛事大全"}
        ]
    }
    </script>
</head>
<body>
<div class="browse-wrap">
<?php include __DIR__ . '/_head.php'; ?>

<div class="browse-hero">
    <div class="container">
        <div class="breadcrumb">
            <a href="/race/">赛事成绩</a> › 赛事大全
        </div>
        <h1>赛事大全</h1>
        <div class="subtitle">浏览全部 <?php echo number_format($total); ?> 场信鸽公棚赛事<?php echo $year ? ' · ' . $year . '年' : ''; ?></div>
    </div>
</div>

<div class="container">
    <!-- Filter bar -->
    <div class="browse-filter">
        <form class="filter-form" method="get" action="/race/browse/">
            <input type="text" name="q" value="<?php echo htmlspecialchars($keyword ?? ''); ?>" placeholder="搜索赛事名称、公棚名称或省份...">
            <select name="year">
                <option value="">全部年份</option>
                <?php foreach ($seasons as $s): 
                    $sy = $s['season_year'] ?? '';
                    if (!$sy) continue;
                    $sel = ($year == $sy) ? ' selected' : '';
                ?>
                <option value="<?php echo $sy; ?>"<?php echo $sel; ?>><?php echo $sy; ?>年</option>
                <?php endforeach; ?>
            </select>
            <select name="type">
                <option value="">全部类型</option>
                <option value="autumn"<?php echo ($type === 'autumn') ? ' selected' : ''; ?>>秋赛</option>
                <option value="spring"<?php echo ($type === 'spring') ? ' selected' : ''; ?>>春赛</option>
            </select>
            <button type="submit"><i class="fas fa-search"></i> 筛选</button>
            <?php if ($keyword || $year || $type): ?>
            <a href="/race/browse/" style="font-size:12px;color:#888;text-decoration:none;padding:8px;">清空</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="browse-content">
        <!-- Left: Race list -->
        <div>
            <?php if (empty($races)): ?>
            <div style="text-align:center;padding:60px 20px;color:#999;">
                <i class="fas fa-inbox" style="font-size:48px;color:#ddd;display:block;margin-bottom:12px;"></i>
                <p>没有找到匹配的赛事</p>
                <?php if ($keyword || $year || $type): ?>
                <a href="/race/browse/" style="color:#1a5fa8;">清除筛选条件</a>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div class="race-list">
                <?php foreach ($races as $race): 
                    $rid = intval($race['id'] ?? 0);
                    $raceUrl = '/race/' . $rid . '.html';
                    $loftUrl = '/loft/' . intval($race['loft_id'] ?? 0) . '.html';
                    $st = $race['season_type'] ?? '';
                    $badgeCls = ($st === 'autumn') ? 'badge-autumn' : (($st === 'spring') ? 'badge-spring' : 'badge-other');
                    $stLabel = ($st === 'autumn') ? '秋' : (($st === 'spring') ? '春' : ($st ?: '—'));
                ?>
                <a href="<?php echo $raceUrl; ?>" class="race-card">
                    <div class="rc-main">
                        <div class="rc-name"><?php echo htmlspecialchars($race['name'] ?? '未命名赛事'); ?></div>
                        <div class="rc-meta">
                            <span><i class="fas fa-flag"></i> <?php echo htmlspecialchars($race['loft_name'] ?? '—'); ?></span>
                            <?php if (!empty($race['province'])): ?>
                            <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($race['province']); ?></span>
                            <?php endif; ?>
                            <?php if (!empty($race['release_time'])): ?>
                            <span><i class="far fa-calendar-alt"></i> <?php echo date('m-d', strtotime($race['release_time'])); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="rc-stat">
                        <div class="val"><?php echo number_format($race['entry_count'] ?? 0); ?></div>
                        <div class="lbl">参赛羽数</div>
                    </div>
                    <div class="rc-badge">
                        <span class="season-badge <?php echo $badgeCls; ?>"><?php echo $stLabel; ?></span>
                        <?php if (!empty($race['season_year'])): ?>
                        <div style="font-size:12px;color:#999;margin-top:2px;"><?php echo $race['season_year']; ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="rc-action">
                        <span>查看成绩 →</span>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): 
                $baseUrl = '/race/browse/page/';
                $queryParams = [];
                if ($year) $queryParams[] = 'year=' . urlencode($year);
                if ($type) $queryParams[] = 'type=' . urlencode($type);
                if ($keyword) $queryParams[] = 'q=' . urlencode($keyword);
                $queryStr = $queryParams ? '?' . implode('&', $queryParams) : '';
            ?>
            <div class="browse-pagination">
                <!-- Prev -->
                <?php if ($page > 1): ?>
                <a href="<?php echo $baseUrl . ($page - 1) . '/' . $queryStr; ?>">‹ 上一页</a>
                <?php endif; ?>

                <?php
                $range = 3;
                $start = max(1, $page - $range);
                $end = min($totalPages, $page + $range);
                
                if ($start > 1): ?>
                <a href="<?php echo $baseUrl . '1/' . $queryStr; ?>">1</a>
                <?php if ($start > 2): ?><span class="ellipsis">…</span><?php endif; ?>
                <?php endif; ?>

                <?php for ($i = $start; $i <= $end; $i++): ?>
                <?php if ($i == $page): ?>
                <span class="active"><?php echo $i; ?></span>
                <?php else: ?>
                <a href="<?php echo $baseUrl . $i . '/' . $queryStr; ?>"><?php echo $i; ?></a>
                <?php endif; ?>
                <?php endfor; ?>

                <?php if ($end < $totalPages): ?>
                <?php if ($end < $totalPages - 1): ?><span class="ellipsis">…</span><?php endif; ?>
                <a href="<?php echo $baseUrl . $totalPages . '/' . $queryStr; ?>"><?php echo $totalPages; ?></a>
                <?php endif; ?>

                <!-- Next -->
                <?php if ($page < $totalPages): ?>
                <a href="<?php echo $baseUrl . ($page + 1) . '/' . $queryStr; ?>">下一页 ›</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Right sidebar -->
        <div class="browse-sidebar">
            <div class="sidebar-block">
                <h3><i class="fas fa-chart-pie"></i> 赛事统计</h3>
                <div class="stats-mini">
                    <div class="s-item">
                        <div class="n"><?php echo number_format($total); ?></div>
                        <div class="t">总赛事</div>
                    </div>
                    <div class="s-item">
                        <div class="n"><?php echo count($seasons); ?></div>
                        <div class="t">赛季数</div>
                    </div>
                </div>
            </div>

            <?php if (!empty($seasons)): ?>
            <div class="sidebar-block">
                <h3><i class="fas fa-calendar-alt"></i> 按年份</h3>
                <div class="year-grid">
                    <a href="/race/browse/" class="<?php echo !$year ? 'active' : ''; ?>">全部</a>
                    <?php foreach ($seasons as $s): 
                        $sy = $s['season_year'] ?? '';
                        if (!$sy) continue;
                        $active = ($year == $sy) ? ' active' : '';
                    ?>
                    <a href="/race/browse/?year=<?php echo $sy; ?>" class="<?php echo $active; ?>"><?php echo $sy; ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="sidebar-block">
                <h3><i class="fas fa-tag"></i> 按类型</h3>
                <div class="type-links">
                    <a href="/race/browse/" class="<?php echo !$type ? 'active' : ''; ?>">全部赛事</a>
                    <a href="/race/browse/?type=autumn" class="<?php echo ($type === 'autumn') ? 'active' : ''; ?>">🍂 秋赛</a>
                    <a href="/race/browse/?type=spring" class="<?php echo ($type === 'spring') ? 'active' : ''; ?>">🌸 春赛</a>
                </div>
            </div>

            <div class="sidebar-block">
                <h3><i class="fas fa-link"></i> 快捷导航</h3>
                <div class="type-links">
                    <a href="/race/champions/"><i class="fas fa-trophy"></i> 冠军鸽榜</a>
                    <a href="/race/champion/"><i class="fas fa-crown"></i> 冠军鸽列表</a>
                    <a href="/race/province/"><i class="fas fa-globe"></i> 省份聚合</a>
                    <a href="/race/city/"><i class="fas fa-city"></i> 城市中心</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/_footer.php'; ?>
</div>
</body>
</html>
