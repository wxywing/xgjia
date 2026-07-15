<?php
/**
 * 信鸽之家 - 文章详情页（B方案 清新专业 · 丰富版）
 */
require_once dirname(__DIR__) . '/app/config/config.php';
require_once dirname(__DIR__) . '/app/core/InternalLinker.php';

extract($data);

// 文章详情页 FAQ（GEO/SEO）
$article_faqs = [
    [
        "question" => "信鸽之家是什么平台？",
        "answer"   => "信鸽之家是中国赛鸽数据聚合平台，提供公棚赛事查询、足环号追踪、鸽主排名、血统证书等工具，帮助鸽友高效管理赛鸽信息。",
    ],
    [
        "question" => "如何查询信鸽足环号？",
        "answer"   => "在信鸽之家首页搜索框输入完整足环号，可查询赛鸽血统档案、历史赛绩及所在公棚信息，报告永久免费。",
    ],
    [
        "question" => "如何获取血统证书？",
        "answer"   => "在信鸽之家铭鸽详情页，点击'生成血统证书'即可自动生成PDF血统证书，包含赛鸽父母祖代完整血统信息。",
    ],
    [
        "question" => "如何查看各城市公棚赛事数据？",
        "answer"   => "在城市赛事中心选择目标城市，可查看该城市的公棚数量、赛事场次及累计参赛羽数，并进入城市TOP排行查看各城市赛鸽分速排名。",
    ],
    [
        "question" => "信鸽之家收录哪些赛事数据？",
        "answer"   => "信鸽之家收录全国各省市协会赛事及公棚赛数据，支持按省份、城市、公棚名称、足环号多维查询，数据持续更新。",
    ],
];

// Build FAQ JSON-LD
$ld_article_faqpage = [
    "@context" => "https://schema.org",
    "@type" => "FAQPage",
    "mainEntity" => [],
];
foreach ($article_faqs as $f) {
    $ld_article_faqpage["mainEntity"][] = [
        "@type" => "Question",
        "name" => $f["question"],
        "acceptedAnswer" => [
            "@type" => "Answer",
            "text" => $f["answer"],
        ],
    ];
}

