<?php
/**
 * 信鸽之家 - 公棚详情页（B方案 清新专业·丰富版）
 */
require_once dirname(__DIR__) . '/app/config/config.php';

extract($data);

$page_title = ($loft['name'] ?? '公棚详情') . (!empty($loft['province']) ? ' ' . $loft['province'] : '') . '公棚 | ' . SITE_NAME;
$currentPath = $_SERVER['REQUEST_URI'] ?? '';

// SEO
$meta_desc = $loft['description'] ?? ($loft['name'] ?? '公棚') . ' - ' . ($loft['location'] ?? '') . '信鸽公棚，赛事规程、历年成绩、联系方式一应俱全。';

// FAQPage Schema (GEO SEO)
$loftName = $loft['name'] ?? '该公棚';
$loftProvince = $loft['province'] ?? '';
$raceCount = count($races ?: []);
$loft_faqs = [
    [
        'question' => $loftName . '在哪里？',
        'answer' => $loftName . '位于' . ($loft['location'] ?? $loftProvince ?? '—') . '，是' . ($loftProvince ?: '当地') . '知名信鸽公棚，提供赛鸽寄养、训练、赛事服务。',
    ],
    [
        'question' => $loftName . '今年有哪些赛事？',
        'answer' => $loftName . '今年计划举办' . ($raceCount > 0 ? $raceCount . '场' : '多场') . '赛事，包括春赛、秋赛等常规赛事。具体赛程可在「历年赛绩」标签页查看历史办赛记录。',
    ],
    [
        'question' => '如何联系' . $loftName . '？',
        'answer' => '在公棚详情页查看联系方式，或点击「认领/管理」按钮进行公棚认领。认领后可获得完整管理权限，包括联系方式编辑、赛事发布等。',
    ],
    [
        'question' => $loftName . '的历史赛绩如何？',
        'answer' => '在「历年赛绩」标签页可查看' . $loftName . '的历史办赛数据，包括参赛羽数、冠军鸽信息、平均分速等详细统计。',
    ],
    [
        'question' => $loftName . '的参赛羽数是多少？',
        'answer' => $loftName . '当前寄养羽数约' . number_format($loft['pigeon_count'] ?? $loft['current_count'] ?? 0) . '羽，历年累计参赛羽数' . (!empty($seasonStats['total_results']) ? number_format($seasonStats['total_results']) : '数据待统计') . '羽。',
    ],
];
$ld_loft_faqpage = [
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => [],
];
foreach ($loft_faqs as $f) {
    $ld_loft_faqpage['mainEntity'][] = [
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
    <title><?php echo h($page_title); ?></title>
    <meta name="description" content="<?php echo h($meta_desc); ?>">
    <meta property="og:title" content="<?php echo h($page_title); ?>">
        <meta property="og:image" content="<?php echo !empty($loft['logo'] ?? null) ? h($loft['logo']) : 'https://www.xgjia.com/public/images/og-cover.png'; ?>">
    <meta property="og:description" content="<?php echo h($meta_desc); ?>">
    <meta property="og:type" content="website">
    <meta name="keywords" content="<?php echo h(($loft['name'] ?? '') . ',' . ($loft['province'] ?? '') . ',公棚,' . KEYWORDS_LOFTS); ?>">
    <link rel="canonical" href="https://www.xgjia.com/loft/<?php echo intval($loft['id']); ?>.html">
    <script type="application/ld+json"><?php echo json_encode($ld_loft_faqpage, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>
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
            --radius: 12px;
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

        /* 阅读进度条 */
        .reading-progress {
            position: fixed;
            top: 0; left: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--primary), var(--accent));
            z-index: 9999;
            transition: width 0.1s linear;
        }

        /* 面包屑 */
        .breadcrumb-wrap {
            background: var(--white);
            border-bottom: 1px solid var(--border);
        }
        .breadcrumb {
            padding: 12px 0;
            font-size: 13px;
            color: var(--text-light);
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 4px;
        }
        .breadcrumb a { color: var(--primary); transition: color 0.3s; }
        .breadcrumb a:hover { color: var(--primary-dark); }
        .breadcrumb i { margin: 0 4px; font-size: 10px; color: var(--text-light); }
        .breadcrumb span { color: var(--text); }

        /* Hero Banner */
        .hero-banner {
            position: relative;
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 50%, var(--primary-light) 100%);
            color: white;
            padding: 40px 0;
            overflow: hidden;
        }
        .hero-banner::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            background: rgba(255,255,255,0.03);
        }
        .hero-banner::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--accent), var(--accent-light), var(--accent));
        }
        .hero-inner {
            position: relative;
            z-index: 1;
        }
        .hero-badges {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 14px;
        }
        .hero-badge {
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .badge-certified { background: var(--accent); color: white; }
        .badge-gold { background: rgba(255,255,255,0.2); color: var(--accent-light); border: 1px solid rgba(201,168,76,0.3); }
        .badge-blue { background: rgba(255,255,255,0.15); color: white; }

        .loft-name {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 10px;
            line-height: 1.3;
        }
        .loft-meta {
            font-size: 14px;
            opacity: 0.9;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 16px;
        }
        .loft-meta span { display: flex; align-items: center; gap: 5px; }
        .loft-meta i { color: var(--accent-light); }

        /* Hero Stats */
        .hero-stats {
            display: flex;
            gap: 24px;
            flex-wrap: wrap;
        }
        .hero-stat {
            text-align: center;
            padding: 12px 20px;
            background: rgba(255,255,255,0.12);
            border-radius: 10px;
            border: 1px solid rgba(255,255,255,0.1);
            min-width: 90px;
        }
        .hero-stat-num {
            font-size: 26px;
            font-weight: 700;
            line-height: 1.1;
        }
        .hero-stat-lbl {
            font-size: 11px;
            opacity: 0.75;
            margin-top: 4px;
        }

        /* 快捷操作栏 */
        .action-bar {
            background: var(--white);
            border-bottom: 1px solid var(--border);
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .action-bar-inner {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 0;
            flex-wrap: wrap;
        }
        .action-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 18px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.25s;
            border: 1px solid var(--border);
            background: var(--white);
            color: var(--text-light);
        }
        .action-btn:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        .action-btn.fav-active {
            background: #fef3e2;
            color: var(--accent);
            border-color: var(--accent);
        }
        .action-bar .tab-links {
            margin-left: auto;
            display: flex;
            gap: 4px;
        }
        .action-bar .tab-link {
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 13px;
            color: var(--text-light);
            cursor: pointer;
            transition: all 0.25s;
            border: none;
            background: transparent;
        }
        .action-bar .tab-link:hover,
        .action-bar .tab-link.active {
            background: var(--bg);
            color: var(--primary);
            font-weight: 600;
        }

        /* Detail Layout */
        .detail-layout {
            display: grid;
            grid-template-columns: 1fr 370px;
            gap: 30px;
            padding: 30px 0 60px;
        }
        .left-col { min-width: 0; }
        .right-col { position: sticky; top: 60px; align-self: start; }

        /* Section */
        .section {
            background: var(--white);
            border-radius: var(--radius);
            padding: 24px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            margin-bottom: 20px;
        }
        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
            padding-bottom: 12px;
            border-bottom: 2px solid var(--bg);
        }
        .section-title i { color: var(--accent); }

        /* Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .info-item {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            color: var(--text-light);
            padding: 12px 16px;
            background: var(--bg);
            border-radius: 8px;
            transition: all 0.25s;
        }
        .info-item:hover { background: #eaf4ff; }
        .info-item i {
            color: var(--accent);
            width: 18px;
            flex-shrink: 0;
            font-size: 14px;
            text-align: center;
        }
        .info-item strong { color: var(--text); font-weight: 600; }

        /* Description */
        .desc-text {
            font-size: 15px;
            color: var(--text-light);
            line-height: 2;
            text-align: justify;
        }

        /* Table */
        .result-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        .result-table thead th {
            background: linear-gradient(180deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 12px 14px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
        }
        .result-table thead th:first-child { border-radius: 8px 0 0 0; }
        .result-table thead th:last-child { border-radius: 0 8px 0 0; }
        .result-table tbody td {
            padding: 12px 14px;
            border-bottom: 1px solid var(--border);
            color: var(--text-light);
        }
        .result-table tbody tr:last-child td { border-bottom: none; }
        .result-table tbody tr:hover td { background: var(--bg); }
        .table-rank {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            font-weight: 700;
            font-size: 12px;
        }
        .rank-1 { background: #fef3e2; color: #d4a843; }
        .rank-2 { background: #eaf4ff; color: var(--primary); }
        .rank-3 { background: #fef9e7; color: #e67e22; }

        /* News */
        .news-list { display: flex; flex-direction: column; gap: 10px; }
        .news-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px;
            background: var(--bg);
            border-radius: 8px;
            transition: all 0.25s;
            cursor: pointer;
            border: 1px solid transparent;
        }
        .news-item:hover {
            background: #eaf4ff;
            border-color: var(--primary);
        }
        .news-date {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: white;
            flex-shrink: 0;
        }
        .news-month { font-size: 11px; opacity: 0.85; line-height: 1; }
        .news-day { font-size: 20px; font-weight: 700; line-height: 1.1; }
        .news-title {
            font-size: 14px;
            font-weight: 600;
            color: var(--text);
            flex: 1;
        }
        .news-arrow {
            color: var(--text-light);
            font-size: 12px;
            opacity: 0;
            transition: all 0.3s;
        }
        .news-item:hover .news-arrow { opacity: 1; color: var(--primary); }

        /* Gallery */
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
        }
        .gallery-thumb {
            aspect-ratio: 1;
            background: var(--bg);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 35px;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.3s;
            border: 2px solid transparent;
            position: relative;
        }
        .gallery-thumb:hover {
            border-color: var(--primary);
            box-shadow: var(--shadow-hover);
            transform: translateY(-2px);
        }
        .gallery-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .gallery-thumb .thumb-overlay {
            position: absolute;
            inset: 0;
            background: rgba(26,95,168,0.6);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            opacity: 0;
            transition: opacity 0.3s;
        }
        .gallery-thumb:hover .thumb-overlay { opacity: 1; }

        /* Sidebar Cards */
        .sidebar-card {
            background: var(--white);
            border-radius: var(--radius);
            padding: 20px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            margin-bottom: 20px;
        }
        .sidebar-title {
            font-size: 15px;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            padding-bottom: 12px;
            border-bottom: 2px solid var(--bg);
        }
        .sidebar-title i { color: var(--accent); }

        /* Contact */
        .contact-row {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 0;
            border-bottom: 1px solid var(--border);
            font-size: 14px;
            color: var(--text);
        }
        .contact-row:last-child { border-bottom: none; }
        .contact-row i { color: var(--accent); width: 16px; flex-shrink: 0; }
        .contact-value { color: var(--primary); font-weight: 600; font-size: 14px; }

        /* Prize */
        .prize-total {
            text-align: center;
            padding: 20px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border-radius: 10px;
            margin-bottom: 14px;
            color: white;
        }
        .prize-amount { font-size: 32px; font-weight: 700; }
        .prize-label { font-size: 12px; opacity: 0.8; margin-top: 4px; }
        .prize-list { font-size: 13px; color: var(--text-light); line-height: 2.2; }
        .prize-list i { margin-right: 6px; }
        .prize-rank-1 { color: #f39c12; }
        .prize-rank-2 { color: #bdc3c7; }
        .prize-rank-3 { color: #cd6133; }

        /* Owner */
        .owner-card {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 14px;
        }
        .owner-avatar {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 22px;
            flex-shrink: 0;
        }
        .owner-name { font-weight: 700; color: var(--text); font-size: 15px; }
        .owner-role { font-size: 12px; color: var(--text-light); }

        /* Countdown */
        .countdown-box {
            text-align: center;
        }
        .countdown-title {
            font-size: 13px;
            color: var(--text-light);
            margin-bottom: 10px;
        }
        .countdown-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
        }
        .countdown-item {
            background: var(--bg);
            border-radius: 8px;
            padding: 10px 6px;
            text-align: center;
        }
        .countdown-num {
            font-size: 22px;
            font-weight: 700;
            color: var(--primary);
            line-height: 1;
        }
        .countdown-unit {
            font-size: 11px;
            color: var(--text-light);
            margin-top: 4px;
        }

        /* Related Lofts */
        .related-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }
        .related-card {
            background: var(--white);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            transition: all 0.3s;
        }
        .related-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-hover);
        }
        .related-img {
            width: 100%;
            height: 140px;
            background: linear-gradient(135deg, #e8ecf0, #d5dbe2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            overflow: hidden;
        }
        .related-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .related-body { padding: 14px; }
        .related-name {
            font-size: 15px;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 6px;
            transition: color 0.3s;
        }
        .related-name:hover { color: var(--primary); }
        .related-meta {
            font-size: 12px;
            color: var(--text-light);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .related-meta i { color: var(--accent); }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.25s;
            border: none;
            text-decoration: none;
            justify-content: center;
        }
        .btn-primary {
            background: var(--primary);
            color: white;
        }
        .btn-primary:hover {
            background: var(--primary-dark);
        }
        .btn-outline {
            background: transparent;
            color: var(--primary);
            border: 1px solid var(--primary);
        }
        .btn-outline:hover {
            background: var(--primary);
            color: white;
        }
        .btn-accent {
            background: var(--accent);
            color: white;
        }
        .btn-accent:hover {
            background: #b8943a;
        }
        .btn-block { width: 100%; }
        .btn-lg { padding: 14px 24px; font-size: 15px; }

        /* Empty State */
        .empty-cell {
            text-align: center;
            color: var(--text-light);
            padding: 40px 20px !important;
            font-size: 14px;
        }
        .empty-cell i {
            display: block;
            font-size: 36px;
            margin-bottom: 10px;
            color: var(--border);
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .detail-layout { grid-template-columns: 1fr; }
            .right-col { position: static; }
            .related-grid { grid-template-columns: repeat(3, 1fr); }
            .gallery-grid { grid-template-columns: repeat(3, 1fr); }
        }
        @media (max-width: 768px) {
            .loft-name { font-size: 24px; }
            .hero-banner { padding: 30px 0; }
            .info-grid { grid-template-columns: 1fr; }
            .hero-stats { gap: 10px; }
            .hero-stat { padding: 10px 14px; min-width: 70px; }
            .hero-stat-num { font-size: 20px; }
            .gallery-grid { grid-template-columns: repeat(2, 1fr); }
            .related-grid { grid-template-columns: 1fr 1fr; }
            .action-bar .tab-links { display: none; }
        }
        @media (max-width: 480px) {
            .hero-stats { justify-content: center; }
            .related-grid { grid-template-columns: 1fr; }
            .countdown-grid { grid-template-columns: repeat(4, 1fr); }
            .countdown-num { font-size: 18px; }
        }
    .loft-stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
    }
    .loft-stat-card {
        background: linear-gradient(135deg, #f8f9fa, #e8ecf1);
        border-radius: 10px;
        padding: 16px 12px;
        text-align: center;
        transition: transform 0.2s;
    }
    .loft-stat-card:hover { transform: translateY(-2px); }
    .lsc-val {
        font-size: 22px;
        font-weight: 700;
        color: #1a5fa8;
        word-break: break-all;
    }
    .lsc-label {
        font-size: 11px;
        color: #888;
        margin-top: 4px;
    }
    .champion-carousel {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 10px;
    }
    .champion-card {
        background: linear-gradient(135deg, #fffde7, #fff8e1);
        border: 1px solid #ffe082;
        border-radius: 10px;
        padding: 14px;
        transition: all 0.2s;
    }
    .champion-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(201, 168, 76, 0.3);
    }
    .champion-ring {
        font-size: 14px;
        margin-bottom: 6px;
    }
    .champion-owner {
        font-size: 13px;
        color: #555;
        margin-bottom: 4px;
    }
    .champion-meta {
        font-size: 11px;
        color: #999;
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
    }
    .champion-date {
        font-size: 11px;
        color: #aaa;
        margin-top: 4px;
    }
    @media (max-width: 768px) {
        .loft-stats-grid { grid-template-columns: repeat(2, 1fr); }
        .champion-carousel { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 480px) {
        .loft-stats-grid { grid-template-columns: repeat(2, 1fr); }
        .champion-carousel { grid-template-columns: 1fr; }
    }
    </style>

    <!-- JSON-LD Structured Data -->
    <?php
    $_ld_loft = [
        '@context' => 'https://schema.org',
        '@type' => 'LocalBusiness',
        'name' => $loft['name'] ?? '',
        'description' => !empty($loft['description']) ? mb_substr(strip_tags($loft['description']), 0, 200) : ($loft['name'] ?? '') . '公棚',
        'url' => 'https://www.xgjia.com/loft/' . ($loft['id'] ?? '') . '.html',
    ];
    if (!empty($loft['logo']) || !empty($loft['cover'])):
        $_ld_loft['image'] = $loft['logo'] ?: $loft['cover'];
    endif;
    $_addr_parts = array_filter([$loft['province'] ?? '', $loft['city'] ?? '', $loft['address'] ?? '']);
    if ($_addr_parts):
        $_ld_loft['address'] = [
            '@type' => 'PostalAddress',
            'addressLocality' => implode('', array_slice($_addr_parts, 1)), // 城市+地址
            'addressRegion' => $loft['province'] ?? '', // 省/自治区
        ];
    endif;
    if (!empty($loft['contact_phone'])) $_ld_loft['telephone'] = $loft['contact_phone'];
    if (!empty($loft['lat']) && !empty($loft['lng'])) {
        $_ld_loft['geo'] = [
            '@type' => 'GeoCoordinates',
            'latitude' => floatval($loft['lat']),
            'longitude' => floatval($loft['lng']),
        ];
    }
    ?>
    <script type="application/ld+json"><?php echo json_encode($_ld_loft, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>

    <!-- BreadcrumbList -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "BreadcrumbList",
        "itemListElement": [
            {"@type": "ListItem", "position": 1, "name": "首页", "item": "https://www.xgjia.com"},
            {"@type": "ListItem", "position": 2, "name": "公棚大全", "item": "https://www.xgjia.com/loft/"}
            <?php if (!empty($loft['province'])): ?>,
            {"@type": "ListItem", "position": 3, "name": "<?php echo h($loft['province']); ?>公棚", "item": "https://www.xgjia.com/loft/province/<?php echo urlencode($loft['province']); ?>/"},
            {"@type": "ListItem", "position": 4, "name": "<?php echo h($loft['name'] ?? '公棚'); ?>"}
            <?php else: ?>,
            {"@type": "ListItem", "position": 3, "name": "<?php echo h($loft['name'] ?? '公棚'); ?>"}
            <?php endif; ?>
        ]
    }
    </script>
</head>
<body>
    <div class="reading-progress" id="readingProgress"></div>
    <?php include __DIR__ . '/_head.php'; ?>

    <!-- 面包屑 -->
    <div class="breadcrumb-wrap">
        <div class="container">
            <div class="breadcrumb">
                <a href="/"><i class="fas fa-home"></i> 首页</a>
                <i class="fas fa-chevron-right"></i>
                <a href="/loft/">公棚</a>
                <?php if (!empty($loft['province'])): ?>
                <i class="fas fa-chevron-right"></i>
                <a href="/loft/province/<?php echo urlencode($loft['province']); ?>/"><?php echo h($loft['province']); ?>公棚</a>
                <?php endif; ?>
                <i class="fas fa-chevron-right"></i>
                <span><?php echo h($loft['name'] ?? ''); ?></span>
            </div>
        </div>
    </div>

    <!-- Hero Banner -->
    <div class="hero-banner">
        <div class="container hero-inner">
            <div class="hero-badges">
                <?php if(!empty($loft['certified'])): ?>
                <span class="hero-badge badge-certified"><i class="fas fa-shield-alt"></i> 已认证</span>
                <?php endif; ?>
                <?php if(($loft['prize_pool'] ?? 0) > 0): ?>
                <span class="hero-badge badge-gold"><i class="fas fa-trophy"></i> 总奖金 ¥<?php echo number_format($loft['prize_pool']); ?></span>
                <?php endif; ?>
                <?php if(!empty($loft['race_type'])): ?>
                <span class="hero-badge badge-blue"><i class="fas fa-flag-checkered"></i> <?php echo h($loft['race_type']); ?></span>
                <?php endif; ?>
            </div>

            <h1 class="loft-name"><?php echo h($loft['name'] ?? '未命名公棚'); ?></h1>

            <?php if (!empty($isOwner)): ?>
            <div style="margin: 12px 0;">
                <a href="/loft/edit/<?php echo intval($loft['id']); ?>/" class="btn-edit-loft" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; background: var(--accent); color: white; border-radius: 6px; font-size: 14px; font-weight: 500; text-decoration: none;"><i class="fas fa-edit"></i> 编辑公棚信息</a>
            </div>
            <?php endif; ?>

            <div class="loft-meta">
                <?php if(!empty($loft['location'])): ?>
                <span><i class="fas fa-map-marker-alt"></i> <?php echo h($loft['location']); ?></span>
                <?php endif; ?>
                <?php if(!empty($loft['rating'])): ?>
                <span><i class="fas fa-star"></i> <?php echo number_format($loft['rating'], 1); ?> 分</span>
                <?php endif; ?>
                <?php if(!empty($loft['established'])): ?>
                <span><i class="fas fa-calendar-alt"></i> <?php echo h($loft['established']); ?>年建棚</span>
                <?php endif; ?>
            </div>

            <div class="hero-stats">
                <div class="hero-stat">
                    <div class="hero-stat-num"><?php echo count($races ?: []); ?></div>
                    <div class="hero-stat-lbl">赛事记录</div>
                </div>
                <div class="hero-stat">
                    <div class="hero-stat-num"><?php echo h($loft['distance'] ?? '--'); ?></div>
                    <div class="hero-stat-lbl">决赛距离(km)</div>
                </div>
                <div class="hero-stat">
                    <div class="hero-stat-num"><?php echo !empty($seasonStats) ? ($seasonStats['champion_count'] ?? 0) : '--'; ?></div>
                    <div class="hero-stat-lbl">🏆 冠军数</div>
                </div>
                <div class="hero-stat">
                    <div class="hero-stat-num"><?php echo !empty($seasonStats) && ($seasonStats['total_results'] ?? 0) > 0 ? number_format($seasonStats['total_results'] / 10000, 1) . '万' : '--'; ?></div>
                    <div class="hero-stat-lbl">总成绩条</div>
                </div>
                <div class="hero-stat">
                    <div class="hero-stat-num"><?php echo !empty($seasonStats['best_speed']) ? number_format($seasonStats['best_speed'], 0) : '--'; ?></div>
                    <div class="hero-stat-lbl">⚡ 最高分速</div>
                </div>
            </div>
        </div>
    </div>

    <!-- 快捷操作栏 -->
    <div class="action-bar">
        <div class="container action-bar-inner">
            <button class="action-btn" onclick="toggleFav()" id="favBtn">
                <i class="far fa-heart"></i> 收藏
            </button>
            <button class="action-btn" onclick="sharePage()">
                <i class="fas fa-share-alt"></i> 分享
            </button>
            <?php if(!empty($loft['phone']) || !empty($loft['mobile'])): ?>
            <a href="tel:<?php echo h($loft['phone'] ?? $loft['mobile'] ?? ''); ?>" class="action-btn">
                <i class="fas fa-phone"></i> 电话咨询
            </a>
            <?php endif; ?>
            <div class="tab-links">
                <button class="tab-link active" onclick="scrollToSection('info')">基本信息</button>
                <button class="tab-link" onclick="scrollToSection('analysis')">数据分析</button>
                <button class="tab-link" onclick="scrollToSection('champions')">冠军荣耀</button>
                <button class="tab-link" onclick="scrollToSection('races')">历年赛绩</button>
            </div>
        </div>
    </div>

    <!-- 主体内容 -->
    <div class="container">
        <div class="detail-layout">
            <!-- 左栏 - 主内容 -->
            <div class="left-col">
                <!-- 基本信息 -->
                <div class="section" id="section-info">
                    <h2 class="section-title"><i class="fas fa-info-circle"></i> 基本信息</h2>
                    <div class="info-grid">
                        <?php if(!empty($loft['fee'])): ?>
                        <div class="info-item">
                            <i class="fas fa-yen-sign"></i>
                            <span>参赛费：<strong>¥<?php echo number_format($loft['fee']); ?>/羽</strong></span>
                        </div>
                        <?php endif; ?>
                        <?php if(!empty($loft['distance'])): ?>
                        <div class="info-item">
                            <i class="fas fa-route"></i>
                            <span>决赛距离：<strong><?php echo h($loft['distance']); ?>km</strong></span>
                        </div>
                        <?php endif; ?>
                        <?php if(!empty($loft['pigeon_count']) || !empty($loft['current_count'])): ?>
                        <div class="info-item">
                            <i class="fas fa-dove"></i>
                            <span>当前羽数：<strong><?php echo number_format($loft['pigeon_count'] ?? $loft['current_count'] ?? 0); ?>羽</strong></span>
                        </div>
                        <?php endif; ?>
                        <div class="info-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>所在省份：<a href="/race/province/<?php echo urlencode($loft['province'] ?? ''); ?>/" style="color:var(--accent);text-decoration:none;font-weight:600;"><?php echo h($loft['province'] ?? '—'); ?></a></span>
                        </div>
                        <?php if(!empty($loft['race_type'])): ?>
                        <div class="info-item">
                            <i class="fas fa-flag-checkered"></i>
                            <span>赛事类型：<strong><?php echo h($loft['race_type']); ?></strong></span>
                        </div>
                        <?php endif; ?>
                        <?php if(!empty($loft['owner_name'])): ?>
                        <div class="info-item">
                            <i class="fas fa-user-tie"></i>
                            <span>负责人：<strong><?php echo h($loft['owner_name']); ?></strong></span>
                        </div>
                        <?php endif; ?>
                        <?php if(!empty($loft['phone'])): ?>
                        <div class="info-item">
                            <i class="fas fa-phone-alt"></i>
                            <span>电话：<strong><?php echo h($loft['phone']); ?></strong></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- 公棚简介 -->
                <?php if(!empty($loft['description'])): ?>
                <div class="section">
                    <h2 class="section-title"><i class="fas fa-book-open"></i> 公棚简介</h2>
                    <p class="desc-text"><?php echo nl2br(h($loft['description'])); ?></p>
                </div>
                <?php endif; ?>

                <!-- AI 赛事数据分析 -->
                <?php if(!empty($aiDescription)): ?>
                <div class="section" id="section-analysis">
                    <h2 class="section-title"><i class="fas fa-chart-line"></i> 数据深度解读</h2>
                    <p class="desc-text" style="color:#4a5568;">
                        📊 <?php echo h($aiDescription); ?>
                    </p>
                </div>
                <?php endif; ?>

                <!-- P0: 赛事数据看板 -->
                <?php
                $raceStats = $raceStats ?? null;
                $seasonStats = $seasonStats ?? null;
                if(!empty($raceStats) || !empty($seasonStats)):
                ?>
                <div class="section">
                    <h2 class="section-title"><i class="fas fa-chart-bar"></i> 赛事数据看板</h2>
                    <div class="loft-stats-grid">
                        <?php $rs = $raceStats ?: []; ?>
                        <div class="loft-stat-card">
                            <div class="lsc-val"><?php echo number_format($rs['total_races'] ?? 0); ?></div>
                            <div class="lsc-label">赛事总数</div>
                        </div>
                        <div class="loft-stat-card">
                            <div class="lsc-val"><?php echo !empty($rs['total_entries']) ? number_format($rs['total_entries']) : '—'; ?></div>
                            <div class="lsc-label">累计参赛羽数</div>
                        </div>
                        <div class="loft-stat-card">
                            <div class="lsc-val"><?php echo !empty($rs['avg_return_rate']) ? number_format($rs['avg_return_rate'], 1) . '%' : '—'; ?></div>
                            <div class="lsc-label">平均归巢率</div>
                        </div>
                        <div class="loft-stat-card">
                            <div class="lsc-val"><?php echo !empty($seasonStats['best_speed']) ? number_format($seasonStats['best_speed'], 0) : '—'; ?></div>
                            <div class="lsc-label">⚡ 最高分速 (m/min)</div>
                        </div>
                        <div class="loft-stat-card">
                            <div class="lsc-val"><?php echo !empty($seasonStats['champion_count']) ? number_format($seasonStats['champion_count']) : '0'; ?></div>
                            <div class="lsc-label">🏆 冠军次数</div>
                        </div>
                        <div class="loft-stat-card">
                            <div class="lsc-val"><?php echo !empty($seasonStats['owner_count']) ? number_format($seasonStats['owner_count']) : '—'; ?></div>
                            <div class="lsc-label">👤 参赛鸽主</div>
                        </div>
                        <?php if (!empty($rs['min_distance']) && !empty($rs['max_distance'])): ?>
                        <div class="loft-stat-card">
                            <div class="lsc-val"><?php echo number_format($rs['min_distance']); ?>-<?php echo number_format($rs['max_distance']); ?></div>
                            <div class="lsc-label">📏 空距范围 (km)</div>
                        </div>
                        <div class="loft-stat-card">
                            <div class="lsc-val"><?php echo number_format($rs['season_count'] ?? 0); ?></div>
                            <div class="lsc-label">📅 赛季数</div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div style="text-align:center;margin-top:12px;">
                        <a href="/race/loft/<?php echo intval($loft['id']); ?>/" class="btn btn-outline" style="display:inline-flex;align-items:center;gap:8px;padding:6px 16px;font-size:13px;">
                            <i class="fas fa-list"></i> 查看全部赛事列表
                        </a>
                    </div>
                </div>
                <?php endif; ?>

                <!-- P0: 冠军鸽列表 -->
                <?php if(!empty($loftChampions)): ?>
                <div class="section" id="section-champions">
                    <h2 class="section-title"><i class="fas fa-crown"></i> 冠军鸽荣耀榜</h2>
                    <div class="champion-carousel">
                        <?php foreach ($loftChampions as $champ): ?>
                        <div class="champion-card">
                            <div class="champion-ring">
                                <?php if (!empty($champ['ring_number'])): ?>
                                <a href="/race/ring/<?php echo urlencode($champ['ring_number']); ?>" style="color:#1a5fa8;text-decoration:none;font-weight:600;">
                                    🔢 <?php echo htmlspecialchars($champ['ring_number']); ?>
                                </a>
                                <?php else: ?>
                                <span style="color:#999;">足环号未知</span>
                                <?php endif; ?>
                            </div>
                            <div class="champion-owner">👤 <?php echo htmlspecialchars($champ['owner_name'] ?? '—'); ?></div>
                            <div class="champion-meta">
                                <span><?php echo htmlspecialchars($champ['race_name'] ?? '赛事'); ?></span>
                                <?php if (!empty($champ['distance_km'])): ?>
                                <span>· <?php echo number_format($champ['distance_km']); ?>km</span>
                                <?php endif; ?>
                                <?php if (!empty($champ['speed'])): ?>
                                <span>· <?php echo number_format($champ['speed']); ?> m/min</span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($champ['release_time'])): ?>
                            <div class="champion-date">📅 <?php echo htmlspecialchars($champ['release_time']); ?></div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- 赛季对比 -->
                <?php if (!empty($seasonComparison)): ?>
                <div class="section">
                    <h2 class="section-title"><i class="fas fa-calendar-check"></i> 历年赛季对比</h2>
                    <div style="overflow-x:auto;">
                        <table class="result-table">
                            <thead>
                                <tr>
                                    <th>赛季</th>
                                    <th>赛事数</th>
                                    <th>参赛羽数</th>
                                    <th>归巢羽数</th>
                                    <th>平均空距</th>
                                    <th>最高分速</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($seasonComparison as $sc): ?>
                                <tr>
                                    <td style="font-weight:600;color:var(--primary);">
                                        <a href="/race/season/<?php echo $sc['season_year']; ?>/" style="color:#1a5fa8;text-decoration:none;">
                                            <?php echo h($sc['season_year'] . ' ' . ($sc['season_type'] ?: '赛季')); ?>
                                        </a>
                                    </td>
                                    <td><?php echo number_format($sc['race_count']); ?> 场</td>
                                    <td><?php echo !empty($sc['total_entries']) ? number_format($sc['total_entries']) : '—'; ?> 羽</td>
                                    <td><?php echo !empty($sc['total_returned']) ? number_format($sc['total_returned']) : '—'; ?> 羽</td>
                                    <td><?php echo !empty($sc['avg_distance']) ? number_format($sc['avg_distance'], 1) : '—'; ?> km</td>
                                    <td style="color:#2e7d32;font-weight:600;">
                                        <?php echo !empty($sc['best_speed']) ? number_format($sc['best_speed'], 0) : '—'; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>

                <!-- 鸽主荣誉榜 -->
                <?php if (!empty($topOwners)): ?>
                <div class="section">
                    <h2 class="section-title"><i class="fas fa-star"></i> 鸽主荣誉榜</h2>
                    <div style="overflow-x:auto;">
                        <table class="result-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>鸽主</th>
                                    <th>冠军次数</th>
                                    <th>最高分速</th>
                                    <th>代表鸽</th>
                                    <th>最近夺冠</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topOwners as $i => $owner): ?>
                                <?php $rankClass = $i === 0 ? 'gold' : ($i === 1 ? 'silver' : ($i === 2 ? 'bronze' : '')); ?>
                                <tr>
                                    <td>
                                        <span class="rank-badge <?php echo $rankClass; ?>"><?php echo $i + 1; ?></span>
                                    </td>
                                    <td>
                                        <a href="/page/owner/<?php echo urlencode($owner['owner_name'] ?? ''); ?>/" style="color:#1a5fa8;text-decoration:none;font-weight:600;">
                                            <?php echo htmlspecialchars($owner['owner_name'] ?? '—'); ?>
                                        </a>
                                    </td>
                                    <td style="color:#c9a84c;font-weight:700;"><?php echo number_format($owner['champion_count'] ?? 0); ?> 次</td>
                                    <td style="color:#2e7d32;font-weight:600;">
                                        <?php echo !empty($owner['best_speed']) ? number_format($owner['best_speed']) : '—'; ?> m/min
                                    </td>
                                    <td style="font-size:12px;color:#666;">
                                        <?php echo htmlspecialchars($owner['top_rings'] ?? '—'); ?>
                                    </td>
                                    <td style="font-size:12px;color:#999;">
                                        <?php echo !empty($owner['latest_release']) ? htmlspecialchars($owner['latest_release']) : '—'; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>

                <!-- 赛事规程 -->
                <?php if(!empty($entries)): ?>
                <div class="section" id="section-schedule">
                    <h2 class="section-title"><i class="fas fa-flag-checkered"></i> 赛事规程</h2>
                    <div style="overflow-x:auto;">
                        <table class="result-table">
                            <thead>
                                <tr>
                                    <th>关次</th>
                                    <th>日期</th>
                                    <th>空距</th>
                                    <th>参赛费</th>
                                    <th>奖金分配</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($entries as $e): ?>
                                <tr>
                                    <td style="font-weight:600;color:var(--text);">
                                        <?php echo h($e['name'] ?? $e['race_name'] ?? '第' . ($e['sequence'] ?? '--') . '关'); ?>
                                    </td>
                                    <td><?php echo h($e['race_date'] ?? '--'); ?></td>
                                    <td><?php echo h($e['distance'] ?? '--'); ?> km</td>
                                    <td><?php echo !empty($e['fee']) ? '¥' . number_format($e['fee']) : '--'; ?></td>
                                    <td><?php echo h($e['prize_desc'] ?? '--'); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>

                <!-- 历年成绩 -->
                <?php if(!empty($reviews)): ?>
                <div class="section" id="section-results">
                    <h2 class="section-title"><i class="fas fa-history"></i> 历年成绩</h2>
                    <div style="overflow-x:auto;">
                        <table class="result-table">
                            <thead>
                                <tr>
                                    <th>年份</th>
                                    <th>赛事</th>
                                    <th>冠军鸽主</th>
                                    <th>羽数</th>
                                    <th>冠军分速</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($reviews as $r): ?>
                                <tr>
                                    <td style="font-weight:700;color:var(--primary);"><?php echo h($r['year'] ?? '--'); ?></td>
                                    <td><?php echo h($r['name'] ?? '秋季决赛'); ?></td>
                                    <td><?php echo h($r['owner'] ?? '--'); ?></td>
                                    <td><?php echo !empty($r['pigeon_count']) ? number_format($r['pigeon_count']) . '羽' : '--'; ?></td>
                                    <td><?php echo !empty($r['speed']) ? h($r['speed']) . 'm/分' : '--'; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>

                <!-- 赛事列表 -->
                <?php if(!empty($races)): ?>
                <div class="section" id="section-races">
                    <h2 class="section-title"><i class="fas fa-flag-checkered"></i> 赛事列表</h2>
                    <div style="overflow-x:auto;">
                        <table class="result-table">
                            <thead>
                                <tr>
                                    <th>赛事名称</th>
                                    <th>日期</th>
                                    <th>类别</th>
                                    <th>参赛数</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($races as $race): ?>
                                <tr>
                                    <td style="font-weight:600;color:var(--text);">
                                        <a href="/race/<?php echo intval($race['id']); ?>.html"><?php echo h($race['name'] ?? '未命名赛事'); ?></a>
                                    </td>
                                    <td><?php echo h($race['release_time'] ?? '--'); ?></td>
                                    <td><?php echo h($race['race_category'] ?? '--'); ?></td>
                                    <td><?php echo number_format($race['entry_count'] ?? 0); ?> 羽</td>
                                    <td>
                                        <a href="/race/<?php echo intval($race['id']); ?>.html" class="btn btn-primary" style="padding:6px 12px;font-size:12px;">查看成绩</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>

                <!-- 公棚动态 -->
                <?php if(!empty($loftNews)): ?>
                <div class="section">
                    <h2 class="section-title"><i class="fas fa-newspaper"></i> 公棚动态</h2>
                    <div class="news-list">
                        <?php foreach(array_slice($loftNews, 0, 5) as $n):
                            $nDate = $n['created_at'] ?? $n['date'] ?? time();
                            $ts = is_numeric($nDate) ? $nDate : strtotime($nDate);
                        ?>
                        <div class="news-item">
                            <div class="news-date">
                                <span class="news-month"><?php echo date('m月', $ts); ?></span>
                                <span class="news-day"><?php echo date('d', $ts); ?></span>
                            </div>
                            <div class="news-title"><?php echo h($n['title'] ?? $n['content'] ?? ''); ?></div>
                            <i class="fas fa-chevron-right news-arrow"></i>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- 公棚相册 -->
                <?php if(!empty($loftPhotos)): ?>
                <div class="section" id="section-gallery">
                    <h2 class="section-title"><i class="fas fa-images"></i> 公棚相册</h2>
                    <div class="gallery-grid">
                        <?php foreach(array_slice($loftPhotos, 0, 8) as $p): ?>
                        <div class="gallery-thumb" onclick="openGallery('<?php echo h($p['url'] ?? $p['image'] ?? ''); ?>')">
                            <?php if(!empty($p['url'] ?? $p['image'])): ?>
                            <img src="<?php echo h($p['url'] ?? $p['image']); ?>" alt="<?php echo h($loft['name'] ?? '公棚'); ?> 相册" loading="lazy">
                            <div class="thumb-overlay"><i class="fas fa-search-plus"></i></div>
                            <?php else: ?>
                            <i class="fas fa-image" style="color:var(--border);font-size:32px;"></i>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- 相关公棚 -->
                <?php if(!empty($relatedLofts)): ?>
                <div class="section">
                    <h2 class="section-title"><i class="fas fa-building"></i> 附近公棚推荐</h2>
                    <div class="related-grid">
                        <?php foreach(array_slice($relatedLofts, 0, 4) as $rl): ?>
                        <a href="/loft/<?php echo intval($rl['id']); ?>.html" class="related-card">
                            <div class="related-img">
                                <?php if(!empty($rl['cover_image'])): ?>
                                <img src="<?php echo h($rl['cover_image']); ?>" alt="<?php echo h($rl['name'] ?? ''); ?>" loading="lazy">
                                <?php else: ?>
                                <i class="fas fa-warehouse" style="color:var(--border);"></i>
                                <?php endif; ?>
                            </div>
                            <div class="related-body">
                                <div class="related-name"><?php echo h($rl['name'] ?? ''); ?></div>
                                <div class="related-meta">
                                    <?php if(!empty($rl['location'])): ?>
                                    <span><i class="fas fa-map-marker-alt"></i> <?php echo h($rl['location']); ?></span>
                                    <?php endif; ?>
                                    <?php if(!empty($rl['rating'])): ?>
                                    <span><i class="fas fa-star"></i> <?php echo number_format($rl['rating'], 1); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- 右栏 - 侧边栏 -->
            <div class="right-col">
                <!-- 联系方式 -->
                <div class="sidebar-card">
                    <h3 class="sidebar-title"><i class="fas fa-address-book"></i> 联系方式</h3>
                    <?php if(!empty($loft['phone'])): ?>
                    <div class="contact-row">
                        <i class="fas fa-phone"></i>
                        <span>电话：</span>
                        <span class="contact-value"><?php echo h($loft['phone']); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if(!empty($loft['mobile'])): ?>
                    <div class="contact-row">
                        <i class="fas fa-mobile-alt"></i>
                        <span>手机：</span>
                        <span class="contact-value"><?php echo h($loft['mobile']); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if(!empty($loft['address'])): ?>
                    <div class="contact-row" style="align-items: flex-start;">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>地址：</span>
                        <span style="font-size:13px;color:var(--text);line-height:1.6;"><?php echo h($loft['address']); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if(!empty($loft['business_hours'])): ?>
                    <div class="contact-row">
                        <i class="fas fa-clock"></i>
                        <span>营业：</span>
                        <span><?php echo h($loft['business_hours']); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php $contactPhone = $loft['phone'] ?? $loft['mobile'] ?? ''; ?>
                    <?php if($contactPhone): ?>
                    <a href="tel:<?php echo h($contactPhone); ?>" class="btn btn-primary btn-block btn-lg" style="margin-top:16px;">
                        <i class="fas fa-phone-alt"></i> 立即咨询
                    </a>
                    <?php endif; ?>
                </div>

                <!-- 赛季奖金 -->
                <?php if(($loft['prize_pool'] ?? 0) > 0): ?>
                <div class="sidebar-card">
                    <h3 class="sidebar-title"><i class="fas fa-trophy"></i> 赛季奖金</h3>
                    <div class="prize-total">
                        <div class="prize-amount">¥<?php echo number_format($loft['prize_pool']); ?></div>
                        <div class="prize-label">总奖金池</div>
                    </div>
                    <div class="prize-list">
                        <div>
                            <i class="fas fa-medal prize-rank-1"></i>
                            决赛冠军：<strong>¥<?php echo number_format(($loft['prize_pool'] ?? 0) * 0.16); ?></strong>
                        </div>
                        <div>
                            <i class="fas fa-medal prize-rank-2"></i>
                            决赛亚军：<strong>¥<?php echo number_format(($loft['prize_pool'] ?? 0) * 0.10); ?></strong>
                        </div>
                        <div>
                            <i class="fas fa-medal prize-rank-3"></i>
                            决赛季军：<strong>¥<?php echo number_format(($loft['prize_pool'] ?? 0) * 0.06); ?></strong>
                        </div>
                        <div style="margin-top:8px;padding-top:8px;border-top:1px solid var(--border);">
                            <i class="fas fa-users" style="color:var(--text-light);"></i>
                            前 <?php echo max(10, intval(($loft['prize_pool'] ?? 0) / 10000)); ?> 名获奖
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- 赛事倒计时 -->
                <?php
                $nextRaceDate = $entries[0]['race_date'] ?? null;
                if ($nextRaceDate):
                    $raceTs = strtotime($nextRaceDate);
                    if ($raceTs > time()):
                ?>
                <div class="sidebar-card">
                    <h3 class="sidebar-title"><i class="fas fa-hourglass-half"></i> 赛事倒计时</h3>
                    <div class="countdown-box">
                        <div class="countdown-title">距 <?php echo h($entries[0]['name'] ?? $entries[0]['race_name'] ?? '下一关'); ?></div>
                        <div class="countdown-grid" id="countdown">
                            <div class="countdown-item">
                                <div class="countdown-num" id="cd-days">--</div>
                                <div class="countdown-unit">天</div>
                            </div>
                            <div class="countdown-item">
                                <div class="countdown-num" id="cd-hours">--</div>
                                <div class="countdown-unit">时</div>
                            </div>
                            <div class="countdown-item">
                                <div class="countdown-num" id="cd-mins">--</div>
                                <div class="countdown-unit">分</div>
                            </div>
                            <div class="countdown-item">
                                <div class="countdown-num" id="cd-secs">--</div>
                                <div class="countdown-unit">秒</div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; endif; ?>

                <!-- 负责人信息 -->
                <?php if(!empty($loft['owner_name'])): ?>
                <div class="sidebar-card">
                    <h3 class="sidebar-title"><i class="fas fa-user-circle"></i> 负责人信息</h3>
                    <div class="owner-card">
                        <div class="owner-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div>
                            <div class="owner-name"><?php echo h($loft['owner_name']); ?></div>
                            <div class="owner-role">公棚负责人</div>
                        </div>
                    </div>
                    <?php if(!empty($loft['owner_phone'])): ?>
                    <a href="tel:<?php echo h($loft['owner_phone']); ?>" class="btn btn-outline btn-block">
                        <i class="fas fa-phone"></i> 联系负责人
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- 快速导航 -->
                <div class="sidebar-card">
                    <h3 class="sidebar-title"><i class="fas fa-compass"></i> 快速导航</h3>
                    <div style="display:flex;flex-direction:column;gap:8px;">
                        <a href="/loft/" class="action-btn" style="justify-content:flex-start;">
                            <i class="fas fa-list"></i> 浏览更多公棚
                        </a>
                        <a href="/pigeon/" class="action-btn" style="justify-content:flex-start;">
                            <i class="fas fa-dove"></i> 铭鸽展厅
                        </a>
                        <a href="/article/" class="action-btn" style="justify-content:flex-start;">
                            <i class="fas fa-newspaper"></i> 赛鸽资讯
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/_footer.php'; ?>

    <script>
        // 阅读进度条
        window.addEventListener('scroll', function() {
            var scrollTop = window.scrollY || document.documentElement.scrollTop;
            var docHeight = document.documentElement.scrollHeight - window.innerHeight;
            var progress = docHeight > 0 ? (scrollTop / docHeight) * 100 : 0;
            document.getElementById('readingProgress').style.width = Math.min(progress, 100) + '%';
        });

        // 收藏切换
        function toggleFav() {
            var btn = document.getElementById('favBtn');
            var isActive = btn.classList.toggle('fav-active');
            btn.innerHTML = isActive
                ? '<i class="fas fa-heart"></i> 已收藏'
                : '<i class="far fa-heart"></i> 收藏';
        }

        // 分享
        function sharePage() {
            var url = window.location.href;
            var title = document.title;
            if (navigator.share) {
                navigator.share({ title: title, url: url }).catch(function(){});
            } else {
                navigator.clipboard.writeText(url).then(function() {
                    alert('链接已复制到剪贴板');
                }).catch(function() {
                    prompt('复制此链接分享：', url);
                });
            }
        }

        // 滚动到指定区块
        function scrollToSection(id) {
            var el = document.getElementById('section-' + id);
            if (el) {
                var offset = 60;
                var top = el.getBoundingClientRect().top + window.scrollY - offset;
                window.scrollTo({ top: top, behavior: 'smooth' });
            }
            // 更新 tab active 状态
            document.querySelectorAll('.tab-link').forEach(function(t) { t.classList.remove('active'); });
            var evt = event || window.event;
            if (evt && evt.target) evt.target.classList.add('active');
        }

        // 相册查看
        function openGallery(src) {
            if (src) {
                window.open(src, '_blank');
            }
        }

        // 倒计时
        <?php if (!empty($nextRaceDate) && ($raceTs ?? 0) > time()): ?>
        (function() {
            var target = <?php echo $raceTs; ?> * 1000;
            function updateCountdown() {
                var now = Date.now();
                var diff = Math.max(0, target - now);
                var days = Math.floor(diff / 86400000);
                var hours = Math.floor((diff % 86400000) / 3600000);
                var mins = Math.floor((diff % 3600000) / 60000);
                var secs = Math.floor((diff % 60000) / 1000);
                document.getElementById('cd-days').textContent = days;
                document.getElementById('cd-hours').textContent = String(hours).padStart(2, '0');
                document.getElementById('cd-mins').textContent = String(mins).padStart(2, '0');
                document.getElementById('cd-secs').textContent = String(secs).padStart(2, '0');
            }
            updateCountdown();
            setInterval(updateCountdown, 1000);
        })();
        <?php endif; ?>

        // 高亮当前 tab（滚动监听）
        (function() {
            var sections = ['info', 'schedule', 'results', 'gallery'];
            var tabs = document.querySelectorAll('.tab-link');
            window.addEventListener('scroll', function() {
                var current = '';
                sections.forEach(function(id) {
                    var el = document.getElementById('section-' + id);
                    if (el && el.getBoundingClientRect().top < 200) {
                        current = id;
                    }
                });
                tabs.forEach(function(tab, i) {
                    tab.classList.toggle('active', sections[i] === current);
                });
            });
        })();
    </script>
</body>
</html>