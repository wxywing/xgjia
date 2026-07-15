<?php
/**
 * 信鸽之家 - 资讯列表页（B方案 清新专业）
 */
require_once dirname(__DIR__) . '/app/config/config.php';

extract($data);

$page_title = (!empty($currentCategory) ? ($currentCategory['name'] ?? '分类') . ' - ' : '') . '资讯中心' . (($page ?? 1) > 1 ? ' - 第' . intval($page) . '页' : '') . ' | ' . SITE_NAME;
$currentPath = $_SERVER['REQUEST_URI'] ?? '';
$canonical_url = 'https://www.xgjia.com/article/' . ($_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '');
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo h($page_title); ?></title>
    <meta name="description" content="<?php echo h('信鸽之家资讯中心，已收录' . number_format($total ?? 0) . '篇赛鸽数据分析、养鸽知识百科、行业深度解读等专业内容' . (!empty($currentCategory) ? '【' . ($currentCategory['name'] ?? '') . '】' : '') . '。基于1270万条赛绩数据，为鸽友提供可验证的知识参考。'); ?>">
    <meta name="keywords" content="赛鸽资讯,信鸽新闻,赛鸽数据分析,养鸽知识,足环查询,信鸽之家">
    <meta property="og:title" content="<?php echo h($page_title); ?>">
    <meta property="og:image" content="https://www.xgjia.com/public/images/og-cover.png">
    <link rel="canonical" href="<?php echo h($canonical_url); ?>">
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/public/css/b-scheme.css">
    <link rel="shortcut icon" href="/public/images/favicon.ico">

    <!-- ItemList -->
    <?php if (!empty($articles)):
    $_items = [];
    foreach ($articles as $idx => $ar) {
        $_items[] = ['@type' => 'ListItem', 'position' => $idx + 1, 'url' => 'https://www.xgjia.com/article/' . ($ar['id'] ?? '') . '.html'];
    }
    $_ld_il = ['@context' => 'https://schema.org', '@type' => 'ItemList', 'numberOfItems' => count($_items), 'itemListElement' => $_items];
    ?>
    <script type="application/ld+json"><?php echo json_encode($_ld_il, JSON_UNESCAPED_SLASHES); ?></script>
    <?php endif; ?>
</head>
<body>
<?php include __DIR__ . '/_head.php'; ?>

<!-- page-articles wrapper -->
<div class="page-articles">

<div class="hero">
    <div class="hero-inner">
        <h1><i class="fas fa-newspaper"></i>资讯中心</h1>
        <p>赛鸽数据分析 · 养鸽知识百科 · 行业深度解读</p>
    </div>
</div>

<!-- 分类导航 -->
<div class="cat-bar">
    <div class="cat-inner">
        <a href="/article/" class="cat-item <?php if(empty($currentCategory)) echo 'active'; ?>">全部</a>
        <?php if(!empty($categories)): foreach($categories as $cat): ?>
        <a href="/article/?category=<?php echo intval($cat['id']); ?>" class="cat-item <?php if(!empty($currentCategory) && $currentCategory == $cat['id']) echo 'active'; ?>"><?php echo h($cat['name'] ?? ''); ?></a>
        <?php endforeach; endif; ?>
    </div>
</div>

<div class="container" style="padding-top:30px; padding-bottom:60px;">
    <div class="content-layout">
        <div class="main-content">

            <?php if(!empty($articles)): ?>
            <!-- 文章列表 -->
            <div class="article-grid">
                <?php foreach($articles as $a): ?>
                <div class="article-card">
                    <?php if(!empty($a['cover'])): ?>
                    <div class="article-thumb"><img loading="lazy" src="<?php echo h($a['cover']); ?>" alt="<?php echo h($a['title'] ?? ''); ?>"></div>
                    <?php else: ?>
                    <div class="article-thumb">📰</div>
                    <?php endif; ?>
                    <div class="article-body">
                        <?php if(!empty($a['category_name'])): ?><div class="article-cat"><?php echo h($a['category_name']); ?></div><?php endif; ?>
                        <h2 class="article-title"><a href="/article/<?php echo intval($a['id']); ?>.html"><?php echo h($a['title'] ?? ''); ?></a></h2>
                        <p class="article-desc"><?php echo h(mb_substr(strip_tags($a['content'] ?? $a['summary'] ?? ''), 0, 80)); ?></p>
                        <div class="article-meta">
                            <span><i class="fas fa-clock"></i> <?php echo timeAgo($a['created_at'] ?? time()); ?></span>
                            <span><i class="fas fa-eye"></i> <?php echo number_format($a['views'] ?? 0); ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- 分页 -->
                <?php echo renderPagination($page ?? 1, $totalPages ?? 1); ?>

            <?php else: ?>
            <!-- 无任何文章 -->
            <div class="empty-state">
                <div class="empty-icon">📰</div>
                <p class="empty-text">暂无相关资讯</p>
                <a href="/article/" style="display:inline-block;margin-top:15px;padding:8px 24px;background:var(--primary);color:white;border-radius:8px;font-size:14px;">查看全部资讯</a>
            </div>
            <?php endif; ?>

        </div><!-- /.main-content -->

        <!-- 侧边栏 -->
        <div class="sidebar">
            <div class="sidebar-card">
                <h3 class="sidebar-title"><i class="fas fa-fire"></i>热门文章</h3>
                <div class="hot-list">
                    <?php if(!empty($hotArticles)): ?>
                    <?php foreach($hotArticles as $ha): ?>
                    <a href="/article/<?php echo intval($ha['id']); ?>.html" class="hot-item">
                        <span class="hot-title"><?php echo h(mb_substr($ha['title'] ?? '', 0, 24)); ?><?php echo mb_strlen($ha['title'] ?? '') > 24 ? '...' : ''; ?></span>
                        <span class="hot-views"><i class="fas fa-eye"></i> <?php echo number_format($ha['views'] ?? 0); ?></span>
                    </a>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <div style="font-size:13px;color:var(--text-light);text-align:center;padding:10px 0;">暂无热门文章</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="sidebar-card">
                <h3 class="sidebar-title"><i class="fas fa-folder-open"></i>资讯分类</h3>
                <div class="cat-list">
                    <?php if(!empty($categories)): foreach($categories as $cat): ?>
                    <a href="/article/?category=<?php echo intval($cat['id']); ?>" class="cat-link">
                        <span><?php echo h($cat['name'] ?? ''); ?></span>
                        <span class="cat-count"><?php echo number_format($cat['article_count'] ?? 0); ?></span>
                    </a>
                    <?php endforeach; endif; ?>
                </div>
            </div>

            <div class="sidebar-card" style="background:linear-gradient(135deg,var(--primary) 0%,var(--primary-light) 100%);border:none;">
                <h3 class="sidebar-title" style="border-bottom-color:rgba(255,255,255,0.2);color:white;"><i class="fas fa-bullhorn"></i>免责声明</h3>
                <p style="font-size:12px;color:rgba(255,255,255,0.85);line-height:1.8;">本平台所展示的公棚及铭鸽信息由用户自行发布，平台不对信息真实性承担连带责任，交易前请核实。</p>
            </div>
        </div>
    </div><!-- /.content-layout -->
</div><!-- /.container -->


</div><!-- /page-articles -->

<?php include __DIR__ . '/_footer.php'; ?>
</body>
</html>
