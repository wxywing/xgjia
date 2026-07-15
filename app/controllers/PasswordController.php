<?php
/**
 * 信鸽之家 - 密码重置控制器
 */

require_once dirname(__DIR__) . '/config/config.php';

define('VIEW_PATH', dirname(dirname(__DIR__)) . '/views');

class PasswordController
{
    /**
     * 显示忘记密码页面
     */
    public function forgot()
    {
        $error = '';
        $success = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $phone = trim($_POST['phone'] ?? '');
            
            if (empty($phone)) {
                $error = '请输入手机号';
            } elseif (!preg_match('/^1\d{10}$/', $phone)) {
                $error = '请输入正确的11位手机号';
            } else {
                $pdo = get_db_connection();
                $stmt = $pdo->prepare("SELECT * FROM users WHERE (phone = ? OR username = ?) AND status = 1");
                $stmt->execute([$phone, $phone]);
                $user = $stmt->fetch();
                
                if (!$user) {
                    $error = '该手机号未注册';
                } else {
                    // 生成重置令牌（1小时有效）
                    $token = bin2hex(random_bytes(32));
                    $expires = time() + 3600;
                    
                    $_SESSION['reset_token'] = $token;
                    $_SESSION['reset_user_id'] = $user['id'];
                    $_SESSION['reset_expires'] = $expires;
                    
                    // MVP 阶段：直接显示重置链接（实际应发短信）
                    $resetUrl = "/reset-password?token={$token}&phone=" . urlencode($phone);
                    $success = "重置链接已生成（测试环境）：<a href='{$resetUrl}'>点击重置密码</a>";
                }
            }
        }
        
        require_once VIEW_PATH . '/forgot_password.php';
    }
    
    /**
     * 显示重置密码页面
     */
    public function reset()
    {
        $error = '';
        $success = '';
        
        $token = $_GET['token'] ?? '';
        $phone = $_GET['phone'] ?? '';
        
        if (empty($token) || empty($phone)) {
            redirect('/forgot-password');
        }
        
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE (phone = ? OR username = ?) AND status = 1");
        $stmt->execute([$phone, $phone]);
        $user = $stmt->fetch();
        
        if (!$user) {
            $error = '无效的重置链接';
        } else {
            // 验证令牌（仅 session，无需 remember_token 列）
            $valid = isset($_SESSION['reset_token']) &&
                     $_SESSION['reset_token'] === $token &&
                     $_SESSION['reset_user_id'] === $user['id'] &&
                     $_SESSION['reset_expires'] > time();
            
            if (!$valid) {
                $error = '重置链接已过期，请重新申请';
            }
        }
        
        // 处理密码重置
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error)) {
            $password = $_POST['password'] ?? '';
            $confirm = $_POST['confirm'] ?? '';
            
            if (strlen($password) < 6) {
                $error = '密码至少需要6位';
            } elseif ($password !== $confirm) {
                $error = '两次输入的密码不一致';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hash, $user['id']]);
                
                unset($_SESSION['reset_token'], $_SESSION['reset_user_id'], $_SESSION['reset_expires']);
                
                $success = '密码重置成功，<a href="/auth?action=login">点击登录</a>';
            }
        }
        
        require_once VIEW_PATH . '/reset_password.php';
    }
}
