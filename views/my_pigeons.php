<?php
/**
 * 信鸽之家 - 我的铭鸽
 * 
 * 功能：当前用户发布的铭鸽列表管理
 * 设计：响应式（PC + 手机），继承index.php导航和页脚结构
 */

require_once dirname(__DIR__) . '/app/config/config.php';

// 检查登录
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect'] = '/user/pigeons';
    redirect('/login');
}

$pdo = get_db_connection();
$user_id = $_SESSION['user_id'];

// 分页参数
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$page_size = 12;
$offset = ($page - 1) * $page_size;

// 获取用户铭鸽总数
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM pigeons WHERE user_id = ? AND status IN (0, 1, 2)");
$stmt->execute([$user_id]);
$total = $stmt->fetch()['total'];

$pagination = paginate($total, $page, $page_size);

// 获取用户铭鸽列表
$stmt = $pdo->prepare("
    SELECT p.*, c.name as category_name 
    FROM pigeons p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.user_id = ? 
    ORDER BY p.created_at DESC 
    LIMIT ? OFFSET ?
");
$stmt->execute([$user_id, $pagination['page_size'], $pagination['offset']]);
$pigeons = $stmt->fetchAll();

// 统计数据
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM pigeons WHERE user_id = ? AND status = 1");
$stmt->execute([$user_id]);
$published_count = $stmt->fetch()['count'];

$stmt = $pdo->prepare("SELECT COALESCE(SUM(views), 0) as total_views FROM pigeons WHERE user_id = ?");
$stmt->execute([$user_id]);
$total_views = $stmt->fetch()['total_views'];

$stmt = $pdo->prepare("SELECT COALESCE(SUM(likes), 0) as total_likes FROM pigeons WHERE user_id = ?");
$stmt->execute([$user_id]);
$total_likes = $stmt->fetch()['total_likes'];

$page_title = '我的铭鸽 | ' . SITE_NAME;
$noindex = true;
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="keywords" content="我的铭鸽,铭鸽管理,信鸽之家">
    <meta property="og:title" content="我的铭鸽 - 信鸽之家">
    <meta property="og:description" content="管理您在信鸽之家发布的铭鸽信息，编辑详情、更新图片。">
    <meta property="og:image" content="https://www.xgjia.com/public/images/og-cover.png">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="信鸽之家">
    <link rel="canonical" href="https://www.xgjia.com/user/pigeons">

    <title><?php echo h($page_title); ?></title>
    <meta name="description" content="管理我发布的铭鸽信息">
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
            background: linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%);
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

        /* 铭鸽网格 */
        .pigeon-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        @media (max-width: 1200px) {
            .pigeon-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .pigeon-grid {
                grid-template-columns: 1fr;
            }
        }

        .pigeon-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            transition: all 0.3s;
            position: relative;
        }

        .pigeon-card:hover {
            box-shadow: var(--box-shadow-hover);
            transform: translateY(-5px);
        }

        .pigeon-card-image {
            width: 100%;
            height: 220px;
            object-fit: cover;
        }

        .pigeon-card-image-placeholder {
            width: 100%;
            height: 220px;
            background: var(--gray-100);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gray-300);
            font-size: 64px;
        }

        .pigeon-card-body {
            padding: 16px;
        }

        .pigeon-card-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 6px;
            color: var(--gray-900);
        }

        .pigeon-card-title a {
            color: var(--gray-900);
            text-decoration: none;
        }

        .pigeon-card-title a:hover {
            color: #7c3aed;
        }

        .pigeon-card-brief {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            font-size: 13px;
            color: var(--gray-500);
            margin-bottom: 10px;
        }

        .pigeon-card-brief span {
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .pigeon-card-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 10px;
            border-top: 1px solid var(--gray-100);
        }

        .pigeon-card-stats {
            display: flex;
            gap: 14px;
            font-size: 13px;
            color: var(--gray-400);
        }

        .pigeon-card-stats span {
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .pigeon-card-actions {
            display: flex;
            gap: 6px;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-size: 13px;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn-action-edit {
            background: #ede9fe;
            color: #7c3aed;
        }

        .btn-action-edit:hover {
            background: #7c3aed;
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

        .status-draft {
            background: #fef3c7;
            color: #92400e;
        }

        .status-offline {
            background: #fee2e2;
            color: #991b1b;
        }

        /* 标签 */
        .pigeon-tag {
            display: inline-block;
            padding: 2px 8px;
            background: #f3e8ff;
            color: #7c3aed;
            border-radius: 12px;
            font-size: 11px;
            margin-right: 4px;
            margin-bottom: 4px;
        }

        /* 空状态 */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: var(--gray-500);
            grid-column: 1 / -1;
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
            background-color: #7c3aed;
            color: white;
        }

        .pagination .active {
            background-color: #7c3aed;
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
                    <a href="/user/my_pigeons" class="active">
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
        <div class="main-content">
            <!-- 页面头部 -->
            <div class="page-header">
                <div class="container">
                    <h1><i class="fas fa-dove mr-2"></i>我的铭鸽</h1>
                    <p>管理我发布的铭鸽信息</p>
                </div>
            </div>
        <!-- 统计卡片 -->
        <div class="stats-bar">
            <div class="stat-card">
                <div class="stat-icon" style="background: #7c3aed;">
                    <i class="fas fa-dove"></i>
                </div>
                <div class="stat-value"><?php echo $published_count; ?></div>
                <div class="stat-label">已展示</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-green-500">
                    <i class="fas fa-eye"></i>
                </div>
                <div class="stat-value"><?php echo $total_views; ?></div>
                <div class="stat-label">总浏览</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-red-500">
                    <i class="fas fa-heart"></i>
                </div>
                <div class="stat-value"><?php echo $total_likes; ?></div>
                <div class="stat-label">总点赞</div>
            </div>
        </div>

        <!-- 操作栏 -->
        <div class="action-bar">
            <div class="total-info">
                <i class="fas fa-list mr-1"></i>共 <?php echo $total; ?> 只铭鸽
            </div>
            <a href="/pigeon/create" class="btn btn-primary" style="background: #7c3aed; border-color: #7c3aed;">
                <i class="fas fa-plus mr-1"></i>发布铭鸽
            </a>
        </div>

        <!-- 铭鸽列表 -->
        <?php if (!empty($pigeons)): ?>
        <div class="pigeon-grid">
            <?php foreach ($pigeons as $pigeon): ?>
            <?php
            $images = !empty($pigeon['images']) ? json_decode($pigeon['images'], true) : [];
            $cover_image = $images[0] ?? '';
            ?>
            <div class="pigeon-card">
                <?php if ($cover_image): ?>
                <img loading="lazy" src="<?php echo h($cover_image); ?>" alt="<?php echo h($pigeon['name']); ?>" class="pigeon-card-image">
                <?php else: ?>
                <div class="pigeon-card-image-placeholder">
                    <i class="fas fa-dove"></i>
                </div>
                <?php endif; ?>

                <div class="pigeon-card-body">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px; flex-wrap: wrap;">
                        <?php
                        $status_map = [
                            0 => ['label' => '草稿', 'class' => 'status-draft'],
                            1 => ['label' => '已展示', 'class' => 'status-published'],
                            2 => ['label' => '已下线', 'class' => 'status-offline'],
                        ];
                        $s = $status_map[$pigeon['status']] ?? ['label' => '未知', 'class' => 'status-draft'];
                        ?>
                        <span class="status-badge <?php echo $s['class']; ?>"><?php echo $s['label']; ?></span>
                    </div>

                    <h3 class="pigeon-card-title">
                        <a href="/pigeon/<?php echo $pigeon['id']; ?>.html">
                            <?php echo h($pigeon['name']); ?>
                        </a>
                    </h3>

                    <div class="pigeon-card-brief">
                        <?php if (!empty($pigeon['ring_number'])): ?>
                        <span><i class="fas fa-ring"></i> <?php echo h($pigeon['ring_number']); ?></span>
                        <?php endif; ?>
                        <?php if (!empty($pigeon['bloodline'])): ?>
                        <span><i class="fas fa-dna"></i> <?php echo h($pigeon['bloodline']); ?></span>
                        <?php endif; ?>
                        <?php if (!empty($pigeon['gender'])): ?>
                        <span><i class="fas fa-venus-mars"></i> <?php echo h($pigeon['gender']); ?></span>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($pigeon['category_name'])): ?>
                    <div style="margin-bottom: 8px;">
                        <span class="pigeon-tag"><i class="fas fa-folder mr-1"></i><?php echo h($pigeon['category_name']); ?></span>
                    </div>
                    <?php endif; ?>

                    <div class="pigeon-card-meta">
                        <div class="pigeon-card-stats">
                            <span><i class="fas fa-eye"></i> <?php echo $pigeon['views']; ?></span>
                            <span><i class="fas fa-heart"></i> <?php echo $pigeon['likes'] ?? 0; ?></span>
                            <span><i class="fas fa-clock"></i> <?php echo date('Y-m-d', strtotime($pigeon['created_at'])); ?></span>
                        </div>
                        <div class="pigeon-card-actions">
                            <a href="/pigeon_edit.php?id=<?php echo $pigeon['id']; ?>" class="btn-action btn-action-edit" title="编辑">
                                <i class="fas fa-pen"></i>
                            </a>
                            <button onclick="deletePigeon(<?php echo $pigeon['id']; ?>, '<?php echo h($pigeon['name']); ?>')" class="btn-action btn-action-delete" title="删除">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- 分页 -->
        <?php echo renderPagination($page, $pagination['total_pages']); ?>

        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-dove"></i>
            <p>您还没有发布过铭鸽</p>
            <a href="/pigeon/create" class="btn btn-primary" style="background: #7c3aed; border-color: #7c3aed;">
                <i class="fas fa-plus mr-1"></i>发布第一只铭鸽
            </a>
        </div>
        <?php endif; ?>
    </div>
    </div>

    <!-- 删除铭鸽 JavaScript -->
    <script>
        function deletePigeon(id, name) {
            if (confirm('确定要删除铭鸽《' + name + '》吗？此操作不可撤销。')) {
                var xhr = new XMLHttpRequest();
                xhr.open('POST', '/api/pigeon/delete', true);
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
    </script>

    <?php include __DIR__ . '/_footer.php'; ?>
