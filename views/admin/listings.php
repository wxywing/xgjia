<?php extract($data); $activeMenu = 'listings';
$typeLabels = [1 => '鸽舍转让', 2 => '配对信息', 3 => '求购/转让'];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo h($pageTitle); ?></title>
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <?php include __DIR__ . '/_styles.php'; ?>
</head>
<body>
<div class="admin-container">
    <?php include __DIR__ . '/_sidebar.php'; ?>
    <main class="main-content">
<?php include __DIR__ . '/_header.php'; ?>
        <!-- 筛选栏 -->
        <div class="filter-bar">
            <a href="/admin.php?action=listings" class="<?php echo !isset($currentStatus) ? 'active' : ''; ?>">全部</a>
            <a href="/admin.php?action=listings&status=1" class="<?php echo ($currentStatus ?? '') === 1 ? 'active' : ''; ?>">已发布</a>
            <a href="/admin.php?action=listings&status=0" class="<?php echo ($currentStatus ?? '') === 0 ? 'active' : ''; ?>">待审核</a>
        </div>

        <div class="content-card">
            <div class="card-header">
                <h3>分类信息列表（共 <?php echo $total; ?> 条）</h3>
            </div>
            <div class="card-body">
                <?php if (empty($listings)): ?>
                <div class="empty-state"><i class="fas fa-list-alt"></i><p>暂无分类信息</p></div>
                <?php else: ?>
                <table class="admin-table">
                    <thead><tr><th>ID</th><th>标题</th><th>作者</th><th>类型</th><th>地区</th><th>价格</th><th>状态</th><th>时间</th><th>操作</th></tr></thead>
                    <tbody>
                    <?php foreach ($listings as $item): ?>
                    <tr>
                        <td><?php echo $item['id']; ?></td>
                        <td><?php echo h(mb_substr($item['title'] ?? '', 0, 30)); ?></td>
                        <td><?php echo h($item['author_name'] ?? '-'); ?></td>
                        <td><span class="badge badge-info"><?php echo h($typeLabels[$item['type'] ?? 0] ?? '未知'); ?></span></td>
                        <td><?php echo h($item['location'] ?? '-'); ?></td>
                        <td><?php echo isset($item['price']) && $item['price'] > 0 ? '¥' . number_format($item['price'], 2) : '-'; ?></td>
                        <td>
                            <?php if (($item['status'] ?? 0) == 1): ?>
                            <span class="badge badge-success">已发布</span>
                            <?php else: ?>
                            <span class="badge badge-warning">待审核</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:12px;color:#9ca3af;"><?php echo date('m-d H:i', strtotime($item['created_at'] ?? 'now')); ?></td>
                        <td class="actions">
                            <a href="/listing/<?php echo $item['id']; ?>.html" target="_blank" class="btn btn-sm btn-outline">查看</a>
                            <button onclick="openEditListingModal(<?php echo $item['id']; ?>)" class="btn btn-sm btn-primary">编辑</button>
                            <button onclick="toggleStatus('listing', <?php echo $item['id']; ?>, <?php echo $item['status'] ?? 0; ?>)" class="btn btn-sm btn-warning">切换状态</button>
                            <button onclick="confirmDelete('listing', <?php echo $item['id']; ?>, '<?php echo h(addslashes($item['title'] ?? '')); ?>')" class="btn btn-sm btn-danger">删除</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php
                    $range = 2;
                    $start_p = max(1, $page - $range);
                    $end_p = min($totalPages, $page + $range);

                    if ($page > 1): ?>
                    <a href="?action=listings&page=<?php echo $page-1; ?>">上一页</a>
                    <?php endif;

                    if ($start_p > 1): ?>
                    <a href="?action=listings&page=1">1</a>
                    <?php if ($start_p > 2): ?><span class="ellipsis" style="padding:8px 6px;color:#9ca3af;">...</span><?php endif; ?>
                    <?php endif;

                    for ($i = $start_p; $i <= $end_p; $i++): ?>
                    <?php if ($i == $page): ?>
                    <span class="active"><?php echo $i; ?></span>
                    <?php else: ?>
                    <a href="?action=listings&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                    <?php endfor;

                    if ($end_p < $totalPages): ?>
                    <?php if ($end_p < $totalPages - 1): ?><span class="ellipsis" style="padding:8px 6px;color:#9ca3af;">...</span><?php endif; ?>
                    <a href="?action=listings&page=<?php echo $totalPages; ?>"><?php echo $totalPages; ?></a>
                    <?php endif;

                    if ($page < $totalPages): ?>
                    <a href="?action=listings&page=<?php echo $page+1; ?>">下一页</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
    </main>
