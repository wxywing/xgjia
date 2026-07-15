<?php
/**
 * 信鸽之家 - 公棚列表页（V2）
 */
require_once dirname(__DIR__) . '/app/config/config.php';

extract($data);

$page_title = '公棚大全' . (($page ?? 1) > 1 ? ' - 第' . intval($page) . '页' : '') . ' | ' . SITE_NAME;
$canonical_url = 'https://www.xgjia.com/loft/' . ($_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '');
$currentPath = $_SERVER['REQUEST_URI'] ?? '';

// 分页 query 参数保持器
$_queryBase = [];
if (!empty($currentProvince)) $_queryBase['province'] = $currentProvince;
if (!empty($currentRaceType)) $_queryBase['race_type'] = $currentRaceType;
if (!empty($currentKeyword)) $_queryBase['keyword'] = $currentKeyword;
if (!empty($isCertified)) $_queryBase['certified'] = 1;
if (!empty($orderBy)) $_queryBase['order'] = $orderBy;
$_qs = !empty($_queryBase) ? '&amp;' . http_build_query($_queryBase) : '';

// JSON-LD - ItemList
$ld_items = [];
foreach (array_slice($lofts ?? [], 0, 10) as $i => $l) {
    $ld_items[] = [
        '@type' => 'ListItem',
        'position' => $i + 1,
        'url' => 'https://www.xgjia.com/loft/' . $l['id'] . '.html',
    ];
}
$ld_itemlist = [
    '@context' => 'https://schema.org',
    '@type' => 'ItemList',
    'name' => '公棚大全',
    'numberOfItems' => $total ?? 0,
    'itemListElement' => $ld_items,
];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo h($page_title); ?></title>
    <meta name="description" content="信鸽之家公棚大全，收录全国<?php echo number_format($total ?? 0); ?>家公棚信息，支持按地区筛选查询。">
    <meta name="keywords" content="公棚,信鸽公棚,公棚查询,公棚大全,<?php echo h(SITE_KEYWORDS); ?>">
    <meta property="og:title" content="<?php echo h($page_title); ?>">
    <meta property="og:image" content="https://www.xgjia.com/public/images/og-cover.png">
    <link rel="canonical" href="<?php echo h($canonical_url); ?>">
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/public/css/b-scheme.css">
    <script type="application/ld+json"><?php echo json_encode($ld_itemlist, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>
    <link rel="shortcut icon" href="/public/images/favicon.ico">
<style>
/* ===== Loft List V2 CSS ===== */
*,*::before,*::after{box-sizing:border-box}
/* === Hero === */
.loft-hero{background:linear-gradient(135deg,#1e3a8a 0%,#2563eb 60%,#3b82f6 100%);color:#fff;padding:64px 20px 56px;text-align:center;position:relative;overflow:hidden}
.loft-hero::before{content:'';position:absolute;inset:0;background:radial-gradient(circle at 30% 60%,rgba(255,255,255,.06) 0%,transparent 50%);pointer-events:none}
.loft-hero h1{font-size:clamp(28px,5vw,40px);font-weight:800;letter-spacing:-1px;position:relative;z-index:1}
.loft-hero p{font-size:15px;opacity:.8;margin-top:10px;position:relative;z-index:1;max-width:440px;margin-inline:auto}

/* === Layout === */
.loft-layout{max-width:1280px;margin:0 auto;padding:24px 20px;display:grid;grid-template-columns:1fr 320px;gap:24px;align-items:start}
.loft-main{min-width:0}
.loft-sidebar{display:flex;flex-direction:column;gap:18px}

/* === Filter === */
.loft-filter{background:#fff;border-radius:12px;padding:14px 18px;box-shadow:0 1px 3px rgba(0,0,0,.07);display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:28px;border:1px solid #e5e7eb}
.loft-filter-label{font-size:12px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;white-space:nowrap}
.loft-filter-tag{padding:5px 14px;border-radius:20px;font-size:13px;color:#6b7280;background:#f3f4f6;transition:all .2s;white-space:nowrap;font-weight:500;text-decoration:none}
.loft-filter-tag:hover,.loft-filter-tag.active{background:#1e3a8a;color:#fff;text-decoration:none}
.loft-filter-spacer{flex:1}
.loft-filter-more{position:relative}
.loft-filter-more-btn{padding:5px 14px;border-radius:20px;font-size:13px;color:#1e3a8a;background:#eff6ff;border:none;cursor:pointer;font-weight:600;white-space:nowrap;font-family:inherit;text-decoration:none}
.loft-filter-more-btn:hover{background:#1e3a8a;color:#fff}
.loft-filter-dropdown{display:none;position:absolute;top:100%;left:0;margin-top:6px;background:#fff;border:1px solid #e5e7eb;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.1);padding:8px;min-width:160px;max-height:200px;overflow-y:auto;z-index:30;columns:2;column-gap:4px}
.loft-filter-dropdown.open{display:block}
.loft-filter-dropdown a{display:block;padding:5px 10px;border-radius:6px;font-size:12px;color:#6b7280;break-inside:avoid;text-decoration:none}
.loft-filter-dropdown a:hover{background:#eff6ff;color:#1e3a8a}
.loft-filter-search{position:relative}
.loft-filter-search input{padding:7px 12px 7px 32px;border:1px solid #e5e7eb;border-radius:20px;font-size:13px;width:160px;outline:none;font-family:inherit;transition:border-color .2s}
.loft-filter-search input:focus{border-color:#1e3a8a;width:200px}
.loft-filter-search .fa-search{position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:13px}

/* === Section Headers === */
.loft-section-hd{display:flex;align-items:baseline;justify-content:space-between;margin:32px 0 16px}
.loft-section-hd:first-of-type{margin-top:0}
.loft-section-hd h2{font-size:18px;font-weight:700;display:flex;align-items:center;gap:8px;color:#1f2937}
.loft-section-hd .count{font-size:13px;font-weight:400;color:#9ca3af}
.loft-section-hd .more{font-size:13px;color:#f97316;font-weight:600;text-decoration:none}
.loft-section-hd .more:hover{color:#fb923c}

/* === Hot Cards === */
.loft-hot-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;margin-bottom:36px}
.loft-hot-card{background:#fff;border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,.07);border:1px solid #e5e7eb;display:flex;flex-direction:column;overflow:hidden;transition:box-shadow .25s,transform .25s}
.loft-hot-card:hover{box-shadow:0 8px 24px rgba(0,0,0,.1);transform:translateY(-2px);border-color:#c7d2fe}
.loft-hot-body{padding:18px 18px 12px;display:flex;flex-direction:column;gap:8px;flex:1}
.loft-hot-body h3{font-size:15px;font-weight:700;line-height:1.35}
.loft-hot-body h3 a{color:#1f2937;text-decoration:none}
.loft-hot-body h3 a:hover{color:#1e3a8a}
.loft-hot-loc{font-size:12px;color:#9ca3af}
.loft-hot-badges{display:flex;gap:5px;flex-wrap:wrap}
.loft-hot-stats{display:grid;grid-template-columns:1fr 1fr;gap:6px 12px;margin-top:4px}
.loft-hot-stats>div{display:flex;justify-content:space-between;align-items:center}
.loft-hot-stats span{font-size:11px;color:#9ca3af}
.loft-hot-stats strong{font-size:12px;font-weight:700}
.loft-hot-btn{display:block;text-align:center;padding:9px;background:#eff6ff;color:#1e3a8a;font-size:13px;font-weight:600;transition:all .2s;border-top:1px solid #e5e7eb;text-decoration:none}
.loft-hot-btn:hover{background:#1e3a8a;color:#fff}

/* === List View === */
.loft-list-wrap{background:#fff;border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,.07);border:1px solid #e5e7eb}
.loft-list-row{display:flex;align-items:center;gap:14px;padding:14px 16px;border-bottom:1px solid #e5e7eb;transition:background .15s;font-size:14px;text-decoration:none;color:inherit}
.loft-list-row:hover{background:#eff6ff;text-decoration:none}
.loft-list-row:last-child{border-bottom:none}
.loft-list-stars{font-size:12px;flex-shrink:0;min-width:72px;color:#f59e0b;letter-spacing:1px}
.loft-list-name{flex:1;font-weight:600;color:#1f2937;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.loft-list-loc{font-size:12px;color:#9ca3af;flex-shrink:0;white-space:nowrap}
.loft-list-arrow{color:#9ca3af;font-size:13px;flex-shrink:0;transition:all .2s}
.loft-list-row:hover .loft-list-arrow{color:#1e3a8a;transform:translateX(3px)}
.loft-list-row:hover .loft-list-name{color:#1e3a8a}

/* === Sidebar Panels === */
.loft-panel{background:#fff;border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,.07);border:1px solid #e5e7eb;overflow:hidden}
.loft-panel-hd{padding:14px 16px;font-size:14px;font-weight:700;border-bottom:1px solid #e5e7eb;display:flex;align-items:center;gap:6px}
.loft-panel-body{padding:8px 0}
.loft-panel-body ul{list-style:none;padding:0;margin:0}
.loft-panel-body li+li{border-top:1px solid #f3f4f6}
.loft-panel-body li a{display:flex;flex-direction:column;padding:10px 16px;text-decoration:none;transition:background .15s;gap:2px;cursor:pointer}
.loft-panel-body li a:hover{background:#eff6ff}
.loft-season-stats{display:grid;grid-template-columns:1fr 1fr;gap:10px;padding:14px 16px}
.loft-season-stat{text-align:center;padding:10px;background:#f9fafb;border-radius:8px}
.loft-season-stat .num{font-size:22px;font-weight:800;color:#1e3a8a}
.loft-season-stat .lbl{font-size:11px;color:#9ca3af;margin-top:2px}
.loft-champ-loft{font-size:13px;font-weight:600;color:#1f2937}
.loft-champ-meta{font-size:11px;color:#9ca3af;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.loft-art-title{font-size:13px;font-weight:500;color:#1f2937;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.loft-art-date{font-size:11px;color:#9ca3af;margin-top:2px}

/* === Pagination === */
.loft-pagination{display:flex;justify-content:center;align-items:center;gap:4px;padding:20px 0}
.loft-pagination a,.loft-pagination span{display:inline-flex;align-items:center;justify-content:center;min-width:34px;height:34px;border-radius:8px;font-size:13px;font-weight:500;transition:all .2s;text-decoration:none}
.loft-pagination a{color:#6b7280;background:#fff;box-shadow:0 1px 3px rgba(0,0,0,.07);border:1px solid #e5e7eb}
.loft-pagination a:hover,.loft-pagination span.active{background:#1e3a8a;color:#fff;border-color:#1e3a8a}
.loft-pagination span.disabled{color:#9ca3af;opacity:.4}
.loft-pagination-note{text-align:center;font-size:12px;color:#9ca3af;margin:0 0 12px}

/* === Compare Tool CTA === */
.loft-compare-cta{max-width:1280px;margin:16px auto 0;padding:0 20px 28px}
.loft-compare-cta a{display:flex;align-items:center;justify-content:space-between;background:linear-gradient(135deg,#1e3a8a,#3b82f6);color:#fff;border-radius:12px;padding:16px 24px;text-decoration:none;transition:transform .2s,box-shadow .2s;gap:16px}
.loft-compare-cta a:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(30,58,138,.2);text-decoration:none;color:#fff}
.cta-content{display:flex;align-items:center;gap:12px;min-width:0}
.cta-icon{font-size:24px;flex-shrink:0}
.cta-text{display:flex;flex-direction:column;gap:3px;min-width:0}
.cta-text strong{font-size:15px;font-weight:700}
.cta-text span{font-size:13px;opacity:.8}
.cta-btn{background:#c9a84c;padding:8px 20px;border-radius:6px;font-weight:600;font-size:14px;white-space:nowrap;flex-shrink:0;transition:background .2s}
.loft-compare-cta a:hover .cta-btn{background:#b8943f}

/* === Responsive === */
@media(max-width:768px){
  .loft-layout{grid-template-columns:1fr;padding:16px}
  .loft-sidebar{order:2}
  .loft-hero{padding:40px 16px}
  .loft-hot-grid{grid-template-columns:1fr;gap:12px}
  .loft-list-row{gap:10px;padding:12px 10px}
  .loft-list-stars{min-width:60px;font-size:11px}
  .loft-list-name{font-size:13px}
  .loft-list-loc{display:none}
  .loft-filter-search input{width:120px}
  .loft-filter-search input:focus{width:140px}
  .loft-filter-more .loft-filter-dropdown{right:0;left:auto}
  .loft-season-stats{grid-template-columns:1fr 1fr}
}
</style>
</head>
<body>
<?php include __DIR__ . '/_head.php'; ?>

<!-- Hero -->
<section class="loft-hero">
  <h1>🏠 全国公棚信息大全</h1>
  <p>参赛费用 · 赛事数据 · 综合评分，帮您找到心仪的公棚</p>
</section>

<!-- Compare Tool CTA -->
<div class="loft-compare-cta">
  <a href="/loft/compare/">
    <div class="cta-content">
      <span class="cta-icon">⚖️</span>
      <div class="cta-text">
        <strong>公棚对比工具</strong>
        <span>选择 2~3 家公棚，横向对比参赛费、奖金、成绩数据</span>
      </div>
    </div>
    <span class="cta-btn">立即对比 →</span>
  </a>
</div>

<div class="loft-layout">
  <div class="loft-main">

    <!-- Filter Bar -->
    <div class="loft-filter">
      <span class="loft-filter-label">📍 地区</span>
      <a href="/loft/" class="loft-filter-tag<?php echo empty($currentProvince) ? ' active' : ''; ?>">全部</a>
      <?php
      $visProvs = array_slice($provinces ?? [], 0, 8);
      $moreProvs = array_slice($provinces ?? [], 8);
      foreach ($visProvs as $p):
          $active = ($currentProvince ?? '') === $p ? ' active' : '';
      ?>
      <a href="/loft/?province=<?php echo urlencode($p); ?>" class="loft-filter-tag<?php echo $active; ?>"><?php echo h($p); ?></a>
      <?php endforeach; ?>
      <?php if (!empty($moreProvs)): ?>
      <div class="loft-filter-more" id="provMore">
        <span class="loft-filter-more-btn" onclick="document.getElementById('provDropdown').classList.toggle('open')">更多 ▼</span>
        <div class="loft-filter-dropdown" id="provDropdown">
          <?php foreach ($moreProvs as $p): ?>
          <a href="/loft/?province=<?php echo urlencode($p); ?>"><?php echo h($p); ?></a>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
      <span class="loft-filter-spacer"></span>
      <div class="loft-filter-search">
        <i class="fa fa-search"></i>
        <input type="text" placeholder="搜索公棚..." value="<?php echo h($currentKeyword ?? ''); ?>" 
               onkeydown="if(event.key==='Enter'){var v=this.value.trim();if(v)location.href='/loft/?keyword='+encodeURIComponent(v);}">
      </div>
    </div>

    <!-- All Lofts -->
    <div class="loft-section-hd">
      <h2>📋 全部公棚 <span class="count">共 <?php echo number_format($total ?? 0); ?> 家</span></h2>
    </div>
    <?php if (!empty($lofts)): ?>
    <div class="loft-list-wrap">
      <?php foreach ($lofts as $l): ?>
      <a href="/loft/<?php echo intval($l['id']); ?>.html" class="loft-list-row">
        <span class="loft-list-stars"><?php
          $r = intval($l['rating'] ?? 0);
          for ($i=0;$i<5;$i++) echo $i * 2 < $r ? '★' : '☆';
        ?></span>
        <span class="loft-list-name"><?php echo h($l['name'] ?? ''); ?></span>
        <span class="loft-list-loc"><?php echo h(($l['province'] ?? '') . ($l['city'] ? ' · ' . $l['city'] : '')); ?></span>
        <span class="loft-list-arrow">→</span>
      </a>
      <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php $_tp = intval($totalPages ?? 1); $_cp = intval($page ?? 1); ?>
    <div class="loft-pagination">
      <?php if ($_cp > 1): ?><a href="/loft/?page=<?php echo $_cp-1 . $_qs; ?>">‹ 上一页</a><?php else: ?><span class="disabled">‹ 上一页</span><?php endif; ?>
      <?php
      for ($i = max(1, $_cp-2); $i <= min($_tp, $_cp+2); $i++):
          echo $i === $_cp ? '<span class="active">'.$i.'</span>' : '<a href="/loft/?page='.$i.$_qs.'">'.$i.'</a>';
      endfor;
      ?>
      <?php if ($_cp < $_tp): ?><a href="/loft/?page=<?php echo $_cp+1 . $_qs; ?>">下一页 ›</a><?php else: ?><span class="disabled">下一页 ›</span><?php endif; ?>
    </div>
    <div class="loft-pagination-note">共 <?php echo number_format($total ?? 0); ?> 家公棚 · <?php echo $_tp; ?> 页</div>
    <?php else: ?>
    <div style="text-align:center;padding:60px 20px;color:#9ca3af">
      <p>暂无符合条件的公棚</p>
      <a href="/loft/" style="color:#1e3a8a;font-weight:600">查看全部公棚 →</a>
    </div>
    <?php endif; ?>

    <!-- Hot Lofts -->
    <?php if (!empty($hotLofts)): ?>
    <div class="loft-section-hd">
      <h2>🔥 热门公棚 <span class="count">TOP <?php echo count($hotLofts); ?></span></h2>
      <a href="/loft/?order=rating" class="more">全部排行 →</a>
    </div>
    <div class="loft-hot-grid">
      <?php foreach ($hotLofts as $l): ?>
      <div class="loft-hot-card">
        <div class="loft-hot-body">
          <h3><a href="/loft/<?php echo intval($l['id']); ?>.html"><?php echo h($l['name'] ?? ''); ?></a></h3>
          <div class="loft-hot-loc">📍 <?php echo h(($l['province'] ?? '') . ($l['city'] ? ' · ' . $l['city'] : '')); ?></div>
          <?php
          $badges = [];
          if (!empty($l['race_type'])) $badges[] = '<span class="badge badge-type">' . h($l['race_type']) . '</span>';
          if (!empty($l['is_certified'])) $badges[] = '<span class="badge badge-cert">已认证</span>';
          if (!empty($l['prize_pool']) && $l['prize_pool'] > 1000000)
              $badges[] = '<span class="badge badge-prize">🏆 ¥' . number_format($l['prize_pool']/10000, 0) . '万</span>';
          ?>
          <?php if ($badges): ?>
          <div class="loft-hot-badges"><?php echo implode('', $badges); ?></div>
          <?php endif; ?>
          <div class="loft-hot-stats">
            <div><span>参赛费</span><strong><?php echo !empty($l['entry_fee']) ? ('<span style="color:#f97316">¥' . number_format($l['entry_fee']) . '</span>/羽') : '待定'; ?></strong></div>
            <div><span>评分</span><strong><?php
              $rating = intval($l['rating'] ?? 0);
              for ($i=0;$i<5;$i++) echo '<i class="fa' . ($i * 2 < $rating ? 's' : 'r') . ' fa-star" style="color:' . ($i * 2 < $rating ? '#f59e0b' : '#d1d5db') . ';font-size:11px"></i>';
              echo ' <small>' . $rating . '</small>';
            ?></strong></div>
            <div><span>收鸽</span><strong><?php echo !empty($l['current_count']) ? (number_format($l['current_count']) . '羽') : '--'; ?></strong></div>
            <div><span>距离</span><strong><?php echo !empty($l['race_distance']) ? ($l['race_distance'] . 'km') : '--'; ?></strong></div>
          </div>
        </div>
        <a href="/loft/<?php echo intval($l['id']); ?>.html" class="loft-hot-btn">查看详情 →</a>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

  </div>

  <!-- Sidebar -->
  <aside class="loft-sidebar">
    <!-- 2026 Season Stats -->
    <?php if (!empty($seasonStats)): ?>
    <div class="loft-panel">
      <div class="loft-panel-hd">📊 2026赛季数据</div>
      <div class="loft-season-stats">
        <div class="loft-season-stat">
          <div class="num"><?php echo number_format($seasonStats['active_lofts'] ?? 0); ?></div>
          <div class="lbl">参赛公棚</div>
        </div>
        <div class="loft-season-stat">
          <div class="num"><?php echo number_format($seasonStats['total_races'] ?? 0); ?></div>
          <div class="lbl">场比赛</div>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <!-- Recent Champions -->
    <?php if (!empty($champions)): ?>
    <div class="loft-panel">
      <div class="loft-panel-hd">🏆 最新冠军</div>
      <div class="loft-panel-body">
        <ul>
          <?php foreach ($champions as $c): ?>
          <li>
            <a href="/loft/<?php echo intval($c['loft_id'] ?? 0); ?>.html">
              <span class="loft-champ-loft"><?php echo h($c['loft_name']); ?></span>
              <span class="loft-champ-meta"><?php echo h(($c['race_name'] ?? '') . ' · ' . number_format($c['speed'] ?? 0, 2) . 'm/min'); ?></span>
            </a>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
    <?php endif; ?>

    <!-- Related Articles -->
    <?php if (!empty($loftArticles)): ?>
    <div class="loft-panel">
      <div class="loft-panel-hd">📰 公棚资讯</div>
      <div class="loft-panel-body">
        <ul>
          <?php foreach ($loftArticles as $a): ?>
          <li>
            <a href="/article/<?php echo intval($a['id']); ?>.html">
              <span class="loft-art-title"><?php echo h($a['title'] ?? ''); ?></span>
              <span class="loft-art-date"><?php echo h(substr($a['created_at'] ?? '', 0, 10)); ?></span>
            </a>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
    <?php endif; ?>
  </aside>
</div>

<!-- 关闭 `更多 ▼` 下拉的外点击 -->
<script>
document.addEventListener('click', function(e) {
  var dd = document.getElementById('provDropdown');
  var more = document.getElementById('provMore');
  if (dd && more && !more.contains(e.target)) {
    dd.classList.remove('open');
  }
});
</script>

<?php include __DIR__ . '/_footer.php'; ?>
</body>
</html>
