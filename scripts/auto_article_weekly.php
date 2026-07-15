<?php
/**
 * 信鸽之家 - 每周赛事自动文章生成脚本
 * 
 * 功能：从最新赛事数据中自动生成周报文章
 * 用法：
 *   php auto_article_weekly.php              # 干跑（不写入，只输出）
 *   php auto_article_weekly.php --run        # 正式执行（写入数据库）
 *   php auto_article_weekly.php --stats      # 仅输出统计，不生成文章
 * 
 * 建议 cron：
 *   每周一凌晨2点执行  0 2 * * 1  php /path/to/auto_article_weekly.php --run
 */

require_once __DIR__ . '/../app/config/config.php';

$pdo = get_db_connection();

// ===== 参数解析 =====
$dryRun = !in_array('--run', $argv ?? []);
$statsOnly = in_array('--stats', $argv ?? []);

// ===== 查询最近赛事数据（最近7天或最近N场）=====
$daysBack = 7;
$cutoffDate = date('Y-m-d H:i:s', strtotime("-{$daysBack} days"));

// 1. 统计近7天赛事数量
$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT r.id) as race_count,
           COUNT(rr.id) as entry_count,
           AVG(rr.speed) as avg_speed,
           MAX(rr.speed) as max_speed,
           COUNT(DISTINCT l.province) as province_count,
           COUNT(DISTINCT l.city) as city_count,
           COUNT(DISTINCT l.name) as loft_count
    FROM races r
    LEFT JOIN race_results rr ON rr.race_id = r.id
    LEFT JOIN pigeon_lofts l ON r.loft_id = l.id
    WHERE r.release_time >= ?
");
$stmt->execute([$cutoffDate]);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

