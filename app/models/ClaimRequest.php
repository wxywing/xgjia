<?php
/**
 * 认领申请模型
 */
class ClaimRequest {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * 根据ID查找
     */
    public function findById($id) {
        $stmt = $this->pdo->prepare("
            SELECT cr.*, u.username, u.nickname, u.avatar,
                   s.name as shop_name, l.name as loft_name
            FROM claim_requests cr
            LEFT JOIN users u ON cr.user_id = u.id
            LEFT JOIN shops s ON cr.target_type = 'shop' AND cr.target_id = s.id
            LEFT JOIN lofts l ON cr.target_type = 'loft' AND cr.target_id = l.id
            WHERE cr.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * 创建认领申请
     */
    public function create($data) {
        // 检查是否已有待审核申请
        $existing = $this->hasPending($data['user_id'], $data['target_type'], $data['target_id']);
        if ($existing) {
            return false; // 已有待审核申请
        }

        $sql = "INSERT INTO claim_requests (user_id, target_type, target_id, real_name, phone, wechat, evidence, reason, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['user_id'],
            $data['target_type'],
            $data['target_id'],
            $data['real_name'],
            $data['phone'],
            $data['wechat'] ?? '',
            $data['evidence'] ?? '',
            $data['reason']
        ]);
    }

    /**
     * 检查是否有待审核申请
     */
    public function hasPending($userId, $targetType, $targetId) {
        $stmt = $this->pdo->prepare("
            SELECT id FROM claim_requests
            WHERE user_id = ? AND target_type = ? AND target_id = ? AND status = 0
        ");
        $stmt->execute([$userId, $targetType, $targetId]);
        return $stmt->fetch() ? true : false;
    }

    /**
     * 获取用户的认领申请
     */
    public function getByUser($userId, $options = []) {
        $where = "WHERE cr.user_id = ?";
        $params = [$userId];

        if (isset($options['status'])) {
            $where .= " AND cr.status = ?";
            $params[] = intval($options['status']);
        }

        $orderBy = "ORDER BY cr.created_at DESC";
        $page = max(1, intval($options['page'] ?? 1));
        $perPage = intval($options['per_page'] ?? 10);
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT cr.*, s.name as shop_name, l.name as loft_name
                FROM claim_requests cr
                LEFT JOIN shops s ON cr.target_type = 'shop' AND cr.target_id = s.id
                LEFT JOIN lofts l ON cr.target_type = 'loft' AND cr.target_id = l.id
                {$where}
                {$orderBy}
                LIMIT {$perPage} OFFSET {$offset}";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 获取用户认领申请总数
     */
    public function getCountByUser($userId, $status = null) {
        $where = "WHERE user_id = ?";
        $params = [$userId];
        if ($status !== null) {
            $where .= " AND status = ?";
            $params[] = intval($status);
        }
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM claim_requests {$where}");
        $stmt->execute($params);
        return intval($stmt->fetchColumn());
    }

    /**
     * 获取目标的认领申请（管理员用）
     */
    public function getByTarget($targetType, $targetId, $status = null) {
        $where = "WHERE cr.target_type = ? AND cr.target_id = ?";
        $params = [$targetType, $targetId];

        if ($status !== null) {
            $where .= " AND cr.status = ?";
            $params[] = intval($status);
        }

        $sql = "SELECT cr.*, u.username, u.nickname, u.avatar
                FROM claim_requests cr
                LEFT JOIN users u ON cr.user_id = u.id
                {$where}
                ORDER BY cr.created_at ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 管理员：获取所有认领申请列表
     */
    public function getList($options = []) {
        $where = "WHERE 1=1";
        $params = [];

        if (!empty($options['target_type'])) {
            $where .= " AND cr.target_type = ?";
            $params[] = $options['target_type'];
        }
        if (isset($options['status'])) {
            $where .= " AND cr.status = ?";
            $params[] = intval($options['status']);
        }
        if (!empty($options['keyword'])) {
            $where .= " AND (cr.real_name LIKE ? OR cr.phone LIKE ? OR cr.reason LIKE ?)";
            $kw = "%" . $options['keyword'] . "%";
            $params[] = $kw;
            $params[] = $kw;
            $params[] = $kw;
        }

        $page = max(1, intval($options['page'] ?? 1));
        $perPage = intval($options['per_page'] ?? 15);
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT cr.*, u.username, u.nickname, u.avatar,
                       s.name as shop_name, l.name as loft_name
                FROM claim_requests cr
                LEFT JOIN users u ON cr.user_id = u.id
                LEFT JOIN shops s ON cr.target_type = 'shop' AND cr.target_id = s.id
                LEFT JOIN lofts l ON cr.target_type = 'loft' AND cr.target_id = l.id
                {$where}
                ORDER BY cr.status ASC, cr.created_at DESC
                LIMIT {$perPage} OFFSET {$offset}";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 管理员：获取认领申请总数
     */
    public function getCount($options = []) {
        $where = "WHERE 1=1";
        $params = [];

        if (!empty($options['target_type'])) {
            $where .= " AND target_type = ?";
            $params[] = $options['target_type'];
        }
        if (isset($options['status'])) {
            $where .= " AND status = ?";
            $params[] = intval($options['status']);
        }

        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM claim_requests {$where}");
        $stmt->execute($params);
        return intval($stmt->fetchColumn());
    }

    /**
     * 审核通过
     */
    public function approve($id, $adminId, $note = '') {
        $claim = $this->findById($id);
        if (!$claim || $claim['status'] != 0) return false;

        // 更新申请状态
        $stmt = $this->pdo->prepare("
            UPDATE claim_requests SET status = 1, admin_note = ?, reviewed_at = NOW(), reviewed_by = ?
            WHERE id = ? AND status = 0
        ");
        $result = $stmt->execute([$note, $adminId, $id]);

        if ($result) {
            // 转移所有权
            $table = $claim['target_type'] === 'shop' ? 'shops' : 'lofts';
            $stmt = $this->pdo->prepare("UPDATE {$table} SET user_id = ?, status = 1 WHERE id = ?");
            $stmt->execute([$claim['user_id'], $claim['target_id']]);

            // 拒绝同一目标的其他待审核申请
            $stmt = $this->pdo->prepare("
                UPDATE claim_requests SET status = 2, admin_note = '已有其他申请通过审核', reviewed_at = NOW(), reviewed_by = ?
                WHERE target_type = ? AND target_id = ? AND status = 0 AND id != ?
            ");
            $stmt->execute([$adminId, $claim['target_type'], $claim['target_id'], $id]);
        }

        return $result;
    }

    /**
     * 审核拒绝
     */
    public function reject($id, $adminId, $note = '') {
        $stmt = $this->pdo->prepare("
            UPDATE claim_requests SET status = 2, admin_note = ?, reviewed_at = NOW(), reviewed_by = ?
            WHERE id = ? AND status = 0
        ");
        return $stmt->execute([$note, $adminId, $id]);
    }

    /**
     * 用户取消申请
     */
    public function cancel($id, $userId) {
        $stmt = $this->pdo->prepare("
            UPDATE claim_requests SET status = 3
            WHERE id = ? AND user_id = ? AND status = 0
        ");
        return $stmt->execute([$id, $userId]);
    }

    /**
     * 统计各状态数量
     */
    public function getStatusCounts() {
        $stmt = $this->pdo->query("
            SELECT status, COUNT(*) as cnt FROM claim_requests GROUP BY status
        ");
        $counts = [0 => 0, 1 => 0, 2 => 0, 3 => 0];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $counts[intval($row['status'])] = intval($row['cnt']);
        }
        return $counts;
    }
}
