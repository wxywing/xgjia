<?php
/**
 * 信鸽之家 - 铭鸽详情页（B方案 清新专业）
 */
require_once dirname(__DIR__) . '/app/config/config.php';

extract($data);

$pigeonName = $pigeon['title'] ?? $pigeon['name'] ?? '铭鸽详情';
$page_title = $pigeonName . (!empty($pigeon['bloodline']) ? ' ' . $pigeon['bloodline'] : '') . ' 铭鸽 | ' . SITE_NAME;
$currentPath = $_SERVER['REQUEST_URI'] ?? '';
$images = json_decode($pigeon['images'] ?? '[]', true) ?: [];
$mainImage = $images[0] ?? '';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo h($page_title); ?></title>
    <meta name="description" content="<?php echo h($pigeon['description'] ?? mb_substr(strip_tags($pigeon['content'] ?? ''), 0, 120)); ?>">
    <meta property="og:title" content="<?php echo h($page_title); ?>">
    <?php if($mainImage): ?>
    <meta property="og:image" content="<?php echo h($mainImage); ?>">
    <?php endif; ?>
    <meta property="og:type" content="website">
    <meta name="keywords" content="<?php echo h(($pigeon['bloodline'] ?? '') . ',' . ($pigeon['name'] ?? '') . ',铭鸽,' . KEYWORDS_PIGEONS); ?>">
    <link rel="canonical" href="https://www.xgjia.com/pigeon/<?php echo intval($pigeon['id']); ?>.html">
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
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "PingFang SC", "Microsoft YaHei", "Helvetica Neue", sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.6;
        }
        a { text-decoration: none; color: inherit; }
        img { max-width: 100%; }
        .container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }

        /* Detail Layout - 修复：添加网格容器 */
        .detail-layout { display: grid; grid-template-columns: 1fr 370px; gap: 30px; padding: 20px 0 60px; }
        .left-col { min-width: 0; }
        .right-col { min-width: 0; }

        /* Gallery */
        .gallery-section { background: var(--white); border-radius: 12px; overflow: hidden; box-shadow: var(--shadow); border: 1px solid var(--border); margin-bottom: 20px; }
        .gallery-main {
            width: 100%;
            max-width: 420px;
            height: auto;
            max-height: 380px;
            margin: 0 auto;
            background: linear-gradient(135deg, #e8f4fd 0%, #d0e8f8 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 120px;
            position: relative;
            overflow: hidden;
        }
        .gallery-main img { width: 100%; height: auto; max-height: 380px; object-fit: contain; }
        .gallery-attribution {
            text-align: center;
            font-size: 12px;
            color: #999;
            padding: 6px 12px 10px;
            background: var(--white);
            border-top: 1px solid var(--border);
        }
        .gallery-attribution a { color: #1a5fa8; text-decoration: none; }
        .gallery-attribution a:hover { text-decoration: underline; }
        .gallery-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.9);
            border: none;
            border-radius: 50%;
            font-size: 16px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.25s;
            color: var(--text);
        }
        .gallery-nav:hover { background: var(--primary); color: white; }
        .gallery-nav.prev { left: 15px; }
        .gallery-nav.next { right: 15px; }
        .gallery-thumbs { display: flex; gap: 8px; padding: 12px; background: var(--bg); overflow-x: auto; }
        .gallery-thumb {
            width: 72px;
            height: 56px;
            border-radius: 6px;
            background: var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            cursor: pointer;
            border: 2px solid transparent;
            flex-shrink: 0;
            overflow: hidden;
        }
        .gallery-thumb.active { border-color: var(--primary); }
        .gallery-thumb img { width: 100%; height: 100%; object-fit: cover; }

        /* Info Section */
        .info-section { background: var(--white); border-radius: 12px; padding: 24px; box-shadow: var(--shadow); border: 1px solid var(--border); margin-bottom: 20px; }
        .info-title { font-size: 26px; font-weight: 700; color: var(--text); margin-bottom: 12px; line-height: 1.4; }
        .info-price { font-size: 30px; font-weight: 700; color: var(--primary); margin-bottom: 16px; }
        .info-price small { font-size: 15px; font-weight: 400; color: var(--text-light); }
        .info-tags { display: flex; flex-wrap: wrap; gap: 8px; margin: 14px 0; }
        .info-tag { padding: 4px 14px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .tag-male { background: #eaf4ff; color: #3498db; border: 1px solid #3498db; }
        .tag-female { background: #f4ecf7; color: #9b59b6; border: 1px solid #9b59b6; }
        .tag-pedigree { background: #fef9e7; color: var(--gold); border: 1px solid var(--accent); }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .info-item { display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--text-light); padding: 10px 14px; background: var(--bg); border-radius: 8px; }
        .info-item i { color: var(--accent); width: 16px; flex-shrink: 0; }
        .info-item strong { color: var(--text); font-weight: 600; }
        .info-actions { display: flex; gap: 10px; margin-top: 18px; }
        .info-actions .btn { flex: 1; }

        /* Seller */
        .seller-section { background: var(--white); border-radius: 12px; padding: 20px; box-shadow: var(--shadow); border: 1px solid var(--border); margin-bottom: 20px; }
        .seller-title { font-size: 15px; font-weight: 700; color: var(--text); margin-bottom: 14px; display: flex; align-items: center; gap: 8px; padding-bottom: 12px; border-bottom: 1px solid var(--border); }
        .seller-title i { color: var(--accent); }
        .seller-info { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; }
        .seller-avatar { width: 48px; height: 48px; border-radius: 50%; background: var(--primary); display: flex; align-items: center; justify-content: center; color: white; font-size: 20px; flex-shrink: 0; }
        .seller-name { font-size: 15px; font-weight: 700; color: var(--text); }
        .seller-level { font-size: 12px; color: var(--text-light); }
        .seller-meta { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .seller-meta-item { text-align: center; padding: 10px; background: var(--bg); border-radius: 8px; }
        .seller-meta-num { font-size: 16px; font-weight: 700; color: var(--primary); }
        .seller-meta-lbl { font-size: 11px; color: var(--text-light); }

        /* Description */
        .desc-section { background: var(--white); border-radius: 12px; padding: 24px; box-shadow: var(--shadow); border: 1px solid var(--border); margin-bottom: 20px; }
        .desc-title { font-size: 18px; font-weight: 700; color: var(--text); margin-bottom: 15px; display: flex; align-items: center; gap: 8px; }
        .desc-title i { color: var(--accent); }
        .desc-text { font-size: 14px; color: var(--text-light); line-height: 1.9; }

        /* Pedigree */
        .pedigree-section { background: var(--white); border-radius: 12px; padding: 24px; box-shadow: var(--shadow); border: 1px solid var(--border); margin-bottom: 20px; }
        .pedigree-title { font-size: 18px; font-weight: 700; color: var(--text); margin-bottom: 18px; display: flex; align-items: center; gap: 8px; }
        .pedigree-title i { color: var(--accent); }
        .pedigree-tree { display: flex; flex-direction: column; align-items: center; gap: 0; }
        .pedigree-row { display: flex; align-items: center; gap: 20px; }
        .pedigree-node { padding: 10px 18px; background: var(--bg); border-radius: 8px; border: 1px solid var(--border); font-size: 13px; color: var(--text); min-width: 100px; text-align: center; }
        .pedigree-node.grand { background: #fef9e7; border-color: var(--accent); font-weight: 700; color: var(--gold); }
        .pedigree-line { width: 2px; height: 20px; background: var(--accent); }

        /* Race Results */
        .race-results-section { background: var(--white); border-radius: 12px; padding: 24px; box-shadow: var(--shadow); border: 1px solid var(--border); margin-bottom: 20px; }
        .race-results-title { font-size: 18px; font-weight: 700; color: var(--text); margin-bottom: 15px; display: flex; align-items: center; gap: 8px; }
        .race-results-title i { color: var(--accent); }
        .race-results-list { display: flex; flex-direction: column; gap: 10px; }
        .race-result-item { display: flex; align-items: center; gap: 15px; padding: 12px 16px; background: var(--bg); border-radius: 8px; font-size: 13px; }
        .race-result-item .race-name { color: var(--primary); font-weight: 600; text-decoration: none; flex: 1; }
        .race-result-item .race-name:hover { text-decoration: underline; }
        .race-result-item .race-rank { color: var(--text); font-weight: 600; }
        .race-result-item .race-speed { color: var(--text-light); }
        .race-result-item .race-meta { display: flex; gap: 12px; margin-top: 4px; flex-wrap: wrap; }
        .race-view-btn { display: inline-flex; align-items: center; gap: 5px; padding: 8px 16px; background: var(--primary); color: #fff; border-radius: 6px; font-size: 12px; font-weight: 600; text-decoration: none; white-space: nowrap; transition: all 0.2s; flex-shrink: 0; }
        .race-view-btn:hover { background: #15508c; transform: translateY(-1px); }

        /* Related */
        .related-section { margin-top: 30px; padding-bottom: 40px; }
        .related-title { font-size: 22px; font-weight: 700; color: var(--text); margin-bottom: 20px; display: flex; align-items: center; gap: 8px; }
        .related-title i { color: var(--accent); }
        .related-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; }
        .related-card { background: var(--white); border-radius: 10px; overflow: hidden; box-shadow: var(--shadow); border: 1px solid var(--border); transition: all 0.3s; }
        .related-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-hover); }
        .related-img { width: 100%; height: 140px; background: linear-gradient(135deg, #e8f4fd 0%, #d0e8f8 100%); display: flex; align-items: center; justify-content: center; font-size: 40px; overflow: hidden; }
        .related-img img { width: 100%; height: 100%; object-fit: cover; }
        .related-body { padding: 14px; }
        .related-name { font-size: 14px; font-weight: 700; color: var(--text); margin-bottom: 6px; }
        .related-name a:hover { color: var(--primary); }
        .related-price { font-size: 14px; font-weight: 700; color: var(--primary); }
        .related-meta { font-size: 12px; color: var(--text-light); margin-top: 4px; }
        .related-meta i { color: var(--accent); margin-right: 2px; }

        /* Footer */
        .footer { background: var(--white); border-top: 1px solid var(--border); padding: 50px 0 20px; margin-top: 30px; }
        .footer-grid { display: grid; grid-template-columns: 1.5fr 1fr 1fr 1fr; gap: 40px; margin-bottom: 35px; }
        .footer-brand h3 { font-size: 20px; margin-bottom: 12px; color: var(--primary); display: flex; align-items: center; gap: 8px; }
        .footer-brand h3 i { color: var(--accent); }
        .footer-brand p { font-size: 13px; color: var(--text-light); line-height: 1.9; }
        .footer-col h4 { font-size: 14px; font-weight: 700; margin-bottom: 16px; color: var(--text); }
        .footer-col ul { list-style: none; }
        .footer-col li { margin-bottom: 10px; }
        .footer-col a { font-size: 13px; color: var(--text-light); transition: color 0.3s; }
        .footer-col a:hover { color: var(--primary); }
        .footer-bottom { border-top: 1px solid var(--border); padding-top: 20px; text-align: center; font-size: 12px; color: var(--text-light); }

        /* Responsive */
        @media (max-width: 1024px) {
            .detail-layout { grid-template-columns: 1fr; }
            .related-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 768px) {
            .gallery-main { max-width: 100%; height: auto; max-height: 280px; }
            .gallery-main img { max-height: 280px; }
            .info-section { padding: 16px; }
            .info-price { font-size: 26px; }
            .info-title { font-size: 20px; }
            .info-grid { grid-template-columns: 1fr; }
            .info-actions { flex-direction: column; }
            .seller-section { padding: 14px; }
            .seller-meta { grid-template-columns: repeat(2, 1fr); gap: 8px; }
            .pedigree-section { padding: 16px; }
            .pedigree-row { gap: 12px; overflow-x: auto; padding-bottom: 8px; }
            .pedigree-node { min-width: 80px; font-size: 12px; }
            .related-grid { grid-template-columns: 1fr 1fr; }
            .footer-grid { grid-template-columns: 1fr 1fr; gap: 25px; }
            .footer-brand { grid-column: 1 / -1; }
        }
        @media (max-width: 480px) {
            .related-grid { grid-template-columns: 1fr; }
        }

        /* ========== 面包屑 ========== */
        .breadcrumb-wrap { background: var(--white); border-bottom: 1px solid var(--border); padding: 0; }
        .breadcrumb-wrap .breadcrumb { padding: 12px 0; font-size: 13px; color: var(--text-light); }
        .breadcrumb-wrap .breadcrumb a { color: var(--primary); text-decoration: none; }
        .breadcrumb-wrap .breadcrumb a:hover { text-decoration: underline; }
        .breadcrumb-wrap .breadcrumb .fas { margin: 0 4px; font-size: 11px; color: var(--text-light); }
        .breadcrumb-wrap .breadcrumb span { color: var(--text); }

    </style>

    <!-- JSON-LD Structured Data -->
    <?php
    $_ld_pigeon = [
        '@context' => 'https://schema.org',
        '@type' => 'Thing',
        'name' => $pigeon['name'] ?? '',
        'description' => !empty($pigeon['description']) ? mb_substr(strip_tags($pigeon['description']), 0, 200) : ($pigeon['name'] ?? '') . '赛鸽信息',
    ];
    if (!empty($pigeon['images'])):
        $_imgs = json_decode($pigeon['images'], true) ?: [];
        if ($_imgs) $_ld_pigeon['image'] = $_imgs[0];
    endif;
    $_props = [];
    if (!empty($pigeon['bloodline'])) $_props[] = ['@type' => 'PropertyValue', 'name' => '血统', 'value' => $pigeon['bloodline']];
    if (!empty($pigeon['ring_number'])) $_props[] = ['@type' => 'PropertyValue', 'name' => '足环号', 'value' => $pigeon['ring_number']];
    if (!empty($pigeon['gender'])) $_props[] = ['@type' => 'PropertyValue', 'name' => '性别', 'value' => intval($pigeon['gender']) == 1 ? '雄' : '雌'];
    if (!empty($pigeon['eye_type'])) $_props[] = ['@type' => 'PropertyValue', 'name' => '眼砂', 'value' => $pigeon['eye_type']];
    if (!empty($pigeon['color'])) $_props[] = ['@type' => 'PropertyValue', 'name' => '羽色', 'value' => $pigeon['color']];
    if (!empty($pigeon['category'])) $_props[] = ['@type' => 'PropertyValue', 'name' => '品类', 'value' => $pigeon['category']];
    if ($_props) $_ld_pigeon['additionalProperty'] = $_props;
    ?>
    <script type="application/ld+json"><?php echo json_encode($_ld_pigeon, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>

    <!-- BreadcrumbList -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "BreadcrumbList",
        "itemListElement": [
            {"@type": "ListItem", "position": 1, "name": "首页", "item": "https://www.xgjia.com"},
            {"@type": "ListItem", "position": 2, "name": "铭鸽展厅", "item": "https://www.xgjia.com/pigeon/"},
            {"@type": "ListItem", "position": 3, "name": "<?php echo h($pigeonName); ?>"}
        ]
    }
    </script>
</head>
<body>
<?php include __DIR__ . '/_head.php'; ?>

    <div class="container">

        <!-- 面包屑 -->
        <div class="breadcrumb-wrap">
            <div class="breadcrumb">
                <a href="/"><i class="fas fa-home"></i> 首页</a>
                <i class="fas fa-chevron-right"></i>
                <a href="/pigeon/">铭鸽展厅</a>
                <i class="fas fa-chevron-right"></i>
                <span><?php echo h($pigeonName); ?></span>
            </div>
        </div>

        <div class="detail-layout">
            <!-- 左栏：画廊 + 描述 + 血统 + 相关 -->
            <div class="left-col">
                <!-- 图片画廊 -->
                <div class="gallery-section">
                    <div class="gallery-main" id="galleryMain">
                        <?php if($mainImage): ?>
                        <a href="<?php echo h($mainImage); ?>" target="_blank" rel="noopener" title="点击查看原图">
                            <img src="<?php echo h($mainImage); ?>" alt="<?php echo h($pigeonName); ?>" id="mainImg">
                        </a>
                        <?php else: ?>
                        <span style="font-size: 120px;">🐦</span>
                        <?php endif; ?>
                        <?php if(count($images) > 1): ?>
                        <button class="gallery-nav prev" onclick="prevImg()">◀</button>
                        <button class="gallery-nav next" onclick="nextImg()">▶</button>
                        <?php endif; ?>
                    </div>
                    <?php if($mainImage): ?>
                    <div class="gallery-attribution">
                        📷 图片来源：网络 · <a href="<?php echo h($mainImage); ?>" target="_blank" rel="noopener">点击查看原图</a>
                    </div>
                    <?php endif; ?>
                    <?php if(count($images) > 1): ?>
                    <div class="gallery-thumbs">
                        <?php foreach($images as $i => $img): ?>
                        <div class="gallery-thumb <?php if($i === 0) echo 'active'; ?>" onclick="setImg(<?php echo $i; ?>)">
                            <img loading="lazy" src="<?php echo h($img); ?>" alt="<?php echo h($pigeonName); ?> 图片<?php echo $i+1; ?>">
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- 基本档案 -->
                <div class="desc-section">
                    <h2 class="desc-title"><i class="fas fa-id-card"></i> 基本档案</h2>
                    <div class="info-grid">
                        <?php if (!empty($pigeon['ring_number'])): ?>
                        <div class="info-item"><i class="fas fa-tag"></i> <span>足环号</span> <strong><?php echo h($pigeon['ring_number']); ?></strong></div>
                        <?php endif; ?>
                        <?php if (!empty($pigeon['bloodline'])): ?>
                        <div class="info-item"><i class="fas fa-dna"></i> <span>血统</span> <strong>
                            <?php if (!empty($strain)): ?><a href="/pedigree/strain/<?php echo urlencode($strain['name']); ?>/" style="color:var(--accent);"><?php echo h($pigeon['bloodline']); ?></a>
                            <?php else: ?><?php echo h($pigeon['bloodline']); ?><?php endif; ?>
                        </strong></div>
                        <?php endif; ?>
                        <?php $genderVal = (int)($pigeon['gender'] ?? 0); ?>
                        <div class="info-item"><i class="fas fa-venus-mars"></i> <span>性别</span> <strong><?php echo $genderVal == 1 ? '雄' : ($genderVal == 2 ? '雌' : '未知'); ?></strong></div>
                        <?php if (!empty($pigeon['color'])): ?>
                        <div class="info-item"><i class="fas fa-palette"></i> <span>羽色</span> <strong><?php echo h($pigeon['color']); ?></strong></div>
                        <?php endif; ?>
                        <?php if (!empty($pigeon['eye_color'])): ?>
                        <div class="info-item"><i class="fas fa-eye"></i> <span>眼砂</span> <strong><?php echo h($pigeon['eye_color']); ?></strong></div>
                        <?php endif; ?>
                        <?php if (!empty($pigeon['birth_date']) && substr($pigeon['birth_date'], 0, 10) != '0000-00-00'): ?>
                        <div class="info-item"><i class="fas fa-calendar"></i> <span>出生日期</span> <strong><?php echo h($pigeon['birth_date']); ?></strong></div>
                        <?php endif; ?>
                        <?php if (!empty($pigeon['shop_name'])): ?>
                        <div class="info-item"><i class="fas fa-store"></i> <span>所属展厅</span> <strong><a href="/shop/<?php echo intval($pigeon['shop_id']); ?>.html" style="color:var(--primary);"><?php echo h($pigeon['shop_name']); ?></a></strong></div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- 简介 -->
                <div class="desc-section">
                    <h2 class="desc-title"><i class="fas fa-feather-alt"></i> 简介</h2>
                    <div class="desc-text">
                        <?php
                        $parts = [];
                        $gv = (int)($pigeon['gender'] ?? 0);
                        if ($gv > 0) $parts[] = ($gv == 1 ? '雄' : '雌') . '鸽';
                        if (!empty($pigeon['color'])) $parts[] = h($pigeon['color']) . '羽色';
                        if (!empty($pigeon['bloodline'])) $parts[] = h($pigeon['bloodline']) . '血统';
                        if (!empty($pigeon['eye_color'])) $parts[] = h($pigeon['eye_color']);
                        if (!empty($pigeon['ring_number'])) $parts[] = '足环号 ' . h($pigeon['ring_number']);
                        if (!empty($parts)) {
                            echo '<p style="margin-bottom:12px;color:var(--text);line-height:1.9;">' . '这羽' . implode('，', array_slice($parts, 0, 2)) . '铭鸽，' . implode('，', array_slice($parts, 2)) . '。</p>';
                        }
                        $raw = $pigeon['description'] ?? $pigeon['content'] ?? '';
                        if (!empty($raw)) echo $raw;
                        elseif (empty($parts)) echo '<p>暂无详细信息</p>';
                        ?>
                    </div>
                </div>

                <!-- 所属展厅 -->
                <?php if (!empty($shop)): ?>
                <div class="pedigree-section">
                    <h2 class="pedigree-title"><i class="fas fa-store"></i> 所属展厅</h2>
                    <div style="display:flex;gap:16px;align-items:center;">
                        <?php $shopImg = $shop['avatar'] ?? ''; ?>
                        <?php if (!empty($shopImg)): ?>
                        <img src="<?php echo h($shopImg); ?>" style="width:64px;height:64px;border-radius:8px;object-fit:cover;flex-shrink:0;" alt="<?php echo h($shop['name']); ?>">
                        <?php else: ?>
                        <div style="width:64px;height:64px;border-radius:8px;background:var(--bg);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:28px;">🏠</div>
                        <?php endif; ?>
                        <div style="flex:1;min-width:0;">
                            <a href="/shop/<?php echo intval($shop['id']); ?>.html" style="font-size:16px;font-weight:600;color:var(--primary);text-decoration:none;">
                                <?php echo h($shop['name']); ?>
                            </a>
                            <?php if (!empty($shop['address'])): ?>
                            <div style="font-size:13px;color:var(--text-light);margin-top:6px;">
                                <i class="fas fa-map-marker-alt" style="margin-right:4px;"></i><?php echo h($shop['address']); ?>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($shop['contact_phone'])): ?>
                            <div style="font-size:13px;color:var(--text-light);margin-top:4px;">
                                <i class="fas fa-phone" style="margin-right:4px;"></i><?php echo h($shop['contact_phone']); ?>
                            </div>
                            <?php endif; ?>
                            <a href="/shop/<?php echo intval($shop['id']); ?>.html" style="display:inline-block;margin-top:8px;font-size:13px;color:var(--accent);text-decoration:none;">
                                查看完整展厅 <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- 参赛成绩 -->
                <?php if (!empty($raceLinks)): ?>
                <div class="race-results-section">
                    <h2 class="race-results-title"><i class="fas fa-trophy"></i> 参赛成绩</h2>
                    <div class="race-results-list">
                        <?php foreach ($raceLinks as $race): ?>
                        <div class="race-result-item">
                            <div style="flex:1;min-width:0;">
                                <a href="/race/<?php echo intval($race['race_id'] ?? $race['id']); ?>.html" class="race-name">
                                    <?php echo h($race['race_name'] ?? $race['name'] ?? '未知赛事'); ?>
                                </a>
                                <div class="race-meta">
                                    <span class="race-rank">🏅 名次：<?php echo intval($race['rank'] ?? 0); ?></span>
                                    <span class="race-speed">⚡ 分速：<?php echo number_format($race['speed'] ?? 0, 3); ?></span>
                                </div>
                            </div>
                            <a href="/race/<?php echo intval($race['race_id'] ?? $race['id']); ?>.html" class="race-view-btn">
                                查看完整成绩 <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- 同品系铭鸽 -->
                <?php if(!empty($sameStrainPigeons)): ?>
                <div class="related-section">
                    <h2 class="related-title"><i class="fas fa-dna"></i> <?php echo h($strain['name'] ?? $pigeon['bloodline'] ?? ''); ?> 品系铭鸽</h2>
                    <div class="related-grid">
                        <?php foreach($sameStrainPigeons as $sp): ?>
                        <?php $spImages = json_decode($sp['images'] ?? '[]', true) ?: []; ?>
                        <div class="related-card">
                            <a href="/pigeon/<?php echo intval($sp['id']); ?>.html">
                            <div class="related-img">
                                <?php if(!empty($spImages[0])): ?>
                                <img loading="lazy" src="<?php echo h($spImages[0]); ?>" alt="<?php echo h($sp['name'] ?? ''); ?>">
                                <?php else: ?>
                                <i class="fas fa-dove" style="color:#d1d5db;font-size:40px;"></i>
                                <?php endif; ?>
                            </div>
                            </a>
                            <div class="related-body">
                                <div class="related-name"><a href="/pigeon/<?php echo intval($sp['id']); ?>.html"><?php echo h($sp['name']); ?></a></div>
                                <?php if (!empty($sp['ring_number'])): ?>
                                <div class="related-meta"><i class="fas fa-tag"></i> <?php echo h(mb_strimwidth($sp['ring_number'], 0, 14, '...')); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- 相关铭鸽推荐 -->
                <?php if(!empty($relatedPigeons)): ?>
                <div class="related-section">
                    <h2 class="related-title"><i class="fas fa-th-large"></i> 相关铭鸽推荐</h2>
                    <div class="related-grid">
                        <?php foreach(array_slice($relatedPigeons, 0, 4) as $rp): ?>
                        <?php $rpImages = json_decode($rp['images'] ?? '[]', true) ?: []; ?>
                        <div class="related-card">
                            <div class="related-img">
                                <?php if(!empty($rpImages[0])): ?>
                                <img loading="lazy" src="<?php echo h($rpImages[0]); ?>" alt="<?php echo h($rp['title'] ?? ''); ?>">
                                <?php else: ?>
                                <span style="font-size: 40px;">🐦</span>
                                <?php endif; ?>
                            </div>
                            <div class="related-body">
                                <h3 class="related-name"><a href="/pigeon/<?php echo intval($rp['id']); ?>.html"><?php echo h($rp['title'] ?? $rp['name'] ?? '未命名'); ?></a></h3>
                                <?php if(!empty($rp['price'])): ?>
                                <div class="related-price">¥<?php echo number_format($rp['price']); ?></div>
                                <?php endif; ?>
                                <div class="related-meta">
                                    <?php if(!empty($rp['bloodline'])): ?><i class="fas fa-dna"></i> <?php echo h($rp['bloodline']); ?><?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- 右栏：价格信息 + 卖家信息 -->
            <div class="right-col">
                <!-- 价格和信息 -->
                <div class="info-section">
                    <h1 class="info-title"><?php echo h($pigeonName); ?></h1>
                    <?php if(!empty($pigeon['price']) && $pigeon['price'] > 0): ?>
                    <div class="info-price">¥<?php echo number_format($pigeon['price']); ?> <small>/ 议价可询</small></div>
                    <?php else: ?>
                    <div class="info-price" style="color:var(--text-light);font-size:20px;">价格面议 <small>联系询价</small></div>
                    <?php endif; ?>

                    <div class="info-tags">
                        <?php if(($pigeon['gender'] ?? '') === 'male'): ?><span class="info-tag tag-male"><i class="fas fa-mars"></i> 雄鸽</span><?php endif; ?>
                        <?php if(($pigeon['gender'] ?? '') === 'female'): ?><span class="info-tag tag-female"><i class="fas fa-venus"></i> 雌鸽</span><?php endif; ?>
                        <?php if(!empty($pigeon['bloodline'])): ?><span class="info-tag tag-pedigree"><i class="fas fa-award"></i> 纯血<?php echo h($pigeon['bloodline']); ?></span><?php endif; ?>
                    </div>

                    <div class="info-grid">
                        <?php if(!empty($pigeon['ring_number'])): ?>
                        <div class="info-item"><i class="fas fa-ring"></i><span>足环：<strong><?php echo h($pigeon['ring_number']); ?></strong></span></div>
                        <?php endif; ?>
                        <?php if(!empty($pigeon['bloodline'])): ?>
                        <div class="info-item"><i class="fas fa-dna"></i><span>血统：<strong><?php echo h($pigeon['bloodline']); ?></strong></span></div>
                        <?php endif; ?>
                        <?php if(!empty($pigeon['color'])): ?>
                        <div class="info-item"><i class="fas fa-palette"></i><span>羽色：<strong><?php echo h($pigeon['color']); ?></strong></span></div>
                        <?php endif; ?>
                        <?php if(!empty($pigeon['year'])): ?>
                        <div class="info-item"><i class="fas fa-calendar"></i><span>年份：<strong><?php echo h($pigeon['year']); ?>年</strong></span></div>
                        <?php endif; ?>
                        <?php if(!empty($pigeon['gender'])): ?>
                        <div class="info-item"><i class="fas fa-venus-mars"></i><span>性别：<strong><?php echo ($pigeon['gender']==='male')?'雄':'雌'; ?></strong></span></div>
                        <?php endif; ?>
                        <?php if(!empty($pigeon['achievement'])): ?>
                        <div class="info-item"><i class="fas fa-trophy"></i><span>赛绩：<strong><?php echo h($pigeon['achievement']); ?></strong></span></div>
                        <?php endif; ?>
                    </div>

                    <div class="info-actions">
                        <?php if ($isLoggedIn && !empty($seller['phone'])): ?>
                        <a href="tel:<?php echo h($seller['phone']); ?>" class="btn btn-primary"><i class="fas fa-phone"></i> 联系卖家</a>
                        <?php elseif ($isLoggedIn): ?>
                        <button class="btn btn-outline" disabled style="opacity:0.6;cursor:not-allowed;">卖家暂未公开联系方式</button>
                        <?php else: ?>
                        <a href="/login" class="btn btn-outline"><i class="fas fa-lock"></i> 登录后查看</a>
                        <?php endif; ?>
                        <button class="btn btn-outline" onclick="toggleFav(this)"><i class="far fa-star"></i> 收藏</button>
                    </div>
                </div>

                <!-- 卖家信息 -->
                <?php if(!empty($seller)): ?>
                <div class="seller-section">
                    <h3 class="seller-title"><i class="fas fa-user-circle"></i> 卖家信息</h3>
                    <div class="seller-info">
                        <div class="seller-avatar"><i class="fas fa-user"></i></div>
                        <div>
                            <div class="seller-name"><?php echo h($seller['nickname'] ?? $seller['username'] ?? '鸽友'); ?></div>
                            <div class="seller-level"><?php echo h($seller['member_level'] ?? '认证鸽友'); ?></div>
                        </div>
                    </div>
                    <div class="seller-meta">
                        <div class="seller-meta-item"><div class="seller-meta-num"><?php echo number_format($seller['pigeon_count'] ?? $seller['listing_count'] ?? 0); ?></div><div class="seller-meta-lbl">在售铭鸽</div></div>
                        <div class="seller-meta-item"><div class="seller-meta-num"><?php echo number_format($seller['rating'] ?? $seller['好评率'] ?? 98); ?>%</div><div class="seller-meta-lbl">好评率</div></div>
                        <div class="seller-meta-item"><div class="seller-meta-num"><?php echo number_format($pigeon['views'] ?? $pigeon['view_count'] ?? 0); ?></div><div class="seller-meta-lbl">浏览量</div></div>
                        <div class="seller-meta-item"><div class="seller-meta-num"><?php echo number_format($seller['listing_count'] ?? 0); ?></div><div class="seller-meta-lbl">总发布</div></div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

<?php include __DIR__ . '/_footer.php'; ?>

<script>
let currentImg = 0;
const images = <?php echo json_encode($images); ?>;

function setImg(idx) {
    currentImg = idx;
    document.getElementById('mainImg').src = images[idx];
    document.querySelectorAll('.gallery-thumb').forEach((t, i) => {
        t.classList.toggle('active', i === idx);
    });
}

function prevImg() {
    currentImg = (currentImg - 1 + images.length) % images.length;
    setImg(currentImg);
}

function nextImg() {
    currentImg = (currentImg + 1) % images.length;
    setImg(currentImg);
}

function toggleFav(btn) {
    btn.classList.toggle('active');
    const icon = btn.querySelector('i');
    if (btn.classList.contains('active')) {
        icon.className = 'fas fa-star';
        btn.style.background = 'var(--primary)';
        btn.style.color = 'white';
    } else {
        icon.className = 'far fa-star';
        btn.style.background = '';
        btn.style.color = '';
    }
}
</script>
</body>
</html>
