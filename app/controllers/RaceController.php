<?php
/**
 * 赛事成绩控制器
 */
require_once __DIR__ . '/../models/Race.php';
require_once __DIR__ . '/../models/Loft.php';
require_once __DIR__ . '/../core/Controller.php';

class RaceController extends Controller
{
    private $race;
    private $loft;

    public function __construct($db)
    {
        parent::__construct($db);
        $this->race = new Race($db);
        $this->loft = new Loft($db);
    }

    /**
     * 赛事成绩聚合首页
     */
    public function index()
    {
        $page = max(1, intval($_GET['page'] ?? 1));
        $loftPage = max(1, intval($_GET['lp'] ?? 1));
        $yearParam = $_GET['year'] ?? '2026';  // 默认 2026 赛季
        $year = ($yearParam === 'all') ? '' : $yearParam;
        $type = $_GET['type'] ?? '';
        $keyword = $_GET['q'] ?? '';

        $stats = $this->race->getStats();
        $seasons = $this->race->getSeasons();
        $recentRaces = $this->race->getList($page, 10, $year, $type, $keyword);
        $champions = $this->race->getChampions(8);
        $loftRaces = $this->race->getLoftsWithLatestRace($loftPage, 20);

        require __DIR__ . '/../../views/races.php';
    }

    /**
     * 赛事详情 + 成绩表 /race/123.html
     */
    public function detail()
    {
        $id = intval($_GET['id'] ?? 0);
        if (!$id) {
            http_response_code(404);
            require __DIR__ . '/../../views/404.php';
            return;
        }

        $page = max(1, intval($_GET['page'] ?? 1));
        $keyword = $_GET['q'] ?? '';

        // 缓存：不同 id + page + keyword 分别缓存
        $cacheKey = 'race_detail_' . $id . '_p' . $page . '_' . md5($keyword);
        $cached = $this->cache()->get($cacheKey);
        if ($cached !== null) {
            echo $cached;
            return;
        }

        $race = $this->race->getDetail($id);
        if (!$race) {
            http_response_code(404);
            require __DIR__ . '/../../views/404.php';
            return;
        }

        ob_start();

        $page = max(1, intval($_GET['page'] ?? 1));
        $keyword = $_GET['q'] ?? '';
        $results = $this->race->getResults($id, $page, 50, $keyword);

        // 数据融合 Phase 3：为每条成绩查找关联的铭鸽
        require_once __DIR__ . '/../models/Pigeon.php';
        require_once __DIR__ . '/../core/RingNormalizer.php';
        $pigeonModel = new Pigeon($this->pdo);
        
        foreach ($results['list'] as &$result) {
            $ringNumber = $result['ring_number'] ?? '';
            if ($ringNumber) {
                $pigeon = $pigeonModel->findByRingNumber($ringNumber);
                if ($pigeon) {
                    $result['pigeon_id'] = $pigeon['id'];
                    $result['pigeon_name'] = $pigeon['name'];
                }
            }
        }
        unset($result); // Break the reference

        // Phase 2 SEO: load other races from same loft for cross-links
        $otherRaces = $this->race->getByLoftId($race['loft_id']);
        $otherRaces = array_values(array_filter($otherRaces, function ($r) use ($id) {
            return $r['id'] != $id;
        }));

        // P0: 分速分布 + 鸽主排行 + 冠军鸽 + 时间分布 + 地区分布
        $speedDist = $this->race->getSpeedDistribution($id);
        $topOwners = $this->race->getTopOwnersInRace($id, 10);
        $champion = $this->race->getChampionPigeon($id);
        $timeDist = $this->race->getTimeDistribution($id);
        $regionDist = $this->race->getRegionDistribution($id);

        // P2: 赛事分析页增强 — 难度评级 + 鸽主重叠 + 历史对比
        $difficulty = $this->race->getRaceDifficultyRating($id, $race['distance_km'] ?? 0, $race['return_rate'] ?? 0, $race['entry_count'] ?? 0);
        $ownerOverlap = $this->race->getOwnerOverlapInProvince($id, $race['province'] ?? '', 5);
        $loftHistory = $this->race->getLoftHistoryComparison($race['loft_id'], $race['season_type'] ?? '', $race['season_year'] ?? 2026, 3);

        require __DIR__ . '/../../views/race_detail.php';

        $output = ob_get_clean();
        $this->cache()->set($cacheKey, $output, 3600);
        echo $output;
    }

