<?php
// ============================================================
// 信鸽之家 配置文件模板
// 使用方法：复制此文件为 config.php，修改下方配置项
// ============================================================

<?php
/**
 * 信鸽之家 - 配置文件
 * 
 * 数据库配置、常量定义、基础设置
 */

// =============================================
// 数据库配置
// =============================================
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'xgjia');
define('DB_USER', 'root');
define('DB_PASS', 'YOUR_DB_PASSWORD');
define('DB_CHARSET', 'utf8mb4');

// =============================================
// 数据库连接（尽早建立，用于读取配置）
// =============================================
function get_db_connection() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            die('数据库连接失败: ' . $e->getMessage());
        }
    }
    return $pdo;
}

// =============================================
// 从数据库读取站点配置
// =============================================
$__db_pdo = get_db_connection();
$__site_name = '信鸽之家';
try {
    $__stmt = $__db_pdo->prepare("SELECT `value` FROM `settings` WHERE `key` = 'site_name' LIMIT 1");
    $__stmt->execute();
    $__row = $__stmt->fetch();
    if ($__row && !empty(trim($__row['value']))) {
        $__site_name = trim($__row['value']);
    }
} catch (PDOException $e) {
    // 数据库表不存在或查询失败时使用默认值
}
unset($__db_pdo, $__stmt, $__row);

// =============================================
// 网站基础配置
// =============================================
define('SITE_NAME', $__site_name);
unset($__site_name);
define('SITE_URL', 'https://www.xgjia.com');
define('SITE_SLOGAN', '鸽友信赖的信鸽信息平台');
define('SITE_DESCRIPTION', '信鸽之家是专业的信鸽信息服务平台，提供公棚查询、铭鸽展厅、血统查询、赛事资讯、鸽友交流等全方位服务。找公棚、看铭鸽、查血统、看赛事，信鸽之家是您的一站式信鸽信息中心。');
define('SITE_KEYWORDS', '信鸽,赛鸽,公棚,铭鸽,血统,鸽舍,鸽友,赛事,配对,足环号查询,血统证书,公棚查询,信鸽交易,赛鸽资讯');

// 分站点关键词（用于不同页面TDK优化）
define('KEYWORDS_PIGEONS', '铭鸽,赛鸽,血统,足环号,配对记录,血统证书');
define('KEYWORDS_LOFTS', '公棚,公棚查询,公棚大全,秋棚,春棚,公棚比赛');
define('KEYWORDS_SHOPS', '鸽舍,鸽舍展厅,铭鸽展厅,优秀鸽舍');
define('KEYWORDS_LISTINGS', '信鸽交易,赛鸽出售,鸽友交易,二手鸽具');
define('KEYWORDS_ARTICLES', '赛鸽资讯,信鸽新闻,赛鸽数据分析,养鸽知识,足环查询');
define('KEYWORDS_RACES', '信鸽赛事,赛鸽比赛,公棚决赛,赛事直播');

// =============================================
// ICP备案号（上线后修改）
// =============================================
define('SITE_ICP', '京ICP备2025118452号'); // 例如: '京ICP备12345678号'

// =============================================
// 百度统计ID（上线后修改）
// =============================================
define('BAIDU_TONGJI_ID', '7786194e83b604bf8604de6a36750b33');

// =============================================
// 路径配置
// =============================================
define('ROOT_PATH', dirname(__DIR__));
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('UPLOAD_PATH', ROOT_PATH . '/uploads');
define('UPLOAD_URL', SITE_URL . '/uploads');

// =============================================
// 上传配置
// =============================================
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx']);

// =============================================
// 分页配置
// =============================================
define('PAGE_SIZE', 20);
define('MAX_PAGE_SIZE', 100);

// =============================================
// 用户角色
// =============================================
define('ROLE_USER', 1);
define('ROLE_EDITOR', 2);
define('ROLE_ADMIN', 3);

// =============================================
// 内容状态
// =============================================
define('STATUS_DRAFT', 0);
define('STATUS_PUBLISHED', 1);
define('STATUS_OFFLINE', 2);

// =============================================
// 订单状态
// =============================================
define('ORDER_PENDING', 1);    // 待付款
define('ORDER_PAID', 2);       // 待发货
define('ORDER_SHIPPED', 3);    // 待收货
define('ORDER_COMPLETED', 4);  // 已完成
define('ORDER_CANCELLED', 5);  // 已取消
define('ORDER_REFUNDED', 6);   // 退款中

// =============================================
// 时区设置
// =============================================
date_default_timezone_set('Asia/Shanghai');

// =============================================
// 错误报告（开发环境开启，生产环境关闭）
// =============================================
error_reporting(E_ALL);
ini_set('display_errors', 1);

