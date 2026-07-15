<?php extract($data); $activeMenu = 'pigeons'; ?>
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
        <!-- 数据表格 -->
        <div class="content-card">
            <div class="card-header">
                <h3>铭鸽列表 (共 <?php echo h($total); ?> 只)</h3>
            </div>
            <div class="card-body">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>名称</th>
                            <th>足环号</th>
                            <th>血统</th>
                            <th>展厅</th>
                            <th>性别</th>
                            <th>浏览</th>
                            <th>状态</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pigeons)): ?>
                        <tr>
                            <td colspan="9">
                                <div class="empty-state">
                                    <i class="fas fa-dove"></i>
                                    <p>暂无铭鸽数据</p>
                                </div>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($pigeons as $pigeon): ?>
                        <tr>
                            <td><?php echo h($pigeon['id']); ?></td>
                            <td><?php echo h($pigeon['name'] ?? '-'); ?></td>
                            <td style="font-size:13px;color:#6b7280;"><?php echo h($pigeon['ring_number'] ?? '-'); ?></td>
                            <td><?php echo h($pigeon['bloodline'] ?? '-'); ?></td>
                            <td style="font-size:13px;"><?php echo h($pigeon['shop_name'] ?? '-'); ?></td>
                            <td>
                                <?php
                                $genderMap = [0 => '未知', 1 => '雄', 2 => '雌'];
                                echo $genderMap[$pigeon['gender'] ?? 0] ?? '未知';
                                ?>
                            </td>
                            <td><?php echo number_format($pigeon['views'] ?? 0); ?></td>
                            <td>
                                <?php if (($pigeon['status'] ?? 1) == 1): ?>
                                <span class="badge badge-success">已发布</span>
                                <?php elseif (($pigeon['status'] ?? 1) == 0): ?>
                                <span class="badge badge-secondary">待审核</span>
                                <?php elseif (($pigeon['status'] ?? 1) == 2): ?>
                                <span class="badge badge-danger">已拒绝</span>
                                <?php else: ?>
                                <span class="badge badge-warning">已下架</span>
                                <?php endif; ?>
                            </td>
                            <td class="actions">
                                <a href="/pigeon/<?php echo h($pigeon['id']); ?>.html" target="_blank" class="btn btn-sm btn-outline"><i class="fas fa-eye"></i></a>
                                <button onclick="openEditPigeonModal(<?php echo $pigeon['id']; ?>)" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i> 编辑</button>
                                <button onclick="toggleStatus('pigeon', <?php echo h($pigeon['id']); ?>, <?php echo h($pigeon['status'] ?? 0); ?>)" class="btn btn-sm <?php echo ($pigeon['status'] ?? 0) == 1 ? 'btn-warning' : 'btn-success'; ?>">
                                    <i class="fas fa-toggle-<?php echo ($pigeon['status'] ?? 0) == 1 ? 'on' : 'off'; ?>"></i>
                                </button>
                                <button onclick="confirmDelete('pigeon', <?php echo h($pigeon['id']); ?>, '<?php echo h($pigeon['name'] ?? ''); ?>')" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
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
                    <a href="?action=pigeons&page=<?php echo $page-1; ?>">上一页</a>
                    <?php endif;

                    if ($start_p > 1): ?>
                    <a href="?action=pigeons&page=1">1</a>
                    <?php if ($start_p > 2): ?><span class="ellipsis" style="padding:8px 6px;color:#9ca3af;">...</span><?php endif; ?>
                    <?php endif;

                    for ($i = $start_p; $i <= $end_p; $i++): ?>
                    <?php if ($i == $page): ?>
                    <span class="active"><?php echo $i; ?></span>
                    <?php else: ?>
                    <a href="?action=pigeons&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                    <?php endfor;

                    if ($end_p < $totalPages): ?>
                    <?php if ($end_p < $totalPages - 1): ?><span class="ellipsis" style="padding:8px 6px;color:#9ca3af;">...</span><?php endif; ?>
                    <a href="?action=pigeons&page=<?php echo $totalPages; ?>"><?php echo $totalPages; ?></a>
                    <?php endif;

                    if ($page < $totalPages): ?>
                    <a href="?action=pigeons&page=<?php echo $page+1; ?>">下一页</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<!-- 编辑铭鸽模态框 -->
