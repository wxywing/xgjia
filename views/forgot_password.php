<?php
/**
 * 信鸽之家 - 忘记密码视图
 * 
 * @var string $error 错误信息
 * @var string $success 成功信息
 */
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="信鸽之家密码找回，通过注册手机号重置密码。">
    <meta name="keywords" content="忘记密码,重置密码,信鸽之家">
    <meta name="robots" content="noindex, nofollow">
    <meta property="og:title" content="忘记密码 - 信鸽之家">
    <meta property="og:description" content="信鸽之家密码找回，通过注册手机号重置密码。">
    <meta property="og:image" content="https://www.xgjia.com/public/images/og-cover.png">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="信鸽之家">
    <link rel="canonical" href="https://www.xgjia.com/forgot-password">

    <title>忘记密码 | <?php echo SITE_NAME; ?></title>
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
        
        .alert-info {
            background-color: #dbeafe;
            color: #1e40af;
            border: 1px solid #93c5fd;
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
            <h1 class="login-title">忘记密码</h1>
            <p class="login-subtitle">输入手机号重置密码</p>
            
            <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <?php echo h($error); ?>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <?php echo $success; ?>
            </div>
            <?php else: ?>
            <div class="alert alert-info">
                请输入注册时使用的手机号，我们将为您生成密码重置链接。
            </div>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label" for="phone">手机号</label>
                    <input type="tel" id="phone" name="phone" class="form-control" 
                           placeholder="请输入手机号" 
                           pattern="1[0-9]{10}"
                           required autofocus autocomplete="tel">
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn-login">获取重置链接</button>
                </div>
            </form>
            <?php endif; ?>
            
            <div class="login-links">
                <a href="/auth?action=login">返回登录</a>
                <a href="/register">没有账户？立即注册</a>
            </div>
        </div>
    </div>
    
    <?php include __DIR__ . '/_footer.php'; ?>
