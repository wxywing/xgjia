<?php
/**
 * 管理后台 - 共用侧边栏和头部
 * 通过 include 使用，不单独访问
 */
?>
<!-- 侧边栏 -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <a href="/admin.php" class="sidebar-brand">
            <i class="fas fa-dove"></i>
            <span>信鸽之家</span>
        </a>
    </div>
    <nav class="sidebar-nav">
        <a href="/admin.php" class="nav-item <?php echo ($activeMenu ?? '') === 'dashboard' ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt"></i><span>仪表盘</span>
        </a>
        <a href="/admin.php?action=users" class="nav-item <?php echo ($activeMenu ?? '') === 'users' ? 'active' : ''; ?>">
            <i class="fas fa-users"></i><span>用户管理</span>
        </a>
        <a href="/admin.php?action=articles" class="nav-item <?php echo ($activeMenu ?? '') === 'articles' ? 'active' : ''; ?>">
            <i class="fas fa-newspaper"></i><span>文章管理</span>
        </a>
        <a href="/admin.php?action=pigeons" class="nav-item <?php echo ($activeMenu ?? '') === 'pigeons' ? 'active' : ''; ?>">
            <i class="fas fa-dove"></i><span>铭鸽管理</span>
        </a>
        <a href="/admin.php?action=listings" class="nav-item <?php echo ($activeMenu ?? '') === 'listings' ? 'active' : ''; ?>">
            <i class="fas fa-list-alt"></i><span>分类信息</span>
        </a>
        <a href="/admin.php?action=lofts" class="nav-item <?php echo ($activeMenu ?? '') === 'lofts' ? 'active' : ''; ?>">
            <i class="fas fa-building"></i><span>公棚管理</span>
        </a>
        <a href="/admin.php?action=races" class="nav-item <?php echo ($activeMenu ?? '') === 'races' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-alt"></i><span>赛事管理</span>
        </a>
        <a href="/admin.php?action=dynamics" class="nav-item <?php echo ($activeMenu ?? '') === 'dynamics' ? 'active' : ''; ?>">
            <i class="fas fa-comments"></i><span>动态管理</span>
        </a>
        <a href="/admin.php?action=orders" class="nav-item <?php echo ($activeMenu ?? '') === 'orders' ? 'active' : ''; ?>">
            <i class="fas fa-receipt"></i><span>订单管理</span>
        </a>
        <a href="/admin.php?action=claims" class="nav-item <?php echo ($activeMenu ?? '') === 'claims' ? 'active' : ''; ?>">
            <i class="fas fa-hand-holding-heart"></i><span>认领管理</span>
        </a>
        <a href="/admin.php?action=ads" class="nav-item <?php echo ($activeMenu ?? '') === 'ads' ? 'active' : ''; ?>">
            <i class="fas fa-bullhorn"></i><span>广告管理</span>
        </a>
        <a href="/admin.php?action=settings" class="nav-item <?php echo ($activeMenu ?? '') === 'settings' ? 'active' : ''; ?>">
            <i class="fas fa-cog"></i><span>系统设置</span>
        </a>
        <a href="/" class="nav-item">
            <i class="fas fa-external-link-alt"></i><span>访问前台</span>
        </a>
    </nav>
</aside>

