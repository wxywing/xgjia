<?php
/**
 * 信鸽之家 - 搜索结果页
 * 
 * 功能：搜索文章、铭鸽、公棚
 */

require_once dirname(__DIR__) . '/app/config/config.php';

$pdo = get_db_connection();

// 获取搜索参数
$keyword = trim($_GET['q'] ?? '');
$type = $_GET['type'] ?? 'all'; // all, article, pigeon, loft
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$page_size = 20;

$results = [];
$total = 0;
$pagination = null;

// JSON-LD - ItemList (搜索结果)
$ld_items = [];
if (!empty($results['articles'])) {
    foreach (array_slice($results['articles'], 0, 5) as $i => $a) {
        $ld_items[] = ['@type' => 'ListItem', 'position' => count($ld_items) + 1, 'url' => 'https://www.xgjia.com/article/' . ($a['id'] ?? '') . '.html'];
    }
}
if (!empty($results['pigeons'])) {
    foreach (array_slice($results['pigeons'], 0, 5) as $i => $p) {
        $ld_items[] = ['@type' => 'ListItem', 'position' => count($ld_items) + 1, 'url' => 'https://www.xgjia.com/pigeon/' . ($p['id'] ?? '') . '.html'];
    }
}
if (!empty($results['lofts'])) {
    foreach (array_slice($results['lofts'], 0, 5) as $i => $l) {
        $ld_items[] = ['@type' => 'ListItem', 'position' => count($ld_items) + 1, 'url' => 'https://www.xgjia.com/loft/' . ($l['id'] ?? '') . '.html'];
    }
}
$ld_itemlist = [
    '@context' => 'https://schema.org',
    '@type' => 'ItemList',
    'name' => '搜索结果',
    'numberOfItems' => $total ?? 0,
    'itemListElement' => array_slice($ld_items, 0, 10),
];

if (!empty($keyword)) {
    $search_term = "%$keyword%";

    // 统计各类型数量
    $type_counts = ['article' => 0, 'pigeon' => 0, 'loft' => 0];

    // 文章数
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM articles WHERE status = 1 AND (title LIKE ? OR content LIKE ? OR summary LIKE ?)");
    $stmt->execute([$search_term, $search_term, $search_term]);
    $type_counts['article'] = $stmt->fetch()['total'];

    // 铭鸽数
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM pigeons WHERE (ring_number LIKE ? OR bloodline LIKE ? OR name LIKE ?)");
    $stmt->execute([$search_term, $search_term, $search_term]);
    $type_counts['pigeon'] = $stmt->fetch()['total'];

    // 公棚数
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM lofts WHERE (name LIKE ? OR address LIKE ?)");
    $stmt->execute([$search_term, $search_term]);
    $type_counts['loft'] = $stmt->fetch()['total'];

    if ($type === 'all') {
        // 各取前5条
        $stmt = $pdo->prepare("
            SELECT a.*, 'article' as result_type 
            FROM articles a 
            WHERE a.status = 1 AND (a.title LIKE ? OR a.content LIKE ? OR a.summary LIKE ?)
            ORDER BY a.views DESC LIMIT 5
        ");
        $stmt->execute([$search_term, $search_term, $search_term]);
        $articles = $stmt->fetchAll();

        $stmt = $pdo->prepare("
            SELECT p.*, 'pigeon' as result_type, s.name as shop_name
            FROM pigeons p
            LEFT JOIN shops s ON p.shop_id = s.id
            WHERE (p.ring_number LIKE ? OR p.bloodline LIKE ? OR p.name LIKE ?)
            ORDER BY p.id DESC LIMIT 5
        ");
        $stmt->execute([$search_term, $search_term, $search_term]);
        $pigeons = $stmt->fetchAll();

        $stmt = $pdo->prepare("
            SELECT l.*, 'loft' as result_type
            FROM lofts l
            WHERE (l.name LIKE ? OR l.address LIKE ?)
            ORDER BY l.id DESC LIMIT 5
        ");
        $stmt->execute([$search_term, $search_term]);
        $lofts = $stmt->fetchAll();

        $results = ['articles' => $articles, 'pigeons' => $pigeons, 'lofts' => $lofts];
        $total = array_sum($type_counts);

    } elseif ($type === 'article') {
        $total = $type_counts['article'];
        if (function_exists('paginate')) {
            $pagination = paginate($total, $page, $page_size);
        } else {
            $total_pages = max(1, ceil($total / $page_size));
            $pagination = ['total_pages' => $total_pages, 'page_size' => $page_size, 'offset' => ($page - 1) * $page_size, 'has_prev' => $page > 1, 'has_next' => $page < $total_pages];
        }
        $stmt = $pdo->prepare("
            SELECT a.*, 'article' as result_type 
            FROM articles a 
            WHERE a.status = 1 AND (a.title LIKE ? OR a.content LIKE ? OR a.summary LIKE ?)
            ORDER BY a.is_top DESC, a.views DESC 
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$search_term, $search_term, $search_term, $pagination['page_size'], $pagination['offset']]);
        $results = $stmt->fetchAll();

    } elseif ($type === 'pigeon') {
        $total = $type_counts['pigeon'];
        $total_pages = max(1, ceil($total / $page_size));
        $pagination = ['total_pages' => $total_pages, 'page_size' => $page_size, 'offset' => ($page - 1) * $page_size, 'has_prev' => $page > 1, 'has_next' => $page < $total_pages];
        $stmt = $pdo->prepare("
            SELECT p.*, 'pigeon' as result_type, s.name as shop_name
            FROM pigeons p
            LEFT JOIN shops s ON p.shop_id = s.id
            WHERE (p.ring_number LIKE ? OR p.bloodline LIKE ? OR p.name LIKE ?)
            ORDER BY p.id DESC 
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$search_term, $search_term, $search_term, $pagination['page_size'], $pagination['offset']]);
        $results = $stmt->fetchAll();

    } elseif ($type === 'loft') {
        $total = $type_counts['loft'];
        $total_pages = max(1, ceil($total / $page_size));
        $pagination = ['total_pages' => $total_pages, 'page_size' => $page_size, 'offset' => ($page - 1) * $page_size, 'has_prev' => $page > 1, 'has_next' => $page < $total_pages];
        $stmt = $pdo->prepare("
            SELECT l.*, 'loft' as result_type
            FROM lofts l
            WHERE (l.name LIKE ? OR l.address LIKE ?)
            ORDER BY l.id DESC 
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$search_term, $search_term, $pagination['page_size'], $pagination['offset']]);
        $results = $stmt->fetchAll();
    }
}

