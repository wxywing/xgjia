<?php extract($data); $activeMenu = 'dynamics'; ?>
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
        <div class="content-card">
            <div class="card-header">
                <h3>动态管理（共 <?php echo $total; ?> 条）</h3>
            </div>
            <div class="card-body">
                <?php if (empty($dynamics)): ?>
                <div class="empty-state"><i class="fas fa-comments"></i><p>暂无动态</p></div>
                <?php else: ?>
                <table class="admin-table">
                    <thead><tr><th>ID</th><th>内容</th><th>发布者</th><th>点赞</th><th>评论</th><th>状态</th><th>时间</th><th>操作</th></tr></thead>
                    <tbody>
                    <?php foreach ($dynamics as $dyn): ?>
                    <tr>
                        <td><?php echo $dyn['id']; ?></td>
                        <td style="max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?php echo h($dyn['content'] ?? ''); ?>"><?php echo h(mb_substr($dyn['content'] ?? '', 0, 80)); ?></td>
                        <td>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <img loading="lazy" src="<?php echo h($dyn['avatar'] ?? '/public/assets/images/default-avatar.png'); ?>" style="width:28px;height:28px;border-radius:50%;object-fit:cover;" alt="">
                                <span><?php echo h($dyn['username'] ?? '-'); ?></span>
                            </div>
                        </td>
                        <td><i class="fas fa-heart" style="color:#ef4444;margin-right:4px;"></i><?php echo number_format($dyn['likes'] ?? 0); ?></td>
                        <td><i class="fas fa-comment" style="color:#3b82f6;margin-right:4px;"></i><?php echo number_format($dyn['comments'] ?? 0); ?></td>
                        <td>
                            <?php if (($dyn['status'] ?? 1) == 1): ?>
                            <span class="badge badge-success">显示</span>
                            <?php else: ?>
                            <span class="badge badge-secondary">隐藏</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:12px;color:#9ca3af;"><?php echo date('m-d H:i', strtotime($dyn['created_at'] ?? 'now')); ?></td>
                        <td class="actions">
                            <button onclick="openEditDynamicModal(<?php echo $dyn['id']; ?>)" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></button>
                            <button onclick="toggleDynamicStatus(<?php echo $dyn['id']; ?>, <?php echo $dyn['status'] ?? 1; ?>)" class="btn btn-sm <?php echo ($dyn['status'] ?? 1) == 1 ? 'btn-warning' : 'btn-success'; ?>">
                                <i class="fas fa-toggle-<?php echo ($dyn['status'] ?? 1) == 1 ? 'on' : 'off'; ?>"></i>
                            </button>
                            <button onclick="confirmDelete('dynamic', <?php echo $dyn['id']; ?>, '动态#<?php echo $dyn['id']; ?>')" class="btn btn-sm btn-danger">删除</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<!-- 编辑动态模态框 -->
<div class="modal-overlay" id="editDynamicModal">
    <div class="modal" style="width:700px;max-width:95vw;max-height:90vh;">
        <div class="modal-header">
            <h3>编辑动态</h3>
            <button class="modal-close" onclick="closeModal('editDynamicModal')">&times;</button>
        </div>
        <div class="modal-body" style="overflow-y:auto;max-height:calc(90vh - 120px);">
            <form id="editDynamicForm">
                <input type="hidden" name="id" id="editDynamicId">
                <div class="form-group">
                    <label class="form-label">内容</label>
                    <div id="editDynamicEditor" style="background:#fff;height:300px;"></div>
                    <input type="hidden" name="content" id="editDynamicContent">
                </div>
                <div class="form-group">
                    <label class="form-label">状态</label>
                    <select name="status" id="editDynamicStatus" class="form-control">
                        <option value="1">显示</option>
                        <option value="0">隐藏</option>
                    </select>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('editDynamicModal')">取消</button>
            <button class="btn btn-primary" onclick="submitEditDynamic()">保存</button>
        </div>
    </div>
</div>

<?php include __DIR__ . '/_scripts.php'; ?>
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script>
var dynamicListData = <?php echo json_encode($dynamics ?? []); ?>;
var quillDynamic = null;

function openEditDynamicModal(id) {
    var dyn = dynamicListData.find(function(d) { return d.id == id; });
    if (dyn) {
        document.getElementById('editDynamicId').value = dyn.id;
        document.getElementById('editDynamicStatus').value = dyn.status ?? 1;
        openModal('editDynamicModal');
        setTimeout(function() {
            initQuillDynamic();
            if (quillDynamic) {
                quillDynamic.root.innerHTML = dyn.content || '';
            }
        }, 50);
    }
}

function submitEditDynamic() {
    if (quillDynamic) {
        document.getElementById('editDynamicContent').value = quillDynamic.root.innerHTML;
    }
    var formData = new FormData(document.getElementById('editDynamicForm'));
    fetch('/admin.php?action=edit-dynamic', { method: 'POST', body: formData })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) { showToast(data.message, 'success'); setTimeout(function() { location.reload(); }, 1000); }
        else { showToast(data.message, 'error'); }
    })
    .catch(function() { showToast('操作失败', 'error'); });
}

function initQuillDynamic() {
    if (quillDynamic) return;
    quillDynamic = new Quill('#editDynamicEditor', {
        theme: 'snow',
        placeholder: '请输入动态内容...',
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                ['clean']
            ]
        }
    });
}

function toggleDynamicStatus(id, currentStatus) {
    var newStatus = currentStatus == 1 ? 0 : 1;
    fetch('/admin.php?action=toggle-status', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'type=dynamic&id=' + id + '&status=' + newStatus
    })
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