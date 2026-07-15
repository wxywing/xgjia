<?php
/**
 * 信鸽之家 - 铭鸽展厅（V3 简洁版 2026-06-10）
 * 移除统计块 · 搜索框精简入筛选栏
 */
require_once dirname(__DIR__) . '/app/config/config.php';

extract($data);

$page_title = '铭鸽展厅' . (($page ?? 1) > 1 ? ' - 第' . intval($page) . '页' : '') . ' | ' . SITE_NAME;
$__kw = h($_GET['keyword'] ?? '');
$canonical_url = 'https://www.xgjia.com/pigeon/' . ($_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '');
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo h($page_title); ?></title>
    <meta name="description" content="<?php echo h('信鸽之家铭鸽展厅，已收录' . number_format($total ?? 0) . '羽优质赛鸽，涵盖詹森、胡本、桑杰士等知名血统' . (!empty($currentStrain) ? '【' . $currentStrain . '】' : '') . '。按足环号、血统、性别快速筛选。'); ?>">
    <meta name="keywords" content="铭鸽,赛鸽,信鸽,血统,足环号,<?php echo h(SITE_NAME); ?>">
    <meta property="og:title" content="<?php echo h($page_title); ?>">
    <meta property="og:image" content="https://www.xgjia.com/public/images/og-cover.png">
    <link rel="canonical" href="<?php echo h($canonical_url); ?>">
    <meta property="og:type" content="website">
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/public/css/b-scheme.css">
    <link rel="shortcut icon" href="/public/images/favicon.ico">

    <?php if (!empty($pigeons)):
    $_items = [];
    foreach ($pigeons as $idx => $pg) {
        $_items[] = ['@type' => 'ListItem', 'position' => $idx + 1, 'url' => 'https://www.xgjia.com/pigeon/' . ($pg['id'] ?? '') . '.html'];
    }
    $_ld_il = ['@context' => 'https://schema.org', '@type' => 'ItemList', 'numberOfItems' => count($_items), 'itemListElement' => $_items];
    ?>
    <script type="application/ld+json"><?php echo json_encode($_ld_il, JSON_UNESCAPED_SLASHES); ?></script>
    <?php endif; ?>

