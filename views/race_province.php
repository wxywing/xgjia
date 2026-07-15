<?php
/**
 * 某省份赛事聚合页 - Phase 2 SEO
 * URL: /race/province/{province}/
 * 
 * 展示指定省份的所有赛事及其统计
 */
$provinceName = htmlspecialchars($province);

// 省份静态描述（GEO SEO）
$province_descriptions = [
    '北京' => '北京是华北乃至全国赛鸽核心城市之一，拥有悠久的赛鸽文化历史。公棚赛集中在通州、顺义、昌平、大兴等区，赛事以中长距离为主，分速水平位居全国前列。北京赛鸽与天津、河北形成京津冀赛事圈，赛鸽交易活跃。',
    '天津' => '天津是北方赛鸽枢纽城市，地处渤海湾经济圈核心。武清、宝坻、静海等区公棚密集，春秋两季赛事频繁，与北京、河北形成半小时赛事通勤圈。天津赛鸽以稳为主，分速表现均衡。',
    '河北' => '河北省环绕京津，是全国赛鸽大省之一。公棚主要分布在石家庄、保定、唐山、沧州、廊坊等市，与京津赛事网络深度融合。冀鸽以耐力和稳定分速著称，在北方中长距离赛事中占据重要地位。',
    '山西' => '山西省赛鸽运动历史悠久，是华北赛鸽重要省份。太原为核心城市，大同、运城、临汾等地公棚近年快速发展。晋鸽以耐粗饲料和适应性强著称，在北方赛事中表现稳定。',
    '内蒙古' => '内蒙古是中国面积最大的省级行政区，赛鸽运动覆盖呼和浩特、包头、鄂尔多斯等地。草原气候独特，赛鸽耐粗饲料能力强。蒙鸽在北方超长距离赛事中优势明显，与东北、华北赛事圈紧密相连。',
    '辽宁' => '辽宁省是东北赛鸽领军省份，公棚主要分布在大连、沈阳、鞍山、锦州等市。辽鸽以耐寒、高分速著称，齐齐哈尔、绥化等地形成寒地赛鸽特色带。辽南沿海鸽系抗海风能力强。',
    '吉林' => '吉林省赛鸽运动以长春、吉林市为核心，松原、四平公棚群近年快速发展。吉林鸽耐低温能力突出，在北方春季赛事中表现活跃，是东北赛事圈的重要力量。',
    '黑龙江' => '黑龙江省是中国最北赛鸽省份，哈尔滨、大庆、齐齐哈尔公棚群极具特色。极寒气候造就了独特的寒地赛鸽体系，黑龙江鸽耐冻、抗逆性强，在北疆赛事中独树一帜。',
    '上海' => '上海是华东乃至全国赛鸽核心城市，赛鸽历史与国际化程度均为全国顶尖。崇明岛及宝山、嘉定公棚群设施先进，赛事规程接轨国际。上海鸽系以速度见长，与江浙形成长三角赛鸽经济圈。',
    '江苏' => '江苏省是全国赛鸽第一大省，公棚数量和赛事规模均居全国前列。南京、苏州、无锡、常州为核心城市带，徐州、扬州、南通公棚群分布密集。苏鸽以耐力和速度兼备著称，与上海、浙江形成长三角赛事核心区。',
    '浙江' => '浙江省是中国赛鸽经济最活跃的省份之一，温州、台州、杭州、宁波公棚群最为发达。浙鸽以速度见长，商界赛鸽群体资金实力雄厚，赛事奖金体系完善，与福建、江苏形成华东华南连接带。',
    '安徽' => '安徽省是中部赛鸽重要省份，合肥、芜湖、蚌埠公棚近年快速发展。安徽鸽系兼容南北特色，在长三角赛事网络中承担重要连接作用。皖鸽耐力与速度表现均衡。',
    '福建' => '福建省是东南沿海赛鸽重要省份，福州、厦门、泉州、漳州公棚群活跃。闽鸽以速度型选手居多，与台湾赛鸽交流密切，赛事国际化程度较高。福建沿海鸽系抗海风能力强。',
    '江西' => '江西省是中部赛鸽新兴省份，南昌、赣州、九江公棚近年快速崛起。赣鸽在华中赛事中渐露头角，以稳定性和耐力见长，正在追赶周边省份赛鸽水平。',
    '山东' => '山东省是全国赛鸽强省之一，公棚数量和参赛羽数均居全国前三。济南、青岛、潍坊、临沂为核心城市，淄博、烟台、威海公棚群各有特色。齐鲁鸽兼容并蓄，中长距离赛绩稳定。',
    '河南' => '河南省是中原赛鸽核心省份，郑州、洛阳、开封、新乡公棚密集。河南地处中原，交通便利，鸽系兼容南北特色。豫鸽以稳定分速和适应性强著称，是连接南北赛鸽交流的关键枢纽。',
    '湖北' => '湖北省是中部赛鸽枢纽省份，武汉、襄阳、宜昌公棚群覆盖全省。长江沿岸公棚分布密集，湖北鸽兼容南北赛鸽特点，综合实力强劲，在华中地区影响力突出。',
    '湖南' => '湖南省是华中赛鸽新兴省份，长沙、株洲、湘潭公棚近年快速发展。湘鸽以速度见长，在短距离赛事中表现活跃，与广东、湖北形成中部赛事交流带。',
    '广东' => '广东省是全国赛鸽第一经济强省，广州、深圳、东莞、佛山公棚最为发达。珠三角赛鸽信息化程度高，与港澳赛鸽交流频繁。粤鸽以速度见长，赛事奖金冠绝全国。',
    '广西' => '广西是西南赛鸽重要省份，南宁、柳州、桂林、玉林公棚群活跃。桂鸽以耐力和适应性见长，在云贵高原气候中表现突出。广西沿海鸽系抗热能力强。',
    '海南' => '海南省是中国最南端赛鸽省份，海口、三亚公棚极具热带赛鸽特色。海南鸽系适应高温高湿气候，与广东、福建形成华南赛事圈，在南海赛事中独树一帜。',
    '重庆' => '重庆是中国面积最大的直辖市，也是西南赛鸽重镇。山地地形造就独特的赛鸽体系，对鸽系定向能力要求更高。重庆鸽以耐力和适应复杂地形著称，公棚赛近年蓬勃发展。',
    '四川' => '四川省是全国赛鸽大省，成都、绵阳、德阳公棚最为发达。四川鸽系以耐力见长，在长距离赛事中表现突出，川鸽文化底蕴深厚，与云贵青藏形成西南赛事圈。',
    '贵州' => '贵州省是西南赛鸽新兴省份，贵阳、遵义、凯里公棚近年快速发展。贵州山地赛鸽特色鲜明，鸽系适应高原气候能力强，在云贵赛事带中地位渐升。',
    '云南' => '云南省是西南赛鸽特色省份，昆明、玉溪、曲靖公棚历史悠久。云南鸽系适应高原缺氧能力强，耐粗饲料，是国内高原赛鸽代表。昆明四季如春的气候也为赛鸽训练提供了优越条件。',
    '西藏' => '西藏是中国最高海拔赛鸽地区，拉萨、日喀则公棚极具挑战性。藏鸽耐高原极端缺氧气候，是世界上适应最高海拔的赛鸽群体。西藏赛鸽虽规模有限，但极具科研和文化价值。',
    '陕西' => '陕西省是西北赛鸽核心省份，西安、咸阳、宝鸡公棚最为发达。陕西赛鸽历史可追溯至唐代，雁塔系历史积淀深厚。陕鸽耐干燥气候，在西北赛事中占据核心地位。',
    '甘肃' => '甘肃省是西北赛鸽重要省份，兰州、天水、酒泉公棚连接丝路赛鸽带。甘肃地处黄土高原与青藏高原过渡带，鸽系适应性强，连接西北各省赛事网络。',
    '青海' => '青海省是青藏高原赛鸽省份，西宁、海东公棚极具高原特色。青海鸽耐高原缺氧能力极强，是国内海拔最高的赛鸽群体之一，与西藏、四川高原赛鸽形成青稞带。',
    '宁夏' => '宁夏回族自治区是西北赛鸽特色省份，银川、吴忠、石嘴山公棚依托黄河灌区。宁夏鸽适应干燥气候能力强，与甘肃、陕西形成西北赛事协作圈。',
    '新疆' => '新疆维吾尔自治区是中国面积最大的省级行政区，乌鲁木齐、克拉玛依、石河子公棚近年快速发展。新疆鸽系在极端气候中锤炼，与中亚国家赛事交流密切，是连接中欧赛鸽的重要节点。',
    '贵州' => '贵州省是西南山区赛鸽代表省份，贵阳、遵义、六盘水公棚因地制宜发展山区赛鸽特色。贵州鸽适应云贵高原气候，在复杂地形中表现出色。',
    '海南' => '海南省热带赛鸽特色鲜明，海口、三亚、儋州公棚气候条件优越。海南鸽耐高温高湿，是国内热带赛鸽代表，与广东、福建形成华南赛事圈。',
];
$province_desc = $province_descriptions[$provinceName] ?? '';
$raceCount = number_format($races['total'] ?? 0);
$loftCount = $provinceStats['loft_count'] ?? 0;
$totalEntries = number_format($provinceStats['total_entries'] ?? 0);

