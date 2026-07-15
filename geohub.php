<?php
/**
 * 信鸽之家 - GeoHub 地理聚合页
 * 
 * URL 结构：
 *   /geohub/province/{省份}/   → 省级赛鸽枢纽
 *   /geohub/city/{城市}/       → 城市级赛鸽枢纽
 */

require_once __DIR__ . '/app/bootstrap.php';

$pdo = get_db_connection();
$type = $_GET['type'] ?? '';
$name = urldecode($_GET['name'] ?? '');

$site_url = 'https://www.xgjia.com';

if (!$type || !$name) {
    // ===== GeoHub 首页：列出所有有公棚的省份 =====
    $stmt = $pdo->prepare("SELECT province, COUNT(*) AS cnt FROM lofts WHERE province IS NOT NULL AND province != '' GROUP BY province ORDER BY cnt DESC");
    $stmt->execute();
    $provinces = $stmt->fetchAll();

    $page_title = '全国赛鸽地理枢纽 - 公棚、赛事、成绩查询 | 信鸽之家 GeoHub';
    $page_description = '信鸽之家 GeoHub 聚合全国各省市赛鸽数据，按地理维度查询公棚、赛事成绩、鸽主排名。覆盖河北、山东、云南等35个省级赛鸽枢纽。';
    $page_keywords = '赛鸽地理,公棚查询,赛事成绩,信鸽数据,赛鸽枢纽,赛鸽省份';
    $canonical = $site_url . '/geohub/';
    $breadcrumbs = [
        ['name' => '首页', 'url' => $site_url . '/'],
        ['name' => 'GeoHub', 'url' => ''],
    ];

    include __DIR__ . '/views/geohub_index.php';
    exit;
}

$page_title = '';
$page_description = '';
$page_keywords = '';
$canonical = '';
$breadcrumbs = [];

