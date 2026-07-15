<?php
/**
 * 信鸽之家 - 赛事成绩入口
 * 
 * SEO URL: /race/              → 赛事成绩首页
 *           /race/123.html     → 赛事详情
 *           /race/ring/CHN.../ → 足环号时间线
 *           /race/loft/123/    → 某公棚赛事列表
 *           /race/search       → 足环号追溯
 *           /race/champions    → 冠军榜
 *           /race/province/    → 省份聚合首页
 *           /race/province/河北/ → 某省份赛事列表
 */

require_once __DIR__ . '/app/bootstrap.php';

$controller = controller('RaceController');

$action = $_GET['action'] ?? '';
$id = intval($_GET['id'] ?? 0);
$loftId = intval($_GET['loft_id'] ?? 0);
$ring = $_GET['ring'] ?? '';

if ($id) {
    // /race/123.html → 赛事详情 + 成绩表
    $controller->detail();
} elseif ($loftId) {
    // /race/loft/123/ → 某公棚所有赛事
    $controller->byLoft();
} elseif ($action === 'ring') {
    // /race/ring/CHN.../ → 足环号跨赛季时间线
    $controller->ring();
} elseif ($action === 'search') {
    // /race/search?ring=XXX → 足环号跨公棚追溯（旧）
    $controller->search();
} elseif ($action === 'champions') {
    // /race/champions → 冠军榜
    $controller->champions();
} elseif ($action === 'province') {
    // /race/province/ → 省份聚合 /race/province/河北/
    $_GET['province'] = $_GET['province'] ?? '';
    $controller->byProvince();
} elseif ($action === 'champion') {
    // P1: /race/champion/ → 冠军鸽列表
    $controller->championIndex();
} elseif ($action === 'city') {
    // P1: /race/city/ → 城市赛事中心 /race/city/北京/
    $_GET['city'] = $_GET['city'] ?? '';
    $controller->byCity();
} elseif ($action === 'cityTop') {
    // A方案GEO SEO: /race/city/北京/top/ → 城市赛事TOP排行榜
    $_GET['city'] = $_GET['city'] ?? '';
    $controller->cityTop();
} elseif ($action === 'season') {
    // P2: /race/season/2026/ → 赛季总结
    $_GET['season_year'] = $_GET['season_year'] ?? '';
    $controller->seasonDetail();
} elseif ($action === 'browse') {
    // P2: /race/browse/ → 赛事浏览大全
    $controller->browse();
} elseif ($action === 'report') {
    // /race/report/CHN.../ → 足环号深度查询报告
    $controller->report();
} else {
    // /race/ → 赛事成绩聚合首页
    $controller->index();
}
