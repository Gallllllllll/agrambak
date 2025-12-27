<?php
// Navbar reusable
// Jangan pakai session_start() di sini
$userLogin = $_SESSION['user'] ?? null;

// Base URL proyek, sesuaikan dengan struktur folder kamu
$baseUrl = '/UAS/bus-ticket'; // misal: http://localhost/UAS/bus-ticket

// Ambil foto user jika login
$profileImg = null;
$userName = 'U';
if ($userLogin) {
    require_once __DIR__ . '/../config/database.php'; // path aman dari partials
    $stmt = $pdo->prepare("SELECT foto, nama FROM users WHERE user_id = ?");
    $stmt->execute([$userLogin['user_id']]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($userData) {
        $profileImg = $userData['foto'] ?? null;
        $userName = $userData['nama'] ?? 'U';
    }
}
?>

<style>
.navbar {
    background: #dbe5f1;
    padding: 12px 20px;
}
.navbar-container {
    max-width: 1100px;
    margin: auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.navbar-logo {
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: bold;
    font-size: 20px; /* diperbesar */
    color: #1b2b4b;
}
.navbar-logo img {
    height: 48px; /* diperbesar */
}
.navbar-menu {
    display: flex;
    gap: 25px;
    align-items: center;
}
.navbar-menu a {
    text-decoration: none;
    color: #1b2b4b;
    font-weight: 500;
}
.navbar-profile {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    cursor: pointer;
    overflow: hidden;
}
.navbar-profile img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
/* ===== MOBILE ===== */
.menu-toggle {
    display: none;
    font-size: 22px;
    cursor: pointer;
}
@media (max-width: 768px) {
    .menu-toggle { display: block; }
    .navbar-menu {
        position: absolute;
        top: 65px;
        left: 0;
        right: 0;
        background: #dbe5f1;
        flex-direction: column;
        gap: 15px;
        padding: 15px;
        display: none;
    }
    .navbar-menu.active { display: flex; }
}
</style>

<nav class="navbar">
    <div class="navbar-container">
        <div class="navbar-logo">
            <img src="<?= $baseUrl ?>/assets/logo-tranzio.png" alt="Tranzio">
        </div>

        <div class="menu-toggle" onclick="toggleMenu()">☰</div>

        <div class="navbar-menu" id="navbarMenu">
            <a href="<?= $baseUrl ?>/member/dashboard.php">Home</a>
            <a href="<?= $baseUrl ?>/tentang_kami.php">Tentang Kami</a>

            <?php if ($userLogin): ?>
                <a href="<?= $baseUrl ?>/member/status_pemesanan.php">Tiket Saya</a>
                <a href="<?= $baseUrl ?>/akun/index.php" class="navbar-profile">
                    <?php if ($profileImg): ?>
                        <img src="<?= $baseUrl ?>/uploads/<?= htmlspecialchars($profileImg) ?>" alt="Foto Profil">
                    <?php else: ?>
                        <?= strtoupper(substr($userName,0,1)) ?>
                    <?php endif; ?>
                </a>
            <?php else: ?>
                <a href="<?= $baseUrl ?>/login.php">Login</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<script>
function toggleMenu() {
    document.getElementById('navbarMenu').classList.toggle('active');
}
</script>
