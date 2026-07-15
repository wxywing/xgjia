<?php
/**
 * 展厅模型
 */
class Shop {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * 根据ID查找展厅
     */
    public function findById($id) {
        $stmt = $this->pdo->prepare("
            SELECT s.*, u.username as owner_name
            FROM shops s
            LEFT JOIN users u ON s.user_id = u.id
            WHERE s.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * 获取展厅列表
     */
    public function getList($options = []) {
        $where = ["1=1"];
        $params = [];

        if (!empty($options["province"])) {
            $where[] = "s.province = ?";
            $params[] = $options["province"];
        }
        if (!empty($options["keyword"])) {
            $where[] = "(s.name LIKE ? OR s.province LIKE ? OR s.description LIKE ?)";
            $keyword = "%" . $options["keyword"] . "%";
            $params[] = $keyword;
            $params[] = $keyword;
            $params[] = $keyword;
        }
        if (isset($options["is_certified"])) {
            $where[] = "s.is_certified = ?";
            $params[] = intval($options["is_certified"]);
        }
        if (isset($options["status"])) {
            $where[] = "s.status = ?";
            $params[] = intval($options["status"]);
        } else {
            $where[] = "s.status = 1";
        }

        $whereClause = implode(" AND ", $where);

        $orderBy = "s.is_hot DESC, s.pigeon_count DESC, s.updated_at DESC";
        if (!empty($options["order_by"])) {
            $allowed = ["s.created_at DESC", "s.pigeon_count DESC", "s.updated_at DESC"];
            if (in_array($options["order_by"], $allowed)) {
                $orderBy = $options["order_by"];
            }
        }

        $page = max(1, intval($options["page"] ?? 1));
        $perPage = intval($options["per_page"] ?? 12);
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT s.*, u.username as owner_name
                FROM shops s
                LEFT JOIN users u ON s.user_id = u.id
                WHERE {$whereClause}
                ORDER BY {$orderBy}
                LIMIT {$perPage} OFFSET {$offset}";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 获取展厅总数
     */
    public function getCount($options = []) {
        $where = ["1=1"];
        $params = [];

        if (!empty($options["province"])) {
            $where[] = "province = ?";
            $params[] = $options["province"];
        }
        if (!empty($options["keyword"])) {
            $where[] = "(name LIKE ? OR province LIKE ? OR description LIKE ?)";
            $keyword = "%" . $options["keyword"] . "%";
            $params[] = $keyword;
            $params[] = $keyword;
            $params[] = $keyword;
        }
        if (isset($options["is_certified"])) {
            $where[] = "is_certified = ?";
            $params[] = intval($options["is_certified"]);
        }
        if (isset($options["status"])) {
            $where[] = "status = ?";
            $params[] = intval($options["status"]);
        } else {
            $where[] = "status = 1";
        }

        $whereClause = implode(" AND ", $where);
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM shops WHERE {$whereClause}");
        $stmt->execute($params);
        return intval($stmt->fetchColumn());
    }

    /**
     * 获取热门展厅
     */
    public function getHot($limit = 6) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM shops
            WHERE status = 1
            ORDER BY is_hot DESC, pigeon_count DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 获取省份列表
     */
    public function getProvinces() {
        $stmt = $this->pdo->query("
            SELECT DISTINCT province FROM shops
            WHERE status = 1 AND province IS NOT NULL AND province != ''
            ORDER BY province
        ");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * 获取展厅的血系分类
     */
    public function getCategories($shopId) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM shop_categories
            WHERE shop_id = ?
            ORDER BY sort ASC, pigeon_count DESC
        ");
        $stmt->execute([$shopId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 获取展厅的展品（铭鸽）
     */
    public function getPigeons($shopId, $options = []) {
        $where = ["p.shop_id = ?"];
        $params = [$shopId];

        if (!empty($options["category"])) {
            $where[] = "p.category = ?";
            $params[] = $options["category"];
        }
        if (!empty($options["bloodline"])) {
            $where[] = "p.bloodline LIKE ?";
            $params[] = "%" . $options["bloodline"] . "%";
        }
        if (!empty($options["gender"])) {
            $where[] = "p.gender = ?";
            $params[] = intval($options["gender"]);
        }
        if (isset($options["status"])) {
            $where[] = "p.status = ?";
            $params[] = intval($options["status"]);
        } else {
            $where[] = "p.status = 1";
        }

        $whereClause = implode(" AND ", $where);
        $page = max(1, intval($options["page"] ?? 1));
        $perPage = intval($options["per_page"] ?? 20);
        $offset = ($page - 1) * $perPage;

        $stmt = $this->pdo->prepare("
            SELECT p.* FROM pigeons p
            WHERE {$whereClause}
            ORDER BY p.created_at DESC
            LIMIT {$perPage} OFFSET {$offset}
        ");
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 获取展厅展品总数
     */
    public function getPigeonCount($shopId, $options = []) {
        $where = ["p.shop_id = ?"];
        $params = [$shopId];

        if (!empty($options["category"])) {
            $where[] = "p.category = ?";
            $params[] = $options["category"];
        }
        if (!empty($options["bloodline"])) {
            $where[] = "p.bloodline LIKE ?";
            $params[] = "%" . $options["bloodline"] . "%";
        }

        $whereClause = implode(" AND ", $where);
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM pigeons p WHERE {$whereClause}");
        $stmt->execute($params);
        return intval($stmt->fetchColumn());
    }

    /**
     * 增加浏览量
     */
    public function incrementViews($id) {
        $stmt = $this->pdo->prepare("UPDATE shops SET views = COALESCE(views, 0) + 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * 创建展厅
     */
    public function create($data) {
        $fields = [];
        $placeholders = [];
        $values = [];
        $allowed = ["user_id", "source_id", "name", "avatar", "province", "city",
                    "address", "contact_name", "contact_phone", "description",
                    "website", "model", "pigeon_count", "is_certified", "is_hot", "status"];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = $field;
                $placeholders[] = "?";
                $values[] = $data[$field];
            }
        }

        $sql = "INSERT INTO shops (" . implode(", ", $fields) . ") VALUES (" . implode(", ", $placeholders) . ")";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($values);
    }

    /**
     * 更新展厅
     */
    public function update($id, $data) {
        $fields = [];
        $values = [];
        $allowed = ["name", "avatar", "province", "city", "address",
                    "contact_name", "contact_phone", "description", "website",
                    "pigeon_count", "is_certified", "is_hot", "status"];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = ?";
                $values[] = $data[$field];
            }
        }

        if (empty($fields)) return false;

        $values[] = $id;
        $sql = "UPDATE shops SET " .implode(", ", $fields) . " WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($values);
    }

    /**
     * 删除展厅
     */
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM shops WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
