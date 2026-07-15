<?php
/**
 * 信鸽之家 - 用户中心侧边栏
 * 
 * 使用方式: 在需要侧边栏的页面引入
 * require_once __DIR__ . "/sidebar.php";
 * 
 * 参数:
 *   $active_page - 当前激活的菜单项 (home/articles/pigeons/listings/pairings/orders/membership/claims/profile/password)
 */
$active = $active_page ?? "";
?>
<style>
    /* 用户侧边栏 */
    .user-sidebar {
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        padding: 0;
        height: fit-content;
        position: sticky;
        top: 90px;
        overflow: hidden;
    }

    .user-sidebar .sidebar-menu {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .user-sidebar .sidebar-menu li {
        border-bottom: 1px solid var(--gray-100);
    }

    .user-sidebar .sidebar-menu li:last-child {
        border-bottom: none;
    }

    .user-sidebar .sidebar-menu a {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 15px 20px;
        color: var(--gray-700);
        transition: all 0.3s;
    }

    .user-sidebar .sidebar-menu a:hover {
        background-color: var(--primary);
        color: white;
    }

    .user-sidebar .sidebar-menu a.active {
        background-color: var(--primary);
        color: white;
    }

    .user-sidebar .sidebar-menu i {
        width: 20px;
        text-align: center;
    }

    .content-with-sidebar {
        display: grid;
        grid-template-columns: 250px 1fr;
        gap: 30px;
    }

    @media (max-width: 768px) {
        .content-with-sidebar {
            grid-template-columns: 1fr;
        }
    }
</style>
<aside class="user-sidebar">
    <ul class="sidebar-menu">
        <li>
            <a href="/" <?php echo $active === "home" ? "class=\"active\"" : ""; ?>>
                <i class="fas fa-home"></i>
                <span>仪表盘</span>
            </a>
        </li>
        <li>
            <a href="/user/my_articles" <?php echo $active === "articles" ? "class=\"active\"" : ""; ?>>
                <i class="fas fa-newspaper"></i>
                <span>我的文章</span>
            </a>
        </li>
        <li>
            <a href="/user/my_pigeons" <?php echo $active === "pigeons" ? "class=\"active\"" : ""; ?>>
                <i class="fas fa-dove"></i>
                <span>我的铭鸽</span>
            </a>
        </li>
        <li>
            <a href="/user/my_listings" <?php echo $active === "listings" ? "class=\"active\"" : ""; ?>>
                <i class="fas fa-list"></i>
                <span>我的发布</span>
            </a>
        </li>
        <li>
            <a href="/pedigree/pairings/" <?php echo $active === "pairings" ? "class=\"active\"" : ""; ?>>
                <i class="fas fa-heart"></i>
                <span>我的配对</span>
            </a>
        </li>
        <li>
            <a href="/pay/?action=orders" <?php echo $active === "orders" ? "class=\"active\"" : ""; ?>>
                <i class="fas fa-receipt"></i>
                <span>我的订单</span>
            </a>
        </li>
        <li>
            <a href="/user/membership" <?php echo $active === "membership" ? "class=\"active\"" : ""; ?>>
                <i class="fas fa-crown"></i>
                <span>会员中心</span>
            </a>
        </li>
        <li>
            <a href="/claim?action=my_claims" <?php echo $active === "claims" ? "class=\"active\"" : ""; ?>>
                <i class="fas fa-hand-holding-heart"></i>
                <span>我的认领</span>
            </a>
        </li>
        <li>
            <a href="/user/edit_profile" <?php echo $active === "profile" ? "class=\"active\"" : ""; ?>>
                <i class="fas fa-user-edit"></i>
                <span>编辑资料</span>
            </a>
        </li>
        <li>
            <a href="/user/change_password" <?php echo $active === "password" ? "class=\"active\"" : ""; ?>>
                <i class="fas fa-key"></i>
                <span>修改密码</span>
            </a>
        </li>
        <li>
            <a href="/logout">
                <i class="fas fa-sign-out-alt"></i>
                <span>退出登录</span>
            </a>
        </li>
    </ul>
</aside>
