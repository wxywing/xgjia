<?php
/**
 * 赛事成绩模型
 */
class Race
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * 获取赛事列表（含分页和筛选）
     */
    public function getList($page = 1, $perPage = 12, $year = null, $type = null, $keyword = null)
    {
        $where = ['r.status = 1'];
        $params = [];

        if ($year) {
            $where[] = 'r.season_year = ?';
            $params[] = $year;
        }
        if ($type) {
            $where[] = 'r.season_type = ?';
            $params[] = $type;
        }
        if ($keyword) {
            $where[] = '(r.name LIKE ? OR l.name LIKE ? OR l.province LIKE ?)';
            $kw = "%{$keyword}%";
            $params[] = $kw;
            $params[] = $kw;
            $params[] = $kw;
        }

        $whereStr = implode(' AND ', $where);
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT r.*, l.name AS loft_name, l.province, l.id AS loft_id
                FROM races r
                LEFT JOIN lofts l ON r.loft_id = l.id
                WHERE {$whereStr}
                ORDER BY r.release_time DESC
                LIMIT ?, ?";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_merge($params, [$offset, $perPage]));
        $list = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Count
        $countSql = "SELECT COUNT(*) as cnt FROM races r LEFT JOIN lofts l ON r.loft_id = l.id WHERE {$whereStr}";
        $countStmt = $this->pdo->prepare($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0;

        return [
            'list' => $list ?: [],
            'total' => $total,
            'page' => $page,
            'total_pages' => ceil($total / $perPage)
        ];
    }

    /**
     * 获取可用赛季列表（去重）
     */
    public function getSeasons()
    {
        return $this->pdo->query("SELECT DISTINCT season_year, season_type FROM races ORDER BY season_year DESC, season_type")->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * 获取某公棚的全部赛事
     */
    public function getByLoftId($loftId)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM races WHERE loft_id = ? AND status = 1 ORDER BY release_time DESC");
        $stmt->execute([$loftId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * 获取冠军榜
     */
    public function getChampions($limit = 12)
    {
        $stmt = $this->pdo->prepare(
            "SELECT rr.rank, rr.owner_name, rr.ring_number, rr.speed,
                    r.name AS race_name, r.distance_km, r.id AS race_id, r.release_time,
                    l.name AS loft_name, l.id AS loft_id
             FROM race_results rr
             LEFT JOIN races r ON rr.race_id = r.id
             LEFT JOIN lofts l ON r.loft_id = l.id
             WHERE rr.rank = 1 AND r.status = 1
             ORDER BY r.release_time DESC
             LIMIT ?"
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * 获取单场赛事详情
     */
    public function getDetail($id)
    {
        $stmt = $this->pdo->prepare(
            "SELECT r.*, l.name AS loft_name, l.province, l.id AS loft_id
             FROM races r
             LEFT JOIN lofts l ON r.loft_id = l.id
             WHERE r.id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * 获取赛事成绩明细（分页）
     */
    public function getResults($raceId, $page = 1, $perPage = 50, $keyword = null)
    {
        $where = ['rr.race_id = ?'];
        $params = [$raceId];

        if ($keyword) {
            $where[] = '(rr.owner_name LIKE ? OR rr.ring_number LIKE ? OR rr.region LIKE ?)';
            $kw = "%{$keyword}%";
            $params[] = $kw;
            $params[] = $kw;
            $params[] = $kw;
        }

        $whereStr = implode(' AND ', $where);
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT * FROM race_results rr WHERE {$whereStr} ORDER BY rr.rank ASC LIMIT ?, ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_merge($params, [$offset, $perPage]));
        $list = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Count
        $countSql = "SELECT COUNT(*) as cnt FROM race_results rr WHERE {$whereStr}";
        $countStmt = $this->pdo->prepare($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0;

        return [
            'list' => $list ?: [],
            'total' => $total,
            'page' => $page,
            'total_pages' => ceil($total / $perPage)
        ];
    }

    /**
     * 足环号跨公棚追溯（多格式智能匹配，全部用 = 精确查，可利用索引）
     */
    public function getResultsByRing($ringNumber)
    {
        // 标准化输入（内联，避免依赖 RingNormalizer）
        $normalized = strtoupper(preg_replace('/\s+/', '', trim($ringNumber)));
        $variants = [$ringNumber, $normalized];

        $segments = explode('-', $normalized);

        // 两段输入 (如 07-0456058)：补年份前缀和 CHN 前缀
        if (count($segments) === 2) {
            $currentYear = intval(date('Y'));
            for ($year = $currentYear; $year >= 2020; $year--) {
                $variants[] = $year . '-' . $normalized;
                $variants[] = 'CHN' . $year . '-' . $normalized;
            }
        }

        // 三段且第一段像年份 (如 2025-07-0456058)：补去年前缀版
        if (count($segments) === 3 && preg_match('/^20\d{2}$/', $segments[0])) {
            $variants[] = $segments[1] . '-' . $segments[2];
        }

        // 去重后逐一精确匹配
        $variants = array_values(array_unique($variants));
        foreach ($variants as $variant) {
            $result = $this->searchRingExact($variant);
            if (!empty($result)) return $result;
        }

        return [];
    }

    /**
     * 精确匹配 — 仅查 race_results，后补全 race/loft 信息
     */
    private function searchRingExact($ringNumber)
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM race_results WHERE ring_number = ? ORDER BY arrival_time DESC"
        );
        $stmt->execute([$ringNumber]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        if (empty($results)) return $results;

        // 检测 race_results 表里用哪个列关联 races（本地/线上列名不同）
        $firstRow = $results[0];
        $joinKey = isset($firstRow['source_id']) ? 'source_id' : (isset($firstRow['race_id']) ? 'race_id' : null);

        if ($joinKey) {
            $sourceIds = array_values(array_unique(array_column($results, $joinKey)));

            // 查 races 表（优先用 source_id 匹配，找不到行时再试 id 匹配）
            $raceMap = [];
            $loftIds = [];
            if (!empty($sourceIds)) {
                $phs = implode(',', array_fill(0, count($sourceIds), '?'));
                // 尝试 source_id 匹配
                try {
                    $stmt2 = $this->pdo->prepare(
                        "SELECT id, source_id, loft_id, name, release_time, season_year, season_type FROM races WHERE source_id IN ($phs)"
                    );
                    $stmt2->execute($sourceIds);
                    foreach ($stmt2->fetchAll(PDO::FETCH_ASSOC) as $race) {
                        $raceMap[$race['source_id']] = $race;
                    }
                } catch (\Exception $e) {}

                // 如果 source_id 匹配不到行，fallback 用 id 匹配
                if (empty($raceMap)) {
                    try {
                        $stmt2 = $this->pdo->prepare(
                            "SELECT id, loft_id, name, release_time, season_year, season_type FROM races WHERE id IN ($phs)"
                        );
                        $stmt2->execute($sourceIds);
                        foreach ($stmt2->fetchAll(PDO::FETCH_ASSOC) as $race) {
                            $raceMap[$race['id']] = $race;
                        }
                    } catch (\Exception $e) {}
                }

                foreach ($raceMap as $r) {
                    $loftIds[$r['loft_id'] ?? 0] = true;
                }
            }

            // 查 lofts
            $loftMap = [];
            $loftIds = array_keys($loftIds);
            if (!empty($loftIds)) {
                $phs = implode(',', array_fill(0, count($loftIds), '?'));
                try {
                    $stmt3 = $this->pdo->prepare(
                        "SELECT id, name, province FROM lofts WHERE id IN ($phs)"
                    );
                    $stmt3->execute($loftIds);
                    foreach ($stmt3->fetchAll(PDO::FETCH_ASSOC) as $loft) {
                        $loftMap[$loft['id']] = $loft;
                    }
                } catch (\Exception $e) {}
            }

            // 补全每行
            foreach ($results as &$row) {
                $race = $raceMap[$row[$joinKey]] ?? null;
                $loft = $loftMap[$race['loft_id'] ?? 0] ?? null;
                $row['race_id']       = $race['id'] ?? $row[$joinKey];
                $row['race_name']     = $race['name'] ?? ($row['race_name'] ?? '');
                $row['release_time']  = $race['release_time'] ?? null;
                $row['season_year']   = $race['season_year'] ?? '';
                $row['season_type']   = $race['season_type'] ?? '';
                $row['loft_id']       = $loft['id'] ?? 0;
                $row['loft_name']     = $loft['name'] ?? ($row['loft_name'] ?? '');
                $row['province']      = $loft['province'] ?? '';
            }
            unset($row);
        }

        return $results;
    }

    /**
     * 批量插入赛事
     */
    public function batchInsert(array $races)
    {
        $sql = "INSERT INTO races (loft_id, source_id, name, release_time, entry_count, returned_count, return_rate, race_category, season_year, season_type)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE name=VALUES(name), release_time=VALUES(release_time),
                        entry_count=VALUES(entry_count), returned_count=VALUES(returned_count),
                        return_rate=VALUES(return_rate)";
        $stmt = $this->pdo->prepare($sql);
        $count = 0;
        foreach ($races as $r) {
            $stmt->execute([
                $r['loft_id'], $r['source_id'], $r['name'], $r['release_time'],
                $r['entry_count'], $r['returned_count'], $r['return_rate'],
                $r['race_category'], $r['season_year'], $r['season_type']
            ]);
            $count++;
        }
        return $count;
    }

    /**
     * 批量插入成绩明细（替换式：先删后插）
     */
    public function batchInsertResults(int $raceId, array $results)
    {
        $this->pdo->exec("DELETE FROM race_results WHERE race_id = " . intval($raceId));

        $sql = "INSERT INTO race_results (race_id, rank, owner_name, region, ring_number, color, arrival_time, speed)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $count = 0;
        foreach ($results as $r) {
            $stmt->execute([
                $raceId,
                $r['rank'] ?? 0,
                $r['owner_name'] ?? '',
                $r['region'] ?? '',
                $r['ring_number'] ?? '',
                $r['color'] ?? '',
                $r['arrival_time'] ?? null,
                $r['speed'] ?? 0,
            ]);
            $count++;
        }
        return $count;
    }

    /**
     * 获取赛事总数
     */
    public function getCount($conditions = ['status' => 1])
    {
        $where = [];
        $params = [];
        foreach ($conditions as $k => $v) {
            $where[] = "$k = ?";
            $params[] = $v;
        }
        $whereStr = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as cnt FROM races {$whereStr}");
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0;
    }

    /**
     * 获取即将到来/最近的赛事
     */
    public function getUpcoming($limit = 10)
    {
        $stmt = $this->pdo->prepare(
            "SELECT r.*, l.name AS loft_name, l.province, l.id AS loft_id
             FROM races r
             LEFT JOIN lofts l ON r.loft_id = l.id
             WHERE r.status = 1
             ORDER BY r.release_time DESC
             LIMIT ?"
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * 获取热门赛事（按成绩记录数排序）
     */
    public function getHot($limit = 5)
    {
        $stmt = $this->pdo->prepare(
            "SELECT r.*, l.name AS loft_name, l.province, l.id AS loft_id, r.result_count
             FROM races r
             LEFT JOIN lofts l ON r.loft_id = l.id
             WHERE r.status = 1
             ORDER BY r.result_count DESC
             LIMIT ?"
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * 公棚成绩一览：按公棚分组，取最新赛事，按更新时间倒序
     */
    public function getLoftsWithLatestRace($page = 1, $perPage = 20)
    {
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT
                    l.id AS loft_id,
                    l.name AS loft_name,
                    l.province,
                    r.name AS latest_race_name,
                    r.release_time AS latest_update,
                    r.entry_count AS latest_entry_count,
                    r.id AS latest_race_id,
                    (SELECT COUNT(*) FROM races WHERE loft_id = l.id AND status = 1) AS season_count
                FROM lofts l
                INNER JOIN races r ON r.id = (
                    SELECT r2.id FROM races r2
                    WHERE r2.loft_id = l.id AND r2.status = 1
                    ORDER BY r2.release_time DESC
                    LIMIT 1
                )
                WHERE l.status = 1
                ORDER BY r.release_time DESC
                LIMIT ?, ?";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$offset, $perPage]);
        $list = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $countSql = "SELECT COUNT(DISTINCT l.id) as cnt
                     FROM lofts l
                     INNER JOIN races r ON r.loft_id = l.id AND r.status = 1
                     WHERE l.status = 1";
        $total = $this->pdo->query($countSql)->fetch(\PDO::FETCH_ASSOC)['cnt'] ?? 0;

        return [
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'total_pages' => ceil($total / $perPage)
        ];
    }

    /**
     * 获取数据概览统计
     */
    public function getStats()
    {
        $stats = [
            'loft_count' => 0,
            'race_count' => 0,
            'result_count' => 0,
            'champion_count' => 0
        ];

        $row = $this->pdo->query("SELECT COUNT(DISTINCT loft_id) as cnt FROM races WHERE status = 1")->fetch(PDO::FETCH_ASSOC);
        if ($row) $stats['loft_count'] = $row['cnt'];

        $row = $this->pdo->query("SELECT COUNT(*) as cnt FROM races WHERE status = 1")->fetch(PDO::FETCH_ASSOC);
        if ($row) $stats['race_count'] = $row['cnt'];

        // 1270万行 COUNT 太慢，用 information_schema 近似值
        $row = $this->pdo->query("SELECT TABLE_ROWS as cnt FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'xgjia' AND TABLE_NAME = 'race_results'")->fetch(PDO::FETCH_ASSOC);
        if ($row) $stats['result_count'] = $row['cnt'];

        $row = $this->pdo->query("SELECT COUNT(*) as cnt FROM race_results WHERE rank = 1")->fetch(PDO::FETCH_ASSOC);
        if ($row) $stats['champion_count'] = $row['cnt'];

        return $stats;
    }

    /**
     * 按鸽主名获取全部成绩（含赛事+公棚信息）
     */
    public function getResultsByOwner($ownerName, $page = 1, $perPage = 30)
    {
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT rr.*, r.name AS race_name, r.release_time, r.season_year, r.season_type, r.id AS race_id,
                       l.name AS loft_name, l.province, l.id AS loft_id
                FROM race_results rr
                LEFT JOIN races r ON rr.race_id = r.id
                LEFT JOIN lofts l ON r.loft_id = l.id
                WHERE rr.owner_name = ?
                ORDER BY r.release_time DESC
                LIMIT ?, ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$ownerName, $offset, $perPage]);
        $list = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $countStmt = $this->pdo->prepare("SELECT COUNT(*) as cnt FROM race_results WHERE owner_name = ?");
        $countStmt->execute([$ownerName]);
        $total = $countStmt->fetch(\PDO::FETCH_ASSOC)['cnt'] ?? 0;

        return [
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'total_pages' => ceil($total / $perPage)
        ];
    }

    /**
     * 鸽主聚合统计
     */
    public function getOwnerStats($ownerName)
    {
        $stmt = $this->pdo->prepare(
            "SELECT
                COUNT(DISTINCT rr.race_id) as race_count,
                COUNT(*) as total_results,
                MIN(rr.rank) as best_rank,
                MAX(rr.speed) as best_speed,
                AVG(rr.speed) as avg_speed,
                COUNT(DISTINCT r.loft_id) as loft_count,
                SUM(CASE WHEN rr.rank = 1 THEN 1 ELSE 0 END) as champion_count,
                SUM(CASE WHEN rr.rank <= 3 THEN 1 ELSE 0 END) as podium_count,
                SUM(CASE WHEN rr.rank <= 10 THEN 1 ELSE 0 END) as top10_count
             FROM race_results rr
             LEFT JOIN races r ON rr.race_id = r.id
             WHERE rr.owner_name = ?"
        );
        $stmt->execute([$ownerName]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * 热门鸽主（按参赛场次+冠军数排名）
     */
    public function getTopOwners($limit = 20)
    {
        $stmt = $this->pdo->prepare(
            "SELECT owner_name,
                    COUNT(DISTINCT race_id) as race_count,
                    COUNT(*) as total_results,
                    SUM(CASE WHEN rank = 1 THEN 1 ELSE 0 END) as champion_count,
                    MAX(speed) as best_speed
             FROM race_results
             WHERE owner_name IS NOT NULL AND owner_name != ''
             GROUP BY owner_name
             HAVING race_count >= 3
             ORDER BY champion_count DESC, race_count DESC
             LIMIT ?"
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Phase 2 SEO P0: 获取某赛事的分速分布
     */
    public function getSpeedDistribution($raceId)
    {
        $stmt = $this->pdo->prepare(
            "SELECT
                COUNT(*) as total,
                SUM(CASE WHEN speed < 800 THEN 1 ELSE 0 END) as r_lt_800,
                SUM(CASE WHEN speed >= 800 AND speed < 1000 THEN 1 ELSE 0 END) as r_800_1000,
                SUM(CASE WHEN speed >= 1000 AND speed < 1200 THEN 1 ELSE 0 END) as r_1000_1200,
                SUM(CASE WHEN speed >= 1200 AND speed < 1400 THEN 1 ELSE 0 END) as r_1200_1400,
                SUM(CASE WHEN speed >= 1400 THEN 1 ELSE 0 END) as r_gt_1400,
                MIN(speed) as min_speed,
                MAX(speed) as max_speed,
                AVG(speed) as avg_speed,
                STDDEV_POP(speed) as stddev_speed
             FROM race_results WHERE race_id = ?"
        );
        $stmt->execute([$raceId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Phase 2 SEO P0: 获取某赛事鸽主排行（前N名）
     */
    public function getTopOwnersInRace($raceId, $limit = 10)
    {
        $stmt = $this->pdo->prepare(
            "SELECT owner_name,
                    COUNT(*) as entry_count,
                    MIN(rank) as best_rank,
                    MAX(speed) as best_speed,
                    SUM(CASE WHEN rank <= 10 THEN 1 ELSE 0 END) as top10_count,
                    SUM(CASE WHEN rank <= 3 THEN 1 ELSE 0 END) as podium_count
             FROM race_results
             WHERE race_id = ? AND owner_name IS NOT NULL AND owner_name != ''
             GROUP BY owner_name
             ORDER BY top10_count DESC, podium_count DESC, best_rank ASC
             LIMIT ?"
        );
        $stmt->execute([$raceId, $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Phase 2 SEO P0: 公棚赛事汇总统计
     */
    public function getLoftRaceStats($loftId)
    {
        $stmt = $this->pdo->prepare(
            "SELECT
                COUNT(DISTINCT r.id) as total_races,
                SUM(r.entry_count) as total_entries,
                SUM(r.returned_count) as total_returned,
                ROUND(AVG(r.return_rate), 1) as avg_return_rate,
                MIN(r.distance_km) as min_distance,
                MAX(r.distance_km) as max_distance,
                AVG(r.distance_km) as avg_distance,
                MIN(r.release_time) as first_release,
                MAX(r.release_time) as latest_release,
                COUNT(DISTINCT r.season_year) as season_count
             FROM races r WHERE r.loft_id = ? AND r.status = 1"
        );
        $stmt->execute([$loftId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Phase 2 SEO P0: 公棚冠军鸽列表
     */
    public function getLoftChampions($loftId, $limit = 8)
    {
        $stmt = $this->pdo->prepare(
            "SELECT rr.owner_name, rr.ring_number, rr.speed, r.name as race_name,
                    r.release_time, r.distance_km, r.season_year, r.season_type,
                    r.loft_id, l.name as loft_name
             FROM race_results rr
             JOIN races r ON rr.race_id = r.id
             LEFT JOIN lofts l ON r.loft_id = l.id
             WHERE r.loft_id = ? AND rr.rank = 1 AND r.status = 1
             ORDER BY r.release_time DESC
             LIMIT ?"
        );
        $stmt->execute([$loftId, $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Phase 2 SEO P2: 公棚历年赛季对比
     */
    public function getLoftSeasonComparison($loftId)
    {
        // 先取该公棚所有赛事ID，避免子查询扫全表
        $raceIds = $this->pdo->prepare(
            "SELECT id FROM races WHERE loft_id = ? AND status = 1"
        );
        $raceIds->execute([$loftId]);
        $ids = $raceIds->fetchAll(\PDO::FETCH_COLUMN) ?: [];
        if (empty($ids)) return [];

        $phs = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->pdo->prepare(
            "SELECT
                r.season_year,
                r.season_type,
                COUNT(DISTINCT r.id) as race_count,
                SUM(r.entry_count) as total_entries,
                SUM(r.returned_count) as total_returned,
                ROUND(AVG(r.distance_km), 1) as avg_distance,
                MAX(rr.best_speed) as best_speed
             FROM races r
             LEFT JOIN (
                 SELECT race_id, MAX(speed) as best_speed
                 FROM race_results
                 WHERE race_id IN ($phs) AND speed > 0
                 GROUP BY race_id
             ) rr ON r.id = rr.race_id
             WHERE r.id IN ($phs)
             GROUP BY r.season_year, r.season_type
             ORDER BY r.season_year DESC, r.season_type"
        );
        $stmt->execute(array_merge($ids, $ids));
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Phase 2 SEO P2: 公棚鸽主荣誉榜（该公棚最多冠军的鸽主）
     */
    public function getLoftTopOwners($loftId, $limit = 8)
    {
        // 先取该公棚所有赛事ID
        $raceIds = $this->pdo->prepare(
            "SELECT id FROM races WHERE loft_id = ? AND status = 1"
        );
        $raceIds->execute([$loftId]);
        $ids = $raceIds->fetchAll(\PDO::FETCH_COLUMN) ?: [];
        if (empty($ids)) return [];

        $phs = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->pdo->prepare(
            "SELECT
                rr.owner_name,
                COUNT(*) as champion_count,
                MAX(rr.speed) as best_speed,
                MAX(r.release_time) as latest_release,
                GROUP_CONCAT(DISTINCT rr.ring_number ORDER BY rr.speed DESC SEPARATOR ', ') as top_rings
             FROM race_results rr
             JOIN races r ON rr.race_id = r.id
             WHERE rr.race_id IN ($phs) AND rr.rank = 1
             GROUP BY rr.owner_name
             ORDER BY champion_count DESC
             LIMIT ?"
        );
        $stmt->execute(array_merge($ids, [$limit]));
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        // MySQL 5.7 不支持 GROUP_CONCAT 的 LIMIT，PHP 截断
        foreach ($rows as &$row) {
            $rings = explode(', ', $row['top_rings'] ?? '');
            $row['top_rings'] = implode(', ', array_slice($rings, 0, 3));
        }
        return $rows;
    }

    /**
     * P1: 冠军鸽列表（分页，去重足环号+排名=1）
     */
    public function getChampionList($page = 1, $perPage = 20)
    {
        $offset = ($page - 1) * $perPage;

        $stmt = $this->pdo->prepare(
            "SELECT rr.ring_number, rr.owner_name, rr.speed, rr.arrival_time,
                    r.name as race_name, r.release_time, r.distance_km,
                    r.loft_id, l.name as loft_name, r.id as race_id
             FROM race_results rr
             JOIN races r ON rr.race_id = r.id
             LEFT JOIN lofts l ON r.loft_id = l.id
             WHERE rr.rank = 1 AND r.status = 1 AND rr.ring_number IS NOT NULL AND rr.ring_number != ''
             ORDER BY r.release_time DESC
             LIMIT ?, ?"
        );
        $stmt->execute([$offset, $perPage]);
        $list = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $count = $this->pdo->query(
            "SELECT COUNT(*) as cnt FROM race_results WHERE rank = 1 AND ring_number IS NOT NULL AND ring_number != ''"
        )->fetch(\PDO::FETCH_ASSOC)['cnt'];

        return [
            'list' => $list ?: [],
            'total' => $count,
            'page' => $page,
            'total_pages' => ceil($count / $perPage),
        ];
    }

    /**
     * P1: 城市赛事中心 — 城市列表（去重）
     */
    public function getCities()
    {
        return $this->pdo->query(
            "SELECT l.city, l.province,
                    COUNT(DISTINCT r.id) as race_count,
                    COUNT(DISTINCT r.loft_id) as loft_count,
                    SUM(r.entry_count) as total_entries
             FROM lofts l
             JOIN races r ON r.loft_id = l.id AND r.status = 1
             WHERE l.city IS NOT NULL AND l.city != ''
             GROUP BY l.city, l.province
             ORDER BY race_count DESC"
        )->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * P1: 城市赛事中心 — 某城市赛事列表
     */
    public function getByCity($city, $page = 1, $perPage = 12)
    {
        $offset = ($page - 1) * $perPage;

        $stmt = $this->pdo->prepare(
            "SELECT r.*, l.name as loft_name, l.province, l.city, l.id as loft_id
             FROM races r
             JOIN lofts l ON r.loft_id = l.id
             WHERE r.status = 1 AND l.city = ?
             ORDER BY r.release_time DESC
             LIMIT ?, ?"
        );
        $stmt->execute([$city, $offset, $perPage]);
        $list = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $countStmt = $this->pdo->prepare(
            "SELECT COUNT(*) as cnt FROM races r JOIN lofts l ON r.loft_id = l.id WHERE r.status = 1 AND l.city = ?"
        );
        $countStmt->execute([$city]);
        $total = $countStmt->fetch(\PDO::FETCH_ASSOC)['cnt'] ?? 0;

        return [
            'list' => $list ?: [],
            'total' => $total,
            'page' => $page,
            'total_pages' => ceil($total / $perPage),
        ];
    }

    /**
     * A方案GEO SEO: 获取城市赛事综合统计
     * 优化：races 表已反范式化加入 city/province，无需 JOIN lofts
     */
    public function getCityStats(string $city): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT r.city, r.province,
                    COUNT(DISTINCT r.id) as race_count,
                    COUNT(DISTINCT r.loft_id) as loft_count,
                    SUM(r.entry_count) as total_entries,
                    MAX(r.release_time) as latest_race_time,
                    MIN(r.release_time) as first_race_time
             FROM races r
             WHERE r.status = 1 AND r.city = ?
             GROUP BY r.city, r.province"
        );
        $stmt->execute([$city]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * A方案GEO SEO: 城市赛事前N名高分速赛鸽
     * 优化：过滤条件改用 r.city（races 表已反范式化）
     */
    public function getCityTopSpeedPigeons(string $city, int $limit = 10): array
    {
        // STRAIGHT_JOIN: 强制先扫 races (小表，idx_city) 再 JOIN race_results
        // idx_race_id_speed: race_id 定位 + speed 排序一次性完成
        $stmt = $this->pdo->prepare(
            "SELECT rr.ring_number, rr.owner_name, rr.speed, rr.rank,
                    r.name as race_name, r.release_time, r.distance_km,
                    l.name as loft_name, l.id as loft_id
             FROM races r
             JOIN race_results rr
               ON rr.race_id = r.id AND rr.speed > 0 AND rr.speed < 5000
             STRAIGHT_JOIN lofts l
               ON l.id = r.loft_id
             WHERE r.city = ?
             ORDER BY rr.speed DESC
             LIMIT ?"
        );
        $stmt->execute([$city, $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * A方案GEO SEO: 城市赛事前N名鸽主（按入赏次数）
     * 优化：去掉 JOIN lofts，过滤条件改用 r.city
     */
    public function getCityTopOwners(string $city, int $limit = 10): array
    {
        // STRAIGHT_JOIN: 强制 races(idx_city) → race_results(idx_race_id_owner)
        // idx_race_id_owner: race_id 定位后按 owner_name 聚合，兼顾 JOIN 效率和 GROUP BY
        $stmt = $this->pdo->prepare(
            "SELECT rr.owner_name,
                    COUNT(rr.id) as entry_count,
                    COUNT(CASE WHEN rr.rank <= 100 THEN 1 END) as top100_count,
                    MAX(rr.speed) as best_speed,
                    ROUND(AVG(rr.speed), 1) as avg_speed
             FROM races r
             JOIN race_results rr
               ON rr.race_id = r.id AND rr.speed > 0
             WHERE r.city = ?
             GROUP BY rr.owner_name
             ORDER BY top100_count DESC, best_speed DESC
             LIMIT ?"
        );
        $stmt->execute([$city, $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * A方案GEO SEO: 城市赛事前N名公棚
     * 优化：改为从 races 出发过滤 r.city（利用索引），再 JOIN lofts
     */
    public function getCityTopLofts(string $city, int $limit = 10): array
    {
        // STRAIGHT_JOIN: races(idx_city) → race_results(idx_race_id_rank_full) → lofts
        // idx_race_id_rank_full: race_id + rank 过滤在索引层完成
        // speed 过滤移入 JOIN 条件，让 MySQL 直接在索引扫描时过滤
        $stmt = $this->pdo->prepare(
            "SELECT l.id as loft_id, l.name as loft_name,
                    COUNT(DISTINCT r.id) as race_count,
                    COUNT(rr.id) as total_entries,
                    COUNT(CASE WHEN rr.rank <= 100 THEN 1 END) as top100_count,
                    ROUND(AVG(rr.speed), 1) as avg_speed,
                    MAX(rr.speed) as max_speed
             FROM races r
             JOIN race_results rr
               ON rr.race_id = r.id AND rr.speed > 0
             STRAIGHT_JOIN lofts l
               ON l.id = r.loft_id
             WHERE r.city = ?
             GROUP BY l.id, l.name
             ORDER BY top100_count DESC, avg_speed DESC
             LIMIT ?"
        );
        $stmt->execute([$city, $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }


    // ============================
    // 预计算表读方法（方案 B，<5ms）
    // ============================

    public function getCityTopSpeedPigeonsPrecomputed(string $city): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT ring_number, owner_name, speed, `rank`, race_name, release_time, distance_km, loft_name, loft_id
             FROM city_pigeon_rankings
             WHERE city = ?
             ORDER BY rank_pos"
        );
        $stmt->execute([$city]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    public function getCityTopOwnersPrecomputed(string $city): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT owner_name, entry_count, top100_count, best_speed, avg_speed
             FROM city_owner_rankings
             WHERE city = ?
             ORDER BY rank_pos"
        );
        $stmt->execute([$city]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    public function getCityTopLoftsPrecomputed(string $city): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT loft_id, loft_name, race_count, total_entries, top100_count, avg_speed, max_speed
             FROM city_loft_rankings
             WHERE city = ?
             ORDER BY rank_pos"
        );
        $stmt->execute([$city]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Phase 2 SEO: 获取各省份赛事聚合统计
     */
    public function getProvinces()
    {
        return $this->pdo->query(
            "SELECT l.province,
                    COUNT(DISTINCT r.id) as race_count,
                    COUNT(DISTINCT r.loft_id) as loft_count,
                    SUM(r.entry_count) as total_entries,
                    MAX(r.release_time) as latest_race_time
             FROM races r
             LEFT JOIN lofts l ON r.loft_id = l.id
             WHERE r.status = 1 AND l.province IS NOT NULL AND l.province != ''
             GROUP BY l.province
             ORDER BY race_count DESC"
        )->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Phase 2 SEO: 获取某省份赛事列表（分页）
     */
    public function getByProvince($province, $page = 1, $perPage = 12)
    {
        $offset = ($page - 1) * $perPage;

        $stmt = $this->pdo->prepare(
            "SELECT r.*, l.name AS loft_name, l.province, l.id AS loft_id
             FROM races r
             LEFT JOIN lofts l ON r.loft_id = l.id
             WHERE r.status = 1 AND l.province = ?
             ORDER BY r.release_time DESC
             LIMIT ?, ?"
        );
        $stmt->execute([$province, $offset, $perPage]);
        $list = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $countStmt = $this->pdo->prepare(
            "SELECT COUNT(*) as cnt FROM races r LEFT JOIN lofts l ON r.loft_id = l.id WHERE r.status = 1 AND l.province = ?"
        );
        $countStmt->execute([$province]);
        $total = $countStmt->fetch(\PDO::FETCH_ASSOC)['cnt'] ?? 0;

        return [
            'list' => $list ?: [],
            'total' => $total,
            'page' => $page,
            'total_pages' => ceil($total / $perPage),
        ];
    }

    /**
     * P2: 品系赛事统计（该品系所有铭鸽的赛事成绩汇总）
     */
    public function getStrainRaceStats($strainId)
    {
        $stmt = $this->pdo->prepare(
            "SELECT
                COUNT(DISTINCT rr.id) as total_results,
                COUNT(DISTINCT rr.ring_number) as pigeon_count,
                COUNT(DISTINCT r.id) as race_count,
                MIN(rr.rank) as best_rank,
                MAX(rr.speed) as best_speed,
                ROUND(AVG(rr.speed), 2) as avg_speed,
                SUM(CASE WHEN rr.rank = 1 THEN 1 ELSE 0 END) as champion_count,
                SUM(CASE WHEN rr.rank <= 3 THEN 1 ELSE 0 END) as podium_count,
                SUM(CASE WHEN rr.rank <= 10 THEN 1 ELSE 0 END) as top10_count
             FROM race_results rr
             JOIN races r ON rr.race_id = r.id
             WHERE r.status = 1
               AND rr.ring_number IS NOT NULL AND rr.ring_number != ''
               AND rr.ring_number IN (
                   SELECT ring_number FROM pigeons WHERE strain_id = ? AND status = 1
               )"
        );
        $stmt->execute([$strainId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * P2: 品系参赛最佳鸽（该品系在赛事中表现最好的铭鸽）
     */
    public function getStrainTopPigeons($strainId, $limit = 8)
    {
        $stmt = $this->pdo->prepare(
            "SELECT p.id as pigeon_id, p.name as pigeon_name, p.ring_number, p.images,
                    rr.speed, rr.rank, rr.owner_name,
                    r.name as race_name, r.loft_id, l.name as loft_name
             FROM race_results rr
             JOIN races r ON rr.race_id = r.id
             JOIN pigeons p ON p.ring_number = rr.ring_number AND p.status = 1
             LEFT JOIN lofts l ON r.loft_id = l.id
             WHERE r.status = 1 AND p.strain_id = ? AND rr.rank > 0
             ORDER BY rr.rank ASC, rr.speed DESC
             LIMIT ?"
        );
        $stmt->execute([$strainId, $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * P2: 赛季总结 — 年度统计
     */
    public function getSeasonSummary($year)
    {
        // 主统计：只查 races + lofts，不碰 race_results（12.7M行）
        $stmt = $this->pdo->prepare(
            "SELECT
                COUNT(DISTINCT r.id) as race_count,
                COUNT(DISTINCT r.loft_id) as loft_count,
                SUM(r.entry_count) as total_entries,
                COUNT(DISTINCT l.city) as city_count,
                ROUND(AVG(r.distance_km), 1) as avg_distance
             FROM races r
             LEFT JOIN lofts l ON r.loft_id = l.id
             WHERE r.status = 1 AND r.season_year = ?"
        );
        $stmt->execute([$year]);
        $summary = $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        if (!$summary) return null;

        // 最快分速：子查询只取一条，强制走 speed 单列索引
        $stmt2 = $this->pdo->prepare(
            "SELECT rr.speed
             FROM race_results rr FORCE INDEX (idx_speed_only)
             JOIN races r ON rr.race_id = r.id
             WHERE rr.speed > 0 AND r.status = 1 AND r.season_year = ?
             ORDER BY rr.speed DESC
             LIMIT 1"
        );
        $stmt2->execute([$year]);
        $summary['fastest_speed'] = (float)($stmt2->fetchColumn() ?: 0);

        return $summary;
    }

    /**
     * P2: 赛季总结 — 月度赛事分布
     */
    public function getSeasonMonthlyDistribution($year)
    {
        $stmt = $this->pdo->prepare(
            "SELECT
                SUBSTR(r.release_time, 6, 2) as month,
                COUNT(*) as race_count,
                SUM(r.entry_count) as total_entries
             FROM races r
             WHERE r.status = 1 AND r.season_year = ? AND r.release_time IS NOT NULL
             GROUP BY month
             ORDER BY month"
        );
        $stmt->execute([$year]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * P2: 赛季总结 — 年度最佳分速 TOP10
     */
    public function getSeasonTopFastest($year, $limit = 10)
    {
        $stmt = $this->pdo->prepare(
            "SELECT rr.ring_number, rr.owner_name, rr.speed, rr.rank,
                    r.name as race_name, r.loft_id, l.name as loft_name, r.id as race_id
             FROM race_results rr FORCE INDEX (idx_speed_only)
             JOIN races r ON rr.race_id = r.id
             LEFT JOIN lofts l ON r.loft_id = l.id
             WHERE rr.speed > 0 AND r.status = 1 AND r.season_year = ?
             ORDER BY rr.speed DESC
             LIMIT ?"
        );
        $stmt->execute([$year, $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * P2: 赛季总结 — 年度最多赛事公棚 TOP10
     */
    public function getSeasonTopLofts($year, $limit = 10)
    {
        // Step 1: 先从 races 表取 TOP10（不 join race_results，极快）
        $stmt = $this->pdo->prepare(
            "SELECT l.id as loft_id, l.name, l.city, l.province,
                    COUNT(r.id) as race_count,
                    SUM(r.entry_count) as total_entries
             FROM races r
             JOIN lofts l ON r.loft_id = l.id
             WHERE r.status = 1 AND r.season_year = ?
             GROUP BY l.id
             ORDER BY race_count DESC
             LIMIT ?"
        );
        $stmt->execute([$year, $limit]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        if (empty($rows)) return [];

        // Step 2: 只对 TOP10 公棚查总成绩数
        // 强制从 races 驱动，先找到 10 个公棚的 ~300 场赛事，再走 idx_race 查成绩
        $loftIds = array_column($rows, 'loft_id');
        $phs = implode(',', array_fill(0, count($loftIds), '?'));
        $stmt2 = $this->pdo->prepare(
            "SELECT r2.loft_id, COUNT(*) as total_results
             FROM races r2
             JOIN race_results rr ON r2.id = rr.race_id
             WHERE r2.status = 1 AND r2.season_year = ? AND r2.loft_id IN ($phs)
             GROUP BY r2.loft_id"
        );
        $stmt2->execute(array_merge([$year], $loftIds));
        $countMap = [];
        foreach ($stmt2->fetchAll(\PDO::FETCH_ASSOC) as $c) {
            $countMap[$c['loft_id']] = (int)$c['total_results'];
        }

        // Step 3: 合并 total_results
        foreach ($rows as &$row) {
            $row['total_results'] = $countMap[$row['loft_id']] ?? 0;
        }
        return $rows;
    }

    /**
     * 获取赛事冠军鸽（rank=1）
     */
    public function getChampionPigeon($raceId)
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM race_results WHERE race_id = ? AND rank = 1 LIMIT 1"
        );
        $stmt->execute([$raceId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * 获取归巢时间分布（按开笼后小时分段）
     */
    public function getTimeDistribution($raceId)
    {
        $stmt = $this->pdo->prepare(
            "SELECT
                COUNT(*) as total,
                MIN(arrival_time) as first_arrival,
                MAX(arrival_time) as last_arrival,
                SUM(IF(diff_sec < 3600, 1, 0)) as lt_1h,
                SUM(IF(diff_sec >= 3600 AND diff_sec < 7200, 1, 0)) as h1_2,
                SUM(IF(diff_sec >= 7200 AND diff_sec < 14400, 1, 0)) as h2_4,
                SUM(IF(diff_sec >= 14400 AND diff_sec < 21600, 1, 0)) as h4_6,
                SUM(IF(diff_sec >= 21600 AND diff_sec < 28800, 1, 0)) as h6_8,
                SUM(IF(diff_sec >= 28800, 1, 0)) as gt_8h
             FROM (
                SELECT rr.arrival_time,
                    CASE
                        WHEN TIME_TO_SEC(IFNULL(rr.arrival_time, '00:00:00')) < TIME_TO_SEC(TIME(r.release_time))
                        THEN TIME_TO_SEC(rr.arrival_time) + 86400 - TIME_TO_SEC(TIME(r.release_time))
                        ELSE TIME_TO_SEC(rr.arrival_time) - TIME_TO_SEC(TIME(r.release_time))
                    END as diff_sec
                FROM race_results rr
                JOIN races r ON rr.race_id = r.id
                WHERE rr.race_id = ? AND rr.arrival_time IS NOT NULL AND rr.arrival_time != ''
             ) t"
        );
        $stmt->execute([$raceId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * 获取鸽主地区分布（TOP N）
     */
    public function getRegionDistribution($raceId, $limit = 10)
    {
        $stmt = $this->pdo->prepare(
            "SELECT region, COUNT(*) as cnt
             FROM race_results
             WHERE race_id = ? AND region IS NOT NULL AND region != ''
             GROUP BY region
             ORDER BY cnt DESC
             LIMIT ?"
        );
        $stmt->execute([$raceId, $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * P2: 品系赛事成绩 — 分页列表
     * 返回某品系下所有铭鸽的比赛成绩记录
     */
    public function getStrainRaceResults($strainId, $limit = 20, $offset = 0)
    {
        $stmt = $this->pdo->prepare(
            "SELECT rr.id, rr.ring_number, rr.owner_name, rr.speed, rr.rank,
                    rr.arrival_time, rr.color, rr.region,
                    r.name as race_name, r.distance_km, r.release_time, r.season_year,
                    l.name as loft_name, l.id as loft_id,
                    p.name as pigeon_name, p.id as pigeon_id
             FROM race_results rr
             JOIN races r ON rr.race_id = r.id AND r.status = 1
             JOIN pigeons p ON p.ring_number = rr.ring_number AND p.strain_id = ? AND p.status = 1
             LEFT JOIN lofts l ON r.loft_id = l.id
             WHERE rr.ring_number IS NOT NULL AND rr.ring_number != ''
             ORDER BY rr.speed DESC
             LIMIT ? OFFSET ?"
        );
        $stmt->execute([$strainId, $limit, $offset]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * P2: 品系赛事成绩 — 总数
     */
    public function getStrainRaceResultsCount($strainId)
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM race_results rr
             JOIN races r ON rr.race_id = r.id AND r.status = 1
             JOIN pigeons p ON p.ring_number = rr.ring_number AND p.strain_id = ? AND p.status = 1
             WHERE rr.ring_number IS NOT NULL AND rr.ring_number != ''"
        );
        $stmt->execute([$strainId]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * P2: 鸽主重叠度 — 本场鸽主在同城/同省其他赛事中的参赛情况
     */
    public function getOwnerOverlapInProvince($raceId, $province, $limit = 5)
    {
        // Step 1: 取本场 distinct owner（最多 100 个去重鸽主，先截断）
        $stmt = $this->pdo->prepare(
            "SELECT DISTINCT owner_name FROM race_results
             WHERE race_id = ? AND owner_name IS NOT NULL AND owner_name != ''
             LIMIT 100"
        );
        $stmt->execute([$raceId]);
        $owners = $stmt->fetchAll(\PDO::FETCH_COLUMN) ?: [];
        if (empty($owners)) return [];

        // Step 2: 用 IN 子查询 + 单表聚合，避免自联接爆炸
        $ph = rtrim(str_repeat('?,', count($owners)), ',');
        $params = array_merge($owners, [$raceId, $province, $limit]);
        $stmt = $this->pdo->prepare(
            "SELECT rr.owner_name, COUNT(DISTINCT rr.race_id) as other_race_count
             FROM race_results rr
             JOIN races r ON rr.race_id = r.id AND r.status = 1 AND r.id != ?
             JOIN lofts l ON r.loft_id = l.id AND l.province = ?
             WHERE rr.owner_name IN ($ph)
             GROUP BY rr.owner_name
             HAVING other_race_count > 0
             ORDER BY other_race_count DESC
             LIMIT ?"
        );
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * P2: 同公棚历史对比 — 同一公棚同赛季类型往年赛事
     */
    public function getLoftHistoryComparison($loftId, $seasonType, $currentYear, $limit = 3)
    {
        $stmt = $this->pdo->prepare(
            "SELECT r.id, r.name, r.season_year, r.entry_count, r.returned_count,
                    r.distance_km, r.release_time,
                    (SELECT MAX(speed) FROM race_results WHERE race_id = r.id AND speed > 0) as top_speed,
                    (SELECT AVG(speed) FROM race_results WHERE race_id = r.id AND speed > 0) as avg_speed
             FROM races r
             WHERE r.loft_id = ? AND r.season_type = ? AND r.season_year < ? AND r.status = 1
             ORDER BY r.season_year DESC
             LIMIT ?"
        );
        $stmt->execute([$loftId, $seasonType, $currentYear, $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * P2: 赛事业余评级 — 基于距离、归巢率、分速、参赛规模四维计算
     * 返回: level(EASY/MEDIUM/HARD/EXPERT), score(0-100), factors[]
     */
    public function getRaceDifficultyRating($raceId, $distanceKm, $returnRate, $entryCount)
    {
        // 距离得分: <200=20, 200-400=40, 400-600=60, 600-800=80, >800=100
        $distScore = min(100, max(10, ($distanceKm / 800) * 100));
        
        // 归巢率得分: >80%=10, 60-80=30, 40-60=50, 20-40=70, <20%=90 (越低越难)
        if ($returnRate >= 80) $retScore = 10;
        elseif ($returnRate >= 60) $retScore = 25;
        elseif ($returnRate >= 40) $retScore = 50;
        elseif ($returnRate >= 20) $retScore = 70;
        else $retScore = 90;
        
        // 规模得分: <500=10, 500-2000=30, 2000-5000=50, 5000-10000=70, >10000=90
        if ($entryCount < 500) $scaleScore = 10;
        elseif ($entryCount < 2000) $scaleScore = 30;
        elseif ($entryCount < 5000) $scaleScore = 50;
        elseif ($entryCount < 10000) $scaleScore = 70;
        else $scaleScore = 90;
        
        // 加权: 距离40% + 归巢率35% + 规模25%
        $score = round($distScore * 0.4 + $retScore * 0.35 + $scaleScore * 0.25);
        
        if ($score < 25) $level = 'EASY';
        elseif ($score < 50) $level = 'MEDIUM';
        elseif ($score < 75) $level = 'HARD';
        else $level = 'EXPERT';
        
        return [
            'level' => $level,
            'score' => $score,
            'dist_score' => round($distScore),
            'ret_score' => round($retScore),
            'scale_score' => round($scaleScore),
            'factors' => [
                'distance' => $distanceKm . 'km ' . ($distanceKm >= 500 ? '中长距离' : '短距离'),
                'return_rate' => $returnRate . '% ' . ($returnRate >= 60 ? '归巢良好' : ($returnRate >= 40 ? '中等归巢' : '低归巢率')),
                'scale' => number_format($entryCount) . '羽 ' . ($entryCount >= 2000 ? '大规模赛事' : ($entryCount >= 500 ? '中等规模' : '小型赛事')),
            ],
        ];
    }

    /**
     * 查询同一鸽主的其他足环号（用于足环深度报告）
     */
    public function getSameOwnerBirds($ownerName, $excludeRing, $limit = 20)
    {
        $stmt = $this->pdo->prepare(
            "SELECT ring_number, color, region, COUNT(*) as race_count, MAX(speed) as best_speed
             FROM race_results
             WHERE owner_name = ? AND ring_number != ?
             GROUP BY ring_number, color, region
             ORDER BY race_count DESC
             LIMIT " . intval($limit)
        );
        $stmt->execute([$ownerName, $excludeRing]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }
}
