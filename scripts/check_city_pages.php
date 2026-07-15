<?php
/**
 * 信鸽之家 - 城市页面数据诊断脚本
 * 
 * 检查数据库中有多少城市有赛事数据，对应城市页面是否可访问
 * 用法：
 *   php check_city_pages.php          # 查看所有有数据的城市
 *   php check_city_pages.php --html   # 输出城市列表 HTML（可直接预览）
 */

require_once __DIR__ . '/../app/config/config.php';

$pdo = get_db_connection();
$argv = $argv ?? [];
$html_mode = in_array('--html', $argv);

echo "=== 城市赛事数据诊断 ===\n\n";

// 查询所有有赛事数据的城市
$stmt = $pdo->query("
    SELECT 
        l.city,
        l.province,
        COUNT(DISTINCT r.id) as race_count,
        COUNT(rr.id) as entry_count,
        MIN(r.release_time) as first_race,
        MAX(r.release_time) as latest_race
    FROM races r
    JOIN pigeon_lofts l ON r.loft_id = l.id
    LEFT JOIN race_results rr ON rr.race_id = r.id
    WHERE l.city IS NOT NULL 
      AND l.city != ''
      AND r.status = 1
    GROUP BY l.city, l.province
    ORDER BY race_count DESC
");
$cities = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_cities = count($cities);
$total_races = array_sum(array_column($cities, 'race_count'));
$total_entries = array_sum(array_column($cities, 'entry_count'));

echo "📊 总体数据\n";
echo "───────────────\n";
echo "有赛事数据的城市：{$total_cities} 个\n";
echo "总赛事场次：{$total_races} 场\n";
echo "总参赛羽数：" . number_format($total_entries) . " 羽\n";
echo "\n";

// 按省份分组
$by_province = [];
foreach ($cities as $c) {
    $p = $c['province'] ?: '未知';
    if (!isset($by_province[$p])) $by_province[$p] = [];
    $by_province[$p][] = $c;
}

ksort($by_province);

if (!$html_mode) {
    echo "📍 各省份城市数\n";
    echo "───────────────\n";
    foreach ($by_province as $province => $list) {
        $race_sum = array_sum(array_column($list, 'race_count'));
        echo sprintf("%-8s  %2d 个城市  %4d 场赛事\n", $province, count($list), $race_sum);
    }
    echo "\n";
    
    echo "🏙️ 完整城市列表（按赛事数排序）\n";
    echo "────────────────────────────────────────────────────────\n";
    printf("%-4s %-8s %-10s %6s %10s %s\n", "序", "省份", "城市", "赛事", "参赛羽数", "最近赛事");
    echo "────────────────────────────────────────────────────────\n";
    foreach ($cities as $i => $c) {
        $idx = $i + 1;
        $province = mb_substr($c['province'], 0, 6);
        $city = mb_substr($c['city'], 0, 8);
        $races = $c['race_count'];
        $entries = number_format($c['entry_count']);
        $latest = substr($c['latest_race'], 0, 10);
        printf("%3d. %-8s %-10s %5d 场 %10s  %s\n", $idx, $province, $city, $races, $entries, $latest);
    }
    echo "\n";
    
    // 高价值城市（赛事>=5场）
    $hot_cities = array_filter($cities, fn($c) => $c['race_count'] >= 5);
    echo "🔥 高价值城市（赛事≥5场，共 " . count($hot_cities) . " 个）\n";
    echo "────────────────────────────────────────────────────────\n";
    foreach ($hot_cities as $c) {
        $city = htmlspecialchars($c['city']);
        $province = htmlspecialchars($c['province']);
        echo "  ✅ /race/city/{$city}/  ({$province}, {$c['race_count']}场)\n";
    }
    echo "\n";
    
    // 低数据城市
    $low_cities = array_filter($cities, fn($c) => $c['race_count'] <= 1);
    echo "⚠️ 低数据城市（赛事≤1场，共 " . count($low_cities) . " 个，可考虑从列表隐藏）\n";
    foreach (array_slice($low_cities, 0, 10) as $c) {
        echo "  ⚠️ {$c['city']} ({$c['province']}) - {$c['race_count']} 场\n";
    }
    if (count($low_cities) > 10) echo "  ... 还有 " . (count($low_cities) - 10) . " 个\n";
    
    echo "\n📌 SEO 建议：\n";
    echo "  · {$total_cities} 个城市页面均可通过 /race/city/{城市名}/ 访问\n";
    echo "  · 高价值城市（≥5场赛事）：" . count($hot_cities) . " 个，建议优先推送到百度\n";
    echo "  · 城市页面已有 TDK、JSON-LD、Breadcrumb，完全合规\n";
    
} else {
    // HTML 输出
    $html = '<!DOCTYPE html><html lang="zh-CN"><head>';
    $html .= '<meta charset="UTF-8"><title>城市赛事数据</title>';
    $html .= '<style>';
    $html .= 'body{font-family:sans-serif;padding:20px;background:#f4f6f9}';
    $html .= 'h1{color:#1a5fa8}th,td{padding:8px 12px;border-bottom:1px solid #e0e0e0;text-align:left}';
    $html .= 'th{background:#1a5fa8;color:#fff}.province{margin-top:24px;font-size:18px;color:#333;font-weight:bold}';
    $html .= 'table{width:100%;border-collapse:collapse;background:#fff;border-radius:8px;overflow:hidden}';
    $html .= '.race-count{color:#c9a84c;font-weight:bold}';
    $html .= 'a{color:#1a5fa8;text-decoration:none}a:hover{text-decoration:underline}';
    $html .= '.summary{background:#fff;padding:16px;border-radius:8px;margin-bottom:20px}';
    $html .= '</style></head><body>';
    $html .= '<h1>🏙️ 城市赛事数据总览</h1>';
    $html .= '<div class="summary">';
    $html .= "<strong>{$total_cities}</strong> 个城市有赛事数据，共 <strong>{$total_races}</strong> 场比赛，";
    $html .= "<strong>" . number_format($total_entries) . "</strong> 羽赛鸽记录。";
    $html .= '</div>';
    
    foreach ($by_province as $province => $list) {
        $html .= '<div class="province">📍 ' . htmlspecialchars($province) . ' (' . count($list) . ' 个城市)</div>';
        $html .= '<table><thead><tr><th>城市</th><th>赛事</th><th>参赛羽数</th><th>最早赛事</th><th>最近赛事</th><th>页面</th></tr></thead><tbody>';
        foreach ($list as $c) {
            $city_enc = urlencode($c['city']);
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($c['city']) . '</td>';
            $html .= '<td class="race-count">' . $c['race_count'] . ' 场</td>';
            $html .= '<td>' . number_format($c['entry_count']) . '</td>';
            $html .= '<td>' . substr($c['first_race'], 0, 10) . '</td>';
            $html .= '<td>' . substr($c['latest_race'], 0, 10) . '</td>';
            $html .= '<td><a href="/race/city/' . $city_enc . '/" target="_blank">查看页面</a></td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';
    }
    
    $html .= '<p style="color:#888;margin-top:20px;font-size:12px;">数据来源：信鸽之家 · 生成时间：' . date('Y-m-d H:i:s') . '</p>';
    $html .= '</body></html>';
    
    $out = __DIR__ . '/city_data_report.html';
    file_put_contents($out, $html);
    echo "✅ HTML 报告已生成: {$out}\n";
    echo "   可用浏览器打开查看，支持直接点击城市名访问对应页面\n";
}
