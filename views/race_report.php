<?php
/**
 * 足环号深度查询报告 — 数据分析仪表盘
 * URL: /race/report/{ring}/
 */
$ring_encoded = htmlspecialchars($ring);
$owner_enc = htmlspecialchars($stats['owner_name']);
$page_title = '足环号 ' . $ring . ' 深度查询报告 | 信鸽之家';
$page_desc = '足环号 ' . $ring . ' 深度分析报告：' . $stats['total_races'] . '场赛事数据';
if ($stats['owner_name']) $page_desc .= '，鸽主：' . $stats['owner_name'];
if ($stats['best_rank']) $page_desc .= '，最佳名次第' . $stats['best_rank'] . '名';
if ($stats['champion_count'] > 0) $page_desc .= '，含' . $stats['champion_count'] . '次冠军记录';
$page_desc .= ' — 含分速趋势图、赛季分布、同鸽主铭鸽推荐';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo $page_title; ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($page_desc); ?>">
    <meta name="keywords" content="<?php echo $ring; ?>,足环号查询,信鸽成绩分析,分速趋势,赛鸽报告,信鸽之家">
    <meta property="og:title" content="<?php echo $page_title; ?>">
    <meta property="og:type" content="article">
    <meta property="og:description" content="<?php echo htmlspecialchars($page_desc); ?>">
    <meta property="og:image" content="https://www.xgjia.com/public/images/og-cover.png">
    <link rel="canonical" href="https://www.xgjia.com/race/report/<?php echo urlencode($ring); ?>/">
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/public/css/style.css">
    <link rel="stylesheet" href="/public/css/b-scheme.css">
    <link rel="shortcut icon" href="/public/images/favicon.ico">
    <script src="https://cdn.bootcdn.net/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
    <style>
