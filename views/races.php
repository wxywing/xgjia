<?php
$page_title = '赛事成绩' . ((($recentRaces['page'] ?? 1) > 1) ? ' - 第' . intval($recentRaces['page']) . '页' : '') . ' | 信鸽之家';
$page_desc = '查公棚赛事成绩、冠军榜、足环号追溯。覆盖全国公棚，实时赛事数据。';
$page_type = 'website';
$page_url = 'https://www.xgjia.com/race/' . ($_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '');

// JSON-LD - ItemList (最近赛事)
$ld_items = [];
foreach (array_slice($recentRaces['list'] ?? [], 0, 10) as $i => $r) {
    $ld_items[] = [
        '@type' => 'ListItem',
        'position' => $i + 1,
        'url' => 'https://www.xgjia.com/race/' . ($r['id'] ?? '') . '.html',
    ];
}
$ld_itemlist = [
    '@context' => 'https://schema.org',
    '@type' => 'ItemList',
    'name' => '赛事成绩',
    'numberOfItems' => $recentRaces['total'] ?? 0,
    'itemListElement' => $ld_items,
];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($page_desc); ?>">
    <meta name="keywords" content="赛事成绩,公棚成绩,赛鸽成绩查询,足环号查询,冠军榜,信鸽之家">
    <meta property="og:title" content="<?php echo htmlspecialchars($page_title); ?>">
    <meta property="og:type" content="<?php echo $page_type; ?>">
    <meta property="og:image" content="https://www.xgjia.com/public/images/og-cover.png">
    <link rel="canonical" href="<?php echo $page_url; ?>">
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/public/css/b-scheme.css">
    <link rel="shortcut icon" href="/public/images/favicon.ico">
    <script type="application/ld+json"><?php echo json_encode($ld_itemlist, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>
    <style>
.page-races-wrap { background: #f4f6f9; }
.race-hero {
    background: linear-gradient(135deg, #1a5fa8 0%, #0d3b6e 50%, #0a2d52 100%);
    color: #fff; padding: 48px 0 40px; position: relative; overflow: hidden;
}
.race-hero h1 { font-size: 32px; font-weight: 700; margin-bottom: 8px; }
.race-hero .subtitle { font-size: 15px; opacity: 0.85; margin-bottom: 24px; }
.race-search-box {
    display: flex; gap: 0; background: rgba(255,255,255,0.15);
    backdrop-filter: blur(10px); border-radius: 12px; overflow: hidden;
    max-width: 680px; box-shadow: 0 4px 20px rgba(0,0,0,0.15);
}
.race-search-box input {
    flex: 1; padding: 14px 20px; border: none; background: transparent;
    color: #fff; font-size: 16px; outline: none;
}
.race-search-box input::placeholder { color: rgba(255,255,255,0.6); }
.race-search-box button {
    padding: 14px 28px; background: #c9a84c; color: #fff; border: none;
    font-size: 16px; font-weight: 600; cursor: pointer;
}
.race-search-box button:hover { background: #b8953a; }

.race-stats-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin: -24px 0 32px; position: relative; z-index: 2; }
.stat-card { background: #fff; border-radius: 12px; padding: 20px; text-align: center; box-shadow: 0 2px 12px rgba(0,0,0,0.06); }
.stat-card .num { font-size: 28px; font-weight: 700; color: #1a5fa8; }
.stat-card .label { font-size: 13px; color: #888; margin-top: 4px; }

.season-tabs { display: flex; gap: 8px; margin-bottom: 24px; flex-wrap: wrap; }
.season-tab { padding: 6px 16px; border-radius: 20px; font-size: 13px; cursor: pointer; border: 1px solid #ddd; background: #fff; color: #666; transition: all .2s; text-decoration: none; }
.season-tab:hover { border-color: #1a5fa8; color: #1a5fa8; }
.season-tab.active { background: #1a5fa8; color: #fff; border-color: #1a5fa8; }
.season-tab.fire::after { content: ' 🔥'; }

.race-cards { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; margin-bottom: 32px; }
.race-card { background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 1px 8px rgba(0,0,0,0.05); transition: transform .2s; }
.race-card:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(0,0,0,0.1); }
.race-card .badge { display: inline-block; padding: 2px 10px; border-radius: 12px; font-size: 12px; font-weight: 600; margin-bottom: 8px; }
.badge-spring { background: #e8f5e9; color: #2e7d32; }
.badge-autumn { background: #fff3e0; color: #e65100; }
.race-card h3 { font-size: 16px; margin: 8px 0 6px; }
.race-card h3 a { color: #1a5fa8; text-decoration: none; }
.race-card h3 a:hover { text-decoration: underline; }
.race-card .meta { font-size: 13px; color: #888; }
.race-card .meta-row { display: flex; justify-content: space-between; margin-top: 12px; font-size: 13px; color: #555; }
.race-card .meta-row span strong { color: #1a5fa8; }

.section-title { font-size: 20px; font-weight: 700; color: #333; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
.champion-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 32px; }
.champion-card { background: #fff; border-radius: 10px; padding: 14px; box-shadow: 0 1px 6px rgba(0,0,0,0.05); border-top: 3px solid #1a5fa8; text-align: center; }
.champion-card.silver { border-top-color: #9e9e9e; }
.champion-card.bronze { border-top-color: #c9a84c; }
.champion-card .medal { font-size: 24px; margin-bottom: 4px; }
.champion-card .ring { font-size: 14px; font-weight: 700; color: #333; }
.champion-card .owner { font-size: 12px; color: #888; margin: 2px 0; }
.champion-card .speed { font-size: 13px; color: #e65100; font-weight: 600; }

.race-table-wrap { overflow-x: auto; margin-bottom: 32px; }
.race-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.race-table th { background: #f8f9fa; color: #555; font-weight: 600; padding: 10px 12px; text-align: left; border-bottom: 2px solid #e0e0e0; white-space: nowrap; }
.race-table td { padding: 10px 12px; border-bottom: 1px solid #f0f0f0; }
.race-table tr:hover td { background: #f8f9ff; }
.tag-hot { background: #fce4ec; color: #c62828; padding: 1px 8px; border-radius: 10px; font-size: 11px; }
.tag-live { background: #e8f5e9; color: #2e7d32; padding: 1px 8px; border-radius: 10px; font-size: 11px; }

.pagination { display: flex; gap: 6px; justify-content: center; margin: 24px 0; }
.pagination .page-link { padding: 6px 14px; border: 1px solid #ddd; border-radius: 6px; color: #555; text-decoration: none; font-size: 14px; }
.pagination .page-link.active { background: #1a5fa8; color: #fff; border-color: #1a5fa8; }
.pagination .page-link:hover:not(.active) { border-color: #1a5fa8; color: #1a5fa8; }

@media (max-width: 768px) {
    .race-stats-row { grid-template-columns: repeat(2, 1fr); }
    .race-cards { grid-template-columns: 1fr; }
    .champion-grid { grid-template-columns: repeat(2, 1fr); }
}
    </style>
</head>
<body>
<div class="page-races-wrap">
<?php include __DIR__ . '/_head.php'; ?>

<!-- Hero -->
<section class="race-hero">
    <div class="container">
        <h1>🏆 赛事成绩</h1>
        <p class="subtitle">覆盖全国公棚赛事，实时查询成绩与冠军榜 · <a href="/race/season/2026/" style="color:#c9a84c;text-decoration:underline;">📊 赛季总结 →</a> · <a href="/race/champion/" style="color:#c9a84c;text-decoration:underline;">🏆 冠军鸽 →</a> · <a href="/race/city/" style="color:#c9a84c;text-decoration:underline;">🏙️ 按城市 →</a> · <a href="/race/province/" style="color:#c9a84c;text-decoration:underline;">📍 按省份 →</a></p>
        <form class="race-search-box" action="/race/" method="get" onsubmit="
            var v = this.q.value.trim();
            if (!v) return false;
            // 自动识别足环号：数字-数字(06-1819177) / 纯数字(2025260011408) / 字母开头(CHN2025...) → 跳足环时间线
            if (/^(\d{1,4}-\d{4,}|\d{8,}|[A-Za-z]{2,}\d)/i.test(v)) {
                window.location.href = '/race/ring/' + encodeURIComponent(v) + '/';
                return false;
            }
            return true;
        ">
            <input type="text" name="q" placeholder="查足环号或搜公棚/鸽主..." value="<?php echo htmlspecialchars($keyword ?? ''); ?>">
            <button type="submit">查成绩</button>
        </form>
        <div style="margin-top:10px;font-size:12px;opacity:0.7;">
            <i class="fas fa-lightbulb"></i> 输入足环号（如 CHN2025-03-1234567）自动跳转赛绩追踪页，查看全部参赛记录、分速变化与排名
        </div>
    </div>
</section>

<div class="container" style="padding: 30px 0;">
    <!-- Stats -->
    <div class="race-stats-row">
        <div class="stat-card">
            <div class="num"><?php echo number_format($stats['loft_count'] ?? 0); ?></div>
            <div class="label">收录公棚</div>
        </div>
        <div class="stat-card">
            <div class="num"><?php echo number_format($stats['race_count'] ?? 0); ?></div>
            <div class="label">累计赛季</div>
        </div>
        <div class="stat-card">
            <div class="num"><?php echo number_format($stats['result_count'] ?? 0); ?></div>
            <div class="label">成绩记录</div>
        </div>
        <div class="stat-card">
            <div class="num"><?php echo number_format($stats['champion_count'] ?? 0); ?></div>
            <div class="label">冠军鸽收录</div>
        </div>
    </div>

    <!-- 赛季回顾卡片 -->
    <div class="season-review-card" style="background: linear-gradient(135deg, #1a5fa8 0%, #0d3b6e 100%); border-radius: 12px; padding: 20px 24px; display: flex; align-items: center; gap: 20px; margin-bottom: 20px; color: #fff;">
        <div style="flex:1;">
            <h3 style="font-size:18px;font-weight:700;margin:0 0 6px;"><i class="fas fa-chart-bar" style="color:#c9a84c;"></i> 2026赛季回顾</h3>
            <p style="font-size:13px;opacity:0.85;margin:0;">数据驱动的赛季总结：月度分布、最佳分速、活跃公棚排名</p>
        </div>
        <a href="/race/season/2026/" style="flex-shrink:0;background:#fff;color:#1a5fa8;padding:10px 24px;border-radius:8px;font-weight:600;font-size:14px;text-decoration:none;white-space:nowrap;transition:all .2s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">查看详情 →</a>
    </div>

    <!-- Season Tabs -->
    <div class="season-tabs">
        <?php
        $currentYear = $_GET['year'] ?? '2026';  // 默认 2026
        $allActive = ($currentYear === 'all') ? ' active' : '';
        echo "<a href='/race/?year=all' class='season-tab{$allActive}'>全部</a>";
        if (!empty($seasons)):
            foreach ($seasons as $key => $s):
                if ($s['season_type'] == 'autumn') $typeLabel = '秋赛';
                elseif ($s['season_type'] == 'other') $typeLabel = '其他';
                else $typeLabel = '春赛';
                $label = $s['season_year'] . $typeLabel;
                $active = ($currentYear == $s['season_year'] && ($_GET['type'] ?? '') == $s['season_type']) ? ' active' : '';
                $fire = $s['season_type'] == 'autumn' ? ' fire' : '';
                echo "<a href='/race/?year={$s['season_year']}&type={$s['season_type']}' class='season-tab{$active}{$fire}'>{$label}</a>";
            endforeach;
        endif;
        ?>
    </div>

    <!-- Recent Completed Races -->
    <h2 class="section-title">📋 最近完赛</h2>
    <div class="race-cards">
        <?php if (!empty($recentRaces['list'])): ?>
            <?php foreach ($recentRaces['list'] as $r): ?>
            <div class="race-card">
                <?php
                $badgeClass = ($r['season_type'] == 'spring') ? 'badge-spring' : (($r['season_type'] == 'other') ? 'badge-other' : 'badge-autumn');
                $badgeLabel = ($r['season_type'] == 'spring') ? '春赛' : (($r['season_type'] == 'other') ? '其他' : '秋赛');
                ?>
                <span class="badge <?php echo $badgeClass; ?>"><?php echo $badgeLabel; ?></span>
                <h3><a href="/race/<?php echo $r['id']; ?>.html"><?php echo htmlspecialchars($r['name']); ?></a></h3>
                <div class="meta"><?php echo htmlspecialchars($r['loft_name']); ?> · <?php echo htmlspecialchars($r['province'] ?? ''); ?></div>
                <div class="meta-row">
                    <span>📅 <?php echo (!empty($r['release_time']) && strtotime($r['release_time'])) ? date('Y-m-d', strtotime($r['release_time'])) : '—'; ?></span>
                    <span>参赛 <strong><?php echo number_format($r['entry_count'] ?? 0); ?></strong></span>
                    <span>归巢 <strong><?php echo number_format($r['returned_count'] ?? 0); ?></strong></span>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="grid-column:1/-1;text-align:center;padding:40px;color:#999;">暂无赛事数据，数据采集中...</div>
        <?php endif; ?>
    </div>

    <?php if ($recentRaces['total_pages'] > 1): ?>
    <?php echo renderPagination($recentRaces['page'], $recentRaces['total_pages']); ?>
    <?php endif; ?>

    <!-- Champions -->
    <h2 class="section-title">👑 冠军榜</h2>
    <div class="champion-grid">
        <?php foreach ($champions as $i => $c): ?>
        <?php $cls = [0 => '', 1 => 'silver', 2 => 'bronze'][$i] ?? ''; ?>
        <?php $emoji = [0 => '🥇', 1 => '🥈', 2 => '🥉'][$i] ?? '⭐'; ?>
        <div class="champion-card <?php echo $cls; ?>">
            <div class="medal"><?php echo $emoji; ?></div>
            <div class="ring"><?php echo htmlspecialchars($c['ring_number'] ?? '—'); ?></div>
            <div class="owner"><?php echo htmlspecialchars($c['owner_name'] ?? '—'); ?></div>
            <div class="speed"><?php echo number_format($c['speed'] ?? 0, 2); ?> m/min</div>
            <div style="font-size:11px;color:#aaa;margin-top:4px;"><?php echo htmlspecialchars($c['loft_name'] ?? ''); ?></div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($champions)): ?>
            <div style="grid-column:1/-1;text-align:center;padding:30px;color:#999;">暂无冠军数据</div>
        <?php endif; ?>
    </div>

    <!-- Loft Race Table -->
    <h2 class="section-title">📊 公棚成绩一览</h2>
    <div class="race-table-wrap">
        <table class="race-table">
            <thead>
                <tr>
                    <th>公棚名称</th>
                    <th>省份</th>
                    <th>最新赛事</th>
                    <th>更新时间</th>
                    <th>收录赛季</th>
                    <th>参赛羽数</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($loftRaces['list'])): ?>
                <?php foreach ($loftRaces['list'] as $lr): ?>
                <tr>
                    <td><a href="/loft/<?php echo $lr['loft_id']; ?>.html" style="color:#1a5fa8;font-weight:600;"><?php echo htmlspecialchars($lr['loft_name']); ?></a></td>
                    <td><?php if (!empty($lr['province'])): ?><a href="/race/province/<?php echo urlencode($lr['province']); ?>/" style="color:#1a5fa8;"><?php echo htmlspecialchars($lr['province']); ?></a><?php endif; ?></td>
                    <td><a href="/race/<?php echo $lr['latest_race_id']; ?>.html" style="color:#1a5fa8;"><?php echo htmlspecialchars($lr['latest_race_name']); ?></a></td>
                    <td style="color:#888;font-size:13px;"><?php echo (!empty($lr['latest_update']) && strtotime($lr['latest_update'])) ? date('Y-m-d', strtotime($lr['latest_update'])) : '—'; ?></td>
                    <td><?php echo number_format($lr['season_count'] ?? 0); ?> 场</td>
                    <td><?php echo $lr['latest_entry_count'] ? number_format($lr['latest_entry_count']) : '—'; ?></td>
                </tr>
                <?php endforeach; ?>
                <?php else: ?>
                <tr><td colspan="6" style="text-align:center;padding:30px;color:#999;">暂无公棚数据</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($loftRaces['total_pages'] > 1): ?>
    <div class="pagination">
        <?php if ($loftRaces['page'] > 1): ?>
        <a class="page-link" href="/race/?lp=<?php echo $loftRaces['page'] - 1; ?><?php echo !empty($year) ? '&year=' . $year : ''; ?><?php echo !empty($type) ? '&type=' . $type : ''; ?>">上一页</a>
        <?php endif; ?>
        <?php for ($i = 1; $i <= $loftRaces['total_pages']; $i++): ?>
        <a class="page-link<?php echo $i == $loftRaces['page'] ? ' active' : ''; ?>" href="/race/?lp=<?php echo $i; ?><?php echo !empty($year) ? '&year=' . $year : ''; ?><?php echo !empty($type) ? '&type=' . $type : ''; ?>"><?php echo $i; ?></a>
        <?php endfor; ?>
        <?php if ($loftRaces['page'] < $loftRaces['total_pages']): ?>
        <a class="page-link" href="/race/?lp=<?php echo $loftRaces['page'] + 1; ?><?php echo !empty($year) ? '&year=' . $year : ''; ?><?php echo !empty($type) ? '&type=' . $type : ''; ?>">下一页</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/_footer.php'; ?>
</div>
</body>
</html>