// =============================================
// Session 配置
// =============================================
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// =============================================
// 辅助函数：自动加载类
// =============================================
spl_autoload_register(function ($className) {
    $paths = [
        ROOT_PATH . '/app/controllers/',
        ROOT_PATH . '/app/models/',
        ROOT_PATH . '/app/core/',
    ];
    
    foreach ($paths as $path) {
        $file = $path . $className . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// =============================================
// 辅助函数：安全输出
// =============================================
function h($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// =============================================
// 辅助函数：重定向
// =============================================
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

// =============================================
// 辅助函数：时间显示
// =============================================
function time_ago($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    if ($diff < 60) return '刚刚';
    if ($diff < 3600) return floor($diff / 60) . '分钟前';
    if ($diff < 86400) return floor($diff / 3600) . '小时前';
    if ($diff < 604800) return floor($diff / 86400) . '天前';
    return date('m-d', $time);
}

// =============================================
// 辅助函数：JSON响应
// =============================================
function json_response($data, $code = 200, $message = 'success') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'code' => $code,
        'message' => $message,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// =============================================
// 辅助函数：生成分页
// =============================================
function paginate($total, $page = 1, $pageSize = PAGE_SIZE) {
    $totalPages = ceil($total / $pageSize);
    $page = max(1, min($page, $totalPages));
    
    return [
        'total' => $total,
        'page' => $page,
        'page_size' => $pageSize,
        'total_pages' => $totalPages,
        'offset' => ($page - 1) * $pageSize,
        'has_prev' => $page > 1,
        'has_next' => $page < $totalPages,
    ];
}

// =============================================
// 辅助函数：渲染分页 HTML（统一封装）
// 用法：echo renderPagination($page ?? 1, $totalPages ?? 1);
// 自动继承当前 URL 的 $_GET 参数，只替换 page 值
// =============================================
function renderPagination($page, $totalPages) {
    $page = intval($page);
    $totalPages = intval($totalPages);
    if ($totalPages <= 1) return '';
    
    $pageUrl = function($p) {
        $params = $_GET;
        $params['page'] = $p;
        return '?' . http_build_query($params);
    };
    
    $html = '<div class="pagination">' . "\n";
    
    // 上一页
    if ($page > 1) {
        $html .= '    <a href="' . h($pageUrl($page - 1)) . '" class="page-link" rel="prev"><i class="fas fa-chevron-left"></i></a>' . "\n";
    } else {
        $html .= '    <span class="page-link disabled"><i class="fas fa-chevron-left"></i></span>' . "\n";
    }
    
    // 页码（当前页 ± 2，共 5 个）
    $start = max(1, $page - 2);
    $end = min($totalPages, $page + 2);
    
    // 首页（当窗口不包含第 1 页时）
    if ($start > 1) {
        $html .= '    <a href="' . h($pageUrl(1)) . '" class="page-link">1</a>' . "\n";
        if ($start > 2) $html .= '    <span class="page-link disabled">...</span>' . "\n";
    }
    
    for ($i = $start; $i <= $end; $i++) {
        $active = ($i === $page) ? ' active' : '';
        $html .= '    <a href="' . h($pageUrl($i)) . '" class="page-link' . $active . '">' . $i . '</a>' . "\n";
    }
    
    // 末页（当窗口不包含最后页时）
    if ($end < $totalPages) {
        if ($end < $totalPages - 1) $html .= '    <span class="page-link disabled">...</span>' . "\n";
        $html .= '    <a href="' . h($pageUrl($totalPages)) . '" class="page-link">' . $totalPages . '</a>' . "\n";
    }
    
    // 下一页
    if ($page < $totalPages) {
        $html .= '    <a href="' . h($pageUrl($page + 1)) . '" class="page-link" rel="next"><i class="fas fa-chevron-right"></i></a>' . "\n";
    } else {
        $html .= '    <span class="page-link disabled"><i class="fas fa-chevron-right"></i></span>' . "\n";
    }
    
    $html .= '</div>';
    return $html;
}

// =============================================
// CSRF 防护
// =============================================
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// =============================================
// 辅助函数：时间友好显示
// =============================================
function timeAgo($datetime) {
    $time = is_numeric($datetime) ? (int)$datetime : strtotime($datetime);
    if ($time === false) return '未知时间';
    $diff = time() - $time;
    if ($diff < 60) return '刚刚';
    if ($diff < 3600) return floor($diff / 60) . ' 分钟前';
    if ($diff < 86400) return floor($diff / 3600) . ' 小时前';
    if ($diff < 2592000) return floor($diff / 86400) . ' 天前';
    if ($diff < 31104000) return floor($diff / 2592000) . ' 个月前';
    return date('Y-m-d', $time);
}

// ========== 支付配置 ==========
// ⚠️ 正式上线前替换为真实密钥

define('WECHAT_PAY_APPKEY', 'wx_placeholder_appkey_xxxxxxxx');
define('WECHAT_PAY_SECRET', 'wx_placeholder_secret_xxxxxxxxxxxxxxxx');
define('WECHAT_PAY_MCHID', 'YOUR_WECHAT_MCHID');           // 商户号
define('ALIPAY_APPKEY', 'alipay_placeholder_appkey_xxxxxxxx');
define('ALIPAY_SECRET', 'alipay_placeholder_secret_xxxxxxxxxxxxxxxx');
define('ALIPAY_PID', '2088xxxxxxxxxxxx');           // 合作者身份ID

// 支付模式: sandbox=模拟支付 real=真实支付
define('PAY_MODE', 'sandbox');



