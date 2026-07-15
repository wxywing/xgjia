<?php
/**
 * 鸽主专辑页 — 战绩聚合
 */
$owner_encoded = htmlspecialchars($ownerName);
$raceCount = $stats['race_count'] ?? 0;
$bestRank = $stats['best_rank'] ?? null;
$championCount = $stats['champion_count'] ?? 0;
$podiumCount = $stats['podium_count'] ?? 0;
$top10Count = $stats['top10_count'] ?? 0;
$loftCount = $stats['loft_count'] ?? 0;
$bestSpeed = $stats['best_speed'] ?? 0;

$page_title = $ownerName . ' 赛绩专辑 | 赛事成绩 | 信鸽之家';
$page_desc = '鸽主 ' . $ownerName . ' 历年参赛记录：共 ' . $raceCount . ' 场赛事 ';
if ($championCount > 0) {
    $page_desc .= $championCount . ' 次冠军，';
}
if ($podiumCount > 0) {
    $page_desc .= $podiumCount . ' 次前三，';
}
$page_desc .= '最高分速 ' . number_format($bestSpeed, 0) . 'm/分。';
$page_url = 'https://www.xgjia.com/page/owner/' . urlencode($ownerName) . '/';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo $page_title; ?></title>
    <meta name="description" content="<?php echo $page_desc; ?>">
    <meta name="keywords" content="鸽主,<?php echo $ownerName; ?>,赛绩,成绩,信鸽之家">
    <meta property="og:title" content="<?php echo $page_title; ?>">
    <meta property="og:type" content="article">
    <meta property="og:description" content="<?php echo $page_desc; ?>">
    <meta property="og:image" content="https://www.xgjia.com/public/images/og-cover.png">
    <link rel="canonical" href="<?php echo $page_url; ?>">
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/public/css/b-scheme.css">
    <link rel="shortcut icon" href="/public/images/favicon.ico">
    <style>
