<?php
/**
 * 管理后台 - 顶部工具栏
 * 通过 include 使用，放在 <main class="main-content"> 内部开头
 */
?>
<!-- 顶部工具栏 -->
<div class="top-bar">
    <div style="display:flex;align-items:center;gap:15px;">
        <button class="menu-toggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
        <h1><?php echo h($pageTitle ?? '管理后台'); ?></h1>
    </div>
    <div class="user-info">
        <span><i class="fas fa-user-shield mr-1"></i><?php echo h($_SESSION['username'] ?? '管理员'); ?></span>
        <a href="/logout"><i class="fas fa-sign-out-alt"></i> 退出</a>
    </div>
</div>