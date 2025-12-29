<?php
session_start();
require "config/database.php";
$page_type = 'public';

// Jika sudah login, langsung arahkan ke dashboard member
if (isset($_SESSION['user']) && is_array($_SESSION['user'])) {
    header("Location: member/dashboard.php");
    exit;
}

$blogs = $pdo->query("
    SELECT blog_id, judul, gambar, created_at
    FROM blog
    ORDER BY created_at DESC
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

// ================= DATA FASILITAS ARMADA =================
$tipeArmada = $pdo->query("
    SELECT tipe_id, nama_tipe, deskripsi
    FROM armada_tipe
    ORDER BY tipe_id
")->fetchAll(PDO::FETCH_ASSOC);

$fasilitasData = [];

foreach ($tipeArmada as $t) {

    // Foto fasilitas
    $stmtFoto = $pdo->prepare("
        SELECT foto 
        FROM armada_tipe_foto 
        WHERE tipe_id = ?
    ");
    $stmtFoto->execute([$t['tipe_id']]);
    $fotos = $stmtFoto->fetchAll(PDO::FETCH_COLUMN);

    // Fasilitas + icon
    $stmtFas = $pdo->prepare("
        SELECT f.nama_fasilitas, f.icon
        FROM armada_tipe_fasilitas atf
        JOIN fasilitas f ON atf.fasilitas_id = f.fasilitas_id
        WHERE atf.tipe_id = ?
    ");
    $stmtFas->execute([$t['tipe_id']]);
    $fasilitas = $stmtFas->fetchAll(PDO::FETCH_ASSOC);

    $fasilitasData[$t['tipe_id']] = [
        'tipe' => $t,
        'foto' => $fotos,
        'fasilitas' => $fasilitas
    ];
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
    <style>
        .fasilitas {
            background: #f8f9fb;
            padding: 60px 20px;
            margin: 70px 0;
            box-shadow: 0 8px 20px rgba(0,0,0,0.25);
            border-top: 5px gradien #253562ff;
        }

        .fasilitas .title {
            text-align: center;
            margin-bottom: 30px;
        }

        .fasilitas-nav {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 30px;
            background: #222e5aff;
            border-radius: 30px;
            padding: 20px;
        }

        .fasilitas-tab {
            padding: 10px 25px;
            border-radius: 20px;
            border: none;
            background: #ddd;
            cursor: pointer;
            font-weight: bold;
        }

        .fasilitas-tab.active {
            background: #7eade3ff;
            color: #fff;
        }

        .fasilitas-content {
            display: none;
            max-width: 900px;
            margin: auto;
        }

        .fasilitas-content.active {
            display: block;
            align-items: center;
        }

        .fasilitasSwiper img {
            width: 100%;
            border-radius: 20px;
        }

        .deskripsi {
            text-align: center;
            margin: 20px 0;
            color: #555;
        }

        .fasilitas-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px,1fr));
            gap: 12px;
        }

        .fasilitas-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .fasilitas-item i {
            align-items: center;
            color: #222e5aff;
            font-size: 18px;
        }
        /* ================= CARA RESERVASI ================= */
        .cara-reservasi {
            padding: 70px 30px;
            margin: 0 30px 70px;
            background: #ffffff;
            border-radius: 40px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }

        .cara-reservasi .title {
            text-align: center;
            margin-bottom: 40px;
        }

        .reservasi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 25px;
            max-width: 1000px;
            margin: auto;
        }

        .reservasi-card {
            background: #f8f9fb;
            border-radius: 25px;
            padding: 30px 20px;
            text-align: center;
            position: relative;
            transition: all 0.3s ease;
            cursor: default;
        }

        .reservasi-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.25);
        }

        .reservasi-card i {
            font-size: 40px;
            color: #222e5aff;
            margin-bottom: 15px;
        }

        .reservasi-card h3 {
            margin-bottom: 10px;
            color: #222;
        }

        .reservasi-card p {
            color: #555;
            font-size: 14px;
        }

        /* Nomor langkah */
        .step-number {
            position: absolute;
            top: -15px;
            left: -15px;
            background: #7eade3ff;
            color: #fff;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 18px;
        }

    </style>
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