$page_title = $provinceName . '信鸽赛事成绩 | 省份聚合 | 赛事成绩';
$meta_description = '查看' . $provinceName . '各地公棚信鸽竞赛成绩。共' . $raceCount . '场赛事，' . $loftCount . '个公棚，' . $totalEntries . '羽参赛。查名次、鸽主、分速。';
$meta_keywords = $provinceName . '信鸽,' . $provinceName . '公棚,' . $provinceName . '赛事,赛鸽成绩,信鸽比赛';
$og_type = 'website';
$og_image = 'https://www.xgjia.com/public/images/og-cover.png';
$canonical_url = 'https://www.xgjia.com/race/province/' . urlencode($province) . '/';
$ld_json = [
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => '首页', 'item' => 'https://www.xgjia.com'],
                ['@type' => 'ListItem', 'position' => 2, 'name' => '赛事成绩', 'item' => 'https://www.xgjia.com/race/'],
                ['@type' => 'ListItem', 'position' => 3, 'name' => '省份聚合', 'item' => 'https://www.xgjia.com/race/province/'],
                ['@type' => 'ListItem', 'position' => 4, 'name' => $provinceName],
            ],
        ],
        [
            '@type' => 'CollectionPage',
            'name' => $provinceName . '信鸽赛事成绩',
            'description' => $meta_description,
            'about' => ['@type' => 'Place', 'name' => $provinceName],
        ],
    ],
];

