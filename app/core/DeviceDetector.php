<?php
/**
 * 设备检测助手类
 * 用于判断请求来自 PC 端还是移动端
 */

class DeviceDetector {
    
    /**
     * 判断是否为移动设备
     * 
     * @return bool
     */
    public static function isMobile() {
        // 检查 User-Agent
        $userAgent = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');
        
        $mobileKeywords = [
            'mobile', 'android', 'iphone', 'ipad', 'phone',
            'ipod', 'blackberry', 'opera mini', 'windows phone',
            'symbian', ' Palm', 'handheld', 'webos', 'kindle'
        ];
        
        foreach ($mobileKeywords as $keyword) {
            if (strpos($userAgent, $keyword) !== false) {
                return true;
            }
        }
        
        // 检查 HTTP headers（部分代理会添加这些 headers）
        if (isset($_SERVER['HTTP_X_WAP_PROFILE']) || isset($_SERVER['HTTP_PROFILE'])) {
            return true;
        }
        
        // 检查 Accept header
        if (isset($_SERVER['HTTP_ACCEPT'])) {
            $accept = strtolower($_SERVER['HTTP_ACCEPT']);
            if (strpos($accept, 'application/vnd.wap.xhtml+xml') !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * 获取当前设备类型
     * 
     * @return string 'pc' 或 'mobile'
     */
    public static function getDeviceType() {
        return self::isMobile() ? 'mobile' : 'pc';
    }
    
    /**
     * 获取视图路径
     * 
     * @param string $viewName 视图文件名（不含扩展名）
     * @return string 视图文件的完整路径
     */
    public static function getViewPath($viewName) {
        // 统一从 views/ 根目录加载（响应式设计，不再区分 pc/mobile）
        $viewPath = __DIR__ . "/../../views/{$viewName}.php";
        return $viewPath;
    }
    
    /**
     * 加载视图文件
     * 
     * @param string $viewName 视图文件名（不含扩展名）
     * @param array $data 要传递给视图的数据
     * @return void
     */
    public static function loadView($viewName, $data = []) {
        $viewPath = self::getViewPath($viewName);
        
        if (!file_exists($viewPath)) {
            die("视图文件不存在: {$viewName}");
        }
        
        // 将数组键名转换为变量
        extract($data);
        
        // 加载视图文件
        require_once $viewPath;
    }
}
