<?php
/**
 * P1: 城市赛事中心 — 城市详情 /race/city/{city}/
 */
$cityName = htmlspecialchars($city ?? '');
$provinceName = htmlspecialchars($cityStats['province'] ?? '');

// 城市静态描述（GEO SEO）
$city_descriptions = [
    '北京' => '北京是华北地区赛鸽重镇，公棚赛集中在通州、顺义、昌平等区，赛事以中长距离为主，分速水平位居全国前列。',
    '上海' => '上海赛鸽历史深厚，崇明岛及周边公棚群集聚，赛鸽运动与国际化接轨，赛事规程严谨，奖金体系完善。',
    '天津' => '天津地处渤海湾，是北方赛鸽枢纽城市，武清、宝坻等区公棚密集，春秋两季赛事频繁。',
    '重庆' => '重庆山地赛鸽特色鲜明，因地形复杂，对赛鸽定向能力要求更高，近年来公棚数量稳步增长。',
    '成都' => '成都为西南赛鸽核心城市，温江、郫县等地公棚集中，川鸽以耐力见长，在长距离赛事中表现突出。',
    '广州' => '广州是珠三角赛鸽中心，天河、白云等区公棚众多，赛事信息化程度高，与港澳赛鸽交流频繁。',
    '深圳' => '深圳赛鸽起步较晚但发展迅速，近年新建公棚注重设施现代化，参赛鸽以快速系为主。',
    '武汉' => '武汉为中部赛鸽枢纽，长江沿岸公棚分布密集，赛事兼容南北赛鸽特点，综合实力强劲。',
    '西安' => '西安赛鸽历史可追溯至唐代，雁塔、长安区公棚群历史悠久，西北赛鸽代表城市。',
    '南京' => '南京是江苏赛鸽核心，浦口、江宁公棚密度高，赛鸽运动与六朝古都文化交融。',
    '郑州' => '郑州为中原赛鸽重镇，中牟、新郑公棚数量众多，是连接南北赛鸽交流的重要节点。',
    '沈阳' => '沈阳是东北赛鸽中心，浑南、苏家屯公棚发达，辽鸽以耐寒、高分速著称。',
    '哈尔滨' => '哈尔滨是中国最北赛鸽城市，冬季赛事极具体育挑战性，齐齐哈尔、绥化公棚群形成寒地赛鸽特色。',
    '长沙' => '长沙为湘江赛鸽核心，雨花、岳麓公棚近年兴起，湘鸽以速度见长，在短距离赛表现活跃。',
    '昆明' => '昆明四季如春，是赛鸽训练圣地，安宁、呈贡公棚享誉全国，云南鸽系适应高原气候能力突出。',
    '济南' => '济南为山东赛鸽核心，市中、历城公棚密集，齐鲁赛鸽兼容并蓄，中长距离赛绩稳定。',
    '青岛' => '青岛沿海赛鸽特色鲜明，即墨、胶州公棚群受海风气候影响，赛鸽定向能力强。',
    '石家庄' => '石家庄为河北赛鸽枢纽，正定、鹿泉公棚连接京津冀赛事网络，冀鸽以稳定分速见长。',
    '太原' => '太原是山西赛鸽核心，迎泽、小店公棚历史悠久，晋鸽耐力和稳定性并重。',
    '呼和浩特' => '呼和浩特是内蒙古赛鸽重镇，新城、赛罕公棚连接草原赛鸽带，蒙鸽以耐粗饲料著称。',
    '南宁' => '南宁为广西赛鸽中心，青秀、西乡塘公棚近年快速发展，桂鸽速度与耐力兼备。',
    '贵阳' => '贵阳山地赛鸽特色突出，云岩、南明公棚因地形差异形成独特的训练体系。',
    '福州' => '福州为福建赛鸽核心，鼓楼、仓山公棚连接海峡两岸赛事，闽鸽速度型选手居多。',
    '厦门' => '厦门赛鸽与台湾交流密切，思明、湖里公棚设施现代，厦鸽以耐海风著称。',
    '南昌' => '南昌为江西赛鸽枢纽，红谷滩、高新公棚近年增长，赣鸽在华中赛事中渐露头角。',
    '合肥' => '合肥是安徽赛鸽核心，庐阳、蜀山公棚连接长三角赛事网络，合鸽稳定性突出。',
    '杭州' => '杭州为浙江赛鸽明珠，西湖、余杭公棚依托长三角经济圈，浙鸽以速度与灵性著称。',
    '宁波' => '宁波沿海赛鸽特色鲜明，镇海、北仑公棚群受海洋性气候影响，鸽系适应性强。',
    '温州' => '温州为浙江赛鸽重镇，瓯海、龙湾公棚近年崛起，温商赛鸽群体资金实力雄厚。',
    '苏州' => '苏州古典与现代交融，园区、吴中公棚依托江苏赛鸽传统，苏鸽耐力与速度兼备。',
    '无锡' => '无锡为苏南赛鸽核心，滨湖、锡山公棚密度高，锡鸽在长三角赛事中排名靠前。',
    '常州' => '常州是江苏赛鸽新锐，钟楼、天宁公棚发展迅速，常鸽以速度见长。',
    '徐州' => '徐州为苏北赛鸽枢纽，铜山、鼓楼公棚连接鲁豫赛事带，徐鸽耐力出色。',
    '大连' => '大连为辽宁沿海赛鸽明珠，甘井子、金州公棚发达，辽南鸽以抗海风著称。',
    '长春' => '长春为吉林赛鸽核心，朝阳、南关公棚因气候形成独特赛鸽风格。',
    '兰州' => '兰州是西北赛鸽枢纽，城关、七里河公棚连接丝路赛鸽带，甘鸽耐干燥气候。',
    '银川' => '银川为宁夏赛鸽重镇，兴庆、西夏公棚依托黄河灌区，塞上鸽适应性强。',
    '西宁' => '西宁是青藏高原赛鸽核心，城东、城西公棚享誉藏区，青海鸽耐高原缺氧。',
    '乌鲁木齐' => '乌鲁木齐为新疆赛鸽枢纽，天山、沙依巴克公棚连接中亚赛事网络。',
    '拉萨' => '拉萨是世界最高赛鸽城市，城关公棚极具挑战性，藏鸽耐高原极端气候。',
    '唐山' => '唐山为河北工业重镇+赛鸽重镇，路南、路北公棚密集，冀东鸽分速稳定。',
    '保定' => '保定是河北历史名城+赛鸽城市，新市、竞秀公棚近年发展迅速。',
    '廊坊' => '廊坊地处京津之间，安次、广阳公棚承接京津冀赛事，燕郊鸽速度型居多。',
    '邢台' => '邢台为河北赛鸽古城，桥东、桥西公棚历史积淀深厚，冀南鸽耐力突出。',
    '邯郸' => '邯郸是河北最南端赛鸽城市，丛台、邯郸县公棚连接晋冀鲁豫赛事带。',
    '潍坊' => '潍坊为山东蔬菜之乡+赛鸽城市，潍城、寒亭公棚近年快速增长。',
    '临沂' => '临沂是山东人口大市+赛鸽重镇，兰山、罗庄公棚参赛羽数规模大。',
    '德州' => '德州为山东北部门户，乐陵、德城公棚连接京津冀与山东赛事网络。',
    '聊城' => '聊城为山东西部门户，东昌府公棚近年崛起，冀鲁豫交汇处赛鸽特色鲜明。',
    '洛阳' => '洛阳为河南文化名城+赛鸽核心，洛龙、西工公棚依托中原赛事网络。',
    '开封' => '开封是河南古城+赛鸽重镇，龙亭、鼓楼公棚历史悠久，豫东鸽代表。',
    '新乡' => '新乡为河南赛鸽新兴城市，红旗、卫滨公棚近年快速发展。',
    '南阳' => '南阳是河南人口大市+赛鸽枢纽，卧龙、宛城公棚参赛羽数规模大。',
    '许昌' => '许昌为河南三国古城+赛鸽城市，魏都公棚近年现代化升级。',
    '平顶山' => '平顶山为河南能源城市+赛鸽新兴城市，新华、卫东公棚发展中。',
    '榆林' => '榆林为陕西北部赛鸽重镇，榆阳、神木公棚依托能源经济，陕北鸽耐粗饲料。',
    '渭南' => '渭南为陕西东部门户，临渭、高新公棚连接秦晋豫赛事带。',
    '宝鸡' => '宝鸡是陕西工业城市+赛鸽核心，金台、陈仓公棚历史积累深厚。',
    '绵阳' => '绵阳是中国科技城+四川赛鸽重镇，游仙、涪城公棚依托军工传统。',
    '宜宾' => '宜宾为四川酒城+赛鸽城市，翠屏、南溪公棚近年发展迅速。',
    '南充' => '南充是四川人口大市+赛鸽枢纽，顺庆、高坪公棚覆盖川东北赛事。',
    '达州' => '达州为四川东部门户，通川、达川公棚连接川渝赛事网络。',
    '遵义' => '遵义为贵州红色名城+赛鸽核心，红花岗、汇川公棚近年崛起。',
    '遵义县' => '遵义县（现播州区）是贵州赛鸽重镇，鸽系耐云贵高原气候。',
    '泸州' => '泸州是四川酒城+赛鸽城市，江阳、龙马潭公棚依托酒业经济。',
    '内江' => '内江为四川甜城+赛鸽城市，市中、东兴公棚近年快速发展。',
    '乐山' => '乐山是四川旅游名城+赛鸽城市，市中、峨眉山公棚结合旅游文化。',
    '南宁地区' => '南宁是广西赛鸽核心，兴宁、青秀公棚近年设施升级迅速。',
    '柳州' => '柳州为广西工业城市+赛鸽中心，柳南、柳北公棚工业基础雄厚。',
    '桂林' => '桂林是广西旅游名城+赛鸽城市，七星、秀峰公棚结合旅游特色。',
    '玉林' => '玉林为广西人口大市+赛鸽城市，玉州、福绵公棚近年快速发展。',
    '百色' => '百色是广西西部赛鸽枢纽，右江、田阳公棚连接滇桂赛事带。',
    '河池' => '河池为广西山区赛鸽城市，金城江公棚因地制宜发展山区赛鸽特色。',
];
$city_desc = $city_descriptions[$cityName] ?? ''; // 有描述则显示，否则整段不渲染
$page_title = $cityName . '赛事 | ' . $provinceName . '信鸽比赛 - 信鸽之家';
$meta_description = $cityName . $provinceName . '公棚赛事汇总：' . number_format($races['total'] ?? 0) . '场比赛，' . number_format($cityStats['loft_count'] ?? 0) . '个公棚，' . number_format($cityStats['total_entries'] ?? 0) . '羽参赛。';
$meta_keywords = $cityName . '赛鸽,' . $cityName . '公棚,' . $cityName . '信鸽比赛,' . $provinceName . '赛事';
$og_type = 'website';
$og_image = 'https://www.xgjia.com/public/images/og-cover.png';
$canonical_url = 'https://www.xgjia.com/race/city/' . urlencode($city ?? '') . '/';
$ld_json = [
    '@context' => 'https://schema.org',
    '@type' => 'CollectionPage',
    'name' => $page_title,
    'description' => $meta_description,
    'url' => $canonical_url,
    'isPartOf' => ['@type' => 'WebSite', 'name' => '信鸽之家', 'url' => 'https://www.xgjia.com'],
];
// FAQPage Schema (GEO SEO)
$city_faqs = [
    [
        'question' => '2026年' . $cityName . '有哪些信鸽公棚？',
        'answer' => $cityName . '现有' . number_format($cityStats['loft_count'] ?? 0) . '家注册公棚，覆盖' . $provinceName . '各区，年均办赛' . number_format($cityStats['race_count'] ?? 0) . '场，参赛羽数累计' . number_format($cityStats['total_entries'] ?? 0) . '羽。',
    ],
    [
        'question' => '如何查询' . $cityName . '信鸽比赛成绩？',
        'answer' => '在信鸽之家城市赛事页选择目标公棚，查看最新赛事及赛鸽分速排名，或输入足环号追踪个体赛绩。',
    ],
    [
        'question' => $cityName . '有哪些知名公棚？',
        'answer' => '按参赛羽数和办赛规模，' . $cityName . '公棚榜前列为：' . ($cityStats['loft_count'] ?? 0) . '家注册公棚。公棚信息及历史赛绩均可通过信鸽之家查询。',
    ],
    [
        'question' => '信鸽之家能查' . $cityName . '以外的赛事吗？',
        'answer' => '信鸽之家收录全国各省市赛事数据，支持按省份、城市、公棚名称搜索，覆盖信鸽协会正规赛事及各大公棚赛。',
    ],
    [
        'question' => '如何获取信鸽足环号查询报告？',
        'answer' => '在信鸽之家搜索框输入完整足环号，可获取血统档案、历史赛绩及所在公棚信息，报告永久免费。',
    ],
];
$ld_city_faqpage = [
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => [],
];
foreach ($city_faqs as $f) {
    $ld_city_faqpage['mainEntity'][] = [
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <?php include __DIR__ . '/_seo_head.php'; ?>
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/public/css/b-scheme.css">
    <link rel="shortcut icon" href="/public/images/favicon.ico">
    <style>
.city-detail-hero { background: linear-gradient(135deg, #1a5fa8 0%, #0d3b6e 100%); color: #fff; padding: 32px 0; }
.city-detail-hero h1 { font-size: 24px; font-weight: 700; }
.city-detail-hero .breadcrumb { font-size: 13px; opacity: 0.8; margin-bottom: 4px; }
.city-detail-hero .breadcrumb a { color: rgba(255,255,255,0.8); text-decoration: none; }
.city-detail-hero .breadcrumb a:hover { text-decoration: underline; }
.city-stats-row { display: flex; gap: 16px; margin-top: 16px; flex-wrap: wrap; }
.city-stat-item { background: rgba(255,255,255,0.1); padding: 8px 16px; border-radius: 8px; font-size: 14px; }
.city-stat-item strong { font-size: 18px; }
.city-wrap { background: #f4f6f9; padding-bottom: 40px; }
.race-list { display: grid; gap: 10px; }
.race-card { display: flex; align-items: center; background: #fff; border-radius: 10px; padding: 14px 18px; box-shadow: 0 1px 6px rgba(0,0,0,0.06); text-decoration: none; transition: all 0.2s; gap: 14px; }
.race-card:hover { transform: translateX(4px); box-shadow: 0 3px 12px rgba(26,95,168,0.12); }
.race-card-name { flex: 1; min-width: 0; }
.race-card-name .name { font-size: 15px; font-weight: 600; color: #1a5fa8; }
.race-card-name .loft { font-size: 12px; color: #999; margin-top: 2px; }
.race-card-meta { display: flex; gap: 16px; align-items: center; flex-shrink: 0; }
.race-card-meta .meta-item { font-size: 13px; color: #666; white-space: nowrap; }
.race-card-arrow { color: #ccc; font-size: 16px; flex-shrink: 0; }
.pagination { display: flex; gap: 6px; justify-content: center; margin: 24px 0; }
.pagination .page-link { padding: 8px 16px; border: 1px solid #ddd; border-radius: 6px; color: #555; text-decoration: none; font-size: 13px; }
.pagination .page-link.active { background: #1a5fa8; color: #fff; border-color: #1a5fa8; }
@media (max-width: 768px) {
    .race-card { flex-direction: column; align-items: flex-start; }
    .race-card-meta { flex-wrap: wrap; gap: 8px; }
    .city-stat-item { font-size: 12px; padding: 6px 12px; }
}

/* Other races section */
.other-races { margin-top: 32px; }
.other-races h3 { font-size: 18px; font-weight: 700; color: #1a5fa8; margin-bottom: 16px; padding-bottom: 8px; border-bottom: 2px solid #1a5fa8; }
.other-races-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; }
.other-race-card {
    display: block; padding: 14px 16px; background: #fff;
    border-radius: 8px; text-decoration: none;
    box-shadow: 0 1px 4px rgba(0,0,0,0.06);
    transition: all 0.2s;
}
.other-race-card:hover { box-shadow: 0 3px 12px rgba(26,95,168,0.15); transform: translateY(-1px); }
.other-race-card .race-name { display: block; font-size: 14px; color: #1a5fa8; font-weight: 600; margin-bottom: 4px; }
.other-race-card .race-meta { display: block; font-size: 12px; color: #888; }
@media (max-width: 480px) {
    .other-races-grid { grid-template-columns: 1fr; }
}
    </style>

    <!-- BreadcrumbList -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "BreadcrumbList",
        "itemListElement": [
            {"@type": "ListItem", "position": 1, "name": "首页", "item": "https://www.xgjia.com"},
            {"@type": "ListItem", "position": 2, "name": "城市赛事中心", "item": "https://www.xgjia.com/race/city/"},
            <?php if (!empty($provinceName)): ?>
            {"@type": "ListItem", "position": 3, "name": "<?php echo $provinceName; ?>赛事", "item": "https://www.xgjia.com/race/province/<?php echo urlencode($provinceName); ?>/"},
            {"@type": "ListItem", "position": 4, "name": "<?php echo $cityName; ?>赛事"}
            <?php else: ?>
            {"@type": "ListItem", "position": 3, "name": "<?php echo $cityName; ?>赛事"}
            <?php endif; ?>
        ]
    }
    </script>
</head>
<body>
<div class="city-wrap">
<?php include __DIR__ . '/_head.php'; ?>
<div class="city-detail-hero">
    <div class="container">
        <div class="breadcrumb">
            <a href="/race/city/">城市赛事中心</a> ›
            <?php if (!empty($provinceName)): ?>
            <a href="/race/province/<?php echo urlencode($cityStats['province']); ?>/"><?php echo $provinceName; ?></a> ›
            <?php endif; ?>
            <?php echo $cityName; ?>
        </div>
        <h1><i class="fas fa-map-marker-alt"></i> <?php echo $cityName; ?>赛事</h1>
        <?php if (!empty($cityStats)): ?>
        <div class="city-stats-row">
            <div class="city-stat-item"><strong><?php echo number_format($cityStats['race_count'] ?? 0); ?></strong> 场比赛</div>
            <div class="city-stat-item"><strong><?php echo number_format($cityStats['loft_count'] ?? 0); ?></strong> 个公棚</div>
            <div class="city-stat-item"><strong><?php echo !empty($cityStats['total_entries']) ? number_format($cityStats['total_entries']) : '—'; ?></strong> 羽参赛</div>
        </div>
        <div style="margin-top:14px;">
            <a href="/race/city/<?php echo urlencode($city); ?>/top/" style="display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,0.15);color:#fff;padding:8px 18px;border-radius:20px;text-decoration:none;font-size:14px;font-weight:500;transition:background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.25)'" onmouseout="this.style.background='rgba(255,255,255,0.15)'">
                🏆 <?php echo $cityName; ?>TOP排行 ›
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php if (!empty($city_desc)): ?>
<div class="container" style="padding: 0 0 16px;">
    <div style="background:#fff;border-radius:12px;padding:18px 22px;box-shadow:0 1px 5px rgba(0,0,0,0.05);">
        <p style="margin:0;font-size:15px;line-height:1.9;color:#444;text-indent:2em;"><?php echo $city_desc; ?></p>
    </div>
</div>
<?php endif; ?>
<div class="container" style="padding: 20px 0;">
    <?php if (!empty($races['list'])): ?>
    <div class="race-list">
        <?php foreach ($races['list'] as $r): ?>
        <a href="/race/<?php echo intval($r['id'] ?? 0); ?>.html" class="race-card">
            <div class="race-card-name">
                <div class="name"><?php echo htmlspecialchars($r['name'] ?? '未命名赛事'); ?></div>
                <div class="loft"><?php echo htmlspecialchars($r['loft_name'] ?? ''); ?></div>
            </div>
            <div class="race-card-meta">
                <?php if (!empty($r['distance_km'])): ?>
                <span class="meta-item"><?php echo number_format($r['distance_km']); ?>km</span>
                <?php endif; ?>
                <?php if (!empty($r['entry_count'])): ?>
                <span class="meta-item"><?php echo number_format($r['entry_count']); ?>羽</span>
                <?php endif; ?>
                <span class="meta-item"><?php echo htmlspecialchars($r['release_time'] ?? ''); ?></span>
            </div>
            <span class="race-card-arrow"><i class="fas fa-chevron-right"></i></span>
        </a>
        <?php endforeach; ?>
    </div>
    <?php if (($races['total_pages'] ?? 1) > 1): ?>
    <?php echo renderPagination($races['page'], $races['total_pages']); ?>
    <?php endif; ?>
    <?php else: ?>
    <div style="text-align:center;padding:60px 0;color:#999;">该城市暂无赛事数据</div>
    <?php endif; ?>
</div>
<?php if (!empty($provinceName)): ?>
<div class="container" style="padding-bottom: 40px;">
    <div class="other-races" style="margin-top: 0;">
        <h3><i class="fas fa-compass"></i> 探索更多</h3>
        <div class="other-races-grid">
            <a href="/race/province/<?php echo urlencode($provinceName); ?>/" class="other-race-card" style="border-left:3px solid #1a5fa8;">
                <span class="race-name">📊 <?php echo htmlspecialchars($provinceName); ?>赛事汇总</span>
                <span class="race-meta">查看<?php echo htmlspecialchars($provinceName); ?>全境公棚赛事</span>
            </a>
            <a href="/race/" class="other-race-card" style="border-left:3px solid #c9a84c;">
                <span class="race-name">🔍 赛事成绩搜索</span>
                <span class="race-meta">按足环号或公棚名查找赛事成绩</span>
            </a>
        </div>
    </div>
</div>
<?php endif; ?>
<?php include __DIR__ . '/_footer.php'; ?>
</div>
</body>
</html>