<!-- ================= FASILITAS ARMADA ================= -->
<section class="fasilitas">
    <div class="container">

        <h1 class="title">Fasilitas Armada Kami</h1>

        <!-- NAV TAB -->
        <div class="fasilitas-nav">
            <?php foreach ($tipeArmada as $i => $t): ?>
                <button 
                    class="fasilitas-tab <?= $i === 0 ? 'active' : '' ?>"
                    data-target="tipe<?= $t['tipe_id'] ?>">
                    <?= htmlspecialchars($t['nama_tipe']) ?>
                </button>
            <?php endforeach; ?>
        </div>

        <!-- CONTENT -->
        <?php foreach ($fasilitasData as $id => $data): ?>
        <div class="fasilitas-content <?= $id === $tipeArmada[0]['tipe_id'] ? 'active' : '' ?>" 
             id="tipe<?= $id ?>">

            <!-- SLIDER FOTO -->
            <div class="swiper fasilitasSwiper">
                <div class="swiper-wrapper">
                    <?php foreach ($data['foto'] as $foto): ?>
                        <div class="swiper-slide">
                            <img src="uploads/fasilitas/<?= $foto ?>">
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
            </div>

            <!-- DESKRIPSI -->
            <p class="deskripsi">
                <?= htmlspecialchars($data['tipe']['deskripsi']) ?>
            </p>

            <!-- LIST FASILITAS -->
            <div class="fasilitas-list">
                <?php foreach ($data['fasilitas'] as $f): ?>
                    <div class="fasilitas-item">
                        <i class="fa-solid <?= $f['icon'] ?>"></i>
                        <span><?= $f['nama_fasilitas'] ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

        </div>
        <?php endforeach; ?>

    </div>
</section>

<!-- ================= CARA RESERVASI ================= -->
<section class="cara-reservasi">
    <div class="container">
        <h1 class="title">Cara Reservasi</h1>

        <div class="reservasi-grid">

            <div class="reservasi-card">
                <div class="step-number">1</div>
                <i class="fa-solid fa-magnifying-glass"></i>
                <h3>Cari Jadwal</h3>
                <p>Pilih rute, tanggal keberangkatan, dan armada yang tersedia.</p>
            </div>

            <div class="reservasi-card">
                <div class="step-number">2</div>
                <i class="fa-solid fa-chair"></i>
                <h3>Pilih Kursi</h3>
                <p>Tentukan kursi sesuai keinginan dan jumlah penumpang.</p>
            </div>

            <div class="reservasi-card">
                <div class="step-number">3</div>
                <i class="fa-solid fa-credit-card"></i>
                <h3>Lakukan Pembayaran</h3>
                <p>Selesaikan pembayaran dengan metode yang tersedia.</p>
            </div>

            <div class="reservasi-card">
                <div class="step-number">4</div>
                <i class="fa-solid fa-ticket"></i>
                <h3>Cetak Tiket</h3>
                <p>Tiket siap digunakan dan dapat dicetak atau disimpan.</p>
            </div>

        </div>
    </div>
</section>

<!-- ================= BLOG & NEWS ================= -->
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

                <?php if (count($blogs) === 0): ?>
                    <p style="padding:20px;">Belum ada artikel.</p>
                <?php endif; ?>

                <?php foreach ($blogs as $b): ?>
                    <div class="news-card">
                        <a href="member/detail_blog.php?blog_id=<?= $b['blog_id'] ?>" style="text-decoration:none;color:inherit;">

                            <?php if (!empty($b['gambar'])): ?>
                                <img src="uploads/<?= htmlspecialchars($b['gambar']) ?>" alt="Blog">
                            <?php else: ?>
                                <img src="aset/img/news-default.jpg" alt="Blog">
                            <?php endif; ?>

                            <h3><?= htmlspecialchars($b['judul']) ?></h3>
                            <small style="color:#777;">
                                <?= date('d M Y', strtotime($b['created_at'])) ?>
                            </small>

                        </a>
                    </div>
                <?php endforeach; ?>

            </div>
        </div>

    </div>
</section>

<?php include "footerguest.php"; ?>

<script src="aset/js/dashboard.js"></script>
<script>
document.querySelectorAll('.fasilitas-tab').forEach(tab => {
    tab.addEventListener('click', () => {

        document.querySelectorAll('.fasilitas-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.fasilitas-content').forEach(c => c.classList.remove('active'));

        tab.classList.add('active');
        document.getElementById(tab.dataset.target).classList.add('active');
    });
});

// Init swiper
document.querySelectorAll('.fasilitasSwiper').forEach(swiperEl => {
    new Swiper(swiperEl, {
        loop: true,
        navigation: {
            nextEl: swiperEl.querySelector('.swiper-button-next'),
            prevEl: swiperEl.querySelector('.swiper-button-prev'),
        }
    });
});
</script>


</body>
</html>
