<?php
/**
 * 足环号时间线 — 单羽信鸽跨赛季/跨公棚成绩追踪
 */
$ring_encoded = htmlspecialchars($ring);
$page_title = '足环号 ' . $ring . ' 赛绩时间线 | 赛事成绩 | 信鸽之家';
$page_desc = '足环号 ' . $ring . ' 跨公棚跨赛季赛绩追踪，共参与 ' . $stats['total_races'] . ' 场比赛';
if ($stats['best_rank']) {
    $page_desc .= '，最佳名次第 ' . $stats['best_rank'] . ' 名';
}
if ($stats['best_speed'] > 0) {
    $page_desc .= '，最高分速 ' . number_format($stats['best_speed'], 2) . 'm/分';
}
$page_desc .= '。';
$page_url = 'https://www.xgjia.com/race/ring/' . urlencode($ring) . '/';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo $page_title; ?></title>
    <meta name="description" content="<?php echo $page_desc; ?>">
    <meta name="keywords" content="<?php echo $ring; ?>,足环号查询,信鸽成绩,赛鸽追踪,信鸽之家">
    <meta property="og:title" content="<?php echo $page_title; ?>">
    <meta property="og:type" content="article">
    <meta property="og:description" content="<?php echo $page_desc; ?>">
    <meta property="og:image" content="https://www.xgjia.com/public/images/og-cover.png">
    <link rel="canonical" href="<?php echo $page_url; ?>">
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/public/css/b-scheme.css">
    <link rel="shortcut icon" href="/public/images/favicon.ico">
    <style>
