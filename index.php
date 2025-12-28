<?php
session_start();

// Jika sudah login, langsung arahkan ke dashboard member
if (isset($_SESSION['user']) && is_array($_SESSION['user'])) {
    header("Location: member/dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Home</title>
    <link rel="stylesheet" href="aset/css/dashboard.css">
    <link rel="icon" href="aset/img/logo-tranzio2.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css">
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
<?php include "navguest.php"; ?>
<!-- HERO SLIDER -->
<section class="hero">
    <div class="slides">
        <img src="aset/img/hero1.png" class="active">
        <img src="aset/img/hero2.png">
        <img src="aset/img/hero3.png">
    </div>
    <div class="dots">
        <span class="active"></span>
        <span></span>
        <span></span>
    </div>
</section>

<!-- BERITA -->
<section class="blog-news">
    <div class="container">

        <div class="news-header">
            <h2>Blog & News</h2>

            <div class="news-nav">
                <button class="news-btn" id="newsPrev">‹</button>
                <button class="news-btn" id="newsNext">›</button>
            </div>
        </div>

        <div class="news-container" id="newsContainer">
            <div class="news-track">

                <div class="news-card">
                    <img src="aset/img/news1.jpg" alt="">
                    <h3>Promo Natal dan Tahun Baru 2025</h3>
                </div>

                <div class="news-card">
                    <img src="gambar/news2.jpg" alt="">
                    <h3>Benefit Tambahan dengan Keanggotaan RI Plus</h3>
                </div>

                <div class="news-card">
                    <img src="gambar/news3.jpg" alt="">
                    <h3>Pilihan Kelas Armada dengan Fasilitas Lengkap</h3>
                </div>

                <div class="news-card">
                    <img src="gambar/news4.jpg" alt="">
                    <h3>Inovasi Layanan Digital untuk Penumpang</h3>
                </div>

                <div class="news-card">
                    <img src="gambar/news4.jpg" alt="">
                    <h3>Inovasi Layanan Digital untuk Penumpang</h3>
                </div>

                <div class="news-card">
                    <img src="gambar/news4.jpg" alt="">
                    <h3>Inovasi Layanan Digital untuk Penumpang</h3>
                </div>

            </div>
        </div>

    </div>
</section>


<?php include "footerguest.php"; ?>

<script src="aset/js/dashboard.js"></script>

</body>
</html>
