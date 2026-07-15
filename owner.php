<?php
/**
 * 鸽主专辑页入口 /page/owner/{name}
 */
require_once __DIR__ . '/app/bootstrap.php';

$ownerName = rtrim(trim($_GET['name'] ?? ''), '/');
if (!$ownerName) {
    header('Location: /race/');
    exit;
}

$pdo = get_pdo();
$raceModel = new Race($pdo);

$page = max(1, intval($_GET['page'] ?? 1));
$stats = $raceModel->getOwnerStats($ownerName);
$results = $raceModel->getResultsByOwner($ownerName, $page, 20);

// 提取冠军成绩（前3名）
$topResults = [];
if (!empty($results['list'])) {
    foreach ($results['list'] as $r) {
        if (intval($r['rank'] ?? 0) <= 3) {
            $topResults[] = $r;
        }
    }
    // 按名次升序
    usort($topResults, function ($a, $b) {
        return ($a['rank'] ?? 999) - ($b['rank'] ?? 999);
    });
}

// 按赛季分组
$seasonGroups = [];
foreach ($results['list'] as $r) {
    $key = ($r['season_year'] ?? '未知') . '-' . ($r['season_type'] ?? '未知');
    $seasonGroups[$key][] = $r;
}

$total = $results['total'] ?? 0;
$totalPages = $results['total_pages'] ?? 1;

require __DIR__ . '/views/owner_page.php';
