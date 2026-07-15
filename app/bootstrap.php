<?php
/**
 * 应用引导文件
 * 初始化框架核心组件
 */

// 启动会话
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 缓存开关（开发时改 false 即可关闭所有缓存）
define('CACHE_ENABLED', false);

// 加载配置
require_once __DIR__ . '/config/config.php';

// 加载核心类
require_once __DIR__ . '/core/DeviceDetector.php';
require_once __DIR__ . '/core/Controller.php';
require_once __DIR__ . '/core/Model.php';

// 加载模型类
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/models/Article.php';
require_once __DIR__ . '/models/Pigeon.php';
require_once __DIR__ . '/models/Category.php';
require_once __DIR__ . '/models/Listing.php';
require_once __DIR__ . '/models/Race.php';
require_once __DIR__ . '/models/Dynamic.php';
require_once __DIR__ . '/models/Loft.php';
require_once __DIR__ . '/models/Shop.php';
require_once __DIR__ . '/models/ClaimRequest.php';

/**
 * 获取数据库连接
 */
function get_pdo() {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = get_db_connection();
    }
    return $pdo;
}

/**
 * 创建控制器实例
 */
function controller($name) {
    $controllerFile = __DIR__ . '/controllers/' . $name . '.php';
    if (!file_exists($controllerFile)) {
        die("控制器不存在: {$name}");
    }
    require_once $controllerFile;
    
    $pdo = get_pdo();
    return new $name($pdo);
}
