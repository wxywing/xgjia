<?php
extract($data); $activeMenu = 'ads';
$positionLabels = ['home_banner' => '首页横幅', 'sidebar' => '侧边栏', 'article_bottom' => '文章底部'];
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
        <div class="content-card">
            <div class="card-header">
                <h3>广告列表</h3>
                <button onclick="openAdModal(0)" class="btn btn-primary"><i class="fas fa-plus"></i> 新增广告</button>
            </div>
            <div class="card-body">
                <?php if (empty($ads)): ?>
                <div class="empty-state"><i class="fas fa-bullhorn"></i><p>暂无广告</p></div>
                <?php else: ?>
                <table class="admin-table">
                    <thead><tr><th>ID</th><th>标题</th><th>位置</th><th>图片</th><th>排序</th><th>状态</th><th>有效期</th><th>操作</th></tr></thead>
                    <tbody>
                    <?php foreach ($ads as $ad): ?>
                    <tr>
                        <td><?php echo $ad['id']; ?></td>
                        <td><?php echo h($ad['title']); ?></td>
                        <td><span class="badge badge-info"><?php echo h($positionLabels[$ad['position']] ?? $ad['position']); ?></span></td>
                        <td>
                            <?php if ($ad['image'] ?? ''): ?>
                            <img loading="lazy" src="<?php echo h($ad['image'] ?? ''); ?>" style="max-width:80px;max-height:40px;object-fit:cover;border-radius:4px;" alt="">
                            <?php else: ?>-
                            <?php endif; ?>
                        </td>
                        <td><?php echo $ad['sort']; ?></td>
                        <td>
                            <?php if ($ad['status'] == 1): ?>
                            <span class="badge badge-success">启用</span>
                            <?php else: ?>
                            <span class="badge badge-danger">禁用</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:12px;color:#9ca3af;"><?php echo h($ad['start_at'] ?? ''); ?> ~ <?php echo h($ad['end_at'] ?? ''); ?></td>
                        <td class="actions">
                            <button onclick="openAdModal(<?php echo $ad['id']; ?>)" class="btn btn-sm btn-primary">编辑</button>
                            <button onclick="toggleStatus('ad', <?php echo $ad['id']; ?>, <?php echo $ad['status']; ?>)" class="btn btn-sm btn-warning">切换状态</button>
                            <button onclick="confirmDelete('ad', <?php echo $ad['id']; ?>, '<?php echo h(addslashes($ad['title'])); ?>')" class="btn btn-sm btn-danger">删除</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- 广告编辑模态框 -->
        <div class="modal-overlay" id="adModal">
            <div class="modal">
                <div class="modal-header">
                    <h3 id="adModalTitle">新增广告</h3>
                    <button class="modal-close" onclick="closeModal('adModal')">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="adForm" onsubmit="saveAd(event)">
                        <input type="hidden" id="adId" name="id" value="0">
                        <div class="form-group">
                            <label class="form-label">广告标题</label>
                            <input type="text" id="adTitle" name="title" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">广告位置</label>
                            <select id="adPosition" name="position" class="form-control">
                                <option value="home_banner">首页横幅</option>
                                <option value="sidebar">侧边栏</option>
                                <option value="article_bottom">文章底部</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">图片URL</label>
                            <input type="text" id="adImage" name="image" class="form-control" placeholder="/public/assets/images/ad-example.jpg">
                        </div>
                        <div class="form-group">
                            <label class="form-label">链接URL</label>
                            <input type="text" id="adLink" name="link" class="form-control" placeholder="https://example.com">
                        </div>
                        <div class="form-group">
                            <label class="form-label">排序权重</label>
                            <input type="number" id="adSort" name="sort" class="form-control" value="0">
                            <div class="form-hint">数字越大排序越靠前</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">状态</label>
                            <select id="adStatus" name="status" class="form-control">
                                <option value="1">启用</option>
                                <option value="0">禁用</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">开始日期</label>
                            <input type="date" id="adStartAt" name="start_at" class="form-control">
                        </div>
                        <div class="form-group">
                            <label class="form-label">结束日期</label>
                            <input type="date" id="adEndAt" name="end_at" class="form-control">
                        </div>
                        <div class="modal-footer" style="padding:0;border:none;margin-top:20px;">
                            <button type="button" class="btn btn-secondary" onclick="closeModal('adModal')">取消</button>
                            <button type="submit" class="btn btn-primary">保存</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>
<?php include __DIR__ . '/_scripts.php'; ?>
<script>
var adListData = <?php echo json_encode($ads ?? []); ?>;

function openAdModal(id) {
    var modal = document.getElementById('adModalTitle');
    if (id > 0) {
        modal.textContent = '编辑广告';
        var ad = adListData.find(function(a) { return a.id == id; });
        if (ad) {
            document.getElementById('adId').value = ad.id;
            document.getElementById('adTitle').value = ad.title || '';
            document.getElementById('adPosition').value = ad.position || 'home_banner';
            document.getElementById('adImage').value = ad.image || '';
            document.getElementById('adLink').value = ad.link || '';
            document.getElementById('adSort').value = ad.sort || 0;
            document.getElementById('adStatus').value = ad.status || 1;
            document.getElementById('adStartAt').value = ad.start_at || '';
            document.getElementById('adEndAt').value = ad.end_at || '';
        }
    } else {
        modal.textContent = '新增广告';
        document.getElementById('adId').value = 0;
        document.getElementById('adForm').reset();
    }
    openModal('adModal');
}

function saveAd(e) {
    e.preventDefault();
    var form = document.getElementById('adForm');
    var formData = new FormData(form);
    fetch('/admin.php?action=save-ad', { method: 'POST', body: formData })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(function() { location.reload(); }, 1000);
        } else { showToast(data.message, 'error'); }
    })
    .catch(function() { showToast('保存失败', 'error'); });
}
</script>
</body>
</html>