<?php
/**
 * 信鸽之家 - 鸽友圈（B方案设计·丰富版）
 */

require_once dirname(__DIR__) . '/app/config/config.php';

// $data 由 Controller::loadView() 提取
extract($data);

$page_title = $pageTitle ?? '鸽友圈' . (($page ?? 1) > 1 ? ' - 第' . intval($page) . '页' : '') . ' - 鸽友交流社区 | ' . SITE_NAME;

// SEO 元信息
$total = $total ?? 0;
$meta_description = "信鸽鸽友圈，{$total}条动态分享，鸽友交流养鸽经验、赛事心得。加入信鸽之家鸽友圈，和全国鸽友互动。";
$meta_keywords = '鸽友圈,鸽友交流,信鸽社区,' . SITE_KEYWORDS;
$canonical_url = 'https://www.xgjia.com/dynamic/' . ($_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '');

// JSON-LD - ItemList
$ld_items = [];
foreach (array_slice($dynamics ?? [], 0, 10) as $i => $d) {
    $ld_items[] = [
        '@type' => 'ListItem',
        'position' => $i + 1,
        'url' => 'https://www.xgjia.com/dynamic/' . ($d['id'] ?? ''),
    ];
}
$ld_itemlist = [
    '@context' => 'https://schema.org',
    '@type' => 'ItemList',
    'name' => '鸽友圈动态',
    'numberOfItems' => $total ?? 0,
    'itemListElement' => $ld_items,
];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo h($page_title); ?></title>
    
    <!-- SEO Meta -->
    <meta name="description" content="<?php echo h($meta_description); ?>">
    <meta name="keywords" content="<?php echo h($meta_keywords); ?>">
    <link rel="canonical" href="<?php echo h($canonical_url); ?>">
    
    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?php echo h($page_title); ?>">
    <meta property="og:description" content="<?php echo h($meta_description); ?>">
    <meta property="og:image" content="https://www.xgjia.com/public/images/og-cover.png">
    <meta property="og:url" content="<?php echo h($canonical_url); ?>">
    <meta property="og:site_name" content="<?php echo SITE_NAME; ?>">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary">
    <script type="application/ld+json"><?php echo json_encode($ld_itemlist, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>
    <meta name="twitter:title" content="<?php echo h($page_title); ?>">
    <meta name="twitter:description" content="<?php echo h($meta_description); ?>">
    
    <!-- CSS -->
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/public/css/b-scheme.css">
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="/public/images/favicon.ico">
</head>
<body>
    <?php include __DIR__ . '/_head.php'; ?>

<!-- page-dynamics wrapper -->
<div class="page-dynamics">
    
    <!-- 页面标题 -->
    <div class="page-header">
        <div class="container">
            <h1><i class="fas fa-users"></i> 鸽友圈</h1>
            <p>分享养鸽心得，交流比赛经验</p>
        </div>
    </div>
    
    <!-- 主内容区 -->
    <div class="dynamics-layout">
        <!-- 左侧主内容 -->
        <div class="dynamics-main">
            <?php if (isset($_SESSION['user_id'])): ?>
            <!-- 发布框 -->
            <div class="publish-box">
                <textarea id="dynamicContent" placeholder="分享你的养鸽心得..."></textarea>
                
                <div class="publish-actions">
                    <div class="image-upload-btn">
                        <i class="fas fa-image"></i>
                        <span>上传图片</span>
                    </div>
                    
                    <button class="btn btn-primary" onclick="publishDynamic()">
                        <i class="fas fa-paper-plane"></i> 发布
                    </button>
                </div>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <p>登录后可以发布动态</p>
                <a href="/login" class="btn btn-primary">登录</a>
            </div>
            <?php endif; ?>
            
            <!-- 动态列表 -->
            <?php if (!empty($dynamics)): ?>
            <div class="dynamics-list">
                <?php foreach ($dynamics as $dynamic): ?>
                <?php
                $images = json_decode($dynamic['images'] ?? '[]', true) ?: [];
                $imageCount = count($images);
                $imageClass = $imageCount === 1 ? 'single' : ($imageCount === 2 ? 'double' : 'multiple');
                ?>
                <div class="dynamic-card" data-id="<?php echo $dynamic['id']; ?>">
                    <div class="dynamic-header">
                        <?php if (!empty($dynamic['avatar'])): ?>
                        <img loading="lazy" src="<?php echo h($dynamic['avatar']); ?>" alt="<?php echo h($dynamic['username'] ?? '用户'); ?>" 
                             alt="<?php echo h($dynamic['nickname'] ?? $dynamic['username']); ?>" 
                             class="user-avatar">
                        <?php else: ?>
                        <div class="user-avatar-placeholder">
                            <i class="fas fa-user"></i>
                        </div>
                        <?php endif; ?>
                        
                        <div class="user-info">
                            <div class="user-name">
                                <?php echo h($dynamic['nickname'] ?? $dynamic['username']); ?>
                            </div>
                            <div class="post-time">
                                <?php 
                                $time = strtotime($dynamic['created_at']);
                                $diff = time() - $time;
                                
                                if ($diff < 60) {
                                    echo '刚刚';
                                } elseif ($diff < 3600) {
                                    echo floor($diff / 60) . '分钟前';
                                } elseif ($diff < 86400) {
                                    echo floor($diff / 3600) . '小时前';
                                } elseif ($diff < 604800) {
                                    echo floor($diff / 86400) . '天前';
                                } else {
                                    echo date('Y-m-d', $time);
                                }
                                ?>
                            </div>
                        </div>
                        
                        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $dynamic['user_id']): ?>
                        <div class="dynamic-actions">
                            <button class="action-btn-sm" onclick="openEditModal(<?php echo $dynamic['id']; ?>)" title="编辑">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="action-btn-sm text-danger" onclick="deleteDynamic(<?php echo $dynamic['id']; ?>)" title="删除">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="dynamic-body">
                        <div class="dynamic-content"><?php echo $dynamic['content']; ?></div>
                        
                        <?php if (!empty($images)): ?>
                        <div class="dynamic-images <?php echo $imageClass; ?>">
                            <?php foreach ($images as $image): ?>
                            <img src="<?php echo h($image); ?>" alt="动态图片" loading="lazy" 
                                 alt="动态图片" 
                                 class="dynamic-image"
                                 onclick="viewImage(this.src)">
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="dynamic-footer">
                        <button class="action-btn" onclick="likeDynamic(<?php echo $dynamic['id']; ?>)">
                            <i class="far fa-heart"></i>
                            <span><?php echo $dynamic['likes'] ?? 0; ?></span>
                        </button>
                        
                        <button class="action-btn" onclick="showComments(<?php echo $dynamic['id']; ?>)">
                            <i class="far fa-comment"></i>
                            <span><?php echo $dynamic['comments'] ?? 0; ?></span>
                        </button>
                        
                        <button class="action-btn" onclick="shareDynamic(<?php echo $dynamic['id']; ?>)">
                            <i class="fas fa-share"></i>
                            <span>分享</span>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- 分页 -->
            <?php echo renderPagination($page, $totalPages); ?>
            
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-users"></i>
                <p>暂无动态</p>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <button class="btn btn-primary" onclick="document.getElementById('dynamicContent').focus()">
                        发布第一条动态
                    </button>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- 右侧边栏 -->
        <div class="dynamics-sidebar">
            <?php if (isset($_SESSION['user_id'])): ?>
            <!-- 用户统计 -->
            <div class="sidebar-card">
                <div class="sidebar-title">我的统计</div>
                <div class="stats-grid">
                    <div class="stats-item">
                        <div class="stats-number"><?php echo $userStats['dynamics_count'] ?? 0; ?></div>
                        <div class="stats-label">发布动态</div>
                    </div>
                    <div class="stats-item">
                        <div class="stats-number"><?php echo $userStats['likes_received'] ?? 0; ?></div>
                        <div class="stats-label">获得点赞</div>
                    </div>
                    <div class="stats-item">
                        <div class="stats-number"><?php echo $userStats['comments_received'] ?? 0; ?></div>
                        <div class="stats-label">获得评论</div>
                    </div>
                    <div class="stats-item">
                        <div class="stats-number"><?php echo $userStats['followers_count'] ?? 0; ?></div>
                        <div class="stats-label">粉丝数</div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- 热门话题 -->
            <div class="sidebar-card">
                <div class="sidebar-title">热门话题</div>
                <?php if (!empty($hotTopics)): ?>
                    <?php foreach ($hotTopics as $index => $topic): ?>
                    <div class="hot-topic-item">
                        <div class="hot-topic-rank <?php echo $index < 3 ? 'top3' : ''; ?>"><?php echo $index + 1; ?></div>
                        <div class="hot-topic-content">
                            <div class="hot-topic-title"><?php echo h($topic['title']); ?></div>
                            <div class="hot-topic-heat"><?php echo $topic['heat']; ?> 热度</div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color: var(--text-light); font-size: 14px;">暂无热门话题</p>
                <?php endif; ?>
            </div>
            
            <!-- 推荐用户 -->
            <div class="sidebar-card">
                <div class="sidebar-title">推荐关注</div>
                <?php if (!empty($recommendedUsers)): ?>
                    <?php foreach ($recommendedUsers as $user): ?>
                    <div class="recommended-user-item">
                        <?php if (!empty($user['avatar'])): ?>
                        <img loading="lazy" src="<?php echo h($user['avatar']); ?>" alt="<?php echo h($user['username'] ?? '用户'); ?>" 
                             alt="<?php echo h($user['nickname'] ?? $user['username']); ?>" 
                             class="recommended-user-avatar">
                        <?php else: ?>
                        <div class="recommended-user-avatar-placeholder">
                            <i class="fas fa-user"></i>
                        </div>
                        <?php endif; ?>
                        <div class="recommended-user-info">
                            <div class="recommended-user-name"><?php echo h($user['nickname'] ?? $user['username']); ?></div>
                            <div class="recommended-user-desc"><?php echo h($user['bio'] ?? '这位鸽友很懒，还没有简介'); ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color: var(--text-light); font-size: 14px;">暂无推荐用户</p>
                <?php endif; ?>
            </div>
            
            <!-- 热门标签 -->
            <div class="sidebar-card">
                <div class="sidebar-title">热门标签</div>
                <?php if (!empty($trendingTags)): ?>
                    <?php foreach ($trendingTags as $tag): ?>
                        <span class="trending-tag-item"><?php echo h($tag); ?></span>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color: var(--text-light); font-size: 14px;">暂无热门标签</p>
                <?php endif; ?>
            </div>
            
            <!-- 社区统计 -->
            <div class="sidebar-card">
                <div class="sidebar-title">社区数据</div>
                <div class="stats-grid">
                    <div class="stats-item">
                        <div class="stats-number"><?php echo number_format($total); ?></div>
                        <div class="stats-label">总动态数</div>
                    </div>
                    <div class="stats-item">
                        <div class="stats-number"><?php echo number_format($totalUsers ?? 0); ?></div>
                        <div class="stats-label">注册用户</div>
                    </div>
                    <div class="stats-item">
                        <div class="stats-number"><?php echo number_format($todayDynamics ?? 0); ?></div>
                        <div class="stats-label">今日新增</div>
                    </div>
                    <div class="stats-item">
                        <div class="stats-number"><?php echo number_format($onlineUsers ?? 0); ?></div>
                        <div class="stats-label">在线用户</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 发布动态 JavaScript -->
    <script>
        function publishDynamic() {
            var content = document.getElementById('dynamicContent').value.trim();
            if (!content) {
                alert('请输入动态内容');
                return;
            }
            
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = '/dynamic/create';
            
            var input = document.createElement('input');
            input.name = 'content';
            input.value = content;
            form.appendChild(input);
            
            document.body.appendChild(form);
            form.submit();
        }
        
        function likeDynamic(id) {
            // TODO: Implement like functionality
            console.log('Like dynamic:', id);
        }
        
        function showComments(id) {
            // TODO: Implement show comments functionality
            console.log('Show comments for dynamic:', id);
        }
        
        function shareDynamic(id) {
            var url = window.location.origin + '/dynamics/' + id + '.html';
            var text = '快来看看这条鸽友圈动态';

            // 优先使用原生分享（移动端）
            if (navigator.share) {
                navigator.share({ title: '信鸽之家鸽友圈', text: text, url: url }).catch(function() {});
                return;
            }

            // Fallback: 复制链接到剪贴板
            copyToClipboard(url);
        }

        function copyToClipboard(text) {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(function() { showShareTip('链接已复制，快去分享吧！'); }, function() { fallbackCopy(text); });
            } else {
                fallbackCopy(text);
            }
        }

        function fallbackCopy(text) {
            var ta = document.createElement('textarea');
            ta.value = text;
            ta.style.cssText = 'position:fixed;top:-9999px;left:-9999px';
            document.body.appendChild(ta);
            ta.select();
            try { document.execCommand('copy'); showShareTip('链接已复制，快去分享吧！'); } catch(e) { alert('复制失败，请手动复制链接：' + text); }
            document.body.removeChild(ta);
        }

        var shareTimer;
        function showShareTip(msg) {
            var tip = document.getElementById('share-tip');
            if (!tip) {
                tip = document.createElement('div');
                tip.id = 'share-tip';
                tip.style.cssText = 'position:fixed;bottom:80px;left:50%;transform:translateX(-50%);background:#1e3a8a;color:#fff;padding:10px 24px;border-radius:24px;font-size:14px;z-index:9999;white-space:nowrap;box-shadow:0 4px 12px rgba(0,0,0,.15);transition:opacity .3s';
                document.body.appendChild(tip);
            }
            tip.textContent = msg;
            tip.style.opacity = '1';
            clearTimeout(shareTimer);
            shareTimer = setTimeout(function() { tip.style.opacity = '0'; }, 2000);
        }
        
        function viewImage(src) {
            // TODO: Implement image viewer
            window.open(src, '_blank');
        }
        
        function openEditModal(id) {
            // TODO: Implement edit modal
            console.log('Edit dynamic:', id);
        }
        
        function deleteDynamic(id) {
            if (!confirm('确定要删除这条动态吗？')) {
                return;
            }
            
            // TODO: Implement delete functionality
            console.log('Delete dynamic:', id);
        }
    </script>
    
    
</div><!-- /page-dynamics -->

<?php include __DIR__ . '/_footer.php'; ?>
</body>
</html>