<?php
/**
 * 信鸽之家 - 我的分类信息
 * 
 * 功能：当前用户发布的分类信息列表管理
 * 设计：响应式（PC + 手机），继承index.php导航和页脚结构
 */

require_once dirname(__DIR__) . '/app/config/config.php';

// 检查登录
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect'] = '/user/listings';
    redirect('/login');
}

$pdo = get_db_connection();
$user_id = $_SESSION['user_id'];

// 分页参数
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$page_size = 10;
$offset = ($page - 1) * $page_size;

// 筛选状态
$status_filter = isset($_GET['status']) ? intval($_GET['status']) : -1;

// 构建查询
$where = "WHERE l.user_id = ?";
$params = [$user_id];

if ($status_filter >= 0) {
    $where .= " AND l.status = ?";
    $params[] = $status_filter;
}

// 获取总数
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM listings l $where");
$stmt->execute($params);
$total = $stmt->fetch()['total'];

$pagination = paginate($total, $page, $page_size);

// 获取列表
$params_paged = $params;
$params_paged[] = $pagination['page_size'];
$params_paged[] = $pagination['offset'];

$stmt = $pdo->prepare("
    SELECT l.* 
    FROM listings l 
    $where 
    ORDER BY l.created_at DESC 
    LIMIT ? OFFSET ?
");
$stmt->execute($params_paged);
$listings = $stmt->fetchAll();

// 统计数据
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM listings WHERE user_id = ? AND status = 1");
$stmt->execute([$user_id]);
$published_count = $stmt->fetch()['count'];

$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM listings WHERE user_id = ? AND status = 0");
$stmt->execute([$user_id]);
$pending_count = $stmt->fetch()['count'];

$stmt = $pdo->prepare("SELECT COALESCE(SUM(views), 0) as total_views FROM listings WHERE user_id = ?");
$stmt->execute([$user_id]);
$total_views = $stmt->fetch()['total_views'];

// 分类信息类型映射
$type_map = [
    1 => '出售',
    2 => '求购',
    3 => '转让',
    4 => '配对',
];

$page_title = '我的发布 | ' . SITE_NAME;
$noindex = true;
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="keywords" content="我的发布,信鸽交易,信鸽之家">
    <meta property="og:title" content="我的发布 - 信鸽之家">
    <meta property="og:description" content="管理您在信鸽之家发布的分类信息：出售、求购、转让等。">
    <meta property="og:image" content="https://www.xgjia.com/public/images/og-cover.png">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="信鸽之家">
    <link rel="canonical" href="https://www.xgjia.com/user/listings">

    <title><?php echo h($page_title); ?></title>
    <meta name="description" content="管理我发布的分类信息">
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

        /* 页面头部 */
        .page-header {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: white;
            padding: 40px 0;
            margin-bottom: 30px;
        }

        .page-header h1 {
            font-size: 32px;
            margin-bottom: 8px;
        }

        .page-header p {
            opacity: 0.85;
            font-size: 16px;
        }

        @media (max-width: 768px) {
            .page-header {
                padding: 30px 0;
            }

            .page-header h1 {
                font-size: 26px;
            }
        }

        /* 统计卡片 */
        .stats-bar {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        @media (max-width: 768px) {
            .stats-bar {
                grid-template-columns: repeat(3, 1fr);
                gap: 10px;
            }
        }

        .stat-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 20px;
            text-align: center;
            transition: all 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--box-shadow-hover);
        }

        .stat-card .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: white;
            margin-bottom: 10px;
        }

        .stat-card .stat-value {
            font-size: 28px;
            font-weight: bold;
            color: var(--gray-900);
        }

        .stat-card .stat-label {
            font-size: 13px;
            color: var(--gray-500);
            margin-top: 4px;
        }

        /* 操作栏 */
        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 12px;
        }

        .action-bar .total-info {
            color: var(--gray-600);
            font-size: 14px;
        }

        /* 筛选标签 */
        .filter-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .filter-tab {
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            background: white;
            color: var(--gray-600);
            border: 1px solid var(--gray-200);
        }

        .filter-tab:hover {
            border-color: #059669;
            color: #059669;
        }

        .filter-tab.active {
            background: #059669;
            color: white;
            border-color: #059669;
        }

        /* 分类信息列表 */
        .listing-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .listing-item {
            display: flex;
            gap: 20px;
            padding: 20px;
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            transition: all 0.3s;
            position: relative;
        }

        .listing-item:hover {
            box-shadow: var(--box-shadow-hover);
            transform: translateY(-2px);
        }

        .listing-thumb {
            width: 160px;
            height: 120px;
            object-fit: cover;
            border-radius: var(--border-radius);
            flex-shrink: 0;
        }

        .listing-thumb-placeholder {
            width: 160px;
            height: 120px;
            background: var(--gray-100);
            border-radius: var(--border-radius);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gray-300);
            font-size: 36px;
            flex-shrink: 0;
        }

        .listing-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .listing-header {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 6px;
            flex-wrap: wrap;
        }

        .listing-title {
            font-size: 17px;
            font-weight: bold;
            color: var(--gray-900);
        }

        .listing-title a {
            color: var(--gray-900);
            text-decoration: none;
        }

        .listing-title a:hover {
            color: #059669;
        }

        .listing-desc {
            color: var(--gray-500);
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 10px;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .listing-meta {
            display: flex;
            gap: 16px;
            font-size: 13px;
            color: var(--gray-400);
            flex-wrap: wrap;
            margin-top: auto;
        }

        .listing-meta span {
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .listing-price {
            font-size: 20px;
            font-weight: bold;
            color: #dc2626;
        }

        .listing-price small {
            font-size: 13px;
            font-weight: normal;
            color: var(--gray-400);
        }

        /* 类型标签 */
        .type-badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }

        .type-sell {
            background: #fee2e2;
            color: #991b1b;
        }

        .type-buy {
            background: #dbeafe;
            color: #1e40af;
        }

        .type-transfer {
            background: #fef3c7;
            color: #92400e;
        }

        .type-coop {
            background: #d1fae5;
            color: #065f46;
        }

        /* 状态标签 */
        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-published {
            background: #d1fae5;
            color: #065f46;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-offline {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-expired {
            background: var(--gray-100);
            color: var(--gray-500);
        }

        /* 操作按钮 */
        .listing-actions {
            display: flex;
            gap: 8px;
            align-items: flex-start;
            flex-shrink: 0;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn-action-edit {
            background: #d1fae5;
            color: #059669;
        }

        .btn-action-edit:hover {
            background: #059669;
            color: white;
        }

        .btn-action-delete {
            background: #fee2e2;
            color: #991b1b;
        }

        .btn-action-delete:hover {
            background: #991b1b;
            color: white;
        }

        .btn-action-refresh {
            background: #dbeafe;
            color: #1e40af;
        }

        .btn-action-refresh:hover {
            background: #1e40af;
            color: white;
        }

        @media (max-width: 768px) {
            .listing-item {
                flex-direction: column;
            }

            .listing-thumb,
            .listing-thumb-placeholder {
                width: 100%;
                height: 180px;
            }

            .listing-actions {
                justify-content: flex-end;
                padding-top: 10px;
                border-top: 1px solid var(--gray-100);
            }
        }

        /* 空状态 */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: var(--gray-500);
        }

        .empty-state i {
            font-size: 64px;
            margin-bottom: 20px;
            display: block;
            color: var(--gray-300);
        }

        .empty-state p {
            margin-bottom: 20px;
            font-size: 16px;
        }

        /* 分页 */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin: 40px 0;
            flex-wrap: wrap;
        }

        .pagination a,
        .pagination span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 38px;
            height: 38px;
            padding: 0 12px;
            background-color: white;
            border-radius: 8px;
            color: var(--gray-700);
            font-size: 14px;
            transition: all 0.3s;
            text-decoration: none;
        }

        .pagination a:hover {
            background-color: #059669;
            color: white;
        }

        .pagination .active {
            background-color: #059669;
            color: white;
            font-weight: bold;
        }

        .pagination .disabled {
            color: var(--gray-300);
            cursor: not-allowed;
        }

        /* 用户侧边栏 */
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

        .user-sidebar .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .user-sidebar .sidebar-menu li {
            border-bottom: 1px solid var(--gray-100);
        }

        .user-sidebar .sidebar-menu li:last-child {
            border-bottom: none;
        }

        .user-sidebar .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 15px 20px;
            color: var(--gray-700);
            transition: all 0.3s;
        }

        .user-sidebar .sidebar-menu a:hover {
            background-color: var(--primary);
            color: white;
        }

        .user-sidebar .sidebar-menu a.active {
            background-color: var(--primary);
            color: white;
        }

        .user-sidebar .sidebar-menu i {
            width: 20px;
            text-align: center;
        }

        .content-with-sidebar {
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 30px;
        }

        @media (max-width: 768px) {
            .content-with-sidebar {
                grid-template-columns: 1fr;
            }
        }

    </style>
