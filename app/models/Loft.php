<?php
/**
 * 公棚模型
 */
class Loft {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * 根据ID查找公棚
     */
    public function findById($id) {
        $stmt = $this->pdo->prepare("
            SELECT l.*, u.username as owner_name,
                   c.name as category_name
            FROM lofts l
            LEFT JOIN users u ON l.user_id = u.id
            LEFT JOIN categories c ON c.type = 4
            WHERE l.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * 获取公棚列表
     */
    public function getList($options = []) {
        $where = ['1=1'];
        $params = [];

        if (!empty($options['province'])) {
            $where[] = 'l.province = ?';
            $params[] = $options['province'];
        }
        if (!empty($options['city'])) {
            $where[] = 'l.city = ?';
            $params[] = $options['city'];
        }
        if (!empty($options['race_type'])) {
            $where[] = 'l.race_type = ?';
            $params[] = $options['race_type'];
        }
        if (isset($options['is_certified'])) {
            $where[] = 'l.is_certified = ?';
            $params[] = intval($options['is_certified']);
        }
        if (isset($options['status'])) {
            $where[] = 'l.status = ?';
            $params[] = intval($options['status']);
        } else {
            $where[] = 'l.status = 1';
        }
        if (!empty($options['keyword'])) {
            $where[] = '(l.name LIKE ? OR l.province LIKE ? OR l.city LIKE ?)';
            $keyword = '%' . $options['keyword'] . '%';
            $params[] = $keyword;
            $params[] = $keyword;
            $params[] = $keyword;
        }

        $whereClause = implode(' AND ', $where);

        // 排序（默认最新优先）
        $orderBy = 'l.id DESC';
        if (!empty($options['order_by'])) {
            $allowed = ['l.created_at DESC', 'l.views DESC', 'l.rating DESC', 'l.prize_pool DESC', 'l.current_count DESC'];
            if (in_array($options['order_by'], $allowed)) {
                $orderBy = $options['order_by'];
            }
        }

        $page = max(1, intval($options['page'] ?? 1));
        $perPage = intval($options['per_page'] ?? 12);
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT l.*, u.username as owner_name
                FROM lofts l
                LEFT JOIN users u ON l.user_id = u.id
                WHERE {$whereClause}
                ORDER BY {$orderBy}
                LIMIT {$perPage} OFFSET {$offset}";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 获取公棚总数
     */
    public function getCount($options = []) {
        $where = ['1=1'];
        $params = [];

        if (!empty($options['province'])) {
            $where[] = 'province = ?';
            $params[] = $options['province'];
        }
        if (!empty($options['city'])) {
            $where[] = 'city = ?';
            $params[] = $options['city'];
        }
        if (!empty($options['race_type'])) {
            $where[] = 'race_type = ?';
            $params[] = $options['race_type'];
        }
        if (isset($options['is_certified'])) {
            $where[] = 'is_certified = ?';
            $params[] = intval($options['is_certified']);
        }
        if (isset($options['status'])) {
            $where[] = 'status = ?';
            $params[] = intval($options['status']);
        } else {
            $where[] = 'status = 1';
        }
        if (!empty($options['keyword'])) {
            $where[] = '(name LIKE ? OR province LIKE ? OR city LIKE ?)';
            $keyword = '%' . $options['keyword'] . '%';
            $params[] = $keyword;
            $params[] = $keyword;
            $params[] = $keyword;
        }

        $whereClause = implode(' AND ', $where);
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM lofts WHERE {$whereClause}");
        $stmt->execute($params);
        return intval($stmt->fetchColumn());
    }

    /**
     * 获取热门公棚
     */
    public function getHot($limit = 6) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM lofts
            WHERE status = 1
            ORDER BY is_hot DESC, rating DESC, views DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 获取认证公棚
     */
    public function getCertified($limit = 10) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM lofts
            WHERE status = 1 AND is_certified = 1
            ORDER BY rating DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 获取省份列表（用于筛选）
     */
    public function getProvinces() {
        $stmt = $this->pdo->query("
            SELECT DISTINCT province FROM lofts
            WHERE status = 1 AND province IS NOT NULL AND province != ''
            ORDER BY province
        ");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * 获取某省份公棚统计数据
     */
    public function getProvinceStats($province) {
        $stmt = $this->pdo->prepare("
            SELECT
                COUNT(*) as loft_count,
                COUNT(DISTINCT city) as city_count
            FROM lofts
            WHERE status = 1 AND province = ?
        ");
        $stmt->execute([$province]);
        $stats = $stmt->fetch(\PDO::FETCH_ASSOC) ?: ['loft_count' => 0, 'city_count' => 0];

        // 赛事统计（通过 lofts JOIN races）
        $stmt2 = $this->pdo->prepare("
            SELECT COUNT(DISTINCT r.id) as race_count, SUM(r.entry_count) as total_entries
            FROM races r JOIN lofts l ON r.loft_id = l.id
            WHERE r.status = 1 AND l.province = ?
        ");
        $stmt2->execute([$province]);
        $raceStats = $stmt2->fetch(\PDO::FETCH_ASSOC) ?: ['race_count' => 0, 'total_entries' => 0];

        return array_merge($stats, $raceStats);
    }

    /**
     * 获取某省份下的城市列表（含公棚数）
     */
    public function getCitiesByProvince($province) {
        $stmt = $this->pdo->prepare("
            SELECT city, COUNT(*) as loft_count
            FROM lofts
            WHERE status = 1 AND province = ? AND city IS NOT NULL AND city != ''
            GROUP BY city
            ORDER BY loft_count DESC
        ");
        $stmt->execute([$province]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * 获取某城市公棚统计数据
     */
    public function getCityStats($city) {
        $stmt = $this->pdo->prepare("
            SELECT
                COUNT(*) as loft_count,
                COUNT(DISTINCT province) as province_count
            FROM lofts
            WHERE status = 1 AND city = ?
        ");
        $stmt->execute([$city]);
        $stats = $stmt->fetch(\PDO::FETCH_ASSOC) ?: ['loft_count' => 0, 'province_count' => 0];

        // 赛事统计（通过 lofts JOIN races）
        $stmt2 = $this->pdo->prepare("
            SELECT COUNT(DISTINCT r.id) as race_count, SUM(r.entry_count) as total_entries
            FROM races r JOIN lofts l ON r.loft_id = l.id
            WHERE r.status = 1 AND l.city = ?
        ");
        $stmt2->execute([$city]);
        $raceStats = $stmt2->fetch(\PDO::FETCH_ASSOC) ?: ['race_count' => 0, 'total_entries' => 0];

        return array_merge($stats, $raceStats);
    }

    /**
     * 获取公棚参赛记录
     */
    public function getEntries($loftId, $options = []) {
        $where = ['le.loft_id = ?'];
        $params = [$loftId];

        if (isset($options['status'])) {
            $where[] = 'le.status = ?';
            $params[] = intval($options['status']);
        }
        if (!empty($options['user_id'])) {
            $where[] = 'le.user_id = ?';
            $params[] = intval($options['user_id']);
        }

        $whereClause = implode(' AND ', $where);
        $page = max(1, intval($options['page'] ?? 1));
        $perPage = intval($options['per_page'] ?? 20);
        $offset = ($page - 1) * $perPage;

        $stmt = $this->pdo->prepare("
            SELECT le.*, u.username, u.avatar
            FROM loft_entries le
            LEFT JOIN users u ON le.user_id = u.id
            WHERE {$whereClause}
            ORDER BY le.created_at DESC
            LIMIT {$perPage} OFFSET {$offset}
        ");
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 获取参赛记录总数
     */
    public function getEntryCount($loftId) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM loft_entries WHERE loft_id = ?");
        $stmt->execute([$loftId]);
        return intval($stmt->fetchColumn());
    }

    /**
     * 获取公棚评价
     */
    public function getReviews($loftId, $limit = 10, $offset = 0) {
        $stmt = $this->pdo->prepare("
            SELECT lr.*, u.username, u.avatar
            FROM loft_reviews lr
            LEFT JOIN users u ON lr.user_id = u.id
            WHERE lr.loft_id = ? AND lr.status = 1
            ORDER BY lr.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$loftId, $limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 增加浏览量
     */
    public function incrementViews($id) {
        $stmt = $this->pdo->prepare("UPDATE lofts SET views = views + 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * 创建公棚
     */
    public function create($data) {
        $fields = [];
        $placeholders = [];
        $values = [];
        $allowed = ['user_id', 'name', 'province', 'city', 'address', 'contact_name', 'contact_phone',
                    'logo', 'photos', 'description', 'capacity', 'entry_fee', 'management_fee',
                    'prize_pool', 'prize_detail', 'race_distance', 'race_type',
                    'collect_start', 'collect_end', 'training_start', 'race_date',
                    'rules', 'facilities', 'status'];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = $field;
                $placeholders[] = '?';
                $values[] = $data[$field];
            }
        }

        $sql = "INSERT INTO lofts (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($values);
    }

    /**
     * 更新公棚
     */
    public function update($id, $data) {
        $fields = [];
        $values = [];
        $allowed = ['name', 'province', 'city', 'address', 'contact_name', 'contact_phone',
                    'logo', 'photos', 'description', 'capacity', 'current_count',
                    'entry_fee', 'management_fee', 'prize_pool', 'prize_detail',
                    'race_distance', 'race_type', 'collect_start', 'collect_end',
                    'training_start', 'race_date', 'rules', 'facilities',
                    'is_certified', 'is_hot', 'status',
                    'source_url', 'wechat', 'website', 'lat', 'lng'];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = ?";
                $values[] = $data[$field];
            }
        }

        if (empty($fields)) return false;

        $values[] = $id;
        $sql = "UPDATE lofts SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($values);
    }

    /**
     * 获取公棚动态/公告
     */
    public function getNews($loftId, $limit = 10) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM loft_news
                WHERE loft_id = ?
                ORDER BY published_at DESC, created_at DESC
                LIMIT ?
            ");
            $stmt->execute([$loftId, $limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * 获取公棚相册
     * @param int $loftId
     * @param string|null $category 分类筛选，为空返回全部
     * @param int $limit
     * @return array
     */
    public function getPhotos($loftId, $category = null, $limit = 20) {
        try {
            $sql = "SELECT * FROM loft_photos WHERE loft_id = ?";
            $params = [$loftId];
            if ($category) {
                $sql .= " AND category = ?";
                $params[] = $category;
            }
            $sql .= " ORDER BY sort_order ASC, created_at DESC LIMIT ?";
            $params[] = $limit;
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * 获取公棚相册（按分类分组）
     * @param int $loftId
     * @return array [category => [photos...]]
     */
    public function getPhotosGrouped($loftId) {
        $photos = $this->getPhotos($loftId, null, 50);
        $grouped = [];
        $catNames = [
            'loft' => '鸽棚全景',
            'pigeon' => '赛鸽照片',
            'award' => '颁奖现场',
            'training' => '训放记录',
            'other' => '其他',
        ];
        foreach ($photos as $p) {
            $cat = $p['category'] ?: 'other';
            if (!isset($grouped[$cat])) {
                $grouped[$cat] = ['name' => $catNames[$cat] ?? $cat, 'photos' => []];
            }
            $grouped[$cat]['photos'][] = $p;
        }
        return $grouped;
    }

    /**
     * 删除公棚
     */
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM lofts WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * 获取公棚对比数据（含赛事统计）
     * @param array $ids
     * @return array
     */
    public function getCompareData(array $ids) {
        if (empty($ids)) return [];
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        
        $sql = "SELECT l.*,
                    COUNT(DISTINCT r.id) as race_count,
                    COALESCE(COUNT(rr.id), 0) as result_count,
                    COALESCE(MAX(rr.speed), 0) as best_speed,
                    COALESCE(ROUND(AVG(rr.speed), 1), 0) as avg_speed
                FROM lofts l
                LEFT JOIN races r ON r.loft_id = l.id AND r.status = 1
                LEFT JOIN race_results rr ON rr.race_id = r.id
                WHERE l.id IN ({$placeholders})
                GROUP BY l.id";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($ids);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * 搜索公棚（用于对比选择器）
     */
    public function search($keyword, $limit = 20) {
        $stmt = $this->pdo->prepare(
            "SELECT id, name, province, city, race_type, entry_fee, prize_pool, capacity, rating
             FROM lofts WHERE status = 1 AND name LIKE ? ORDER BY views DESC LIMIT ?"
        );
        $stmt->execute(["%{$keyword}%", $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * 公棚搜索自动补全（API）
     */
    public function autocomplete($q, $limit = 10) {
        $stmt = $this->pdo->prepare(
            "SELECT id, name, province FROM lofts 
             WHERE status = 1 AND name LIKE ? 
             ORDER BY views DESC LIMIT ?"
        );
        $stmt->execute(["%{$q}%", $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