    /**
     * 某公棚的全部赛事 /race/loft/123/
     */
    public function byLoft()
    {
        $loftId = intval($_GET['loft_id'] ?? 0);
        if (!$loftId) {
            http_response_code(404);
            require __DIR__ . '/../../views/404.php';
            return;
        }

        $loftInfo = $this->loft->findById($loftId);
        if (!$loftInfo) {
            http_response_code(404);
            require __DIR__ . '/../../views/404.php';
            return;
        }

        $races = $this->race->getByLoftId($loftId);

        require __DIR__ . '/../../views/race_loft.php';
    }

    /**
     * 足环号跨公棚追溯 /race/search?ring=XXX
     */
    public function search()
    {
        $ring = trim($_GET['ring'] ?? '');
        $results = [];
        if ($ring) {
            $results = $this->race->getResultsByRing($ring);
        }

        require __DIR__ . '/../../views/race_search.php';
    }

    /**
     * 冠军榜 /race/champions
     */
    public function champions()
    {
        $champions = $this->race->getChampions(50);
        require __DIR__ . '/../../views/race_champions.php';
    }

    /**
     * 足环号时间线 /race/ring/{ring_number}
     */
    public function ring()
    {
        $ring = rtrim(trim($_GET['ring'] ?? ''), '/');
        if (!$ring) {
            header('Location: /race/');
            exit;
        }

        $results = $this->race->getResultsByRing($ring);

        // 聚合统计
        $stats = [
            'total_races' => count($results),
            'best_rank' => null,
            'best_speed' => 0,
            'total_lofts' => 0,
            'podium_count' => 0,
        ];
        $loftIds = [];
        foreach ($results as $r) {
            $rank = intval($r['rank'] ?? 0);
            if ($rank > 0 && ($stats['best_rank'] === null || $rank < $stats['best_rank'])) {
                $stats['best_rank'] = $rank;
            }
            if (($r['speed'] ?? 0) > $stats['best_speed']) {
                $stats['best_speed'] = $r['speed'];
            }
            if ($rank >= 1 && $rank <= 3) {
                $stats['podium_count']++;
            }
            $loftIds[$r['loft_id'] ?? 0] = true;
        }
        $stats['total_lofts'] = count($loftIds);

        require __DIR__ . '/../../views/race_ring.php';
    }

    /**
     * Phase 2 SEO: 省份聚合 - 省份列表
     */
    public function provinceIndex()
    {
        $provinces = $this->race->getProvinces();
        require __DIR__ . '/../../views/race_provinces.php';
    }

    /**
     * Phase 2 SEO: 省份聚合 - 某省份赛事列表
     */
    public function byProvince()
    {
        $province = rtrim(trim($_GET['province'] ?? ''), '/');
        if (!$province) {
            $this->provinceIndex();
            return;
        }

        $page = max(1, intval($_GET['page'] ?? 1));
        $races = $this->race->getByProvince($province, $page, 12);
        $stats = $this->race->getProvinces();
        $provinceStats = null;
        foreach ($stats as $s) {
            if ($s['province'] === $province) {
                $provinceStats = $s;
                break;
            }
        }

        if (empty($races['list']) && !$provinceStats) {
            http_response_code(404);
            require __DIR__ . '/../../views/404.php';
            return;
        }

        require __DIR__ . '/../../views/race_province.php';
    }

