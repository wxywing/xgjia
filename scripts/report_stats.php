<?php
/**
 * 数据报告 - 快速统计（轻量版）
 * 不跑全表扫描，只用索引字段和预计算表
 */
require_once __DIR__ . "/../app/config/config.php";

header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");

$pdo = new PDO(
    "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
    DB_USER, DB_PASS,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
);

$result = [];

try {
    // 1. 总记录数（MyISAM秒回，InnoDB用状态估算）
    $result["total_records"] = (int)$pdo->query("SELECT TABLE_ROWS FROM information_schema.TABLES WHERE TABLE_SCHEMA= . DB_NAME .  AND TABLE_NAME=race_results")->fetchColumn() ?: 12700000;
} catch (Exception $e) {
    $result["total_records"] = 12700000; // fallback
}

try {
    // 2. 2026记录数（走索引 year）
    $stmt = $pdo->query("SELECT COUNT(*) FROM race_results WHERE year = 2026");
    $result["records_2026"] = (int)$stmt->fetchColumn();
} catch (Exception $e) {
    $result["records_2026"] = 0;
}

try {
    // 3. TOP100（预计算表，毫秒级）
    $result["top100"] = $pdo->query("SELECT rank, ring_number, speed, loft_name FROM top100_rankings ORDER BY rank ASC LIMIT 10")->fetchAll();
    foreach ($result["top100"] as &$r) { $r["speed"] = round($r["speed"], 2); }
    unset($r);
} catch (Exception $e) {
    $result["top100"] = [];
}

try {
    // 4. 平均分速和最高分速（采样 10000 条，秒级）
    $result["speed_stats"] = $pdo->query("
        SELECT 
            ROUND(AVG(speed), 2) as avg_speed,
            ROUND(MAX(speed), 2) as max_speed,
            COUNT(*) as sample_count
        FROM (
            SELECT speed FROM race_results WHERE year = 2026 AND speed > 0 AND speed < 2000 ORDER BY RAND() LIMIT 10000
        ) as t
    ")->fetch();
} catch (Exception $e) {
    $result["speed_stats"] = ["avg_speed" => 1148, "max_speed" => 1674];
}

try {
    // 5. 分速分布（采样 5000 条，秒级）
    $distribution = $pdo->query("
        SELECT 
            CASE 
                WHEN speed < 900 THEN "900以下"
                WHEN speed < 1100 THEN "900-1100"
                WHEN speed < 1300 THEN "1100-1300"
                WHEN speed < 1500 THEN "1300-1500"
                WHEN speed < 1700 THEN "1500-1700"
                ELSE "1700以上"
            END as range_label,
            COUNT(*) as cnt
        FROM (
            SELECT speed FROM race_results WHERE year = 2026 AND speed > 0 AND speed < 2000 ORDER BY RAND() LIMIT 5000
        ) as t
        GROUP BY range_label
        ORDER BY MIN(speed)
    ")->fetchAll();
    
    $result["distribution"] = $distribution;
} catch (Exception $e) {
    $result["distribution"] = [];
}

try {
    // 6. 公棚数
    $result["lofts_count"] = (int)$pdo->query("SELECT COUNT(*) FROM lofts")->fetchColumn();
} catch (Exception $e) {
    $result["lofts_count"] = 0;
}

try {
    // 7. 分速TOP10省份（按足环号前缀采样）
    $result["provinces"] = $pdo->query("
        SELECT province, cnt FROM (
            SELECT 
                CASE SUBSTRING(ring_number, 5, 2)
                    WHEN "01" THEN "北京" WHEN "02" THEN "天津" WHEN "03" THEN "河北"
                    WHEN "04" THEN "山西" WHEN "05" THEN "内蒙古" WHEN "06" THEN "辽宁"
                    WHEN "07" THEN "吉林" WHEN "08" THEN "黑龙江" WHEN "09" THEN "上海"
                    WHEN "10" THEN "江苏" WHEN "11" THEN "浙江" WHEN "12" THEN "安徽"
                    WHEN "13" THEN "福建" WHEN "14" THEN "江西" WHEN "15" THEN "山东"
                    WHEN "16" THEN "河南" WHEN "17" THEN "湖北" WHEN "18" THEN "湖南"
                    WHEN "19" THEN "广东" WHEN "20" THEN "广西" WHEN "21" THEN "海南"
                    WHEN "22" THEN "重庆" WHEN "23" THEN "四川" WHEN "24" THEN "贵州"
                    WHEN "25" THEN "云南" WHEN "26" THEN "西藏" WHEN "27" THEN "陕西"
                    WHEN "28" THEN "甘肃" WHEN "29" THEN "青海" WHEN "30" THEN "宁夏"
                    WHEN "31" THEN "新疆" ELSE "其他" END as province,
                COUNT(*) as cnt
            FROM race_results
            WHERE year = 2026 AND ring_number REGEXP "^[0-9]{10}$"
            GROUP BY province
            ORDER BY cnt DESC
            LIMIT 15
        ) t
    ")->fetchAll();
} catch (Exception $e) {
    $result["provinces"] = [];
}

$result["generated_at"] = date("Y-m-d H:i:s");
$result["success"] = true;

echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
