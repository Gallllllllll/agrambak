<?php
// NAVBAR GUEST REUSABLE
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Base URL proyek (samakan dengan nav member)
$baseUrl = (
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http'
) . '://' . $_SERVER['HTTP_HOST']
  . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
?>

<nav class="navbar">
    <div class="logo">
        <img src="<?= $baseUrl ?>/aset/img/logo-tranzio.png" alt="Tranzio">
    </div>

    <!-- MENU DESKTOP -->
    <ul class="menu desktop-menu">
        <li><a href="<?= $baseUrl ?>/index.php">Dashboard</a></li>
        <li><a href=#>Tentang Kami</a></li>
        <li><a href="<?= $baseUrl ?>/auth/login.php">Login</a></li>
        <li><a href="<?= $baseUrl ?>/auth/register.php">Register</a></li>
    </ul>

    <!-- BURGER MENU -->
    <div class="burger" id="burger">
        <span></span>
        <span></span>
        <span></span>
    </div>
</nav>

<!-- OFFCANVAS MENU (MOBILE) -->
<div class="side-menu" id="sideMenu">
    <ul>
        <li><a href="<?= $baseUrl ?>/index.php">Dashboard</a></li>
        <li><a href=#>Tentang Kami</a></li>
        <li><a href="<?= $baseUrl ?>/auth/login.php">Login</a></li>
        <li><a href="<?= $baseUrl ?>/auth/register.php">Register</a></li>
    </ul>
</div>

<!-- OVERLAY -->
<div class="overlay" id="overlay"></div>

<script>
const burger = document.getElementById("burger");
const sideMenu = document.getElementById("sideMenu");
const overlay = document.getElementById("overlay");

burger.addEventListener("click", () => {
    sideMenu.classList.add("active");
    overlay.classList.add("active");
});

overlay.addEventListener("click", () => {
    sideMenu.classList.remove("active");
    overlay.classList.remove("active");
});
</script>
