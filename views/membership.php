<?php
/**
 * 信鸽之家 - 会员中心（月度/年度两种方案）
 */

require_once dirname(__DIR__) . '/app/config/config.php';

// $data 由 Controller::loadView() 提取
extract($data);

$page_title = $pageTitle ?? '会员中心 - 升级VIP享无限配额 | ' . SITE_NAME;
$meta_description = '信鸽之家VIP会员：月付¥29 / 年付¥299，无限发布文章、铭鸽、分类信息，查看联系方式，优先审核。';
$meta_keywords = '会员升级,信鸽会员,VIP会员,发布配额,' . SITE_KEYWORDS;

// 简化：只区分免费会员 vs VIP会员
$isVipUser = (int)($user['member_level'] ?? 0) > 0;
$currentLevel = $isVipUser ? 1 : 0;
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="信鸽之家VIP会员：月付¥29 / 年付¥299，无限发布文章、铭鸽、分类信息，查看联系方式，优先审核。">
    <meta name="keywords" content="会员,信鸽会员,VIP,铭鸽发布,联系方式,会员特权">
    <meta property="og:title" content="会员中心 - 信鸽之家">
    <meta property="og:description" content="信鸽之家VIP会员：月付¥29 / 年付¥299，无限发布文章、铭鸽、分类信息，查看联系方式，优先审核。">
    <meta property="og:image" content="https://www.xgjia.com/public/images/og-cover.png">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="信鸽之家">
    <link rel="canonical" href="https://www.xgjia.com/membership">

    <title><?php echo h($page_title); ?></title>
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
        .page-header h1 { font-size: 36px; margin-bottom: 10px; }
        .page-header p { font-size: 18px; opacity: 0.9; }

        .content-wrapper {
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 30px;
            margin-bottom: 60px;
        }
        @media (max-width: 768px) {
            .content-wrapper { grid-template-columns: 1fr; }
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
        @media (max-width: 768px) { .user-sidebar { position: static; } }

        .sidebar-menu { list-style: none; }
        .sidebar-menu li { border-bottom: 1px solid var(--gray-100); }
        .sidebar-menu li:last-child { border-bottom: none; }
        .sidebar-menu a {
            display: flex; align-items: center; gap: 10px;
            padding: 15px 20px; color: var(--gray-700); transition: all 0.3s;
        }
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background-color: var(--primary); color: white;
        }
        .sidebar-menu i { width: 20px; text-align: center; }

        .main-content {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 40px;
        }

        /* 当前会员状态 */
        .status-card {
            color: white; border-radius: var(--border-radius);
            padding: 40px; margin-bottom: 40px; text-align: center;
        }
        .status-icon { font-size: 64px; margin-bottom: 20px; }
        .status-title { font-size: 32px; font-weight: bold; margin-bottom: 10px; }
        .status-desc { font-size: 18px; opacity: 0.9; margin-bottom: 20px; }
        .expire-date { font-size: 16px; opacity: 0.8; }

        /* 会员方案卡片 */
        .section-title {
            font-size: 24px; font-weight: bold; margin-bottom: 20px;
            padding-bottom: 15px; border-bottom: 2px solid var(--gray-100);
        }
        .plans-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 40px;
        }
        @media (max-width: 1024px) { .plans-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 568px) { .plans-grid { grid-template-columns: 1fr; } }

        .plan-card {
            background: white; border: 2px solid var(--gray-200);
            border-radius: var(--border-radius); padding: 30px 20px;
            text-align: center; transition: all 0.3s; position: relative;
            display: flex; flex-direction: column; align-items: center;
        }
        .plan-card:hover {
            border-color: var(--primary); transform: translateY(-5px);
            box-shadow: var(--box-shadow-hover);
        }
        .plan-card.current {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }
        .plan-card.recommended {
            border-color: #f59e0b;
        }

        .plan-badge {
            position: absolute; top: 12px; right: 12px;
            font-size: 12px; padding: 3px 10px; border-radius: 12px;
            font-weight: 600;
        }
        .badge-current { background: #dbeafe; color: #2563eb; }
        .badge-recommend { background: #fef3c7; color: #d97706; }

        .plan-icon { font-size: 48px; margin-bottom: 12px; }
        .plan-name { font-size: 20px; font-weight: bold; margin-bottom: 6px; }
        .plan-slug { font-size: 13px; color: var(--gray-400); margin-bottom: 16px; text-transform: uppercase; letter-spacing: 1px; }

        .plan-price-block { margin-bottom: 20px; }
        .plan-price { font-size: 36px; font-weight: bold; }
        .plan-price small { font-size: 14px; font-weight: normal; color: var(--gray-500); }
        .plan-price.free { color: var(--gray-500); }
        .plan-yearly { font-size: 13px; color: var(--gray-400); margin-top: 4px; }
        .plan-yearly .save { color: #10b981; font-weight: 600; }

        .plan-features {
            list-style: none; text-align: left; margin-bottom: 24px;
            width: 100%; flex: 1;
        }
        .plan-features li {
            padding: 8px 0; border-bottom: 1px solid var(--gray-100);
            font-size: 14px; color: var(--gray-600);
        }
        .plan-features li:last-child { border-bottom: none; }
        .plan-features i { width: 18px; margin-right: 6px; }
        .plan-features .fa-check { color: #10b981; }
        .plan-features .fa-times { color: #d1d5db; }
        .plan-features .fa-infinity { color: #f59e0b; font-size: 12px; }

        .btn-plan {
            display: inline-block; padding: 12px 32px; border-radius: 25px;
            font-weight: 600; font-size: 15px; transition: all 0.3s;
            cursor: pointer; border: none;
        }
        .btn-plan-primary {
            background: var(--primary); color: white;
        }
        .btn-plan-primary:hover { transform: translateY(-2px); box-shadow: 0 4px 10px rgba(59,130,246,0.4); }
        .btn-plan-gold {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white;
        }
        .btn-plan-gold:hover { transform: translateY(-2px); box-shadow: 0 4px 10px rgba(245,158,11,0.4); }
        .btn-plan-disabled {
            background: var(--gray-200); color: var(--gray-400); cursor: not-allowed;
        }
        .btn-plan-current {
            background: transparent; color: var(--primary);
            border: 2px solid var(--primary);
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/_head.php'; ?>

    <!-- 页面头部 -->
    <div class="page-header">
        <div class="container">
            <h1><i class="fas fa-crown"></i> 会员中心</h1>
            <p>升级会员，享受更多特权与发布配额</p>
        </div>
    </div>

    <!-- 主内容区 -->
    <div class="container">
        <div class="content-wrapper">
            <!-- 侧边栏 -->
            <aside class="user-sidebar">
                <ul class="sidebar-menu">
                    <li><a href="/user"><i class="fas fa-home"></i><span>仪表盘</span></a></li>
                    <li><a href="/user/my_articles"><i class="fas fa-newspaper"></i><span>我的文章</span></a></li>
                    <li><a href="/user/my_pigeons"><i class="fas fa-dove"></i><span>我的铭鸽</span></a></li>
                    <li><a href="/user/my_listings"><i class="fas fa-list"></i><span>我的发布</span></a></li>
                    <li><a href="/pedigree/?action=pairings"><i class="fas fa-heart"></i><span>我的配对</span></a></li>
                    <li><a href="/pay/?action=orders"><i class="fas fa-receipt"></i><span>我的订单</span></a></li>
                    <li><a href="/user/membership" class="active"><i class="fas fa-crown"></i><span>会员中心</span></a></li>
                    <li><a href="/claim?action=my_claims"><i class="fas fa-hand-holding-heart"></i><span>我的认领</span></a></li>
                    <li><a href="/user/edit_profile"><i class="fas fa-user-edit"></i><span>编辑资料</span></a></li>
                    <li><a href="/user/change_password"><i class="fas fa-key"></i><span>修改密码</span></a></li>
                    <li><a href="/logout"><i class="fas fa-sign-out-alt"></i><span>退出登录</span></a></li>
                </ul>
            </aside>

            <!-- 主内容 -->
            <main class="main-content">
                <!-- 当前会员状态卡片 -->
                <div class="status-card" style="background: <?php echo $isVipUser ? 'linear-gradient(135deg, #1a5fa8 0%, #2980b9 100%)' : 'linear-gradient(135deg, #6b7280 0%, #9ca3af 100%)'; ?>;">
                    <div class="status-icon">
                        <i class="fas <?php echo $isVipUser ? 'fa-crown' : 'fa-user'; ?>"></i>
                    </div>
                    <div class="status-title"><?php echo $isVipUser ? 'VIP会员' : '免费会员'; ?></div>
                    <div class="status-desc">
                        <?php if ($isVipUser): ?>
                            您已开通VIP会员，畅享所有高级功能
                        <?php else: ?>
                            升级VIP会员，解锁无限发布与全部特权
                        <?php endif; ?>
                    </div>
                    <?php if ($isVipUser && !empty($user['member_expire_at'])): ?>
                    <div class="expire-date">
                        <i class="fas fa-clock"></i> 到期时间：<?php echo date('Y年m月d日', strtotime($user['member_expire_at'])); ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- 会员方案（月度 / 年度） -->
                <div class="plans-section">
                    <h2 class="section-title" style="margin-bottom:20px;">会员方案</h2>
                    <div class="plans-grid" style="grid-template-columns: repeat(2, 1fr);">
                        <!-- 月度会员 -->
                        <div class="plan-card recommended" onclick="location.href='/upgrade?plan_type=1'">
                            <span class="plan-badge badge-recommend">推荐</span>
                            <div class="plan-icon" style="color:#3b82f6;">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div class="plan-name">月度会员</div>
                            <div class="plan-slug" style="color:var(--gray-500);font-size:14px;">随时可取消</div>

                            <div class="plan-price-block">
                                <div class="plan-price price-display">¥29<small class="price-period">/月</small></div>
                            </div>

                            <ul class="plan-features">
                                <li><i class="fas fa-check"></i> 无限发布文章、铭鸽、分类信息</li>
                                <li><i class="fas fa-check"></i> 无限发布动态</li>
                                <li><i class="fas fa-check"></i> 查看所有联系方式</li>
                                <li><i class="fas fa-check"></i> 内容置顶（5次/月）</li>
                                <li><i class="fas fa-check"></i> 优先审核</li>
                                <li><i class="fas fa-check"></i> 完全无广告</li>
                            </ul>

                            <button class="btn-plan btn-plan-primary" onclick="event.stopPropagation(); location.href='/upgrade?plan_type=1'">
                                立即开通月度会员
                            </button>
                        </div>

                        <!-- 年度会员 -->
                        <div class="plan-card" onclick="location.href='/upgrade?plan_type=2'">
                            <div class="plan-icon" style="color:#f59e0b;">
                                <i class="fas fa-crown"></i>
                            </div>
                            <div class="plan-name">年度会员</div>
                            <div class="plan-slug">
                                <span style="color:#059669;font-size:13px;font-weight:600;">年付立省 ¥499</span>
                            </div>

                            <div class="plan-price-block">
                                <div class="plan-price price-display">¥299<small class="price-period">/年</small></div>
                                <div style="color:var(--gray-500);font-size:14px;margin-top:4px;">
                                    相当于 ¥25/月，立省 40%
                                </div>
                            </div>

                            <ul class="plan-features">
                                <li><i class="fas fa-check"></i> <strong>月度会员全部特权</strong></li>
                                <li><i class="fas fa-check"></i> <strong>内容置顶提升至 100 次/年</strong></li>
                                <li><i class="fas fa-check"></i> <strong>专属年度会员标识</strong></li>
                                <li><i class="fas fa-check"></i> <strong>优先推荐展示</strong></li>
                                <li><i class="fas fa-check"></i> <strong>专属客服支持</strong></li>
                            </ul>

                            <button class="btn-plan btn-plan-gold" onclick="event.stopPropagation(); location.href='/upgrade?plan_type=2'">
                                立即开通年度会员
                            </button>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>


    <?php include __DIR__ . '/_footer.php'; ?>
