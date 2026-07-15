<?php
/**
 * 信鸽之家 - 修改密码
 */

require_once dirname(__DIR__) . '/app/config/config.php';

// $data 由 Controller::loadView() 提取
extract($data);

$page_title = $pageTitle ?? '修改密码 | ' . SITE_NAME;
$noindex = true;
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="修改信鸽之家账号密码，保障账号安全。">
    <meta name="keywords" content="修改密码,信鸽之家">
    <meta property="og:title" content="修改密码 - 信鸽之家">
    <meta property="og:description" content="修改信鸽之家账号密码，保障账号安全。">
    <meta property="og:image" content="https://www.xgjia.com/public/images/og-cover.png">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="信鸽之家">
    <link rel="canonical" href="https://www.xgjia.com/user/change-password">

    <title><?php echo h($page_title); ?></title>
    <meta name="robots" content="noindex, nofollow">
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

        .page-header {
            background: linear-gradient(135deg, var(--primary) 0%, #1e40af 100%);
            color: white;
            padding: 40px 0;
            margin-bottom: 40px;
            text-align: center;
        }
        
        .page-header h1 {
            font-size: 36px;
            margin-bottom: 10px;
        }
        
        .page-header p {
            font-size: 18px;
            opacity: 0.9;
        }
        
        .content-wrapper {
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 30px;
            margin-bottom: 60px;
        }
        
        @media (max-width: 768px) {
            .content-wrapper {
                grid-template-columns: 1fr;
            }
        }
        
        .user-sidebar {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 0;
            height: fit-content;
            position: sticky;
            top: 90px;
            overflow: hidden;
        }
        
        @media (max-width: 768px) {
            .user-sidebar {
                position: static;
            }
        }
        
        .sidebar-menu {
            list-style: none;
        }
        
        .sidebar-menu li {
            border-bottom: 1px solid var(--gray-100);
        }
        
        .sidebar-menu li:last-child {
            border-bottom: none;
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 15px 20px;
            color: var(--gray-700);
            transition: all 0.3s;
        }
        
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background-color: var(--primary);
            color: white;
        }
        
        .sidebar-menu i {
            width: 20px;
            text-align: center;
        }
        
        .main-content {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 40px;
        }
        
        .form-section {
            max-width: 500px;
        }
        
        .form-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--gray-100);
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--gray-700);
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid var(--gray-200);
            border-radius: var(--border-radius);
            font-size: 16px;
            transition: all 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .password-wrapper {
            position: relative;
        }
        
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--gray-400);
            transition: color 0.3s;
        }
        
        .password-toggle:hover {
            color: var(--gray-600);
        }
        
        .password-strength {
            margin-top: 8px;
            height: 4px;
            background: var(--gray-200);
            border-radius: 2px;
            overflow: hidden;
        }
        
        .password-strength-bar {
            height: 100%;
            width: 0;
            transition: all 0.3s;
        }
        
        .password-hint {
            font-size: 14px;
            color: var(--gray-500);
            margin-top: 8px;
        }
        
        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        
        .btn-save {
            background: var(--primary);
            color: white;
            padding: 12px 30px;
            border-radius: var(--border-radius);
            font-weight: 600;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }
        
        .btn-save:hover {
            background: #1e40af;
            transform: translateY(-2px);
        }
        
        .btn-save:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .btn-cancel {
            background: var(--gray-200);
            color: var(--gray-700);
            padding: 12px 30px;
            border-radius: var(--border-radius);
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-cancel:hover {
            background: var(--gray-300);
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            display: none;
        }
        
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #6ee7b7;
        }
        
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }
        
        .security-tips {
            background: var(--gray-50);
            border-radius: var(--border-radius);
            padding: 20px;
            margin-top: 30px;
        }
        
        .security-tips h3 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--gray-700);
        }
        
        .security-tips ul {
            list-style: none;
            padding: 0;
        }
        
        .security-tips li {
            padding: 8px 0;
            color: var(--gray-600);
            font-size: 14px;
        }
        
        .security-tips li i {
            color: var(--primary);
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/_head.php'; ?>
<!-- 页面头部 -->
    <div class="page-header">
        <div class="container">
            <h1><i class="fas fa-key"></i> 修改密码</h1>
            <p>定期修改密码可以保护您的账户安全</p>
        </div>
    </div>

    <!-- 主内容区 -->
    <div class="container">
        <div class="content-wrapper">
            <!-- 侧边栏 -->
            <aside class="user-sidebar">
            <ul class="sidebar-menu">
                <li>
                    <a href="/user">
                        <i class="fas fa-home"></i>
                        <span>仪表盘</span>
                    </a>
                </li>
                <li>
                    <a href="/user/my_articles">
                        <i class="fas fa-newspaper"></i>
                        <span>我的文章</span>
                    </a>
                </li>
                <li>
                    <a href="/user/my_pigeons">
                        <i class="fas fa-dove"></i>
                        <span>我的铭鸽</span>
                    </a>
                </li>
                <li>
                    <a href="/user/my_listings">
                        <i class="fas fa-list"></i>
                        <span>我的发布</span>
                    </a>
                </li>
                <li>
                    <a href="/pedigree/?action=pairings">
                        <i class="fas fa-heart"></i>
                        <span>我的配对</span>
                    </a>
                </li>
                <li>
                    <a href="/pay/?action=orders">
                        <i class="fas fa-receipt"></i>
                        <span>我的订单</span>
                    </a>
                </li>
                <li>
                    <a href="/user/membership">
                        <i class="fas fa-crown"></i>
                        <span>会员中心</span>
                    </a>
                </li>
                <li>
                    <a href="/claim?action=my_claims">
                        <i class="fas fa-hand-holding-heart"></i>
                        <span>我的认领</span>
                    </a>
                </li>
                <li>
                    <a href="/user/edit_profile">
                        <i class="fas fa-user-edit"></i>
                        <span>编辑资料</span>
                    </a>
                </li>
                <li>
                    <a href="/user/change_password" class="active">
                        <i class="fas fa-key"></i>
                        <span>修改密码</span>
                    </a>
                </li>
                <li>
                    <a href="/logout">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>退出登录</span>
                    </a>
                </li>
            </ul>
        </aside>
<!-- 主内容 -->
            <main class="main-content">
                <div id="alertBox" class="alert"></div>
                
                <div class="form-section">
                    <h2 class="form-title">修改密码</h2>
                    
                    <form id="passwordForm">
                        <div class="form-group">
                            <label for="old_password">原密码</label>
                            <div class="password-wrapper">
                                <input type="password" 
                                       id="old_password" 
                                       name="old_password"
                                       placeholder="请输入原密码"
                                       required>
                                <span class="password-toggle" onclick="togglePassword('old_password', this)">
                                    <i class="fas fa-eye"></i>
                                </span>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password">新密码</label>
                            <div class="password-wrapper">
                                <input type="password" 
                                       id="new_password" 
                                       name="new_password"
                                       placeholder="请输入新密码（至少6位）"
                                       required
                                       minlength="6"
                                       oninput="checkPasswordStrength(this.value)">
                                <span class="password-toggle" onclick="togglePassword('new_password', this)">
                                    <i class="fas fa-eye"></i>
                                </span>
                            </div>
                            <div class="password-strength">
                                <div class="password-strength-bar" id="strengthBar"></div>
                            </div>
                            <p class="password-hint">密码强度：<span id="strengthText">未输入</span></p>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">确认新密码</label>
                            <div class="password-wrapper">
                                <input type="password" 
                                       id="confirm_password" 
                                       name="confirm_password"
                                       placeholder="请再次输入新密码"
                                       required>
                                <span class="password-toggle" onclick="togglePassword('confirm_password', this)">
                                    <i class="fas fa-eye"></i>
                                </span>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn-save" id="submitBtn">
                                <i class="fas fa-save"></i> 确认修改
                            </button>
                            <a href="/user" class="btn-cancel">取消</a>
                        </div>
                    </form>
                    
                    <div class="security-tips">
                        <h3><i class="fas fa-shield-alt"></i> 安全提示</h3>
                        <ul>
                            <li><i class="fas fa-check"></i> 密码长度至少6位</li>
                            <li><i class="fas fa-check"></i> 建议使用字母、数字、符号的组合</li>
                            <li><i class="fas fa-check"></i> 不要使用与其他网站相同的密码</li>
                            <li><i class="fas fa-check"></i> 定期修改密码可以保护账户安全</li>
                            <li><i class="fas fa-check"></i> 不要将密码告诉他人</li>
                        </ul>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- 密码强度验证 JavaScript -->
    <script>
        function checkPasswordStrength(password) {
            var strength = 0;
            var bar = document.getElementById('strengthBar');
            var text = document.getElementById('strengthText');
            
            if (password.length >= 6) strength++;
            if (password.length >= 10) strength++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^a-zA-Z0-9]/.test(password)) strength++;
            
            var colors = ['#ef4444', '#f97316', '#eab308', '#22c55e', '#16a34a'];
            var labels = ['非常弱', '弱', '中等', '强', '非常强'];
            
            var index = Math.min(strength, 4);
            bar.style.width = ((index + 1) * 20) + '%';
            bar.style.backgroundColor = colors[index];
            text.textContent = labels[index];
        }
    </script>

    <?php include __DIR__ . '/_footer.php'; ?>
