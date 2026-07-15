<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Article.php';
require_once __DIR__ . '/../models/Pigeon.php';

/**
 * 用户控制器
 */
class UserController extends Controller {
    
    /**
     * 用户中心首页
     */
    public function index() {
        // 检查登录
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        $userModel = new User($this->pdo);
        $articleModel = new Article($this->pdo);
        $pigeonModel = new Pigeon($this->pdo);
        
        $userId = $_SESSION['user_id'];
        $user = $userModel->findById($userId);
        
        // 获取用户发布的文章
        $myArticles = $articleModel->getList([
            'user_id' => $userId,
            'limit' => 5
        ]);
        
        // 获取用户发布的铭鸽
        $myPigeons = $pigeonModel->getList([
            'user_id' => $userId,
            'limit' => 5
        ]);
        
        // 获取用户发布的分类信息
        require_once __DIR__ . '/../models/Listing.php';
        $listingModel = new Listing($this->pdo);
        $myListings = $listingModel->getList([
            'user_id' => $userId,
            'limit' => 5
        ]);
        
        // 检查会员状态
        $isVip = $user['member_level'] > 0 && strtotime($user['member_expire_at']) > time();
        
        $data = [
            'user' => $user,
            'myArticles' => $myArticles,
            'myPigeons' => $myPigeons,
            'myListings' => $myListings,
            'isVip' => $isVip,
            'pageTitle' => '个人中心 | ' . SITE_NAME
        ];
        
        $this->loadView('user', $data);
    }
    
    /**
     * 我的文章
     */
    public function myArticles() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        $articleModel = new Article($this->pdo);
        
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        $userId = $_SESSION['user_id'];
        
        $articles = $articleModel->getList([
            'user_id' => $userId,
            'limit' => $limit,
            'offset' => $offset
        ]);
        
        $total = $articleModel->getCount(['user_id' => $userId]);
        $totalPages = ceil($total / $limit);
        
        $data = [
            'articles' => $articles,
            'page' => $page,
            'totalPages' => $totalPages,
            'pageTitle' => '我的文章 | ' . SITE_NAME
        ];
        
