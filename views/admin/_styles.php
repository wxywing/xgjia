<style>
/* 管理后台共用样式 */
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif; background:#f3f4f6; }
.admin-container { display:flex; min-height:100vh; }

/* 侧边栏 */
.sidebar { width:250px; background:linear-gradient(180deg,#1e40af,#3b82f6); color:#fff; position:fixed; height:100vh; overflow-y:auto; flex-shrink:0; z-index:100; }
.sidebar-header { padding:20px; border-bottom:1px solid rgba(255,255,255,.1); }
.sidebar-brand { display:flex; align-items:center; color:#fff; text-decoration:none; font-size:18px; font-weight:700; }
.sidebar-brand i { margin-right:10px; font-size:24px; }
.sidebar-nav { padding:15px 0; }
.nav-item { display:block; padding:12px 20px; color:rgba(255,255,255,.8); text-decoration:none; transition:.3s; font-size:14px; }
.nav-item:hover,.nav-item.active { background:rgba(255,255,255,.1); color:#fff; }
.nav-item i { margin-right:10px; width:20px; }

/* 主内容 */
.main-content { flex:1; margin-left:250px; padding:20px; }

/* 顶部工具栏 */
.top-bar { background:#fff; padding:15px 20px; border-radius:8px; box-shadow:0 1px 3px rgba(0,0,0,.1); margin-bottom:20px; display:flex; justify-content:space-between; align-items:center; }
.top-bar h1 { font-size:22px; color:#1f2937; }
.user-info { display:flex; align-items:center; gap:15px; color:#6b7280; font-size:14px; }
.user-info a { color:#6b7280; text-decoration:none; }
.user-info a:hover { color:#3b82f6; }

/* 统计卡片 */
.stats-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:20px; margin-bottom:30px; }
.stat-card { background:#fff; border-radius:8px; padding:20px; box-shadow:0 1px 3px rgba(0,0,0,.1); }
.stat-card .icon { width:48px; height:48px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:22px; margin-bottom:12px; }
.stat-card.users .icon { background:#dbeafe; color:#3b82f6; }
.stat-card.articles .icon { background:#d1fae5; color:#10b981; }
.stat-card.pigeons .icon { background:#fde68a; color:#f59e0b; }
.stat-card.listings .icon { background:#fce7f3; color:#ec4899; }
.stat-card.races .icon { background:#e0e7ff; color:#6366f1; }
.stat-card.dynamics .icon { background:#ccfbf1; color:#14b8a6; }
.stat-card .value { font-size:28px; font-weight:700; color:#1f2937; }
.stat-card .label { color:#6b7280; font-size:13px; margin-top:4px; }

/* 内容卡片 */
.content-card { background:#fff; border-radius:8px; box-shadow:0 1px 3px rgba(0,0,0,.1); overflow:hidden; margin-bottom:20px; }
.card-header { padding:15px 20px; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center; }
.card-header h3 { font-size:16px; color:#1f2937; }
.card-header a,.card-header .btn { color:#3b82f6; text-decoration:none; font-size:14px; }
.card-body { padding:0; }

/* 数据表格 */
.admin-table { width:100%; border-collapse:collapse; }
.admin-table th { padding:12px 16px; text-align:left; font-weight:600; color:#374151; background:#f9fafb; font-size:13px; border-bottom:1px solid #e5e7eb; white-space:nowrap; }
.admin-table td { padding:12px 16px; border-bottom:1px solid #f3f4f6; font-size:14px; color:#4b5563; }
.admin-table tr:hover td { background:#f9fafb; }
.admin-table .actions { white-space:nowrap; }
.admin-table .actions a,.admin-table .actions button { margin-right:8px; }

/* 按钮 */
.btn { display:inline-block; padding:6px 14px; border-radius:6px; font-size:13px; font-weight:500; cursor:pointer; border:none; text-decoration:none; transition:.3s; }
.btn-primary { background:#3b82f6; color:#fff; }
.btn-primary:hover { background:#2563eb; }
.btn-success { background:#10b981; color:#fff; }
.btn-success:hover { background:#059669; }
.btn-danger { background:#ef4444; color:#fff; }
.btn-danger:hover { background:#dc2626; }
.btn-warning { background:#f59e0b; color:#fff; }
.btn-warning:hover { background:#d97706; }
.btn-secondary { background:#6b7280; color:#fff; }
.btn-secondary:hover { background:#4b5563; }
.btn-sm { padding:4px 10px; font-size:12px; }
.btn-outline { background:transparent; border:1px solid #d1d5db; color:#4b5563; }
.btn-outline:hover { background:#f9fafb; }

/* 状态标签 */
.badge { display:inline-block; padding:2px 8px; border-radius:9999px; font-size:12px; font-weight:500; }
.badge-success { background:#d1fae5; color:#065f46; }
.badge-danger { background:#fee2e2; color:#991b1b; }
.badge-warning { background:#fef3c7; color:#92400e; }
.badge-info { background:#dbeafe; color:#1e40af; }
.badge-secondary { background:#f3f4f6; color:#4b5563; }

/* 分页 */
.pagination { display:flex; gap:5px; margin-top:20px; justify-content:center; }
.pagination a,.pagination span { padding:8px 12px; border:1px solid #d1d5db; border-radius:6px; font-size:13px; text-decoration:none; color:#4b5563; }
.pagination a:hover { background:#f3f4f6; }
.pagination .active { background:#3b82f6; color:#fff; border-color:#3b82f6; }
.pagination .disabled { opacity:.5; cursor:not-allowed; }

/* 搜索栏 */
.search-bar { display:flex; gap:10px; margin-bottom:20px; }
.search-bar input { padding:8px 12px; border:1px solid #d1d5db; border-radius:6px; font-size:14px; flex:1; }
.search-bar input:focus { outline:none; border-color:#3b82f6; }

/* 筛选栏 */
.filter-bar { display:flex; gap:10px; margin-bottom:20px; flex-wrap:wrap; align-items:center; }
.filter-bar a { padding:6px 14px; border-radius:6px; font-size:13px; text-decoration:none; color:#4b5563; border:1px solid #d1d5db; }
.filter-bar a:hover,.filter-bar a.active { background:#3b82f6; color:#fff; border-color:#3b82f6; }

/* 模态框 */
.modal-overlay { display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,.5); z-index:1000; justify-content:center; align-items:center; }
.modal-overlay.active { display:flex; }
.modal { background:#fff; border-radius:12px; width:90%; max-width:600px; max-height:90vh; overflow-y:auto; }
.modal-header { padding:20px; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center; }
.modal-header h3 { font-size:18px; color:#1f2937; }
.modal-close { background:none; border:none; font-size:20px; cursor:pointer; color:#6b7280; }
.modal-body { padding:20px; }
.modal-footer { padding:15px 20px; border-top:1px solid #e5e7eb; display:flex; justify-content:flex-end; gap:10px; }

/* 表单 */
.form-group { margin-bottom:16px; }
.form-label { display:block; margin-bottom:6px; font-weight:600; color:#374151; font-size:14px; }
.form-control { width:100%; padding:8px 12px; border:1px solid #d1d5db; border-radius:6px; font-size:14px; transition:.3s; }
.form-control:focus { outline:none; border-color:#3b82f6; box-shadow:0 0 0 3px rgba(59,130,246,.1); }
select.form-control { appearance:auto; }
textarea.form-control { min-height:100px; resize:vertical; }
.form-hint { font-size:12px; color:#9ca3af; margin-top:4px; }

/* 空状态 */
.empty-state { text-align:center; padding:60px 20px; color:#9ca3af; }
.empty-state i { font-size:48px; margin-bottom:15px; }
.empty-state p { font-size:16px; }

/* 数据列表 */
.data-list { list-style:none; }
.data-list li { padding:12px 20px; border-bottom:1px solid #f3f4f6; display:flex; justify-content:space-between; align-items:center; }
.data-list li:last-child { border-bottom:none; }
.data-list .item-info { flex:1; }
.data-list .item-title { color:#1f2937; font-weight:500; margin-bottom:3px; }
.data-list .item-meta { color:#9ca3af; font-size:13px; }

/* 响应式 */
@media (max-width:1024px) {
    .sidebar { transform:translateX(-100%); transition:transform .3s; }
    .sidebar.active { transform:translateX(0); }
    .main-content { margin-left:0; }
    .menu-toggle { display:block !important; }
}
.menu-toggle { display:none; background:none; border:none; font-size:20px; cursor:pointer; color:#1f2937; }

/* 确认对话框 */
.confirm-dialog { display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:#fff; padding:30px; border-radius:12px; box-shadow:0 20px 60px rgba(0,0,0,.2); z-index:2000; text-align:center; min-width:300px; }
.confirm-dialog.active { display:block; }
.confirm-dialog p { margin-bottom:20px; font-size:16px; color:#1f2937; }
.confirm-dialog .btn { min-width:80px; }

/* Toast提示 */
.toast { position:fixed; top:20px; right:20px; padding:12px 20px; border-radius:8px; color:#fff; font-size:14px; z-index:3000; opacity:0; transform:translateY(-10px); transition:.3s; }
.toast.show { opacity:1; transform:translateY(0); }
.toast.success { background:#10b981; }
.toast.error { background:#ef4444; }
</style>
