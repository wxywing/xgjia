<?php
require_once dirname(__DIR__) . '/app/config/config.php';
$page_title = $pageTitle ?? '我的订单 | ' . SITE_NAME;
$noindex = true;
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="查看您在信鸽之家的订单记录，包括会员购买、交易订单等。">
    <meta name="keywords" content="我的订单,信鸽之家">
    <meta property="og:title" content="我的订单 - 信鸽之家">
    <meta property="og:description" content="查看您在信鸽之家的订单记录，包括会员购买、交易订单等。">
    <meta property="og:image" content="https://www.xgjia.com/public/images/og-cover.png">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="信鸽之家">
    <link rel="canonical" href="https://www.xgjia.com/user/orders">

    <title><?php echo h($page_title); ?></title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/public/css/b-scheme.css">
    <link rel="shortcut icon" href="/public/images/favicon.ico">
</head>
<body>
    <?php include __DIR__ . '/_head.php'; ?>

    <!-- 主内容区 -->
    <div class="container content-with-sidebar" style="margin-bottom:60px;">
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

            .content-with-sidebar{display:grid;grid-template-columns:250px 1fr;gap:30px;}
            @media(max-width:768px){.content-with-sidebar{grid-template-columns:1fr;}}
            .user-sidebar{background:#fff;border-radius:10px;box-shadow:0 1px 4px rgba(0,0,0,0.06);padding:0;height:fit-content;position:sticky;top:90px;}
            @media(max-width:768px){.user-sidebar{position:static;}}
            .sidebar-menu{list-style:none;margin:0;padding:0;}
            .sidebar-menu li{border-bottom:1px solid #f3f4f6;}
            .sidebar-menu li:last-child{border-bottom:none;}
            .sidebar-menu a{display:flex;align-items:center;gap:10px;padding:14px 20px;color:#374151;transition:all .3s;}
            .sidebar-menu a:hover,.sidebar-menu a.active{background:#1a2a3a;color:#fff;}
            .sidebar-menu i{width:20px;text-align:center;}
            .main-content{background:#fff;border-radius:10px;padding:24px;box-shadow:0 1px 4px rgba(0,0,0,0.06);}
        </style>
        <aside class="user-sidebar">
            <ul class="sidebar-menu">
                <li><a href="/user"><i class="fas fa-home"></i><span>仪表盘</span></a></li>
                <li><a href="/user/my_articles"><i class="fas fa-newspaper"></i><span>我的文章</span></a></li>
                <li><a href="/user/my_pigeons"><i class="fas fa-dove"></i><span>我的铭鸽</span></a></li>
                <li><a href="/user/my_listings"><i class="fas fa-list"></i><span>我的发布</span></a></li>
                <li><a href="/pedigree/?action=pairings"><i class="fas fa-heart"></i><span>我的配对</span></a></li>
                <li><a href="/pay/?action=orders" class="active"><i class="fas fa-receipt"></i><span>我的订单</span></a></li>
                <li><a href="/user/membership"><i class="fas fa-crown"></i><span>会员中心</span></a></li>
                <li><a href="/claim?action=my_claims"><i class="fas fa-hand-holding-heart"></i><span>我的认领</span></a></li>
                <li><a href="/user/edit_profile"><i class="fas fa-user-edit"></i><span>编辑资料</span></a></li>
                <li><a href="/user/change_password"><i class="fas fa-key"></i><span>修改密码</span></a></li>
                <li><a href="/logout"><i class="fas fa-sign-out-alt"></i><span>退出登录</span></a></li>
            </ul>
        </aside>
        <main class="main-content">
            <h2 style="font-size:22px;margin-bottom:20px;"><i class="fas fa-receipt" style="margin-right:8px;"></i>我的订单</h2>

        <?php if ($stats): ?>
        <div style="display:flex;gap:16px;margin-bottom:24px;flex-wrap:wrap;">
            <div style="flex:1;min-width:120px;background:#fff;border-radius:10px;padding:16px;text-align:center;box-shadow:0 1px 4px rgba(0,0,0,0.06);">
                <div style="font-size:24px;font-weight:bold;color:#d4a843;"><?php echo $stats['total']; ?></div>
                <div style="font-size:13px;color:#6b7280;">全部订单</div>
            </div>
            <div style="flex:1;min-width:120px;background:#fff;border-radius:10px;padding:16px;text-align:center;box-shadow:0 1px 4px rgba(0,0,0,0.06);">
                <div style="font-size:24px;font-weight:bold;color:#10b981;"><?php echo $stats['paid']; ?></div>
                <div style="font-size:13px;color:#6b7280;">已支付</div>
            </div>
            <div style="flex:1;min-width:120px;background:#fff;border-radius:10px;padding:16px;text-align:center;box-shadow:0 1px 4px rgba(0,0,0,0.06);">
                <div style="font-size:24px;font-weight:bold;color:#f59e0b;"><?php echo $stats['pending']; ?></div>
                <div style="font-size:13px;color:#6b7280;">待支付</div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (empty($orders)): ?>
        <div style="background:#fff;border-radius:10px;padding:60px 20px;text-align:center;box-shadow:0 1px 4px rgba(0,0,0,0.06);">
            <i class="fas fa-inbox" style="font-size:48px;color:#d1d5db;margin-bottom:12px;"></i>
            <p style="color:#6b7280;">暂无订单记录</p>
            <a href="/user/membership" style="display:inline-block;margin-top:16px;padding:10px 24px;background:#1a2a3a;color:#fff;border-radius:8px;text-decoration:none;">开通会员</a>
        </div>
        <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:12px;">
            <?php foreach ($orders as $o):
                $isMembership = ($o['product_type'] ?? 'membership') === 'membership' || ($o['product_type'] ?? '') === '';
                // 会员订单：待支付 = 待审核
                if ($isMembership && $o['status'] == 0) {
                    $statusLabel = '待审核';
                    $statusColorVal = '#f59e0b';
                } else {
                    $statusLabelMap = ['0'=>'待支付','1'=>'已支付','2'=>'已取消','3'=>'已退款'];
                    $statusLabel = $statusLabelMap[$o['status']] ?? '未知';
                    $statusColorMap = ['0'=>'#f59e0b','1'=>'#10b981','2'=>'#6b7280','3'=>'#ef4444'];
                    $statusColorVal = $statusColorMap[$o['status']] ?? '#6b7280';
                }
            ?>
            <div style="background:#fff;border-radius:10px;padding:16px 20px;box-shadow:0 1px 4px rgba(0,0,0,0.06);">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                    <span style="font-size:13px;color:#6b7280;"><?php echo h($o['order_no']); ?></span>
                    <span style="font-size:13px;color:<?php echo $statusColorVal; ?>;font-weight:500;"><?php echo $statusLabel; ?></span>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <div>
                        <div style="font-weight:500;font-size:15px;"><?php echo h($o['plan_name'] ?? '会员套餐'); ?></div>
                        <div style="font-size:12px;color:#9ca3af;margin-top:2px;"><?php echo $o['created_at'] ?? ''; ?></div>
                    </div>
                    <div style="font-size:18px;font-weight:bold;color:#d4a843;">¥<?php echo number_format($o['amount'], 2); ?></div>
                </div>
                <?php if ($o['status'] == 0 && !$isMembership): ?>
                <div style="margin-top:10px;text-align:right;">
                    <a href="/pay/?action=result&order_no=<?php echo urlencode($o['order_no']); ?>" style="padding:6px 16px;background:#1a2a3a;color:#fff;border-radius:6px;font-size:13px;text-decoration:none;">去支付</a>
                </div>
                <?php elseif ($o['status'] == 0 && $isMembership): ?>
                <div style="margin-top:10px;text-align:right;">
                    <a href="/upgrade?plan_type=<?php echo intval($o['plan_type'] ?? 1); ?>&status=pending&order_no=<?php echo urlencode($o['order_no']); ?>" style="padding:6px 16px;background:#f59e0b;color:#fff;border-radius:6px;font-size:13px;text-decoration:none;">查看详情</a>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        </main>
    </div>

    <footer class="footer"><div class="container"><div class="footer-bottom"><p>&copy; 2026 <?php echo SITE_NAME; ?> 版权所有</p></div></div></footer>
    <script>function toggleMenu(){document.getElementById('navbarMenu').classList.toggle('active');}</script>

    <?php include __DIR__ . '/_footer.php'; ?>
</body>
</html>