    /**
     * P1: 冠军鸽列表 /race/champion/
     */
    public function championIndex()
    {
        $page = max(1, intval($_GET['page'] ?? 1));
        $champions = $this->race->getChampionList($page, 20);
        require __DIR__ . '/../../views/race_champion_index.php';
    }

    /**
     * P1: 城市赛事中心 — 城市列表 /race/city/
     */
    public function cityIndex()
    {
        $cities = $this->race->getCities();
        require __DIR__ . '/../../views/race_cities.php';
    }

    /**
     * P1: 城市赛事中心 — 城市详情 /race/city/{city}/
     */
    public function byCity()
    {
        $city = rtrim(trim($_GET['city'] ?? ''), '/');
        if (!$city) {
            $this->cityIndex();
            return;
        }

        $page = max(1, intval($_GET['page'] ?? 1));
        $races = $this->race->getByCity($city, $page, 12);
        $cities = $this->race->getCities();
        $cityStats = null;
        foreach ($cities as $c) {
            if ($c['city'] === $city) {
                $cityStats = $c;
                break;
            }
        }

        if (empty($races['list']) && !$cityStats) {
            http_response_code(404);
            require __DIR__ . '/../../views/404.php';
            return;
        }

        require __DIR__ . '/../../views/race_city.php';
    }

    /**
     * A方案GEO SEO: 城市赛事深度分析页 /race/city/{city}/top/
     */
    public function cityTop()
    {
        $start = microtime(true);

        $city = rtrim(trim($_GET['city'] ?? ''), '/');
        if (!$city) {
            $this->cityIndex();
            return;
        }

        // 调试计时
        $t0 = microtime(true);
        $cityStats = $this->race->getCityStats($city);
        error_log("[cityTop] getCityStats: " . round((microtime(true) - $t0) * 1000) . "ms");

        if (!$cityStats || ($cityStats['race_count'] ?? 0) == 0) {
            http_response_code(404);
            require __DIR__ . '/../../views/404.php';
            return;
        }

        $cacheKey = 'race_city_top_' . md5($city);
        $t1 = microtime(true);
        $cached = $this->cache()->get($cacheKey);
        error_log("[cityTop] cache.get: " . round((microtime(true) - $t1) * 1000) . "ms, hit=" . ($cached !== null ? "YES" : "NO"));
        if ($cached !== null) {
            error_log("[cityTop] TOTAL (cached): " . round((microtime(true) - $start) * 1000) . "ms");
            echo $cached;
            return;
        }

        ob_start();

        $t2 = microtime(true);
        $topSpeedPigeons = $this->race->getCityTopSpeedPigeonsPrecomputed($city);
        error_log("[cityTop] getCityTopSpeedPigeonsPrecomputed: " . round((microtime(true) - $t2) * 1000) . "ms");

        $t3 = microtime(true);
        $topOwners = $this->race->getCityTopOwnersPrecomputed($city);
        error_log("[cityTop] getCityTopOwnersPrecomputed: " . round((microtime(true) - $t3) * 1000) . "ms");

        $t4 = microtime(true);
        $topLofts = $this->race->getCityTopLoftsPrecomputed($city);
        error_log("[cityTop] getCityTopLoftsPrecomputed: " . round((microtime(true) - $t4) * 1000) . "ms");

        $t5 = microtime(true);
        $cities = $this->race->getCities();
        error_log("[cityTop] getCities: " . round((microtime(true) - $t5) * 1000) . "ms");

        $t6 = microtime(true);
        require __DIR__ . '/../../views/race_city_top.php';
        error_log("[cityTop] view render: " . round((microtime(true) - $t6) * 1000) . "ms");

        $output = ob_get_clean();
        $this->cache()->set($cacheKey, $output, 3600); // TTL 1小时
        error_log("[cityTop] TOTAL (uncached): " . round((microtime(true) - $start) * 1000) . "ms");
        echo $output;
    }