.page-report-wrap { background: #f4f6f9; }
.report-hero {
    background: linear-gradient(135deg, #1a5fa8 0%, #0d3b6e 50%, #0a2d52 100%);
    color: #fff; padding: 40px 0;
}
.report-hero .breadcrumb { font-size: 13px; opacity: 0.7; margin-bottom: 12px; }
.report-hero .breadcrumb a { color: rgba(255,255,255,0.8); text-decoration: none; }
.report-hero .breadcrumb a:hover { text-decoration: underline; }
.report-hero h1 { font-size: 30px; font-weight: 700; margin-bottom: 8px; display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.report-hero .subtitle { font-size: 14px; opacity: 0.85; margin-bottom: 20px; }
.report-badges { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 16px; }
.report-badge { display: inline-flex; align-items: center; gap: 5px; padding: 4px 14px; border-radius: 20px; font-size: 13px; background: rgba(255,255,255,0.18); }
.report-badge.owner { background: rgba(201,168,76,0.3); }
.report-stats-row { display: grid; grid-template-columns: repeat(5, 1fr); gap: 12px; }
.report-stat { background: rgba(255,255,255,0.12); border-radius: 12px; padding: 16px 12px; text-align: center; }
.report-stat .val { font-size: 24px; font-weight: 700; }
.report-stat .lbl { font-size: 11px; opacity: 0.7; margin-top: 4px; }
.report-stat.champion .val { color: #ffd700; }

.report-search-bar { background: #fff; padding: 16px 0; border-bottom: 1px solid #e8ecf0; }
.report-search-inner { display: flex; gap: 0; max-width: 600px; }
.report-search-inner input { flex: 1; padding: 12px 18px; border: 2px solid #dde1e6; border-right: none; border-radius: 8px 0 0 8px; font-size: 15px; outline: none; }
.report-search-inner input:focus { border-color: #1a5fa8; }
.report-search-inner button { padding: 12px 28px; background: #1a5fa8; color: #fff; border: none; border-radius: 0 8px 8px 0; font-size: 15px; font-weight: 600; cursor: pointer; white-space: nowrap; }
.report-search-inner button:hover { background: #15508c; }

/* ===== 内容区 ===== */
.report-content { padding: 32px 0; }
.report-section { background: #fff; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); padding: 24px; margin-bottom: 24px; }
.report-section h3 { font-size: 18px; font-weight: 700; margin-bottom: 16px; color: #2c3e50; display: flex; align-items: center; gap: 8px; }
.report-section h3 i { color: #1a5fa8; }

/* 分速趋势图 */
.speed-chart-wrap { position: relative; height: 380px; max-height: 50vh; }
.speed-chart-wrap canvas { width: 100% !important; }

/* 统计表格 */
.report-table { width: 100%; border-collapse: collapse; font-size: 14px; }
.report-table th { background: #f8f9fb; padding: 10px 12px; text-align: left; font-weight: 600; color: #555; border-bottom: 2px solid #e8ecf0; white-space: nowrap; }
.report-table td { padding: 10px 12px; border-bottom: 1px solid #f0f0f0; }
.report-table tr:hover { background: #f8f9fb; }
.report-table .podium td { background: #fffdf5; }
.report-table .champion td { background: #fff8f8; font-weight: 600; }
.report-table .champion .rank-cell { color: #c62828; }
.report-table .rank-cell { font-weight: 700; }
.report-table .rank-cell.crown { color: #f39c12; }
.report-table .speed-val { font-weight: 600; color: #1a5fa8; white-space: nowrap; }
.report-table .dist-val { color: #777; white-space: nowrap; }

/* 冠军高亮区 */
.champion-row { padding: 14px 18px; border-radius: 10px; margin-bottom: 10px; background: linear-gradient(135deg, #fff8f8, #fff3e0); border-left: 4px solid #c62828; display: flex; align-items: center; gap: 14px; flex-wrap: wrap; }
.champion-row .champ-rank { font-size: 28px; font-weight: 900; color: #c62828; min-width: 50px; }
.champion-row .champ-detail { flex: 1; min-width: 200px; }
.champion-row .champ-detail .race-name { font-weight: 700; font-size: 15px; }
.champion-row .champ-detail .race-meta { color: #777; font-size: 13px; margin-top: 2px; }
.champion-row .champ-speed { text-align: right; min-width: 100px; }
.champion-row .champ-speed .speed { font-size: 18px; font-weight: 700; color: #1a5fa8; }

/* 同鸽主铭鸽卡片 */
.same-owner-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 12px; }
.same-owner-card { padding: 14px; border-radius: 10px; background: #f8f9fb; border: 1px solid #e8ecf0; cursor: pointer; transition: all .2s; }
.same-owner-card:hover { border-color: #1a5fa8; box-shadow: 0 2px 8px rgba(26,95,168,0.15); }
.same-owner-card .so-ring { font-weight: 700; color: #1a5fa8; margin-bottom: 4px; }
.same-owner-card .so-meta { font-size: 12px; color: #888; }
.same-owner-card .so-meta span { margin-right: 10px; }

/* 赛季分布条 */
.season-bars { display: flex; gap: 8px; height: 28px; border-radius: 6px; overflow: hidden; }
.season-bar { display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 600; color: #fff; min-width: 40px; transition: all .3s; }
.season-bar.spring { background: #4caf50; }
.season-bar.autumn { background: #e65100; }
.season-bar.winter { background: #5c6bc0; }
.season-bar.summer { background: #f9a825; color: #333; }

/* 响应式 */
@media (max-width: 768px) {
    .report-stats-row { grid-template-columns: repeat(3, 1fr); }
    .report-hero h1 { font-size: 22px; }
    .report-hero { padding: 24px 0; }
    .speed-chart-wrap { height: 280px; }
    .champion-row { flex-direction: column; align-items: flex-start; }
    .champion-row .champ-speed { text-align: left; }
    .same-owner-grid { grid-template-columns: 1fr; }
    .report-section { padding: 16px; }
    .report-table { font-size: 12px; }
    .report-table th, .report-table td { padding: 8px 6px; }
}

/* ===== 打印样式 ===== */
@media print {
    /* 隐藏导航、页脚、按钮 */
    .navbar, .nav-actions, .hamburger, .mobile-nav,
    .footer, .no-print,
    .breadcrumb,
    .paywall-card, .paywall-action,
    .report-actions, .report-badges { display: none !important; }

    /* Hero 去装饰，保留标题+统计 */
    .report-hero {
        background: #fff !important;
        color: #000 !important;
        padding: 0 0 12px 0 !important;
        border-bottom: 2px solid #000 !important;
        margin-bottom: 16px !important;
    }
    .report-hero .subtitle { color: #333 !important; }

    /* 基础重置 */
    body { background: #fff !important; color: #000 !important; margin: 0; padding: 0; }
    .page-report-wrap { background: #fff !important; max-width: 100% !important; }
    .container { max-width: 100% !important; padding: 0 !important; }

    /* 内容区 */
    .report-section {
        box-shadow: none !important;
        border: 1px solid #ccc !important;
        page-break-inside: avoid;
        margin-bottom: 12px !important;
        padding: 14px !important;
        background: #fff !important;
    }
    .report-section h3 { color: #000 !important; font-size: 16px !important; }

    /* 表格 */
    .report-table { font-size: 12px !important; width: 100% !important; }
    .report-table th { background: #eee !important; color: #000 !important; }
    .report-table td { color: #000 !important; border-bottom: 1px solid #ddd !important; }
    .report-table .champion td { background: #fffde7 !important; }
    .report-table .podium td { background: #f5f5f5 !important; }

    /* 图表 */
    .speed-chart-wrap { max-width: 100% !important; height: auto !important; page-break-inside: avoid; }
    .speed-chart-wrap canvas { max-width: 100% !important; height: auto !important; }

    /* 卡片 */
    .same-owner-card, .champion-row {
        border: 1px solid #ddd !important;
        page-break-inside: avoid;
    }

    /* 链接去色 */
    a { color: #000 !important; text-decoration: underline; }

    /* 边距优化 */
    @page { margin: 15mm; }
    h1 { font-size: 20px !important; margin-top: 0 !important; }
    .report-stats-row { display: grid !important; grid-template-columns: repeat(5, 1fr) !important; gap: 8px !important; }
    .report-stat { background: #f5f5f5 !important; border: 1px solid #ddd !important; color: #000 !important; }
    .report-stat .val { font-size: 20px !important; color: #000 !important; }
    .report-stat .lbl { font-size: 11px !important; color: #555 !important; }

    /* 冠军行 */
    .champion-row { flex-direction: row !important; align-items: center !important; }
    .rank-cell.crown { color: #c62828 !important; font-weight: 700 !important; }

    /* 隐藏付费预览区（未解锁时） */
    .paywall-blur { filter: none !important; }
}
    </style>
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "BreadcrumbList",
        "itemListElement": [
            {"@type": "ListItem", "position": 1, "name": "首页", "item": "https://www.xgjia.com"},
            {"@type": "ListItem", "position": 2, "name": "赛事成绩", "item": "https://www.xgjia.com/race/"},
            {"@type": "ListItem", "position": 3, "name": "足环号报告: <?php echo $ring_encoded; ?>"}
        ]
    }
    </script>
</head>
<body>
<div class="page-report-wrap">
<?php include __DIR__ . '/_head.php'; ?>

<!-- Hero -->
<div class="report-hero">
    <div class="container">
        <div class="breadcrumb">
            <a href="/race/">赛事成绩</a> › <a href="/race/ring/<?php echo urlencode($ring); ?>/">足环号时间线</a> › 深度报告
        </div>
        <h1>
            📊 足环号 <?php echo $ring_encoded; ?>
            <span style="font-size:14px;font-weight:400;opacity:0.8;background:rgba(255,255,255,0.15);padding:3px 12px;border-radius:12px;">深度查询报告</span>
        </h1>
        <?php if ($stats['owner_name'] || $stats['color'] || $stats['region']): ?>
        <div class="report-badges">
            <?php if ($stats['owner_name']): ?>
            <span class="report-badge owner"><i class="fas fa-user"></i> <?php echo $owner_enc; ?></span>
            <?php endif; ?>
            <?php if ($stats['color']): ?>
            <span class="report-badge"><i class="fas fa-palette"></i> <?php echo htmlspecialchars($stats['color']); ?></span>
            <?php endif; ?>
            <?php if ($stats['region']): ?>
            <span class="report-badge"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($stats['region']); ?></span>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        <div class="report-stats-row">
            <div class="report-stat">
                <div class="val"><?php echo $stats['total_races']; ?></div>
                <div class="lbl">参赛场次</div>
            </div>
            <div class="report-stat">
                <div class="val"><?php echo $stats['best_rank'] ? '第' . $stats['best_rank'] . '名' : '—'; ?></div>
                <div class="lbl">最佳名次</div>
            </div>
            <div class="report-stat">
                <div class="val"><?php echo $stats['best_speed'] > 0 ? number_format($stats['best_speed'], 0) : '—'; ?></div>
                <div class="lbl">最高分速(m/分)</div>
            </div>
            <div class="report-stat">
                <div class="val"><?php echo $stats['total_lofts']; ?></div>
                <div class="lbl">参赛公棚</div>
            </div>
            <div class="report-stat champion">
                <div class="val"><?php echo $stats['champion_count'] > 0 ? '🏆×' . $stats['champion_count'] : '—'; ?></div>
                <div class="lbl">冠军次数</div>
            </div>
        </div>
    </div>
</div>

<!-- Search Bar -->
<div class="report-search-bar">
    <div class="container">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:10px;">
            <a href="/race/ring/<?php echo urlencode($ring); ?>" style="font-size:13px;color:#999;text-decoration:none;padding-bottom:4px;transition:color .2s;" onmouseover="this.style.color='#1a5fa8'">⏱ 时间线</a>
            <a href="/race/report/<?php echo urlencode($ring); ?>" style="font-size:13px;font-weight:700;color:#1a5fa8;text-decoration:none;border-bottom:3px solid #1a5fa8;padding-bottom:4px;">📊 深度报告</a>
        </div>
        <form class="report-search-inner" onsubmit="
            var v = document.getElementById('reportRingInput').value.trim().replace(/\/+$/g,'');
            if (!v) return false;
            window.location.href = '/race/report/' + encodeURIComponent(v) + '/';
            return false;
        ">
            <input type="text" id="reportRingInput" placeholder="输入足环号生成深度报告" value="<?php echo $ring_encoded; ?>">
            <button type="submit"><i class="fas fa-chart-bar"></i> 查报告</button>
        </form>
    </div>
</div>

<div class="container report-content">
<?php if (empty($results)): ?>
    <div class="report-section" style="text-align:center;padding:60px 20px;">
        <i class="fas fa-search" style="font-size:48px;color:#ddd;display:block;margin-bottom:16px;"></i>
        <h3>未找到该足环号的赛事记录</h3>
        <p style="color:#999;margin-top:8px;">请检查足环号是否正确，或前往 <a href="/race/ring/<?php echo urlencode($ring); ?>/">时间线</a> 查看</p>
    </div>
<?php else: ?>

    <!-- 1. 基本统计 -->
    <div class="report-section">
        <h3><i class="fas fa-chart-pie"></i> 赛季分布 & 关键指标</h3>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:16px;">
            <div style="text-align:center;padding:12px;background:#f8f9fb;border-radius:8px;">
                <div style="font-size:13px;color:#888;margin-bottom:4px;">平均分速</div>
                <div style="font-size:22px;font-weight:700;color:#1a5fa8;"><?php echo $stats['avg_speed'] > 0 ? number_format($stats['avg_speed'], 0) : '—'; ?> <span style="font-size:12px;color:#999;">m/分</span></div>
            </div>
            <div style="text-align:center;padding:12px;background:#f8f9fb;border-radius:8px;">
                <div style="font-size:13px;color:#888;margin-bottom:4px;">登台次数</div>
                <div style="font-size:22px;font-weight:700;color:#c9a84c;"><?php echo $stats['podium_count'] > 0 ? $stats['podium_count'] . '次' : '—'; ?></div>
            </div>
            <div style="text-align:center;padding:12px;background:#f8f9fb;border-radius:8px;">
                <div style="font-size:13px;color:#888;margin-bottom:4px;">首战</div>
                <div style="font-size:16px;font-weight:600;"><?php echo $stats['first_race'] ? htmlspecialchars($stats['first_race']) : '—'; ?></div>
            </div>
            <div style="text-align:center;padding:12px;background:#f8f9fb;border-radius:8px;">
                <div style="font-size:13px;color:#888;margin-bottom:4px;">最近一战</div>
                <div style="font-size:16px;font-weight:600;"><?php echo $stats['last_race'] ? htmlspecialchars($stats['last_race']) : '—'; ?></div>
            </div>
        </div>
        <?php
        $totalSeason = $seasons['spring'] + $seasons['autumn'] + $seasons['summer'] + $seasons['winter'];
        if ($totalSeason > 0):
        ?>
        <div style="font-size:13px;color:#888;margin-bottom:6px;">赛季分布</div>
        <div class="season-bars">
            <?php if ($seasons['spring']): ?><div class="season-bar spring" style="flex:<?php echo $seasons['spring']; ?>">春 <?php echo $seasons['spring']; ?></div><?php endif; ?>
            <?php if ($seasons['summer']): ?><div class="season-bar summer" style="flex:<?php echo $seasons['summer']; ?>">夏 <?php echo $seasons['summer']; ?></div><?php endif; ?>
            <?php if ($seasons['autumn']): ?><div class="season-bar autumn" style="flex:<?php echo $seasons['autumn']; ?>">秋 <?php echo $seasons['autumn']; ?></div><?php endif; ?>
            <?php if ($seasons['winter']): ?><div class="season-bar winter" style="flex:<?php echo $seasons['winter']; ?>">冬 <?php echo $seasons['winter']; ?></div><?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($unlocked): ?>

    <!-- 已解锁工具栏 -->
    <div class="no-print" style="max-width:960px;margin:16px auto;display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
        <span style="font-size:13px;color:#2e7d32;"><i class="fas fa-check-circle"></i> 已解锁完整版</span>
        <button onclick="window.print()" style="margin-left:auto;padding:10px 24px;background:#1a5fa8;color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:700;cursor:pointer;"><i class="fas fa-print"></i> 打印 / 导出PDF</button>
    </div>
    <div class="no-print" style="max-width:960px;margin:0 auto 16px;padding:8px 14px;background:#fff8e1;border:1px solid #ffe082;border-radius:6px;font-size:12px;color:#795548;">
        💡 <strong>导出PDF提示：</strong>点击上方按钮后，在浏览器打印对话框中将「目标打印机」改为<strong>「另存为PDF」</strong>即可保存为PDF文件。
    </div>

    <!-- 2. 分速趋势图 -->
    <?php if (count($speedData) >= 2): ?>
    <div class="report-section">
        <h3><i class="fas fa-chart-line"></i> 分速趋势</h3>
        <p style="font-size:13px;color:#888;margin-bottom:12px;">按参赛时间排列，金牌点 = 冠军场次</p>
        <div class="speed-chart-wrap">
            <canvas id="speedChart"></canvas>
        </div>
    </div>
    <?php endif; ?>

    <!-- 3. 冠军记录 -->
    <?php if (!empty($championRaces)): ?>
    <div class="report-section">
        <h3><i class="fas fa-crown" style="color:#c62828;"></i> 冠军记录（<?php echo count($championRaces); ?>次登顶）</h3>
        <?php foreach ($championRaces as $cr): ?>
        <div class="champion-row">
            <div class="champ-rank">🥇</div>
            <div class="champ-detail">
                <div class="race-name"><?php echo htmlspecialchars($cr['race_name'] ?? '未知赛事'); ?></div>
                <div class="race-meta">
                    <?php echo htmlspecialchars($cr['loft_name'] ?? ''); ?>
                    <?php if ($cr['release_time'] ?? ''): ?> · <?php echo date('Y-m-d', strtotime($cr['release_time'])); ?><?php endif; ?>
                    <?php if (($cr['distance'] ?? 0) > 0): ?> · <?php echo number_format($cr['distance']); ?>m<?php endif; ?>
                </div>
            </div>
            <div class="champ-speed">
                <div class="speed"><?php echo ($cr['speed'] ?? 0) > 0 ? number_format($cr['speed'], 0) : '—'; ?></div>
                <div style="font-size:12px;color:#999;">m/分</div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- 4. 参赛公棚 -->
    <?php if (!empty($loftList)): ?>
    <div class="report-section">
        <h3><i class="fas fa-bullseye"></i> 参赛公棚（<?php echo count($loftList); ?>个）</h3>
        <div style="display:flex;gap:12px;flex-wrap:wrap;">
            <?php foreach ($loftList as $lid => $loft): ?>
            <a href="/loft/<?php echo $lid; ?>.html" style="padding:10px 18px;border:1px solid #e0e0e0;border-radius:8px;text-decoration:none;color:#333;display:flex;align-items:center;gap:8px;transition:all .2s;">
                <span style="font-weight:600;"><?php echo htmlspecialchars($loft['name']); ?></span>
                <span style="font-size:12px;color:#1a5fa8;background:#e8f2fc;padding:2px 8px;border-radius:10px;"><?php echo $loft['count']; ?>场</span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- 5. 全量赛绩表 -->
    <div class="report-section">
        <h3><i class="fas fa-table"></i> 参赛履历（<?php echo count($results); ?>场）</h3>
        <div style="overflow-x:auto;">
        <table class="report-table">
            <thead>
                <tr>
                    <th>日期</th>
                    <th>赛季</th>
                    <th>赛事</th>
                    <th>公棚</th>
                    <th>空距</th>
                    <th>名次</th>
                    <th>分速</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $r):
                    $rank = intval($r['rank'] ?? 0);
                    $rowCls = '';
                    if ($rank == 1) $rowCls = ' champion';
                    elseif ($rank <= 3) $rowCls = ' podium';
                    $seasonLabel = ($r['season_type'] ?? '') == 'autumn' ? '秋' : (($r['season_type'] ?? '') == 'spring' ? '春' : (($r['season_type'] ?? '') == 'summer' ? '夏' : (($r['season_type'] ?? '') == 'winter' ? '冬' : '')));
                ?>
                <tr class="<?php echo $rowCls; ?>">
                    <td style="white-space:nowrap;"><?php echo ($r['release_time'] ?? '') ? date('Y-m-d', strtotime($r['release_time'])) : ($r['season_year'] ?? '—'); ?></td>
                    <td><?php echo $seasonLabel ?: '—'; ?></td>
                    <td><a href="/race/<?php echo intval($r['race_id'] ?? 0); ?>.html" style="color:#1a5fa8;"><?php echo htmlspecialchars($r['race_name'] ?? '—'); ?></a></td>
                    <td><a href="/loft/<?php echo intval($r['loft_id'] ?? 0); ?>.html" style="color:#666;"><?php echo htmlspecialchars($r['loft_name'] ?? '—'); ?></a></td>
                    <td class="dist-val"><?php echo ($r['distance'] ?? 0) > 0 ? number_format($r['distance']) . 'm' : '—'; ?></td>
                    <td class="rank-cell<?php echo $rank == 1 ? ' crown' : ''; ?>"><?php echo $rank > 0 ? $rank : '—'; ?></td>
                    <td class="speed-val"><?php echo ($r['speed'] ?? 0) > 0 ? number_format($r['speed'], 0) . ' m/分' : '—'; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>

    <!-- 6. 同鸽主其他铭鸽 -->
    <?php if (!empty($sameOwnerBirds)): ?>
    <div class="report-section">
        <h3><i class="fas fa-users"></i> 同鸽主（<?php echo $owner_enc; ?>）其他赛鸽</h3>
        <p style="font-size:13px;color:#888;margin-bottom:14px;">同一鸽主的其他参赛鸽，点击可查看详细报告</p>
        <div class="same-owner-grid">
            <?php foreach ($sameOwnerBirds as $bird):
                $bRing = $bird['ring_number'] ?? '';
                if (empty($bRing) || $bRing === $ring) continue;
            ?>
            <a href="/race/report/<?php echo urlencode($bRing); ?>/" class="same-owner-card" style="text-decoration:none;display:block;">
                <div class="so-ring"><?php echo htmlspecialchars($bRing); ?></div>
                <div class="so-meta">
                    <?php if ($bird['color'] ?? ''): ?><span>🪶 <?php echo htmlspecialchars($bird['color']); ?></span><?php endif; ?>
                    <?php if ($bird['region'] ?? ''): ?><span>📍 <?php echo htmlspecialchars($bird['region']); ?></span><?php endif; ?>
                    <span><?php echo intval($bird['race_count'] ?? 0); ?>场比赛</span>
                    <?php if (($bird['best_speed'] ?? 0) > 0): ?><span>⚡<?php echo number_format($bird['best_speed'], 0); ?>m/分</span><?php endif; ?>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php elseif ($pendingOrder): ?>

    <!-- 审核中提示 -->
    <div style="max-width:960px;margin:32px auto;padding:40px 24px;border:2px solid #ffe082;border-radius:16px;background:#fffde7;text-align:center;">
        <div style="font-size:56px;margin-bottom:16px;">⏳</div>
        <h3 style="color:#f57f17;margin-bottom:10px;font-size:20px;">审核中，请耐心等待</h3>
        <p style="color:#795548;font-size:14px;margin-bottom:20px;line-height:1.8;">
            您的解锁申请已提交（订单号：<?php echo htmlspecialchars($pendingOrder['order_no']); ?>），<br>
            管理员审核通过后即可查看完整深度报告。
        </p>
        <div style="padding:14px 20px;background:#fff;border-radius:10px;border:1px solid #ffe082;display:inline-block;text-align:left;font-size:13px;color:#666;">
            💡 如需加急处理，请<a href="/pages/contact/" style="color:#1a5fa8;">联系我们</a>并提供订单号
        </div>
        <div style="margin-top:20px;">
            <button onclick="location.reload()" style="padding:10px 28px;background:#1a5fa8;color:#fff;border:none;border-radius:8px;font-size:14px;cursor:pointer;">
                <i class="fas fa-sync-alt"></i> 刷新查看状态
            </button>
        </div>
    </div>

    <?php elseif ($approvedOrder): ?>

    <!-- 已审核通过，请完成支付 -->
    <div class="paywall-card" style="max-width:500px;margin:32px auto;padding:32px 24px;background:#fff;border-radius:16px;box-shadow:0 4px 24px rgba(0,0,0,0.08);text-align:center;">
        <div style="font-size:48px;margin-bottom:12px;">✅</div>
        <h3 style="color:#16a34a;margin-bottom:8px;font-size:20px;">审核已通过！</h3>
        <p style="color:#666;font-size:14px;margin-bottom:6px;">订单号：<strong><?php echo htmlspecialchars($approvedOrder['order_no']); ?></strong></p>
        <p style="color:#888;font-size:13px;margin-bottom:20px;">管理员已审核通过，请完成支付即可查看完整报告</p>
        
        <!-- 价格 -->
        <div style="margin:20px 0;padding:18px;background:#f0fdf4;border-radius:10px;border:1px solid #bbf7d0;">
            <div style="font-size:13px;color:#666;margin-bottom:4px;">支付金额</div>
            <div style="font-size:42px;font-weight:900;color:#16a34a;line-height:1;">
                <span style="font-size:22px;">¥</span>9<span style="font-size:16px;">.9</span>
            </div>
            <div style="font-size:12px;color:#999;margin-top:4px;">一次购买，永久可查</div>
        </div>

        <!-- 支付方式 -->
        <div style="display:flex;gap:10px;margin-bottom:20px;">
            <div class="paywall-pay-method" style="flex:1;padding:12px;border:2px solid #1a5fa8;border-radius:8px;cursor:pointer;text-align:center;background:#e8f2fc;" onclick="togglePayMethod(this)" data-method="wechat">
                <i class="fab fa-weixin" style="font-size:20px;color:#07c160;"></i>
                <span style="display:block;font-size:13px;margin-top:4px;font-weight:600;">微信支付</span>
            </div>
            <div class="paywall-pay-method" style="flex:1;padding:12px;border:2px solid #e0e0e0;border-radius:8px;cursor:pointer;text-align:center;" onclick="togglePayMethod(this)" data-method="alipay">
                <i class="fab fa-alipay" style="font-size:20px;color:#1677ff;"></i>
                <span style="display:block;font-size:13px;margin-top:4px;font-weight:600;">支付宝</span>
            </div>
        </div>

        <!-- 支付按钮 -->
        <button id="payBtn" onclick="showQRModal('approved')" style="width:100%;padding:14px;background:#16a34a;color:#fff;border:none;border-radius:8px;font-size:16px;font-weight:700;cursor:pointer;transition:opacity .2s;" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
            <i class="fas fa-credit-card" style="margin-right:6px;"></i> 立即支付 ¥9.9
        </button>
        <p style="font-size:12px;color:#bbb;margin-top:16px;">遇到支付问题请<a href="/pages/contact/" style="color:#999;">联系我们</a></p>
    </div>

    <?php else: ?>

    <!-- ===== 付费内容预览 ===== -->

    <!-- 冠军亮点 -->
    <?php if ($stats['champion_count'] > 0): ?>
    <div class="report-section" style="background:linear-gradient(135deg,#fffdf5,#fff8e1);border-left:4px solid #f39c12;">
        <h3><i class="fas fa-crown" style="color:#f39c12;"></i> 冠军亮点</h3>
        <p style="font-size:14px;color:#555;margin-bottom:8px;">该足环号曾 <strong style="color:#c62828;font-size:20px;"><?php echo $stats['champion_count']; ?> 次</strong> 登顶冠军</p>
        <div style="display:flex;flex-wrap:wrap;gap:8px;">
            <?php
            $shown = 0;
            foreach ($results as $r):
                if (intval($r['rank'] ?? 0) !== 1) continue;
                if ($shown++ >= 3) break;
            ?>
            <span style="display:inline-flex;align-items:center;gap:4px;padding:4px 12px;background:#fff;border:1px solid #f0d78c;border-radius:20px;font-size:13px;">
                🥇 <?php echo htmlspecialchars(mb_strlen($r['race_name'] ?? '', 'UTF-8') > 12 ? mb_substr($r['race_name'], 0, 12, 'UTF-8') . '…' : ($r['race_name'] ?? '未知')); ?>
            </span>
            <?php endforeach; ?>
            <?php if ($stats['champion_count'] > 3): ?>
            <span style="display:inline-flex;align-items:center;padding:4px 12px;color:#999;font-size:13px;">+<?php echo $stats['champion_count'] - 3; ?> 场</span>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- 参赛履历预览（前5条） -->
    <div class="report-section" style="position:relative;overflow:hidden;">
        <h3><i class="fas fa-table"></i> 参赛履历预览
            <span style="font-size:13px;font-weight:400;color:#999;margin-left:8px;">显示前 5 场，共 <?php echo $stats['total_races']; ?> 场</span>
        </h3>
        <div style="overflow-x:auto;">
        <table class="report-table">
            <thead>
                <tr>
                    <th>日期</th>
                    <th>赛季</th>
                    <th>赛事</th>
                    <th>公棚</th>
                    <th>空距</th>
                    <th>名次</th>
                    <th>分速</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $previewCount = 0;
                foreach ($results as $r):
                    if ($previewCount++ >= 5) break;
                    $rank = intval($r['rank'] ?? 0);
                    $rowCls = '';
                    if ($rank == 1) $rowCls = ' champion';
                    elseif ($rank <= 3) $rowCls = ' podium';
                    $seasonLabel = ($r['season_type'] ?? '') == 'autumn' ? '秋' : (($r['season_type'] ?? '') == 'spring' ? '春' : (($r['season_type'] ?? '') == 'summer' ? '夏' : (($r['season_type'] ?? '') == 'winter' ? '冬' : '')));
                ?>
                <tr class="<?php echo $rowCls; ?>">
                    <td style="white-space:nowrap;"><?php echo ($r['release_time'] ?? '') ? date('Y-m-d', strtotime($r['release_time'])) : ($r['season_year'] ?? '—'); ?></td>
                    <td><?php echo $seasonLabel ?: '—'; ?></td>
                    <td><?php echo htmlspecialchars(mb_strlen($r['race_name'] ?? '', 'UTF-8') > 15 ? mb_substr($r['race_name'], 0, 15, 'UTF-8') . '…' : ($r['race_name'] ?? '—')); ?></td>
                    <td style="color:#666;"><?php echo htmlspecialchars($r['loft_name'] ?? '—'); ?></td>
                    <td class="dist-val"><?php echo ($r['distance'] ?? 0) > 0 ? number_format($r['distance']) . 'm' : '—'; ?></td>
                    <td class="rank-cell<?php echo $rank == 1 ? ' crown' : ''; ?>"><?php echo $rank > 0 ? $rank : '—'; ?></td>
                    <td class="speed-val"><?php echo ($r['speed'] ?? 0) > 0 ? number_format($r['speed'], 0) . ' m/分' : '—'; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>

        <?php if ($stats['total_races'] > 5): ?>
        <!-- 模糊遮罩 -->
        <div style="position:absolute;bottom:0;left:0;right:0;height:80px;background:linear-gradient(transparent, #fff);display:flex;align-items:flex-end;justify-content:center;padding-bottom:16px;">
            <span style="font-size:13px;color:#999;background:#fff;padding:6px 20px;border-radius:20px;box-shadow:0 2px 8px rgba(0,0,0,0.08);">
                <i class="fas fa-lock" style="margin-right:4px;"></i>还有 <?php echo $stats['total_races'] - 5; ?> 条记录需解锁
            </span>
        </div>
        <?php endif; ?>
    </div>

    <!-- 参赛公棚概览 -->
    <?php if (!empty($loftList)): ?>
    <div class="report-section">
        <h3><i class="fas fa-bullseye"></i> 参赛公棚概览
            <span style="font-size:13px;font-weight:400;color:#999;margin-left:8px;">共 <?php echo count($loftList); ?> 个</span>
        </h3>
        <div style="display:flex;flex-wrap:wrap;gap:8px;">
            <?php $loftPreview = array_slice($loftList, 0, 6);
            foreach ($loftPreview as $lid => $loft): ?>
            <span style="display:inline-block;padding:6px 14px;background:#f8f9fb;border:1px solid #e8ecf0;border-radius:6px;font-size:13px;color:#555;">
                <?php echo htmlspecialchars($loft['name']); ?>
                <span style="font-size:11px;color:#1a5fa8;margin-left:4px;"><?php echo $loft['count']; ?>场</span>
            </span>
            <?php endforeach; ?>
            <?php if (count($loftList) > 6): ?>
            <span style="display:inline-block;padding:6px 14px;font-size:13px;color:#999;">+<?php echo count($loftList) - 6; ?> 个</span>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- ===== 付费门控卡 ===== -->
    <div class="report-section" style="text-align:center;background:linear-gradient(135deg,#f8f9fb,#e8f2fc);border:1px dashed #1a5fa8;padding:40px 24px;">
        <div style="max-width:480px;margin:0 auto;">
            <div style="font-size:32px;margin-bottom:12px;">🔓</div>
            <h3 style="justify-content:center;font-size:20px;">解锁完整深度报告</h3>
            <p style="color:#888;font-size:14px;margin-bottom:6px;">分速趋势图 · 全部 <?php echo $stats['total_races']; ?> 场履历 · 同鸽主铭鸽推荐 · 赛季对比分析</p>

            <!-- 价格 -->
            <div style="margin:20px 0;padding:18px;background:#fff;border-radius:10px;border:1px solid #e8ecf0;">
                <div style="font-size:13px;color:#888;margin-bottom:4px;">解锁费用</div>
                <div style="font-size:42px;font-weight:900;color:#1a5fa8;line-height:1;">
                    <span style="font-size:22px;">¥</span>9<span style="font-size:16px;">.9</span>
                </div>
                <div style="font-size:12px;color:#999;margin-top:4px;">一次购买，永久可查</div>
            </div>

            <!-- 支付方式 -->
            <div style="display:flex;gap:10px;margin-bottom:20px;">
                <div class="paywall-pay-method" style="flex:1;padding:12px;border:2px solid #1a5fa8;border-radius:8px;cursor:pointer;text-align:center;background:#e8f2fc;" onclick="togglePayMethod(this)" data-method="wechat">
                    <i class="fab fa-weixin" style="font-size:20px;color:#07c160;"></i>
                    <span style="display:block;font-size:13px;margin-top:4px;font-weight:600;">微信支付</span>
                </div>
                <div class="paywall-pay-method" style="flex:1;padding:12px;border:2px solid #e0e0e0;border-radius:8px;cursor:pointer;text-align:center;" onclick="togglePayMethod(this)" data-method="alipay">
                    <i class="fab fa-alipay" style="font-size:20px;color:#1677ff;"></i>
                    <span style="display:block;font-size:13px;margin-top:4px;font-weight:600;">支付宝</span>
                </div>
            </div>

            <!-- 支付按钮（预留） -->
            <button id="payBtn" onclick="showQRModal('paywall')" style="width:100%;padding:14px;background:#1a5fa8;color:#fff;border:none;border-radius:8px;font-size:16px;font-weight:700;cursor:pointer;transition:opacity .2s;" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                <i class="fas fa-lock-open" style="margin-right:6px;"></i> 立即解锁
            </button>

            <p style="font-size:12px;color:#bbb;margin-top:16px;">遇到支付问题请<a href="/pages/contact/" style="color:#999;">联系我们</a></p>
        </div>
    </div>

    <?php endif; ?>

<?php endif; ?>
</div>

<?php include __DIR__ . '/_footer.php'; ?>
</div>

<!-- Chart.js 初始化 -->
<?php if (count($speedData) >= 2): ?>
<script>
(function() {
    var speedData = <?php echo json_encode($speedData, JSON_UNESCAPED_UNICODE); ?>;
    var labels = speedData.map(function(d) { return d.label; });
    var speeds = speedData.map(function(d) { return d.speed; });
    var ranks = speedData.map(function(d) { return d.rank; });
    var avgSpeed = <?php echo $stats['avg_speed']; ?>;

    var ctx = document.getElementById('speedChart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: '分速 (m/分)',
                data: speeds,
                borderColor: '#1a5fa8',
                backgroundColor: 'rgba(26,95,168,0.08)',
                borderWidth: 2,
                fill: true,
                tension: 0.3,
                pointRadius: speeds.map(function(s, i) { return ranks[i] === 1 ? 7 : (ranks[i] <= 3 ? 5 : 3); }),
                pointBackgroundColor: speeds.map(function(s, i) { return ranks[i] === 1 ? '#f39c12' : (ranks[i] <= 3 ? '#c9a84c' : '#1a5fa8'); }),
                pointBorderColor: speeds.map(function(s, i) { return ranks[i] === 1 ? '#e67e22' : '#fff'; }),
                pointBorderWidth: 1.5,
                pointHoverRadius: 8,
            }, {
                label: '平均分速',
                data: speeds.map(function() { return avgSpeed; }),
                borderColor: '#e0e0e0',
                borderWidth: 1.5,
                borderDash: [6, 4],
                pointRadius: 0,
                fill: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: { usePointStyle: true, padding: 20, font: { size: 12 } }
                },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            if (ctx.datasetIndex === 1) return '平均: ' + avgSpeed.toFixed(0) + ' m/分';
                            var r = ranks[ctx.dataIndex];
                            var suffix = r === 1 ? ' 🥇冠军' : (r <= 3 ? ' 🏅获奖' : '');
                            return '分速: ' + ctx.raw.toFixed(0) + ' m/分 — 第' + r + '名' + suffix;
                        }
                    }
                }
            },
            scales: {
                x: {
                    ticks: { maxRotation: 45, font: { size: 10 } },
                    grid: { display: false }
                },
                y: {
                    title: { display: true, text: '分速 (m/分)' },
                    beginAtZero: false,
                    ticks: { callback: function(v) { return v.toFixed(0); } }
                }
            },
            interaction: { intersect: false, mode: 'index' }
        }
    });
})();
</script>
<?php endif; ?>

<!-- 支付门控 JS（预留接口） -->
<script>
var payMethod = 'wechat';
function togglePayMethod(el) {
    payMethod = el.getAttribute('data-method');
    document.querySelectorAll('.paywall-pay-method').forEach(function(m) {
        m.style.borderColor = '#e0e0e0';
        m.style.background = '';
    });
    el.style.borderColor = '#1a5fa8';
    el.style.background = '#e8f2fc';
}
// ===== QR 支付弹窗 =====
var qrModalData = {};

function showQRModal(orderType) {
    <?php if (empty($_SESSION['user_id'])): ?>
    location.href = '/auth?action=login&redirect=<?php echo urlencode('/race/report/' . $ring . '/'); ?>';
    return;
    <?php endif; ?>

    qrModalData.orderType = orderType;
    qrModalData.orderNo = null;
    
    // 更新二维码显示
    var isWechat = payMethod === 'wechat';
    var qrIcon = document.getElementById('qrPayIcon');
    if (isWechat) {
        qrIcon.className = 'fab fa-weixin';
        qrIcon.parentElement.style.background = '#07c160';
        document.getElementById('qrPayTitle').textContent = '微信扫码支付';
        document.getElementById('qrPayHint').textContent = '请使用微信扫描二维码完成支付';
    } else {
        qrIcon.className = 'fab fa-alipay';
        qrIcon.parentElement.style.background = '#1677ff';
        document.getElementById('qrPayTitle').textContent = '支付宝扫码支付';
        document.getElementById('qrPayHint').textContent = '请使用支付宝扫描二维码完成支付';
    }
    
    // 显示弹窗
    document.getElementById('qrModal').style.display = 'flex';
    
    // 如果是首次解锁，先创建订单
    if (orderType === 'paywall') {
        createOrderThenShowQR();
    }
}

function createOrderThenShowQR() {
    var formData = new FormData();
    formData.append('product_type', 'report');
    formData.append('ring', <?php echo json_encode($ring); ?>);
    formData.append('pay_method', payMethod);
    
    var simBtn = document.getElementById('qrSimBtn');
    if (simBtn) { simBtn.textContent = '创建订单中…'; simBtn.disabled = true; }
    
    fetch('/pay/?action=create', { method: 'POST', body: formData })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            qrModalData.orderNo = data.order_no;
            if (simBtn) { simBtn.textContent = '模拟扫码完成支付'; simBtn.disabled = false; }
        } else {
            alert(data.message || '创建订单失败');
            closeQRModal();
        }
    })
    .catch(function(err) {
        alert('网络错误');
        closeQRModal();
    });
}

function simulatePayment() {
    var simBtn = document.getElementById('qrSimBtn');
    if (simBtn) { simBtn.disabled = true; simBtn.textContent = '支付处理中…'; }
    
    // paywall 路径：提交审核，等管理员审批后再支付解锁
    if (qrModalData.orderType === 'paywall') {
        if (simBtn) { simBtn.textContent = '订单已提交，等待审核…'; }
        setTimeout(function() { location.reload(); }, 1500);
        return;
    }
    
    // approved 路径：已有审批订单，直接完成支付解锁
    var formData = new FormData();
    formData.append('order_no', <?php echo json_encode($approvedOrder['order_no'] ?? ''); ?>);
    formData.append('pay_method', payMethod);
    
    fetch('/pay/?action=complete', { method: 'POST', body: formData })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            location.reload();
        } else {
            alert('支付失败：' + (data.message || '请重试'));
            if (simBtn) { simBtn.disabled = false; simBtn.textContent = '模拟扫码完成支付'; }
        }
    })
    .catch(function(err) {
        alert('网络错误，请重试');
        if (simBtn) { simBtn.disabled = false; simBtn.textContent = '模拟扫码完成支付'; }
    });
}

function closeQRModal() {
    document.getElementById('qrModal').style.display = 'none';
}
</script>

<!-- ===== QR 支付弹窗 ===== -->
<div id="qrModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);z-index:9999;justify-content:center;align-items:center;">
    <div style="background:#fff;border-radius:16px;padding:30px 24px 24px;max-width:380px;width:90%;text-align:center;box-shadow:0 12px 48px rgba(0,0,0,0.25);position:relative;">
        <button onclick="closeQRModal()" style="position:absolute;top:12px;right:16px;background:none;border:none;font-size:20px;color:#999;cursor:pointer;">&times;</button>
        <div id="qrPayTitle" style="font-size:20px;font-weight:700;color:#333;margin-bottom:8px;">微信扫码支付</div>
        <div id="qrPayHint" style="font-size:14px;color:#888;margin-bottom:20px;">请使用微信扫描二维码完成支付</div>
        
        <!-- 模拟二维码 -->
        <div style="width:200px;height:200px;margin:0 auto 20px;background:#fff;border:3px solid #e0e0e0;border-radius:12px;position:relative;overflow:hidden;">
            <div style="display:grid;grid-template-columns:repeat(10,1fr);grid-template-rows:repeat(10,1fr);gap:2px;padding:14px;width:100%;height:100%;box-sizing:border-box;opacity:0.35;">
                <div style="background:#333;"></div><div style="background:#333;"></div><div style="background:#fff;"></div><div style="background:#333;"></div><div style="background:#fff;"></div><div style="background:#333;"></div><div style="background:#333;"></div><div style="background:#fff;"></div><div style="background:#333;"></div><div style="background:#fff;"></div>
                <div style="background:#fff;"></div><div style="background:#fff;"></div><div style="background:#333;"></div><div style="background:#fff;"></div><div style="background:#333;"></div><div style="background:#fff;"></div><div style="background:#fff;"></div><div style="background:#333;"></div><div style="background:#fff;"></div><div style="background:#333;"></div>
                <div style="background:#333;"></div><div style="background:#fff;"></div><div style="background:#333;"></div><div style="background:#333;"></div><div style="background:#fff;"></div><div style="background:#333;"></div><div style="background:#fff;"></div><div style="background:#fff;"></div><div style="background:#333;"></div><div style="background:#fff;"></div>
                <div style="background:#fff;"></div><div style="background:#333;"></div><div style="background:#fff;"></div><div style="background:#fff;"></div><div style="background:#333;"></div><div style="background:#fff;"></div><div style="background:#333;"></div><div style="background:#fff;"></div><div style="background:#fff;"></div><div style="background:#333;"></div>
                <div style="background:#333;"></div><div style="background:#fff;"></div><div style="background:#333;"></div><div style="background:#333;"></div><div style="background:#fff;"></div><div style="background:#333;"></div><div style="background:#fff;"></div><div style="background:#333;"></div><div style="background:#333;"></div><div style="background:#fff;"></div>
                <div style="background:#fff;"></div><div style="background:#333;"></div><div style="background:#fff;"></div><div style="background:#fff;"></div><div style="background:#333;"></div><div style="background:#fff;"></div><div style="background:#333;"></div><div style="background:#fff;"></div><div style="background:#fff;"></div><div style="background:#333;"></div>
                <div style="background:#333;"></div><div style="background:#fff;"></div><div style="background:#333;"></div><div style="background:#333;"></div><div style="background:#fff;"></div><div style="background:#333;"></div><div style="background:#fff;"></div><div style="background:#333;"></div><div style="background:#333;"></div><div style="background:#fff;"></div>
                <div style="background:#fff;"></div><div style="background:#333;"></div><div style="background:#fff;"></div><div style="background:#fff;"></div><div style="background:#333;"></div><div style="background:#fff;"></div><div style="background:#333;"></div><div style="background:#fff;"></div><div style="background:#fff;"></div><div style="background:#333;"></div>
                <div style="background:#333;"></div><div style="background:#fff;"></div><div style="background:#fff;"></div><div style="background:#333;"></div><div style="background:#fff;"></div><div style="background:#333;"></div><div style="background:#fff;"></div><div style="background:#333;"></div><div style="background:#fff;"></div><div style="background:#fff;"></div>
                <div style="background:#fff;"></div><div style="background:#333;"></div><div style="background:#fff;"></div><div style="background:#fff;"></div><div style="background:#333;"></div><div style="background:#fff;"></div><div style="background:#333;"></div><div style="background:#fff;"></div><div style="background:#333;"></div><div style="background:#333;"></div>
            </div>
            <!-- 中心支付图标 -->
            <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:50px;height:50px;background:#07c160;border-radius:12px;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(0,0,0,0.2);">
                <i id="qrPayIcon" class="fab fa-weixin" style="font-size:26px;color:#fff;"></i>
            </div>
        </div>
        
        <!-- 价格 -->
        <div style="margin:16px 0;padding:12px;background:#f0fdf4;border-radius:8px;display:flex;align-items:center;justify-content:center;gap:8px;">
            <span style="font-size:14px;color:#666;">支付金额</span>
            <span style="font-size:24px;font-weight:900;color:#16a34a;">¥9.9</span>
        </div>
        
        <p style="font-size:12px;color:#999;margin-bottom:16px;">⚠️ 沙箱模式 — 不会产生真实扣费</p>
        
        <button id="qrSimBtn" onclick="simulatePayment()" style="width:100%;padding:12px;background:#1a5fa8;color:#fff;border:none;border-radius:8px;font-size:15px;font-weight:600;cursor:pointer;transition:opacity .2s;" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
            <i class="fas fa-check-circle" style="margin-right:6px;"></i> 模拟扫码完成支付
        </button>
        
        <button onclick="closeQRModal()" style="width:100%;margin-top:8px;padding:10px;background:none;border:1px solid #e0e0e0;border-radius:8px;font-size:13px;color:#888;cursor:pointer;">取消</button>
    </div>
</div>
</body>
</html>