.owner-wrap { background: #f4f6f9; }
.owner-hero {
    background: linear-gradient(135deg, #1a5fa8 0%, #0d3b6e 50%, #0a2d52 100%);
    color: #fff; padding: 40px 0;
}
.owner-hero .breadcrumb { font-size: 13px; opacity: 0.7; margin-bottom: 12px; }
.owner-hero .breadcrumb a { color: rgba(255,255,255,0.8); text-decoration: none; }
.owner-hero .breadcrumb a:hover { text-decoration: underline; }
.owner-avatar-row { display: flex; align-items: center; gap: 16px; margin-bottom: 16px; }
.owner-avatar-icon { width: 64px; height: 64px; border-radius: 50%; background: rgba(255,255,255,0.15); display: flex; align-items: center; justify-content: center; font-size: 32px; }
.owner-hero h1 { font-size: 30px; font-weight: 700; margin: 0; }
.owner-hero .subtitle { font-size: 14px; opacity: 0.8; margin-top: 2px; }
.owner-stats-row { display: grid; grid-template-columns: repeat(6, 1fr); gap: 12px; margin-top: 24px; }
.owner-stat { background: rgba(255,255,255,0.12); border-radius: 10px; padding: 14px 10px; text-align: center; }
.owner-stat .val { font-size: 22px; font-weight: 700; }
.owner-stat .lbl { font-size: 11px; opacity: 0.7; margin-top: 4px; }

.owner-content { padding: 32px 0; }
.owner-grid { display: grid; grid-template-columns: 1fr 360px; gap: 24px; }

.top-section { background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); margin-bottom: 20px; }
.top-section h2 { font-size: 18px; font-weight: 700; color: #2c3e50; margin-bottom: 14px; display: flex; align-items: center; gap: 8px; }
.top-section h2 i { color: #c9a84c; }
.top-results { display: flex; flex-direction: column; gap: 8px; }
.top-result-item { display: flex; align-items: center; gap: 12px; padding: 12px; background: #f8f9fa; border-radius: 8px; }
.top-result-medal { font-size: 24px; flex-shrink: 0; }
.top-result-info { flex: 1; min-width: 0; }
.top-result-race { font-size: 14px; font-weight: 600; color: #2c3e50; }
.top-result-meta { font-size: 12px; color: #888; margin-top: 2px; }
.top-result-speed { font-size: 14px; font-weight: 700; color: #e65100; white-space: nowrap; }

.results-section { background: #fff; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); overflow: hidden; }
.results-section h2 { font-size: 18px; font-weight: 700; color: #2c3e50; padding: 20px; border-bottom: 1px solid #f0f0f0; }
.results-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.results-table th { background: #f8f9fa; color: #666; font-weight: 600; padding: 10px 12px; text-align: left; border-bottom: 2px solid #e0e0e0; white-space: nowrap; }
.results-table td { padding: 10px 12px; border-bottom: 1px solid #f0f0f0; }
.results-table tr:hover td { background: #f4f8ff; }
.results-table .badge-rank { display: inline-flex; align-items: center; justify-content: center; min-width: 28px; height: 24px; border-radius: 4px; font-size: 12px; font-weight: 700; padding: 0 6px; }
.badge-r1 { background: #c62828; color: #fff; }
.badge-r2 { background: #455a64; color: #fff; }
.badge-r3 { background: #e65100; color: #fff; }
.badge-rx { background: #e8ecf0; color: #666; }

.sidebar-card { background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); margin-bottom: 20px; }
.sidebar-card h3 { font-size: 15px; font-weight: 700; color: #2c3e50; margin-bottom: 14px; display: flex; align-items: center; gap: 8px; padding-bottom: 10px; border-bottom: 2px solid #f4f6f9; }
.sidebar-card h3 i { color: #c9a84c; }
.sidebar-stat-row { display: flex; justify-content: space-between; padding: 8px 0; font-size: 13px; color: #666; border-bottom: 1px solid #f5f5f5; }
.sidebar-stat-row:last-child { border-bottom: none; }
.sidebar-stat-row strong { color: #1a5fa8; }
.season-list { display: flex; flex-direction: column; gap: 6px; }
.season-tag { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; background: #f4f6f9; border-radius: 6px; font-size: 13px; color: #555; }
.season-tag i { font-size: 10px; color: #1a5fa8; }
.season-tag .cnt { margin-left: auto; font-weight: 600; color: #1a5fa8; }

.pagination { display: flex; gap: 6px; justify-content: center; padding: 20px; flex-wrap: wrap; align-items: center; }
.pagination .page-link { padding: 6px 14px; border: 1px solid #ddd; border-radius: 6px; color: #555; text-decoration: none; font-size: 13px; }
.pagination .page-link:hover { background: #f4f6f9; }
.pagination .page-link.active { background: #1a5fa8; color: #fff; border-color: #1a5fa8; }
.pagination .page-ellipsis { padding: 6px 8px; color: #999; font-size: 13px; }

.empty-state { text-align: center; padding: 60px 20px; color: #999; }
.empty-state i { font-size: 48px; color: #ddd; display: block; margin-bottom: 12px; }

@media (max-width: 768px) {
    .owner-grid { grid-template-columns: 1fr; }
    .owner-stats-row { grid-template-columns: repeat(3, 1fr); }
    .owner-hero h1 { font-size: 22px; }
    .results-table { font-size: 12px; }
}
    </style>
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "BreadcrumbList",
        "itemListElement": [
            {"@type": "ListItem", "position": 1, "name": "首页", "item": "https://www.xgjia.com"},
            {"@type": "ListItem", "position": 2, "name": "赛事成绩", "item": "https://www.xgjia.com/race/"},
            {"@type": "ListItem", "position": 3, "name": "鸽主 <?php echo $owner_encoded; ?>"}
        ]
    }
    </script>
    <!-- Person Schema for GEO -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Person",
        "name": "<?php echo $owner_encoded; ?>",
        "description": "信鸽鸽主，共参与 <?php echo $raceCount; ?> 场赛事<?php echo $championCount > 0 ? '，' . $championCount . '次冠军' : ''; ?>",
        "url": "<?php echo $page_url; ?>",
        "knowsAbout": "信鸽竞赛"
    }
    </script>
</head>
<body>
<div class="owner-wrap">
<?php include __DIR__ . '/_head.php'; ?>

<div class="owner-hero">
    <div class="container">
        <div class="breadcrumb">
            <a href="/race/">赛事成绩</a> › 鸽主专辑
        </div>
        <div class="owner-avatar-row">
            <div class="owner-avatar-icon">
                <i class="fas fa-user"></i>
            </div>
            <div>
                <h1><?php echo $owner_encoded; ?></h1>
                <div class="subtitle">信鸽鸽主 · <?php echo $raceCount; ?> 场赛事记录</div>
            </div>
        </div>
        <div class="owner-stats-row">
            <div class="owner-stat">
                <div class="val"><?php echo $championCount; ?></div>
                <div class="lbl">🏆 冠军</div>
            </div>
            <div class="owner-stat">
                <div class="val"><?php echo $podiumCount; ?></div>
                <div class="lbl">🥇 前三名</div>
            </div>
            <div class="owner-stat">
                <div class="val"><?php echo $top10Count; ?></div>
                <div class="lbl">📊 前十名</div>
            </div>
            <div class="owner-stat">
                <div class="val"><?php echo $raceCount; ?></div>
                <div class="lbl">🏁 参赛场次</div>
            </div>
            <div class="owner-stat">
                <div class="val"><?php echo $loftCount; ?></div>
                <div class="lbl">🏢 参战公棚</div>
            </div>
            <div class="owner-stat">
                <div class="val"><?php echo $bestSpeed > 0 ? number_format($bestSpeed, 0) : '—'; ?></div>
                <div class="lbl">⚡ 最高分速</div>
            </div>
        </div>
    </div>
</div>

<div class="container owner-content">
    <div class="owner-grid">
        <!-- 左栏：成绩列表 -->
        <div>
            <?php if (!empty($topResults)): ?>
            <div class="top-section">
                <h2><i class="fas fa-trophy"></i> 高光时刻</h2>
                <div class="top-results">
                    <?php foreach (array_slice($topResults, 0, 5) as $tr): ?>
                    <div class="top-result-item">
                        <div class="top-result-medal">
                            <?php
                            $r = intval($tr['rank'] ?? 0);
                            echo $r == 1 ? '🥇' : ($r == 2 ? '🥈' : '🥉');
                            ?>
                        </div>
                        <div class="top-result-info">
                            <div class="top-result-race">
                                <a href="/race/<?php echo intval($tr['race_id'] ?? 0); ?>.html" style="color:#1a5fa8;text-decoration:none;">
                                    <?php echo htmlspecialchars($tr['race_name'] ?? '未知赛事'); ?>
                                </a>
                            </div>
                            <div class="top-result-meta">
                                <?php echo htmlspecialchars($tr['loft_name'] ?? ''); ?>
                                · <?php echo $tr['season_year'] ?? ''; ?>
                                · 第<?php echo $tr['rank']; ?>名
                            </div>
                        </div>
                        <div class="top-result-speed">
                            <?php echo ($tr['speed'] ?? 0) > 0 ? number_format($tr['speed'], 2) . ' m/min' : '—'; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="results-section">
                <h2>📋 全部参赛记录（<?php echo number_format($total); ?> 条）</h2>
                <?php if (!empty($results['list'])): ?>
                <div style="overflow-x:auto;">
                <table class="results-table">
                    <thead>
                        <tr>
                            <th>名次</th>
                            <th>赛事</th>
                            <th>公棚</th>
                            <th>足环号</th>
                            <th>赛季</th>
                            <th>分速</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results['list'] as $r):
                            $rank = intval($r['rank'] ?? 0);
                            $bCls = $rank == 1 ? 'badge-r1' : ($rank == 2 ? 'badge-r2' : ($rank == 3 ? 'badge-r3' : 'badge-rx'));
                            $bLabel = $rank > 0 ? $rank : '—';
                        ?>
                        <tr>
                            <td><span class="badge-rank <?php echo $bCls; ?>"><?php echo $bLabel; ?></span></td>
                            <td>
                                <a href="/race/<?php echo intval($r['race_id'] ?? 0); ?>.html" style="color:#1a5fa8;text-decoration:none;font-weight:500;">
                                    <?php echo htmlspecialchars($r['race_name'] ?? '—'); ?>
                                </a>
                            </td>
                            <td>
                                <a href="/loft/<?php echo intval($r['loft_id'] ?? 0); ?>.html" style="color:#555;">
                                    <?php echo htmlspecialchars($r['loft_name'] ?? '—'); ?>
                                </a>
                            </td>
                            <td>
                                <?php $rn = $r['ring_number'] ?? ''; ?>
                                <?php if ($rn): ?>
                                <a href="/race/ring/<?php echo urlencode($rn); ?>/" style="color:#1a5fa8;"><?php echo htmlspecialchars($rn); ?></a>
                                <?php else: ?>
                                <span style="color:#ccc;">—</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo ($r['season_year'] ?? '') . ' ' . (($r['season_type'] ?? '') == 'autumn' ? '秋' : (($r['season_type'] ?? '') == 'spring' ? '春' : '')); ?></td>
                            <td style="font-weight:600;<?php echo ($r['speed'] ?? 0) > 1200 ? 'color:#2e7d32;' : ''; ?>">
                                <?php echo ($r['speed'] ?? 0) > 0 ? number_format($r['speed'], 2) : '—'; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
                <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php
                    $urlBase = '/page/owner/' . urlencode($ownerName) . '/?page=';
                    $maxVisible = 5; // 当前页左右各显示几个
                    
                    // 上一页
                    if ($page > 1): ?>
                    <a class="page-link" href="<?php echo $urlBase . ($page - 1); ?>">‹ 上一页</a>
                    <?php endif; ?>
                    
                    <?php // 第一页
                    if ($page > $maxVisible + 1): ?>
                    <a class="page-link" href="<?php echo $urlBase . '1'; ?>">1</a>
                    <span class="page-ellipsis">…</span>
                    <?php endif; ?>
                    
                    <?php // 中间页码
                    $start = max(1, $page - $maxVisible);
                    $end = min($totalPages, $page + $maxVisible);
                    for ($i = $start; $i <= $end; $i++): ?>
                    <a class="page-link<?php echo $i == $page ? ' active' : ''; ?>"
                       href="<?php echo $urlBase . $i; ?>">
                       <?php echo $i; ?>
                    </a>
                    <?php endfor; ?>
                    
                    <?php // 最后一页
                    if ($page < $totalPages - $maxVisible): ?>
                    <span class="page-ellipsis">…</span>
                    <a class="page-link" href="<?php echo $urlBase . $totalPages; ?>"><?php echo $totalPages; ?></a>
                    <?php endif; ?>
                    
                    <?php // 下一页
                    if ($page < $totalPages): ?>
                    <a class="page-link" href="<?php echo $urlBase . ($page + 1); ?>">下一页 ›</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-user-slash"></i>
                    <p>未找到该鸽主的成绩记录</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- 右栏：统计 -->
        <div>
            <div class="sidebar-card">
                <h3><i class="fas fa-chart-bar"></i> 战绩总览</h3>
                <div class="sidebar-stat-row">
                    <span>参赛场次</span><strong><?php echo $raceCount; ?> 场</strong>
                </div>
                <div class="sidebar-stat-row">
                    <span>参战公棚</span><strong><?php echo $loftCount; ?> 个</strong>
                </div>
                <div class="sidebar-stat-row">
                    <span>冠军次数</span><strong><?php echo $championCount; ?> 次</strong>
                </div>
                <div class="sidebar-stat-row">
                    <span>前三次数</span><strong><?php echo $podiumCount; ?> 次</strong>
                </div>
                <div class="sidebar-stat-row">
                    <span>前十次数</span><strong><?php echo $top10Count; ?> 次</strong>
                </div>
                <div class="sidebar-stat-row">
                    <span>最佳名次</span><strong><?php echo $bestRank ? '第 ' . $bestRank . ' 名' : '—'; ?></strong>
                </div>
                <div class="sidebar-stat-row">
                    <span>最高分速</span><strong><?php echo $bestSpeed > 0 ? number_format($bestSpeed, 2) . ' m/min' : '—'; ?></strong>
                </div>
            </div>

            <?php if (!empty($seasonGroups)): ?>
            <div class="sidebar-card">
                <h3><i class="fas fa-calendar-alt"></i> 赛季分布</h3>
                <div class="season-list">
                    <?php foreach ($seasonGroups as $seasonKey => $records): ?>
                    <div class="season-tag">
                        <i class="fas fa-chevron-right"></i>
                        <?php echo htmlspecialchars($seasonKey); ?> 赛季
                        <span class="cnt"><?php echo count($records); ?> 场</span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="sidebar-card">
                <h3><i class="fas fa-link"></i> 快捷操作</h3>
                <div style="display:flex;flex-direction:column;gap:8px;">
                    <a href="/race/?q=<?php echo urlencode($ownerName); ?>" class="action-btn" style="justify-content:flex-start;">
                        <i class="fas fa-search"></i> 搜索此鸽主赛事
                    </a>
                    <a href="/pigeon/?q=<?php echo urlencode($ownerName); ?>" class="action-btn" style="justify-content:flex-start;">
                        <i class="fas fa-dove"></i> 搜索铭鸽
                    </a>
                </div>
                <style>
                .action-btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; border-radius: 8px; font-size: 13px; cursor: pointer; border: 1px solid #e8ecf0; background: #fff; color: #666; text-decoration: none; transition: all 0.2s; }
                .action-btn:hover { background: #1a5fa8; color: #fff; border-color: #1a5fa8; }
                </style>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/_footer.php'; ?>
</div>
</body>
</html>
