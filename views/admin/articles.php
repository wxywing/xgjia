<?php extract($data); $activeMenu = 'articles'; ?>
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
        <!-- 筛选栏 -->
        <div class="filter-bar">
            <a href="/admin.php?action=articles" class="<?php echo !isset($currentStatus) ? 'active' : ''; ?>">全部</a>
            <a href="/admin.php?action=articles&status=1" class="<?php echo ($currentStatus ?? '') === 1 ? 'active' : ''; ?>">已发布</a>
            <a href="/admin.php?action=articles&status=0" class="<?php echo ($currentStatus ?? '') === 0 ? 'active' : ''; ?>">草稿</a>
        </div>

        <!-- 数据表格 -->
        <div class="content-card">
            <div class="card-header">
                <h3>文章列表 (共 <?php echo h($total); ?> 篇)</h3>
            </div>
            <div class="card-body">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>标题</th>
                            <th>作者</th>
                            <th>分类</th>
                            <th>状态</th>
                            <th>浏览量</th>
                            <th>发布时间</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($articles)): ?>
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <i class="fas fa-newspaper"></i>
                                    <p>暂无文章数据</p>
                                </div>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($articles as $article): ?>
                        <tr>
                            <td><?php echo h($article['id']); ?></td>
                            <td><?php echo h($article['title'] ?? ''); ?></td>
                            <td><?php echo h($article['author_name'] ?? '-'); ?></td>
                            <td><?php echo h($article['category_name'] ?? '-'); ?></td>
                            <td>
                                <?php if (($article['status'] ?? 0) == 1): ?>
                                <span class="badge badge-success">已发布</span>
                                <?php else: ?>
                                <span class="badge badge-secondary">草稿</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo number_format($article['views'] ?? 0); ?></td>
                            <td style="font-size:12px;color:#9ca3af;"><?php echo date('m-d H:i', strtotime($article['created_at'] ?? 'now')); ?></td>
                            <td class="actions">
                                <a href="/article/<?php echo h($article['id']); ?>.html" target="_blank" class="btn btn-sm btn-outline"><i class="fas fa-eye"></i> 查看</a>
                                <button onclick="openEditArticleModal(<?php echo $article['id']; ?>)" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i> 编辑</button>
                                <button onclick="toggleStatus('article', <?php echo h($article['id']); ?>, <?php echo h($article['status'] ?? 0); ?>)" class="btn btn-sm <?php echo ($article['status'] ?? 0) == 1 ? 'btn-warning' : 'btn-success'; ?>">
                                    <i class="fas fa-toggle-<?php echo ($article['status'] ?? 0) == 1 ? 'on' : 'off'; ?>"></i>
                                </button>
                                <button onclick="confirmDelete('article', <?php echo h($article['id']); ?>, '<?php echo h($article['title'] ?? ''); ?>')" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>

                <!-- 分页 -->
                <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php
                    $range = 2;
                    $start_p = max(1, $page - $range);
                    $end_p = min($totalPages, $page + $range);

                    if ($page > 1): ?>
                    <a href="?action=articles&page=<?php echo $page-1; ?><?php echo isset($currentStatus) ? '&status=' . intval($currentStatus) : ''; ?>">上一页</a>
                    <?php endif;

                    if ($start_p > 1): ?>
                    <a href="?action=articles&page=1<?php echo isset($currentStatus) ? '&status=' . intval($currentStatus) : ''; ?>">1</a>
                    <?php if ($start_p > 2): ?><span class="ellipsis" style="padding:8px 6px;color:#9ca3af;">...</span><?php endif; ?>
                    <?php endif;

                    for ($i = $start_p; $i <= $end_p; $i++): ?>
                    <?php if ($i == $page): ?>
                    <span class="active"><?php echo $i; ?></span>
                    <?php else: ?>
                    <a href="?action=articles&page=<?php echo $i; ?><?php echo isset($currentStatus) ? '&status=' . intval($currentStatus) : ''; ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                    <?php endfor;

                    if ($end_p < $totalPages): ?>
                    <?php if ($end_p < $totalPages - 1): ?><span class="ellipsis" style="padding:8px 6px;color:#9ca3af;">...</span><?php endif; ?>
                    <a href="?action=articles&page=<?php echo $totalPages; ?><?php echo isset($currentStatus) ? '&status=' . intval($currentStatus) : ''; ?>"><?php echo $totalPages; ?></a>
                    <?php endif;

                    if ($page < $totalPages): ?>
                    <a href="?action=articles&page=<?php echo $page+1; ?><?php echo isset($currentStatus) ? '&status=' . intval($currentStatus) : ''; ?>">下一页</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<!-- 编辑文章模态框 -->
