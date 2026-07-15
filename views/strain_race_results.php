<?php
/**
 * 信鸽之家 - 品系赛事成绩独立页（P2 SEO）
 * URL: /pedigree/strain/{name}/race-results
 */

require_once dirname(__DIR__) . '/app/config/config.php';

extract($data);

$strainName = h($strain['name']);
$totalResults = $total ?? 0;
$page_title = $pageTitle ?? ($strainName . ' - 赛事成绩 | ' . SITE_NAME);

// SEO 元信息
$statsDesc = '';
if (!empty($raceStats)) {
    $parts = [];
    if (($raceStats['total_results'] ?? 0) > 0) $parts[] = '共' . number_format($raceStats['total_results']) . '条比赛成绩';
    if (($raceStats['pigeon_count'] ?? 0) > 0) $parts[] = number_format($raceStats['pigeon_count']) . '羽参赛铭鸽';
    if (($raceStats['champion_count'] ?? 0) > 0) $parts[] = number_format($raceStats['champion_count']) . '次冠军';
    $statsDesc = implode('，', $parts);
}
$meta_description = $strainName . '品系赛事成绩查询。' . ($statsDesc ?: '查看' . $strainName . '品系铭鸽在各地赛事的比赛成绩、分速、排名。') . ' | ' . SITE_NAME;
$meta_keywords = $strainName . ',品系成绩,信鸽比赛成绩,血统品系排名,' . SITE_KEYWORDS;
$canonical_url = 'https://www.xgjia.com/pedigree/strain/' . urlencode($strain['name']) . '/race-results/';

