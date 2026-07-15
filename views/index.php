<?php
/**
 * 信鸽之家 - 首页（v8 工具型数据平台）
 * 定位：赛鸽界的"企查查"，以数据分析工具（深度报告/血统证书/公棚对比）差异化
 */
require_once dirname(__DIR__) . '/app/config/config.php';

extract($data);

$page_title = '信鸽之家 - 赛鸽数据查询平台 | 深度赛绩分析·血统证书·公棚对比';
$raceResultsCount = $stats['race_results'] ?? 0;
$raceResultsWan = $raceResultsCount > 0 ? round($raceResultsCount / 10000, 0) : 1274;
$raceResultsDisplay = $raceResultsWan >= 1000 ? number_format($raceResultsWan) : $raceResultsWan;

// FAQPage Schema (GEO SEO)
$homepage_faqs = [
    [
        'question' => '信鸽之家是什么网站？',
        'answer' => '信鸽之家是赛鸽数据查询平台，基于' . number_format($raceResultsWan, 0) . '万条真实赛事数据，提供足环号查询、赛绩分析、血统证书生成、公棚对比等服务，做赛鸽界的「企查查」。',
    ],
    [
        'question' => '如何查询赛鸽成绩？',
        'answer' => '在首页搜索框输入完整足环号，即可查看该赛鸽的历史参赛记录、分速排名、所在公棚等详细赛绩数据。',
    ],
    [
        'question' => '足环号查询是免费的吗？',
        'answer' => '信鸽之家基础查询功能永久免费，包括足环号搜索、赛绩查看、血统证书生成等。部分深度数据分析报告为付费服务。',
    ],
    [
        'question' => '血统证书怎么生成？',
        'answer' => '输入足环号查询赛鸽详情，点击「生成血统证书」按钮，系统自动提取该鸽父母信息及赛绩数据，生成专业PDF血统证书。',
    ],
    [
        'question' => '如何对比不同公棚？',
        'answer' => '在公棚详情页点击「加入对比」，选择2-3个公棚后进入对比页面，可查看各公棚的赛事规模、参赛羽数、分速水平等多维度数据对比。',
    ],
];
$ld_homepage_faqpage = [
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => [],
];
foreach ($homepage_faqs as $f) {
    $ld_homepage_faqpage['mainEntity'][] = [
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
    <meta name="description" content="赛鸽数据查询平台，输入足环号查赛绩、生成血统证书、对比全国公棚。基于真实赛事数据，做赛鸽界的「企查查」。">
    <meta name="keywords" content="赛鸽数据,足环号查询,赛绩分析,血统证书,公棚对比,信鸽数据,深度报告">
    <meta property="og:title" content="<?php echo h($page_title); ?>">
    <meta property="og:description" content="输入足环号查赛绩、生成血统证书、对比全国公棚。基于真实赛事数据，做赛鸽界的「企查查」。">
    <meta property="og:image" content="https://www.xgjia.com/public/images/og-cover.png">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?php echo h(SITE_NAME); ?>">
    <link rel="canonical" href="https://www.xgjia.com/">
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/public/css/b-scheme.css">
    <link rel="shortcut icon" href="/public/images/favicon.ico">

    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "<?php echo SITE_NAME; ?>",
        "url": "https://www.xgjia.com",
        "description": "赛鸽数据查询平台，深度赛绩分析、血统证书生成、公棚数据对比。",
        "sameAs": ["https://www.xgjia.com"]
    }
    </script>
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebSite",
        "name": "<?php echo SITE_NAME; ?>",
        "url": "https://www.xgjia.com",
        "potentialAction": {
            "@type": "SearchAction",
            "target": "https://www.xgjia.com/search?q={search_term_string}",
            "query-input": "required name=search_term_string"
        }
    }
    </script>
    <script type="application/ld+json"><?php echo json_encode($ld_homepage_faqpage, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>

<style>
/* ===== 首页 v8 内容区内联样式 ===== */
:root {
  --primary: #1a5fa8; --primary-dark: #154360; --accent: #c9a84c;
}

/* 标签 */
.badge-new { display: inline-block; background: #e74c3c; color: #fff; font-size: 10px; padding: 2px 8px; border-radius: 10px; margin-left: 6px; vertical-align: middle; font-weight: 600; }

/* ===== HERO ===== */
.hero-v2 {
  background: linear-gradient(135deg, #0d1b2a 0%, #1b3a5c 50%, #0d2137 100%);
  color: #fff; padding: 50px 0 56px; text-align: center;
  position: relative; overflow: hidden;
}
.hero-v2::before { content: ""; position: absolute; top: -50%; right: -20%; width: 600px; height: 600px; background: radial-gradient(circle, rgba(26,95,168,0.15) 0%, transparent 70%); border-radius: 50%; }
.hero-v2::after { content: ""; position: absolute; bottom: -30%; left: -15%; width: 400px; height: 400px; background: radial-gradient(circle, rgba(201,168,76,0.08) 0%, transparent 70%); border-radius: 50%; }
.hero-v2 * { position: relative; z-index: 1; }
.hero-v2-badge { display: inline-flex; align-items: center; gap: 6px; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.15); padding: 6px 16px; border-radius: 20px; font-size: 13px; color: rgba(255,255,255,0.8); margin-bottom: 20px; }
.hero-v2 h1 { font-size: 40px; font-weight: 900; letter-spacing: 2px; margin-bottom: 12px; }
.hero-v2 h1 em { font-style: normal; color: var(--accent); }
.hero-v2-desc { font-size: 16px; color: rgba(255,255,255,0.65); margin-bottom: 32px; letter-spacing: 1px; }
.hero-v2-desc b { color: rgba(255,255,255,0.85); }

/* 搜索 */
.hero-v2-search { max-width: 680px; margin: 0 auto 20px; }
.hero-v2-search-box { display: flex; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 8px 40px rgba(0,0,0,0.25); }
.hero-v2-search-box input { flex: 1; border: none; padding: 16px 20px; font-size: 16px; outline: none; color: var(--text); min-width: 0; }
.hero-v2-search-box input::placeholder { color: #95a5b8; }
.hero-v2-search-box button { background: var(--primary); color: #fff; border: none; padding: 16px 32px; font-size: 16px; font-weight: 600; cursor: pointer; white-space: nowrap; transition: background .2s; }
.hero-v2-search-box button:hover { background: var(--primary-dark); }
.hero-v2-search-hint { font-size: 12px; color: rgba(255,255,255,0.4); margin-top: 10px; }

/* 三大工具卡片 */
.hero-v2-tools { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; max-width: 780px; margin: 0 auto; }
.hero-v2-tool { background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 20px; text-align: left; transition: all .2s; cursor: pointer; text-decoration: none; color: #fff; display: block; }
.hero-v2-tool:hover { background: rgba(255,255,255,0.14); border-color: rgba(255,255,255,0.25); transform: translateY(-2px); }
.hero-v2-tool.disabled { opacity: 0.4; cursor: default; }
.hero-v2-tool.disabled:hover { background: rgba(255,255,255,0.08); transform: none; }
.hero-v2-tool-icon { font-size: 28px; margin-bottom: 8px; }
.hero-v2-tool h3 { font-size: 15px; font-weight: 700; margin-bottom: 4px; }
.hero-v2-tool p { font-size: 12px; color: rgba(255,255,255,0.55); line-height: 1.4; margin: 0; }

/* ===== 数据看板 ===== */
.stats-row-v2 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 10px; }
.stat-card-v2 { background: var(--white); border-radius: 8px; padding: 24px 20px; text-align: center; box-shadow: 0 2px 12px rgba(26,95,168,0.07); }
.stat-num-v2 { font-size: 32px; font-weight: 900; color: var(--primary); line-height: 1.2; }
.stat-label-v2 { font-size: 13px; color: #95a5b8; margin-top: 4px; }
.stats-note-v2 { text-align: center; font-size: 12px; color: #95a5b8; margin-top: 14px; }

/* ===== 快捷入口 ===== */
.quick-grid-v2 { display: grid; grid-template-columns: repeat(auto-fit, minmax(100px, 1fr)); gap: 10px; }
.quick-item-v2 { background: var(--white); border-radius: 8px; padding: 16px 10px; text-align: center; box-shadow: 0 2px 12px rgba(26,95,168,0.07); transition: all .2s; text-decoration: none; display: block; }
.quick-item-v2:hover { box-shadow: 0 8px 30px rgba(26,95,168,0.12); transform: translateY(-2px); }
.quick-item-v2 .qi-icon { font-size: 24px; margin-bottom: 6px; }
.quick-item-v2 .qi-name { font-size: 13px; font-weight: 600; color: #2c3e50; }

/* ===== 两栏布局 ===== */
.two-col-v2 { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }

/* ===== 公棚排名列表 ===== */
.list-card { background: var(--white); border-radius: 8px; box-shadow: 0 2px 12px rgba(26,95,168,0.07); overflow: hidden; }
.list-card-body { padding: 16px 20px; }
.list-item-v2 { display: flex; align-items: center; gap: 12px; padding: 12px 0; border-bottom: 1px solid #e8ecf0; text-decoration: none; }
.list-item-v2:last-child { border-bottom: none; }
.list-item-rank { width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; color: #fff; flex-shrink: 0; }
.list-item-rank.gold { background: linear-gradient(135deg, #f59e0b, #d97706); }
.list-item-rank.silver { background: linear-gradient(135deg, #94a3b8, #64748b); }
.list-item-rank.bronze { background: linear-gradient(135deg, #d97706, #b45309); }
.list-item-rank.num { background: #95a5b8; }
.list-item-info { flex: 1; min-width: 0; }
.list-item-title { font-size: 14px; font-weight: 600; color: #2c3e50; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.list-item-sub { font-size: 12px; color: #95a5b8; margin-top: 2px; }

/* ===== 铭鸽小卡片 ===== */
.pigeon-mini-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; }
.pigeon-mini-item { text-align: center; text-decoration: none; }
.pigeon-mini-item .pm-icon { width: 60px; height: 60px; border-radius: 8px; background: #f0f4ff; display: flex; align-items: center; justify-content: center; margin: 0 auto 6px; font-size: 28px; }
.pigeon-mini-item .pm-name { font-size: 12px; font-weight: 600; color: #2c3e50; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.pigeon-mini-item .pm-sub { font-size: 11px; color: #95a5b8; margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

/* ===== 资讯 ===== */
.art-row-v2 { display: flex; gap: 12px; padding: 12px 0; border-bottom: 1px solid #e8ecf0; text-decoration: none; color: inherit; }
.art-row-v2:last-child { border-bottom: none; }
.art-thumb-v2 { width: 80px; height: 56px; border-radius: 6px; background: #f0f4ff; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 20px; }
.art-body-v2 { flex: 1; min-width: 0; }
.art-title-v2 { font-size: 14px; font-weight: 600; color: #2c3e50; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.art-meta-v2 { font-size: 11px; color: #95a5b8; margin-top: 4px; }

/* ===== 最新更新 ===== */
.latest-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
.latest-card { background: var(--white); border-radius: 8px; padding: 20px; box-shadow: 0 2px 12px rgba(26,95,168,0.07); transition: all .2s; text-decoration: none; display: block; color: inherit; }
.latest-card:hover { box-shadow: 0 8px 30px rgba(26,95,168,0.12); transform: translateY(-2px); }
.latest-card-date { font-size: 11px; color: var(--primary); font-weight: 600; margin-bottom: 6px; }
.latest-card-title { font-size: 14px; font-weight: 600; color: #2c3e50; line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 42px; }
.latest-card-meta { font-size: 12px; color: #95a5b8; margin-top: 8px; }

/* ===== Section ===== */
.home-section { padding: 36px 0; }
.home-section-alt { padding: 36px 0; background: var(--white); }
.home-sec-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.home-sec-head h2 { font-size: 20px; font-weight: 800; color: #2c3e50; margin: 0; }
.home-sec-head a { font-size: 13px; color: var(--primary); font-weight: 600; text-decoration: none; }

/* ===== Mobile ===== */
@media (max-width: 768px) {
  .hero-v2 { padding: 32px 0 40px; }
  .hero-v2 h1 { font-size: 26px; }
  .hero-v2-desc { font-size: 14px; }
  .hero-v2-search-box input { font-size: 15px; padding: 14px; }
  .hero-v2-search-box button { padding: 14px 20px; font-size: 14px; }
  .hero-v2-tools { grid-template-columns: 1fr; }
  .stats-row-v2 { grid-template-columns: repeat(2, 1fr); }
  .quick-grid-v2 { grid-template-columns: repeat(4, 1fr); }
  .two-col-v2 { grid-template-columns: 1fr; }
  .pigeon-mini-row { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 480px) {
  .hero-v2 h1 { font-size: 22px; }
  .quick-grid-v2 { grid-template-columns: repeat(3, 1fr); }
  .hero-v2-search-box button { padding: 14px 16px; }
  .latest-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 480px) {
  .latest-grid { grid-template-columns: 1fr; }
}
</style>
</head>
<body>
<h1 style="position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);border:0;white-space:nowrap;">信鸽之家 - 赛鸽数据查询平台 | 深度赛绩分析·血统证书·公棚对比</h1>
<?php include __DIR__ . '/_head.php'; ?>

<!-- ===== HERO ===== -->
<section class="hero-v2">
  <div class="container">
    <div class="hero-v2-badge"><i class="fas fa-chart-line"></i> 赛鸽数据分析平台</div>
    <h1>赛鸽数据<em>，</em>一查便知</h1>
    <p class="hero-v2-desc">深度赛绩分析 &nbsp;·&nbsp; 血统证书生成 &nbsp;·&nbsp; 公棚横向对比<br><b>基于全国公棚真实赛事数据，做赛鸽界的"企查查"</b></p>

    <div class="hero-v2-search">
      <form class="hero-v2-search-box" action="/search.php" method="get">
        <input type="text" name="q" placeholder="输入足环号查赛绩 · 或搜公棚名称 / 鸽主姓名" autocomplete="off">
        <button type="submit"><i class="fas fa-search"></i> 查赛绩</button>
      </form>
      <div class="hero-v2-search-hint">
        <i class="fas fa-database"></i> 实时查询 <?php echo number_format($stats['lofts'] ?? 0); ?> 家公棚 · <?php echo number_format($stats['races'] ?? 0); ?> 场2026赛季 · <?php echo $raceResultsDisplay; ?> 万条成绩记录
      </div>
    </div>

    <div class="hero-v2-tools">
      <a href="/race/report/" class="hero-v2-tool">
        <div class="hero-v2-tool-icon">📊</div>
        <h3>深度赛绩报告</h3>
        <p>输入足环号 · 自动生成完整赛绩分析报告</p>
      </a>
      <a href="/pedigree/certificate/" class="hero-v2-tool">
        <div class="hero-v2-tool-icon">📜</div>
        <h3>血统证书生成</h3>
        <p>一键填入信息 · 生成可打印血统证书</p>
      </a>
      <a href="/loft/compare/" class="hero-v2-tool">
        <div class="hero-v2-tool-icon">🏠</div>
        <h3>公棚对比工具</h3>
        <p>横向对比全国公棚 · 数据决策参考</p>
      </a>
    </div>
  </div>
</section>

<!-- ===== 数据看板 ===== -->
<section class="home-section" style="padding-top: 32px;">
  <div class="container">
    <div class="stats-row-v2">
      <div class="stat-card-v2">
        <div class="stat-num-v2"><?php echo number_format($stats['lofts'] ?? 0); ?></div>
        <div class="stat-label-v2">收录公棚</div>
      </div>
      <div class="stat-card-v2">
        <div class="stat-num-v2"><?php echo number_format($stats['races'] ?? 0); ?></div>
        <div class="stat-label-v2">2026赛季赛事</div>
      </div>
      <div class="stat-card-v2">
        <div class="stat-num-v2"><?php echo $raceResultsDisplay; ?><small style="font-size:16px">万</small></div>
        <div class="stat-label-v2">成绩记录</div>
      </div>
      <div class="stat-card-v2">
        <div class="stat-num-v2"><?php echo number_format($stats['pigeons'] ?? 0); ?></div>
        <div class="stat-label-v2">收录铭鸽</div>
      </div>
    </div>
    <div class="stats-note-v2"><i class="fas fa-sync-alt fa-spin" style="margin-right:4px;"></i>数据来自全国公棚赛事平台，持续更新中</div>
  </div>
</section>

<!-- ===== 快捷入口 ===== -->
<section class="home-section" style="padding-top: 20px;">
  <div class="container">
    <div class="home-sec-head">
      <h2><i class="fas fa-compass" style="color:var(--primary);"></i> 快捷入口</h2>
    </div>
    <div class="quick-grid-v2">
      <a href="/race/champion/" class="quick-item-v2">
        <div class="qi-icon">🏆</div>
        <div class="qi-name">冠军鸽</div>
      </a>
      <a href="/race/season/2026/" class="quick-item-v2">
        <div class="qi-icon">📊</div>
        <div class="qi-name">赛季总结</div>
      </a>
      <a href="/race/city/" class="quick-item-v2">
        <div class="qi-icon">🏙️</div>
        <div class="qi-name">城市赛事</div>
      </a>
      <a href="/race/province/" class="quick-item-v2">
        <div class="qi-icon">📍</div>
        <div class="qi-name">省份聚合</div>
      </a>
      <a href="/pigeon/" class="quick-item-v2">
        <div class="qi-icon">🕊️</div>
        <div class="qi-name">铭鸽展厅</div>
      </a>
      <a href="/loft/" class="quick-item-v2">
        <div class="qi-icon">🏠</div>
        <div class="qi-name">公棚大全</div>
      </a>
      <a href="/tools/ring-guide/" class="quick-item-v2">
        <div class="qi-icon">📋</div>
        <div class="qi-name">足环代码表</div>
      </a>
      <a href="/race/city/%E5%8C%97%E4%BA%AC/top/" class="quick-item-v2">
        <div class="qi-icon">🏆</div>
        <div class="qi-name">城市赛鸽排行</div>
      </a>
      <a href="/tools/top100/" class="quick-item-v2">
        <div class="qi-icon">🏅</div>
        <div class="qi-name">分速TOP100</div>
      </a>
      <a href="/pedigree/" class="quick-item-v2">
        <div class="qi-icon">🧬</div>
        <div class="qi-name">血统品系</div>
      </a>
    </div>
  </div>
</section>

<!-- ===== 最新更新 ===== -->
<section class="home-section-alt">
  <div class="container">
    <div class="home-sec-head">
      <h2><i class="fas fa-sync-alt" style="color:var(--accent);"></i> 最新更新</h2>
      <a href="/race/browse/">全部赛事 →</a>
    </div>
    <div class="latest-grid">
<?php if (!empty($latestRaces)): ?>
<?php foreach (array_slice($latestRaces, 0, 6) as $race): ?>
      <a href="/race/<?php echo $race['id']; ?>.html" class="latest-card">
        <div class="latest-card-date"><i class="fas fa-calendar-alt"></i> <?php echo isset($race['release_time']) ? h(substr($race['release_time'], 0, 10)) : ''; ?></div>
        <div class="latest-card-title"><?php echo h($race['name'] ?? ''); ?></div>
        <div class="latest-card-meta">
          <i class="fas fa-building"></i> <?php echo h($race['loft_name'] ?? '未知公棚'); ?>
          <?php if (!empty($race['province'])): ?>
          <span style="margin-left:8px;"><i class="fas fa-map-marker-alt"></i> <?php echo h($race['province']); ?></span>
          <?php endif; ?>
        </div>
      </a>
<?php endforeach; ?>
<?php else: ?>
      <p style="color:#95a5b8;text-align:center;padding:20px;grid-column:1/-1;">暂无最新赛事数据</p>
<?php endif; ?>
    </div>
  </div>
</section>

<!-- ===== 两栏：热门公棚 + 铭鸽/资讯 ===== -->
<section class="home-section-alt">
  <div class="container">
    <div class="two-col-v2">

      <!-- 左：热门公棚 -->
      <div>
        <div class="home-sec-head">
          <h2><i class="fas fa-building" style="color:var(--accent);"></i> 热门公棚</h2>
          <a href="/loft/">查看全部 →</a>
        </div>
        <div class="list-card">
          <div class="list-card-body">
<?php if (!empty($hotLofts)): ?>
<?php $rankClasses = ['gold','silver','bronze','num','num','num','num','num']; ?>
<?php foreach (array_slice($hotLofts, 0, 8) as $idx => $loft): ?>
            <a href="/loft/<?php echo $loft['id']; ?>.html" class="list-item-v2">
              <span class="list-item-rank <?php echo $rankClasses[$idx] ?? 'num'; ?>"><?php echo $idx + 1; ?></span>
              <div class="list-item-info">
                <div class="list-item-title"><?php echo h($loft['name'] ?? ''); ?></div>
                <div class="list-item-sub"><i class="fas fa-map-marker-alt"></i> <?php echo h($loft['province'] ?? $loft['location'] ?? ''); ?><?php if (!empty($loft['distance_km'])): ?> · 决赛<?php echo $loft['distance_km']; ?>km<?php endif; ?></div>
              </div>
            </a>
<?php endforeach; ?>
<?php else: ?>
            <p style="color:#95a5b8;text-align:center;padding:20px;">暂无公棚数据</p>
<?php endif; ?>
          </div>
        </div>
      </div>

      <!-- 右：铭鸽 + 资讯 -->
      <div style="display:flex;flex-direction:column;gap:24px;">
        <div>
          <div class="home-sec-head">
            <h2><i class="fas fa-dove" style="color:var(--primary);"></i> 热门铭鸽</h2>
            <a href="/pigeon/">查看全部 →</a>
          </div>
          <div class="list-card">
            <div class="list-card-body">
<?php if (!empty($hotPigeons)): ?>
              <div class="pigeon-mini-row">
<?php foreach (array_slice($hotPigeons, 0, 4) as $p): ?>
<?php $pImg = (json_decode($p['images'] ?? '[]', true) ?: [])[0] ?? ''; ?>
                <a href="/pigeon/<?php echo $p['id']; ?>.html" class="pigeon-mini-item">
                  <div class="pm-icon"><?php if ($pImg): ?><img src="<?php echo h($pImg); ?>" alt="<?php echo h($p['name'] ?? ''); ?>" style="width:100%;height:100%;object-fit:cover;border-radius:8px;"><?php else: ?>🕊️<?php endif; ?></div>
                  <div class="pm-name"><?php echo h($p['name'] ?? ''); ?></div>
                  <div class="pm-sub"><?php echo h($p['strain_name'] ?? ''); ?></div>
                </a>
<?php endforeach; ?>
              </div>
<?php else: ?>
              <p style="color:#95a5b8;text-align:center;padding:20px;">暂无铭鸽数据</p>
<?php endif; ?>
            </div>
          </div>
        </div>

        <div>
          <div class="home-sec-head">
            <h2><i class="fas fa-newspaper" style="color:var(--accent);"></i> 最新资讯</h2>
            <a href="/article/">更多 →</a>
          </div>
          <div class="list-card">
            <div class="list-card-body">
<?php if (!empty($latestArticles)): ?>
<?php foreach (array_slice($latestArticles, 0, 4) as $art): ?>
              <a href="/article/<?php echo $art['id']; ?>.html" class="art-row-v2">
                <div class="art-thumb-v2"><?php if (!empty($art['cover'])): ?><img src="<?php echo h($art['cover']); ?>" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:6px;"><?php else: ?>📰<?php endif; ?></div>
                <div class="art-body-v2">
                  <div class="art-title-v2"><?php echo h($art['title'] ?? ''); ?></div>
                  <div class="art-meta-v2"><?php echo isset($art['created_at']) ? time_ago($art['created_at']) : ''; ?> · <?php echo number_format($art['views'] ?? 0); ?> 阅读</div>
                </div>
              </a>
<?php endforeach; ?>
<?php else: ?>
              <p style="color:#95a5b8;text-align:center;padding:20px;">暂无资讯</p>
<?php endif; ?>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ===== 平台介绍 ===== -->
<section class="home-section-alt" style="padding: 28px 0; border-top: 1px solid #e8ecf0;">
  <div class="container">
    <div style="max-width: 720px; margin: 0 auto; text-align: center;">
      <p style="font-size: 13px; color: #8696a7; line-height: 1.9; margin-bottom: 0;">
        信鸽之家专注赛鸽数据分析，收录
        <strong style="color: #5a6c7d;"><?php echo number_format($stats['lofts'] ?? 0); ?> 家全国公棚</strong>、
        <strong style="color: #5a6c7d;"><?php echo number_format($stats['races'] ?? 0); ?> 场2026赛季赛事</strong>，
        累计 <strong style="color: #5a6c7d;"><?php echo $raceResultsDisplay; ?> 万条成绩记录</strong>。
        提供足环号深度查询、血统证书生成、公棚横向对比等工具。
        数据来源覆盖各地公棚官网等权威渠道，持续更新中。
      </p>
    </div>
  </div>
</section>

<?php include __DIR__ . '/_footer.php'; ?>

</body>
</html>
