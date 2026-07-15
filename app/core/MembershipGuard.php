<?php
/**
 * 会员权限校验中间件
 * 用于所有发布和查看操作的权限检查
 */

require_once __DIR__ . '/../models/User.php';

class MembershipGuard {
    private $pdo;
    
    private static $PLAN_CACHE = null;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * 获取当前用户会员信息
     */
    public function getMemberInfo($userId) {
        $userModel = new User($this->pdo);
        $user = $userModel->findById($userId);
        
        if (!$user) {
            return ['error' => '用户不存在'];
        }
        
        $plan = $this->getPlan($user['member_level']);
        $stats = $this->getStats($userId);
        $isValid = $user['member_level'] > 0 
            && $user['member_expire_at'] 
            && strtotime($user['member_expire_at']) > time();
        
        return [
            'user_id' => $userId,
            'level' => (int)$user['member_level'],
            'expire_at' => $user['member_expire_at'],
            'is_valid' => $isValid,
            'plan' => $plan,
            'stats' => $stats,
            'can_upgrade' => $user['member_level'] < 3
        ];
    }
    
    /**
     * 检查用户是否可以发布指定类型内容
     */
    public function canPublish($userId, $type) {
        $member = $this->getMemberInfo($userId);
        
        if (isset($member['error'])) {
            return ['allowed' => false, 'code' => 'user_not_found', 'message' => $member['error']];
        }
        
        // 免费会员不允许发布任何内容
        if ($member['level'] === 0) {
            return [
                'allowed' => false,
                'code' => 'membership_required',
                'message' => '开通VIP会员后可发布内容',
                'redirect' => '/user/membership'
            ];
        }
        
        // 检查会员是否有效
        if (!$member['is_valid']) {
            return [
                'allowed' => false,
                'code' => 'member_expired',
                'message' => '您的会员已到期，请续费后继续发布',
                'redirect' => '/user/membership'
            ];
        }
        
        $stats = $member['stats'];
        $plan = $member['plan'];
        
        switch ($type) {
            case 'article':
                $limit = $plan['article_limit'];
                $used = $stats['article_count'];
                $name = '文章';
                break;
            case 'pigeon':
                $limit = $plan['pigeon_limit'];
                $used = $stats['pigeon_count'];
                $name = '铭鸽';
                break;
            case 'listing':
                $limit = $plan['listing_limit'];
                $used = $stats['listing_count'];
                $name = '分类信息';
                break;
            case 'dynamic':
                $limit = $plan['dynamic_daily_limit'];
                $used = $stats['dynamic_count'];
                $name = '动态';
                break;
            default:
                return ['allowed' => false, 'code' => 'invalid_type', 'message' => '无效的内容类型'];
        }
        
        // 0 表示不限
        if ($limit > 0 && $used >= $limit) {
            return [
                'allowed' => false,
                'code' => 'quota_exceeded',
                'message' => "本月{$name}发布额度已用完（{$used}/{$limit}），请升级会员获取更多额度",
                'redirect' => '/user/membership',
                'used' => $used,
                'limit' => $limit
            ];
        }
        
        return [
            'allowed' => true,
            'remaining' => $limit > 0 ? $limit - $used : 'unlimited',
            'message' => ''
        ];
    }
    
    /**
     * 检查是否可以查看联系方式
     */
    public function canViewContact($userId) {
        $member = $this->getMemberInfo($userId);
        
        if (isset($member['error'])) {
            return false;
        }
        
        if ($member['level'] === 0) {
            return false;
        }
        
        if (!$member['is_valid']) {
            return false;
        }
        
        return (bool)$member['plan']['can_view_contact'];
    }
    
    /**
     * 检查是否可以置顶内容
     */
    public function canTopContent($userId) {
        $member = $this->getMemberInfo($userId);
        
        if (isset($member['error']) || !$member['is_valid'] || $member['level'] === 0) {
            return ['allowed' => false];
        }
        
        $plan = $member['plan'];
        $stats = $member['stats'];
        
        if (!$plan['can_top_content']) {
            return ['allowed' => false, 'message' => '银牌及以上会员可置顶内容'];
        }
        
        $monthly = $plan['top_monthly'];
        $used = $stats['top_used'];
        
        if ($monthly > 0 && $used >= $monthly) {
            return [
                'allowed' => false,
                'message' => "本月置顶次数已用完（{$used}/{$monthly}），可购买增值服务"
            ];
        }
        
        return [
            'allowed' => true,
            'remaining' => $monthly - $used,
            'message' => ''
        ];
    }
    
