<?php
/**
 * 信鸽之家 - 智能搜索入口
 *
 * 根据输入内容自动识别类型：
 * - 足环号（含 CHN/数字-数字模式） → 调用赛事成绩匹配
 * - 公棚名称                       → 搜索公棚库
 * - 鸽主姓名                       → 搜索鸽主成绩
 * - 找不到                         → 显示无结果 + 搜索建议
 */

require_once __DIR__ . '/app/bootstrap.php';

$q = trim(urldecode($_GET['q'] ?? $_POST['q'] ?? ''));

if (empty($q)) {
    header('Location: /');
    exit;
}

$pdo = get_pdo();
require_once __DIR__ . '/app/models/Race.php';
require_once __DIR__ . '/app/models/Loft.php';

$raceModel = new Race($pdo);
$loftModel = new Loft($pdo);

$results = [
    'query'    => $q,
    'type'     => 'all',
    'ring'     => null,
    'lofts'    => [],
    'owners'   => [],
];

// ----------------------------------------------------------------
// 1. 足环号检测：包含 CHN / 年-区号 / 多位数字+连字符
// ----------------------------------------------------------------
$ringPatterns = [
    '/CHN/i',
    '/\d{4}[\s\-]?\d{2}[\s\-]?\d{4,}/',
    '/\d{2}[\s\-]?\d{4,}/',
];
$isRing = false;
foreach ($ringPatterns as $pat) {
    if (preg_match($pat, $q)) { $isRing = true; break; }
}
if ($isRing) {
    $ringResults = $raceModel->getResultsByRing($q);
    if (!empty($ringResults)) {
        $results['ring'] = $ringResults;
        $results['type'] = 'ring';
    }
    // 即使匹配失败也先跳环号页面，让用户看无结果提示
    if ($results['type'] === 'ring' || $isRing) {
        header('Location: /race/ring/' . urlencode($q) . '/');
        exit;
    }
}

// ----------------------------------------------------------------
// 2. 公棚名称搜索
// ----------------------------------------------------------------
$lofts = $loftModel->getList(['keyword' => $q, 'limit' => 12, 'status' => 1]);
$results['lofts'] = $lofts;

// ----------------------------------------------------------------
// 3. 鸽主姓名搜索（从 race_results 表，1270万行，两段优化）
//    阶段1: LIKE 'xxx%' 前缀匹配 → 利用 idx_owner_name 索引，毫秒级
//    阶段2: LIKE '%xxx%' 全模糊 fallback → 全表扫但有 LIMIT 12 兜底
// ----------------------------------------------------------------
$safeQ = addcslashes($q, '%_');
try {
    // 阶段1：前缀匹配（走索引）
    $stmt = $pdo->prepare(
        "SELECT DISTINCT owner_name FROM race_results
         WHERE owner_name LIKE :prefix AND owner_name != ''
         ORDER BY owner_name LIMIT 12"
    );
    $stmt->execute([':prefix' => $safeQ . '%']);
    $results['owners'] = $stmt->fetchAll(\PDO::FETCH_COLUMN);

    // 阶段2：前缀无结果时兜底全模糊
    if (empty($results['owners'])) {
        $stmt = $pdo->prepare(
            "SELECT DISTINCT owner_name FROM race_results
             WHERE owner_name LIKE :contains AND owner_name != ''
             ORDER BY owner_name LIMIT 12"
        );
        $stmt->execute([':contains' => '%' . $safeQ . '%']);
        $results['owners'] = $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }
} catch (Exception $e) {
    $results['owners'] = [];
}

// 判定：如果只有一种结果，直接跳转
if ($results['type'] === 'ring' && !empty($results['ring'])) {
    header('Location: /race/ring/' . urlencode($q) . '/');
    exit;
}

// Option: 如果只有一个匹配的公棚 → 直接跳到公棚详情
if (empty($results['ring']) && count($results['lofts']) === 1 && empty($results['owners'])) {
    header('Location: /loft/' . $results['lofts'][0]['id'] . '.html');
    exit;
}

// 否则：显示综合结果页
$page_title = '搜索: ' . htmlspecialchars($q) . ' - 信鸽之家';

require __DIR__ . '/views/search_results.php';
