<?php
/**
 * 信鸽之家 - Sitemap 拆分生成器
 *
 * 生成结构：
 *   sitemap_index.xml      - 索引文件（提交给搜索引擎）
 *   sitemap_pages.xml      - 首页/列表页/文章/品系/工具/省市目录
 *   sitemap_pigeons.xml    - 铭鸽详情页
 *   sitemap_races.xml      - 赛事详情页/省份/城市/赛季
 *   sitemap_lofts.xml      - 公棚详情页/省份/城市目录
 *   sitemap_shops.xml      - 展厅详情页
 *
 * 用法：
 *   php sitemap.php --generate       # CLI 生成所有静态文件
 *   sitemap.php?generate=1           # Web 触发生成（需带密钥）
 *   sitemap.php?type=index           # 动态输出索引
 *   sitemap.php?type=pigeons         # 动态输出子 sitemap
 */

require_once __DIR__ . '/app/config/config.php';

$pdo = get_db_connection();
$site_url = 'https://www.xgjia.com';
$now = date('Y-m-d');

// 生成密钥（防止外部随意触发）
$SECRET = 'xgjia_sitemap_2026';

$is_cli = php_sapi_name() === 'cli';
$generate = ($is_cli && in_array('--generate', $argv ?? []))
         || (isset($_GET['generate']) && isset($_GET['key']) && $_GET['key'] === $SECRET);
$type = $_GET['type'] ?? null;

// ===== URL 数据采集（一次查询，分桶输出）=====

$buckets = [
    'pages'   => [],
    'pigeons' => [],
    'races'   => [],
    'lofts'   => [],
    'shops'   => [],
    'geo'    => [],
];

// ── pages 桶 ──

// 1. 首页
$buckets['pages'][] = url($site_url . '/', 'daily', '1.0');

// 2. 列表页
$list_pages = [
    '/pigeon/'  => ['daily',  '0.9'],
    '/race/'    => ['weekly', '0.8'],
    '/loft/'    => ['weekly', '0.8'],
    '/shop/'    => ['weekly', '0.8'],
    '/article/' => ['daily',  '0.8'],
    '/listing/' => ['daily',  '0.8'],
    '/dynamic/' => ['daily',  '0.7'],
    '/pedigree/'=> ['weekly', '0.7'],
    '/search'   => ['monthly','0.5'],
];
foreach ($list_pages as $path => $cfg) {
    $buckets['pages'][] = url($site_url . $path, $cfg[0], $cfg[1]);
}

// 3. 工具页
$tools = [
    '/tools/ring-guide/' => ['weekly', '0.6'],
    '/tools/top100/'     => ['weekly', '0.6'],
    '/tools/'            => ['weekly', '0.6'],
    '/race/search/'      => ['monthly', '0.4'],
];
foreach ($tools as $path => $cfg) {
    $buckets['pages'][] = url($site_url . $path, $cfg[0], $cfg[1]);
}


// 标签页
$buckets['pages'][] = url($site_url . '/tags/', 'weekly', '0.5');
$stmt = $pdo->query('SELECT t.slug, COUNT(at.article_id) as c FROM tags t LEFT JOIN article_tags at ON t.id = at.tag_id GROUP BY t.id ORDER BY c DESC LIMIT 100');
while ($row = $stmt->fetch()) {
    $buckets['pages'][] = url($site_url . '/tag/' . $row['slug'] . '/', 'weekly', '0.5');
}

// 4. 文章详情页
$stmt = $pdo->query('SELECT id, updated_at FROM articles WHERE status = 1 ORDER BY id DESC');
while ($row = $stmt->fetch()) {
    $lastmod = !empty($row['updated_at']) ? substr($row['updated_at'], 0, 10) : '';
    $buckets['pages'][] = url($site_url . '/article/' . $row['id'] . '.html', 'monthly', '0.6', $lastmod);
}

// 5. 分类信息详情页
$stmt = $pdo->query('SELECT id, updated_at FROM listings WHERE status = 1 ORDER BY id DESC');
while ($row = $stmt->fetch()) {
    $lastmod = !empty($row['updated_at']) ? substr($row['updated_at'], 0, 10) : '';
    $buckets['pages'][] = url($site_url . '/listing/' . $row['id'] . '.html', 'monthly', '0.6', $lastmod);
}

// 6. 品系页
$buckets['pages'][] = url($site_url . '/pedigree/', 'weekly', '0.7');
$stmt = $pdo->query("SELECT DISTINCT bloodline FROM pigeons WHERE bloodline IS NOT NULL AND bloodline != '' ORDER BY bloodline ASC");
while ($row = $stmt->fetch()) {
    $slug = urlencode($row['bloodline']);
    $buckets['pages'][] = url($site_url . '/pedigree/strain/' . $slug . '/', 'weekly', '0.5');
}

