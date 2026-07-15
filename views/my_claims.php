<?php
/**
 * 信鸽之家 - 我的认领申请
 */

require_once dirname(__DIR__) . '/app/config/config.php';

extract($data);

$page_title = $pageTitle ?? '我的认领 | ' . SITE_NAME;
$noindex = true;

$statusMap = [0 => '待审核', 1 => '已通过', 2 => '已拒绝', 3 => '已取消'];
$statusColors = [0 => '#f59e0b', 1 => '#10b981', 2 => '#ef4444', 3 => '#9ca3af'];
$statusBg = [0 => '#fef3c7', 1 => '#d1fae5', 2 => '#fee2e2', 3 => '#f3f4f6'];
$typeNames = ['shop' => '展厅', 'loft' => '公棚'];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="查看您在信鸽之家认领的公棚或鸽舍审核状态。">
    <meta name="keywords" content="我的认领,认领记录,信鸽之家">
    <meta property="og:title" content="我的认领 - 信鸽之家">
    <meta property="og:description" content="查看您在信鸽之家认领的公棚或鸽舍审核状态。">
    <meta property="og:image" content="https://www.xgjia.com/public/images/og-cover.png">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="信鸽之家">
    <link rel="canonical" href="https://www.xgjia.com/user/claims">

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
            color: white; padding: 40px 0; margin-bottom: 30px; text-align: center;
        }
        .page-header h1 { font-size: 32px; margin-bottom: 8px; }
        .page-header p { font-size: 16px; opacity: 0.9; }

        .content-wrapper {
            display: grid; grid-template-columns: 220px 1fr; gap: 24px;
        }
        @media (max-width: 768px) { .content-wrapper { grid-template-columns: 1fr; } }

        .user-sidebar {
            background: white; border-radius: var(--border-radius);
            box-shadow: var(--box-shadow); overflow: hidden; height: fit-content;
            position: sticky; top: 90px;
        }
        @media (max-width: 768px) { .user-sidebar { position: static; } }
        .sidebar-menu { list-style: none; margin: 0; padding: 0; }
        .sidebar-menu li { border-bottom: 1px solid var(--gray-100); }
        .sidebar-menu a {
            display: flex; align-items: center; gap: 10px;
            padding: 14px 18px; color: var(--gray-700); transition: all 0.2s; font-size: 14px;
        }
        .sidebar-menu a:hover, .sidebar-menu a.active { background: var(--primary); color: white; }
        .sidebar-menu i { width: 18px; text-align: center; }

        .main-content {
            background: white; border-radius: var(--border-radius);
            box-shadow: var(--box-shadow); padding: 30px;
        }

        /* 状态筛选 */
        .filter-tabs {
            display: flex; gap: 8px; margin-bottom: 24px; flex-wrap: wrap;
        }
        .filter-tab {
            padding: 8px 18px; border-radius: 20px; font-size: 13px;
            cursor: pointer; transition: all 0.2s; border: 1px solid var(--gray-200);
            background: white; color: var(--gray-600); text-decoration: none;
        }
        .filter-tab:hover { border-color: var(--primary); color: var(--primary); }
        .filter-tab.active {
            background: var(--primary); color: white; border-color: var(--primary);
        }

        /* 认领卡片 */
        .claim-list { display: flex; flex-direction: column; gap: 16px; }
        .claim-card {
            border: 1px solid var(--gray-200); border-radius: var(--border-radius);
            padding: 20px; transition: all 0.2s; position: relative;
        }
        .claim-card:hover { box-shadow: var(--box-shadow-hover); border-color: var(--gray-300); }
        .claim-header {
            display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;
        }
        .claim-target {
            display: flex; align-items: center; gap: 10px;
        }
        .claim-type-badge {
            padding: 4px 10px; border-radius: 4px; font-size: 12px; font-weight: 600;
        }
        .claim-type-shop { background: #ede9fe; color: #7c3aed; }
        .claim-type-loft { background: #e0f2fe; color: #0369a1; }
        .claim-target-name {
            font-size: 16px; font-weight: 600; color: var(--gray-800);
        }
        .claim-status {
            padding: 5px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;
        }
        .claim-info {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 12px;
            margin-bottom: 12px;
        }
        .claim-info-item { font-size: 13px; color: var(--gray-500); }
        .claim-info-item span { color: var(--gray-700); font-weight: 500; }
        .claim-reason {
            padding: 12px; background: var(--gray-50); border-radius: 6px;
            font-size: 13px; color: var(--gray-600); line-height: 1.6;
        }
        .claim-admin-note {
            margin-top: 10px; padding: 10px 12px; background: #fffbeb;
            border-left: 3px solid #f59e0b; border-radius: 0 6px 6px 0;
            font-size: 13px; color: #92400e;
        }
        .claim-actions {
            margin-top: 14px; display: flex; gap: 8px;
        }
        .btn-cancel-claim {
            padding: 6px 14px; border-radius: 6px; font-size: 13px;
            background: white; border: 1px solid #ef4444; color: #ef4444;
            cursor: pointer; transition: all 0.2s;
        }
        .btn-cancel-claim:hover { background: #ef4444; color: white; }
        .btn-edit-claim {
            padding: 6px 14px; border-radius: 6px; font-size: 13px;
            background: var(--primary); color: white;
            cursor: pointer; transition: all 0.2s; text-decoration: none;
            display: inline-block;
        }
        .btn-edit-claim:hover { background: var(--primary-dark); }

        /* 空状态 */
        .empty-state {
            text-align: center; padding: 60px 20px; color: var(--gray-400);
        }
        .empty-state i { font-size: 48px; margin-bottom: 16px; }
        .empty-state p { font-size: 15px; margin-bottom: 20px; }
        .empty-state a {
            display: inline-block; padding: 10px 24px; border-radius: 25px;
            background: var(--primary); color: white; text-decoration: none;
            font-size: 14px; font-weight: 600;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/_head.php'; ?>

    <!-- 页面头部 -->
    <div class="page-header">
        <div class="container">
            <h1><i class="fas fa-hand-holding-heart"></i> 我的认领</h1>
            <p>管理您的展厅和公棚认领申请</p>
        </div>
    </div>

    <!-- 主内容 -->
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
                    <li><a href="/user/membership"><i class="fas fa-crown"></i><span>会员中心</span></a></li>
                    <li><a href="/claim?action=my_claims" class="active"><i class="fas fa-hand-holding-heart"></i><span>我的认领</span></a></li>
                    <li><a href="/user/edit_profile"><i class="fas fa-user-edit"></i><span>编辑资料</span></a></li>
                    <li><a href="/user/change_password"><i class="fas fa-key"></i><span>修改密码</span></a></li>
                    <li><a href="/logout"><i class="fas fa-sign-out-alt"></i><span>退出登录</span></a></li>
                </ul>
            </aside>

            <!-- 主内容区 -->
            <main class="main-content">
                <!-- 状态筛选 -->
                <div class="filter-tabs">
                    <a href="/claim?action=my_claims" class="filter-tab <?php echo $currentStatus === null ? 'active' : ''; ?>">全部</a>
                    <a href="/claim?action=my_claims&status=0" class="filter-tab <?php echo $currentStatus === 0 ? 'active' : ''; ?>">待审核</a>
                    <a href="/claim?action=my_claims&status=1" class="filter-tab <?php echo $currentStatus === 1 ? 'active' : ''; ?>">已通过</a>
                    <a href="/claim?action=my_claims&status=2" class="filter-tab <?php echo $currentStatus === 2 ? 'active' : ''; ?>">已拒绝</a>
                    <a href="/claim?action=my_claims&status=3" class="filter-tab <?php echo $currentStatus === 3 ? 'active' : ''; ?>">已取消</a>
                </div>

                <?php if (empty($claims)): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>暂无认领申请记录</p>
                    <a href="/shop/">浏览展厅</a>
                </div>
                <?php else: ?>
                <div class="claim-list">
                    <?php foreach ($claims as $claim): ?>
                    <?php
                        $s = intval($claim['status']);
                        $targetName = $claim['target_type'] === 'shop' ? ($claim['shop_name'] ?? '未知展厅') : ($claim['loft_name'] ?? '未知公棚');
                        $targetUrl = $claim['target_type'] === 'shop' ? '/shop/' . $claim['target_id'] . '.html' : '/loft/' . $claim['target_id'] . '.html';
                    ?>
                    <div class="claim-card">
                        <div class="claim-header">
                            <div class="claim-target">
                                <span class="claim-type-badge claim-type-<?php echo $claim['target_type']; ?>">
                                    <?php echo $typeNames[$claim['target_type']] ?? ''; ?>
                                </span>
                                <a href="<?php echo $targetUrl; ?>" class="claim-target-name"><?php echo h($targetName); ?></a>
                            </div>
                            <span class="claim-status" style="background: <?php echo $statusBg[$s]; ?>; color: <?php echo $statusColors[$s]; ?>;">
                                <?php echo $statusMap[$s]; ?>
                            </span>
                        </div>
                        <div class="claim-info">
                            <div class="claim-info-item">姓名: <span><?php echo h($claim['real_name']); ?></span></div>
                            <div class="claim-info-item">电话: <span><?php echo h($claim['phone']); ?></span></div>
                            <?php if (!empty($claim['wechat'])): ?>
                            <div class="claim-info-item">微信: <span><?php echo h($claim['wechat']); ?></span></div>
                            <?php endif; ?>
                            <div class="claim-info-item">申请时间: <span><?php echo date('Y-m-d H:i', strtotime($claim['created_at'])); ?></span></div>
                        </div>
                        <div class="claim-reason"><?php echo h($claim['reason']); ?></div>
                        <?php if (!empty($claim['admin_note'])): ?>
                        <div class="claim-admin-note">
                            <i class="fas fa-info-circle"></i> 管理员备注: <?php echo h($claim['admin_note']); ?>
                        </div>
                        <?php endif; ?>
                        <div class="claim-actions">
                            <?php if ($s === 0): ?>
                            <button class="btn-cancel-claim" onclick="cancelClaim(<?php echo $claim['id']; ?>)">取消申请</button>
                            <?php elseif ($s === 1): ?>
                            <a href="<?php echo $claim['target_type'] === 'shop' ? '/shop/edit/'.intval($claim['target_id']).'/' : '/loft/edit/'.intval($claim['target_id']).'/'; ?>" class="btn-edit-claim">编辑</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <?php echo renderPagination($page, $totalPages); ?>
            <?php endif; ?>
            </main>
        </div>
    </div>

    <!-- 底部导航 -->
    <nav class="mobile-bottom-nav">
        <div class="nav-items">
            <div class="nav-item" onclick="location.href='/'"><i class="fas fa-home"></i><span>首页</span></div>
            <div class="nav-item" onclick="location.href='/article/'"><i class="fas fa-newspaper"></i><span>资讯</span></div>
            <div class="nav-item" onclick="location.href='/shop/'"><i class="fas fa-dove"></i><span>铭鸽</span></div>
            <div class="nav-item" onclick="location.href='/loft/'"><i class="fas fa-building"></i><span>公棚</span></div>
            <div class="nav-item" onclick="location.href='/dynamics/'"><i class="fas fa-comments"></i><span>鸽友圈</span></div>
        </div>
    </nav>

    <script>
        function toggleMenu() {
            document.getElementById('navbarMenu').classList.toggle('active');
        }

        function cancelClaim(id) {
            if (!confirm('确认取消此认领申请？')) return;
            var fd = new FormData();
            fd.append('id', id);
            fetch('/claim?action=cancel', { method: 'POST', body: fd })
                .then(function(r) { return r.json(); })
                .then(function(d) {
                    alert(d.message);
                    if (d.success) location.reload();
                })
                .catch(function() { alert('操作失败'); });
        }
    </script>

    <?php include __DIR__ . '/_footer.php'; ?>
</body>
</html>
