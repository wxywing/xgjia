<?php
require_once __DIR__ . '/../models/User.php';

/**
 * 认证控制器
 */
class AuthController extends Controller {
    
    /**
     * 登录页面
     */
    public function loginForm() {
        // 保存登录后回跳地址（必须在已登录检查之前，否则丢失redirect）
        if (!empty($_GET['redirect'])) {
            $_SESSION['login_redirect'] = $_GET['redirect'];
        }
        
        // 已登录则跳转到个人中心
        if (isset($_SESSION['user_id'])) {
            $redirect = $_SESSION['login_redirect'] ?? '/user';
            unset($_SESSION['login_redirect']);
            header('Location: ' . $redirect);
            exit;
        }
        
        $data = [
            'pageTitle' => '登录 | ' . SITE_NAME,
            'error' => $_GET['error'] ?? '',
            'success' => $_GET['success'] ?? ''
        ];
        
        $this->loadView('login', $data);
    }
    
    /**
     * 登录处理
     */
    public function login() {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            header('Location: /login?error=' . urlencode('请输入手机号和密码'));
            exit;
        }
        
        $userModel = new User($this->pdo);
        $user = $userModel->findByUsername($username); //findByUsername
        
        if (!$user) {
            header('Location: /login?error=' . urlencode('账户不存在，请先注册'));
            exit;
        }
        
        if (!password_verify($password, $user['password'])) {
            header('Location: /login?error=' . urlencode('密码错误，请重试'));
            exit;
        }
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['nickname'] = $user['nickname'];
        $_SESSION['avatar'] = $user['avatar'];
        
        $redirect = $_SESSION['login_redirect'] ?? '/user';
        unset($_SESSION['login_redirect']);
        header('Location: ' . $redirect);
        exit;
    }
    
    /**
     * 注册页面
     */
    public function registerForm() {
        // 保存注册后回跳地址
        if (!empty($_GET['redirect'])) {
            $_SESSION['login_redirect'] = $_GET['redirect'];
        }
        
        // 已登录则跳转到个人中心
        if (isset($_SESSION['user_id'])) {
            $redirect = $_SESSION['login_redirect'] ?? '/user';
            unset($_SESSION['login_redirect']);
            header('Location: ' . $redirect);
            exit;
        }
        
        $data = [
            'pageTitle' => '注册 | ' . SITE_NAME
        ];
        
        $this->loadView('register', $data);
    }
    
    /**
     * 注册处理
     */
    public function register() {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $email = trim($_POST['email'] ?? '');
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        
        $respond = function($success, $message, $redirect = null) use ($isAjax) {
            if ($isAjax) {
                header("Content-Type: application/json; charset=utf-8");
                $resp = ['success' => $success, 'message' => $message];
                if ($redirect) $resp['redirect'] = $redirect;
                echo json_encode($resp);
                exit;
            }
            if ($success) {
                header('Location: ' . ($redirect ?: '/user'));
                exit;
            }
            $_SESSION['register_error'] = $message;
            header('Location: /register');
            exit;
        };
        
        // 验证输入（手机号 + 密码必填，邮箱选填）
        if (empty($username) || empty($password)) {
            $respond(false, '手机号和密码不能为空');
        }
        
        if (!preg_match('/^1\d{10}$/', $username)) {
            $respond(false, '请输入正确的11位手机号');
        }
        
        if ($password !== $confirmPassword) {
            $respond(false, '两次密码输入不一致');
        }
        
        if (strlen($password) < 6) {
            $respond(false, '密码长度至少6位');
        }
        
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $respond(false, '邮箱格式不正确');
        }
        
        $userModel = new User($this->pdo);
        
        // 检查手机号是否已存在
        if ($userModel->findByUsername($username)) {
            $respond(false, '该手机号已被注册');
        }
        
        // 检查邮箱是否已存在（仅非空时检查）
        if (!empty($email) && $userModel->findByEmail($email)) {
            $respond(false, '邮箱已被注册');
        }
        
        // 创建用户
        $data = [
            'username' => $username,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'email' => $email ?: '',
            'phone' => $username,
            'nickname' => $username
        ];
        
        if ($userModel->create($data)) {
            // 自动登录
            $newUser = $userModel->findByUsername($username);
            if ($newUser) {
                $_SESSION['user_id'] = $newUser['id'];
                $_SESSION['username'] = $newUser['username'];
            }
            $respond(true, '注册成功', $_SESSION['login_redirect'] ?? '/user');
            unset($_SESSION['login_redirect']);
        } else {
            $respond(false, '注册失败，请稍后重试');
        }
    }
    
    /**
     * 退出登录
     */
    public function logout() {
        session_destroy();
        header('Location: /');
        exit;
    }
    
    /**
     * 检查用户名是否可用
     */
    public function checkUsername() {
        $username = $_GET['username'] ?? '';
        
        if (empty($username)) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['available' => false]);
            exit;
            return;
        }
        
        $userModel = new User($this->pdo);
        $user = $userModel->findByUsername($username);
        
        header("Content-Type: application/json; charset=utf-8");
        echo json_encode(['available' => !$user]);
        exit;
    }
    
    /**
     * 检查邮箱是否可用
     */
    public function checkEmail() {
        $email = $_GET['email'] ?? '';
        
        if (empty($email)) {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(['available' => false]);
            exit;
            return;
        }
        
        $userModel = new User($this->pdo);
        $user = $userModel->findByEmail($email);
        
        header("Content-Type: application/json; charset=utf-8");
        echo json_encode(['available' => !$user]);
        exit;
    }
}
