<?php
/**
 * 信鸽之家 - 编辑资料
 */

require_once dirname(__DIR__) . '/app/config/config.php';

// $data 由 Controller::loadView() 提取
extract($data);

$page_title = $pageTitle ?? '编辑资料 | ' . SITE_NAME;
$noindex = true;
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="编辑信鸽之家个人资料，修改头像、昵称、联系方式等信息。">
    <meta name="keywords" content="编辑资料,个人资料,信鸽之家">
    <meta property="og:title" content="编辑资料 - 信鸽之家">
    <meta property="og:description" content="编辑信鸽之家个人资料，修改头像、昵称、联系方式等信息。">
    <meta property="og:image" content="https://www.xgjia.com/public/images/og-cover.png">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="信鸽之家">
    <link rel="canonical" href="https://www.xgjia.com/user/edit-profile">

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
            max-width: 600px;
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

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid var(--gray-200);
            border-radius: var(--border-radius);
            font-size: 16px;
            transition: all 0.3s;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .avatar-upload {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
        }

        .current-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--gray-200);
        }

        .avatar-actions {
            flex: 1;
        }

        .avatar-actions p {
            font-size: 14px;
            color: var(--gray-500);
            margin-bottom: 10px;
        }

        .avatar-input {
            display: none;
        }

        .btn-upload {
            display: inline-block;
            padding: 10px 20px;
            background: var(--gray-100);
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-upload:hover {
            background: var(--gray-200);
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
        }

        .btn-save:hover {
            background: #1e40af;
            transform: translateY(-2px);
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
    </style>
</head>
<body>
    <?php include __DIR__ . '/_head.php'; ?>
<!-- 页面头部 -->
    <div class="page-header">
        <div class="container">
            <h1><i class="fas fa-user-edit"></i> 编辑资料</h1>
            <p>修改您的个人信息</p>
        </div>
    </div>

    <!-- 主内容区 -->
    <div class="container">
        <div class="content-wrapper">
            <!-- 侧边栏 -->
            <aside class="user-sidebar">
            <ul class="sidebar-menu">
                <li>
                    <a href="/">
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
                    <a href="/user/edit_profile" class="active">
                        <i class="fas fa-user-edit"></i>
                        <span>编辑资料</span>
                    </a>
                </li>
                <li>
                    <a href="/user/change_password">
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
                    <h2 class="form-title">基本信息</h2>

                    <!-- 头像上传 -->
                    <div class="avatar-upload">
                        <img loading="lazy" src="<?php echo $user['avatar'] ?: '/public/assets/images/default-avatar.png'; ?>" alt="用户头像"
                             alt="头像"
                             class="current-avatar"
                             id="avatarPreview">

                        <div class="avatar-actions">
                            <p>支持 JPG、PNG 格式,大小不超过 2MB</p>
                            <input type="file"
                                   id="avatarInput"
                                   class="avatar-input"
                                   accept="image/jpeg,image/png"
                                   onchange="previewAvatar(this)">
                            <label for="avatarInput" class="btn-upload">
                                <i class="fas fa-upload"></i> 上传头像
                            </label>
                        </div>
                    </div>

                    <form id="profileForm">
                        <div class="form-group">
                            <label for="username">用户名</label>
                            <input type="text"
                                   id="username"
                                   value="<?php echo h($user['username'] ?? ''); ?>"
                                   disabled
                                   style="background: var(--gray-100); cursor: not-allowed;">
                            <small style="color: var(--gray-500); margin-top: 5px; display: block;">
                                用户名不可修改
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="nickname">昵称</label>
                            <input type="text"
                                   id="nickname"
                                   name="nickname"
                                   value="<?php echo h($user['nickname'] ?? ''); ?>"
                                   placeholder="请输入昵称">
                        </div>

                        <div class="form-group">
                            <label for="email">邮箱</label>
                            <input type="email"
                                   id="email"
                                   name="email"
                                   value="<?php echo h($user['email'] ?? ''); ?>"
                                   placeholder="请输入邮箱">
                        </div>

                        <div class="form-group">
                            <label for="phone">手机号</label>
                            <input type="tel"
                                   id="phone"
                                   name="phone"
                                   value="<?php echo h($user['phone'] ?? ''); ?>"
                                   placeholder="请输入手机号">
                        </div>

                        <div class="form-group">
                            <label for="bio">个人简介</label>
                            <textarea id="bio"
                                      name="bio"
                                      placeholder="介绍一下自己吧..."><?php echo h($user['bio'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn-save">
                                <i class="fas fa-save"></i> 保存修改
                            </button>
                            <a href="/user" class="btn-cancel">取消</a>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>

    <!-- 头像预览 JavaScript -->
    <script>
        function previewAvatar(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('avatarPreview').src = e.target.result;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>

    <?php include __DIR__ . '/_footer.php'; ?>
