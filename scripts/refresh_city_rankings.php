<?php
/**
 * 城市 TOP 预计算表刷新脚本
 * 用法: php refresh_city_rankings.php
 * 建议 crontab: 0 * * * * php /path/to/scripts/refresh_city_rankings.php >> /path/to/logs/refresh_city_rankings.log 2>&1
 */

require_once __DIR__ . '/../app/bootstrap.php';

echo "[" . date('Y-m-d H:i:s') . "] 开始刷新城市预计算表...\n";

$pdo = get_pdo();
$raceModel = new Race($pdo);

// 获取所有城市
$cities = $raceModel->getCities();
$cityList = array_column($cities, 'city');
$total = count($cityList);
echo "共 {$total} 个城市\n";

$start = microtime(true);
$success = 0;
$failed = 0;

// 预计算表名
$tPigeon = 'city_pigeon_rankings';
$tOwner  = 'city_owner_rankings';
$tLoft   = 'city_loft_rankings';

// 清空旧数据（保留表结构）
$pdo->exec("TRUNCATE TABLE {$tPigeon}");
$pdo->exec("TRUNCATE TABLE {$tOwner}");
$pdo->exec("TRUNCATE TABLE {$tLoft}");
echo "已清空旧数据\n";

// 使用预处理语句插入，避免 SQL 注入和类型错误
$insertPigeon = $pdo->prepare(
    "INSERT INTO {$tPigeon} (city, rank_pos, ring_number, owner_name, speed, `rank`, race_name, release_time, distance_km, loft_name, loft_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
);
$insertOwner = $pdo->prepare(
    "INSERT INTO {$tOwner} (city, rank_pos, owner_name, entry_count, top100_count, best_speed, avg_speed) VALUES (?, ?, ?, ?, ?, ?, ?)"
);
$insertLoft = $pdo->prepare(
    "INSERT INTO {$tLoft} (city, rank_pos, loft_id, loft_name, race_count, total_entries, top100_count, avg_speed, max_speed) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
);

foreach ($cityList as $idx => $city) {
    $i = $idx + 1;
    $t1 = microtime(true);
    
    try {
        // 速度鸽 TOP 10
        $pigeons = $raceModel->getCityTopSpeedPigeons($city, 10);
        foreach ($pigeons as $pos => $row) {
            $releaseTime = !empty($row['release_time']) && $row['release_time'] != '0000-00-00 00:00:00' ? $row['release_time'] : null;
            $insertPigeon->execute([
                $city,
                $pos + 1,
                $row['ring_number'] ?? '',
                $row['owner_name'] ?? '',
                $row['speed'] ?? 0,
                $row['rank'] ?? 0,
                $row['race_name'] ?? '',
                $releaseTime,
                $row['distance_km'] ?? 0,
                $row['loft_name'] ?? '',
                $row['loft_id'] ?? 0
            ]);
        }

        // 鸽主 TOP 10
        $owners = $raceModel->getCityTopOwners($city, 10);
        foreach ($owners as $pos => $row) {
            $insertOwner->execute([
                $city,
                $pos + 1,
                $row['owner_name'] ?? '',
                $row['entry_count'] ?? 0,
                $row['top100_count'] ?? 0,
                $row['best_speed'] ?? 0,
                $row['avg_speed'] ?? 0
            ]);
        }

        // 公棚 TOP 10
        $lofts = $raceModel->getCityTopLofts($city, 10);
        foreach ($lofts as $pos => $row) {
            $insertLoft->execute([
                $city,
                $pos + 1,
                $row['loft_id'] ?? 0,
                $row['loft_name'] ?? '',
                $row['race_count'] ?? 0,
                $row['total_entries'] ?? 0,
                $row['top100_count'] ?? 0,
                $row['avg_speed'] ?? 0,
                $row['max_speed'] ?? 0
            ]);
        }

        $elapsed = round((microtime(true) - $t1) * 1000);
        echo "[{$i}/{$total}] {$city}: OK ({$elapsed}ms)\n";
        $success++;
    } catch (\Throwable $e) {
        echo "[{$i}/{$total}] {$city}: FAIL - {$e->getMessage()}\n";
        $failed++;
    }
}

$totalTime = round(microtime(true) - $start, 1);
echo "\n完成！成功 {$success} 城市，失败 {$failed} 城市，耗时 {$totalTime}s\n";