    /**
     * P2: 赛季总结 /race/season/{year}/
     */
    public function seasonDetail()
    {
        $year = rtrim(trim($_GET['season_year'] ?? ''), '/');
        if (!$year) {
            // 无年份时取最近赛季
            $seasons = $this->race->getSeasons();
            $year = $seasons[0]['season_year'] ?? date('Y');
            header('Location: /race/season/' . $year . '/');
            exit;
        }

        $cacheKey = 'race_season_' . $year;
        $cached = $this->cache()->get($cacheKey);
        if ($cached !== null) {
            echo $cached;
            return;
        }

        $summary = $this->race->getSeasonSummary($year);
        if (!$summary || ($summary['race_count'] ?? 0) == 0) {
            http_response_code(404);
            require __DIR__ . '/../../views/404.php';
            return;
        }

        ob_start();

        $monthly = $this->race->getSeasonMonthlyDistribution($year);
        $topFastest = $this->race->getSeasonTopFastest($year, 10);
        $topLofts = $this->race->getSeasonTopLofts($year, 10);
        $seasons = $this->race->getSeasons();

        require __DIR__ . '/../../views/race_season.php';

        $output = ob_get_clean();
        $this->cache()->set($cacheKey, $output, 1800);
        echo $output;
    }

    /**
     * P2: 赛事浏览大全 /race/browse/
     */
    public function browse()
    {
        $page = max(1, intval($_GET['page'] ?? 1));
        $year = $_GET['year'] ?? null;
        $type = $_GET['type'] ?? null;
        $keyword = trim($_GET['q'] ?? '');

        $perPage = 20;
        $data = $this->race->getList($page, $perPage, $year, $type, $keyword ?: null);

        // 可用的筛选年份
        $seasons = $this->race->getSeasons();

        require __DIR__ . '/../../views/race_browse.php';
    }

