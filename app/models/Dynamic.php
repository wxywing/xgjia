<?php
/**
 * 动态模型（鸽友圈）
 */
class Dynamic {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * 根据ID获取动态
     */
    public function findById($id) {
        $sql = "SELECT d.*, u.username, u.nickname, u.avatar
                FROM dynamics d
                LEFT JOIN users u ON d.user_id = u.id
                WHERE d.id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * 获取动态列表
     */
    public function getList($options = []) {
        $where = "WHERE 1=1";
        $params = [];
        
        if (!empty($options['user_id'])) {
            $where .= " AND d.user_id = ?";
            $params[] = $options['user_id'];
        }
        
        if (!empty($options['status'])) {
            $where .= " AND d.status = ?";
            $params[] = $options['status'];
        } else {
            $where .= " AND d.status = 1";
        }
        
        $orderBy = "ORDER BY d.created_at DESC";
        $limit = "";
        
        if (!empty($options['limit'])) {
            $limit = "LIMIT " . (int)$options['limit'];
        }
        
        if (!empty($options['offset'])) {
            $limit .= " OFFSET " . (int)$options['offset'];
        }
        
        $sql = "SELECT d.*, u.username, u.nickname, u.avatar
                FROM dynamics d
                LEFT JOIN users u ON d.user_id = u.id
                $where
                $orderBy
                $limit";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * 获取动态总数
     */
    public function getCount($options = []) {
        $where = "WHERE 1=1";
        $params = [];
        
        if (!empty($options['user_id'])) {
            $where .= " AND user_id = ?";
            $params[] = $options['user_id'];
        }
        
        if (!empty($options['status'])) {
            $where .= " AND status = ?";
            $params[] = $options['status'];
        } else {
            $where .= " AND status = 1";
        }
        
        $sql = "SELECT COUNT(*) FROM dynamics $where";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
    
    /**
     * 创建动态
     */
    public function create($data) {
        $sql = "INSERT INTO dynamics (user_id, content, images, likes, comments, status, created_at)
                VALUES (?, ?, ?, 0, 0, 1, NOW())";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['user_id'],
            $data['content'],
            $data['images'] ?? ''
        ]);
    }
    
    /**
     * 更新动态
     */
    public function update($id, $userId, $data) {
        $sql = "UPDATE dynamics SET content = ?, images = ? WHERE id = ? AND user_id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['content'],
            $data['images'] ?? '',
            $id,
            $userId
        ]);
    }

    /**
     * 删除动态
     */
    public function delete($id, $userId) {
        $stmt = $this->pdo->prepare("DELETE FROM dynamics WHERE id = ? AND user_id = ?");
        return $stmt->execute([$id, $userId]);
    }
    
    /**
     * 增加点赞
     */
    public function incrementLikes($id) {
        $stmt = $this->pdo->prepare("UPDATE dynamics SET likes = likes + 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * 减少点赞
     */
    public function decrementLikes($id) {
        $stmt = $this->pdo->prepare("UPDATE dynamics SET likes = GREATEST(0, likes - 1) WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * 增加评论
     */
    public function incrementComments($id) {
        $stmt = $this->pdo->prepare("UPDATE dynamics SET comments = comments + 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * 获取关注用户的动态
     */
    public function getFollowingDynamics($userId, $limit = 20, $offset = 0) {
        $sql = "SELECT d.*, u.username, u.nickname, u.avatar
                FROM dynamics d
                LEFT JOIN users u ON d.user_id = u.id
                LEFT JOIN follows f ON d.user_id = f.following_id
                WHERE f.follower_id = ? AND d.status = 1
                ORDER BY d.created_at DESC
                LIMIT ? OFFSET ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId, (int)$limit, (int)$offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
