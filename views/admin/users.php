<?php extract($data); $activeMenu = 'users'; ?>
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
        <!-- 搜索栏 -->
        <div class="search-bar">
            <form method="GET" action="">
                <input type="hidden" name="action" value="users">
                <input type="text" name="keyword" value="<?php echo h($keyword ?? ''); ?>" placeholder="搜索用户名或邮箱...">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> 搜索</button>
                <?php if (!empty($keyword)): ?>
                <a href="/admin.php?action=users" class="btn btn-secondary">清除</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- 数据表格 -->
        <div class="content-card">
            <div class="card-header">
                <h3>用户列表 (共 <?php echo h($total); ?> 个)</h3>
            </div>
            <div class="card-body">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>用户</th>
                            <th>邮箱</th>
                            <th>会员等级</th>
                            <th>状态</th>
                            <th>注册时间</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <i class="fas fa-users-slash"></i>
                                    <p>暂无用户数据</p>
                                </div>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo h($user['id']); ?></td>
                            <td>
                                <div style="display:flex;align-items:center;gap:10px;">
                                    <img loading="lazy" src="<?php echo h($user['avatar'] ?? '/public/assets/images/default-avatar.png'); ?>" alt="" style="width:40px;height:40px;border-radius:50%;object-fit:cover;">
                                    <span><?php echo h($user['username']); ?></span>
                                </div>
                            </td>
                            <td><?php echo h($user['email']); ?></td>
                            <td>
                                <?php if (($user['member_level'] ?? 0) == 1): ?>
                                <span class="badge badge-warning">VIP</span>
                                <?php else: ?>
                                <span class="badge badge-secondary">免费</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($user['status'] == 1): ?>
                                <span class="badge badge-success">正常</span>
                                <?php else: ?>
                                <span class="badge badge-danger">禁用</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo h($user['created_at']); ?></td>
                            <td class="actions">
                                <button onclick="openEditUserModal(<?php echo htmlspecialchars(json_encode($user), ENT_QUOTES); ?>)" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i> 编辑</button>
                                <button onclick="toggleStatus('user', <?php echo h($user['id']); ?>, <?php echo h($user['status']); ?>)" class="btn btn-sm <?php echo $user['status'] == 1 ? 'btn-warning' : 'btn-success'; ?>">
                                    <i class="fas fa-toggle-<?php echo $user['status'] == 1 ? 'on' : 'off'; ?>"></i>
                                </button>
                                <button onclick="confirmDelete('user', <?php echo h($user['id']); ?>, '<?php echo h($user['username']); ?>')" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
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
                    <a href="?action=users&page=<?php echo $page-1; ?>">上一页</a>
                    <?php endif;

                    if ($start_p > 1): ?>
                    <a href="?action=users&page=1">1</a>
                    <?php if ($start_p > 2): ?><span class="ellipsis" style="padding:8px 6px;color:#9ca3af;">...</span><?php endif; ?>
                    <?php endif;

                    for ($i = $start_p; $i <= $end_p; $i++): ?>
                    <?php if ($i == $page): ?>
                    <span class="active"><?php echo $i; ?></span>
                    <?php else: ?>
                    <a href="?action=users&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                    <?php endfor;

                    if ($end_p < $totalPages): ?>
                    <?php if ($end_p < $totalPages - 1): ?><span class="ellipsis" style="padding:8px 6px;color:#9ca3af;">...</span><?php endif; ?>
                    <a href="?action=users&page=<?php echo $totalPages; ?>"><?php echo $totalPages; ?></a>
                    <?php endif;

                    if ($page < $totalPages): ?>
                    <a href="?action=users&page=<?php echo $page+1; ?>">下一页</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<!-- 编辑用户模态框 -->
<div class="modal-overlay" id="editUserModal">
    <div class="modal">
        <div class="modal-header">
            <h3>编辑用户</h3>
            <button class="modal-close" onclick="closeModal('editUserModal')">&times;</button>
        </div>
        <div class="modal-body">
            <form id="editUserForm">
                <input type="hidden" name="id" id="editUserId">
                <div class="form-group">
                    <label class="form-label">用户名</label>
                    <input type="text" name="username" id="editUsername" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">邮箱</label>
                    <input type="email" name="email" id="editEmail" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">手机</label>
                    <input type="text" name="phone" id="editPhone" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">会员等级</label>
                    <select name="member_level" id="editMemberLevel" class="form-control">
                        <option value="0">免费</option>
                        <option value="1">VIP</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">状态</label>
                    <select name="status" id="editStatus" class="form-control">
                        <option value="0">禁用</option>
                        <option value="1">正常</option>
                    </select>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('editUserModal')">取消</button>
            <button class="btn btn-primary" onclick="submitEditUser()">保存</button>
        </div>
    </div>
</div>

<?php include __DIR__ . '/_scripts.php'; ?>
<script>
function openEditUserModal(user) {
    document.getElementById('editUserId').value = user.id;
    document.getElementById('editUsername').value = user.username;
    document.getElementById('editEmail').value = user.email;
    document.getElementById('editPhone').value = user.phone || '';
    document.getElementById('editMemberLevel').value = user.member_level ?? 0;
    document.getElementById('editStatus').value = user.status;
    openModal('editUserModal');
}

function submitEditUser() {
    var form = document.getElementById('editUserForm');
    var formData = new FormData(form);
    
    fetch('/admin.php?action=edit-user', {
        method: 'POST',
        body: formData
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(function() { location.reload(); }, 1000);
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(function() { showToast('操作失败', 'error'); });
}
</script>
</body>
</html>
