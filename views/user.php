<?php
/**
 * 信鸽之家 - 用户中心
 */

require_once dirname(__DIR__) . '/app/config/config.php';

// $data 由 Controller::loadView() 提取
extract($data);

// 检查登录（控制器已检查，这里二次确认）
if (!isset($user)) {
    redirect('/login');
}

$page_title = $pageTitle ?? '个人中心 | ' . SITE_NAME;
$noindex = true;
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="信鸽之家个人中心，管理您的铭鸽、分类信息、订单、配对记录。">
    <meta name="keywords" content="个人中心,我的铭鸽,信鸽之家">
    <meta property="og:title" content="个人中心 - 信鸽之家">
    <meta property="og:description" content="信鸽之家个人中心，管理您的铭鸽、分类信息、订单、配对记录。">
    <meta property="og:image" content="https://www.xgjia.com/public/images/og-cover.png">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="信鸽之家">
    <link rel="canonical" href="https://www.xgjia.com/user">

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

        .user-header {
            background: linear-gradient(135deg, var(--primary) 0%, #1e40af 100%);
            color: white;
            padding: 60px 0;
            margin-bottom: 40px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 30px;
        }
        
        @media (max-width: 768px) {
            .user-header {
                padding: 40px 0;
            }
            
            .user-info {
                flex-direction: column;
                text-align: center;
                gap: 20px;
            }
        }
        
        .user-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid white;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        
        .user-details h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .user-meta {
            font-size: 16px;
            opacity: 0.9;
            margin-bottom: 15px;
        }
        
        .user-stats {
            display: flex;
            gap: 40px;
            margin-top: 20px;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-value {
            font-size: 28px;
            font-weight: bold;
        }
        
        .stat-label {
            font-size: 14px;
            opacity: 0.8;
        }
        
        @media (max-width: 768px) {
            .user-stats {
                gap: 20px;
            }
            
            .stat-value {
                font-size: 24px;
            }
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
        
        .dashboard {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        @media (max-width: 968px) {
            .dashboard {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 568px) {
            .dashboard {
                grid-template-columns: 1fr;
            }
        }
        
        .dashboard-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            transition: all 0.3s;
        }
        
        .dashboard-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--box-shadow-hover);
        }
        
        .dashboard-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }
        
        .dashboard-content h3 {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .dashboard-content p {
            color: var(--gray-500);
            font-size: 14px;
        }
        
        .section-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--gray-100);
        }
        
        .recent-section {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .recent-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid var(--gray-100);
        }
        
        .recent-item:last-child {
            border-bottom: none;
        }
        
        .recent-image {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: var(--border-radius);
            flex-shrink: 0;
        }
        
        .recent-info {
            flex: 1;
        }
        
        .recent-title {
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .recent-meta {
            font-size: 14px;
            color: var(--gray-500);
        }
        
        .no-data {
            text-align: center;
            padding: 40px 20px;
            color: var(--gray-500);
        }
        
        .no-data i {
            font-size: 48px;
            margin-bottom: 15px;
            display: block;
            color: var(--gray-300);
        }
        
        .vip-badge {
            display: inline-block;
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            margin-left: 10px;
        }
        
        .free-badge {
            display: inline-block;
            background: var(--gray-300);
            color: var(--gray-700);
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            margin-left: 10px;
        }
        
        .btn-upgrade {
            display: inline-block;
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            padding: 10px 30px;
            border-radius: 25px;
            font-weight: 600;
            margin-top: 15px;
            transition: all 0.3s;
        }
        
        .btn-upgrade:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(245, 158, 11, 0.4);
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/_head.php'; ?>
<!-- 用户头部 -->
    <div class="user-header">
        <div class="container">
            <div class="user-info">
                <img src="<?php echo $user['avatar'] ?: '/public/assets/images/default-avatar.png'; ?>" alt="用户头像" 
                     alt="<?php echo h($user['nickname'] ?: $user['username']); ?>" 
                     class="user-avatar">
                
                <div class="user-details">
                    <h1>
                        <?php echo h($user['nickname'] ?: $user['username']); ?>
                        <?php if ($isVip): ?>
                            <span class="vip-badge"><i class="fas fa-crown"></i> VIP会员</span>
                        <?php else: ?>
                            <span class="free-badge">免费会员</span>
                        <?php endif; ?>
                    </h1>
                    
                    <div class="user-meta">
                        <i class="fas fa-envelope"></i> <?php echo h($user['email']); ?>
                        <?php if ($user['phone']): ?>
                            | <i class="fas fa-phone"></i> <?php echo h($user['phone']); ?>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($isVip): ?>
                        <div class="user-meta">
                            <i class="fas fa-clock"></i> 会员到期：<?php echo date('Y-m-d', strtotime($user['member_expire_at'])); ?>
                        </div>
                    <?php else: ?>
                        <a href="/user/membership" class="btn-upgrade">
                            <i class="fas fa-crown"></i> 升级VIP会员
                        </a>
                    <?php endif; ?>
                    
                    <div class="user-stats">
                        <div class="stat-item">
                            <div class="stat-value"><?php echo count($myArticles); ?></div>
                            <div class="stat-label">文章</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?php echo count($myPigeons); ?></div>
                            <div class="stat-label">铭鸽</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?php echo count($myListings); ?></div>
                            <div class="stat-label">信息</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 主内容区 -->
    <div class="container">
        <div class="content-wrapper">
            <!-- 侧边栏 -->
            <aside class="user-sidebar">
            <ul class="sidebar-menu">
                <li>
                    <a href="/" class="active">
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
                    <a href="/user/membership">
                        <i class="fas fa-crown"></i>
                        <span>会员中心</span>
                    </a>
                </li>
                <li>
                    <a href="/pay/?action=orders">
                        <i class="fas fa-receipt"></i>
                        <span>我的订单</span>
                    </a>
                </li>
                <li>
                    <a href="/pedigree/pairings/">
                        <i class="fas fa-project-diagram"></i>
                        <span>我的配对</span>
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
            <main>
                <!-- 仪表盘统计 -->
                <div class="dashboard">
                    <div class="dashboard-card">
                        <div class="dashboard-icon bg-blue-500">
                            <i class="fas fa-newspaper"></i>
                        </div>
                        <div class="dashboard-content">
                            <h3><?php echo count($myArticles); ?></h3>
                            <p>文章</p>
                        </div>
                    </div>
                    
                    <div class="dashboard-card">
                        <div class="dashboard-icon bg-green-500">
                            <i class="fas fa-dove"></i>
                        </div>
                        <div class="dashboard-content">
                            <h3><?php echo count($myPigeons); ?></h3>
                            <p>铭鸽</p>
                        </div>
                    </div>
                    
                    <div class="dashboard-card">
                        <div class="dashboard-icon bg-purple-500">
                            <i class="fas fa-list"></i>
                        </div>
                        <div class="dashboard-content">
                            <h3><?php echo count($myListings); ?></h3>
                            <p>发布</p>
                        </div>
                    </div>
                </div>

                <!-- 最近文章 -->
                <div class="recent-section">
                    <h2 class="section-title">
                        <i class="fas fa-newspaper"></i> 最近文章
                        <a href="/user/my_articles" style="float: right; font-size: 14px; font-weight: normal;">查看全部</a>
                    </h2>
                    
                    <?php if (!empty($myArticles)): ?>
                        <?php foreach ($myArticles as $article): ?>
                        <div class="recent-item">
                            <?php if ($article['cover']): ?>
                            <img loading="lazy" src="<?php echo h($article['cover']); ?>" alt="<?php echo h($article['title'] ?? '文章封面'); ?>" 
                                 alt="<?php echo h($article['title']); ?>" 
                                 class="recent-image">
                            <?php endif; ?>
                            
                            <div class="recent-info">
                                <div class="recent-title">
                                    <a href="/article/<?php echo $article['id']; ?>.html">
                                        <?php echo h($article['title']); ?>
                                    </a>
                                </div>
                                <div class="recent-meta">
                                    <span><i class="fas fa-eye"></i> <?php echo $article['views']; ?></span> |
                                    <span><i class="fas fa-heart"></i> <?php echo $article['likes']; ?></span> |
                                    <span><?php echo date('Y-m-d', strtotime($article['created_at'])); ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-data">
                            <i class="fas fa-newspaper"></i>
                            <p>暂无文章</p>
                            <a href="/article/create" class="btn btn-primary mt-3">发布文章</a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- 最近铭鸽 -->
                <div class="recent-section">
                    <h2 class="section-title">
                        <i class="fas fa-dove"></i> 最近铭鸽
                        <a href="/user/my_pigeons" style="float: right; font-size: 14px; font-weight: normal;">查看全部</a>
                    </h2>
                    
                    <?php if (!empty($myPigeons)): ?>
                        <?php foreach ($myPigeons as $pigeon): ?>
                        <div class="recent-item">
                            <?php 
                            $images = json_decode($pigeon['images'] ?? '[]', true) ?: [];
                            if (!empty($images[0])):
                            ?>
                            <img loading="lazy" src="<?php echo h($images[0]); ?>" alt="<?php echo h($images[0] ?? '铭鸽图片'); ?>" 
                                 alt="<?php echo h($pigeon['name']); ?>" 
                                 class="recent-image">
                            <?php endif; ?>
                            
                            <div class="recent-info">
                                <div class="recent-title">
                                    <a href="/pigeon/<?php echo $pigeon['id']; ?>.html">
                                        <?php echo h($pigeon['name']); ?>
                                    </a>
                                </div>
                                <div class="recent-meta">
                                    <span>足环：<?php echo h($pigeon['ring_number']); ?></span> |
                                    <span><i class="fas fa-eye"></i> <?php echo $pigeon['views']; ?></span> |
                                    <span><?php echo date('Y-m-d', strtotime($pigeon['created_at'])); ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-data">
                            <i class="fas fa-dove"></i>
                            <p>暂无铭鸽</p>
                            <a href="/pigeon/create" class="btn btn-primary mt-3">发布铭鸽</a>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>


    <?php include __DIR__ . '/_footer.php'; ?>