<div class="modal-overlay" id="editArticleModal">
    <div class="modal" style="width:900px;max-width:95vw;max-height:90vh;">
        <div class="modal-header">
            <h3>编辑文章</h3>
            <button class="modal-close" onclick="closeModal('editArticleModal')">&times;</button>
        </div>
        <div class="modal-body" style="overflow-y:auto;max-height:calc(90vh - 120px);">
            <form id="editArticleForm">
                <input type="hidden" name="id" id="editArticleId">
                <div class="form-group">
                    <label class="form-label">标题</label>
                    <input type="text" name="title" id="editArticleTitle" class="form-control" required>
                </div>
                <div class="form-row" style="display:flex;gap:12px;">
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">栏目</label>
                        <select name="category_id" id="editArticleCategoryId" class="form-control">
                            <option value="">请选择栏目</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo h($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">状态</label>
                        <select name="status" id="editArticleStatus" class="form-control">
                            <option value="1">已发布</option>
                            <option value="0">草稿</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">内容</label>
                    <textarea id="editArticleEditor" style="background:#fff;min-height:500px;"></textarea>
                    <input type="hidden" name="content" id="editArticleContent">
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('editArticleModal')">取消</button>
            <button class="btn btn-primary" onclick="submitEditArticle()">保存</button>
        </div>
    </div>
</div>

<?php include __DIR__ . '/_scripts.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/tinymce@6.8.4/tinymce.min.js"></script>
<script>
// 文章列表元数据 + 内容映射（直接内嵌，不走 AJAX）
var articleListData = <?php
$listData = array_map(function($a) {
    return [
        'id' => $a['id'],
        'title' => $a['title'],
        'category_id' => $a['category_id'] ?? null,
        'status' => $a['status'] ?? 1,
        'author_name' => $a['author_name'] ?? '',
        'views' => $a['views'] ?? 0,
    ];
}, $articles ?? []);
echo json_encode($listData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
?>;

var articleContentMap = {
<?php foreach ($articles as $a): ?>
    <?php echo intval($a['id']); ?>: <?php echo json_encode($a['content'] ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE); ?>,
<?php endforeach; ?>
};

var currentArticleId = null;

function initEditor(content) {
    tinymce.init({
        selector: '#editArticleEditor',
        menubar: false,
        plugins: 'table lists link',
        toolbar: 'undo redo | blocks | bold italic underline strikethrough | bullist numlist | table | link',
        valid_elements: '*[*]',          // 放行所有标签和属性，保真原始 HTML
        valid_children: '+body[style]',  // 允许 <style> 在 <body> 内
        extended_valid_elements: '*[*]', // 额外保真
        convert_urls: false,              // 不重写 URL
        remove_script_host: false,
        branding: false,
        placeholder: '请输入文章内容...',
        setup: function(editor) {
            if (content) {
                editor.on('init', function() {
                    editor.setContent(content);
                });
            }
        }
    });
}

function openEditArticleModal(id) {
    currentArticleId = id;
    var article = articleListData.find(function(a) { return a.id == id; });
    if (!article) { showToast('文章数据异常', 'error'); return; }

    document.getElementById('editArticleId').value = article.id;
    document.getElementById('editArticleTitle').value = article.title || '';
    document.getElementById('editArticleCategoryId').value = article.category_id || '';
    document.getElementById('editArticleStatus').value = article.status ?? 1;

    openModal('editArticleModal');

    var content = articleContentMap[id] || '';

    // 销毁旧编辑器
    if (tinymce.activeEditor) {
        tinymce.activeEditor.destroy();
    }
    document.getElementById('editArticleEditor').innerHTML = '';
    initEditor(content);
}

function submitEditArticle() {
    if (tinymce.activeEditor) {
        document.getElementById('editArticleContent').value = tinymce.activeEditor.getContent();
    }
    var formData = new FormData(document.getElementById('editArticleForm'));
    fetch('/admin.php?action=edit-article', { method: 'POST', body: formData })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) { showToast(data.message, 'success'); setTimeout(function() { location.reload(); }, 1000); }
        else { showToast(data.message, 'error'); }
    })
    .catch(function() { showToast('操作失败', 'error'); });
}
</script>
</body>
</html>