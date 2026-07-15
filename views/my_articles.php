<?php
/**
 * 信鸽之家 - 我的文章
 * 
 * 功能：当前用户发布的文章列表管理
 * 设计：响应式（PC + 手机），继承index.php导航和页脚结构
 */

require_once dirname(__DIR__) . '/app/config/config.php';

// 检查登录
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect'] = '/user/articles';
    redirect('/login');
}

$pdo = get_db_connection();
$user_id = $_SESSION['user_id'];

// 分页参数
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$page_size = 10;
$offset = ($page - 1) * $page_size;

// 获取用户文章总数
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM articles WHERE user_id = ? AND status IN (0, 1, 2)");
$stmt->execute([$user_id]);
$total = $stmt->fetch()['total'];

$pagination = paginate($total, $page, $page_size);

// 获取用户文章列表
$stmt = $pdo->prepare("
    SELECT a.*, c.name as category_name 
    FROM articles a 
    LEFT JOIN categories c ON a.category_id = c.id 
    WHERE a.user_id = ? 
    ORDER BY a.created_at DESC 
    LIMIT ? OFFSET ?
");
$stmt->execute([$user_id, $pagination['page_size'], $pagination['offset']]);
$articles = $stmt->fetchAll();

// 统计数据
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM articles WHERE user_id = ? AND status = 1");
$stmt->execute([$user_id]);
$published_count = $stmt->fetch()['count'];

$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM articles WHERE user_id = ? AND status = 0");
$stmt->execute([$user_id]);
$draft_count = $stmt->fetch()['count'];

$stmt = $pdo->prepare("SELECT COALESCE(SUM(views), 0) as total_views FROM articles WHERE user_id = ?");
$stmt->execute([$user_id]);
$total_views = $stmt->fetch()['total_views'];

$page_title = '我的文章 | ' . SITE_NAME;
$noindex = true;
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="keywords" content="我的文章,信鸽之家">
    <meta property="og:title" content="我的文章 - 信鸽之家">
    <meta property="og:description" content="管理您在信鸽之家发布的文章和资讯内容。">
    <meta property="og:image" content="https://www.xgjia.com/public/images/og-cover.png">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="信鸽之家">
    <link rel="canonical" href="https://www.xgjia.com/user/articles">

    <title><?php echo h($page_title); ?></title>
    <meta name="description" content="管理我发布的文章">
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
            background: linear-gradient(135deg, var(--primary) 0%, #1e40af 100%);
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

        /* 文章列表 */
        .my-article-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .my-article-item {
            display: flex;
            gap: 20px;
            padding: 20px;
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            transition: all 0.3s;
            position: relative;
        }

        .my-article-item:hover {
            box-shadow: var(--box-shadow-hover);
            transform: translateY(-2px);
        }

        .my-article-thumb {
            width: 180px;
            height: 130px;
            object-fit: cover;
            border-radius: var(--border-radius);
            flex-shrink: 0;
        }

        .my-article-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .my-article-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 8px;
            color: var(--gray-900);
        }

        .my-article-title a {
            color: var(--gray-900);
            text-decoration: none;
        }

        .my-article-title a:hover {
            color: var(--primary);
        }

        .my-article-summary {
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

        .my-article-meta {
            display: flex;
            gap: 16px;
            font-size: 13px;
            color: var(--gray-400);
            flex-wrap: wrap;
            margin-top: auto;
        }

        .my-article-meta span {
            display: inline-flex;
            align-items: center;
            gap: 4px;
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

        /* 操作按钮 */
        .my-article-actions {
            display: flex;
            gap: 8px;
            align-items: center;
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
            background: #dbeafe;
            color: #1e40af;
        }

        .btn-action-edit:hover {
            background: #1e40af;
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

        @media (max-width: 768px) {
            .my-article-item {
                flex-direction: column;
            }

            .my-article-thumb {
                width: 100%;
                height: 180px;
            }

            .my-article-actions {
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
            background-color: var(--primary);
            color: white;
        }

        .pagination .active {
            background-color: var(--primary);
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
                    <a href="/user/my_articles" class="active">
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
                    <h1><i class="fas fa-newspaper mr-2"></i>我的文章</h1>
                    <p>管理我发布的资讯文章</p>
                </div>
            </div>
        <!-- 统计卡片 -->
        <div class="stats-bar">
            <div class="stat-card">
                <div class="stat-icon bg-blue-500">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-value"><?php echo $published_count; ?></div>
                <div class="stat-label">已发布</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-yellow-500">
                    <i class="fas fa-edit"></i>
                </div>
                <div class="stat-value"><?php echo $draft_count; ?></div>
                <div class="stat-label">草稿</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-green-500">
                    <i class="fas fa-eye"></i>
                </div>
                <div class="stat-value"><?php echo $total_views; ?></div>
                <div class="stat-label">总浏览</div>
            </div>
        </div>

        <!-- 操作栏 -->
        <div class="action-bar">
            <div class="total-info">
                <i class="fas fa-list mr-1"></i>共 <?php echo $total; ?> 篇文章
            </div>
            <a href="/article/create" class="btn btn-primary">
                <i class="fas fa-plus mr-1"></i>发布文章
            </a>
        </div>

        <!-- 文章列表 -->
        <?php if (!empty($articles)): ?>
        <div class="my-article-list">
            <?php foreach ($articles as $article): ?>
            <div class="my-article-item">
                <?php if (!empty($article['cover'])): ?>
                <img loading="lazy" src="<?php echo h($article['cover']); ?>" alt="<?php echo h($article['title']); ?>" class="my-article-thumb">
                <?php else: ?>
                <div class="my-article-thumb bg-gray-100 flex items-center justify-center" style="color: var(--gray-300);">
                    <i class="fas fa-newspaper" style="font-size: 40px;"></i>
                </div>
                <?php endif; ?>
                
                <div class="my-article-content">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px; flex-wrap: wrap;">
                        <?php
                        $status_map = [
                            0 => ['label' => '草稿', 'class' => 'status-draft'],
                            1 => ['label' => '已发布', 'class' => 'status-published'],
                            2 => ['label' => '已下线', 'class' => 'status-offline'],
                        ];
                        $s = $status_map[$article['status']] ?? ['label' => '未知', 'class' => 'status-draft'];
                        ?>
                        <span class="status-badge <?php echo $s['class']; ?>"><?php echo $s['label']; ?></span>
                        <?php if (!empty($article['category_name'])): ?>
                        <span style="font-size: 12px; color: var(--gray-400);"><i class="fas fa-folder mr-1"></i><?php echo h($article['category_name']); ?></span>
                        <?php endif; ?>
                    </div>

                    <h3 class="my-article-title">
                        <a href="/article/<?php echo $article['id']; ?>.html">
                            <?php echo h($article['title']); ?>
                        </a>
                    </h3>

                    <?php if (!empty($article['summary'])): ?>
                    <p class="my-article-summary"><?php echo h($article['summary']); ?></p>
                    <?php endif; ?>
                    
                    <div class="my-article-meta">
                        <span><i class="fas fa-clock"></i> <?php echo date('Y-m-d H:i', strtotime($article['created_at'])); ?></span>
                        <span><i class="fas fa-eye"></i> <?php echo $article['views']; ?></span>
                        <span><i class="fas fa-comment"></i> <?php echo $article['comments'] ?? 0; ?></span>
                        <span><i class="fas fa-heart"></i> <?php echo $article['likes'] ?? 0; ?></span>
                    </div>
                </div>

                <div class="my-article-actions">
                    <a href="/article_edit.php?id=<?php echo $article['id']; ?>" class="btn-action btn-action-edit" title="编辑">
                        <i class="fas fa-pen"></i>
                    </a>
                    <button onclick="deleteArticle(<?php echo $article['id']; ?>, '<?php echo h($article['title']); ?>')" class="btn-action btn-action-delete" title="删除">
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
            <i class="fas fa-newspaper"></i>
            <p>您还没有发布过文章</p>
            <a href="/article/create" class="btn btn-primary">
                <i class="fas fa-plus mr-1"></i>发布第一篇文章
            </a>
        </div>
        <?php endif; ?>
    </div>
    </div>

    <!-- 删除文章 JavaScript -->
    <script>
        function deleteArticle(id, title) {
            if (confirm('确定要删除文章《' + title + '》吗？此操作不可撤销。')) {
                var xhr = new XMLHttpRequest();
                xhr.open('POST', '/api/article/delete', true);
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