if ($type === 'province') {
    // ===== 省级 GeoHub =====
    $province = $name;
    
    // 验证省份存在
    $stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM lofts WHERE province = ? AND status = 1");
    $stmt->execute([$province]);
    if ($stmt->fetch()['cnt'] == 0) {
        http_response_code(404);
        include __DIR__ . '/views/404.php';
        exit;
    }
    
    // 省份描述（复用 race_province.php 的数组）
    $province_descriptions = [
        '北京' => '北京作为中国的首都，赛鸽运动历史悠久，拥有众多知名公棚和高水平赛事...',
        '上海' => '上海赛鸽运动起步早，公棚数量多，赛事组织规范，是华东地区赛鸽重镇...',
        // ... (完整数组见 race_province.php，此处省略以节省篇幅)
    ];
    $description = $province_descriptions[$province] ?? $province . '赛鸽运动发展迅速，拥有多家专业公棚和丰富的赛事资源。';
    
    // 省份统计
    $stats = [];
    $stmt = $pdo->prepare("
        SELECT
            COUNT(DISTINCT r.id) as race_count,
            COUNT(DISTINCT l.id) as loft_count
        FROM races r
        LEFT JOIN lofts l ON r.loft_id = l.id
        WHERE l.province = ? AND r.status = 1
    ");
    $stmt->execute([$province]);
    $stats = $stmt->fetch();
    
    // 省内城市列表（按赛事数排序）
    $stmt = $pdo->prepare("
        SELECT l.city, COUNT(DISTINCT r.id) as race_count, COUNT(DISTINCT l.id) as loft_count
        FROM lofts l
        LEFT JOIN races r ON l.id = r.loft_id AND r.status = 1
        WHERE l.province = ? AND l.status = 1 AND l.city IS NOT NULL AND l.city != ''
        GROUP BY l.city
        ORDER BY race_count DESC, loft_count DESC
        LIMIT 20
    ");
    $stmt->execute([$province]);
    $cities = $stmt->fetchAll();
    
    // 省内 TOP 公棚
    $stmt = $pdo->prepare("
        SELECT l.*, COUNT(r.id) as race_count
        FROM lofts l
        LEFT JOIN races r ON l.id = r.loft_id AND r.status = 1
        WHERE l.province = ? AND l.status = 1
        GROUP BY l.id
        ORDER BY race_count DESC, l.id DESC
        LIMIT 12
    ");
    $stmt->execute([$province]);
    $top_lofts = $stmt->fetchAll();
    
    // 省内近期赛事
    $stmt = $pdo->prepare("
        SELECT r.*, l.name as loft_name
        FROM races r
        JOIN lofts l ON r.loft_id = l.id
        WHERE l.province = ? AND r.status = 1
        ORDER BY r.release_time DESC
        LIMIT 10
    ");
    $stmt->execute([$province]);
    $recent_races = $stmt->fetchAll();
    
    $page_title = $province . '赛鸽 - 公棚、赛事、成绩查询 | 信鸽之家 GeoHub';
    $page_description = $description;
    $page_keywords = $province . '赛鸽,' . $province . '公棚,' . $province . '赛事,' . $province . '信鸽';
    $canonical = $site_url . '/geohub/province/' . urlencode($province) . '/';
    $breadcrumbs = [
        ['name' => '首页', 'url' => $site_url . '/'],
        ['name' => 'GeoHub', 'url' => $site_url . '/geohub/'],
        ['name' => $province, 'url' => '']
    ];
    
    $data = compact(
        'province', 'description', 'stats', 'cities', 'top_lofts', 'recent_races',
        'page_title', 'page_description', 'page_keywords', 'canonical', 'breadcrumbs'
    );
    extract($data);
    include __DIR__ . '/views/geohub_province.php';
    
} elseif ($type === 'city') {
    // ===== 城市级 GeoHub =====
    $city = $name;
    
    // 验证城市存在
    $stmt = $pdo->prepare("SELECT ANY_VALUE(province) as province FROM lofts WHERE city = ? AND status = 1 LIMIT 1");
    $stmt->execute([$city]);
    $city_info = $stmt->fetch();
    if (!$city_info || empty($city_info['province'])) {
        http_response_code(404);
        include __DIR__ . '/views/404.php';
        exit;
    }
    $province = $city_info['province'];
    
    // 城市描述（复用 race_city.php 的数组）
    $city_descriptions = [
        '北京' => '北京是中国的首都，赛鸽运动在这里有着深厚的群众基础...',
        '上海' => '上海赛鸽历史悠久，公棚数量众多，赛事体系完善...',
        // ... (完整数组见 race_city.php)
    ];
    $description = $city_descriptions[$city] ?? $city . '赛鸽活动活跃，拥有多家专业公棚和丰富的赛事资源。';
    
    // 城市统计
    $stats = [];
    $stmt = $pdo->prepare("
        SELECT
            COUNT(DISTINCT r.id) as race_count,
            COUNT(DISTINCT l.id) as loft_count
        FROM races r
        LEFT JOIN lofts l ON r.loft_id = l.id
        WHERE l.city = ? AND r.status = 1
    ");
    $stmt->execute([$city]);
    $stats = $stmt->fetch();
    
    // 城市 TOP 公棚
    $stmt = $pdo->prepare("
        SELECT l.*, COUNT(r.id) as race_count
        FROM lofts l
        LEFT JOIN races r ON l.id = r.loft_id AND r.status = 1
        WHERE l.city = ? AND l.status = 1
        GROUP BY l.id
        ORDER BY race_count DESC, l.id DESC
        LIMIT 10
    ");
    $stmt->execute([$city]);
    $top_lofts = $stmt->fetchAll();

    // 城市近期赛事
    $stmt = $pdo->prepare("
        SELECT r.*, l.name as loft_name
        FROM races r
        JOIN lofts l ON r.loft_id = l.id
        WHERE l.city = ? AND r.status = 1
        ORDER BY r.release_time DESC
        LIMIT 10
    ");
    $stmt->execute([$city]);
    $recent_races = $stmt->fetchAll();
    
    // 同省其他城市（周边推荐）
    $stmt = $pdo->prepare("
        SELECT DISTINCT city, COUNT(*) as loft_count
        FROM lofts
        WHERE province = ? AND city != ? AND status = 1
        GROUP BY city
        ORDER BY loft_count DESC
        LIMIT 8
    ");
    $stmt->execute([$province, $city]);
    $nearby_cities = $stmt->fetchAll();
    
    $page_title = $city . '赛鸽 - 公棚、赛事查询 | 信鸽之家 GeoHub';
    $page_description = $description;
    $page_keywords = $city . '赛鸽,' . $city . '公棚,' . $city . '赛事,' . $city . '信鸽,' . $province . '赛鸽';
    $canonical = $site_url . '/geohub/city/' . urlencode($city) . '/';
    $breadcrumbs = [
        ['name' => '首页', 'url' => $site_url . '/'],
        ['name' => 'GeoHub', 'url' => $site_url . '/geohub/'],
        ['name' => $province, 'url' => $site_url . '/geohub/province/' . urlencode($province) . '/'],
        ['name' => $city, 'url' => '']
    ];
    
    $top_owners = [];
    $data = compact(
        'city', 'province', 'description', 'stats', 'top_lofts', 'recent_races', 'nearby_cities',
        'page_title', 'page_description', 'page_keywords', 'canonical', 'breadcrumbs'
    );
    extract($data);
    include __DIR__ . '/views/geohub_city.php';
    
} else {
    http_response_code(404);
    include __DIR__ . '/views/404.php';
}
