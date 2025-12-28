<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>


<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <button class="sidebar-toggle d-md-none" onclick="toggleSidebar()">
            <i class="fa-solid fa-bars"></i>
        </button>

        <div class="logo">
            <img src="/agrambak/assets/logo-tranzio.png" alt="Tranzio">
        </div>
    </div>

    <ul class="sidebar-menu">
        <li>
            <a href="/agrambak/admin/dashboard.php" class="<?= $currentPage == 'dashboard.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-house"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <li>
            <a href="/agrambak/admin/users/index.php" class="<?= in_array($currentPage, ['index.php','edit.php','detail.php']) ? 'active' : '' ?>">
                <i class="fa-solid fa-users"></i>
                <span>User</span>
            </a>
        </li>

        <li>
            <a href="/agrambak/admin/armada/index.php" class="<?= str_contains($currentPage, '/armada/index.php') ? 'active' : '' ?>">
                <i class="fa-solid fa-bus-side"></i>
                <span>Armada</span>
            </a>
        </li>

        <li>
            <a href="/agrambak/admin/rute/index.php" class="<?= str_contains($currentPage, '/rute/index.php') ? 'active' : '' ?>">
                <i class="fa-solid fa-map-location-dot"></i>
                <span>Rute</span>
            </a>
        </li>

        <li>
            <a href="/agrambak/admin/jadwal/index.php" class="<?= str_contains($currentPage, '/jadwal/index.php') ? 'active' : '' ?>">
                <i class="fa-solid fa-calendar-days"></i>
                <span>Jadwal</span>
            </a>
        </li>

        <li>
            <a href="/agrambak/admin/konfirmasi_refund.php" class="<?= str_contains($currentPage, '/konfirmasi_refund.php') ? 'active' : '' ?>">
                <i class="fa-solid fa-money-bill-transfer"></i>
                <span>Refund</span>
            </a>
        </li>

        <li class="menu-divider"></li>

        <li>
            <a href="/agrambak/auth/logout.php" class="logout">
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
