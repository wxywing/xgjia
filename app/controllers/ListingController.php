<?php
require_once __DIR__ . '/../models/Listing.php';
require_once __DIR__ . '/../models/Category.php';

/**
 * 分类信息控制器
 */
class ListingController extends Controller {
    
    /**
     * 分类信息列表
     */
    public function list() {
        $listingModel = new Listing($this->pdo);
        
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $type = isset($_GET['type']) ? (int)$_GET['type'] : null;
        $location = $_GET['location'] ?? null;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        $options = [
            'limit' => $limit,
            'offset' => $offset
        ];
        
        if ($type) {
            $options['type'] = $type;
        }
        
        if ($location) {
            $options['location'] = $location;
        }
        
        $listings = $listingModel->getList($options);
        $total = $listingModel->getCount($options);
        $totalPages = ceil($total / $limit);
        
        // 获取热门信息
        $hotListings = $listingModel->getHot(5);
        
        $data = [
            'listings' => $listings,
            'hotListings' => $hotListings,
            'currentType' => $type,
            'currentLocation' => $location,
            'page' => $page,
            'totalPages' => $totalPages,
            'pageTitle' => '分类信息 | ' . SITE_NAME
        ];
        
        $this->loadView('listings', $data);
    }
    
    /**
     * 分类信息详情
     */
    public function detail($id) {
        $listingModel = new Listing($this->pdo);
        
        $listing = $listingModel->findById($id);
        
        if (!$listing) {
            http_response_code(404);
            $this->loadView('404', ['pageTitle' => '页面未找到']);
            return;
        }
        
        // 增加浏览次数
        $listingModel->incrementViews($id);
        
        // 获取相关推荐
        $relatedListings = $listingModel->getList([
            'type' => $listing['type'],
            'limit' => 5
        ]);
        
        // 联系方式查看权限
        $canViewContact = false;
        if (isset($_SESSION['user_id'])) {
            require_once __DIR__ . '/../core/MembershipGuard.php';
            $contactCheck = MembershipGuard::check($this->pdo, $_SESSION['user_id'], 'canViewContact');
            $canViewContact = $contactCheck['allowed'];
        }
        
        $data = [
            'listing' => $listing,
            'relatedListings' => $relatedListings,
            'canViewContact' => $canViewContact,
            'pageTitle' => $listing['title'] . ' | ' . SITE_NAME
        ];
        
        $this->loadView('listing', $data);
    }
    
    /**
     * 发布分类信息页面
     */
    public function create() {
        // 检查登录
        if (!isset($_SESSION['user_id'])) {
            header('Location: /auth?action=login');
            exit;
        }
        
        // 会员权限检查
        require_once __DIR__ . '/../core/MembershipGuard.php';
        $check = MembershipGuard::check($this->pdo, $_SESSION['user_id'], 'canPublish', 'listing');
        
        $type = isset($_GET['type']) ? (int)$_GET['type'] : 1;
        
        $data = [
            'type' => $type,
            'publish_check' => $check,
            'pageTitle' => '发布信息 | ' . SITE_NAME
        ];
        
        $this->loadView('listing_create', $data);
    }
    
    /**
     * 保存分类信息
     */
    public function store() {
        if (!isset($_SESSION['user_id'])) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => '请先登录', 'code' => 'not_logged_in']);
            exit;
            return;
        }
        
        // 会员权限检查
        require_once __DIR__ . '/../core/MembershipGuard.php';
        $check = MembershipGuard::check($this->pdo, $_SESSION['user_id'], 'canPublish', 'listing');
        
        if (!$check['allowed']) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => $check['message'], 'code' => $check['code']]);
            exit;
            return;
        }
        
        $listingModel = new Listing($this->pdo);
        
        $data = [
            'user_id' => $_SESSION['user_id'],
            'type' => $_POST['type'],
            'title' => $_POST['title'],
            'description' => $_POST['description'] ?? '',
            'images' => $_POST['images'] ?? '',
            'contact_name' => $_POST['contact_name'] ?? $_SESSION['nickname'],
            'contact_phone' => $_POST['contact_phone'] ?? '',
            'contact_wechat' => $_POST['contact_wechat'] ?? '',
            'price' => $_POST['price'] ?? null,
            'negotiable' => $_POST['negotiable'] ?? 0,
            'location' => $_POST['location'] ?? ''
        ];
        
        $result = $listingModel->create($data);
        
        if ($result) {
            // 消耗配额
            MembershipGuard::check($this->pdo, $_SESSION['user_id'], 'consume', 'listing');
            
            $message = $this->getAuditSetting() ? '发布成功，等待审核' : '发布成功';
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => true, 'message' => $message]);
            exit;
        } else {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['success' => false, 'message' => '发布失败']);
            exit;
        }
    }
    
    /**
     * 检查是否VIP会员
     */
    private function isVipMember($userId) {
        $stmt = $this->pdo->prepare("SELECT member_level, member_expire_at FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user || $user['member_level'] != 1) {
            return false;
        }
        
        if ($user['member_expire_at'] && strtotime($user['member_expire_at']) < time()) {
            return false;
        }
        
        return true;
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
}
