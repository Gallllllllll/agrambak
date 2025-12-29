<?php
// NAVBAR REUSABLE
// Pastikan session sudah dimulai, jika belum, mulai session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ambil user login
$userLogin = $_SESSION['user'] ?? null;

// Base URL proyek
$baseUrl = (
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http'
) . '://' . $_SERVER['HTTP_HOST']
  . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');

// Default
$profileImg = null;
$userName = 'U';

// Jika user login, ambil data dari database
if ($userLogin && isset($userLogin['user_id'])) {
    require_once __DIR__ . '/../config/database.php';

    $stmt = $pdo->prepare("SELECT foto, nama FROM users WHERE user_id = ?");
    $stmt->execute([$userLogin['user_id']]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($userData) {
        $profileImg = $userData['foto'] ?: null;
        $userName = $userData['nama'] ?: 'U';
    }
}

// Jika foto tidak ada, pakai default avatar
$profileImgPath = $profileImg ? $baseUrl.'/../uploads/'.htmlspecialchars($profileImg) 
                              : $baseUrl.'/../aset/icon/profile.png';

?>
<html>
<nav class="navbar">
    <div class="logo">
        <!-- Gunakan path absolut -->
        <img src="<?= $baseUrl ?>/../aset/img/logo-tranzio.png" alt="Tranzio">
    </div>

    <!-- MENU DESKTOP -->
    <ul class="menu desktop-menu">
        <li><a href="<?= $baseUrl ?>/dashboard.php">Dashboard</a></li>
        <li><a href="<?= $baseUrl ?>/status_pemesanan.php">Tiket Saya</a></li>
        <li><a href="<?= $baseUrl ?>/../tentangkami.php">Tentang Kami</a></li>
        <li><a href="<?= $baseUrl ?>/../auth/logout.php">Logout</a></li>
    </ul>

    <!-- PROFILE DESKTOP -->
    <div class="profile desktop-profile">
        <a href="<?= $baseUrl ?>/akun/index.php">
            <img src="<?= $profileImgPath ?>" alt="Foto Profil User">
        </a>
    </div>

    <div class="burger" id="burger">
        <span></span>
        <span></span>
        <span></span>
    </div>
</nav>

<!-- OFFCANVAS MENU (MOBILE) -->
<div class="side-menu" id="sideMenu">
    <div class="side-profile">
        <img src="<?= $profileImgPath ?>" alt="Foto Profil User">
        <p>My Account</p>
    </div>

    <ul>
        <li><a href="<?= $baseUrl ?>/dashboard.php">Dashboard</a></li>
        <li><a href="<?= $baseUrl ?>/status_pemesanan.php">Tiket Saya</a></li>
        <li><a href="#">Tentang Kami</a></li>
        <li><a href="<?= $baseUrl ?>/akun/index.php">Profil</a></li>
        <li><a href="<?= $baseUrl ?>/../auth/logout.php">Logout</a></li>
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
</html>