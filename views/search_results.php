<?php
/**
 * 智能搜索结果页
 */
?><!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo h($page_title); ?></title>
    <meta name="description" content="搜索「<?php echo h($q); ?>」的相关结果 - 信鸽之家赛鸽数据平台">
    <meta name="robots" content="noindex, follow">
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/public/css/b-scheme.css">
    <style>
/* search results inline */
.search-page { padding: 24px 0; min-height: 60vh; }
.search-header { margin-bottom: 24px; }
.search-header h2 { font-size: 22px; font-weight: 800; margin-bottom: 4px; }
.search-header .sub { font-size: 13px; color: #95a5b8; }
.result-group { margin-bottom: 32px; }
.result-group h3 { font-size: 16px; font-weight: 700; margin-bottom: 12px; display: flex; align-items: center; gap: 8px; }
.result-group h3 .badge { font-size: 11px; background: #e8ecf0; color: #5a6a7a; padding: 2px 10px; border-radius: 10px; font-weight: 500; }
.result-row { display: flex; align-items: center; gap: 12px; padding: 12px 16px; background: #fff; border-radius: 8px; margin-bottom: 8px; box-shadow: 0 1px 4px rgba(26,95,168,0.05); text-decoration: none; color: inherit; transition: all .15s; }
.result-row:hover { box-shadow: 0 4px 16px rgba(26,95,168,0.1); transform: translateY(-1px); }
.result-row .icon { font-size: 22px; width: 36px; text-align: center; flex-shrink: 0; }
.result-row .info { flex: 1; min-width: 0; }
.result-row .info .name { font-size: 15px; font-weight: 600; color: #2c3e50; }
.result-row .info .detail { font-size: 12px; color: #95a5b8; margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.result-row .info .detail i { margin-right: 2px; }
.result-row .arrow { color: #95a5b8; flex-shrink: 0; }
.no-result { text-align: center; padding: 40px 20px; color: #95a5b8; }
.no-result .icon { font-size: 48px; margin-bottom: 12px; }
.no-result p { margin-bottom: 16px; font-size: 14px; }
.no-result .hints { font-size: 13px; line-height: 1.8; }
.no-result .hints code { background: #e8ecf0; padding: 2px 8px; border-radius: 4px; font-size: 12px; }

/* ===== 搜索页移动端适配 ===== */
@media (max-width: 768px) {
    .search-page { padding: 12px 0 80px; }
    .search-header { margin-bottom: 16px; }
    .search-header h2 { font-size: 18px; }
    .result-group { margin-bottom: 20px; }
    .result-group h3 { font-size: 14px; }
    .result-row { padding: 10px 12px; }
    .result-row .icon { font-size: 18px; width: 28px; }
    .result-row .info .name { font-size: 13px; }
    .result-row .info .detail { font-size: 11px; white-space: normal; overflow: visible; text-overflow: clip; }
    .result-row .arrow { display: none; }
    .no-result { padding: 30px 16px; }
    .no-result .icon { font-size: 40px; }
}
</style>
</head>
<body>
<?php include __DIR__ . '/_head.php'; ?>

<div class="search-page container">
    <div class="search-header">
        <h2><i class="fas fa-search" style="color:var(--primary);margin-right:8px;"></i>搜索：<?php echo h($q); ?></h2>
<?php
$total = (!empty($results['ring']) ? 1 : 0) + count($results['lofts']) + count($results['owners']);
?>
        <div class="sub">找到 <?php echo $total; ?> 个相关结果</div>
    </div>

<?php if ($total === 0): ?>
    <!-- 无结果 -->
    <div class="no-result">
        <div class="icon">🔍</div>
        <p>没有找到与「<strong><?php echo h($q); ?></strong>」相关的结果</p>
        <div class="hints">
            <div>💡 <strong>试试看：</strong></div>
            <div>• 足环号格式：<code>CHN2025-01-0702812</code> 或 <code>2025-01-0702812</code></div>
            <div>• 公棚名称：<code>开尔爱心</code>、<code>北京翱翔</code></div>
            <div>• 鸽主姓名：<code>张三</code>、<code>王强</code></div>
        </div>
    </div>
<?php else: ?>
    <!-- 环号结果 -->
<?php if (!empty($results['ring'])): ?>
    <div class="result-group">
        <h3><i class="fas fa-ring"></i> 赛事成绩 <span class="badge"><?php echo count($results['ring']); ?> 条</span></h3>
<?php foreach (array_slice($results['ring'], 0, 5) as $rr): ?>
        <a href="/race/<?php echo $rr['id'] ?? ($rr['race_id'] ?? ''); ?>.html" class="result-row">
            <div class="icon">🏁</div>
            <div class="info">
                <div class="name"><?php echo h($rr['race_name'] ?? $rr['ring_number'] ?? ''); ?></div>
                <div class="detail"><i class="fas fa-trophy"></i> 第<?php echo $rr['rank'] ?? ''; ?>名 · <?php echo $rr['speed'] ?? ''; ?> m/min</div>
            </div>
            <span class="arrow"><i class="fas fa-chevron-right"></i></span>
        </a>
<?php endforeach; ?>
    </div>
<?php endif; ?>

    <!-- 公棚结果 -->
<?php if (!empty($results['lofts'])): ?>
    <div class="result-group">
        <h3><i class="fas fa-building"></i> 公棚 <span class="badge"><?php echo count($results['lofts']); ?> 家</span></h3>
<?php foreach ($results['lofts'] as $loft): ?>
        <a href="/loft/<?php echo $loft['id']; ?>.html" class="result-row">
            <div class="icon">🏠</div>
            <div class="info">
                <div class="name"><?php echo h($loft['name'] ?? ''); ?></div>
                <div class="detail"><i class="fas fa-map-marker-alt"></i> <?php echo h($loft['province'] ?? ''); ?><?php if (!empty($loft['contact_phone'])): ?> · <i class="fas fa-phone"></i> <?php echo h($loft['contact_phone']); ?><?php endif; ?></div>
            </div>
            <span class="arrow"><i class="fas fa-chevron-right"></i></span>
        </a>
<?php endforeach; ?>
    </div>
<?php endif; ?>

    <!-- 鸽主结果 -->
<?php if (!empty($results['owners'])): ?>
    <div class="result-group">
        <h3><i class="fas fa-user"></i> 鸽主 <span class="badge"><?php echo count($results['owners']); ?> 位</span></h3>
<?php foreach ($results['owners'] as $owner): ?>
        <a href="/page/owner/<?php echo urlencode($owner); ?>/" class="result-row">
            <div class="icon">👤</div>
            <div class="info">
                <div class="name"><?php echo h($owner); ?></div>
                <div class="detail">查看全部参赛记录</div>
            </div>
            <span class="arrow"><i class="fas fa-chevron-right"></i></span>
        </a>
<?php endforeach; ?>
    </div>
<?php endif; ?>

<?php endif; /* total > 0 */ ?>
</div>

<?php include __DIR__ . '/_footer.php'; ?>
</body>
</html>