        $this->loadView('my_articles', $data);
    }
    
    /**
     * 我的铭鸽
     */
    public function myPigeons() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        $pigeonModel = new Pigeon($this->pdo);
        
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = 12;
        $offset = ($page - 1) * $limit;
        
        $userId = $_SESSION['user_id'];
        
        $pigeons = $pigeonModel->getList([
            'user_id' => $userId,
            'limit' => $limit,
            'offset' => $offset
        ]);
        
        $total = $pigeonModel->getCount(['user_id' => $userId]);
        $totalPages = ceil($total / $limit);
        
        $data = [
            'pigeons' => $pigeons,
            'page' => $page,
            'totalPages' => $totalPages,
            'pageTitle' => '我的铭鸽 | ' . SITE_NAME
        ];
        
        $this->loadView('my_pigeons', $data);
    }
    
    /**
     * 编辑资料页面
     */
    public function editProfilePage() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        $userModel = new User($this->pdo);
        $user = $userModel->findById($_SESSION['user_id']);
        
        $data = [
            'user' => $user,
            'pageTitle' => '编辑资料 | ' . SITE_NAME
        ];
        
        $this->loadView('edit_profile', $data);
    }
    
    /**
     * 编辑资料（POST）
     */
    public function editProfile() {
        if (!isset($_SESSION['user_id'])) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => '请先登录']);
            exit;
            return;
        }
        
        $userModel = new User($this->pdo);
        
        $data = [
            'nickname' => $_POST['nickname'] ?? '',
            'avatar' => $_POST['avatar'] ?? '',
            'email' => $_POST['email'] ?? ''
        ];
        
        $result = $userModel->update($_SESSION['user_id'], $data);
        
        if ($result) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => true, 'message' => '修改成功']);
            exit;
        } else {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => '修改失败']);
            exit;
        }
    }
    
    /**
     * 修改密码页面
     */
    public function changePasswordPage() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        $userModel = new User($this->pdo);
        $user = $userModel->findById($_SESSION['user_id']);
        
        $data = [
            'user' => $user,
            'pageTitle' => '修改密码 | ' . SITE_NAME
        ];
        
        $this->loadView('change_password', $data);
    }
    
    /**
     * 修改密码（POST）
     */
    public function changePassword() {
        if (!isset($_SESSION['user_id'])) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => '请先登录']);
            exit;
            return;
        }
        
        $userModel = new User($this->pdo);
        $user = $userModel->findById($_SESSION['user_id']);
        
        $oldPassword = $_POST['old_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        
        if (!password_verify($oldPassword, $user['password'])) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => '原密码错误']);
            exit;
            return;
        }
        
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $result = $userModel->update($_SESSION['user_id'], ['password' => $hashedPassword]);
        
        if ($result) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => true, 'message' => '密码修改成功']);
            exit;
        } else {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => '密码修改失败']);
            exit;
        }
    }
    
    /**
     * 我的分类信息
     */
    public function myListings() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        require_once __DIR__ . '/../models/Listing.php';
        $listingModel = new Listing($this->pdo);
        
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        $userId = $_SESSION['user_id'];
        
        $listings = $listingModel->getList([
            'user_id' => $userId,
            'limit' => $limit,
            'offset' => $offset
        ]);
        
        $total = $listingModel->getCount(['user_id' => $userId]);
        $totalPages = ceil($total / $limit);
        
        $data = [
            'listings' => $listings,
            'page' => $page,
            'totalPages' => $totalPages,
            'pageTitle' => '我的发布 | ' . SITE_NAME
        ];
        
        $this->loadView('my_listings', $data);
    }
    
    /**
     * 会员中心
     */
    public function membership() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /auth?action=login');
            exit;
        }
        
        $userModel = new User($this->pdo);
        $user = $userModel->findById($_SESSION['user_id']);
        
        // 获取所有会员套餐
        $stmt = $this->pdo->query("SELECT * FROM member_plans WHERE status = 1 ORDER BY level");
        $plans = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $plans[$row['level']] = $row;
        }
        
        // 检查会员状态
        $isVip = $user['member_level'] > 0 && strtotime($user['member_expire_at']) > time();
        
        // 获取当前配额使用情况
        $stats = ['article_count' => 0, 'pigeon_count' => 0, 'listing_count' => 0];
        if ($isVip) {
            $month = date('Y-m');
            $stmt = $this->pdo->prepare("SELECT * FROM publish_stats WHERE user_id = ? AND stat_month = ?");
            $stmt->execute([$_SESSION['user_id'], $month]);
            $statsRow = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($statsRow) {
                $stats = [
                    'article_count' => $statsRow['article_count'],
                    'pigeon_count' => $statsRow['pigeon_count'],
                    'listing_count' => $statsRow['listing_count']
                ];
            }
        }
        
        $data = [
            'user' => $user,
            'isVip' => $isVip,
            'plans' => $plans,
            'stats' => $stats,
            'pageTitle' => '会员中心 | ' . SITE_NAME
        ];
        
        $this->loadView('membership', $data);
    }
    
    /**
     * 升级会员
     */
    public function upgrade() {
        if (!isset($_SESSION['user_id'])) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => '请先登录']);
            exit;
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => '无效的请求']);
            exit;
            return;
        }
        
        $level = (int)($_POST['level'] ?? 0);
        $planType = (int)($_POST['plan_type'] ?? 1); // 1=月费, 2=年费
        
        if (!in_array($level, [1, 2, 3])) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => '无效的会员等级']);
            exit;
            return;
        }
        
        if (!in_array($planType, [1, 2])) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => '无效的套餐类型']);
            exit;
            return;
        }
        
        // 获取套餐信息
        $stmt = $this->pdo->prepare("SELECT * FROM member_plans WHERE level = ? AND status = 1");
        $stmt->execute([$level]);
        $plan = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$plan) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => '套餐不存在']);
            exit;
            return;
        }
        
        // 根据套餐类型获取价格
        $amount = $planType === 1 ? $plan['monthly_price'] : $plan['yearly_price'];
        $months = $planType === 1 ? 1 : 12;
        $planName = $plan['name'];
        $planTypeName = $planType === 1 ? '月费' : '年费';
        
        // 创建订单
        $orderNo = 'VIP' . date('YmdHis') . rand(1000, 9999);
        
        $stmt = $this->pdo->prepare("
            INSERT INTO `member_orders` (`user_id`, `order_no`, `from_level`, `to_level`, `plan_type`, `months`, `amount`, `status`)
            VALUES (?, ?, ?, ?, ?, ?, ?, 0)
        ");
        
        $userModel = new User($this->pdo);
        $user = $userModel->findById($_SESSION['user_id']);
        $fromLevel = $user['member_level'] ?? 0;
        
        $stmt->execute([
            $_SESSION['user_id'],
            $orderNo,
            $fromLevel,
            $level,
            $planType,
            $months,
            $amount
        ]);
        
        // 模拟支付成功（MVP阶段）
        $orderId = $this->pdo->lastInsertId();
        $this->activateMembership($orderId);
        
        header("Content-Type: application/json; charset=utf-8");
        echo json_encode(['success' => true, 'message' => $planName . '开通成功！']);
        exit;
    }
    
    /**
     * 激活会员（模拟支付成功）
     */
    private function activateMembership($orderId) {
        // 更新订单状态
        $stmt = $this->pdo->prepare("UPDATE `member_orders` SET `status` = 1, `paid_at` = NOW() WHERE `id` = ?");
        $stmt->execute([$orderId]);
        
        // 获取订单信息
        $stmt = $this->pdo->prepare("SELECT * FROM `member_orders` WHERE `id` = ?");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$order) {
            return false;
        }
        
        $userModel = new User($this->pdo);
        $user = $userModel->findById($order['user_id']);
        
        // 计算新的到期时间
        $expireAt = ($user['member_expire_at'] && strtotime($user['member_expire_at']) > time())
            ? $user['member_expire_at']
            : date('Y-m-d H:i:s');
        
        $expireAt = date('Y-m-d H:i:s', strtotime($expireAt . ' +' . $order['months'] . ' months'));
        
        // 更新用户会员状态
        $userModel->update($order['user_id'], [
            'member_level' => $order['to_level'],
            'member_expire_at' => $expireAt
        ]);
        
        return true;
    }
}
