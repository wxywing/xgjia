<?php
/**
 * 信鸽之家 - 统一导航栏（B方案）
 * 所有页面在 <body> 后引用：<?php include __DIR__ . '/_head.php'; ?>
 * 链接格式：nginx rewrite 后的 clean URL（/pigeon/ 等）
 */
?>

<div style="--primary:#1a5fa8;--primary-dark:#154360;--accent:#c9a84c;--accent-light:#fef9e7;--white:#ffffff;--bg:#f4f6f9;--bg-dark:#1e2a3a;--text:#2c3e50;--text-light:#6c7a89;--text-muted:#95a5b8;--text-lighter:#b0bec5;--border:#e8ecf0;--border-light:#f0f3f6;--shadow:0 2px 12px rgba(26,95,168,0.08);--shadow-hover:0 8px 30px rgba(26,95,168,0.12);--radius:12px;--radius-sm:8px;--primary-color:#1a5fa8;--secondary-color:#c9a84c;">
<style>
/* 导航栏赛事下拉菜单 */
.navbar-inner { overflow: visible; }
.nav-dropdown { position: relative; }
.nav-dropdown::after { content: ''; position: absolute; top: 100%; left: 0; width: 100%; height: 10px; z-index: 1; }
.nav-dropdown-menu { display: none; position: absolute; top: calc(100% + 8px); left: 50%; transform: translateX(-50%); background: #fff; border-radius: 10px; box-shadow: 0 8px 30px rgba(0,0,0,0.15); min-width: 160px; padding: 8px 0; z-index: 1100; }
.nav-dropdown:hover .nav-dropdown-menu { display: block; }
.nav-dropdown-menu a { display: flex; align-items: center; gap: 8px; padding: 10px 18px; color: var(--text); font-size: 14px; font-weight: 400; text-decoration: none; white-space: nowrap; transition: background .15s; }
.nav-dropdown-menu a:hover { background: #f0f4ff; color: var(--primary); }
.nav-dropdown-menu a i { width: 18px; text-align: center; color: var(--text-muted); }
.nav-dropdown-menu a:hover i { color: var(--primary); }
@media (max-width: 768px) {
  .nav-dropdown-menu { display: none !important; }
}
</style>
<nav class="navbar">
    <div class="navbar-inner">
        <a href="/" class="nav-logo"><i class="fas fa-dove"></i><span>信鸽<small style="font-size:0.55em;vertical-align:middle;">之</small>家</span></a>
        <ul class="nav-links">
            <li><a href="/" <?php echo $_SERVER['REQUEST_URI'] == '/' ? 'class="active"' : ''; ?>>首页</a></li>
            
            
            <li class="nav-dropdown">
                <a href="/race/" class="nav-dropdown-toggle" <?php echo strpos($_SERVER['REQUEST_URI'], '/race') !== false ? 'style="color:var(--primary);border-bottom:2px solid var(--primary);"' : ''; ?>>赛事成绩 <i class="fas fa-angle-down"></i></a>
                <div class="nav-dropdown-menu">
                    <a href="/race/"><i class="fas fa-list"></i> 全部赛事</a>
                    <a href="/race/browse/"><i class="fas fa-th-list"></i> 赛事大全</a>
                    <a href="/race/season/2026/"><i class="fas fa-chart-bar"></i> 赛季总结</a>
                    <a href="/race/champion/"><i class="fas fa-crown"></i> 冠军鸽</a>
                    <a href="/race/city/"><i class="fas fa-city"></i> 城市赛事</a>
                    <a href="/race/province/"><i class="fas fa-map-marked-alt"></i> 省份聚合</a>
                </div>
            </li>
            <li class="nav-dropdown">
                <a href="/pigeon/" class="nav-dropdown-toggle" <?php echo strpos($_SERVER['REQUEST_URI'], '/pigeon') !== false ? 'style="color:var(--primary);border-bottom:2px solid var(--primary);"' : ''; ?>>铭鸽展厅 <i class="fas fa-angle-down"></i></a>
                <div class="nav-dropdown-menu">
                    <a href="/pigeon/"><i class="fas fa-dove"></i> 全部铭鸽</a>
                    <a href="/shop/"><i class="fas fa-store"></i> 全部展厅</a>
                </div>
            </li>
            <li><a href="/loft/" <?php echo strpos($_SERVER['REQUEST_URI'], '/loft') !== false ? 'class="active"' : ''; ?>>公棚大全</a></li>
            <li class="nav-dropdown">
                <a href="/pedigree/" class="nav-dropdown-toggle" <?php echo strpos($_SERVER['REQUEST_URI'], '/pedigree') !== false ? 'style="color:var(--primary);border-bottom:2px solid var(--primary);"' : ''; ?>>血统证书 <i class="fas fa-angle-down"></i></a>
                <div class="nav-dropdown-menu">
                    <a href="/pedigree/"><i class="fas fa-dna"></i> 品系大全</a>
                    <a href="/pedigree/certificate/"><i class="fas fa-certificate"></i> 血统证书</a>
                </div>
            </li>

        </ul>
        <div class="nav-actions">
           
            <?php if (isset($_SESSION['user_id'])): ?>
            <a href="/user" style="font-size:14px;color:var(--text);text-decoration:none;">
                <i class="fas fa-user-circle" style="font-size:20px;margin-right:4px;color:var(--primary);"></i>
                <?php echo h($_SESSION['username']); ?>
            </a>
            <a href="/auth?action=logout" class="btn btn-outline">退出</a>
            <?php else: ?>
            <a href="/auth?action=login" class="btn btn-outline">登录</a>
            <a href="/auth?action=register" class="btn btn-primary">注册</a>
            <?php endif; ?>
        </div>
        <button class="hamburger" onclick="toggleMobileNav()"><i class="fas fa-bars"></i></button>
    </div>
</nav>

<div class="mobile-nav" id="mobileNav">
    <div class="mobile-nav-panel">
        <div class="mobile-nav-header">
            <a href="/" class="nav-logo"><i class="fas fa-dove"></i><span>信鸽<small style="font-size:0.55em;vertical-align:middle;">之</small>家</span></a>
            <button class="mobile-nav-close" onclick="toggleMobileNav()"><i class="fas fa-times"></i></button>
        </div>
        <div class="mobile-nav-links">
            <a href="/" <?php echo $_SERVER['REQUEST_URI'] == '/' ? 'class="active"' : ''; ?>>首页</a>
            
            <a href="/race/" <?php echo strpos($_SERVER['REQUEST_URI'], '/race') !== false ? 'class="active"' : ''; ?>>赛事成绩</a>
            <a href="/race/browse/" style="font-size:13px;padding-left:32px;color:var(--text-muted);border-bottom:none;"><i class="fas fa-th-list" style="font-size:11px;"></i> 赛事大全</a>
            <a href="/race/season/2026/" style="font-size:13px;padding-left:32px;color:var(--text-muted);border-bottom:none;"><i class="fas fa-chart-bar" style="font-size:11px;"></i> 赛季总结</a>
            <a href="/race/champion/" style="font-size:13px;padding-left:32px;color:var(--text-muted);border-bottom:none;"><i class="fas fa-crown" style="font-size:11px;"></i> 冠军鸽</a>
            <a href="/race/city/" style="font-size:13px;padding-left:32px;color:var(--text-muted);border-bottom:none;"><i class="fas fa-city" style="font-size:11px;"></i> 城市赛事</a>
            <a href="/race/province/" style="font-size:13px;padding-left:32px;color:var(--text-muted);"><i class="fas fa-map-marked-alt" style="font-size:11px;"></i> 省份聚合</a>
            <a href="/pigeon/" <?php echo strpos($_SERVER['REQUEST_URI'], '/pigeon') !== false ? 'class="active"' : ''; ?>>铭鸽展厅</a>
            <a href="/shop/" style="font-size:13px;padding-left:32px;color:var(--text-muted);border-bottom:none;"><i class="fas fa-store" style="font-size:11px;"></i> 全部展厅</a>
            <a href="/loft/" <?php echo strpos($_SERVER['REQUEST_URI'], '/loft') !== false ? 'class="active"' : ''; ?>>公棚大全</a>
            <a href="/pedigree/" <?php echo strpos($_SERVER['REQUEST_URI'], '/pedigree') !== false ? 'class="active"' : ''; ?>>血统品系</a>
            <a href="/pedigree/certificate/" style="font-size:13px;padding-left:32px;color:var(--text-muted);border-bottom:none;"><i class="fas fa-certificate" style="font-size:11px;"></i> 血统证书</a>
        </div>
        <div class="mobile-nav-actions">
            <?php if (isset($_SESSION['user_id'])): ?>
            <a href="/user" class="btn btn-outline" style="width:100%;justify-content:center;margin-bottom:10px;"><i class="fas fa-user"></i> 我的</a>
            <a href="/auth?action=logout" class="btn btn-primary" style="width:100%;justify-content:center;">退出</a>
            <?php else: ?>
            <a href="/auth?action=login" class="btn btn-outline" style="width:100%;justify-content:center;margin-bottom:10px;">登录</a>
            <a href="/auth?action=register" class="btn btn-primary" style="width:100%;justify-content:center;">注册</a>
            <?php endif; ?>
        </div>
    </div>
</div>
</div>

<script>
function toggleMobileNav() {
    var nav = document.getElementById('mobileNav');
    nav.classList.toggle('active');
    document.body.style.overflow = nav.classList.contains('active') ? 'hidden' : '';
}
document.getElementById('mobileNav').addEventListener('click', function(e) {
    if (e.target === this) toggleMobileNav();
});
</script>