<style>
/* V3 精简版 */
*,*::before,*::after{box-sizing:border-box}
.page-pigeons{background:#f4f6f9;min-height:100vh}

/* Hero 精简 — 覆盖 b-scheme 白底 */
.page-pigeons .hero{background:linear-gradient(135deg,#1e3a8a 0%,#2563eb 50%,#1d4ed8 100%);padding:36px 20px 32px;text-align:center;color:#fff;border-bottom:none!important}
.page-pigeons .hero::after{display:none!important}
.page-pigeons .hero::before{content:'';position:absolute;inset:0;background:radial-gradient(circle at 30% 50%,rgba(255,255,255,.08) 0%,transparent 50%);pointer-events:none}
.page-pigeons .hero h1{font-size:clamp(24px,5vw,32px);font-weight:800;position:relative;z-index:1;color:#fff!important;margin-bottom:6px}
.page-pigeons .hero h1 i{margin-right:8px;color:#fbbf24!important}
.page-pigeons .hero .hero-sub{font-size:14px;opacity:.8;margin-top:4px;position:relative;z-index:1;color:rgba(255,255,255,.85)!important}
.container{max-width:1200px;margin:0 auto;padding:0 16px}

/* Filter Bar */
.filter-section{position:static!important;background:#fff;border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,.06);margin:20px 0;padding:14px 18px;border:1px solid #e5e7eb}
.filter-inner{display:flex;align-items:center;flex-wrap:wrap;gap:8px 12px}
.filter-group{display:flex;align-items:center;gap:4px;flex-wrap:wrap}
.filter-label{font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;white-space:nowrap;margin-right:2px}
.filter-chip{padding:4px 12px;border-radius:16px;font-size:12px;color:#6b7280;background:#f3f4f6;transition:all .15s;white-space:nowrap;font-weight:500;text-decoration:none}
.filter-chip:hover,.filter-chip.active{background:#1e3a8a;color:#fff;text-decoration:none}
.filter-divider{width:1px;height:20px;background:#e5e7eb;flex-shrink:0}

/* Search row — below bloodline */
.filter-search-row{display:flex;align-items:center;gap:8px;flex-basis:100%;margin-top:2px;padding-top:10px;border-top:1px solid #f3f4f6}
.filter-search-row input{padding:7px 12px;border:1px solid #e5e7eb;border-radius:8px;font-size:13px;width:200px;max-width:100%;outline:none;font-family:inherit;transition:border-color .15s}
.filter-search-row input:focus{border-color:#1e3a8a}
.filter-search-row button{padding:7px 16px;background:#1e3a8a;color:#fff;border:none;border-radius:8px;font-size:13px;cursor:pointer;font-weight:600;white-space:nowrap;font-family:inherit}
.filter-search-row button:hover{background:#1d4ed8}
.filter-hint{font-size:11px;color:#9ca3af}

/* Sort */
.sort-group{display:flex;align-items:center;gap:2px;margin-left:auto}
.sort-btn{padding:5px 12px;border-radius:6px;font-size:12px;color:#6b7280;transition:all .15s;font-weight:500;text-decoration:none}
.sort-btn:hover,.sort-btn.active{background:#1e3a8a;color:#fff;text-decoration:none}

/* Card Grid */
.pigeon-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:16px;margin:20px 0}
.pigeon-card{background:#fff;border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,.06);border:1px solid #e5e7eb;overflow:hidden;transition:transform .2s,box-shadow .2s;display:flex;flex-direction:column}
.pigeon-card:hover{transform:translateY(-2px);box-shadow:0 6px 18px rgba(0,0,0,.08)}
.card-img-wrap{position:relative;width:100%;aspect-ratio:4/3;overflow:hidden;background:#f3f4f6;display:flex;align-items:center;justify-content:center}
.card-img-wrap img{width:100%;height:100%;object-fit:cover;transition:transform .3s}
.pigeon-card:hover .card-img-wrap img{transform:scale(1.04)}
.img-placeholder{font-size:48px;opacity:.3}
.card-badges{position:absolute;top:8px;left:8px;display:flex;gap:4px;flex-wrap:wrap}
.card-badge{padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600;color:#fff;line-height:1.4}
.badge-male{background:#3b82f6}
.badge-female{background:#ec4899}
.badge-price{background:#f97316}
.card-body{padding:12px 14px 14px;flex:1;display:flex;flex-direction:column;gap:6px}
.card-ring{font-size:11px;color:#9ca3af}
.card-name{font-size:14px;font-weight:700;line-height:1.3;margin:0}
.card-name a{color:#1f2937;text-decoration:none}
.card-name a:hover{color:#1e3a8a}
.card-tags{display:flex;gap:4px;flex-wrap:wrap}
.card-tag{font-size:10px;padding:2px 7px;border-radius:10px;background:#f3f4f6;color:#6b7280;white-space:nowrap}
.card-excerpt{font-size:12px;color:#9ca3af;line-height:1.5;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.card-excerpt.empty{color:#d1d5db;font-style:italic}

/* Hot Section */
.hot-section{margin:30px 0 40px}
.hot-heading{font-size:18px;font-weight:700;margin-bottom:14px;color:#1f2937}
.hot-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:12px}
.hot-card{display:flex;align-items:center;gap:10px;padding:10px 12px;background:#fff;border-radius:10px;border:1px solid #e5e7eb;text-decoration:none;color:inherit;transition:box-shadow .15s}
.hot-card:hover{box-shadow:0 2px 8px rgba(0,0,0,.06)}
.hot-thumb{width:52px;height:52px;border-radius:8px;overflow:hidden;background:#f3f4f6;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:24px}
.hot-thumb img{width:100%;height:100%;object-fit:cover}
.hot-info{flex:1;min-width:0}
.hot-name{font-size:13px;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#1f2937}
.hot-meta{font-size:11px;color:#9ca3af;margin-top:1px}
.hot-views{font-size:11px;color:#9ca3af;flex-shrink:0}

/* Empty State */
.empty-state{text-align:center;padding:60px 20px}
.empty-icon{font-size:48px;opacity:.3}
.empty-text{font-size:15px;color:#9ca3af;margin-top:8px}
.empty-link{display:inline-block;margin-top:8px;color:#1e3a8a;font-weight:600;text-decoration:none;font-size:14px}

@media(max-width:768px){
  .hero{padding:28px 16px}
  .filter-section{padding:12px 14px;border-radius:10px}
  .filter-inner{gap:6px 8px}
  .filter-label{font-size:10px}
  .filter-chip{padding:3px 10px;font-size:11px}
  .filter-search-row input{width:140px}
  .pigeon-grid{grid-template-columns:repeat(2,1fr);gap:10px}
  .card-body{padding:10px}
  .card-name{font-size:13px}
  .hot-grid{grid-template-columns:1fr}
  .sort-group{margin-left:0;margin-top:4px}
}
@media(max-width:480px){
  .pigeon-grid{grid-template-columns:1fr}
  .filter-search-row{flex-wrap:wrap}
  .filter-search-row input{width:100%}
  .page-pigeons{padding-bottom:80px}
}
</style>
</head>
<body>
<?php include __DIR__ . '/_head.php'; ?>

<div class="page-pigeons">

<!-- Hero 精简 -->
<section class="hero">
    <div class="container">
        <h1><i class="fas fa-dove"></i>铭鸽展厅</h1>
        <p class="hero-sub">名血传承 · 汇聚国内优质赛鸽 · 为鸽友提供可靠的展示与交流平台</p>
    </div>
</section>

<div class="container">

<!-- Filter Bar -->
<div class="filter-section">
    <div class="filter-inner">
        <!-- 性别 -->
        <div class="filter-group">
            <span class="filter-label">性别</span>
            <?php $gParams = $_GET; unset($gParams['gender']); $gBase = '/pigeon/' . (empty($gParams) ? '' : '?' . http_build_query($gParams)); ?>
            <a href="<?php echo $gBase; ?>" class="filter-chip <?php if(empty($_GET['gender'])) echo 'active'; ?>">全部</a>
            <?php $gM = $_GET; $gM['gender'] = 'male'; ?>
            <a href="/pigeon/?<?php echo http_build_query($gM); ?>" class="filter-chip <?php if(($_GET['gender'] ?? '') === 'male') echo 'active'; ?>">♂ 雄</a>
            <?php $gF = $_GET; $gF['gender'] = 'female'; ?>
            <a href="/pigeon/?<?php echo http_build_query($gF); ?>" class="filter-chip <?php if(($_GET['gender'] ?? '') === 'female') echo 'active'; ?>">♀ 雌</a>
        </div>

        <?php if(!empty($categories)): ?>
        <span class="filter-divider"></span>
        <div class="filter-group">
            <span class="filter-label">种类</span>
            <?php $cParams = $_GET; unset($cParams['category']); $cBase = '/pigeon/' . (empty($cParams) ? '' : '?' . http_build_query($cParams)); ?>
            <a href="<?php echo $cBase; ?>" class="filter-chip <?php if(empty($_GET['category'])) echo 'active'; ?>">全部</a>
            <?php foreach($categories as $cat): ?>
            <?php $catParams = $_GET; $catParams['category'] = $cat['id']; ?>
            <a href="/pigeon/?<?php echo http_build_query($catParams); ?>" class="filter-chip <?php if(($_GET['category'] ?? '') == $cat['id']) echo 'active'; ?>"><?php echo h($cat['name'] ?? ''); ?></a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if(!empty($hotBloodlines)): ?>
        <span class="filter-divider"></span>
        <div class="filter-group">
            <span class="filter-label">血统</span>
            <?php $blParams = $_GET; unset($blParams['bloodline']); $blBase = '/pigeon/' . (empty($blParams) ? '' : '?' . http_build_query($blParams)); ?>
            <a href="<?php echo $blBase; ?>" class="filter-chip <?php if(empty($_GET['bloodline'])) echo 'active'; ?>">全部</a>
            <?php foreach($hotBloodlines as $bl): ?>
            <?php $blP = $_GET; $blP['bloodline'] = $bl['bloodline']; ?>
            <a href="/pigeon/?<?php echo http_build_query($blP); ?>" class="filter-chip <?php if(($_GET['bloodline'] ?? '') === $bl['bloodline']) echo 'active'; ?>"><?php echo h($bl['bloodline']); ?></a>
            <?php endforeach; ?>
            <a href="/pedigree/" class="filter-chip" style="background:#eef2ff;color:#1e3a8a;font-weight:600;">更多品系 &rarr;</a>
        </div>
        <?php endif; ?>

        <!-- 搜索行 —— 血统下方 -->
        <div class="filter-search-row">
            <form action="/pigeon/" method="GET" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                <input type="text" name="keyword" placeholder="名称 / 足环号 / 血统..." value="<?php echo $__kw; ?>">
                <button type="submit"><i class="fas fa-search"></i> 搜索</button>
                <?php if ($__kw): ?>
                <a href="/pigeon/" class="filter-hint" style="text-decoration:none;">✕ 清除</a>
                <?php endif; ?>
            </form>
            <span class="filter-hint">共 <?php echo number_format($total ?? 0); ?> 羽</span>

            <div class="sort-group">
                <?php $sNew = $_GET; unset($sNew['sort']); ?>
                <a href="/pigeon/?<?php echo http_build_query($sNew); ?>" class="sort-btn <?php if(empty($_GET['sort'])) echo 'active'; ?>">最新</a>
                <?php $sHot = $_GET; $sHot['sort'] = 'views'; ?>
                <a href="/pigeon/?<?php echo http_build_query($sHot); ?>" class="sort-btn <?php if(($_GET['sort'] ?? '') === 'views') echo 'active'; ?>">最热</a>
            </div>
        </div>
    </div>
</div>

<!-- Card Grid -->
<?php if(!empty($pigeons)): ?>
<h2 style="position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);border:0;white-space:nowrap;">铭鸽列表</h2>
<div class="pigeon-grid">
    <?php foreach($pigeons as $p): ?>
    <?php
        $pid = intval($p['id']);
        $imgs = json_decode($p['images'] ?? '[]', true) ?: [];
        $img = $imgs[0] ?? '';
        $gender = $p['gender'] ?? '';
        $price = intval($p['price'] ?? 0);
        $ring = trim($p['ring_number'] ?? '');
        $name = h($p['title'] ?? $p['name'] ?? '未命名');
        $bloodline = trim($p['bloodline'] ?? '');
        $color = trim($p['color'] ?? '');
        $eyeColor = trim($p['eye_color'] ?? '');
        $year = trim($p['year'] ?? '');
        $desc = trim(strip_tags($p['description'] ?? ''));
        $isMale = ($gender === 'male' || $gender === 1 || $gender === '1');
        $isFemale = ($gender === 'female' || $gender === 2 || $gender === '2');
    ?>
    <article class="pigeon-card">
        <a href="/pigeon/<?php echo $pid; ?>.html" class="card-img-wrap">
            <?php if($img): ?>
            <img src="<?php echo h($img); ?>" alt="<?php echo $name; ?>" loading="lazy">
            <?php else: ?>
            <span class="img-placeholder">🐦</span>
            <?php endif; ?>
            <div class="card-badges">
                <?php if($isMale): ?><span class="card-badge badge-male">♂ 雄</span><?php endif; ?>
                <?php if($isFemale): ?><span class="card-badge badge-female">♀ 雌</span><?php endif; ?>
                <?php if($price > 0): ?><span class="card-badge badge-price">¥<?php echo number_format($price); ?></span><?php endif; ?>
            </div>
        </a>
        <div class="card-body">
            <?php if($ring): ?>
            <div class="card-ring"><i class="fas fa-ring"></i> <?php echo h($ring); ?></div>
            <?php endif; ?>
            <h3 class="card-name"><a href="/pigeon/<?php echo $pid; ?>.html"><?php echo $name; ?></a></h3>
            <div class="card-tags">
                <?php if($bloodline): ?><span class="card-tag tag-bloodline">🧬 <?php echo h($bloodline); ?></span><?php endif; ?>
                <?php if($color): ?><span class="card-tag tag-color">🎨 <?php echo h($color); ?></span><?php endif; ?>
                <?php if($eyeColor && $eyeColor !== $color): ?><span class="card-tag tag-eye">👁 <?php echo h($eyeColor); ?></span><?php endif; ?>
                <?php if($year): ?><span class="card-tag tag-year">📅 <?php echo h($year); ?>年</span><?php endif; ?>
            </div>
        </div>
    </article>
    <?php endforeach; ?>
</div>

<?php echo renderPagination($page ?? 1, $totalPages ?? 1); ?>

<?php else: ?>
<div class="empty-state">
    <div class="empty-icon">🐦</div>
    <p class="empty-text">暂无符合条件的铭鸽</p>
    <a href="/pigeon/" class="empty-link">清除筛选条件 →</a>
</div>
<?php endif; ?>

<!-- Hot Pigeons -->
<?php if(!empty($hotPigeons)): ?>
<div class="hot-section">
    <h2 class="hot-heading">🔥 热门铭鸽</h2>
    <div class="hot-grid">
        <?php foreach($hotPigeons as $hp): ?>
        <?php
            $hpid = intval($hp['id']);
            $hImgs = json_decode($hp['images'] ?? '[]', true) ?: [];
            $hImg = $hImgs[0] ?? '';
            $hName = h($hp['name'] ?? '未命名');
            $hBlood = trim($hp['bloodline'] ?? '');
            $hViews = intval($hp['views'] ?? 0);
        ?>
        <a href="/pigeon/<?php echo $hpid; ?>.html" class="hot-card">
            <div class="hot-thumb">
                <?php if($hImg): ?>
                <img src="<?php echo h($hImg); ?>" alt="<?php echo $hName; ?>" loading="lazy">
                <?php else: ?>
                🐦
                <?php endif; ?>
            </div>
            <div class="hot-info">
                <div class="hot-name"><?php echo $hName; ?></div>
                <?php if($hBlood): ?>
                <div class="hot-meta"><span style="color:var(--accent);font-weight:600;"><?php echo h($hBlood); ?></span></div>
                <?php endif; ?>
            </div>
            <?php if($hViews > 0): ?>
            <span class="hot-views">👁 <?php echo $hViews > 999 ? round($hViews/1000, 1).'k' : $hViews; ?></span>
            <?php endif; ?>
        </a>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- 热门展厅内链 -->
<?php if(!empty($hotShops)): ?>
<div class="hot-section">
    <h2 class="hot-heading">🏪 热门展厅</h2>
    <div class="hot-grid">
        <?php foreach($hotShops as $hs): ?>
        <a href="/shop/<?php echo intval($hs['id']); ?>.html" class="hot-card">
            <div class="hot-thumb">
                <?php if(!empty($hs['avatar'])): ?>
                <img src="<?php echo h($hs['avatar']); ?>" alt="<?php echo h($hs['name']); ?>" loading="lazy">
                <?php else: ?>
                🏪
                <?php endif; ?>
            </div>
            <div class="hot-info">
                <div class="hot-name">
                    <?php if(!empty($hs['is_certified'])): ?><span style="color:var(--accent);font-weight:700;">✓</span> <?php endif; ?>
                    <?php echo h($hs['name']); ?>
                </div>
                <?php if(!empty($hs['province'])): ?>
                <div class="hot-meta">📍 <?php echo h($hs['province']); ?> · <?php echo intval($hs['pigeon_count'] ?? 0); ?>羽</div>
                <?php else: ?>
                <div class="hot-meta"><?php echo intval($hs['pigeon_count'] ?? 0); ?>羽铭鸽在售</div>
                <?php endif; ?>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

</div><!-- /container -->
</div><!-- /page-pigeons -->

<?php include __DIR__ . '/_footer.php'; ?>
</body>
</html>