</div>

<!-- 编辑分类信息模态框 -->
<div class="modal-overlay" id="editListingModal">
    <div class="modal">
        <div class="modal-header">
            <h3>编辑分类信息</h3>
            <button class="modal-close" onclick="closeModal('editListingModal')">&times;</button>
        </div>
        <div class="modal-body">
            <form id="editListingForm">
                <input type="hidden" name="id" id="editListingId">
                <div class="form-group">
                    <label class="form-label">标题</label>
                    <input type="text" name="title" id="editListingTitle" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">类型</label>
                    <select name="type" id="editListingType" class="form-control">
                        <option value="1">鸽舍转让</option>
                        <option value="2">配对信息</option>
                        <option value="3">求购/转让</option>
                    </select>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                    <div class="form-group">
                        <label class="form-label">价格</label>
                        <input type="number" step="0.01" name="price" id="editListingPrice" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">地区</label>
                        <input type="text" name="location" id="editListingLocation" class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">描述</label>
                    <div id="editListingEditor" style="background:#fff;height:300px;"></div>
                    <input type="hidden" name="description" id="editListingDesc">
                </div>
                <div class="form-group">
                    <label class="form-label">状态</label>
                    <select name="status" id="editListingStatus" class="form-control">
                        <option value="0">待审核</option>
                        <option value="1">已通过</option>
                        <option value="2">已拒绝</option>
                        <option value="3">已下架</option>
                    </select>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('editListingModal')">取消</button>
            <button class="btn btn-primary" onclick="submitEditListing()">保存</button>
        </div>
    </div>
</div>

<?php include __DIR__ . '/_scripts.php'; ?>
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script>
var listingListData = <?php echo json_encode($listings ?? []); ?>;
var quillListing = null;

function openEditListingModal(id) {
    var item = listingListData.find(function(l) { return l.id == id; });
    if (item) {
        document.getElementById('editListingId').value = item.id;
        document.getElementById('editListingTitle').value = item.title || '';
        document.getElementById('editListingType').value = item.type || 1;
        document.getElementById('editListingPrice').value = item.price || '';
        document.getElementById('editListingLocation').value = item.location || '';
        document.getElementById('editListingStatus').value = item.status ?? 0;
        openModal('editListingModal');
        setTimeout(function() {
            initQuillListing();
            if (quillListing) {
                quillListing.root.innerHTML = item.description || '';
            }
        }, 50);
    }
}

function submitEditListing() {
    if (quillListing) {
        document.getElementById('editListingDesc').value = quillListing.root.innerHTML;
    }
    var formData = new FormData(document.getElementById('editListingForm'));
    fetch('/admin.php?action=edit-listing', { method: 'POST', body: formData })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) { showToast(data.message, 'success'); setTimeout(function() { location.reload(); }, 1000); }
        else { showToast(data.message, 'error'); }
    })
    .catch(function() { showToast('操作失败', 'error'); });
}

function initQuillListing() {
    if (quillListing) return;
    quillListing = new Quill('#editListingEditor', {
        theme: 'snow',
        placeholder: '请输入描述内容...',
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'header': [1, 2, 3, false] }],
                [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                [{ 'color': [] }, { 'background': [] }],
                ['link', 'image'],
                ['clean']
            ]
        }
    });
}
</script>
</body>
</html>