<div class="modal-overlay" id="editPigeonModal">
    <div class="modal" style="width:700px;max-width:95vw;">
        <div class="modal-header">
            <h3>编辑铭鸽</h3>
            <button class="modal-close" onclick="closeModal('editPigeonModal')">&times;</button>
        </div>
        <div class="modal-body" style="overflow-y:auto;max-height:calc(90vh - 120px);">
            <form id="editPigeonForm">
                <input type="hidden" name="id" id="editPigeonId">

                <div class="form-row" style="display:flex;gap:12px;">
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">鸽子名称</label>
                        <input type="text" name="name" id="editPigeonName" class="form-control" required>
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">足环号</label>
                        <input type="text" name="ring_number" id="editPigeonRingNumber" class="form-control">
                    </div>
                </div>

                <div class="form-row" style="display:flex;gap:12px;">
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">血统</label>
                        <input type="text" name="bloodline" id="editPigeonBloodline" class="form-control">
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">羽色</label>
                        <input type="text" name="color" id="editPigeonColor" class="form-control">
                    </div>
                </div>

                <div class="form-row" style="display:flex;gap:12px;">
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">性别</label>
                        <select name="gender" id="editPigeonGender" class="form-control">
                            <option value="0">未知</option>
                            <option value="1">雄</option>
                            <option value="2">雌</option>
                        </select>
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">眼砂</label>
                        <input type="text" name="eye_color" id="editPigeonEyeColor" class="form-control">
                    </div>
                </div>

                <div class="form-row" style="display:flex;gap:12px;">
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">状态</label>
                        <select name="status" id="editPigeonStatus" class="form-control">
                            <option value="0">待审核</option>
                            <option value="1">已发布</option>
                            <option value="2">已拒绝</option>
                            <option value="3">已下架</option>
                        </select>
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">置顶</label>
                        <select name="is_top" id="editPigeonIsTop" class="form-control">
                            <option value="0">否</option>
                            <option value="1">是</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">描述</label>
                    <div id="editPigeonEditor" style="background:#fff;height:200px;"></div>
                    <input type="hidden" name="description" id="editPigeonDesc">
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('editPigeonModal')">取消</button>
            <button class="btn btn-primary" onclick="submitEditPigeon()">保存</button>
        </div>
    </div>
</div>

<?php include __DIR__ . '/_scripts.php'; ?>
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script>
var pigeonListData = <?php echo json_encode($pigeons ?? []); ?>;
var quillPigeon = null;

function openEditPigeonModal(id) {
    var pigeon = pigeonListData.find(function(p) { return p.id == id; });
    if (pigeon) {
        document.getElementById('editPigeonId').value = pigeon.id;
        document.getElementById('editPigeonName').value = pigeon.name || '';
        document.getElementById('editPigeonRingNumber').value = pigeon.ring_number || '';
        document.getElementById('editPigeonBloodline').value = pigeon.bloodline || '';
        document.getElementById('editPigeonColor').value = pigeon.color || '';
        document.getElementById('editPigeonGender').value = pigeon.gender ?? 0;
        document.getElementById('editPigeonEyeColor').value = pigeon.eye_color || '';
        document.getElementById('editPigeonStatus').value = pigeon.status ?? 1;
        document.getElementById('editPigeonIsTop').value = pigeon.is_top ?? 0;
        openModal('editPigeonModal');
        setTimeout(function() {
            initQuillPigeon();
            if (quillPigeon) {
                quillPigeon.root.innerHTML = pigeon.description || '';
            }
        }, 50);
    }
}

function submitEditPigeon() {
    if (quillPigeon) {
        document.getElementById('editPigeonDesc').value = quillPigeon.root.innerHTML;
    }
    var formData = new FormData(document.getElementById('editPigeonForm'));
    fetch('/admin.php?action=edit-pigeon', { method: 'POST', body: formData })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) { showToast(data.message, 'success'); setTimeout(function() { location.reload(); }, 1000); }
        else { showToast(data.message, 'error'); }
    })
    .catch(function() { showToast('操作失败', 'error'); });
}

function initQuillPigeon() {
    if (quillPigeon) return;
    quillPigeon = new Quill('#editPigeonEditor', {
        theme: 'snow',
        placeholder: '请输入铭鸽描述...',
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
