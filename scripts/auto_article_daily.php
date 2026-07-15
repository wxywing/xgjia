<?php
/**
 * 信鸽之家 - 每日3篇SEO文章自动生成 v2
 * 
 * 每天 11:00 生成 3 篇不同主题的长尾 SEO 文章
 * 用法：
 *   php auto_article_daily.php                    # 干跑预览3篇
 *   php auto_article_daily.php --run              # 正式写入
 *   php auto_article_daily.php --run --count 1    # 只生成第1篇
 * 
 * 三篇文章模板（每天轮转，确保内容不重复）：
 *  [1] 赛事数据速览 — 近1天赛事统计 + 分速TOP5 + 公棚TOP5
 *  [2] 铭鸽之星 — 随机选1羽高分速铭鸽生成专题介绍
 *  [3] 公棚巡礼 — 随机选1个公棚生成深度介绍
 */

require_once __DIR__ . '/../app/config/config.php';

$pdo = get_db_connection();

// ===== 参数 =====
$dryRun = !in_array('--run', $argv ?? []);
$count = 3;
foreach ($argv ?? [] as $i => $arg) {
    if ($arg === '--count' && isset($argv[$i + 1])) {
        $count = max(1, min(3, intval($argv[$i + 1])));
    }
}
$year = intval(date('Y'));

// ===== 去重辅助：今天该类型是否已生成 =====
function articleExistsToday($pdo, $pattern, $date) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM articles WHERE title LIKE ? AND DATE(created_at) = ?");
    $stmt->execute([$pattern, $date]);
    return $stmt->fetchColumn() > 0;
}

$today = date('Y-m-d');
$articles = []; // 收集生成的3篇文章