$page_title = !empty($keyword) ? '搜索「' . $keyword . '」的结果' : '站内搜索';

// SEO 元信息 - 搜索结果页通常不需要索引
$meta_description = !empty($keyword) ? "在" . SITE_NAME . "搜索'{$keyword}'的结果，查找铭鸽、公棚、赛事、资讯等信鸽信息" : '在' . SITE_NAME . '搜索铭鸽、公棚、赛事、资讯等信鸽信息';
$canonical_url = 'https://www.xgjia.com/search?' . http_build_query(array_filter(['q' => $keyword, 'type' => $type !== 'all' ? $type : null, 'page' => $page > 1 ? $page : null]));

function highlightKeyword($text, $keyword) {
    if (empty($keyword) || empty($text)) return $text;
    return str_ireplace($keyword, '<mark>' . $keyword . '</mark>', $text);
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo h($page_title); ?></title>
    
    <!-- SEO Meta - 搜索结果页不索引 -->
    <meta name="description" content="<?php echo h($meta_description); ?>">
    <meta name="robots" content="noindex, follow">
    <link rel="canonical" href="<?php echo h($canonical_url); ?>">
    
    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:image" content="https://www.xgjia.com/public/images/og-cover.png">
    <meta property="og:title" content="<?php echo h($page_title); ?>">
    <meta property="og:description" content="<?php echo h($meta_description); ?>">
    <meta property="og:url" content="<?php echo h($canonical_url); ?>">
    <meta property="og:site_name" content="<?php echo SITE_NAME; ?>">
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script type="application/ld+json"><?php echo json_encode($ld_itemlist, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>
        <meta name="keywords" content="搜索,信鸽搜索,铭鸽查询,公棚查询,信鸽之家">
<link rel="stylesheet" href="/public/css/b-scheme.css">
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
                --success: #27ae60;
                --danger: #e74c3c;
                --radius: 12px;
            }

        .search-header {
            background: linear-gradient(135deg, var(--primary) 0%, #1e40af 100%);
            color: white;
            padding: 40px 0;
            margin-bottom: 40px;
        }
        .search-box { max-width: 800px; margin: 0 auto; }
        .search-form { display: flex; gap: 10px; margin-bottom: 20px; }
        .search-input { flex: 1; padding: 15px 20px; border: none; border-radius: 50px; font-size: 18px; }
        .search-input:focus { outline: none; }
        .search-btn { padding: 15px 40px; background: var(--secondary-color); color: white; border: none; border-radius: 50px; font-size: 18px; font-weight: 600; cursor: pointer; transition: background .3s; }
        .search-btn:hover { background: var(--secondary-dark); }
        .search-tabs { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
        .search-tab { padding: 8px 18px; background: rgba(255,255,255,.2); border-radius: 20px; cursor: pointer; transition: all .3s; color: #fff; text-decoration: none; font-size: 14px; }
        .search-tab:hover, .search-tab.active { background: white; color: var(--primary); }
        .search-tab .badge { background: rgba(255,255,255,.3); padding: 1px 7px; border-radius: 10px; font-size: 12px; margin-left: 4px; }
        .search-tab.active .badge { background: var(--primary); color: #fff; }

        .search-results { margin-bottom: 60px; }
        .results-header { font-size: 22px; font-weight: bold; margin-bottom: 24px; padding-bottom: 12px; border-bottom: 2px solid var(--gray-200); display: flex; align-items: center; gap: 8px; }
        .results-header i { color: var(--primary); }
        .results-header span { color: var(--primary); }

        .result-item { display: flex; gap: 20px; padding: 20px; background: white; border-radius: var(--border-radius); box-shadow: var(--box-shadow); margin-bottom: 16px; transition: all .3s; }
        .result-item:hover { box-shadow: var(--box-shadow-hover); transform: translateY(-2px); }
        .result-thumb { width: 180px; height: 130px; object-fit: cover; border-radius: var(--border-radius); flex-shrink: 0; background: var(--gray-100); }
        .result-content { flex: 1; min-width: 0; }
        .result-title { font-size: 18px; font-weight: 600; margin-bottom: 8px; }
        .result-title a { color: var(--gray-900); }
        .result-title a:hover { color: var(--primary); }
        .result-summary { color: var(--gray-500); margin-bottom: 8px; line-height: 1.6; font-size: 14px; }
        .result-meta { display: flex; gap: 16px; font-size: 13px; color: var(--gray-500); flex-wrap: wrap; }
        .result-type { display: inline-block; padding: 2px 10px; border-radius: 3px; font-size: 12px; margin-bottom: 8px; font-weight: 500; }
        .result-type.article { background: #dbeafe; color: #1e40af; }
        .result-type.pigeon { background: #fef3c7; color: #92400e; }
        .result-type.loft { background: #d1fae5; color: #065f46; }

        .no-results { text-align: center; padding: 80px 20px; color: var(--gray-500); }
        .no-results i { font-size: 64px; margin-bottom: 20px; display: block; opacity: .4; }

        .more-link { display: inline-block; margin-top: 12px; color: var(--primary); font-size: 14px; }

        @media (max-width: 768px) {
            .search-form { flex-direction: column; }
            .search-input, .search-btn { width: 100%; }
            .result-item { flex-direction: column; }
            .result-thumb { width: 100%; height: 180px; }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/_head.php'; ?>

    <!-- 搜索头部 -->
    <div class="search-header">
        <div class="container">
            <div class="search-box">
                <form class="search-form" action="/search/" method="GET">
                    <input type="text" name="q" class="search-input" 
                           placeholder="搜索文章、铭鸽、公棚..." 
                           value="<?php echo h($keyword); ?>" required>
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i> 搜索
                    </button>
                </form>
                
                <?php if (!empty($keyword)): ?>
                <div class="search-tabs">
                    <a href="?q=<?php echo urlencode($keyword); ?>&type=all" 
                       class="search-tab <?php echo $type === 'all' ? 'active' : ''; ?>">
                        全部 <span class="badge"><?php echo array_sum($type_counts); ?></span>
                    </a>
                    <a href="?q=<?php echo urlencode($keyword); ?>&type=article" 
                       class="search-tab <?php echo $type === 'article' ? 'active' : ''; ?>">
                        <i class="fas fa-newspaper"></i> 资讯 <span class="badge"><?php echo $type_counts['article']; ?></span>
                    </a>
                    <a href="?q=<?php echo urlencode($keyword); ?>&type=pigeon" 
                       class="search-tab <?php echo $type === 'pigeon' ? 'active' : ''; ?>">
                        <i class="fas fa-dove"></i> 铭鸽 <span class="badge"><?php echo $type_counts['pigeon']; ?></span>
                    </a>
                    <a href="?q=<?php echo urlencode($keyword); ?>&type=loft" 
                       class="search-tab <?php echo $type === 'loft' ? 'active' : ''; ?>">
                        <i class="fas fa-building"></i> 公棚 <span class="badge"><?php echo $type_counts['loft']; ?></span>
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- 搜索结果 -->
    <div class="container">
        <div class="search-results">
            <?php if (!empty($keyword)): ?>

                <?php if ($type === 'all'): ?>
                    <!-- ========== 全部搜索结果 ========== -->
                    
                    <!-- 文章 -->
                    <?php if (!empty($results['articles'])): ?>
                    <div class="results-header">
                        <i class="fas fa-newspaper"></i> 资讯 (<?php echo $type_counts['article']; ?>)
                    </div>
                    <?php foreach ($results['articles'] as $article): ?>
                    <div class="result-item">
                        <?php if (!empty($article['cover'])): ?>
                        <img loading="lazy" src="<?php echo h($article['cover']); ?>" alt="文章封面" class="result-thumb">
                        <?php endif; ?>
                        <div class="result-content">
                            <div class="result-type article">资讯</div>
                            <h3 class="result-title">
                                <a href="/article/<?php echo $article['id']; ?>.html">
                                    <?php echo highlightKeyword(h($article['title']), $keyword); ?>
                                </a>
                            </h3>
                            <?php if (!empty($article['summary'])): ?>
                            <p class="result-summary"><?php echo highlightKeyword(mb_substr(strip_tags($article['summary']), 0, 150), $keyword); ?></p>
                            <?php endif; ?>
                            <div class="result-meta">
                                <span><i class="fas fa-clock"></i> <?php echo date('Y-m-d', strtotime($article['created_at'])); ?></span>
                                <span><i class="fas fa-eye"></i> <?php echo $article['views']; ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php if ($type_counts['article'] > 5): ?>
                    <a href="?q=<?php echo urlencode($keyword); ?>&type=article" class="more-link">查看全部 <?php echo $type_counts['article']; ?> 条资讯 →</a>
                    <?php endif; ?>
                    <?php endif; ?>

                    <!-- 铭鸽 -->
                    <?php if (!empty($results['pigeons'])): ?>
                    <div class="results-header" style="margin-top:30px;">
                        <i class="fas fa-dove"></i> 铭鸽 (<?php echo $type_counts['pigeon']; ?>)
                    </div>
                    <?php foreach ($results['pigeons'] as $pigeon): ?>
                    <div class="result-item">
                        <div class="result-content">
                            <div class="result-type pigeon">铭鸽</div>
                            <h3 class="result-title">
                                <a href="/pigeon/<?php echo $pigeon['id']; ?>.html">
                                    <?php echo highlightKeyword(h($pigeon['name'] ?? $pigeon['ring_number'] ?? '未命名'), $keyword); ?>
                                </a>
                            </h3>
                            <p class="result-summary">
                                <?php 
                                $parts = [];
                                if (!empty($pigeon['ring_number'])) $parts[] = '足环号: ' . h($pigeon['ring_number']);
                                if (!empty($pigeon['bloodline'])) $parts[] = '血统: ' . h($pigeon['bloodline']);
                                if (!empty($pigeon['shop_name'])) $parts[] = '展厅: ' . h($pigeon['shop_name']);
                                echo highlightKeyword(implode(' | ', $parts), $keyword);
                                ?>
                            </p>
                            <div class="result-meta">
                                <?php if (!empty($pigeon['sex'])): ?><span><i class="fas fa-venus-mars"></i> <?php echo $pigeon['sex'] == 'male' ? '雄' : '雌'; ?></span><?php endif; ?>
                                <?php if (!empty($pigeon['eye_type'])): ?><span><i class="fas fa-eye"></i> <?php echo h($pigeon['eye_type']); ?></span><?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php if ($type_counts['pigeon'] > 5): ?>
                    <a href="?q=<?php echo urlencode($keyword); ?>&type=pigeon" class="more-link">查看全部 <?php echo $type_counts['pigeon']; ?> 羽铭鸽 →</a>
                    <?php endif; ?>
                    <?php endif; ?>

                    <!-- 公棚 -->
                    <?php if (!empty($results['lofts'])): ?>
                    <div class="results-header" style="margin-top:30px;">
                        <i class="fas fa-building"></i> 公棚 (<?php echo $type_counts['loft']; ?>)
                    </div>
                    <?php foreach ($results['lofts'] as $loft): ?>
                    <div class="result-item">
                        <div class="result-content">
                            <div class="result-type loft">公棚</div>
                            <h3 class="result-title">
                                <a href="/loft/<?php echo $loft['id']; ?>.html">
                                    <?php echo highlightKeyword(h($loft['name']), $keyword); ?>
                                </a>
                            </h3>
                            <p class="result-summary">
                                <?php 
                                $parts = [];
                                if (!empty($loft['address'])) $parts[] = h($loft['address']);
                                if (!empty($loft['race_type'])) $parts[] = h($loft['race_type']);
                                echo highlightKeyword(implode(' | ', $parts), $keyword);
                                ?>
                            </p>
                            <div class="result-meta">
                                <?php if (!empty($loft['contact_phone'])): ?><span><i class="fas fa-phone"></i> <?php echo h($loft['contact_phone']); ?></span><?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php if ($type_counts['loft'] > 5): ?>
                    <a href="?q=<?php echo urlencode($keyword); ?>&type=loft" class="more-link">查看全部 <?php echo $type_counts['loft']; ?> 家公棚 →</a>
                    <?php endif; ?>
                    <?php endif; ?>

                    <?php if (empty($results['articles']) && empty($results['pigeons']) && empty($results['lofts'])): ?>
                    <div class="no-results">
                        <i class="fas fa-search"></i>
                        <h3>未找到相关结果</h3>
                        <p>换个关键词试试吧</p>
                    </div>
                    <?php endif; ?>

                <?php else: ?>
                    <!-- ========== 分类搜索结果 ========== -->
                    <div class="results-header">
                        找到 <span><?php echo $total; ?></span> 条结果
                    </div>
                    
                    <?php if (!empty($results)): ?>
                    <?php foreach ($results as $item): ?>
                    <div class="result-item">
                        <?php if ($item['result_type'] === 'article' && !empty($item['cover'])): ?>
                        <img loading="lazy" src="<?php echo h($item['cover']); ?>" alt="信息图片" class="result-thumb">
                        <?php endif; ?>

                        <div class="result-content">
                            <?php if ($item['result_type'] === 'article'): ?>
                                <div class="result-type article">资讯</div>
                                <h3 class="result-title">
                                    <a href="/article/<?php echo $item['id']; ?>.html">
                                        <?php echo highlightKeyword(h($item['title']), $keyword); ?>
                                    </a>
                                </h3>
                                <p class="result-summary"><?php echo highlightKeyword(mb_substr(strip_tags($item['summary'] ?? ''), 0, 150), $keyword); ?></p>
                                <div class="result-meta">
                                    <span><i class="fas fa-clock"></i> <?php echo date('Y-m-d', strtotime($item['created_at'])); ?></span>
                                    <span><i class="fas fa-eye"></i> <?php echo $item['views']; ?></span>
                                </div>

                            <?php elseif ($item['result_type'] === 'pigeon'): ?>
                                <div class="result-type pigeon">铭鸽</div>
                                <h3 class="result-title">
                                    <a href="/pigeon/<?php echo $item['id']; ?>.html">
                                        <?php echo highlightKeyword(h($item['name'] ?? $item['ring_number'] ?? '未命名'), $keyword); ?>
                                    </a>
                                </h3>
                                <p class="result-summary">
                                    <?php 
                                    $parts = [];
                                    if (!empty($item['ring_number'])) $parts[] = '足环号: ' . h($item['ring_number']);
                                    if (!empty($item['bloodline'])) $parts[] = '血统: ' . h($item['bloodline']);
                                    if (!empty($item['shop_name'])) $parts[] = '展厅: ' . h($item['shop_name']);
                                    echo highlightKeyword(implode(' | ', $parts), $keyword);
                                    ?>
                                </p>
                                <div class="result-meta">
                                    <?php if (!empty($item['sex'])): ?><span><i class="fas fa-venus-mars"></i> <?php echo $item['sex'] == 'male' ? '雄' : '雌'; ?></span><?php endif; ?>
                                </div>

                            <?php elseif ($item['result_type'] === 'loft'): ?>
                                <div class="result-type loft">公棚</div>
                                <h3 class="result-title">
                                    <a href="/loft/<?php echo $item['id']; ?>.html">
                                        <?php echo highlightKeyword(h($item['name']), $keyword); ?>
                                    </a>
                                </h3>
                                <p class="result-summary"><?php echo highlightKeyword(h($item['address'] ?? ''), $keyword); ?></p>
                                <div class="result-meta">
                                    <?php if (!empty($item['contact_phone'])): ?><span><i class="fas fa-phone"></i> <?php echo h($item['contact_phone']); ?></span><?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <!-- 分页 -->
                    <?php echo renderPagination($page, $pagination['total_pages']); ?>
                    
                    <?php else: ?>
                    <div class="no-results">
                        <i class="fas fa-search"></i>
                        <h3>未找到相关结果</h3>
                        <p>换个关键词试试吧</p>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
                
            <?php else: ?>
            <div class="no-results">
                <i class="fas fa-search"></i>
                <h3>输入关键词开始搜索</h3>
                <p>搜索资讯、铭鸽、公棚等信息</p>
            </div>
            <?php endif; ?>
        </div>
    </div>


    <?php include __DIR__ . '/_footer.php'; ?>
