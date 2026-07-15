<?php
require_once __DIR__ . '/../core/RingNormalizer.php';

use App\Core\RingNormalizer;

/**
 * 铭鸽模型
 */
class Pigeon {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * 根据ID获取铭鸽（含展厅信息）
     */
    public function findById($id) {
        $sql = "SELECT p.*, u.username, u.nickname, u.avatar, u.phone, u.member_level, c.name as category_name,
                       s.name as shop_name, s.id as shop_id_ref
                FROM pigeons p
                LEFT JOIN users u ON p.user_id = u.id
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN shops s ON p.shop_id = s.id
                WHERE p.id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * 获取铭鸽列表
     */
    public function getList($options = []) {
        $where = "WHERE p.status = 1";
        $params = [];
        
        if (!empty($options['category_id'])) {
            $where .= " AND p.category_id = ?";
            $params[] = $options['category_id'];
        }
        
        if (!empty($options['shop_id'])) {
            $where .= " AND p.shop_id = ?";
            $params[] = $options['shop_id'];
        }
        
        if (!empty($options['user_id'])) {
            $where .= " AND p.user_id = ?";
            $params[] = $options['user_id'];
        }
        
        if (!empty($options['keyword'])) {
            $where .= " AND (p.name LIKE ? OR p.bloodline LIKE ? OR p.ring_number LIKE ? OR p.description LIKE ?)";
            $keyword = "%{$options['keyword']}%";
            $params[] = $keyword;
            $params[] = $keyword;
            $params[] = $keyword;
            $params[] = $keyword;
        }
        
        // 血统筛选
        if (!empty($options['bloodline'])) {
            $where .= " AND p.bloodline LIKE ?";
            $params[] = "%{$options['bloodline']}%";
        }
        
        // 性别筛选
        if (isset($options['gender']) && $options['gender'] !== '' && $options['gender'] !== null) {
            $where .= " AND p.gender = ?";
            $params[] = (int)$options['gender'];
        }
        
        $orderBy = "ORDER BY p.is_top DESC, p.created_at DESC";
        
        if (!empty($options['sort']) && $options['sort'] === 'views') {
            $orderBy = "ORDER BY p.views DESC, p.created_at DESC";
        } elseif (!empty($options['sort']) && $options['sort'] === 'newest') {
            $orderBy = "ORDER BY p.created_at DESC";
        }
        
        $limit = "";
        
        if (!empty($options['limit'])) {
            $limit = "LIMIT " . (int)$options['limit'];
        }
        
        if (!empty($options['offset'])) {
            $limit .= " OFFSET " . (int)$options['offset'];
        }
        
        $sql = "SELECT p.*, u.username, u.nickname, u.avatar, c.name as category_name,
                       s.name as shop_name
                FROM pigeons p
                LEFT JOIN users u ON p.user_id = u.id
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN shops s ON p.shop_id = s.id
                $where
                $orderBy
                $limit";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * 获取铭鸽总数
     */
    public function getCount($options = []) {
        $where = "WHERE status = 1";
        $params = [];
        
        if (!empty($options['category_id'])) {
            $where .= " AND category_id = ?";
            $params[] = $options['category_id'];
        }
        
        if (!empty($options['shop_id'])) {
            $where .= " AND shop_id = ?";
            $params[] = $options['shop_id'];
        }
        
        if (!empty($options['user_id'])) {
            $where .= " AND user_id = ?";
            $params[] = $options['user_id'];
        }
        
        if (!empty($options['keyword'])) {
            $where .= " AND (name LIKE ? OR bloodline LIKE ? OR ring_number LIKE ? OR description LIKE ?)";
            $keyword = "%{$options['keyword']}%";
            $params[] = $keyword;
            $params[] = $keyword;
            $params[] = $keyword;
            $params[] = $keyword;
        }
        
        if (!empty($options['bloodline'])) {
            $where .= " AND bloodline LIKE ?";
            $params[] = "%{$options['bloodline']}%";
        }
        
        if (isset($options['gender']) && $options['gender'] !== '' && $options['gender'] !== null) {
            $where .= " AND gender = ?";
            $params[] = (int)$options['gender'];
        }
        
        $sql = "SELECT COUNT(*) FROM pigeons $where";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
    
    /**
     * 创建铭鸽
     */
    public function create($data) {
        $sql = "INSERT INTO pigeons (user_id, category_id, name, ring_number, bloodline, gender, description, images, video, achievements, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            intval($data['user_id']),
            intval($data['category_id']),
            trim($data['name']),
            trim($data['ring_number'] ?? ''),
            trim($data['bloodline'] ?? ''),
            intval($data['gender'] ?? 0),
            trim($data['description'] ?? ''),
            $data['images'] ?? '',
            $data['video'] ?? '',
            $data['achievements'] ?? '',
            intval($data['status'] ?? 1)
        ]);
    }
    
    /**
     * 更新铭鸽
     */
    public function update($id, $data) {
        $fields = [];
        $values = [];
        $intFields = ['category_id', 'gender', 'status', 'views', 'user_id'];
        $allowedFields = ['category_id', 'name', 'ring_number', 'bloodline', 'gender', 'color', 'eye_color', 'description', 'images', 'video', 'achievements', 'status'];
        
        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
                $fields[] = "$key = ?";
                $values[] = in_array($key, $intFields) ? intval($value) : (is_string($value) ? trim($value) : $value);
            }
        }
        