// JSON-LD
$ld_collection = [
    '@context' => 'https://schema.org',
    '@type' => 'CollectionPage',
    'name' => $strainName . ' 品系赛事成绩',
    'description' => $meta_description,
    'url' => $canonical_url,
    'mainEntity' => [
        '@type' => 'ItemList',
        'numberOfItems' => $totalResults,
    ],
];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo h($page_title); ?></title>

    <meta name="description" content="<?php echo h($meta_description); ?>">
    <meta name="keywords" content="<?php echo h($meta_keywords); ?>">
    <link rel="canonical" href="<?php echo h($canonical_url); ?>">

    <meta property="og:type" content="website">
    <meta property="og:title" content="<?php echo h($page_title); ?>">
    <meta property="og:image" content="https://www.xgjia.com/public/images/og-cover.png">
    <meta property="og:description" content="<?php echo h($meta_description); ?>">
    <meta property="og:url" content="<?php echo h($canonical_url); ?>">
    <meta property="og:site_name" content="<?php echo SITE_NAME; ?>">

    <script type="application/ld+json"><?php echo json_encode($ld_collection, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>

    <link rel="stylesheet" href="/public/css/style.css?v=3.0">
    <link rel="stylesheet" href="/public/css/b-scheme.css?v=3.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<?php include __DIR__ . '/_head.php'; ?>

<style>
.strain-race-page { max-width: 1200px; margin: 0 auto; padding: 20px 16px 40px; }
.strain-race-header { background: linear-gradient(135deg, #1a5fa8 0%, #2563eb 50%, #1e40af 100%); border-radius: 12px; padding: 28px 32px; margin-bottom: 20px; color: #fff; }
.strain-race-header .back-link { color: rgba(255,255,255,0.75); font-size: 13px; text-decoration: none; display: inline-block; margin-bottom: 12px; }
.strain-race-header .back-link:hover { color: #fff; }
.strain-race-header h1 { font-size: 24px; font-weight: 700; margin: 0 0 6px; }
.strain-race-header .strain-subtitle { font-size: 14px; opacity: 0.85; }

.stats-bar { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 12px; margin-bottom: 20px; }
.stat-card { background: #fff; border-radius: 10px; padding: 16px 14px; box-shadow: 0 1px 6px rgba(0,0,0,0.06); text-align: center; }
.stat-card .stat-val { font-size: 22px; font-weight: 700; color: #1a5fa8; display: block; }
.stat-card .stat-val.gold { color: #c9a84c; }
.stat-card .stat-lbl { font-size: 12px; color: #999; margin-top: 2px; }

.result-table-wrap { background: #fff; border-radius: 12px; box-shadow: 0 1px 6px rgba(0,0,0,0.06); overflow-x: auto; }
.result-table { width: 100%; border-collapse: collapse; font-size: 14px; }
.result-table thead { background: #f8fafc; }
.result-table th { padding: 12px 14px; text-align: left; font-weight: 600; color: #555; font-size: 13px; border-bottom: 2px solid #e5e7eb; white-space: nowrap; }
.result-table td { padding: 11px 14px; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
.result-table tbody tr:hover { background: #f8fafc; }
.result-table .rank-badge { display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 50%; font-weight: 700; font-size: 13px; }
.rank-1 { background: #fef3c7; color: #d97706; }
.rank-2 { background: #f1f5f9; color: #64748b; }
.rank-3 { background: #fef2f2; color: #dc2626; }
.rank-other { color: #888; }
.speed-high { color: #059669; font-weight: 600; }
.race-name-link { color: #1a5fa8; text-decoration: none; }
.race-name-link:hover { text-decoration: underline; }
.pigeon-link { color: #333; text-decoration: none; }
.pigeon-link:hover { color: #1a5fa8; }
.loft-link { color: #888; font-size: 12px; text-decoration: none; }
.loft-link:hover { color: #1a5fa8; }

.empty-state { text-align: center; padding: 60px 20px; color: #999; }
.empty-state i { font-size: 48px; display: block; margin-bottom: 12px; color: #ddd; }

@media (max-width: 768px) {
    .strain-race-header { padding: 20px 16px; }
    .strain-race-header h1 { font-size: 20px; }
    .result-table { font-size: 12px; }
    .result-table th, .result-table td { padding: 8px 6px; }
    .stats-bar { grid-template-columns: repeat(3, 1fr); gap: 8px; }
}
</style>

<div class="strain-race-page">
    <div class="strain-race-header">
        <a href="/pedigree/strain/<?php echo urlencode($strain['name']); ?>/" class="back-link">&larr; 返回<?php echo $strainName; ?>品系详情</a>
        <h1><?php echo $strainName; ?> · 赛事成绩</h1>
        <div class="strain-subtitle">品系铭鸽在各大赛事的比赛成绩全记录</div>
    </div>

    <?php if (!empty($raceStats)): ?>
    <div class="stats-bar">
        <div class="stat-card">
            <span class="stat-val"><?php echo number_format($raceStats['total_results'] ?? 0); ?></span>
            <span class="stat-lbl">总成绩条数</span>
        </div>
        <div class="stat-card">
            <span class="stat-val"><?php echo number_format($raceStats['pigeon_count'] ?? 0); ?></span>
            <span class="stat-lbl">参赛铭鸽</span>
        </div>
        <div class="stat-card">
            <span class="stat-val gold"><?php echo number_format($raceStats['champion_count'] ?? 0); ?></span>
            <span class="stat-lbl">冠军次数</span>
        </div>
        <div class="stat-card">
            <span class="stat-val"><?php echo number_format($raceStats['podium_count'] ?? 0); ?></span>
            <span class="stat-lbl">三甲次数</span>
        </div>
        <div class="stat-card">
            <span class="stat-val"><?php echo number_format($raceStats['top10_count'] ?? 0); ?></span>
            <span class="stat-lbl">前10名</span>
        </div>
        <div class="stat-card">
            <span class="stat-val"><?php echo number_format($raceStats['race_count'] ?? 0); ?></span>
            <span class="stat-lbl">覆盖赛事</span>
        </div>
        <?php if (($raceStats['best_speed'] ?? 0) > 0): ?>
        <div class="stat-card">
            <span class="stat-val"><?php echo number_format($raceStats['best_speed'], 1); ?></span>
            <span class="stat-lbl">最佳分速 (m/min)</span>
        </div>
        <div class="stat-card">
            <span class="stat-val"><?php echo number_format($raceStats['avg_speed'] ?? 0, 1); ?></span>
            <span class="stat-lbl">平均分速 (m/min)</span>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if ($totalResults > 0): ?>
    <div class="result-table-wrap">
        <table class="result-table">
            <thead>
                <tr>
                    <th style="width:60px;">排名</th>
                    <th style="min-width:160px;">足环号</th>
                    <th style="min-width:90px;">鸽主</th>
                    <th style="min-width:140px;">赛事</th>
                    <th style="min-width:90px;">公棚</th>
                    <th style="width:80px;">分速</th>
                    <th style="width:80px;">羽色</th>
                    <th style="width:80px;">地区</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($results as $r): ?>
                <?php
                $rank = (int)($r['rank'] ?? 0);
                if ($rank === 1) $rankCls = 'rank-1';
                elseif ($rank === 2) $rankCls = 'rank-2';
                elseif ($rank === 3) $rankCls = 'rank-3';
                else $rankCls = 'rank-other';
                ?>
                <tr>
                    <td>
                        <span class="rank-badge <?php echo $rankCls; ?>">
                            <?php echo $rank > 0 ? $rank : '—'; ?>
                        </span>
                    </td>
                    <td>
                        <?php if (!empty($r['pigeon_id'])): ?>
                        <a href="/pigeon/<?php echo $r['pigeon_id']; ?>.html" class="pigeon-link">
                            <?php echo h($r['ring_number'] ?? '—'); ?>
                        </a>
                        <?php else: ?>
                            <?php echo h($r['ring_number'] ?? '—'); ?>
                        <?php endif; ?>
                    </td>
                    <td><a href="/page/owner/<?php echo urlencode($r['owner_name'] ?? ''); ?>/" style="color:#333;text-decoration:none;"><?php echo h($r['owner_name'] ?? '—'); ?></a></td>
                    <td>
                        <a href="/race/<?php echo $r['race_id'] ?? 0; ?>.html" class="race-name-link">
                            <?php echo h(mb_strimwidth($r['race_name'] ?? '', 0, 20, '...')); ?>
                        </a>
                        <div style="font-size:11px;color:#aaa;"><?php echo h($r['distance_km'] ?? ''); ?>km</div>
                    </td>
                    <td>
                        <?php if (!empty($r['loft_id'])): ?>
                        <a href="/loft/<?php echo $r['loft_id']; ?>.html" class="loft-link"><?php echo h(mb_strimwidth($r['loft_name'] ?? '', 0, 10, '...')); ?></a>
                        <?php else: ?>
                        <span style="color:#ccc;">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="speed-high"><?php echo $r['speed'] > 0 ? number_format((float)$r['speed'], 1) : '—'; ?></td>
                    <td><?php echo h($r['color'] ?? '—'); ?></td>
                    <td style="color:#888;"><?php echo h($r['region'] ?? '—'); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
    <div style="margin-top:20px;">
        <?php echo renderPagination($page, $totalPages); ?>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <div class="empty-state">
        <i class="fas fa-medal"></i>
        <p>该品系暂无赛事成绩</p>
        <p style="font-size:13px;margin-top:6px;">赛事数据持续补充中，请关注 <a href="/pedigree/strain/<?php echo urlencode($strain['name']); ?>/"><?php echo $strainName; ?>品系详情</a></p>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/_footer.php'; ?>

</body>
</html>
