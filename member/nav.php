<nav class="navbar">
    <!-- BURGER DI KIRI -->
    

    <div class="logo">
        <img src="../aset/img/logo-tranzio.png" alt="Tranzio">
    </div>

    <!-- MENU DESKTOP -->
    <ul class="menu desktop-menu">
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="status_pemesanan.php">Tiket Saya</a></li>
        <li><a href="#">Tentang Kami</a></li>
        <li><a href="../auth/logout.php">Logout</a></li>
    </ul>

    <!-- PROFILE DESKTOP -->
    <div class="profile desktop-profile">
        <a href="user.php">
            <img src="../aset/icon/profile.png" alt="User">
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
        <img src="../aset/icon/profile.png" alt="User">
        <p>My Account</p>
    </div>

    <ul>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="status_pemesanan.php">Tiket Saya</a></li>
        <li><a href="#">Tentang Kami</a></li>
        <li><a href="user.php">Profil</a></li>
        <li><a href="../auth/logout.php">Logout</a></li>
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
