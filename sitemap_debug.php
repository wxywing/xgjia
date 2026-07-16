<?php
require_once __DIR__ . '/app/config/config.php';
$pdo = get_db_connection();

function check($label, $sql) {
    global $pdo;
    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($rows) {
        echo "❌ {$label}: " . count($rows) . " 条\n";
        foreach (array_slice($rows, 0, 10) as $r) {
            echo "   " . implode(" | ", array_values($r)) . "\n";
        }
        if (count($rows) > 10) echo "   ... +" . (count($rows)-10) . "\n";
    } else {
        echo "✅ {$label}: 0\n";
    }
}

echo "=== sitemap 含 & 字段诊断 ===\n\n";
check('品系 bloodline', "SELECT DISTINCT bloodline v FROM pigeons WHERE bloodline LIKE '%&%' AND bloodline != '' LIMIT 20");
check('赛事名 races.name', "SELECT id, name v FROM races WHERE name LIKE '%&%' LIMIT 20");
check('城市 lofts.city', "SELECT DISTINCT city v FROM lofts WHERE city LIKE '%&%' AND city != '' LIMIT 20");
check('城市 races.city', "SELECT DISTINCT city v FROM races WHERE city LIKE '%&%' AND city != '' LIMIT 20");
check('公棚名 lofts.name', "SELECT id, name v FROM lofts WHERE name LIKE '%&%' AND status=1 LIMIT 20");
check('赛季 year', "SELECT DISTINCT season_year v FROM races WHERE season_year LIKE '%&%' LIMIT 20");
check('tag slug', "SELECT slug v FROM tags WHERE slug LIKE '%&%' LIMIT 20");
check('文章 title', "SELECT id, title v FROM articles WHERE title LIKE '%&%' AND status=1 LIMIT 20");
echo "\n=== 完成 ===\n";
