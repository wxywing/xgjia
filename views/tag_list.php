<?php
/**
 * 标签列表页 /tags/
 */
require_once dirname(__DIR__) . '/app/config/config.php';

$page_title = '文章标签' . ' | ' . SITE_NAME;
$meta_description = '信鸽之家文章标签分类，包括赛前调整、幼鸽管理、公棚赛、血统等热门话题标签。';
$canonical_url = 'https://www.xgjia.com/tags/';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo h($page_title); ?></title>
    <meta name="description" content="<?php echo h($meta_description); ?>">
    <meta name="keywords" content="赛鸽标签,信鸽文章标签,赛前调整,幼鸽管理,公棚赛,血统">
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
    <h1 style="font-size:28px;color:#1a5fa8;margin-bottom:24px;">文章标签</h1>
    
    <?php if (!empty($grouped['topic'])): ?>
    <section style="margin-bottom:32px;">
        <h2 style="font-size:20px;color:#333;margin-bottom:16px;border-left:4px solid #1a5fa8;padding-left:12px;">话题标签</h2>
        <div style="display:flex;flex-wrap:wrap;gap:12px;">
            <?php foreach ($grouped['topic'] as $tag): ?>
            <a href="/tag/<?php echo h($tag['slug']); ?>/" 
               style="display:inline-block;padding:8px 16px;background:#f0f4f8;border-radius:20px;color:#1a5fa8;text-decoration:none;font-size:14px;">
                <?php echo h($tag['name']); ?>
                <span style="color:#999;font-size:12px;margin-left:4px;">(<?php echo $tag['article_count']; ?>)</span>
            </a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <?php if (!empty($grouped['strain'])): ?>
    <section style="margin-bottom:32px;">
        <h2 style="font-size:20px;color:#333;margin-bottom:16px;border-left:4px solid #c9a84c;padding-left:12px;">品系标签</h2>
        <div style="display:flex;flex-wrap:wrap;gap:12px;">
            <?php foreach ($grouped['strain'] as $tag): ?>
            <a href="/tag/<?php echo h($tag['slug']); ?>/" 
               style="display:inline-block;padding:8px 16px;background:#fef9e7;border-radius:20px;color:#b8860b;text-decoration:none;font-size:14px;">
                <?php echo h($tag['name']); ?>
                <span style="color:#999;font-size:12px;margin-left:4px;">(<?php echo $tag['article_count']; ?>)</span>
            </a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <?php if (!empty($grouped['season'])): ?>
    <section style="margin-bottom:32px;">
        <h2 style="font-size:20px;color:#333;margin-bottom:16px;border-left:4px solid #27ae60;padding-left:12px;">赛季标签</h2>
        <div style="display:flex;flex-wrap:wrap;gap:12px;">
            <?php foreach ($grouped['season'] as $tag): ?>
            <a href="/tag/<?php echo h($tag['slug']); ?>/" 
               style="display:inline-block;padding:8px 16px;background:#e8f6ef;border-radius:20px;color:#27ae60;text-decoration:none;font-size:14px;">
                <?php echo h($tag['name']); ?>
                <span style="color:#999;font-size:12px;margin-left:4px;">(<?php echo $tag['article_count']; ?>)</span>
            </a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <?php if (!empty($grouped['city'])): ?>
    <section style="margin-bottom:32px;">
        <h2 style="font-size:20px;color:#333;margin-bottom:16px;border-left:4px solid #e74c3c;padding-left:12px;">城市标签</h2>
        <div style="display:flex;flex-wrap:wrap;gap:12px;">
            <?php foreach ($grouped['city'] as $tag): ?>
            <a href="/tag/<?php echo h($tag['slug']); ?>/" 
               style="display:inline-block;padding:8px 16px;background:#fce4ec;border-radius:20px;color:#e74c3c;text-decoration:none;font-size:14px;">
                <?php echo h($tag['name']); ?>
                <span style="color:#999;font-size:12px;margin-left:4px;">(<?php echo $tag['article_count']; ?>)</span>
            </a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/_footer.php'; ?>