    /**
     * 消耗发布额度
     */
    public function consumeQuota($userId, $type) {
        $month = date('Y-m');
        
        // 先查询或创建当月记录
        $stmt = $this->pdo->prepare("
            SELECT id FROM publish_stats 
            WHERE user_id = ? AND stat_month = ?
        ");
        $stmt->execute([$userId, $month]);
        $record = $stmt->fetch();
        
        if (!$record) {
            $stmt = $this->pdo->prepare("
                INSERT INTO publish_stats (user_id, stat_month) VALUES (?, ?)
            ");
            $stmt->execute([$userId, $month]);
        }
        
        // 更新对应计数
        $fieldMap = [
            'article' => 'article_count',
            'pigeon' => 'pigeon_count', 
            'listing' => 'listing_count',
            'dynamic' => 'dynamic_count'
        ];
        
        if (!isset($fieldMap[$type])) {
            return false;
        }
        
        $field = $fieldMap[$type];
        $stmt = $this->pdo->prepare("
            UPDATE publish_stats SET {$field} = {$field} + 1 
            WHERE user_id = ? AND stat_month = ?
        ");
        return $stmt->execute([$userId, $month]);
    }
    
    /**
     * 消耗置顶次数
     */
    public function consumeTop($userId) {
        $month = date('Y-m');
        
        $stmt = $this->pdo->prepare("
            UPDATE publish_stats SET top_used = top_used + 1 
            WHERE user_id = ? AND stat_month = ?
        ");
        return $stmt->execute([$userId, $month]);
    }
    
    /**
     * 获取套餐信息
     */
    private function getPlan($level) {
        if (self::$PLAN_CACHE === null) {
            $stmt = $this->pdo->query("SELECT * FROM member_plans WHERE status = 1 ORDER BY level");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                self::$PLAN_CACHE[$row['level']] = $row;
            }
        }
        
        return self::$PLAN_CACHE[$level] ?? self::$PLAN_CACHE[0];
    }
    
    /**
     * 获取用户当月发布统计
     */
    private function getStats($userId) {
        $month = date('Y-m');
        
        $stmt = $this->pdo->prepare("
            SELECT * FROM publish_stats 
            WHERE user_id = ? AND stat_month = ?
        ");
        $stmt->execute([$userId, $month]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // 今天动态数需要重置
        if ($record) {
            // 检查是否是今天的第一条动态，是的话重置dynamic_count
            $today = date('Y-m-d');
            $lastUpdate = date('Y-m-d', strtotime($record['updated_at']));
            
            if ($lastUpdate !== $today) {
                $record['dynamic_count'] = 0;
            }
            
            // 推荐次数按周重置
            $currentWeek = date('Y-W');
            if (!isset($record['recommend_week']) || $record['recommend_week'] !== $currentWeek) {
                $record['recommend_used'] = 0;
            }
        }
        
        return $record ?: [
            'article_count' => 0,
            'pigeon_count' => 0,
            'listing_count' => 0,
            'dynamic_count' => 0,
            'top_used' => 0,
            'recommend_used' => 0
        ];
    }
    
    /**
     * 检查是否可以推荐内容
     */
    public function canRecommend($userId) {
        $member = $this->getMemberInfo($userId);
        
        if (isset($member['error']) || !$member['is_valid'] || $member['level'] === 0) {
            return ['allowed' => false];
        }
        
        $plan = $member['plan'];
        $stats = $member['stats'];
        
        if (!$plan['can_recommend']) {
            return ['allowed' => false, 'message' => '银牌及以上会员可推荐内容'];
        }
        
        $weekly = $plan['recommend_weekly'];
        $used = $stats['recommend_used'] ?? 0;
        
        if ($weekly > 0 && $used >= $weekly) {
            return [
                'allowed' => false,
                'message' => "本周推荐次数已用完（{$used}/{$weekly}），可购买增值服务"
            ];
        }
        
        return [
            'allowed' => true,
            'remaining' => $weekly - $used,
            'message' => ''
        ];
    }
    
    /**
     * 消耗推荐次数（每周自动重置）
     */
    public function consumeRecommend($userId) {
        $month = date('Y-m');
        $currentWeek = date('Y-W');
        
        // 先查询或创建当月记录
        $stmt = $this->pdo->prepare("
            SELECT id FROM publish_stats 
            WHERE user_id = ? AND stat_month = ?
        ");
        $stmt->execute([$userId, $month]);
        $record = $stmt->fetch();
        
        if (!$record) {
            $stmt = $this->pdo->prepare("
                INSERT INTO publish_stats (user_id, stat_month) VALUES (?, ?)
            ");
            $stmt->execute([$userId, $month]);
        }
        
        // Step1: 重置新周计数
        $stmt = $this->pdo->prepare("
            UPDATE publish_stats 
            SET recommend_used = 0,
            recommend_week = ?
            WHERE user_id = ? AND stat_month = ?
            AND (recommend_week != ? OR recommend_week IS NULL)
        ");
        $stmt->execute([$currentWeek, $userId, $month, $currentWeek]);
        
        // Step2: 递增（同一周内）
        $stmt = $this->pdo->prepare("
            UPDATE publish_stats 
            SET recommend_used = recommend_used + 1
            WHERE user_id = ? AND stat_month = ?
            AND recommend_week = ?
        ");
        return $stmt->execute([$userId, $month, $currentWeek]);
    }
    
    /**
     * 静态方法：便捷调用
     */
    public static function check($pdo, $userId, $action, $targetType = null) {
        $guard = new self($pdo);
        
        switch ($action) {
            case 'canPublish':
                return $guard->canPublish($userId, $targetType);
            case 'canViewContact':
                return $guard->canViewContact($userId);
            case 'canTopContent':
                return $guard->canTopContent($userId);
            case 'consume':
                return $guard->consumeQuota($userId, $targetType);
            case 'consumeTop':
                return $guard->consumeTop($userId);
            case 'canRecommend':
                return $guard->canRecommend($userId);
            case 'consumeRecommend':
                return $guard->consumeRecommend($userId);
            case 'getInfo':
                return $guard->getMemberInfo($userId);
            default:
                return ['error' => 'Unknown action'];
        }
    }
}