        if (empty($fields)) return false;
        
        $values[] = $id;
        $sql = "UPDATE pigeons SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($values);
    }
    
    /**
     * 删除铭鸽
     */
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM pigeons WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * 增加浏览次数
     */
    public function incrementViews($id) {
        $stmt = $this->pdo->prepare("UPDATE pigeons SET views = views + 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * 获取热门铭鸽
     */
    public function getHot($limit = 10) {
        $sql = "SELECT p.*, s.name as shop_name FROM pigeons p LEFT JOIN shops s ON p.shop_id = s.id WHERE p.status = 1 ORDER BY p.views DESC, p.created_at DESC LIMIT ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([(int)$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * 获取推荐铭鸽
     */
    public function getRecommend($limit = 10) {
        $sql = "SELECT p.*, s.name as shop_name FROM pigeons p LEFT JOIN shops s ON p.shop_id = s.id WHERE p.status = 1 AND p.is_recommend = 1 ORDER BY p.created_at DESC LIMIT ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([(int)$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * 获取展厅下的铭鸽
     */
    public function getByShopId($shopId, $page = 1, $limit = 12) {
        $offset = ($page - 1) * $limit;
        $sql = "SELECT * FROM pigeons WHERE shop_id = ? AND status = 1 ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$shopId, (int)$limit, (int)$offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * 获取展厅铭鸽总数
     */
    public function getCountByShopId($shopId) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM pigeons WHERE shop_id = ? AND status = 1");
        $stmt->execute([$shopId]);
        return $stmt->fetchColumn();
    }

    /**
     * 获取同品系铭鸽（排除自身）
     */
    public function getByStrainId($strainId, $excludeId = 0, $limit = 4)
    {
        $sql = "SELECT id, name, images, bloodline, ring_number, color, eye_color, gender FROM pigeons WHERE strain_id = ? AND id != ? AND status = 1 ORDER BY views DESC LIMIT ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$strainId, $excludeId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 根据足环号查找铭鸽（精确匹配，数据融合 Phase 3）
     */
    public function findByRingNumber($ringNumber)
    {
        $normalized = RingNormalizer::normalize($ringNumber);
        
        // First try exact match
        $stmt = $this->pdo->prepare("SELECT * FROM pigeons WHERE ring_number = ? AND status = 1 LIMIT 1");
        $stmt->execute([$ringNumber]);
        $pigeon = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($pigeon) return $pigeon;
        
        // Then try normalized match
        if ($normalized !== $ringNumber) {
            $stmt = $this->pdo->prepare("SELECT * FROM pigeons WHERE ring_number = ? AND status = 1 LIMIT 1");
            $stmt->execute([$normalized]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        return null;
    }
}
