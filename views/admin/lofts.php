<?php extract($data); $activeMenu = 'lofts'; ?>
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
        <!-- 操作按钮 -->
        <div style="margin-bottom:20px;display:flex;gap:12px;">
            <button onclick="openLoftModal(0)" class="btn btn-primary"><i class="fas fa-plus"></i> 新增公棚</button>
        </div>

        <div class="content-card">
            <div class="card-header">
                <h3>公棚列表（共 <?php echo $total; ?> 家）</h3>
            </div>
            <div class="card-body">
                <?php if (empty($lofts)): ?>
                <div class="empty-state"><i class="fas fa-building"></i><p>暂无公棚数据</p></div>
                <?php else: ?>
                <table class="admin-table">
                    <thead><tr><th>ID</th><th>公棚名称</th><th>地区</th><th>类型</th><th>已收/容量</th><th>参赛费</th><th>奖金池</th><th>评分</th><th>认证</th><th>状态</th><th>操作</th></tr></thead>
                    <tbody>
                    <?php foreach ($lofts as $loft): ?>
                    <tr>
                        <td><?php echo $loft['id']; ?></td>
                        <td style="font-weight:600;"><?php echo h($loft['name'] ?? ''); ?></td>
                        <td><?php echo h($loft['province'] . ' ' . $loft['city']); ?></td>
                        <td><span class="badge badge-info"><?php echo h($loft['race_type'] ?? '-'); ?></span></td>
                        <td><?php echo number_format($loft['current_count'] ?? 0); ?>/<?php echo number_format($loft['capacity'] ?? 0); ?></td>
                        <td>¥<?php echo number_format($loft['entry_fee'] ?? 0, 0); ?>/羽</td>
                        <td style="color:#f59e0b;font-weight:600;">¥<?php echo number_format($loft['prize_pool'] ?? 0, 0); ?></td>
                        <td><i class="fas fa-star" style="color:#f59e0b;"></i> <?php echo $loft['rating']; ?></td>
                        <td>
                            <?php if ($loft['is_certified']): ?>
                            <span class="badge badge-success">已认证</span>
                            <?php else: ?>
                            <span class="badge badge-secondary">未认证</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $statusLabels = [0 => ['待审核','badge-warning'], 1 => ['正常','badge-success'], 2 => ['已关闭','badge-secondary'], 3 => ['已取消','badge-danger']];
                            $label = $statusLabels[$loft['status'] ?? 0] ?? ['未知','badge-secondary'];
                            ?>
                            <span class="badge <?php echo $label[1]; ?>"><?php echo $label[0]; ?></span>
                        </td>
                        <td class="actions">
                            <button onclick="openLoftModal(<?php echo $loft['id']; ?>)" class="btn btn-sm btn-primary">编辑</button>
                            <button onclick="confirmDelete('loft', <?php echo $loft['id']; ?>, '<?php echo h(addslashes($loft['name'] ?? '')); ?>')" class="btn btn-sm btn-danger">删除</button>
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
                    <a href="?action=lofts&page=<?php echo $page-1; ?>">上一页</a>
                    <?php endif;

                    if ($start_p > 1): ?>
                    <a href="?action=lofts&page=1">1</a>
                    <?php if ($start_p > 2): ?><span class="ellipsis" style="padding:8px 6px;color:#9ca3af;">...</span><?php endif; ?>
                    <?php endif;

                    for ($i = $start_p; $i <= $end_p; $i++): ?>
                    <?php if ($i == $page): ?>
                    <span class="active"><?php echo $i; ?></span>
                    <?php else: ?>
                    <a href="?action=lofts&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                    <?php endfor;

                    if ($end_p < $totalPages): ?>
                    <?php if ($end_p < $totalPages - 1): ?><span class="ellipsis" style="padding:8px 6px;color:#9ca3af;">...</span><?php endif; ?>
                    <a href="?action=lofts&page=<?php echo $totalPages; ?>"><?php echo $totalPages; ?></a>
                    <?php endif;

                    if ($page < $totalPages): ?>
                    <a href="?action=lofts&page=<?php echo $page+1; ?>">下一页</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

        <!-- 编辑/新增公棚模态框 -->
        <div class="modal-overlay" id="loftModal">
            <div class="modal" style="max-width:680px;">
                <div class="modal-header">
                    <h3 id="loftModalTitle">新增公棚</h3>
                    <button class="modal-close" onclick="closeModal('loftModal')">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="loftForm">
                        <input type="hidden" name="id" id="loftId" value="0">
                        <div class="form-group">
                            <label class="form-label">公棚名称 *</label>
                            <input type="text" name="name" id="loftName" class="form-control" required>
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                            <div class="form-group">
                                <label class="form-label">省份</label>
                                <input type="text" name="province" id="loftProvince" class="form-control">
                            </div>
                            <div class="form-group">
                                <label class="form-label">城市</label>
                                <input type="text" name="city" id="loftCity" class="form-control">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">详细地址</label>
                            <input type="text" name="address" id="loftAddress" class="form-control">
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                            <div class="form-group">
                                <label class="form-label">联系人</label>
                                <input type="text" name="contact_name" id="loftContactName" class="form-control">
                            </div>
                            <div class="form-group">
                                <label class="form-label">联系电话</label>
                                <input type="text" name="contact_phone" id="loftContactPhone" class="form-control">
                            </div>
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">
                            <div class="form-group">
                                <label class="form-label">比赛类型</label>
                                <select name="race_type" id="loftRaceType" class="form-control">
                                    <option value="春棚">春棚</option>
                                    <option value="秋棚">秋棚</option>
                                    <option value="特比环棚">特比环棚</option>
                                    <option value="多关赛棚">多关赛棚</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">比赛距离(km)</label>
                                <input type="number" name="race_distance" id="loftRaceDistance" class="form-control">
                            </div>
                            <div class="form-group">
                                <label class="form-label">收鸽容量(羽)</label>
                                <input type="number" name="capacity" id="loftCapacity" class="form-control">
                            </div>
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">
                            <div class="form-group">
                                <label class="form-label">参赛费(元/羽)</label>
                                <input type="number" step="0.01" name="entry_fee" id="loftEntryFee" class="form-control">
                            </div>
                            <div class="form-group">
                                <label class="form-label">管理费(元/羽)</label>
                                <input type="number" step="0.01" name="management_fee" id="loftManagementFee" class="form-control">
                            </div>
                            <div class="form-group">
                                <label class="form-label">奖金池(元)</label>
                                <input type="number" step="0.01" name="prize_pool" id="loftPrizePool" class="form-control">
                            </div>
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                            <div class="form-group">
                                <label class="form-label">收鸽开始</label>
                                <input type="date" name="collect_start" id="loftCollectStart" class="form-control">
                            </div>
                            <div class="form-group">
                                <label class="form-label">收鸽截止</label>
                                <input type="date" name="collect_end" id="loftCollectEnd" class="form-control">
                            </div>
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                            <div class="form-group">
                                <label class="form-label">训放开始</label>
                                <input type="date" name="training_start" id="loftTrainingStart" class="form-control">
                            </div>
                            <div class="form-group">
                                <label class="form-label">比赛日期</label>
                                <input type="date" name="race_date" id="loftRaceDate" class="form-control">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">公棚简介</label>
                            <textarea name="description" id="loftDesc" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">参赛规程</label>
                            <textarea name="rules" id="loftRules" class="form-control" rows="3"></textarea>
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">
                            <div class="form-group">
                                <label class="form-label">状态</label>
                                <select name="status" id="loftStatus" class="form-control">
                                    <option value="0">待审核</option>
                                    <option value="1">正常</option>
                                    <option value="2">已关闭</option>
                                    <option value="3">已取消</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">认证公棚</label>
                                <select name="is_certified" id="loftCertified" class="form-control">
                                    <option value="0">否</option>
                                    <option value="1">是</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">热门公棚</label>
                                <select name="is_hot" id="loftHot" class="form-control">
                                    <option value="0">否</option>
                                    <option value="1">是</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer" style="padding:0;border:none;margin-top:20px;">
                            <button type="button" class="btn btn-secondary" onclick="closeModal('loftModal')">取消</button>
                            <button type="button" class="btn btn-primary" onclick="saveLoft()">保存</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>
