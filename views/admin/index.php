<?php
/**
 * 管理员后台 - 仪表盘
 */
extract($data);
$activeMenu = 'dashboard';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo h($pageTitle); ?></title>
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php include __DIR__ . '/_styles.php'; ?>
</head>
<body>
<div class="admin-container">
    <?php include __DIR__ . '/_sidebar.php'; ?>
    <main class="main-content">
<?php include __DIR__ . '/_header.php'; ?>
        <!-- 统计卡片 -->
        <div class="stats-grid">
            <div class="stat-card users">
                <div class="icon"><i class="fas fa-users"></i></div>
                <div class="value"><?php echo number_format($stats['total_users']); ?></div>
                <div class="label">总用户数</div>
            </div>
            <div class="stat-card articles">
                <div class="icon"><i class="fas fa-newspaper"></i></div>
                <div class="value"><?php echo number_format($stats['total_articles']); ?></div>
                <div class="label">文章总数</div>
            </div>
            <div class="stat-card pigeons">
                <div class="icon"><i class="fas fa-dove"></i></div>
                <div class="value"><?php echo number_format($stats['total_pigeons']); ?></div>
                <div class="label">铭鸽总数</div>
            </div>
            <div class="stat-card listings">
                <div class="icon"><i class="fas fa-list-alt"></i></div>
                <div class="value"><?php echo number_format($stats['total_listings']); ?></div>
                <div class="label">分类信息</div>
            </div>
            <div class="stat-card races">
                <div class="icon"><i class="fas fa-calendar-alt"></i></div>
                <div class="value"><?php echo number_format($stats['total_races']); ?></div>
                <div class="label">赛事总数</div>
            </div>
            <div class="stat-card dynamics">
                <div class="icon"><i class="fas fa-comments"></i></div>
                <div class="value"><?php echo number_format($stats['total_dynamics']); ?></div>
                <div class="label">动态总数</div>
            </div>
            <div class="stat-card" style="background:linear-gradient(135deg,#8e44ad,#9b59b6);">
                <div class="icon"><i class="fas fa-building"></i></div>
                <div class="value"><?php echo number_format($stats['total_lofts']); ?></div>
                <div class="label">公棚总数</div>
            </div>
        </div>
        
        <!-- 内容区块 -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(400px,1fr));gap:20px;">
            <!-- 最近用户 -->
            <div class="content-card">
                <div class="card-header">
                    <h3><i class="fas fa-users" style="margin-right:8px;color:#3b82f6;"></i>最近注册用户</h3>
                    <a href="/admin.php?action=users">查看全部 →</a>
                </div>
                <div class="card-body">
                    <ul class="data-list">
                        <?php if (!empty($recentUsers)): ?>
                            <?php foreach ($recentUsers as $user): ?>
                            <li>
                                <div class="item-info">
                                    <div class="item-title"><?php echo h($user['username']); ?></div>
                                    <div class="item-meta">
                                        <?php if (($user['member_level'] ?? 0) >= 1): ?>
                                        <span class="badge badge-warning">VIP</span>
                                        <?php endif; ?>
                                        <?php echo h($user['email']); ?>
                                    </div>
                                </div>
                                <div style="color:#6b7280;font-size:13px;"><?php echo date('m-d H:i', strtotime($user['created_at'])); ?></div>
                            </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li style="text-align:center;padding:40px;color:#9ca3af;">暂无数据</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            
            <!-- 最近文章 -->
            <div class="content-card">
                <div class="card-header">
                    <h3><i class="fas fa-newspaper" style="margin-right:8px;color:#10b981;"></i>最近发布文章</h3>
                    <a href="/admin.php?action=articles">查看全部 →</a>
                </div>
                <div class="card-body">
                    <ul class="data-list">
                        <?php if (!empty($recentArticles)): ?>
                            <?php foreach ($recentArticles as $article): ?>
                            <li>
                                <div class="item-info">
                                    <div class="item-title"><?php echo h($article['title']); ?></div>
                                    <div class="item-meta">
                                        <?php echo h($article['author_name'] ?? '管理员'); ?> · 
                                        <?php echo number_format($article['views']); ?> 阅读
                                    </div>
                                </div>
                                <div style="color:#6b7280;font-size:13px;"><?php echo date('m-d H:i', strtotime($article['created_at'])); ?></div>
                            </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li style="text-align:center;padding:40px;color:#9ca3af;">暂无数据</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- 快捷操作 -->
        <div class="content-card" style="margin-top:20px;">
            <div class="card-header">
                <h3><i class="fas fa-bolt" style="margin-right:8px;color:#f59e0b;"></i>快捷操作</h3>
            </div>
            <div style="padding:20px;">
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:15px;">
                    <a href="/admin.php?action=users" style="display:flex;flex-direction:column;align-items:center;padding:20px;background:#f9fafb;border-radius:8px;text-decoration:none;color:#1f2937;transition:.3s;">
                        <i class="fas fa-user-plus" style="font-size:28px;color:#3b82f6;margin-bottom:8px;"></i>
                        <span style="font-size:13px;">用户管理</span>
                    </a>
                    <a href="/admin.php?action=articles" style="display:flex;flex-direction:column;align-items:center;padding:20px;background:#f9fafb;border-radius:8px;text-decoration:none;color:#1f2937;transition:.3s;">
                        <i class="fas fa-file-alt" style="font-size:28px;color:#10b981;margin-bottom:8px;"></i>
                        <span style="font-size:13px;">文章管理</span>
                    </a>
                    <a href="/admin.php?action=pigeons" style="display:flex;flex-direction:column;align-items:center;padding:20px;background:#f9fafb;border-radius:8px;text-decoration:none;color:#1f2937;transition:.3s;">
                        <i class="fas fa-dove" style="font-size:28px;color:#f59e0b;margin-bottom:8px;"></i>
                        <span style="font-size:13px;">铭鸽管理</span>
                    </a>
                    <a href="/admin.php?action=races" style="display:flex;flex-direction:column;align-items:center;padding:20px;background:#f9fafb;border-radius:8px;text-decoration:none;color:#1f2937;transition:.3s;">
                        <i class="fas fa-calendar-plus" style="font-size:28px;color:#6366f1;margin-bottom:8px;"></i>
                        <span style="font-size:13px;">赛事管理</span>
                    </a>
                    <a href="/admin.php?action=ads" style="display:flex;flex-direction:column;align-items:center;padding:20px;background:#f9fafb;border-radius:8px;text-decoration:none;color:#1f2937;transition:.3s;">
                        <i class="fas fa-ad" style="font-size:28px;color:#ec4899;margin-bottom:8px;"></i>
                        <span style="font-size:13px;">广告管理</span>
                    </a>
                    <a href="/admin.php?action=settings" style="display:flex;flex-direction:column;align-items:center;padding:20px;background:#f9fafb;border-radius:8px;text-decoration:none;color:#1f2937;transition:.3s;">
                        <i class="fas fa-cog" style="font-size:28px;color:#6b7280;margin-bottom:8px;"></i>
                        <span style="font-size:13px;">系统设置</span>
                    </a>
                </div>
            </div>
        </div>
    </main>
</div>
<?php include __DIR__ . '/_scripts.php'; ?>
</body>
</html>