// 7. 赛事导航页
$race_nav = [
    '/race/champions/' => ['weekly', '0.7'],
    '/race/champion/'  => ['daily',  '0.7'],
    '/race/browse/'    => ['daily',  '0.7'],
    '/race/province/'  => ['weekly', '0.7'],
    '/race/city/'      => ['weekly', '0.7'],
];
foreach ($race_nav as $path => $cfg) {
    $buckets['pages'][] = url($site_url . $path, $cfg[0], $cfg[1]);
}

// 8. 省份赛事聚合
$stmt = $pdo->query(
    "SELECT DISTINCT l.province, MAX(r.release_time) as latest
     FROM races r LEFT JOIN lofts l ON r.loft_id = l.id
     WHERE r.status = 1 AND l.province IS NOT NULL AND l.province != ''
     GROUP BY l.province"
);
while ($row = $stmt->fetch()) {
    $lastmod = !empty($row['latest']) ? substr($row['latest'], 0, 10) : '';
    $buckets['pages'][] = url($site_url . '/race/province/' . urlencode($row['province']) . '/', 'weekly', '0.6', $lastmod);
}

// 9. 城市赛事聚合
$stmt = $pdo->query(
    "SELECT DISTINCT l.city, MAX(r.release_time) as latest
     FROM races r JOIN lofts l ON r.loft_id = l.id
     WHERE r.status = 1 AND l.city IS NOT NULL AND l.city != ''
     GROUP BY l.city"
);
while ($row = $stmt->fetch()) {
    $lastmod = !empty($row['latest']) ? substr($row['latest'], 0, 10) : '';
    $buckets['pages'][] = url($site_url . '/race/city/' . urlencode($row['city']) . '/', 'weekly', '0.6', $lastmod);
    $buckets['pages'][] = url($site_url . '/race/city/' . urlencode($row['city']) . '/top/', 'weekly', '0.7', $lastmod);
}

// 9.5 GeoHub 省份页
$stmt = $pdo->query(
    "SELECT DISTINCT province FROM lofts WHERE status = 1 AND province IS NOT NULL AND province != '' ORDER BY province"
);
while ($row = $stmt->fetch()) {
    $buckets['pages'][] = url($site_url . '/geohub/province/' . urlencode($row['province']) . '/', 'weekly', '0.7');
}

// 9.6 GeoHub 城市页
$stmt = $pdo->query(
    "SELECT DISTINCT city FROM lofts WHERE status = 1 AND city IS NOT NULL AND city != '' ORDER BY city"
);
while ($row = $stmt->fetch()) {
    $buckets['pages'][] = url($site_url . '/geohub/city/' . urlencode($row['city']) . '/', 'weekly', '0.7');
}

// 10. 赛季总结页
$stmt = $pdo->query(
    "SELECT DISTINCT season_year, MAX(release_time) as latest
     FROM races WHERE status = 1 AND season_year IS NOT NULL AND season_year != ''
     GROUP BY season_year ORDER BY season_year DESC"
);
while ($row = $stmt->fetch()) {
    $lastmod = !empty($row['latest']) ? substr($row['latest'], 0, 10) : '';
    $buckets['pages'][] = url($site_url . '/race/season/' . $row['season_year'] . '/', 'weekly', '0.6', $lastmod);
}

// 11. 省份公棚目录
$stmt = $pdo->query(
    "SELECT DISTINCT province FROM lofts WHERE status = 1 AND province IS NOT NULL AND province != '' ORDER BY province"
);
while ($row = $stmt->fetch()) {
    $buckets['pages'][] = url($site_url . '/loft/province/' . urlencode($row['province']) . '/', 'weekly', '0.6');
}

// 12. 城市公棚目录
$stmt = $pdo->query(
    "SELECT DISTINCT city FROM lofts WHERE status = 1 AND city IS NOT NULL AND city != '' ORDER BY city"
);
while ($row = $stmt->fetch()) {
    $buckets['pages'][] = url($site_url . '/loft/city/' . urlencode($row['city']) . '/', 'weekly', '0.6');
}

// ── pigeons 桶 ──

$stmt = $pdo->query('SELECT id, updated_at FROM pigeons ORDER BY id DESC');
while ($row = $stmt->fetch()) {
    $lastmod = !empty($row['updated_at']) ? substr($row['updated_at'], 0, 10) : '';
    $buckets['pigeons'][] = url($site_url . '/pigeon/' . $row['id'] . '.html', 'monthly', '0.6', $lastmod);
}

