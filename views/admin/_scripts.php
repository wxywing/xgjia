<script>
// 侧边栏切换
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('active');
}

// 点击外部关闭侧边栏
document.addEventListener('click', function(e) {
    var sidebar = document.getElementById('sidebar');
    var toggle = document.querySelector('.menu-toggle');
    if (sidebar && !sidebar.contains(e.target) && toggle && !toggle.contains(e.target)) {
        sidebar.classList.remove('active');
    }
});

// Toast提示
function showToast(msg, type) {
    type = type || 'success';
    var toast = document.createElement('div');
    toast.className = 'toast ' + type;
    toast.textContent = msg;
    document.body.appendChild(toast);
    setTimeout(function() { toast.classList.add('show'); }, 50);
    setTimeout(function() {
        toast.classList.remove('show');
        setTimeout(function() { toast.remove(); }, 300);
    }, 3000);
}

// 确认删除
function confirmDelete(type, id, name) {
    if (!confirm('确定要删除「' + (name || '') + '」吗？此操作不可撤销。')) return;
    fetch('/admin.php?action=delete', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'type=' + type + '&id=' + id
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

// 切换状态
function toggleStatus(type, id, currentStatus) {
    var newStatus = currentStatus === 1 ? 0 : 1;
    fetch('/admin.php?action=toggle-status', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'type=' + type + '&id=' + id + '&status=' + newStatus
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

// 模态框
function openModal(id) {
    document.getElementById(id).classList.add('active');
}
function closeModal(id) {
    document.getElementById(id).classList.remove('active');
}
</script>
