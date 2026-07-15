<?php
/**
 * 控制器基类
 * 所有控制器都应继承此类
 */
abstract class Controller {
    protected $pdo;
    protected $deviceDetector;
    protected $cache;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->deviceDetector = new DeviceDetector();
    }
    
    /**
     * 获取缓存实例（懒加载）
     * 受 CACHE_ENABLED 常量控制
     */
    protected function cache() {
        if ($this->cache === null) {
            if (!defined('CACHE_ENABLED') || !CACHE_ENABLED) {
                // 缓存关闭时返回空实现，get() 永远返回 null，set() 无操作
                $this->cache = new class {
                    public function get($key) { return null; }
                    public function set($key, $value, $ttl = null) { return true; }
                    public function delete($key) {}
                    public function clearPrefix($prefix) {}
                };
            } else {
                require_once __DIR__ . '/Cache.php';
                $this->cache = new \Cache(__DIR__ . '/../../../cache', 3600);
            }
        }
        return $this->cache;
    }
    
    /**
     * 加载视图
     * 
     * @param string $viewName 视图名称（如 'index', 'article'）
     * @param array $data 要传递给视图的数据
     */
    protected function loadView($viewName, $data = []) {
        $this->deviceDetector->loadView($viewName, $data);
    }
    
    /**
     * 渲染视图（不通过设备检测）
     * 
     * @param string $viewPath 视图路径（相对于 views/ 目录）
     * @param array $data 要传递给视图的数据
     */
    protected function render($viewPath, $data = []) {
        extract($data);
        $viewFile = __DIR__ . '/../../views/' . $viewPath . '.php';
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            die('View file not found: ' . $viewFile);
        }
    }
    
    /**
     * 重定向
     */
    protected function redirect($url) {
        header('Location: ' . $url);
        exit;
    }
    
    /**
     * 检查用户是否登录
     */
    protected function requireLogin() {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
        }
    }
}