// FAQPage Schema (GEO SEO)
$province_faqs = [
    [
        'question' => $provinceName . '有哪些信鸽公棚？',
        'answer' => $provinceName . '现有' . number_format($provinceStats['loft_count'] ?? 0) . '家注册公棚，年均举办' . number_format($races['total'] ?? 0) . '场赛事，累计参赛羽数' . ($provinceStats['total_entries'] ? number_format($provinceStats['total_entries']) : '—') . '羽。',
    ],
    [
        'question' => '如何查询' . $provinceName . '赛鸽比赛成绩？',
        'answer' => '在信鸽之家省份赛事页选择目标公棚，查看该省份最新赛事及赛鸽分速排名，或输入足环号追踪个体赛绩。',
    ],
    [
        'question' => $provinceName . '哪个城市公棚最多？',
        'answer' => '在' . $provinceName . '城市列表中可查看各城市公棚数量分布，点击城市卡片进入详情页查看该城市完整公棚信息及赛绩。',
    ],
    [
        'question' => $provinceName . '赛鸽最高分速是多少？',
        'answer' => '在' . $provinceName . '赛事列表中查看各场比赛分速排名，或进入城市TOP排行页查看该省各城市最高分速赛鸽。',
    ],
    [
        'question' => '信鸽之家' . $provinceName . '数据覆盖范围？',
        'answer' => '信鸽之家' . $provinceName . '数据覆盖全省各市县公棚，包括协会赛事及各大公棚赛，支持按城市、公棚名称、足环号多维查询。',
    ],
];
$ld_province_faqpage = [
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => [],
];
foreach ($province_faqs as $f) {
    $ld_province_faqpage['mainEntity'][] = [
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
<!-- FAQPage JSON-LD -->
    <script type="application/ld+json"><?php echo json_encode($ld_province_faqpage, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/public/css/b-scheme.css">
    <link rel="shortcut icon" href="/public/images/favicon.ico">
    <style>
.province-wrap { background: #f4f6f9; padding-bottom: 40px; }
.province-header {
    background: linear-gradient(135deg, #1a5fa8 0%, #0d3b6e 100%);
    color: #fff; padding: 36px 0;
}
.province-header h1 { font-size: 24px; font-weight: 700; margin-bottom: 4px; }
.province-header .breadcrumb { font-size: 13px; opacity: 0.8; margin-bottom: 4px; }
.province-header .breadcrumb a { color: rgba(255,255,255,0.8); text-decoration: none; }
.province-header .breadcrumb a:hover { text-decoration: underline; }
.province-stats-row { display: flex; gap: 16px; margin: 20px 0 0; flex-wrap: wrap; }
.province-stat-item { background: rgba(255,255,255,0.1); border-radius: 8px; padding: 14px 20px; text-align: center; min-width: 100px; }
.province-stat-item .val { font-size: 22px; font-weight: 700; }
.province-stat-item .lbl { font-size: 11px; opacity: 0.7; margin-top: 2px; }

.race-card { display: flex; align-items: center; gap: 16px; padding: 16px 20px; background: #fff; border-radius: 10px; margin-bottom: 10px; box-shadow: 0 1px 4px rgba(0,0,0,0.05); text-decoration: none; transition: all 0.2s; }
.race-card:hover { box-shadow: 0 3px 12px rgba(26,95,168,0.12); transform: translateX(2px); }
.race-card .race-loft { font-size: 13px; color: #888; margin-bottom: 2px; }
.race-card .race-name { font-size: 16px; font-weight: 600; color: #1a5fa8; }
.race-card .race-meta { font-size: 12px; color: #999; margin-top: 4px; }
.race-card .race-arrow { margin-left: auto; color: #ccc; font-size: 16px; }
.season-tag { display: inline-block; background: #c9a84c; color: #fff; font-size: 11px; padding: 1px 8px; border-radius: 8px; margin-left: 6px; font-weight: 600; vertical-align: middle; }

@media (max-width: 768px) {
    .province-stats-row { justify-content: center; }
    .province-stat-item { flex: 1 1 80px; min-width: 80px; padding: 12px 10px; }
    .race-card { flex-direction: column; align-items: flex-start; }
    .race-card .race-arrow { display: none; }
}
    </style>
</head>
<body>
<div class="province-wrap">
<?php include __DIR__ . '/_head.php'; ?>

<div class="province-header">
    <div class="container">
        <div class="breadcrumb">
            <a href="/">首页</a> › <a href="/race/">赛事成绩</a> › <a href="/race/province/">省份聚合</a> › <?php echo $provinceName; ?>
        </div>
        <h1><i class="fas fa-map-marker-alt"></i> <?php echo $provinceName; ?>赛事成绩</h1>
        <div class="province-stats-row">
            <div class="province-stat-item">
                <div class="val"><?php echo $raceCount; ?></div>
                <div class="lbl">赛事</div>
            </div>
            <div class="province-stat-item">
                <div class="val"><?php echo $loftCount; ?></div>
                <div class="lbl">公棚</div>
            </div>
            <?php if (!empty($provinceStats['total_entries'])): ?>
            <div class="province-stat-item">
                <div class="val"><?php echo $totalEntries; ?></div>
                <div class="lbl">参赛羽数</div>
            </div>
            <?php endif; ?>
            <?php if (!empty($provinceStats['latest_race_time'])): ?>
            <div class="province-stat-item">
                <div class="val"><?php echo date('m/d', strtotime($provinceStats['latest_race_time'])); ?></div>
                <div class="lbl">最近赛事</div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php if (!empty($province_desc)): ?>
<div class="container" style="padding: 0 0 4px;">
    <div style="background:#fff;border-radius:10px;padding:16px 20px;box-shadow:0 1px 5px rgba(0,0,0,0.05);margin-bottom:4px;">
        <p style="margin:0;font-size:15px;line-height:1.9;color:#444;text-indent:2em;"><?php echo $province_desc; ?></p>
    </div>
</div>
<?php endif; ?>

<div class="container" style="padding-top: 24px;">
    <?php if (!empty($races['list'])): ?>
    <div style="margin-bottom:16px;font-size:14px;color:#666;">共 <strong><?php echo $raceCount; ?></strong> 场赛事</div>
    <?php foreach ($races['list'] as $r):
        $seasonLabel = '';
        if (!empty($r['season_year'])) {
            $seasonLabel = $r['season_year'] . '年';
            if (!empty($r['season_type'])) $seasonLabel .= $r['season_type'];
        }
    ?>
    <a href="/race/<?php echo $r['id']; ?>.html" class="race-card">
        <div style="flex:1;min-width:0;">
            <div class="race-loft"><?php echo htmlspecialchars($r['loft_name'] ?? '未知公棚'); ?></div>
            <div class="race-name">
                <?php echo htmlspecialchars($r['name'] ?? '未命名'); ?>
                <?php if ($seasonLabel): ?>
                <span class="season-tag"><?php echo htmlspecialchars($seasonLabel); ?></span>
                <?php endif; ?>
            </div>
            <div class="race-meta">
                <?php if (!empty($r['release_time'])): ?>
                <?php echo htmlspecialchars($r['release_time']); ?>
                <?php endif; ?>
                <?php if (!empty($r['distance_km'])): ?>
                 · <?php echo $r['distance_km']; ?>km
                <?php endif; ?>
                <?php if (!empty($r['entry_count'])): ?>
                 · <?php echo number_format($r['entry_count']); ?>羽
                <?php endif; ?>
            </div>
        </div>
        <div class="race-arrow"><i class="fas fa-chevron-right"></i></div>
    </a>
    <?php endforeach; ?>

    <?php if ($races['total_pages'] > 1): ?>
    <?php echo renderPagination($races['page'], $races['total_pages']); ?>
    <?php endif; ?>

    <?php else: ?>
    <div style="text-align:center;padding:60px 0;color:#999;">
        <p>该省份暂无赛事数据</p>
        <p style="margin-top:8px;"><a href="/race/province/" style="color:#1a5fa8;">返回省份聚合</a></p>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/_footer.php'; ?>
</div>
</body>
</html>