$page_title = ($article['title'] ?? '文章') . ' - 赛鸽资讯 | ' . SITE_NAME;
$currentPath = $_SERVER['REQUEST_URI'] ?? '';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo h($page_title); ?></title>
    <meta name="description" content="<?php echo h(mb_substr(strip_tags($article['content'] ?? ''), 0, 150)); ?>">
    <meta property="og:title" content="<?php echo h($page_title); ?>">
    <?php if(!empty($article['cover'])): ?>
    <meta property="og:image" content="<?php echo h($article['cover']); ?>">
    <?php endif; ?>
    <meta property="og:type" content="article">
    <meta name="keywords" content="<?php echo h(($article['title'] ?? '') . ',赛鸽资讯,' . KEYWORDS_ARTICLES); ?>">
    <link rel="canonical" href="https://www.xgjia.com/article/<?php echo intval($article['id']); ?>.html">
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/public/css/b-scheme.css">
    <link rel="shortcut icon" href="/public/images/favicon.ico">
    <script type="application/ld+json"><?php echo json_encode($ld_article_faqpage, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>
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

        /* ========== 阅读进度条 ========== */
        #reading-progress {
            position: fixed; top: 0; left: 0; width: 0%; height: 3px;
            background: linear-gradient(90deg, var(--primary), var(--accent));
            z-index: 9999; transition: width 0.15s ease;
        }

        .container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }

        /* ========== 面包屑 ========== */
        .breadcrumb { padding: 14px 0; font-size: 13px; color: var(--text-light); }
        .breadcrumb a { color: var(--primary); }
        .breadcrumb a:hover { text-decoration: underline; }
        .breadcrumb i { margin: 0 8px; font-size: 10px; }

        /* ========== 布局 ========== */
        .article-layout { display: grid; grid-template-columns: 1fr 320px; gap: 30px; padding-bottom: 60px; }
        @media (max-width: 1024px) { .article-layout { grid-template-columns: 1fr; } }

        /* ========== 主内容区 ========== */
        .article-main {
            background: var(--white); border-radius: var(--radius);
            overflow: hidden; box-shadow: var(--shadow); border: 1px solid var(--border);
        }
        .article-cover {
            width: 100%; height: 340px; background: linear-gradient(135deg, #e8f4fd 0%, #d0e8f8 100%);
            display: flex; align-items: center; justify-content: center; font-size: 80px;
            overflow: hidden; position: relative;
        }
        .article-cover img { width: 100%; height: 100%; object-fit: cover; }
        .article-header { padding: 30px 34px 22px; border-bottom: 1px solid var(--border); }
        .article-category {
            display: inline-block; padding: 4px 14px; background: var(--primary);
            color: white; border-radius: 20px; font-size: 11px; font-weight: 700; margin-bottom: 12px;
        }
        .article-title { font-size: 28px; font-weight: 700; color: var(--text); line-height: 1.45; margin-bottom: 16px; }
        .article-meta { display: flex; gap: 22px; font-size: 13px; color: var(--text-light); flex-wrap: wrap; align-items: center; }
        .article-meta i { color: var(--accent); margin-right: 5px; }

        /* ========== 作者卡片（内联） ========== */
        .author-card-inline {
            display: flex; align-items: center; gap: 14px; padding: 16px 34px;
            background: var(--bg); border-bottom: 1px solid var(--border);
        }
        .author-avatar {
            width: 48px; height: 48px; border-radius: 50%; background: var(--primary);
            display: flex; align-items: center; justify-content: center;
            color: white; font-size: 20px; font-weight: 700; flex-shrink: 0;
            overflow: hidden;
        }
        .author-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .author-info { flex: 1; }
        .author-name { font-size: 14px; font-weight: 600; color: var(--text); }
        .author-desc { font-size: 12px; color: var(--text-light); margin-top: 2px; }
        .author-stats { font-size: 12px; color: var(--text-light); }
        .author-stats strong { color: var(--primary); }

        /* ========== 文章内容 ========== */
        .article-body { padding: 30px 34px; font-size: 15px; color: var(--text); line-height: 2; }
        .article-body p { margin-bottom: 18px; }
        .article-body h2 {
            font-size: 22px; font-weight: 700; color: var(--text); margin: 32px 0 14px;
            padding-left: 14px; border-left: 4px solid var(--primary); line-height: 1.4;
        }
        .article-body h3 {
            font-size: 18px; font-weight: 700; color: var(--text); margin: 24px 0 12px;
            padding-left: 12px; border-left: 3px solid var(--accent); line-height: 1.4;
        }
        .article-body img { max-width: 100%; height: auto; border-radius: 8px; margin: 16px 0; }
        .article-body ul, .article-body ol { margin: 14px 0 18px 24px; }
        .article-body li { margin-bottom: 8px; }
        .article-body blockquote {
            border-left: 4px solid var(--accent); padding: 14px 20px;
            background: linear-gradient(135deg, #fdfcf5, #f9f5e8); margin: 18px 0;
            border-radius: 0 8px 8px 0; color: #5a4a2a; font-style: italic;
        }
        .article-body code {
            background: var(--bg); padding: 2px 7px; border-radius: 4px;
            font-size: 13px; color: var(--primary); font-family: "SF Mono", Consolas, monospace;
        }

        /* ========== 标签 ========== */
        .article-tags { display: flex; gap: 8px; flex-wrap: wrap; padding: 0 34px 20px; }
        .tag-pill {
            display: inline-block; padding: 4px 14px; border-radius: 20px; font-size: 12px; font-weight: 600;
            transition: all 0.25s; cursor: default;
        }
        .tag-pill:hover { transform: translateY(-1px); box-shadow: 0 2px 8px rgba(0,0,0,0.12); }
        .tag-0 { background: #e8f4fd; color: #1a5fa8; }
        .tag-1 { background: #fdf3e8; color: #c9721a; }
        .tag-2 { background: #e8f8f0; color: #1a8a5a; }
        .tag-3 { background: #f3e8f8; color: #7a1aa8; }
        .tag-4 { background: #f8e8e8; color: #a81a1a; }
        .tag-5 { background: #e8f0f8; color: #1a6ea8; }

        /* ========== 分享按钮 ========== */
        .article-share {
            display: flex; gap: 12px; align-items: center; padding: 18px 34px;
            border-top: 1px solid var(--border); flex-wrap: wrap;
        }
        .share-label { font-size: 13px; color: var(--text-light); font-weight: 600; margin-right: 4px; }
        .share-btn {
            width: 38px; height: 38px; border-radius: 50%; display: flex;
            align-items: center; justify-content: center; color: white; cursor: pointer;
            border: none; font-size: 15px; transition: all 0.3s; position: relative;
        }
        .share-btn:hover { transform: scale(1.15) translateY(-2px); box-shadow: 0 4px 14px rgba(0,0,0,0.2); }
        .share-btn .tooltip {
            position: absolute; bottom: calc(100% + 8px); left: 50%; transform: translateX(-50%);
            background: #333; color: white; padding: 4px 10px; border-radius: 6px;
            font-size: 11px; white-space: nowrap; opacity: 0; pointer-events: none;
            transition: opacity 0.2s;
        }
        .share-btn:hover .tooltip { opacity: 1; }
        .share-wechat { background: #07c160; }
        .share-weibo { background: #e6162d; }
        .share-qq { background: #1296db; }
        .share-copy { background: var(--text-light); }
        .share-native { background: var(--primary); }

        /* ========== 作者简介板块 ========== */
        .author-bio {
            margin: 24px 34px; padding: 24px; background: linear-gradient(135deg, var(--bg), #eef4fb);
            border-radius: var(--radius); border: 1px solid var(--border); display: flex; gap: 18px; align-items: flex-start;
        }
        .author-bio-avatar {
            width: 64px; height: 64px; border-radius: 50%; background: var(--primary);
            display: flex; align-items: center; justify-content: center;
            color: white; font-size: 26px; font-weight: 700; flex-shrink: 0; overflow: hidden;
        }
        .author-bio-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .author-bio-name { font-size: 16px; font-weight: 700; color: var(--text); }
        .author-bio-text { font-size: 13px; color: var(--text-light); margin-top: 6px; line-height: 1.7; }
        .author-bio-stats { display: flex; gap: 16px; margin-top: 10px; font-size: 12px; color: var(--text-light); }
        .author-bio-stats span { display: flex; align-items: center; gap: 4px; }
        .author-bio-stats i { color: var(--accent); }

        /* ========== 相关文章（网格） ========== */
        .related-grid-section { padding: 0 34px 24px; }
        .related-grid-title {
            font-size: 16px; font-weight: 700; color: var(--text); margin-bottom: 16px;
            display: flex; align-items: center; gap: 8px;
        }
        .related-grid-title i { color: var(--accent); }
        .related-grid {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;
        }
        @media (max-width: 768px) { .related-grid { grid-template-columns: 1fr 1fr; } }
        @media (max-width: 480px) { .related-grid { grid-template-columns: 1fr; } }
        .related-card {
            background: var(--bg); border-radius: 10px; overflow: hidden;
            transition: all 0.3s; border: 1px solid var(--border); cursor: pointer;
        }
        .related-card:hover { box-shadow: var(--shadow-hover); transform: translateY(-3px); }
        .related-card-thumb {
            width: 100%; height: 120px; background: var(--white); display: flex;
            align-items: center; justify-content: center; font-size: 36px; overflow: hidden;
        }
        .related-card-thumb img { width: 100%; height: 100%; object-fit: cover; }
        .related-card-body { padding: 12px 14px; }
        .related-card-title {
            font-size: 13px; font-weight: 600; color: var(--text); line-height: 1.5;
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
        }
        .related-card-meta { font-size: 11px; color: var(--text-light); margin-top: 8px; display: flex; gap: 12px; }
        .related-card-meta i { color: var(--accent); margin-right: 3px; }

        /* ========== 上下篇导航 ========== */
        .article-nav { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin: 0 34px 24px; }
        .article-nav-item {
            background: var(--bg); border-radius: 10px; padding: 16px 18px;
            border: 1px solid var(--border); transition: all 0.3s; cursor: pointer;
        }
        .article-nav-item:hover { box-shadow: var(--shadow-hover); border-color: var(--primary); }
        .article-nav-label { font-size: 11px; color: var(--text-light); margin-bottom: 6px; display: flex; align-items: center; gap: 4px; }
        .article-nav-title { font-size: 14px; font-weight: 600; color: var(--text); line-height: 1.4; }
        .article-nav-title:hover { color: var(--primary); }
        .nav-empty { color: var(--text-light); font-size: 13px; }

        /* ========== 返回列表 ========== */
        .back-to-list { padding: 0 34px 28px; }
        .back-btn {
            display: inline-flex; align-items: center; gap: 8px; padding: 10px 22px;
            background: var(--white); border: 1px solid var(--border); border-radius: 8px;
            font-size: 13px; font-weight: 600; color: var(--text); cursor: pointer; transition: all 0.25s;
        }
        .back-btn:hover { background: var(--primary); color: white; border-color: var(--primary); }

        /* ========== 侧边栏 ========== */
        .sidebar { display: flex; flex-direction: column; gap: 20px; }
        @media (max-width: 1024px) { .sidebar { display: none; } }
        .sidebar-card {
            background: var(--white); border-radius: var(--radius); padding: 20px;
            box-shadow: var(--shadow); border: 1px solid var(--border);
        }
        .sidebar-title {
            font-size: 15px; font-weight: 700; color: var(--text); margin-bottom: 14px;
            display: flex; align-items: center; gap: 8px; padding-bottom: 11px; border-bottom: 1px solid var(--border);
        }
        .sidebar-title i { color: var(--accent); }

        /* 目录（桌面侧边栏） */
        .toc-nav { display: flex; flex-direction: column; gap: 6px; }
        .toc-item {
            font-size: 13px; color: var(--text-light); padding: 6px 10px; border-radius: 6px;
            cursor: pointer; transition: all 0.2s; line-height: 1.4;
            border-left: 2px solid transparent;
        }
        .toc-item:hover { background: var(--bg); color: var(--primary); border-left-color: var(--primary-light); }
        .toc-item.active { background: #e8f4fd; color: var(--primary); border-left-color: var(--primary); font-weight: 600; }
        .toc-item.h3 { padding-left: 24px; font-size: 12px; }

        /* 侧边栏相关文章（简洁列表） */
        .sidebar-related { display: flex; flex-direction: column; gap: 12px; }
        .sidebar-rel-item { display: flex; gap: 12px; align-items: flex-start; }
        .sidebar-rel-thumb {
            width: 72px; height: 56px; border-radius: 8px; background: var(--bg);
            display: flex; align-items: center; justify-content: center; font-size: 22px;
            flex-shrink: 0; overflow: hidden; border: 1px solid var(--border);
        }
        .sidebar-rel-thumb img { width: 100%; height: 100%; object-fit: cover; }
        .sidebar-rel-title { font-size: 13px; font-weight: 600; color: var(--text); line-height: 1.5; }
        .sidebar-rel-title:hover { color: var(--primary); }
        .sidebar-rel-meta { font-size: 11px; color: var(--text-light); margin-top: 3px; }

        /* 分类列表 */
        .cat-list { display: flex; flex-direction: column; gap: 7px; }
        .cat-link {
            display: flex; justify-content: space-between; align-items: center;
            padding: 10px 14px; background: var(--bg); border-radius: 8px;
            font-size: 13px; color: var(--text); transition: all 0.25s;
        }
        .cat-link:hover { background: var(--primary); color: white; }
        .cat-count { font-size: 11px; font-weight: 700; padding: 2px 9px; background: var(--border); border-radius: 10px; }
        .cat-link:hover .cat-count { background: rgba(255,255,255,0.3); color: white; }

        /* ========== Footer ========== */
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

        /* ========== 响应式 ========== */
        @media (max-width: 768px) {
            .article-cover { height: 200px; }
            .article-title { font-size: 22px; }
            .article-header, .article-body, .author-card-inline,
            .article-share, .author-bio, .related-grid-section,
            .article-nav, .back-to-list { padding-left: 20px; padding-right: 20px; }
            .article-nav { grid-template-columns: 1fr; }
            .author-bio { flex-direction: column; align-items: center; text-align: center; }
            .author-bio-stats { justify-content: center; }
            .footer-grid { grid-template-columns: 1fr 1fr; gap: 25px; }
            .footer-brand { grid-column: 1 / -1; }
        }
    </style>

    <!-- JSON-LD Structured Data -->
    <?php
    $_ld_article = [
        '@context' => 'https://schema.org',
        '@type' => 'Article',
        'headline' => $article['title'] ?? '',
        'description' => mb_substr(strip_tags($article['content'] ?? $article['description'] ?? ''), 0, 200),
        'image' => $article['cover'] ?? '',
        'datePublished' => $article['created_at'] ?? date('c'),
        'dateModified' => $article['updated_at'] ?? $article['created_at'] ?? date('c'),
        'url' => 'https://www.xgjia.com/article/' . ($article['id'] ?? '') . '.html',
    ];
    if (!empty($article['author_name'])):
        $_ld_article['author'] = ['@type' => 'Person', 'name' => $article['author_name']];
    endif;
    $_ld_article['publisher'] = [
        '@type' => 'Organization',
        'name' => SITE_NAME,
        'url' => 'https://www.xgjia.com',
    ];
    ?>
    <script type="application/ld+json"><?php echo json_encode($_ld_article, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>

    <!-- BreadcrumbList -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "BreadcrumbList",
        "itemListElement": [
            {"@type": "ListItem", "position": 1, "name": "首页", "item": "https://www.xgjia.com"},
            {"@type": "ListItem", "position": 2, "name": "赛鸽资讯", "item": "https://www.xgjia.com/article/"},
            {"@type": "ListItem", "position": 3, "name": "<?php echo h($article['title'] ?? '文章'); ?>"}
        ]
    }
    </script>
</head>
<body>

<!-- 阅读进度条 -->
<div id="reading-progress"></div>

<?php include __DIR__ . '/_head.php'; ?>

<div class="container">
    <!-- 面包屑 -->
    <div class="breadcrumb">
        <a href="/">首页</a> <i class="fas fa-chevron-right"></i>
        <a href="/article/">资讯</a> <i class="fas fa-chevron-right"></i>
        <span style="color:var(--text);"><?php echo h(mb_substr($article['title'] ?? '', 0, 20)); ?>...</span>
    </div>

    <div class="article-layout">
        <!-- ======== 主内容 ======== -->
        <div class="article-main">

            <!-- 封面图 -->
            <div class="article-cover">
                <?php if(!empty($article['cover'])): ?>
                <img src="<?php echo h($article['cover']); ?>" alt="<?php echo h($article['title'] ?? ''); ?>">
                <?php else: ?>
                📰
                <?php endif; ?>
            </div>

            <!-- 标题区 -->
            <div class="article-header">
                <?php if(!empty($article['category_name'])): ?>
                <a href="/article/?category=<?php echo intval($article['category_id'] ?? 0); ?>" class="article-category" style="text-decoration:none;display:inline-block;"><?php echo h($article['category_name']); ?></a>
                <?php endif; ?>
                <h1 class="article-title"><?php echo h($article['title'] ?? ''); ?></h1>
                <div class="article-meta">
                    <span><i class="fas fa-user"></i> <?php echo h($article['author_name'] ?? '管理员'); ?></span>
                    <span><i class="fas fa-calendar-alt"></i> <?php echo date('Y-m-d', strtotime($article['created_at'] ?? 'now')); ?></span>
                    <span><i class="fas fa-eye"></i> <?php echo number_format($article['views'] ?? 0); ?> 阅读</span>
                    <?php if(!empty($article['source'])): ?>
                    <span><i class="fas fa-link"></i> 来源：<?php echo h($article['source']); ?></span>
                    <?php endif; ?>
                </div>
            </div>



            <!-- 文章内容 -->
            <h2 style="position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);border:0;white-space:nowrap;">正文详情</h2>
            <div class="article-body" id="article-body">
                <?php echo InternalLinker::process($article['content'] ?? '<p style="color:var(--text-light);">内容加载中...</p>'); ?>
            </div>

            <!-- 标签 -->
            <?php if(!empty($article['tags'])): ?>
            <?php
                $tags = is_array($article['tags']) ? $article['tags'] : explode(',', $article['tags']);
            ?>
            <div class="article-tags">
                <?php foreach($tags as $i => $tag):
                    $tag = trim($tag);
                    if($tag === '') continue;
                    $cls = 'tag-' . ($i % 6);
                ?>
                <span class="tag-pill <?php echo $cls; ?>"><?php echo h($tag); ?></span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- 分享按钮 -->
            <div class="article-share">
                <span class="share-label"><i class="fas fa-share-alt"></i> 分享到</span>
                <button class="share-btn share-wechat" onclick="shareWechat()" title="微信">
                    <i class="fab fa-weixin"></i>
                    <span class="tooltip">微信分享</span>
                </button>
                <button class="share-btn share-weibo" onclick="shareWeibo()" title="微博">
                    <i class="fab fa-weibo"></i>
                    <span class="tooltip">微博分享</span>
                </button>
                <button class="share-btn share-qq" onclick="shareQQ()" title="QQ">
                    <i class="fab fa-qq"></i>
                    <span class="tooltip">QQ分享</span>
                </button>
                <button class="share-btn share-copy" onclick="copyLink()" title="复制链接">
                    <i class="fas fa-link"></i>
                    <span class="tooltip" id="copy-tooltip">复制链接</span>
                </button>
                <button class="share-btn share-native" onclick="nativeShare()" title="更多分享">
                    <i class="fas fa-share"></i>
                    <span class="tooltip">更多方式</span>
                </button>
            </div>

            <!-- 作者简介板块 -->
            <div class="author-bio">
                <div class="author-bio-avatar">
                    <?php if(!empty($article['author_avatar'])): ?>
                    <img loading="lazy" src="<?php echo h($article['author_avatar']); ?>" alt="<?php echo h($article['author_name'] ?? '作者'); ?>">
                    <?php else: ?>
                    <?php echo mb_substr($article['author_name'] ?? '管', 0, 1); ?>
                    <?php endif; ?>
                </div>
                <div>
                    <div class="author-bio-name"><?php echo h($article['author_name'] ?? '管理员'); ?></div>
                    <div class="author-bio-text"><?php echo h($article['author_bio'] ?? '信鸽之家官方编辑团队，致力于分享专业信鸽养殖知识、赛事资讯和名家经验。'); ?></div>
                </div>
            </div>

            <!-- 相关文章（网格） -->
            <?php if(!empty($relatedArticles)): ?>
            <div class="related-grid-section">
                <div class="related-grid-title"><i class="fas fa-th-large"></i> 相关推荐</div>
                <div class="related-grid">
                    <?php foreach($relatedArticles as $ra): ?>
                    <a href="/article/<?php echo intval($ra['id']); ?>.html" class="related-card">
                        <div class="related-card-thumb">
                            <?php if(!empty($ra['cover'])): ?>
                            <img loading="lazy" src="<?php echo h($ra['cover']); ?>" alt="<?php echo h($ra['title'] ?? ''); ?>">
                            <?php else: ?>
                            📰
                            <?php endif; ?>
                        </div>
                        <div class="related-card-body">
                            <div class="related-card-title"><?php echo h($ra['title'] ?? ''); ?></div>
                            <div class="related-card-meta">
                                <span><i class="fas fa-eye"></i> <?php echo number_format($ra['views'] ?? 0); ?></span>
                                <span><i class="fas fa-calendar-alt"></i> <?php echo date('m-d', strtotime($ra['created_at'] ?? 'now')); ?></span>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- 上下篇导航 -->
            <div class="article-nav">
                <div class="article-nav-item">
                    <?php if(!empty($prevArticle)): ?>
                    <a href="/article/<?php echo intval($prevArticle['id']); ?>.html" style="display:block;">
                        <div class="article-nav-label"><i class="fas fa-chevron-left"></i> 上一篇</div>
                        <div class="article-nav-title"><?php echo h($prevArticle['title'] ?? ''); ?></div>
                    </a>
                    <?php else: ?>
                    <div class="nav-empty">已是第一篇文章</div>
                    <?php endif; ?>
                </div>
                <div class="article-nav-item" style="text-align:right;">
                    <?php if(!empty($nextArticle)): ?>
                    <a href="/article/<?php echo intval($nextArticle['id']); ?>.html" style="display:block;">
                        <div class="article-nav-label" style="justify-content:flex-end; display:flex; gap:4px;">下一篇 <i class="fas fa-chevron-right"></i></div>
                        <div class="article-nav-title"><?php echo h($nextArticle['title'] ?? ''); ?></div>
                    </a>
                    <?php else: ?>
                    <div class="nav-empty" style="text-align:right;">已是最后一篇文章</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 返回列表 -->
            <div class="back-to-list">
                <a href="/article/" class="back-btn">
                    <i class="fas fa-arrow-left"></i> 返回资讯列表
                </a>
            </div>

        </div><!-- /.article-main -->

        <!-- ======== 侧边栏（桌面端） ======== -->
        <div class="sidebar">

            <!-- 目录 -->
            <div class="sidebar-card" id="toc-card">
                <h3 class="sidebar-title"><i class="fas fa-list-ul"></i> 文章目录</h3>
                <nav class="toc-nav" id="toc-nav">
                    <div style="font-size:13px;color:var(--text-light);padding:8px 0;">正在提取目录...</div>
                </nav>
            </div>

            <!-- 相关文章（侧边栏简洁版） -->
            <?php if(!empty($relatedArticles)): ?>
            <div class="sidebar-card">
                <h3 class="sidebar-title"><i class="fas fa-th-large"></i> 相关推荐</h3>
                <div class="sidebar-related">
                    <?php foreach($relatedArticles as $ra): ?>
                    <a href="/article/<?php echo intval($ra['id']); ?>.html" class="sidebar-rel-item">
                        <div class="sidebar-rel-thumb">
                            <?php if(!empty($ra['cover'])): ?>
                            <img loading="lazy" src="<?php echo h($ra['cover']); ?>" alt="<?php echo h($ra['title'] ?? ''); ?>">
                            <?php else: ?>
                            📰
                            <?php endif; ?>
                        </div>
                        <div>
                            <div class="sidebar-rel-title"><?php echo h($ra['title'] ?? ''); ?></div>
                            <div class="sidebar-rel-meta"><i class="fas fa-eye"></i> <?php echo number_format($ra['views'] ?? 0); ?></div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- 热门文章（若无$data变量则隐藏） -->
            <?php
            // 注意：控制器当前未传入 $hotArticles，此处保留结构供后续启用
            ?>
            <?php if(!empty($hotArticles)): ?>
            <div class="sidebar-card">
                <h3 class="sidebar-title"><i class="fas fa-fire"></i> 热门文章</h3>
                <div class="sidebar-related">
                    <?php foreach($hotArticles as $h): ?>
                    <a href="/article/<?php echo intval($h['id']); ?>.html" class="sidebar-rel-item">
                        <div class="sidebar-rel-thumb">
                            <?php if(!empty($h['cover'])): ?>
                            <img loading="lazy" src="<?php echo h($h['cover']); ?>" alt="<?php echo h($h['title'] ?? ''); ?>">
                            <?php else: ?>
                            📰
                            <?php endif; ?>
                        </div>
                        <div>
                            <div class="sidebar-rel-title"><?php echo h($h['title'] ?? ''); ?></div>
                            <div class="sidebar-rel-meta"><i class="fas fa-eye"></i> <?php echo number_format($h['views'] ?? 0); ?></div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- 资讯分类 -->
            <div class="sidebar-card">
                <h3 class="sidebar-title"><i class="fas fa-folder-open"></i> 资讯分类</h3>
                <div class="cat-list">
                    <?php if(!empty($categories)): foreach($categories as $cat): ?>
                    <a href="/article/?category=<?php echo intval($cat['id']); ?>" class="cat-link">
                        <span><?php echo h($cat['name'] ?? ''); ?></span>
                        <span class="cat-count"><?php echo number_format($cat['article_count'] ?? 0); ?></span>
                    </a>
                    <?php endforeach; endif; ?>
                </div>
            </div>

        </div><!-- /.sidebar -->
    </div>
</div>

<?php include __DIR__ . '/_footer.php'; ?>

<!-- ========== JS ========== -->
<script>
(function(){
    // ---- 阅读进度条 ----
    const progressBar = document.getElementById('reading-progress');
    function updateProgress(){
        const body = document.body;
        const winH = window.innerHeight;
        const docH = body.scrollHeight - winH;
        const scrolled = window.scrollY;
        const pct = docH > 0 ? (scrolled / docH) * 100 : 0;
        progressBar.style.width = Math.min(pct, 100) + '%';
    }
    window.addEventListener('scroll', updateProgress, {passive:true});
    updateProgress();

    // ---- 从内容自动生成目录 ----
    const bodyEl = document.getElementById('article-body');
    const tocNav = document.getElementById('toc-nav');
    if(bodyEl && tocNav){
        const heads = bodyEl.querySelectorAll('h2,h3');
        if(heads.length > 0){
            tocNav.innerHTML = '';
            heads.forEach((h, i) => {
                const id = 'toc-head-' + i;
                h.id = id;
                const a = document.createElement('a');
                a.href = '#' + id;
                a.className = 'toc-item' + (h.tagName === 'H3' ? ' h3' : '');
                a.textContent = h.textContent;
                a.addEventListener('click', function(e){
                    e.preventDefault();
                    h.scrollIntoView({behavior:'smooth', block:'start'});
                    // 高亮当前
                    tocNav.querySelectorAll('.toc-item').forEach(x => x.classList.remove('active'));
                    a.classList.add('active');
                });
                tocNav.appendChild(a);
            });
        } else {
            tocNav.innerHTML = '<div style="font-size:13px;color:var(--text-light);padding:8px 0;">本文暂无小标题</div>';
            // 隐藏目录卡片
            const tocCard = document.getElementById('toc-card');
            if(tocCard) tocCard.style.display = 'none';
        }
    }

    // ---- 分享功能 ----
    const pageUrl = encodeURIComponent(location.href);
    const pageTitle = encodeURIComponent(document.title);

    window.shareWechat = function(){
        // 微信分享：复制链接并提示
        copyLink();
        alert('请粘贴链接到微信分享');
    };
    window.shareWeibo = function(){
        window.open('https://service.weibo.com/share/share.php?url=' + pageUrl + '&title=' + pageTitle, '_blank');
    };
    window.shareQQ = function(){
        window.open('https://connect.qq.com/widget/shareqq/index.html?url=' + pageUrl + '&title=' + pageTitle, '_blank');
    };
    window.copyLink = function(){
        const url = location.href;
        if(navigator.clipboard){
            navigator.clipboard.writeText(url).then(()=>{
                const tip = document.getElementById('copy-tooltip');
                tip.textContent = '已复制！';
                setTimeout(()=>{ tip.textContent = '复制链接'; }, 2000);
            });
        } else {
            // fallback
            const ta = document.createElement('textarea');
            ta.value = url; document.body.appendChild(ta);
            ta.select(); document.execCommand('copy');
            document.body.removeChild(ta);
            const tip = document.getElementById('copy-tooltip');
            tip.textContent = '已复制！';
            setTimeout(()=>{ tip.textContent = '复制链接'; }, 2000);
        }
    };
    window.nativeShare = function(){
        if(navigator.share){
            navigator.share({ title: document.title, url: location.href });
        } else {
            copyLink();
        }
    };
})();
</script>

</body>
</html>
