<?php
$currentUri = $_SERVER['REQUEST_URI'];
$currentPage = basename($_SERVER['PHP_SELF']);
require_once __DIR__ . '/../config/base_url.php';
?>

<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <button class="sidebar-toggle d-md-none" onclick="toggleSidebar()">
            <i class="fa-solid fa-bars"></i>
        </button>

        <div class="logo">
            <img src="<?= $BASE_URL ?>assets/logo-tranzio.png" alt="Tranzio">
        </div>
    </div>


<ul class="sidebar-menu">
    <li>
        <a href="<?= $BASE_ADMIN_URL ?>dashboard.php" class="<?= strpos($currentUri, '/admin/dashboard.php') !== false ? 'active' : '' ?>">
            <i class="fa-solid fa-house"></i>
            <span>Dashboard</span>
        </a>
    </li>

    <li>
        <a href="<?= $BASE_ADMIN_URL ?>users/index.php" class="<?= strpos($currentUri, '/admin/users/') !== false ? 'active' : '' ?>">
            <i class="fa-solid fa-users"></i>
            <span>User</span>
        </a>
    </li>

    <li>
        <a href="<?= $BASE_ADMIN_URL ?>armada/index.php" class="<?= strpos($currentUri, '/admin/armada/') !== false ? 'active' : '' ?>">
            <i class="fa-solid fa-bus-side"></i>
            <span>Armada</span>
        </a>
    </li>

    <li>
        <a href="<?= $BASE_ADMIN_URL ?>rute/index.php" class="<?= strpos($currentUri, '/admin/rute/') !== false ? 'active' : '' ?>">
            <i class="fa-solid fa-map-location-dot"></i>
            <span>Rute</span>
        </a>
    </li>

    <li>
        <a href="<?= $BASE_ADMIN_URL ?>jadwal/index.php" class="<?= strpos($currentUri, '/admin/jadwal/') !== false ? 'active' : '' ?>">
            <i class="fa-solid fa-calendar-days"></i>
            <span>Jadwal</span>
        </a>
    </li>

    <li>
        <a href="<?= $BASE_ADMIN_URL ?>konfirmasi_refund.php" class="<?= strpos($currentUri, '/admin/konfirmasi_refund.php') !== false ? 'active' : '' ?>">
            <i class="fa-solid fa-money-bill-transfer"></i>
            <span>Refund</span>
        </a>
    </li>

    <li>
        <a href="<?= $BASE_ADMIN_URL ?>reservasi.php" class="<?= strpos($currentUri, '/admin/konfirmasi_refund.php') !== false ? 'active' : '' ?>">
            <i class="fa-solid fa-list"></i>
            <span>Riwayat Reservasi</span>
        </a>
    </li>

    <li>
        <a href="<?= $BASE_ADMIN_URL ?>blog/index.php" class="<?= str_contains($currentUri, '/blog/') ? 'active' : '' ?>">
            <i class="fa-solid fa-newspaper"></i>
            <span>Blog</span>
        </a>
    </li>

    <li class="menu-divider"></li>

    <li>
        <a href="<?= $BASE_URL ?>auth/logout.php" class="logout">
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