<?php include __DIR__ . '/_scripts.php'; ?>
<script>
var loftListData = <?php echo json_encode($lofts ?? []); ?>;

function openLoftModal(id) {
    var titleEl = document.getElementById('loftModalTitle');
    if (id > 0) {
        titleEl.textContent = '编辑公棚';
        var loft = loftListData.find(function(l) { return l.id == id; });
        if (loft) {
            document.getElementById('loftId').value = loft.id;
            document.getElementById('loftName').value = loft.name || '';
            document.getElementById('loftProvince').value = loft.province || '';
            document.getElementById('loftCity').value = loft.city || '';
            document.getElementById('loftAddress').value = loft.address || '';
            document.getElementById('loftContactName').value = loft.contact_name || '';
            document.getElementById('loftContactPhone').value = loft.contact_phone || '';
            document.getElementById('loftRaceType').value = loft.race_type || '秋棚';
            document.getElementById('loftRaceDistance').value = loft.race_distance || '';
            document.getElementById('loftCapacity').value = loft.capacity || '';
            document.getElementById('loftEntryFee').value = loft.entry_fee || '';
            document.getElementById('loftManagementFee').value = loft.management_fee || '';
            document.getElementById('loftPrizePool').value = loft.prize_pool || '';
            document.getElementById('loftCollectStart').value = loft.collect_start || '';
            document.getElementById('loftCollectEnd').value = loft.collect_end || '';
            document.getElementById('loftTrainingStart').value = loft.training_start || '';
            document.getElementById('loftRaceDate').value = loft.race_date || '';
            document.getElementById('loftDesc').value = loft.description || '';
            document.getElementById('loftRules').value = loft.rules || '';
            document.getElementById('loftStatus').value = loft.status ?? 0;
            document.getElementById('loftCertified').value = loft.is_certified ?? 0;
            document.getElementById('loftHot').value = loft.is_hot ?? 0;
        }
    } else {
        titleEl.textContent = '新增公棚';
        document.getElementById('loftId').value = 0;
        document.getElementById('loftForm').reset();
    }
    openModal('loftModal');
}

function saveLoft() {
    var formData = new FormData(document.getElementById('loftForm'));
    fetch('/admin.php?action=save-loft', { method: 'POST', body: formData })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) { showToast(data.message, 'success'); setTimeout(function() { location.reload(); }, 1000); }
        else { showToast(data.message, 'error'); }
    })
    .catch(function() { showToast('保存失败', 'error'); });
}
</script>
</body>
</html>