<?php
/**
 * P1: 冠军鸽列表 /race/champion/
 * 所有冠军鸽（rank=1）聚合，按日期降序，分页
 */
$page_title = '冠军鸽列表 | 赛事成绩 - 信鸽之家';
$meta_description = '浏览全国公棚赛事冠军鸽榜单，查冠军足环号、鸽主、分速、赛事信息。';
$meta_keywords = '冠军鸽,赛鸽冠军,冠军足环号,信鸽冠军,公棚冠军';
$og_type = 'website';
$og_image = 'https://www.xgjia.com/public/images/og-cover.png';
$canonical_url = 'https://www.xgjia.com/race/champion/';
$ld_json = [
    '@context' => 'https://schema.org',
    '@type' => 'CollectionPage',
    'name' => '冠军鸽列表',
    'description' => $meta_description,
    'url' => $canonical_url,
    'isPartOf' => ['@type' => 'WebSite', 'name' => '信鸽之家', 'url' => 'https://www.xgjia.com'],
];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <?php include __DIR__ . '/_seo_head.php'; ?>
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/public/css/b-scheme.css">
    <link rel="shortcut icon" href="/public/images/favicon.ico">
    <style>
.champion-hero { background: linear-gradient(135deg, #1a5fa8 0%, #0d3b6e 100%); color: #fff; padding: 36px 0; }
.champion-hero h1 { font-size: 24px; font-weight: 700; }
.champion-hero .subtitle { font-size: 14px; opacity: 0.85; margin-top: 6px; }
.champion-wrap { background: #f4f6f9; padding-bottom: 40px; }
.champion-table-wrap { background: #fff; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); overflow-x: auto; }
.champion-table { width: 100%; border-collapse: collapse; font-size: 14px; }
.champion-table th { background: #f8f9fa; color: #555; font-weight: 600; padding: 14px 16px; text-align: left; border-bottom: 2px solid #e0e0e0; white-space: nowrap; }
.champion-table td { padding: 12px 16px; border-bottom: 1px solid #f0f0f0; vertical-align: middle; }
.champion-table tr:hover td { background: #f0f4ff; }
.champion-ring { color: #1a5fa8; font-weight: 600; text-decoration: none; }
.champion-ring:hover { text-decoration: underline; }
.champion-badge { display: inline-flex; align-items: center; gap: 4px; background: linear-gradient(135deg, #fffde7, #fff8e1); color: #c9a84c; padding: 3px 10px; border-radius: 12px; font-weight: 600; font-size: 12px; }
.champion-speed { color: #2e7d32; font-weight: 600; }
.champion-meta { font-size: 12px; color: #999; }
.pagination { display: flex; gap: 6px; justify-content: center; margin: 24px 0; }
.pagination .page-link { padding: 8px 16px; border: 1px solid #ddd; border-radius: 6px; color: #555; text-decoration: none; font-size: 13px; }
.pagination .page-link.active { background: #1a5fa8; color: #fff; border-color: #1a5fa8; }
.champion-total { font-size: 14px; color: #666; margin: 16px 0 12px; }
.champion-total strong { color: #c9a84c; }
@media (max-width: 768px) {
    .champion-table { font-size: 13px; }
    .champion-table th, .champion-table td { padding: 10px 8px; }
}
    </style>
</head>
<body>
<div class="champion-wrap">
<?php include __DIR__ . '/_head.php'; ?>
<div class="champion-hero">
    <div class="container">
        <h1><i class="fas fa-crown" style="color:#c9a84c;"></i> 冠军鸽列表</h1>
        <div class="subtitle">全国公棚赛事 · 历届冠军鸽成绩汇总</div>
    </div>
</div>
<div class="container" style="padding: 20px 0;">
    <div class="champion-total">共 <strong><?php echo number_format($champions['total'] ?? 0); ?></strong> 次冠军记录</div>
    <div class="champion-table-wrap">
        <table class="champion-table">
            <thead>
                <tr>
                    <th>足环号</th>
                    <th>冠军鸽主</th>
                    <th>公棚 / 赛事</th>
                    <th>空距</th>
                    <th>分速</th>
                    <th>放飞日期</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($champions['list'])): ?>
                <?php foreach ($champions['list'] as $ch): ?>
                <tr>
                    <td>
                        <?php if (!empty($ch['ring_number'])): ?>
                        <a href="/race/ring/<?php echo urlencode($ch['ring_number']); ?>" class="champion-ring">
                            <?php echo htmlspecialchars($ch['ring_number']); ?>
                        </a>
                        <span class="champion-badge"><i class="fas fa-trophy"></i> 冠军</span>
                        <?php else: ?>
                        <span style="color:#999;">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="/page/owner/<?php echo urlencode($ch['owner_name'] ?? ''); ?>/" style="color:#1a5fa8;text-decoration:none;">
                            <?php echo htmlspecialchars($ch['owner_name'] ?? '—'); ?>
                        </a>
                    </td>
                    <td>
                        <a href="/race/<?php echo intval($ch['race_id'] ?? 0); ?>.html" style="color:#333;text-decoration:none;">
                            <?php echo htmlspecialchars($ch['race_name'] ?? '—'); ?>
                        </a>
                        <div class="champion-meta"><?php echo htmlspecialchars($ch['loft_name'] ?? ''); ?></div>
                    </td>
                    <td><?php echo !empty($ch['distance_km']) ? number_format($ch['distance_km']) . 'km' : '—'; ?></td>
                    <td class="champion-speed"><?php echo !empty($ch['speed']) ? number_format($ch['speed']) : '—'; ?></td>
                    <td><?php echo htmlspecialchars($ch['release_time'] ?? '—'); ?></td>
                </tr>
                <?php endforeach; ?>
                <?php else: ?>
                <tr><td colspan="6" style="text-align:center;padding:40px;color:#999;">暂无冠军鸽数据</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if (($champions['total_pages'] ?? 1) > 1): ?>
    <?php echo renderPagination($champions['page'], $champions['total_pages']); ?>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/_footer.php'; ?>
</div>
</body>
</html>