</head>
<body>
    <?php include __DIR__ . '/_head.php'; ?>

    <!-- 主内容区 -->
    <div class="container content-with-sidebar" style="margin-bottom: 60px;">
        <!-- 左侧边栏 -->
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
                    <a href="/user/my_listings" class="active">
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
                    <a href="/user/change_password">
                        <i class="fas fa-key"></i>
                        <span>修改密码</span>
                    </a>
                </li>
                <li>
                    <a href="/auth?action=logout">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>退出登录</span>
                    </a>
                </li>
            </ul>
        </aside>

        <!-- 右侧主内容 -->
        <div class="main-content">
        <!-- 页面头部 -->
        <div class="page-header">
            <div class="container">
                <h1><i class="fas fa-list-alt mr-2"></i>我的发布</h1>
                <p>管理我发布的分类信息</p>
            </div>
        </div>

        <!-- 统计卡片 -->
        <div class="stats-bar">
            <div class="stat-card">
                <div class="stat-icon" style="background: #059669;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-value"><?php echo $published_count; ?></div>
                <div class="stat-label">已发布</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-yellow-500">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <div class="stat-value"><?php echo $pending_count; ?></div>
                <div class="stat-label">待审核</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-blue-500">
                    <i class="fas fa-eye"></i>
                </div>
                <div class="stat-value"><?php echo $total_views; ?></div>
                <div class="stat-label">总浏览</div>
            </div>
        </div>

        <!-- 操作栏 -->
        <div class="action-bar">
            <div class="total-info">
                <i class="fas fa-list mr-1"></i>共 <?php echo $total; ?> 条信息
            </div>
            <a href="/listing/create" class="btn btn-primary" style="background: #059669; border-color: #059669;">
                <i class="fas fa-plus mr-1"></i>发布信息
            </a>
        </div>

        <!-- 状态筛选 -->
        <div class="filter-tabs">
            <a href="?status=-1" class="filter-tab <?php echo $status_filter === -1 ? 'active' : ''; ?>">全部</a>
            <a href="?status=1" class="filter-tab <?php echo $status_filter === 1 ? 'active' : ''; ?>">已发布</a>
            <a href="?status=0" class="filter-tab <?php echo $status_filter === 0 ? 'active' : ''; ?>">待审核</a>
            <a href="?status=2" class="filter-tab <?php echo $status_filter === 2 ? 'active' : ''; ?>">已下线</a>
        </div>

        <!-- 分类信息列表 -->
        <?php if (!empty($listings)): ?>
        <div class="listing-list">
            <?php foreach ($listings as $listing): ?>
            <?php
            $images = !empty($listing['images']) ? json_decode($listing['images'], true) : [];
            $cover_image = $images[0] ?? '';
            
            $type_class_map = [
                1 => 'type-sell',
                2 => 'type-buy',
                3 => 'type-transfer',
                4 => 'type-coop',
            ];
            $type_class = $type_class_map[$listing['type']] ?? 'type-sell';
            
            $status_map = [
                0 => ['label' => '待审核', 'class' => 'status-pending'],
                1 => ['label' => '已发布', 'class' => 'status-published'],
                2 => ['label' => '已下线', 'class' => 'status-offline'],
                3 => ['label' => '已置顶', 'class' => 'status-published'],
                4 => ['label' => '已推荐', 'class' => 'status-published'],
            ];
            $s = $status_map[$listing['status']] ?? ['label' => '未知', 'class' => 'status-pending'];
            ?>
            <div class="listing-item">
                <?php if ($cover_image): ?>
                <img loading="lazy" src="<?php echo h($cover_image); ?>" alt="<?php echo h($listing['title']); ?>" class="listing-thumb">
                <?php else: ?>
                <div class="listing-thumb-placeholder">
                    <i class="fas fa-list-alt"></i>
                </div>
                <?php endif; ?>
                
                <div class="listing-content">
                    <div class="listing-header">
                        <span class="type-badge <?php echo $type_class; ?>">
                            <?php echo h($type_map[$listing['type']] ?? '其他'); ?>
                        </span>
                        <span class="status-badge <?php echo $s['class']; ?>"><?php echo $s['label']; ?></span>
                    </div>

                    <h3 class="listing-title">
                        <a href="/listing/<?php echo $listing['id']; ?>.html">
                            <?php echo h($listing['title']); ?>
                        </a>
                    </h3>

                    <?php if (!empty($listing['description'])): ?>
                    <p class="listing-desc"><?php echo h(mb_substr($listing['description'], 0, 120)); ?><?php echo mb_strlen($listing['description']) > 120 ? '...' : ''; ?></p>
                    <?php endif; ?>

                    <div class="listing-meta">
                        <?php if ($listing['price'] > 0): ?>
                        <span class="listing-price">¥<?php echo number_format($listing['price'], 0); ?> <?php if ($listing['negotiable']): ?><small>可议价</small><?php endif; ?></span>
                        <?php elseif ($listing['negotiable']): ?>
                        <span style="color: #059669; font-weight: 600;">面议</span>
                        <?php endif; ?>
                        <span><i class="fas fa-map-marker-alt"></i> <?php echo h($listing['location'] ?: '未填写'); ?></span>
                        <span><i class="fas fa-clock"></i> <?php echo date('Y-m-d', strtotime($listing['created_at'])); ?></span>
                        <span><i class="fas fa-eye"></i> <?php echo $listing['views']; ?></span>
                    </div>
                </div>

                <div class="listing-actions">
                    <a href="/listing_edit.php?id=<?php echo $listing['id']; ?>" class="btn-action btn-action-edit" title="编辑">
                        <i class="fas fa-pen"></i>
                    </a>
                    <?php if ($listing['status'] == 2): ?>
                    <button onclick="refreshListing(<?php echo $listing['id']; ?>)" class="btn-action btn-action-refresh" title="重新上架">
                        <i class="fas fa-redo"></i>
                    </button>
                    <?php endif; ?>
                    <button onclick="deleteListing(<?php echo $listing['id']; ?>, '<?php echo h($listing['title']); ?>')" class="btn-action btn-action-delete" title="删除">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- 分页 -->
        <?php echo renderPagination($page, $pagination['total_pages']); ?>

        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-list-alt"></i>
            <p>您还没有发布过分类信息</p>
            <a href="/listing/create" class="btn btn-primary" style="background: #059669; border-color: #059669;">
                <i class="fas fa-plus mr-1"></i>发布第一条信息
            </a>
        </div>
        <?php endif; ?>
    </div><!-- /.main-content -->
    </div><!-- /.content-with-sidebar -->

    <!-- 删除刷新 JavaScript -->
    <script>
        function deleteListing(id, title) {
            if (confirm('确定要删除信息《' + title + '》吗？此操作不可撤销。')) {
                var xhr = new XMLHttpRequest();
                xhr.open('POST', '/api/listing/delete', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        var response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            location.reload();
                        } else {
                            alert(response.message || '删除失败');
                        }
                    }
                };
                xhr.send('id=' + id);
            }
        }

        function refreshListing(id) {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '/api/listing/refresh', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    var response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        location.reload();
                    } else {
                        alert(response.message || '刷新失败');
                    }
                }
            };
            xhr.send('id=' + id);
        }
    </script>

    <?php include __DIR__ . '/_footer.php'; ?>