// 2. 各省份赛事分布（前10）
$stmt2 = $pdo->prepare("
    SELECT l.province,
           COUNT(DISTINCT r.id) as race_count,
           COUNT(rr.id) as entry_count
    FROM races r
    LEFT JOIN race_results rr ON rr.race_id = r.id
    LEFT JOIN pigeon_lofts l ON r.loft_id = l.id
    WHERE r.release_time >= ?
      AND l.province IS NOT NULL AND l.province != ''
    GROUP BY l.province
    ORDER BY race_count DESC
    LIMIT 10
");
$stmt2->execute([$cutoffDate]);
$provinces = $stmt2->fetchAll(PDO::FETCH_ASSOC);

// 3. 最高分速TOP5
$stmt3 = $pdo->prepare("
    SELECT rr.ring_number, rr.owner_name, rr.speed, rr.race_id,
           r.name as race_name, l.name as loft_name
    FROM race_results rr
    JOIN races r ON rr.race_id = r.id
    LEFT JOIN pigeon_lofts l ON r.loft_id = l.id
    WHERE rr.race_id IN (
        SELECT id FROM races WHERE release_time >= ?
    )
      AND rr.speed IS NOT NULL AND rr.speed > 0
      AND rr.speed < 5000
    ORDER BY rr.speed DESC
    LIMIT 5
");
$stmt3->execute([$cutoffDate]);
$topSpeed = $stmt3->fetchAll(PDO::FETCH_ASSOC);

// 4. 近7天赛事列表（取最新10场）
$stmt4 = $pdo->prepare("
    SELECT r.id, r.name, l.name as loft_name, l.province, l.city,
           r.release_time, r.distance_km,
           (SELECT COUNT(*) FROM race_results WHERE race_id = r.id) as entry_count,
           (SELECT MAX(speed) FROM race_results WHERE race_id = r.id AND speed < 5000) as max_speed
    FROM races r
    LEFT JOIN pigeon_lofts l ON r.loft_id = l.id
    WHERE r.release_time >= ?
    ORDER BY r.release_time DESC
    LIMIT 10
");
$stmt4->execute([$cutoffDate]);
$recentRaces = $stmt4->fetchAll(PDO::FETCH_ASSOC);

// 5. 本周新增赛事总数（用于对比上周）
$prevStart = date('Y-m-d H:i:s', strtotime("-14 days"));
$prevEnd = date('Y-m-d H:i:s', strtotime("-7 days"));
$stmt5 = $pdo->prepare("
    SELECT COUNT(DISTINCT id) FROM races WHERE release_time >= ? AND release_time < ?
");
$stmt5->execute([$prevStart, $prevEnd]);
$prevWeekCount = $stmt5->fetchColumn();
$currWeekCount = $stats['race_count'] ?? 0;
$changePct = $prevWeekCount > 0 ? round(($currWeekCount - $prevWeekCount) / $prevWeekCount * 100) : 0;

// ===== 生成文章内容 =====
$weekNum = date('W');
$year = date('Y');
$weekStart = date('m月d日', strtotime("-6 days"));
$weekEnd = date('m月d日');

$title = "{$year}年第{$weekNum}周（{$weekStart}-{$weekEnd}）全国信鸽赛事战报";
$summary = "本周围绕全国信鸽赛事，共录入{$currWeekCount}场赛事，涵盖" 
    . ($stats['province_count'] ?? 0) . "个省份、" 
    . ($stats['city_count'] ?? 0) . "个城市、"
    . number_format($stats['entry_count'] ?? 0) . "羽赛鸽数据。"
    . ($changePct != 0 ? "较上周" . ($changePct > 0 ? "增长" : "下降") . abs($changePct) . "%。" : "");

// 构建HTML内容
$content = <<<HTML
<h2>📊 本周赛事概览</h2>
<p>本周期（{$weekStart}—{$weekEnd}），全国信鸽赛事持续推进。根据信鸽之家数据库统计，本周共录入 <strong>{$currWeekCount} 场</strong>赛事，具体分布如下：</p>

<ul>
<li>📍 <strong>参赛省份：</strong>{$stats['province_count']} 个</li>
<li>🏙️ <strong>覆盖城市：</strong>{$stats['city_count']} 个</li>
<li>🏠 <strong>参与公棚：</strong>{$stats['loft_count']} 个</li>
<li>🐦 <strong>赛鸽羽数：</strong>{$stats['entry_count']} 羽</li>
<li>📈 <strong>平均分速：</strong>{$stats['avg_speed'] > 0 ? round($stats['avg_speed'], 1) . ' 米/分' : '数据不足'}</li>
</ul>

HTML;

// 各省份分布
if (!empty($provinces)) {
    $content .= "<h2>📍 省份赛事分布 TOP10</h2>\n";
    $content .= "<table border='1' cellpadding='6' cellspacing='0' style='border-collapse:collapse;width:100%;'>";
    $content .= "<tr><th>排名</th><th>省份</th><th>赛事场次</th><th>参赛羽数</th></tr>";
    foreach ($provinces as $i => $p) {
        $rank = $i + 1;
        $content .= "<tr>";
        $content .= "<td>{$rank}</td>";
        $content .= "<td><a href='/race/province/" . urlencode($p['province']) . "/'>{$p['province']}</a></td>";
        $content .= "<td>{$p['race_count']} 场</td>";
        $content .= "<td>" . number_format($p['entry_count']) . " 羽</td>";
        $content .= "</tr>";
    }
    $content .= "</table>\n";
}

// 最高分速TOP5
if (!empty($topSpeed)) {
    $content .= "<h2>🏆 本周分速王 TOP5</h2>\n";
    $content .= "<table border='1' cellpadding='6' cellspacing='0' style='border-collapse:collapse;width:100%;'>";
    $content .= "<tr><th>排名</th><th>足环号</th><th>鸽主</th><th>分速(米/分)</th><th>所属赛事</th><th>公棚</th></tr>";
    foreach ($topSpeed as $i => $t) {
        $rank = $i + 1;
        $speed = round($t['speed'], 1);
        $content .= "<tr>";
        $content .= "<td>{$rank}</td>";
        $content .= "<td><a href='/race/ring?q=" . urlencode($t['ring_number']) . "'>{$t['ring_number']}</a></td>";
        $content .= "<td>" . htmlspecialchars($t['owner_name']) . "</td>";
        $content .= "<td><strong style='color:#c9a84c'>{$speed}</strong></td>";
        $content .= "<td><a href='/race/{$t['race_id']}.html'>" . htmlspecialchars($t['race_name']) . "</a></td>";
        $content .= "<td>" . htmlspecialchars($t['loft_name'] ?? '—') . "</td>";
        $content .= "</tr>";
    }
    $content .= "</table>\n";
}

// 最新赛事列表
if (!empty($recentRaces)) {
    $content .= "<h2>🕐 本周最新赛事</h2>\n";
    $content .= "<table border='1' cellpadding='6' cellspacing='0' style='border-collapse:collapse;width:100%;'>";
    $content .= "<tr><th>赛事名称</th><th>公棚</th><th>空距</th><th>参赛羽数</th><th>最高分速</th><th>放飞时间</th></tr>";
    foreach ($recentRaces as $r) {
        $maxSpeed = $r['max_speed'] > 0 ? round($r['max_speed'], 1) : '—';
        $content .= "<tr>";
        $content .= "<td><a href='/race/{$r['id']}.html'>" . htmlspecialchars($r['name']) . "</a></td>";
        $content .= "<td>" . htmlspecialchars($r['loft_name'] ?? '—') . "</td>";
        $content .= "<td>" . ($r['distance_km'] > 0 ? $r['distance_km'] . 'km' : '—') . "</td>";
        $content .= "<td>" . number_format($r['entry_count']) . "</td>";
        $content .= "<td>{$maxSpeed}</td>";
        $content .= "<td>" . substr($r['release_time'], 0, 16) . "</td>";
        $content .= "</tr>";
    }
    $content .= "</table>\n";
}

// 结语
$content .= <<<HTML
<h2>📌 小结</h2>
<p>本周围绕全国信鸽赛事整体活跃，赛事数量{$changePct != 0 ? ($changePct > 0 ? "较上周增长{$changePct}%" : "较上周下降" . abs($changePct) . "%") : "与上周基本持平"}。信鸽之家将持续跟踪每周赛事数据，为鸽友提供及时、精准的赛鸽成绩查询服务。</p>
<p style="color:#888;font-size:12px;">📌 数据来源：信鸽之家 · <a href="/race/browse/">查看更多赛事</a></p>
HTML;

// ===== 输出统计或生成文章 =====

if ($statsOnly) {
    echo "=== 近{$daysBack}天赛事统计 ===\n";
    echo "赛事场次: {$stats['race_count']}\n";
    echo "参赛羽数: " . number_format($stats['entry_count']) . "\n";
    echo "平均分速: " . ($stats['avg_speed'] > 0 ? round($stats['avg_speed'], 1) . " m/min\n" : "数据不足\n");
    echo "最高分速: " . ($stats['max_speed'] > 0 ? round($stats['max_speed'], 1) . " m/min\n" : "数据不足\n");
    echo "参赛省份: {$stats['province_count']}\n";
    echo "覆盖城市: {$stats['city_count']}\n";
    echo "参与公棚: {$stats['loft_count']}\n";
    echo "较上周: " . ($changePct > 0 ? "+{$changePct}%" : ($changePct < 0 ? "{$changePct}%" : "持平")) . "\n";
    echo "\n=== 省份分布 ===\n";
    foreach ($provinces as $i => $p) {
        echo "#" . ($i+1) . " {$p['province']}: {$p['race_count']}场 " . number_format($p['entry_count']) . "羽\n";
    }
    echo "\n=== 分速TOP5 ===\n";
    foreach ($topSpeed as $i => $t) {
        echo "#" . ($i+1) . " {$t['ring_number']}: " . round($t['speed'], 1) . " m/min\n";
    }
    exit(0);
}

echo "=== 每周赛事文章生成 ===\n";
echo "干跑模式: " . ($dryRun ? "是（加 --run 正式写入）" : "否（正式写入）") . "\n\n";
echo "--- 文章预览 ---\n";
echo "标题: {$title}\n";
echo "摘要: {$summary}\n";
echo "字数: " . mb_strlen(strip_tags($content)) . " 字\n";
echo "HTML长度: " . strlen($content) . " 字节\n";
echo "--- 数据统计 ---\n";
echo "近{$daysBack}天赛事: {$stats['race_count']} 场\n";
echo "参赛羽数: " . number_format($stats['entry_count']) . "\n";
echo "省份覆盖: {$stats['province_count']}\n";
echo "城市覆盖: {$stats['city_count']}\n";
echo "分速王: " . ($topSpeed ? round($topSpeed[0]['speed'], 1) . " m/min" : "暂无数据") . "\n";
echo "较上周: " . ($changePct > 0 ? "+{$changePct}%" : ($changePct < 0 ? "{$changePct}%" : "持平")) . "\n";
echo "--- 省份TOP5 ---\n";
foreach (array_slice($provinces, 0, 5) as $i => $p) {
    echo "#" . ($i+1) . " {$p['province']}: {$p['race_count']}场\n";
}
echo "---\n";

// 判断是否有足够数据生成文章
if (($stats['race_count'] ?? 0) == 0) {
    echo "⚠️ 近7天无赛事数据，不生成文章。\n";
    exit(0);
}

if ($dryRun) {
    echo "✅ 干跑完成。加 --run 参数正式写入数据库。\n";
    exit(0);
}

// ===== 正式写入数据库 =====
echo "写入数据库...\n";

$stmtInsert = $pdo->prepare("
    INSERT INTO articles (user_id, category_id, title, summary, content, cover, status, created_at, updated_at)
    VALUES (1, 1, ?, ?, ?, '', 1, NOW(), NOW())
");
$ok = $stmtInsert->execute([$title, $summary, $content]);

if ($ok) {
    $id = $pdo->lastInsertId();
    echo "✅ 文章已生成！ID: {$id}\n";
    echo "   标题: {$title}\n";
    echo "   访问: /article/{$id}.html\n";
} else {
    echo "❌ 写入失败\n";
    exit(1);
}
