<?php
/**
 * 标签详情页 /tag/{slug}/
 */
require_once dirname(__DIR__) . '/app/config/config.php';

$page_title = $tag['name'] . '相关文章' . ' | ' . SITE_NAME;
$meta_description = "信鸽之家关于「{$tag['name']}」的文章合集，共{$total}篇相关内容。";
$canonical_url = 'https://www.xgjia.com/tag/' . $tag['slug'] . '/';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo h($page_title); ?></title>
    <meta name="description" content="<?php echo h($meta_description); ?>">
    <meta name="keywords" content="<?php echo h($tag['name']); ?>,赛鸽文章,信鸽之家">
    <meta property="og:title" content="<?php echo h($page_title); ?>">
    <meta property="og:image" content="https://www.xgjia.com/public/images/og-cover.png">
    <link rel="canonical" href="<?php echo h($canonical_url); ?>">
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/public/css/b-scheme.css">
    <link rel="shortcut icon" href="/public/images/favicon.ico">
</head>
<body>
<?php include __DIR__ . '/_head.php'; ?>

<div class="container" style="max-width:1200px;margin:0 auto;padding:20px;">
    <!-- 面包屑 -->
    <nav style="margin-bottom:20px;font-size:14px;color:#666;">
        <a href="/" style="color:#1a5fa8;text-decoration:none;">首页</a> &gt;
        <a href="/tags/" style="color:#1a5fa8;text-decoration:none;">标签</a> &gt;
        <span style="color:#333;"><?php echo h($tag['name']); ?></span>
    </nav>

    <h1 style="font-size:28px;color:#1a5fa8;margin-bottom:8px;">
        「<?php echo h($tag['name']); ?>」相关文章
    </h1>
    <p style="color:#666;margin-bottom:24px;">共 <?php echo $total; ?> 篇文章</p>

    <?php if (!empty($articles)): ?>
    <div class="article-list" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:20px;">
        <?php foreach ($articles as $a): ?>
        <article class="article-card" style="background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.08);">
            <?php if (!empty($a['cover'])): ?>
            <a href="/article/<?php echo $a['id']; ?>.html">
                <img src="<?php echo h($a['cover']); ?>" alt="<?php echo h($a['title']); ?>" 
                     style="width:100%;height:160px;object-fit:cover;">
            </a>
            <?php endif; ?>
            <div style="padding:16px;">
                <h3 style="font-size:16px;margin-bottom:8px;">
                    <a href="/article/<?php echo $a['id']; ?>.html" style="color:#333;text-decoration:none;">
                        <?php echo h($a['title']); ?>
                    </a>
                </h3>
                <p style="font-size:14px;color:#666;line-height:1.6;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                    <?php echo h(mb_substr(strip_tags($a['summary'] ?? $a['content']), 0, 100)); ?>
                </p>
                <div style="margin-top:12px;font-size:12px;color:#999;">
                    <?php echo $a['published_at']; ?>
                </div>
            </div>
        </article>
        <?php endforeach; ?>
    </div>

    <?php if ($totalPages > 1): ?>
    <div class="pagination" style="margin-top:32px;text-align:center;">
        <?php if ($page > 1): ?>
        <a href="/tag/<?php echo h($tag['slug']); ?>/?page=<?php echo $page - 1; ?>" 
           style="display:inline-block;padding:8px 16px;background:#f0f4f8;color:#1a5fa8;text-decoration:none;border-radius:4px;margin:0 4px;">上一页</a>
        <?php endif; ?>
        
        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
        <a href="/tag/<?php echo h($tag['slug']); ?>/?page=<?php echo $i; ?>" 
           style="display:inline-block;padding:8px 16px;<?php echo $i == $page ? 'background:#1a5fa8;color:#fff;' : 'background:#f0f4f8;color:#1a5fa8;'; ?>text-decoration:none;border-radius:4px;margin:0 4px;">
            <?php echo $i; ?>
        </a>
        <?php endfor; ?>
        
        <?php if ($page < $totalPages): ?>
        <a href="/tag/<?php echo h($tag['slug']); ?>/?page=<?php echo $page + 1; ?>" 
           style="display:inline-block;padding:8px 16px;background:#f0f4f8;color:#1a5fa8;text-decoration:none;border-radius:4px;margin:0 4px;">下一页</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <p style="text-align:center;color:#666;padding:40px;">暂无相关文章</p>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/_footer.php'; ?>