    /**
     * 足环号深度查询报告 /race/report/{ring}/
     */
    public function report()
    {
        $ring = rtrim(trim($_GET['ring'] ?? ''), '/');
        if (!$ring) {
            // 落地页：未传足环号，展示搜索框 + 热门环号快捷入口
            $page_title = '足环号深度赛绩报告 - 信鸽之家';
            require __DIR__ . '/../../views/race_report_landing.php';
            return;
        }

        $results = $this->race->getResultsByRing($ring);

        // ===== 聚合统计 =====
        $stats = [
            'total_races'  => count($results),
            'best_rank'    => null,
            'best_speed'   => 0,
            'avg_speed'    => 0,
            'min_speed'    => 0,
            'total_lofts'  => 0,
            'podium_count' => 0,
            'champion_count' => 0,
            'first_race'   => null,
            'last_race'    => null,
            'owner_name'   => '',
            'color'        => '',
            'region'       => '',
        ];
        $loftIds = [];
        $speedSum = 0;
        $speedCount = 0;
        $seasons = ['spring' => 0, 'autumn' => 0, 'winter' => 0, 'summer' => 0];

        foreach ($results as $r) {
            $rank = intval($r['rank'] ?? 0);
            $speed = floatval($r['speed'] ?? 0);

            if ($rank > 0 && ($stats['best_rank'] === null || $rank < $stats['best_rank'])) {
                $stats['best_rank'] = $rank;
            }
            if ($speed > $stats['best_speed']) $stats['best_speed'] = $speed;
            if ($speed > 0 && ($stats['min_speed'] === 0 || $speed < $stats['min_speed'])) $stats['min_speed'] = $speed;
            if ($speed > 0) { $speedSum += $speed; $speedCount++; }
            if ($rank >= 1 && $rank <= 3) $stats['podium_count']++;
            if ($rank == 1) $stats['champion_count']++;

            $loftIds[$r['loft_id'] ?? 0] = true;
            if (empty($stats['owner_name']) && !empty($r['owner_name'])) $stats['owner_name'] = $r['owner_name'];
            if (empty($stats['color']) && !empty($r['color'])) $stats['color'] = $r['color'];
            if (empty($stats['region']) && !empty($r['region'])) $stats['region'] = $r['region'];

            $seasonType = $r['season_type'] ?? '';
            if (isset($seasons[$seasonType])) $seasons[$seasonType]++;
        }
        $stats['avg_speed'] = $speedCount > 0 ? round($speedSum / $speedCount, 2) : 0;
        $stats['total_lofts'] = count($loftIds);

        // 首战/最后一战
        if (!empty($results)) {
            $first = $results[count($results) - 1];
            $last = $results[0];
            $stats['first_race'] = ($first['release_time'] ?? '') ?: ($first['season_year'] ?? '');
            $stats['last_race'] = ($last['release_time'] ?? '') ?: ($last['season_year'] ?? '');
        }

        // ===== 同鸽主其他铭鸽 =====
        $sameOwnerBirds = [];
        if (!empty($stats['owner_name'])) {
            $sameOwnerBirds = $this->race->getSameOwnerBirds($stats['owner_name'], $ring, 20);
        }

        // ===== 冠军记录 =====
        $championRaces = array_filter($results, function ($r) {
            return intval($r['rank'] ?? 0) === 1;
        });

        // ===== 分速趋势数据 (JSON for Chart.js) =====
        $speedData = [];
        $resultsReversed = array_reverse($results);
        $labelCount = 0;
        foreach ($resultsReversed as $r) {
            $spd = floatval($r['speed'] ?? 0);
            if ($spd <= 0) continue;
            $date = ($r['release_time'] ?? '') ?: ($r['season_year'] ?? '');
            $label = is_numeric($date) ? $date : substr($date, 0, 10);
            $speedData[] = [
                'label' => $label,
                'speed' => $spd,
                'rank'  => intval($r['rank'] ?? 0),
                'race'  => mb_strlen($r['race_name'] ?? '', 'UTF-8') > 8
                    ? mb_substr($r['race_name'], 0, 8, 'UTF-8') . '…' : ($r['race_name'] ?? ''),
            ];
            $labelCount++;
            if ($labelCount >= 50) break; // 最多50个数据点
        }

        // ===== 公棚列表 =====
        $loftList = [];
        foreach ($results as $r) {
            $lid = intval($r['loft_id'] ?? 0);
            if ($lid && !isset($loftList[$lid])) {
                $loftList[$lid] = [
                    'id'   => $lid,
                    'name' => $r['loft_name'] ?? '未知公棚',
                    'count' => 0,
                ];
            }
            if ($lid) $loftList[$lid]['count']++;
        }

        // ===== 解锁状态 =====
        $unlocked = ($_GET['unlock'] ?? '') === '1'; // 开发调试用，上线后删除
        $pendingOrder = null;
        $approvedOrder = null;
        if (!$unlocked && !empty($_SESSION['user_id'])) {
            require_once __DIR__ . '/../models/Payment.php';
            $pmt = new Payment($this->pdo);
            $unlocked = $pmt->isReportUnlocked((int)$_SESSION['user_id'], $ring);
            
            // 检查是否有待审核或已审批待支付的订单
            if (!$unlocked) {
                $stmt = $this->pdo->prepare("SELECT order_no, status, created_at FROM member_orders WHERE user_id = ? AND product_type = 'report' AND product_ref = ? AND status IN (0, 3) ORDER BY created_at DESC LIMIT 1");
                $stmt->execute([(int)$_SESSION['user_id'], $ring]);
                $pendingOrder = $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
                if ($pendingOrder && $pendingOrder['status'] == 3) {
                    $approvedOrder = $pendingOrder;
                    $pendingOrder = null;
                }
            }
        }

        require __DIR__ . '/../../views/race_report.php';
    }
}
