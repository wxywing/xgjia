<?php http_response_code(404); ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>页面未找到 - 信鸽之家</title>
    <meta name="description" content="抱歉，您访问的页面不存在或已被移动。返回信鸽之家首页浏览公棚、铭鸽、资讯等内容。">
    <meta name="keywords" content="信鸽之家,404,页面未找到">
    <meta name="robots" content="noindex, nofollow">
    <meta property="og:title" content="页面未找到 - 信鸽之家">
    <meta property="og:description" content="抱歉，您访问的页面不存在或已被移动。">
    <meta property="og:image" content="https://www.xgjia.com/public/images/og-cover.png">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="信鸽之家">
    <link rel="canonical" href="https://www.xgjia.com/404">
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/public/css/b-scheme.css">
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebSite",
        "name": "信鸽之家",
        "url": "https://www.xgjia.com",
        "description": "公棚查询·铭鸽展厅·血统图谱·赛事资讯",
        "potentialAction": {
            "@type": "SearchAction",
            "target": "https://www.xgjia.com/search?q={search_term_string}",
            "query-input": "required name=search_term_string"
        }
    }
    </script>
    <style>
        .error-page { text-align: center; padding: 80px 20px 60px; }
        .error-code { font-size: 120px; font-weight: 900; color: var(--primary); line-height: 1; opacity: 0.15; }
        .error-icon { font-size: 72px; color: var(--primary); margin-top: -60px; margin-bottom: 20px; }
        .error-page h1 { font-size: 28px; color: var(--text); margin-bottom: 12px; }
        .error-page p { font-size: 15px; color: var(--text-light); max-width: 500px; margin: 0 auto 30px; line-height: 1.7; }
        .error-links { display: flex; flex-wrap: wrap; justify-content: center; gap: 12px; }
        .error-links a { display: inline-flex; align-items: center; gap: 8px; padding: 10px 24px; background: var(--primary); color: #fff; border-radius: 8px; text-decoration: none; font-size: 14px; transition: opacity 0.2s; }
        .error-links a:hover { opacity: 0.85; }
        .error-links a.secondary { background: var(--border); color: var(--text); }
        @media (max-width: 768px) {
            .error-code { font-size: 80px; }
            .error-icon { font-size: 48px; margin-top: -40px; }
            .error-page h1 { font-size: 22px; }
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/_head.php'; ?>

<div class="error-page">
    <div class="error-code">404</div>
    <div class="error-icon"><i class="fas fa-search"></i></div>
    <h1>页面未找到</h1>
    <p>您访问的页面不存在或已被移除。请检查网址是否正确，或通过以下链接继续浏览。</p>
    
    <form class="error-search" action="/search" method="get" style="max-width:400px;margin:0 auto 24px;display:flex;gap:8px;">
        <input type="text" name="q" placeholder="搜索公棚、铭鸽、资讯…" style="flex:1;padding:10px 16px;border:1px solid var(--border);border-radius:8px;font-size:14px;">
        <button type="submit" style="padding:10px 20px;background:var(--primary);color:#fff;border:none;border-radius:8px;cursor:pointer;font-size:14px;"><i class="fas fa-search"></i> 搜索</button>
    </form>
    
    <div class="error-links">
        <a href="/"><i class="fas fa-home"></i> 返回首页</a>
        <a href="/pigeon/" class="secondary"><i class="fas fa-dove"></i> 铭鸽展厅</a>
        <a href="/loft/" class="secondary"><i class="fas fa-warehouse"></i> 公棚大全</a>
        <a href="/article/" class="secondary"><i class="fas fa-newspaper"></i> 赛鸽资讯</a>
        <a href="/shop/" class="secondary"><i class="fas fa-store"></i> 鸽舍展厅</a>
        <a href="/race/" class="secondary"><i class="fas fa-trophy"></i> 赛事成绩</a>
        <a href="/pedigree/strain/" class="secondary"><i class="fas fa-dna"></i> 血统品系</a>
    </div>
</div>

<?php include __DIR__ . '/_footer.php'; ?>
</body>
</html>
