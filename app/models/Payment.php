<?php
/**
 * 支付订单 Model（统一使用 member_orders 表）
 */
class Payment {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * 创建会员订单
     * @param int $userId 用户ID
     * @param int $toLevel 目标会员等级 (1=铜牌 2=银牌 3=金牌)
     * @param int $planType 套餐类型 1=月费 2=年费
     * @param float $amount 金额
     * @param string $payMethod 支付方式 wechat/alipay
     * @param string $productType 产品类型 (membership/report)
     * @param string|null $productRef 产品引用 (足环号等)
     * @return array 订单信息
     */
    public function createOrder($userId, $toLevel, $planType, $amount, $payMethod = 'wechat', $productType = 'membership', $productRef = null) {
        $prefix = $productType === 'report' ? 'RPT' : 'VIP';
        $orderNo = $prefix . date('YmdHis') . mt_rand(1000, 9999);
        $months = $planType === 2 ? 12 : 1;

        // 获取用户当前等级
        $stmt = $this->pdo->prepare("SELECT member_level FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $fromLevel = (int)($user['member_level'] ?? 0);

        $stmt = $this->pdo->prepare("
            INSERT INTO member_orders (user_id, order_no, from_level, to_level, plan_type, months, product_type, product_ref, amount, payment_method, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)
        ");
        $stmt->execute([$userId, $orderNo, $fromLevel, $toLevel, $planType, $months, $productType, $productRef, $amount, $payMethod]);

        return [
            'id'        => (int)$this->pdo->lastInsertId(),
            'order_no'  => $orderNo,
            'from_level' => $fromLevel,
            'to_level'  => $toLevel,
            'plan_type' => $planType,
            'months'    => $months,
            'amount'    => $amount,
        ];
    }

    public function getByOrderNo($orderNo) {
        $stmt = $this->pdo->prepare("SELECT * FROM member_orders WHERE order_no = ?");
        $stmt->execute([$orderNo]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM member_orders WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUserOrders($userId, $limit = 20, $offset = 0) {
        $stmt = $this->pdo->prepare("
            SELECT o.*,
                   CASE o.product_type
                       WHEN 'membership' THEN
                           CASE o.plan_type
                               WHEN 2 THEN '年度VIP'
                               ELSE '月度VIP'
                           END
                       WHEN 'report' THEN '足环深度报告'
                       ELSE IFNULL(o.product_type, '会员套餐')
                   END as plan_name
            FROM member_orders o
            WHERE o.user_id = :user_id
            ORDER BY o.created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':user_id', (int)$userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function markPaid($orderNo, $tradeNo) {
        $stmt = $this->pdo->prepare("
            UPDATE member_orders SET status = 1, payment_no = ?, paid_at = NOW() 
            WHERE order_no = ? AND status = 0
        ");
        return $stmt->execute([$tradeNo, $orderNo]);
    }

    public function markRefunded($orderNo) {
        $stmt = $this->pdo->prepare("UPDATE member_orders SET status = 3 WHERE order_no = ? AND status = 1");
        return $stmt->execute([$orderNo]);
    }

    public function markCancelled($orderNo) {
        $stmt = $this->pdo->prepare("UPDATE member_orders SET status = 2 WHERE order_no = ? AND status = 0");
        return $stmt->execute([$orderNo]);
    }

    public function isDuplicate($orderNo) {
        $stmt = $this->pdo->prepare("SELECT id FROM member_orders WHERE order_no = ? AND status = 1");
        $stmt->execute([$orderNo]);
        return (bool)$stmt->fetch();
    }

    public function getOrderStats($userId) {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as total,
                   SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as paid,
                   SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) as pending,
                   COALESCE(SUM(CASE WHEN status = 1 THEN amount ELSE 0 END), 0) as total_amount
            FROM member_orders WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * 激活会员（支付成功后调用）
     * 对于 product_type=report 的产品，改为记录解锁而非激活会员
     */
    public function activateMembership($orderNo) {
        $order = $this->getByOrderNo($orderNo);
        if (!$order || $order['status'] != 1) return false;

        // 深度报告产品：记录解锁
        if (($order['product_type'] ?? 'membership') === 'report') {
            return $this->recordRaceUnlock(
                (int)$order['user_id'],
                $order['product_ref'] ?? '',
                (int)$order['id']
            );
        }

        $userId = (int)$order['user_id'];
        $toLevel = (int)$order['to_level'];
        $months = (int)$order['months'];

        // 获取用户当前到期时间
        $stmt = $this->pdo->prepare("SELECT member_level, member_expire_at FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) return false;

        // 如果当前等级更高，取当前等级（可降级但保留高等级）
        // 到期时间叠加
        $baseTime = ($user['member_expire_at'] && strtotime($user['member_expire_at']) > time())
            ? $user['member_expire_at']
            : date('Y-m-d H:i:s');
        $expireAt = date('Y-m-d H:i:s', strtotime($baseTime . " +{$months} months"));

        // 如果目标等级更高才更新，否则只续期
        $currentLevel = (int)($user['member_level'] ?? 0);
        if ($toLevel > $currentLevel) {
            $stmt = $this->pdo->prepare("UPDATE users SET member_level = ?, member_expire_at = ? WHERE id = ?");
            $stmt->execute([$toLevel, $expireAt, $userId]);
        } else {
            $stmt = $this->pdo->prepare("UPDATE users SET member_expire_at = ? WHERE id = ?");
            $stmt->execute([$expireAt, $userId]);
        }

        return true;
    }

    /**
     * 记录足环号报告解锁
     */
    public function recordRaceUnlock($userId, $ring, $orderId = null) {
        $stmt = $this->pdo->prepare("
            INSERT IGNORE INTO member_race_unlocks (user_id, ring, order_id, paid_at)
            VALUES (?, ?, ?, NOW())
        ");
        return $stmt->execute([$userId, $ring, $orderId]);
    }

    /**
     * 检查用户是否已解锁指定足环号报告
     */
    public function isReportUnlocked($userId, $ring) {
        $stmt = $this->pdo->prepare("
            SELECT id FROM member_race_unlocks WHERE user_id = ? AND ring = ?
        ");
        $stmt->execute([$userId, $ring]);
        return (bool)$stmt->fetch();
    }

    // ========== 通用产品解锁 (certificate / compare) ==========

    /**
     * 检查产品是否已解锁
     * @param int $userId
     * @param string $productType 'certificate' | 'compare'
     * @param string $productRef cert_id 或 loft_ids
     * @return bool
     */
    public function isProductUnlocked($userId, $productType, $productRef) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT id FROM member_product_unlocks
                WHERE user_id = ? AND product_type = ? AND product_ref = ?
            ");
            $stmt->execute([$userId, $productType, $productRef]);
            return (bool)$stmt->fetch();
        } catch (\PDOException $e) {
            return false;
        }
    }

    /**
     * 记录产品解锁（支付成功后调用）
     */
    public function unlockProduct($userId, $productType, $productRef, $orderId = null) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT IGNORE INTO member_product_unlocks (user_id, product_type, product_ref, order_id, paid_at)
                VALUES (?, ?, ?, ?, NOW())
            ");
            return $stmt->execute([$userId, $productType, $productRef, $orderId ?? 0]);
        } catch (\PDOException $e) {
            return false;
        }
    }

    /**
     * 激活产品解锁（支付成功后统一入口）
     * 支持：report(RaceUnlock) / certificate / compare
     */
    public function activateProduct($orderNo) {
        $order = $this->getByOrderNo($orderNo);
        if (!$order || $order['status'] != 1) return false;

        $userId = (int)$order['user_id'];
        $productType = $order['product_type'] ?? 'membership';
        $productRef = $order['product_ref'] ?? '';

        switch ($productType) {
            case 'report':
                return $this->recordRaceUnlock($userId, $productRef, (int)$order['id']);
            case 'certificate':
            case 'compare':
                return $this->unlockProduct($userId, $productType, $productRef, (int)$order['id']);
            default:
                // membership: 激活会员等级
                return $this->activateMembership($orderNo);
        }
    }
}