.page-ring-wrap { background: #f4f6f9; }
.ring-hero {
    background: linear-gradient(135deg, #1a5fa8 0%, #0d3b6e 50%, #0a2d52 100%);
    color: #fff; padding: 40px 0;
}
.ring-hero .breadcrumb { font-size: 13px; opacity: 0.7; margin-bottom: 12px; }
.ring-hero .breadcrumb a { color: rgba(255,255,255,0.8); text-decoration: none; }
.ring-hero .breadcrumb a:hover { text-decoration: underline; }
.ring-hero h1 { font-size: 28px; font-weight: 700; margin-bottom: 6px; }
.ring-hero .subtitle { font-size: 14px; opacity: 0.8; margin-bottom: 20px; }
.ring-stats-row { display: grid; grid-template-columns: repeat(5, 1fr); gap: 12px; }
.ring-stat { background: rgba(255,255,255,0.12); border-radius: 10px; padding: 14px 10px; text-align: center; }
.ring-stat .val { font-size: 22px; font-weight: 700; }
.ring-stat .lbl { font-size: 11px; opacity: 0.7; margin-top: 4px; }

.ring-search-bar { background: #fff; padding: 16px 0; border-bottom: 1px solid #e8ecf0; }
.ring-search-inner { display: flex; gap: 0; max-width: 600px; }
.ring-search-inner input { flex: 1; padding: 12px 18px; border: 2px solid #dde1e6; border-right: none; border-radius: 8px 0 0 8px; font-size: 15px; outline: none; }
.ring-search-inner input:focus { border-color: #1a5fa8; }
.ring-search-inner button { padding: 12px 28px; background: #1a5fa8; color: #fff; border: none; border-radius: 0 8px 8px 0; font-size: 15px; font-weight: 600; cursor: pointer; white-space: nowrap; }
.ring-search-inner button:hover { background: #15508c; }

.ring-timeline { padding: 32px 0; }
.timeline-stat-wrap { margin-bottom: 28px; }
.timeline-grid { position: relative; }
.timeline-line { position: absolute; left: 16px; top: 0; bottom: 0; width: 3px; background: linear-gradient(to bottom, #1a5fa8, #c9a84c); border-radius: 2px; }

.timeline-card { margin-left: 48px; margin-bottom: 20px; background: #fff; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); overflow: hidden; position: relative; }
.timeline-card::before {
    content: ''; position: absolute; left: -38px; top: 20px; width: 14px; height: 14px; border-radius: 50%;
    background: #1a5fa8; border: 3px solid #c9a84c; z-index: 2;
}
.timeline-card.podium::before { background: #c9a84c; border-color: #e0c060; }
.timeline-card.champion::before { background: #c62828; border-color: #f39c12; box-shadow: 0 0 8px rgba(198,40,40,0.4); }

.timeline-card-header { padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f0f0f0; flex-wrap: wrap; gap: 8px; }
.timeline-loft { font-size: 15px; font-weight: 700; color: #1a5fa8; }
.timeline-race { font-size: 13px; color: #666; }
.timeline-date { font-size: 12px; color: #999; white-space: nowrap; }
.timeline-card-body { padding: 16px 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.timeline-info-item { display: flex; align-items: center; gap: 8px; font-size: 14px; color: #555; }
.timeline-info-item i { color: #c9a84c; width: 16px; text-align: center; }
.timeline-info-item strong { color: #2c3e50; }
.timeline-rank { display: inline-flex; align-items: center; gap: 6px; }
.rank-chip { display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 50%; font-weight: 700; font-size: 13px; }
.rank-chip-r1 { background: #c62828; color: #fff; }
.rank-chip-r2 { background: #455a64; color: #fff; }
.rank-chip-r3 { background: #e65100; color: #fff; }
.rank-chip-other { background: #e8ecf0; color: #666; }
.timeline-empty { text-align: center; padding: 60px 20px; color: #999; }
.timeline-empty i { font-size: 48px; color: #ddd; display: block; margin-bottom: 12px; }

@media (max-width: 768px) {
    .ring-stats-row { grid-template-columns: repeat(3, 1fr); }
    .ring-hero h1 { font-size: 22px; }
    .timeline-card-body { grid-template-columns: 1fr; }
    .timeline-card { margin-left: 36px; }
    .timeline-card::before { left: -28px; width: 12px; height: 12px; }
    .timeline-line { left: 11px; }
}
    </style>
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "BreadcrumbList",
        "itemListElement": [
            {"@type": "ListItem", "position": 1, "name": "首页", "item": "https://www.xgjia.com"},
            {"@type": "ListItem", "position": 2, "name": "赛事成绩", "item": "https://www.xgjia.com/race/"},
            {"@type": "ListItem", "position": 3, "name": "足环号 <?php echo $ring_encoded; ?>"}
        ]
    }
    </script>
</head>
<body>
<div class="page-ring-wrap">
<?php include __DIR__ . '/_head.php'; ?>

<div class="ring-hero">
    <div class="container">
        <div class="breadcrumb">
            <a href="/race/">赛事成绩</a> › 足环号查询
        </div>
        <h1>🏷️ 足环号 <?php echo $ring_encoded; ?> 赛绩追踪</h1>
        <p class="subtitle">跨公棚 · 跨赛季 · 全量成绩时间线</p>
        <div class="ring-stats-row">
            <div class="ring-stat">
                <div class="val"><?php echo $stats['total_races']; ?></div>
                <div class="lbl">参赛场次</div>
            </div>
            <div class="ring-stat">
                <div class="val"><?php echo $stats['total_lofts']; ?></div>
                <div class="lbl">参战公棚</div>
            </div>
            <div class="ring-stat">
                <div class="val"><?php echo $stats['podium_count']; ?></div>
                <div class="lbl">获奖次数</div>
            </div>
            <div class="ring-stat">
                <div class="val"><?php echo $stats['best_rank'] ? '第' . $stats['best_rank'] . '名' : '—'; ?></div>
                <div class="lbl">最佳名次</div>
            </div>
            <div class="ring-stat">
                <div class="val"><?php echo $stats['best_speed'] > 0 ? number_format($stats['best_speed'], 0) : '—'; ?></div>
                <div class="lbl">最高分速(m/min)</div>
            </div>
        </div>
        <a href="/race/report/<?php echo urlencode($ring); ?>/" style="display:inline-flex;align-items:center;gap:6px;margin-top:18px;padding:10px 24px;background:rgba(255,255,255,0.2);color:#fff;border-radius:8px;text-decoration:none;font-weight:600;font-size:14px;transition:all .2s;" onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'">📊 查看深度报告 →</a>
    </div>
</div>

<div class="ring-search-bar">
    <div class="container">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:10px;">
            <a href="/race/ring/<?php echo urlencode($ring); ?>/" style="font-size:13px;font-weight:700;color:#1a5fa8;text-decoration:none;border-bottom:3px solid #1a5fa8;padding-bottom:4px;">⏱ 时间线</a>
            <a href="/race/report/<?php echo urlencode($ring); ?>/" style="font-size:13px;color:#999;text-decoration:none;padding-bottom:4px;transition:color .2s;" onmouseover="this.style.color='#1a5fa8'">📊 深度报告</a>
        </div>
        <form class="ring-search-inner" action="/race/ring/" method="get" onsubmit="
            var v = document.getElementById('ringInput').value.trim().replace(/\/+$/g,'');
            if (!v) return false;
            window.location.href = '/race/ring/' + encodeURIComponent(v) + '/';
            return false;
        ">
            <input type="text" id="ringInput" placeholder="输入足环号查询，如 CHN2025-03-1234567" value="<?php echo $ring_encoded; ?>">
            <button type="submit"><i class="fas fa-search"></i> 查足环</button>
        </form>
    </div>
</div>

<div class="container ring-timeline">
    <?php if (!empty($results)): ?>
    <div class="timeline-stat-wrap" style="font-size:14px;color:#888;margin-bottom:20px;">
        <i class="fas fa-list-ol"></i> 共追踪到 <strong style="color:#1a5fa8;"><?php echo count($results); ?></strong> 场比赛记录
    </div>
    <div class="timeline-grid">
        <div class="timeline-line"></div>
        <?php foreach ($results as $r):
            $rank = intval($r['rank'] ?? 0);
            $cardCls = '';
            if ($rank == 1) $cardCls = ' champion';
            elseif ($rank <= 3) $cardCls = ' podium';
            $rankChip = '';
            if ($rank == 1) $rankChip = ' rank-chip-r1';
            elseif ($rank == 2) $rankChip = ' rank-chip-r2';
            elseif ($rank == 3) $rankChip = ' rank-chip-r3';
            elseif ($rank > 0) $rankChip = ' rank-chip-other';
            $speedVal = $r['speed'] ?? 0;
            $arrivalTime = $r['arrival_time'] ?? '';
            $seasonLabel = ($r['season_type'] ?? '') == 'autumn' ? '秋赛' : (($r['season_type'] ?? '') == 'spring' ? '春赛' : '');
        ?>
        <div class="timeline-card<?php echo $cardCls; ?>">
            <div class="timeline-card-header">
                <div>
                    <a href="/loft/<?php echo intval($r['loft_id'] ?? 0); ?>.html" class="timeline-loft">
                        <?php echo htmlspecialchars($r['loft_name'] ?? '未知公棚'); ?>
                    </a>
                    <span style="color:#999;margin:0 6px;">·</span>
                    <a href="/race/<?php echo intval($r['race_id'] ?? 0); ?>.html" class="timeline-race">
                        <?php echo htmlspecialchars($r['race_name'] ?? '未知赛事'); ?>
                    </a>
                </div>
                <span class="timeline-date">
                    <?php echo $seasonLabel ? $seasonLabel . ' ' : ''; ?><?php echo $r['season_year'] ?? ''; ?>
                    <?php if (!empty($r['release_time'])): ?>
                    · <?php echo date('m-d', strtotime($r['release_time'])); ?>
                    <?php endif; ?>
                </span>
            </div>
            <div class="timeline-card-body">
                <div class="timeline-info-item">
                    <i class="fas fa-trophy"></i>
                    <span>名次：</span>
                    <span class="timeline-rank">
                        <?php if ($rank > 0): ?>
                        <span class="rank-chip<?php echo $rankChip; ?>"><?php echo $rank; ?></span>
                        <?php if ($rank == 1): ?><span style="color:#c62828;">🥇冠军</span>
                        <?php elseif ($rank == 2): ?><span style="color:#455a64;">🥈亚军</span>
                        <?php elseif ($rank == 3): ?><span style="color:#e65100;">🥉季军</span>
                        <?php endif; ?>
                        <?php else: ?>
                        <span style="color:#999;">—</span>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="timeline-info-item">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>分速：<strong><?php echo $speedVal > 0 ? number_format($speedVal, 2) . ' m/min' : '—'; ?></strong></span>
                </div>
                <div class="timeline-info-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>鸽主：<strong><?php echo htmlspecialchars($r['owner_name'] ?? '—'); ?></strong></span>
                </div>
                <div class="timeline-info-item">
                    <i class="fas fa-clock"></i>
                    <span>归巢：<strong><?php echo $arrivalTime ? htmlspecialchars($arrivalTime) : '—'; ?></strong></span>
                </div>
                <div class="timeline-info-item">
                    <i class="fas fa-globe"></i>
                    <span>地区：<?php echo htmlspecialchars($r['region'] ?? '—'); ?></span>
                </div>
                <div class="timeline-info-item">
                    <i class="fas fa-palette"></i>
                    <span>羽色：<?php echo htmlspecialchars($r['color'] ?? '—'); ?></span>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="timeline-empty">
        <i class="fas fa-search"></i>
        <p style="font-size:16px;margin-bottom:8px;">未找到该足环号的成绩记录</p>
        <p style="font-size:13px;">请检查足环号是否正确，支持格式如 CHN2025-03-1234567</p>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/_footer.php'; ?>
</div>
</body>
</html>
