<?php extract($data); $activeMenu = 'claims'; ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo h($pageTitle ?? '认领管理 - 管理后台'); ?></title>
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php include __DIR__ . '/_styles.php'; ?>
</head>
<body>
<div class="admin-container">
    <?php include __DIR__ . '/_sidebar.php'; ?>
    <main class="main-content">
<?php include __DIR__ . '/_header.php'; ?>

<?php
$statusMap = [0 => '待审核', 1 => '已通过', 2 => '已拒绝', 3 => '已取消'];
$statusBadge = [0 => 'warning', 1 => 'success', 2 => 'danger', 3 => 'secondary'];
$typeNames = ['shop' => '展厅', 'loft' => '公棚'];
?>

        <!-- 筛选栏 -->
        <div class="filter-bar">
            <form method="GET" action="/admin.php" class="filter-form" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                <input type="hidden" name="action" value="claims">
                <div class="filter-group" style="display:flex;align-items:center;gap:6px;">
                    <label style="font-size:13px;color:#6b7280;">状态</label>
                    <select name="status" class="form-control" style="width:auto;padding:4px 8px;font-size:13px;">
                        <option value="">全部</option>
                        <option value="0" <?php echo (isset($currentStatus) && $currentStatus === 0) ? 'selected' : ''; ?>>待审核</option>
                        <option value="1" <?php echo (isset($currentStatus) && $currentStatus === 1) ? 'selected' : ''; ?>>已通过</option>
                        <option value="2" <?php echo (isset($currentStatus) && $currentStatus === 2) ? 'selected' : ''; ?>>已拒绝</option>
                    </select>
                </div>
                <div class="filter-group" style="display:flex;align-items:center;gap:6px;">
                    <label style="font-size:13px;color:#6b7280;">类型</label>
                    <select name="target_type" class="form-control" style="width:auto;padding:4px 8px;font-size:13px;">
                        <option value="">全部</option>
                        <option value="shop" <?php echo (isset($targetType) && $targetType === 'shop') ? 'selected' : ''; ?>>展厅</option>
                        <option value="loft" <?php echo (isset($targetType) && $targetType === 'loft') ? 'selected' : ''; ?>>公棚</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">筛选</button>
            </form>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <?php if (!empty($statusCounts)): ?>
                <?php foreach ($statusCounts as $s => $count): ?>
                <span class="badge badge-<?php echo $statusBadge[$s] ?? 'secondary'; ?>">
                    <?php echo $statusMap[$s] ?? '未知'; ?>: <?php echo $count; ?>
                </span>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- 数据表格 -->
        <div class="content-card">
            <div class="card-header">
                <h3>认领申请 (共 <?php echo h($total ?? 0); ?> 条)</h3>
            </div>
            <div class="card-body">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>申请人</th>
                            <th>认领目标</th>
                            <th>姓名/电话</th>
                            <th>申请理由</th>
                            <th>状态</th>
                            <th>申请时间</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($claims)): ?>
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <i class="fas fa-hand-holding-heart"></i>
                                    <p>暂无认领申请</p>
                                </div>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($claims as $claim): ?>
                        <?php
                            $s = intval($claim['status']);
                            $targetName = $claim['target_type'] === 'shop'
                                ? ($claim['shop_name'] ?? '展厅#' . $claim['target_id'])
                                : ($claim['loft_name'] ?? '公棚#' . $claim['target_id']);
                            $targetUrl = $claim['target_type'] === 'shop'
                                ? '/shop/' . $claim['target_id'] . '.html'
                                : '/loft/' . $claim['target_id'] . '.html';
                        ?>
                        <tr>
                            <td>#<?php echo $claim['id']; ?></td>
                            <td>
                                <div style="display:flex;align-items:center;gap:6px;">
                                    <?php if (!empty($claim['avatar'])): ?>
                                    <img loading="lazy" src="<?php echo h($claim['avatar']); ?>" alt="<?php echo h($claim['username'] ?? '用户'); ?> 头像" style="width:28px;height:28px;border-radius:50%;">
                                    <?php endif; ?>
                                    <span><?php echo h($claim['username'] ?? '用户'); ?></span>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-<?php echo $claim['target_type'] === 'shop' ? 'info' : 'primary'; ?>">
                                    <?php echo $typeNames[$claim['target_type']] ?? '未知'; ?>
                                </span>
                                <a href="<?php echo $targetUrl; ?>" target="_blank" style="margin-left:4px;color:#2563eb;"><?php echo h($targetName); ?></a>
                            </td>
                            <td>
                                <div style="font-size:13px;"><?php echo h($claim['real_name'] ?? '-'); ?></div>
                                <div style="font-size:12px;color:#9ca3af;"><?php echo h($claim['phone'] ?? '-'); ?></div>
                                <?php if (!empty($claim['wechat'])): ?>
                                <div style="font-size:12px;color:#9ca3af;">微信: <?php echo h($claim['wechat']); ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:13px;" title="<?php echo h($claim['reason'] ?? ''); ?>">
                                    <?php echo h($claim['reason'] ?? '-'); ?>
                                </div>
                            </td>
                            <td><span class="badge badge-<?php echo $statusBadge[$s] ?? 'secondary'; ?>"><?php echo $statusMap[$s] ?? '未知'; ?></span></td>
                            <td style="font-size:13px;"><?php echo date('m-d H:i', strtotime($claim['created_at'])); ?></td>
                            <td class="actions">
                                <?php if ($s === 0): ?>
                                <button class="btn btn-success btn-xs" onclick="reviewClaim(<?php echo $claim['id']; ?>, 'approve')"><i class="fas fa-check"></i> 通过</button>
                                <button class="btn btn-danger btn-xs" onclick="reviewClaim(<?php echo $claim['id']; ?>, 'reject')"><i class="fas fa-times"></i> 拒绝</button>
                                <?php else: ?>
                                <?php if (!empty($claim['admin_note'])): ?>
                                <span style="font-size:12px;color:#9ca3af;" title="<?php echo h($claim['admin_note']); ?>">
                                    <i class="fas fa-sticky-note"></i>
                                </span>
                                <?php endif; ?>
                                <span style="font-size:12px;color:#9ca3af;">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>

                <!-- 分页 -->
                <?php if (isset($totalPages) && $totalPages > 1): ?>
                <div class="pagination">
                    <?php
                    $tp = $totalPages;
                    $range = 2;
                    $start_p = max(1, $page - $range);
                    $end_p = min($tp, $page + $range);

                    if ($page > 1): ?>
                    <a href="?action=claims&page=<?php echo $page-1; ?>&status=<?php echo $currentStatus ?? ''; ?>&target_type=<?php echo $targetType ?? ''; ?>">上一页</a>
                    <?php endif;

                    if ($start_p > 1): ?>
                    <a href="?action=claims&page=1&status=<?php echo $currentStatus ?? ''; ?>&target_type=<?php echo $targetType ?? ''; ?>">1</a>
                    <?php if ($start_p > 2): ?><span class="ellipsis" style="padding:8px 6px;color:#9ca3af;">...</span><?php endif; ?>
                    <?php endif;

                    for ($i = $start_p; $i <= $end_p; $i++): ?>
                    <?php if ($i == $page): ?>
                    <span class="active"><?php echo $i; ?></span>
                    <?php else: ?>
                    <a href="?action=claims&page=<?php echo $i; ?>&status=<?php echo $currentStatus ?? ''; ?>&target_type=<?php echo $targetType ?? ''; ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                    <?php endfor;

                    if ($end_p < $tp): ?>
                    <?php if ($end_p < $tp - 1): ?><span class="ellipsis" style="padding:8px 6px;color:#9ca3af;">...</span><?php endif; ?>
                    <a href="?action=claims&page=<?php echo $tp; ?>&status=<?php echo $currentStatus ?? ''; ?>&target_type=<?php echo $targetType ?? ''; ?>"><?php echo $tp; ?></a>
                    <?php endif;

                    if ($page < $tp): ?>
                    <a href="?action=claims&page=<?php echo $page+1; ?>&status=<?php echo $currentStatus ?? ''; ?>&target_type=<?php echo $targetType ?? ''; ?>">下一页</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<!-- 审核模态框 -->
