<?php
$currentPage = basename($_SERVER['PHP_SELF']);
require_once __DIR__ . '/../config/base_url.php';
?>

<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <button class="sidebar-toggle d-md-none" onclick="toggleSidebar()">
            <i class="fa-solid fa-bars"></i>
        </button>

        <div class="logo">
            <img src="<?= $BASE_URL ?>../assets/logo-tranzio.png" alt="Tranzio">
        </div>
    </div>

    <ul class="sidebar-menu">
        <li>
            <a href="<?= $BASE_URL ?>/admin/dashboard.php"
               class="<?= $currentPage == 'dashboard.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-house"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <li>
            <a href="<?= $BASE_URL ?>/admin/users/index.php"
               class="<?= str_contains($_SERVER['REQUEST_URI'], '/users/') ? 'active' : '' ?>">
                <i class="fa-solid fa-users"></i>
                <span>User</span>
            </a>
        </li>

        <li>
            <a href="<?= $BASE_URL ?>/admin/armada/index.php"
               class="<?= str_contains($_SERVER['REQUEST_URI'], '/armada/') ? 'active' : '' ?>">
                <i class="fa-solid fa-bus-side"></i>
                <span>Armada</span>
            </a>
        </li>

        <li>
            <a href="<?= $BASE_URL ?>/admin/rute/index.php"
               class="<?= str_contains($_SERVER['REQUEST_URI'], '/rute/') ? 'active' : '' ?>">
                <i class="fa-solid fa-map-location-dot"></i>
                <span>Rute</span>
            </a>
        </li>

        <li>
            <a href="<?= $BASE_URL ?>/admin/jadwal/index.php"
               class="<?= str_contains($_SERVER['REQUEST_URI'], '/jadwal/') ? 'active' : '' ?>">
                <i class="fa-solid fa-calendar-days"></i>
                <span>Jadwal</span>
            </a>
        </li>

        <li>
            <a href="<?= $BASE_URL ?>/admin/blog/index.php"
               class="<?= str_contains($_SERVER['REQUEST_URI'], '/blog/') ? 'active' : '' ?>">
                <i class="fa-solid fa-newspaper"></i>
                <span>Blog</span>
            </a>
        </li>

        <li>
            <a href="<?= $BASE_URL ?>/konfirmasi_refund.php"
               class="<?= str_contains($_SERVER['REQUEST_URI'], 'konfirmasi_refund.php') ? 'active' : '' ?>">
                <i class="fa-solid fa-money-bill-transfer"></i>
                <span>Refund</span>
            </a>
        </li>

        <li class="menu-divider"></li>

        <li>
            <a href="<?= $BASE_URL ?>/auth/logout.php" class="logout">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Logout</span>
            </a>
        </li>
    </ul>
</div>

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('expanded');
}
</script>
