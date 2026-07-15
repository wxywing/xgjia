<?php
/**
 * 分类信息模型
 */
class Listing {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * 根据ID获取分类信息
     */
    public function findById($id) {
        $sql = "SELECT l.*, u.username, u.nickname, u.avatar
                FROM listings l
                LEFT JOIN users u ON l.user_id = u.id
                WHERE l.id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * 获取分类信息列表
     */
    public function getList($options = []) {
        $where = "WHERE 1=1";
        $params = [];
        
        if (!empty($options['type'])) {
            $where .= " AND l.type = ?";
            $params[] = $options['type'];
        }
        
        if (!empty($options['user_id'])) {
            $where .= " AND l.user_id = ?";
            $params[] = $options['user_id'];
        }
        
        if (!empty($options['status'])) {
            $where .= " AND l.status = ?";
            $params[] = $options['status'];
        } else {
            $where .= " AND l.status IN (1, 3, 4)";
        }
        
        if (!empty($options['keyword'])) {
            $where .= " AND (l.title LIKE ? OR l.description LIKE ?)";
            $keyword = "%{$options['keyword']}%";
            $params[] = $keyword;
            $params[] = $keyword;
        }
        
        if (!empty($options['location'])) {
            $where .= " AND l.location LIKE ?";
            $params[] = "%{$options['location']}%";
        }
        
        $orderBy = "ORDER BY l.created_at DESC";
        $limit = "";
        
        if (!empty($options['limit'])) {
            $limit = "LIMIT " . (int)$options['limit'];
        }
        
        if (!empty($options['offset'])) {
            $limit .= " OFFSET " . (int)$options['offset'];
        }
        
        $sql = "SELECT l.*, u.username, u.nickname, u.avatar
                FROM listings l
                LEFT JOIN users u ON l.user_id = u.id
                $where
                $orderBy
                $limit";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * 获取分类信息总数
     */
    public function getCount($options = []) {
        $where = "WHERE 1=1";
        $params = [];
        
        if (!empty($options['type'])) {
            $where .= " AND type = ?";
            $params[] = $options['type'];
        }
        
        if (!empty($options['user_id'])) {
            $where .= " AND user_id = ?";
            $params[] = $options['user_id'];
        }
        
        if (!empty($options['status'])) {
            $where .= " AND status = ?";
            $params[] = $options['status'];
        } else {
            $where .= " AND status IN (1, 3, 4)";
        }
        
        if (!empty($options['keyword'])) {
            $where .= " AND (title LIKE ? OR description LIKE ?)";
            $keyword = "%{$options['keyword']}%";
            $params[] = $keyword;
            $params[] = $keyword;
        }
        
        if (!empty($options['location'])) {
            $where .= " AND location LIKE ?";
            $params[] = "%{$options['location']}%";
        }
        
        $sql = "SELECT COUNT(*) FROM listings $where";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
    
    /**
     * 创建分类信息
     */
    public function create($data) {
        $sql = "INSERT INTO listings (user_id, type, title, description, images, 
                contact_name, contact_phone, contact_wechat, price, negotiable, location, status, expire_at, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $this->pdo->prepare($sql);
        
        // 获取审核设置
        $requireAudit = $this->getAuditSetting();
        $status = $requireAudit ? 0 : 1;
        
        // 设置过期时间（30天后）
        $expireAt = date('Y-m-d H:i:s', strtotime('+30 days'));
        
        return $stmt->execute([
            $data['user_id'],
            $data['type'],
            $data['title'],
            $data['description'] ?? '',
            $data['images'] ?? '',
            $data['contact_name'] ?? '',
            $data['contact_phone'] ?? '',
            $data['contact_wechat'] ?? '',
            $data['price'] ?? null,
            $data['negotiable'] ?? 0,
            $data['location'] ?? '',
            $status,
            $expireAt
        ]);
    }
    
    /**
     * 更新分类信息
     */
    public function update($id, $data) {
        $fields = [];
        $values = [];
        
        $allowedFields = ['type', 'title', 'description', 'images', 'contact_name', 
                         'contact_phone', 'contact_wechat', 'price', 'negotiable', 'location', 'status'];
        
        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
                $fields[] = "$key = ?";
                $values[] = $value;
            }
        }
        
        $values[] = $id;
        $sql = "UPDATE listings SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($values);
    }
    
    /**
     * 删除分类信息
     */
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM listings WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * 增加浏览次数
     */
    public function incrementViews($id) {
        $stmt = $this->pdo->prepare("UPDATE listings SET views = views + 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * 获取审核设置
     */
    private function getAuditSetting() {
        $stmt = $this->pdo->prepare("SELECT value FROM settings WHERE `key` = 'audit_listing'");
        $stmt->execute();
        $result = $stmt->fetchColumn();
        return $result === '1';
    }
    
    /**
     * 获取热门分类信息
     */
    public function getHot($limit = 10) {
        $sql = "SELECT l.*, u.username, u.nickname, u.avatar
                FROM listings l
                LEFT JOIN users u ON l.user_id = u.id
                WHERE l.status IN (1, 3, 4) 
                ORDER BY l.views DESC, l.created_at DESC 
                LIMIT ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([(int)$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * 获取最新分类信息
     */
    public function getLatest($limit = 10) {
        $sql = "SELECT l.*, u.username, u.nickname, u.avatar
                FROM listings l
                LEFT JOIN users u ON l.user_id = u.id
                WHERE l.status IN (1, 3, 4) 
                ORDER BY l.created_at DESC 
                LIMIT ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([(int)$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