<div class="modal-overlay" id="reviewModal">
    <div class="modal" style="max-width:480px;">
        <div class="modal-header">
            <h3 id="reviewModalTitle">审核认领申请</h3>
            <button class="modal-close" onclick="closeModal('reviewModal')">&times;</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="reviewClaimId">
            <input type="hidden" id="reviewAction">
            <div class="form-group">
                <label class="form-label">管理员备注</label>
                <textarea id="reviewNote" class="form-control" rows="3" placeholder="可选，填写审核意见..."></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('reviewModal')">取消</button>
            <button class="btn btn-primary" onclick="submitReview()">确认</button>
        </div>
    </div>
</div>

<?php include __DIR__ . '/_scripts.php'; ?>
<script>
function reviewClaim(id, action) {
    document.getElementById('reviewClaimId').value = id;
    document.getElementById('reviewAction').value = action;
    document.getElementById('reviewModalTitle').textContent = action === 'approve' ? '通过认领申请' : '拒绝认领申请';
    document.getElementById('reviewNote').value = '';
    openModal('reviewModal');
}

function submitReview() {
    var id = document.getElementById('reviewClaimId').value;
    var action = document.getElementById('reviewAction').value;
    var note = document.getElementById('reviewNote').value;

    var fd = new FormData();
    fd.append('id', id);
    fd.append('action', action);
    fd.append('note', note);

    fetch('/claim?action=admin_review', { method: 'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.success) {
                showToast(d.message || '操作成功', 'success');
                setTimeout(function() { location.reload(); }, 1000);
            } else {
                showToast(d.message || '操作失败', 'error');
            }
        })
        .catch(function() { showToast('操作失败', 'error'); });
}
</script>
</body>
</html>