// ── races 桶 ──

$stmt = $pdo->query('SELECT id, release_time, season_year FROM races ORDER BY id DESC');
while ($row = $stmt->fetch()) {
    $lastmod = !empty($row['release_time']) ? substr($row['release_time'], 0, 10) : '';
    $isCurrent = ($row['season_year'] ?? 0) >= 2026;
    $buckets['races'][] = url(
        $site_url . '/race/' . $row['id'] . '.html',
        $isCurrent ? 'weekly' : 'monthly',
        '0.6',
        $lastmod
    );
}

// ── lofts 桶 ──

$stmt = $pdo->query('SELECT id, updated_at FROM lofts ORDER BY id DESC');
while ($row = $stmt->fetch()) {
    $lastmod = !empty($row['updated_at']) ? substr($row['updated_at'], 0, 10) : '';
    $buckets['lofts'][] = url($site_url . '/loft/' . $row['id'] . '.html', 'monthly', '0.6', $lastmod);
}

// ── geo sitemap 桶（公棚坐标）──
$stmt_geo = $pdo->query("
    SELECT id, name, lat, lng, updated_at
    FROM lofts
    WHERE status = 1
      AND lat IS NOT NULL AND lat != ''
      AND lng IS NOT NULL AND lng != ''
      AND lat != '0' AND lng != '0'
    ORDER BY id DESC
");
while ($row = $stmt_geo->fetch()) {
    $lat = trim($row['lat']);
    $lng = trim($row['lng']);
    if (!is_numeric($lat) || !is_numeric($lng)) continue;
    $lat_f = floatval($lat);
    $lng_f = floatval($lng);
    if ($lat_f < -90 || $lat_f > 90 || $lng_f < -180 || $lng_f > 180) continue;
    $lastmod = !empty($row['updated_at']) ? substr($row['updated_at'], 0, 10) : '';
    $buckets['geo'][] = [
        'loc'      => $site_url . '/loft/' . $row['id'] . '.html',
        'lat'      => $lat_f,
        'lng'      => $lng_f,
        'lastmod'  => $lastmod,
        'priority' => '0.7',
    ];
}

// ── shops 桶 ──

$stmt = $pdo->query('SELECT id, updated_at FROM shops WHERE status = 1 ORDER BY id DESC');
while ($row = $stmt->fetch()) {
    $lastmod = !empty($row['updated_at']) ? substr($row['updated_at'], 0, 10) : '';
    $buckets['shops'][] = url($site_url . '/shop/' . $row['id'] . '.html', 'monthly', '0.6', $lastmod);
}

// ===== 辅助函数 =====

function url($loc, $changefreq, $priority, $lastmod = '') {
    return compact('loc', 'changefreq', 'priority', 'lastmod');
}

function build_xml($urls) {
    $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "
";
    foreach ($urls as $u) {
        $xml .= "  <url>
";
        $xml .= '    <loc>' . htmlspecialchars($u['loc'], ENT_XML1, 'UTF-8') . "</loc>
";
        if (!empty($u['lastmod'])) {
            $xml .= '    <lastmod>' . $u['lastmod'] . "</lastmod>
";
        }
        $xml .= '    <changefreq>' . $u['changefreq'] . "</changefreq>
";
        $xml .= '    <priority>' . $u['priority'] . "</priority>
";
        $xml .= "  </url>
";
    }
    $xml .= "</urlset>
";
    return $xml;
}

function build_geo_xml($urls, $site_url) {
    $now = date('Y-m-d');
    $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:geo="http://www.google.com/geo/schemas/sitemap/1.0">' . "
";
    $xml .= "  <sitemap>
";
    $xml .= '    <loc>' . $site_url . '/sitemap_geo.xml</loc>' . "
";
    $xml .= '    <lastmod>' . $now . "</lastmod>
";
    $xml .= "  </sitemap>
";
    foreach ($urls as $u) {
        $xml .= "  <url>
";
        $xml .= '    <loc>' . htmlspecialchars($u['loc'], ENT_XML1, 'UTF-8') . "</loc>
";
        $xml .= '    <lastmod>' . ($u['lastmod'] ?: $now) . "</lastmod>
";
        $xml .= '    <priority>' . $u['priority'] . "</priority>
";
        $xml .= "    <geo:geo>
";
        $xml .= '      <geo:lat>' . $u['lat'] . "</geo:lat>
";
        $xml .= '      <geo:lng>' . $u['lng'] . "</geo:lng>
";
        $xml .= "    </geo:geo>
";
        $xml .= "  </url>
";
    }
    $xml .= "</urlset>
";
    return $xml;
}

function build_index($groups, $site_url) {
    $now = date('Y-m-d');
    $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
";
    $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "
";
    $names = ['pages', 'pigeons', 'races', 'lofts', 'shops', 'geo'];
    foreach ($names as $name) {
        if ($name === 'geo') continue;
        $count = isset($groups[$name]) ? count($groups[$name]) : 0;
        $xml .= "  <sitemap>
";
        $xml .= '    <loc>' . $site_url . '/sitemap_' . $name . '.xml</loc>' . "
";
        $xml .= '    <lastmod>' . $now . "</lastmod>
";
        $xml .= "  </sitemap>
";
    }
    if (!empty($groups['geo'])) {
        $xml .= "  <sitemap>
";
        $xml .= '    <loc>' . $site_url . '/sitemap_geo.xml</loc>' . "
";
        $xml .= '    <lastmod>' . $now . "</lastmod>
";
        $xml .= "  </sitemap>
";
    }
    $xml .= "</sitemapindex>
";
    return $xml;
}

// ===== 输出 =====

$all_names = ['pages', 'pigeons', 'races', 'lofts', 'shops', 'geo'];

if ($generate) {
    // ── 写入所有静态文件 ──
    $base = __DIR__;
    $results = [];
    $total_urls = 0;

    foreach ($all_names as $name) {
        if ($name === 'geo') continue; // geo handled separately by build_geo_xml
        $count = count($buckets[$name]);
        $total_urls += $count;
        $xml = build_xml($buckets[$name]);
        $path = $base . '/sitemap_' . $name . '.xml';
        $bytes = file_put_contents($path, $xml);
        $results[$name] = ['path' => $path, 'urls' => $count, 'bytes' => $bytes, 'ok' => $bytes !== false];
    }

    // 写入 geo sitemap
    if (!empty($buckets['geo'])) {
        $geo_xml = build_geo_xml($buckets['geo'], $site_url);
        $geo_path = $base . '/sitemap_geo.xml';
        $geo_bytes = file_put_contents($geo_path, $geo_xml);
        $results['geo'] = ['path' => $geo_path, 'urls' => count($buckets['geo']), 'bytes' => $geo_bytes, 'ok' => $geo_bytes !== false];
    } else {
        $results['geo'] = ['path' => $base . '/sitemap_geo.xml', 'urls' => 0, 'bytes' => 0, 'ok' => false];
    }

    // 写入索引
    $index_xml = build_index($buckets, $site_url);
    $index_path = $base . '/sitemap_index.xml';
    $index_bytes = file_put_contents($index_path, $index_xml);
    $results['index'] = ['path' => $index_path, 'bytes' => $index_bytes, 'ok' => $index_bytes !== false];

    if ($is_cli) {
        echo "============ Sitemap 拆分生成 ============
";
        foreach ($all_names as $name) {
            $r = $results[$name];
            $status = $r['ok'] ? '✅' : '❌';
            echo "{$status} sitemap_{$name}.xml  → {$r['urls']} URLs, " . number_format($r['bytes']) . " bytes
";
        }
        $status = $results['index']['ok'] ? '✅' : '❌';
        echo "{$status} sitemap_index.xml   → " . number_format($results['index']['bytes']) . " bytes
";
        echo "============================================
";
        echo "总计: {$total_urls} URLs 拆分到 " . count($all_names) . " 个子 sitemap
";
        echo "提交给搜索引擎: {$site_url}/sitemap_index.xml
";
    } else {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => true, 'total_urls' => $total_urls, 'files' => $results]);
    }
} elseif ($type && in_array($type, array_merge($all_names, ['index']))) {
    // ── 动态输出指定类型 ──
    header('Content-Type: application/xml; charset=utf-8');
    if ($type === 'index') {
        echo build_index($buckets, $site_url);
    } elseif ($type === 'geo') {
        echo build_geo_xml($buckets['geo'] ?: [], $site_url);
    } else {
        echo build_xml($buckets[$type]);
    }
} else {
    // ── 默认输出整个 sitemap（兼容旧版）──
    $all_urls = array_merge(
        $buckets['pages'],
        $buckets['pigeons'],
        $buckets['races'],
        $buckets['lofts'],
        $buckets['shops']
    );
    header('Content-Type: application/xml; charset=utf-8');
    echo build_xml($all_urls);
}
