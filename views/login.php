<?php
/**
 * 信鸽之家 - 用户登录
 */

require_once dirname(__DIR__) . '/app/config/config.php';

$error = $_GET['error'] ?? '';
$success = $_GET['success'] ?? '';

// 处理登录
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = '请输入手机号和密码';
    } else {
        $pdo = get_db_connection();
        // 先查账户是否存在（不限status）
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR phone = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        
        if (!$user) {
            $error = '账户不存在，请先注册';
        } elseif ($user['status'] != 1) {
            $error = '账户已被禁用，请联系客服';
        } elseif (!password_verify($password, $user['password'])) {
            $error = '密码错误，请重试';
        } else {
            // 登录成功
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'] ?? 'user';
            
            // 更新最后登录信息
            $stmt = $pdo->prepare("UPDATE users SET last_login_ip = ?, last_login_time = NOW() WHERE id = ?");
            $stmt->execute([$_SERVER['REMOTE_ADDR'], $user['id']]);
            
            // 跳转到首页或之前页面
            $redirect = $_SESSION['redirect'] ?? '/';
            unset($_SESSION['redirect']);
            redirect($redirect);
        }
    }
}

// 如果已登录，直接跳转
if (isset($_SESSION['user_id'])) {
    redirect('/');
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="信鸽之家用户登录入口。登录后管理您的铭鸽、发布分类信息、查看联系方式。">
    <meta name="keywords" content="登录,信鸽之家登录,用户登录">
    <meta name="robots" content="noindex, nofollow">
    <meta property="og:title" content="登录 - 信鸽之家">
    <meta property="og:description" content="信鸽之家用户登录入口。登录后管理您的铭鸽、发布分类信息、查看联系方式。">
    <meta property="og:image" content="https://www.xgjia.com/public/images/og-cover.png">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="信鸽之家">
    <link rel="canonical" href="https://www.xgjia.com/login">

    <title>用户登录 | <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/public/css/b-scheme.css">
    <style>
            :root {
                --primary: #1a5fa8;
                --primary-light: #2980b9;
                --primary-dark: #154360;
                --accent: #c9a84c;
                --accent-light: #e0c060;
                --bg: #f4f6f9;
                --white: #ffffff;
                --text: #2c3e50;
                --text-light: #6c7a89;
                --border: #e8ecf0;
                --shadow: 0 2px 12px rgba(26,95,168,0.08);
                --shadow-hover: 0 8px 30px rgba(26,95,168,0.15);
                --gold: #d4a843;
                --success: #27ae60;
                --danger: #e74c3c;
                --radius: 12px;
            }

        .login-container {
            min-height: calc(100vh - 200px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }
        
        .login-box {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            padding: 40px;
            width: 100%;
            max-width: 450px;
        }
        
        .login-title {
            text-align: center;
            font-size: 32px;
            font-weight: bold;
            color: var(--primary);
            margin-bottom: 10px;
        }
        
        .login-subtitle {
            text-align: center;
            color: var(--gray-500);
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--gray-700);
        }
        
        .form-control {
            width: 100%;
            padding: 14px 16px;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            font-size: 15px;
            background: #f8fafc;
            color: #1e293b;
            transition: all 0.2s ease;
            box-sizing: border-box;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            background: #fff;
            box-shadow: 0 0 0 3px rgba(26, 95, 168, 0.1);
        }
        
        .form-control::placeholder {
            color: #94a3b8;
        }
        
        .btn-login {
            width: 100%;
            padding: 14px;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .btn-login:hover {
            background-color: #1e40af;
        }
        
        .login-links {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            font-size: 14px;
        }
        
        .login-links a {
            color: var(--primary);
        }
        
        .login-links a:hover {
            color: var(--secondary-color);
        }
        
        .alert {
            padding: 12px 15px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
        }
        
        .alert-danger {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }
        
        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        
        @media (max-width: 768px) {
            .login-box {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/_head.php'; ?>
<div class="login-container">
        <div class="login-box">
            <h1 class="login-title">欢迎回来</h1>
            <p class="login-subtitle">登录您的账户</p>
            
            <?php if ($error): ?>
            <div class="alert alert-danger">
                <?php echo h($error); ?>
            </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo h($success); ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label" for="username">手机号</label>
                    <input type="tel" id="username" name="username" class="form-control" 
                           placeholder="请输入手机号" required autofocus autocomplete="tel">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="password">密码</label>
                    <input type="password" id="password" name="password" class="form-control" 
                           placeholder="请输入密码" required autocomplete="current-password">
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn-login">登录</button>
                </div>
            </form>
            
            <div class="login-links">
                <a href="/forgot-password">忘记密码？</a>
                <a href="/register<?php echo !empty($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : ''; ?>">没有账户？立即注册</a></td>
            </div>
        </div>
    </div>


    <?php include __DIR__ . '/_footer.php'; ?>