// ============================================================
// 文章1：赛事数据速览（每日固定第一篇）
// ============================================================
function generate_article_1($pdo, $year, $today, $dryRun) {
    $label = date('m月d日');
    $cutoffDate = date('Y-m-d H:i:s', strtotime('-1 day'));

    // 去重
    $pattern = "%{$year}年%赛事速览%";
    if (articleExistsToday($pdo, $pattern, $today)) {
        echo "  [1] 今天已有赛事速览，跳过\n";
        return null;
    }

    // 统计
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT r.id) as race_count, COUNT(rr.id) as entry_count,
               AVG(rr.speed) as avg_speed, MAX(rr.speed) as max_speed,
               COUNT(DISTINCT l.province) as province_count
        FROM races r LEFT JOIN race_results rr ON rr.race_id = r.id
        LEFT JOIN lofts l ON r.loft_id = l.id
        WHERE r.release_time >= ?
    ");
    $stmt->execute([$cutoffDate]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    if (($stats['race_count'] ?? 0) == 0) {
        echo "  [1] 近1天无赛事数据，跳过\n";
        return null;
    }

    // 分速TOP5
    $top = $pdo->prepare("
        SELECT rr.ring_number, rr.owner_name, rr.speed, rr.race_id,
               r.name as race_name, l.name as loft_name
        FROM race_results rr JOIN races r ON rr.race_id = r.id
        LEFT JOIN lofts l ON r.loft_id = l.id
        WHERE r.release_time >= ? AND rr.speed > 0 AND rr.speed < 5000
        ORDER BY rr.speed DESC LIMIT 5
    ");
    $top->execute([$cutoffDate]);
    $topSpeed = $top->fetchAll(PDO::FETCH_ASSOC);

    // 公棚TOP5
    $topLoft = $pdo->prepare("
        SELECT l.name, l.id as loft_id, l.province, COUNT(DISTINCT r.id) as race_count, COUNT(rr.id) as entry_count
        FROM lofts l JOIN races r ON r.loft_id = l.id
        LEFT JOIN race_results rr ON rr.race_id = r.id
        WHERE r.release_time >= ? GROUP BY l.id ORDER BY entry_count DESC LIMIT 5
    ");
    $topLoft->execute([$cutoffDate]);
    $topLofts = $topLoft->fetchAll(PDO::FETCH_ASSOC);

    $title = "{$year}年{$label}全国信鸽赛事速览";
    $summary = "{$label}共收录{$stats['race_count']}场赛事、" . number_format($stats['entry_count']) . "羽赛鸽数据，涵盖{$stats['province_count']}个省份。平均分速" . round($stats['avg_speed'], 1) . "米/分。";

    $c = "<h2>📊 {$label}赛事概况</h2>\n"
        . "<p>据信鸽之家赛事数据库统计，{$label}共录入 <strong>{$stats['race_count']} 场</strong>赛事，覆盖{$stats['province_count']}个省份，"
        . number_format($stats['entry_count']) . "羽赛鸽参赛。</p>\n"
        . "<p>更多数据请访问 <a href='/'>信鸽之家赛事查询</a> 首页。</p>\n";

    if (!empty($topSpeed)) {
        $c .= "<h2>🏆 今日分速榜 TOP5</h2>\n<table border='1' cellpadding='6' cellspacing='0' style='border-collapse:collapse;width:100%'>"
            . "<tr><th>排名</th><th>足环号</th><th>鸽主</th><th>分速</th><th>赛事</th><th>公棚</th></tr>";
        foreach ($topSpeed as $i => $t) {
            $c .= "<tr><td>" . ($i + 1) . "</td>"
                . "<td><a href='/race/ring/?q=" . urlencode($t['ring_number']) . "'>{$t['ring_number']}</a></td>"
                . "<td>" . htmlspecialchars($t['owner_name']) . "</td>"
                . "<td><strong>" . round($t['speed'], 1) . "</strong></td>"
                . "<td><a href='/race/{$t['race_id']}.html'>" . htmlspecialchars($t['race_name']) . "</a></td>"
                . "<td>" . htmlspecialchars($t['loft_name'] ?? '—') . "</td></tr>\n";
        }
        $c .= "</table>\n";
    }

    if (!empty($topLofts)) {
        $c .= "<h2>🏠 参赛规模最大公棚</h2>\n<ul>";
        foreach ($topLofts as $i => $l) {
            $c .= "<li><a href='/loft/{$l['loft_id']}.html'>" . htmlspecialchars($l['name']) . "</a>"
                . "（{$l['province']}）— {$l['race_count']}场赛事，" . number_format($l['entry_count']) . "羽参赛</li>\n";
        }
        $c .= "</ul>\n";
    }

    $c .= "<h2>📌 相关工具</h2>\n<ul>"
        . "<li>🔍 <a href='/'>足环号查询</a> — 查询任意信鸽的完整参赛记录</li>"
        . "<li>🏆 <a href='/tools.php?action=top100'>TOP100 分速排行</a> — {$year}赛季最高分速榜单</li>"
        . "<li>📊 <a href='/race/browse/'>赛事数据浏览</a> — 按年份、地区筛选所有赛事</li></ul>\n";

    return ['title' => $title, 'summary' => $summary, 'content' => $c, 'label' => '赛事速览'];
}

// ============================================================
// 文章2：铭鸽之星（随机选1羽高分速铭鸽，写专题）
// ============================================================
function generate_article_2($pdo, $year, $today, $dryRun) {
    $dayOfWeek = intval(date('N')); // 1=Mon, 7=Sun

    // 去重
    $pattern = "%{$year}年%铭鸽之星%";
    if (articleExistsToday($pdo, $pattern, $today)) {
        echo "  [2] 今天已有铭鸽之星，跳过\n";
        return null;
    }

    // 按星期几选不同类型的鸽子（确保每天内容不重复）
    $orders = ['speed DESC', 'speed ASC', 'RAND()', 'entry_count DESC', 'speed DESC', 'RAND()', 'entry_count DESC'];
    $order = $orders[$dayOfWeek % 7];

    // 选1羽有足环号、有成绩的高质量鸽子
    $baseSelect = "SELECT p.id, p.ring_number, p.name, p.bloodline as strain,
                   CASE WHEN p.gender=1 THEN '雄' WHEN p.gender=2 THEN '雌' ELSE '未知' END as sex,
                   s.name as shop_name,
                   COUNT(rr.id) as entry_count,
                   MAX(rr.speed) as best_speed, AVG(rr.speed) as avg_speed
            FROM pigeons p
            LEFT JOIN shops s ON p.shop_id = s.id
            JOIN race_results rr ON rr.ring_number = p.ring_number
            WHERE p.ring_number IS NOT NULL AND p.ring_number != ''";
    
    if ($order === 'entry_count DESC') {
        $sql = $baseSelect . "
              AND rr.speed > 0 AND rr.speed < 5000
            GROUP BY p.id ORDER BY entry_count DESC LIMIT 30";
    } elseif ($order === 'speed ASC') {
        $sql = $baseSelect . "
              AND rr.speed > 500 AND rr.speed < 5000
            GROUP BY p.id ORDER BY best_speed ASC LIMIT 30";
    } else {
        $sql = $baseSelect . "
              AND rr.speed > 0 AND rr.speed < 5000
            GROUP BY p.id ORDER BY best_speed DESC LIMIT 30";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($candidates)) {
        echo "  [2] 无合适铭鸽数据，跳过\n";
        return null;
    }

    // 随机选1个（从30个候选里）
    $pigeon = $candidates[array_rand($candidates)];

    // 查询该鸽参赛详情
    $stmt2 = $pdo->prepare("
        SELECT rr.speed, rr.rank, r.name as race_name, r.distance_km, r.release_time, l.name as loft_name
        FROM race_results rr JOIN races r ON rr.race_id = r.id
        LEFT JOIN lofts l ON r.loft_id = l.id
        WHERE rr.ring_number = ? AND rr.speed > 0
        ORDER BY rr.speed DESC LIMIT 5
    ");
    $stmt2->execute([$pigeon['ring_number']]);
    $records = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    $ring = htmlspecialchars($pigeon['ring_number']);
    $label = date('m月d日');
    $title = "{$year}年{$label}铭鸽之星：{$ring}" . ($pigeon['name'] ? "「{$pigeon['name']}」" : "");
    $summary = "今日铭鸽之星 — 足环号 {$ring}，"
        . ($pigeon['strain'] ? "品系{$pigeon['strain']}，" : "")
        . "参赛{$pigeon['entry_count']}场，最佳分速" . round($pigeon['best_speed'], 1) . "米/分。";

    $c = "<h2>📋 铭鸽档案</h2>\n<ul>"
        . "<li><strong>足环号：</strong>{$ring}</li>\n"
        . ($pigeon['name'] ? "<li><strong>鸽名：</strong>" . htmlspecialchars($pigeon['name']) . "</li>\n" : "")
        . "<li><strong>鸽主：</strong>" . htmlspecialchars($pigeon['shop_name'] ?? '未知') . "</li>\n"
        . ($pigeon['strain'] ? "<li><strong>品系：</strong>" . htmlspecialchars($pigeon['strain']) . "</li>\n" : "")
        . ($pigeon['sex'] ? "<li><strong>性别：</strong>" . htmlspecialchars($pigeon['sex']) . "</li>\n" : "")
        . "<li><strong>参赛次数：</strong>{$pigeon['entry_count']} 场</li>\n"
        . "<li><strong>最佳分速：</strong><span style='color:#c9a84c;font-weight:bold;'>" . round($pigeon['best_speed'], 1) . " 米/分</span></li>\n"
        . "<li><strong>平均分速：</strong>" . round($pigeon['avg_speed'], 1) . " 米/分</li>\n"
        . "</ul>\n"
        . "<p>🔍 <a href='/pigeon/{$pigeon['id']}.html'>查看完整血统信息与图片 →</a></p>\n";

    if (!empty($records)) {
        $c .= "<h2>🏆 最佳成绩</h2>\n<table border='1' cellpadding='6' cellspacing='0' style='border-collapse:collapse;width:100%'>"
            . "<tr><th>赛事</th><th>公棚</th><th>空距</th><th>分速</th></tr>";
        foreach ($records as $r) {
            $c .= "<tr>"
                . "<td>" . htmlspecialchars($r['race_name']) . "</td>"
                . "<td>" . htmlspecialchars($r['loft_name'] ?? '—') . "</td>"
                . "<td>" . ($r['distance_km'] > 0 ? $r['distance_km'] . 'km' : '—') . "</td>"
                . "<td><strong>" . round($r['speed'], 1) . " 米/分</strong></td>"
                . "</tr>\n";
        }
        $c .= "</table>\n";
    }

    $c .= "<p style='color:#888;font-size:12px;'>📌 铭鸽之星每日由信鸽之家数据库自动筛选，展示优秀赛鸽风采。更多铭鸽信息请访问 <a href='/pigeons'>铭鸽展厅</a>。</p>\n";

    return ['title' => $title, 'summary' => $summary, 'content' => $c, 'label' => '铭鸽之星'];
}

// ============================================================
// 文章3：公棚巡礼（随机选1个公棚，写深度介绍）
// ============================================================
function generate_article_3($pdo, $year, $today, $dryRun) {
    $dayOfWeek = intval(date('N'));

    // 去重
    $pattern = "%公棚巡礼%";
    if (articleExistsToday($pdo, $pattern, $today)) {
        echo "  [3] 今天已有公棚巡礼，跳过\n";
        return null;
    }

    // 按星期轮转选公棚的类型
    $orders = ['race_count DESC', 'RAND()', 'rating DESC', 'race_count ASC', 'RAND()', 'rating DESC', 'race_count DESC'];
    $order = $orders[$dayOfWeek % 7];

    if ($order === 'race_count ASC') {
        // 选赛事少但数据质量好的公棚
        $sql = "SELECT l.id, l.name, l.province, l.city, l.rating, l.description,
                       COUNT(DISTINCT r.id) as race_count,
                       COUNT(rr.id) as entry_count,
                       AVG(rr.speed) as avg_speed
                FROM lofts l
                JOIN races r ON r.loft_id = l.id
                LEFT JOIN race_results rr ON rr.race_id = r.id
                WHERE l.status = 1 AND l.description IS NOT NULL AND l.description != ''
                GROUP BY l.id HAVING race_count >= 3 ORDER BY race_count ASC LIMIT 30";
    } elseif ($order === 'RAND()') {
        $sql = "SELECT l.id, l.name, l.province, l.city, l.rating, l.description,
                       COUNT(DISTINCT r.id) as race_count,
                       COUNT(rr.id) as entry_count,
                       AVG(rr.speed) as avg_speed
                FROM lofts l
                JOIN races r ON r.loft_id = l.id
                LEFT JOIN race_results rr ON rr.race_id = r.id
                WHERE l.status = 1 AND l.description IS NOT NULL AND l.description != ''
                GROUP BY l.id HAVING race_count >= 3 ORDER BY RAND() LIMIT 30";
    } else {
        $sql = "SELECT l.id, l.name, l.province, l.city, l.rating, l.description,
                       COUNT(DISTINCT r.id) as race_count,
                       COUNT(rr.id) as entry_count,
                       AVG(rr.speed) as avg_speed
                FROM lofts l
                JOIN races r ON r.loft_id = l.id
                LEFT JOIN race_results rr ON rr.race_id = r.id
                WHERE l.status = 1 AND l.description IS NOT NULL AND l.description != ''
                GROUP BY l.id HAVING race_count >= 3 ORDER BY race_count DESC LIMIT 30";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($candidates)) {
        echo "  [3] 无合适公棚数据，跳过\n";
        return null;
    }

    $loft = $candidates[array_rand($candidates)];

    // 查询该公棚最近赛事
    $stmt2 = $pdo->prepare("
        SELECT r.id, r.name, r.release_time, r.distance_km,
               (SELECT COUNT(*) FROM race_results WHERE race_id = r.id) as entry_count
        FROM races r WHERE r.loft_id = ? ORDER BY r.release_time DESC LIMIT 5
    ");
    $stmt2->execute([$loft['id']]);
    $recentRaces = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    $label = date('m月d日');
    $loftName = htmlspecialchars($loft['name']);
    $title = "{$year}年{$label}公棚巡礼：{$loftName}";
    $summary = "今日公棚巡礼 — {$loftName}" . ($loft['city'] ? "位于" . htmlspecialchars($loft['city']) : "")
        . "，" . number_format($loft['entry_count']) . "羽参赛数据，"
        . round($loft['avg_speed'], 1) . "米/分均速。";

    $c = "<h2>🏠 公棚概况</h2>\n<ul>"
        . "<li><strong>名称：</strong>{$loftName}</li>\n"
        . "<li><strong>地区：</strong>" . htmlspecialchars($loft['province'] ?? '')
        . ($loft['city'] ? htmlspecialchars($loft['city']) : '') . "</li>\n"
        . "<li><strong>评分：</strong>" . ($loft['rating'] > 0 ? str_repeat('⭐', min(5, intval($loft['rating']))) : '暂无') . "</li>\n"
        . "<li><strong>收录赛事：</strong>{$loft['race_count']} 场</li>\n"
        . "<li><strong>参赛总量：</strong>" . number_format($loft['entry_count']) . " 羽</li>\n"
        . "<li><strong>平均分速：</strong>" . round($loft['avg_speed'], 1) . " 米/分</li>\n"
        . "</ul>\n";

    if (!empty($loft['description'])) {
        $desc = htmlspecialchars(mb_substr($loft['description'], 0, 500));
        $c .= "<blockquote style='border-left:3px solid #1a5fa8;padding:8px 16px;background:#f4f6f9;color:#555;'><p>{$desc}" . (mb_strlen($loft['description']) > 500 ? '...' : '') . "</p></blockquote>\n";
    }

    $c .= "<p>👉 <a href='/loft/{$loft['id']}.html'>查看 {$loftName} 完整信息 →</a></p>\n";

    if (!empty($recentRaces)) {
        $c .= "<h2>🕐 最近赛事</h2>\n<ul>";
        foreach ($recentRaces as $r) {
            $c .= "<li><a href='/race/{$r['id']}.html'>" . htmlspecialchars($r['name']) . "</a>"
                . " — " . ($r['distance_km'] > 0 ? $r['distance_km'] . 'km' : '')
                . "，" . number_format($r['entry_count']) . "羽参赛"
                . "</li>\n";
        }
        $c .= "</ul>\n";
    }

    $c .= "<h2>📌 每日一棚</h2>\n"
        . "<p>信鸽之家每日精选一个公棚进行深度介绍，帮助鸽友了解全国公棚赛事情况。浏览 <a href='/loft/'>全部公棚</a> 或按 <a href='/loft/province/" . urlencode($loft['province'] ?? '') . "/'>" . htmlspecialchars($loft['province'] ?? '') . "地区</a> 筛选。</p>\n";

    return ['title' => $title, 'summary' => $summary, 'content' => $c, 'label' => '公棚巡礼'];
}

// ============================================================
// 主流程
// ============================================================

echo "=== 每日3篇SEO文章生成 v2 ===\n";
echo "日期: {$today}\n";
echo "模式: " . ($dryRun ? "干跑（加 --run 正式写入）" : "正式写入") . "\n";
echo "目标: {$count} 篇\n\n";

$generators = ['generate_article_1', 'generate_article_2', 'generate_article_3'];

for ($i = 0; $i < $count; $i++) {
    $fn = $generators[$i];
    $num = $i + 1;
    echo "[{$num}/3] 生成：「" . ($i == 0 ? '赛事速览' : ($i == 1 ? '铭鸽之星' : '公棚巡礼')) . "」...\n";
    
    $article = $fn($pdo, $year, $today, $dryRun);
    
    if ($article === null) {
        continue;
    }

    echo "  标题: {$article['title']}\n";
    echo "  摘要: {$article['summary']}\n";
    echo "  正文: " . mb_strlen(strip_tags($article['content'])) . " 字\n";

    if ($dryRun) {
        echo "  ✅ 预览完成（未写入）\n\n";
    } else {
        $stmtInsert = $pdo->prepare("
            INSERT INTO articles (user_id, category_id, title, summary, content, cover, source, views, status, published_at, created_at, updated_at)
            VALUES (1, 1, ?, ?, ?, '', '信鸽之家数据引擎', 0, 1, NOW(), NOW(), NOW())
        ");
        $ok = $stmtInsert->execute([$article['title'], $article['summary'], $article['content']]);
        
        if ($ok) {
            $id = $pdo->lastInsertId();
            echo "  ✅ 已生成！ID: {$id} → /article/{$id}.html\n\n";
        } else {
            $err = $stmtInsert->errorInfo();
            echo "  ❌ 写入失败: {$err[2]}\n\n";
        }
    }
}

if ($dryRun) {
    echo "✅ 干跑完成。加 --run 正式写入 {$count} 篇。\n";
} else {
    echo "✅ 全部完成！\n";
}
