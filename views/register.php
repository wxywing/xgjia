<?php
/**
 * 信鸽之家 - 用户注册
 */

require_once dirname(__DIR__) . '/app/config/config.php';

$error = $_SESSION['register_error'] ?? '';
unset($_SESSION['register_error']);
$success = $_SESSION['register_success'] ?? '';
unset($_SESSION['register_success']);

// 处理注册（本页直POST）
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // 验证
    if (empty($username) || empty($password)) {
        $error = '手机号和密码不能为空';
    } elseif (!preg_match('/^1\d{10}$/', $username)) {
        $error = '请输入正确的11位手机号';
    } elseif ($password !== $confirm_password) {
        $error = '两次输入的密码不一致';
    } elseif (strlen($password) < 6) {
        $error = '密码长度至少6位';
    } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = '邮箱格式不正确';
    } else {
        $pdo = get_db_connection();
        
        // 检查手机号是否已存在
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $error = '该手机号已被注册';
        } else {
            // 检查邮箱是否已存在
            if (!empty($email)) {
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    $error = '邮箱已被注册';
                }
            }
            
            // 注册用户
            if (empty($error)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt = $pdo->prepare("INSERT INTO users (username, password, email, phone, created_at) VALUES (?, ?, ?, ?, NOW())");
                $result = $stmt->execute([$username, $hashed_password, $email, $username]);
                
                if ($result) {
                    $success = '注册成功！正在跳转到登录页...';
                    header('refresh:2;url=/login');
                } else {
                    $error = '注册失败，请稍后重试';
                }
            }
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
    <meta name="description" content="注册信鸽之家账号，免费发布铭鸽、查看公棚信息、交流养鸽经验。快速注册，立即开启您的信鸽之旅。">
    <meta name="keywords" content="注册,信鸽之家注册,用户注册">
    <meta name="robots" content="noindex, nofollow">
    <meta property="og:title" content="注册 - 信鸽之家">
    <meta property="og:description" content="注册信鸽之家账号，免费发布铭鸽、查看公棚信息、交流养鸽经验。快速注册，立即开启您的信鸽之旅。">
    <meta property="og:image" content="https://www.xgjia.com/public/images/og-cover.png">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="信鸽之家">
    <link rel="canonical" href="https://www.xgjia.com/register">

    <title>用户注册 | <?php echo SITE_NAME; ?></title>
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

        .register-container {
            min-height: calc(100vh - 200px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }
        
        .register-box {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            padding: 40px;
            width: 100%;
            max-width: 500px;
        }
        
        .register-title {
            text-align: center;
            font-size: 32px;
            font-weight: bold;
            color: var(--primary);
            margin-bottom: 10px;
        }
        
        .register-subtitle {
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
        
        .btn-register {
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
        
        .btn-register:hover {
            background-color: #1e40af;
        }
        
        .register-links {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }
        
        .register-links a {
            color: var(--primary);
        }
        
        .register-links a:hover {
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
        
        .form-hint {
            font-size: 12px;
            color: var(--gray-500);
            margin-top: 5px;
        }
        
        @media (max-width: 768px) {
            .register-box {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/_head.php'; ?>
<div class="register-container">
        <div class="register-box">
            <h1 class="register-title">创建账户</h1>
            <p class="register-subtitle">加入信鸽之家，开启您的信鸽之旅</p>
            
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
                    <label class="form-label" for="username">手机号 *</label>
                    <input type="tel" id="username" name="username" class="form-control" 
                           placeholder="请输入手机号" required autofocus
                           pattern="1[0-9]{10}"
                           autocomplete="tel"
                           value="<?php echo isset($_POST['username']) ? h($_POST['username']) : ''; ?>">
                    <div class="form-hint">手机号将作为您的登录账号</div>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="email">邮箱</label>
                    <input type="email" id="email" name="email" class="form-control" 
                           placeholder="选填，用于找回密码" autocomplete="email">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="password">密码 *</label>
                    <input type="password" id="password" name="password" class="form-control" 
                           placeholder="至少6位" required autocomplete="new-password">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="confirm_password">确认密码 *</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" 
                           placeholder="再次输入密码" required autocomplete="new-password">
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn-register">注册</button>
                </div>
            </form>
            
            <div class="register-links">
                已有账户？<a href="/login">立即登录</a>
            </div>
        </div>
    </div>


    <?php include __DIR__ . '/_footer.php'; ?>
