<?php
require "../config/database.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pesan = trim($_POST['pesan']);

    $stmt = $pdo->prepare("
        INSERT INTO kritik_saran (pesan, created_at)
        VALUES (?, NOW())
    ");
    $stmt->execute([$pesan]);

    header("Location: tentangkami.php?success=1");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="stylesheet" href="../aset/css/dashboard.css">
    <link rel="stylesheet" href="../aset/css/tentangkami.css">
    <link rel="icon" href="../aset/img/logo-tranzio2.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css">
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <title>Tentang Kami</title>
</head>
<body>
    <?php include "nav.php"; ?>

    <!-- ABOUT US SECTION -->
     <header class="aboutus-header">
    <div class="judulheader">
        <h1 style="color: white;">PT Tranzio</h1>
        <h3 style="color: white;">
            <i>Your Reliable Travel Partner</i> untuk perjalanan bus antar kota yang aman, nyaman, dan terpercaya.
        </h3>
    </div>
</header>


<div class="visi-misi-section">

    <div class="visi-misi-title">
        <i class="fa-solid fa-star"></i>
        <h2>Visi & Misi</h2>
        <i class="fa-solid fa-star"></i>
    </div>

    <div class="visi-misi-wrapper">

        <!-- Visi Card -->
        <div class="visi-misi-card visi-card">
            <div class="card-header">
                <i class="fa-solid fa-eye"></i>
                <h3>Visi</h3>
            </div>
            <p>
                Menjadi perusahaan transportasi bus yang terpercaya, modern, dan unggul dalam pelayanan perjalanan darat di Indonesia.
            </p>
        </div>

        <!-- Misi Card -->
        <div class="visi-misi-card misi-card">
            <div class="card-header">
                <i class="fa-solid fa-bullseye"></i>
                <h3>Misi</h3>
            </div>
            <ul>
                <li>Menyediakan layanan transportasi yang aman, nyaman, dan tepat waktu</li>
                <li>Mengutamakan kepuasan pelanggan melalui pelayanan profesional</li>
                <li>Mengelola armada dengan standar keselamatan dan perawatan terbaik</li>
                <li>Mengembangkan sistem layanan yang mudah, transparan, dan efisien</li>
                <li>Membangun hubungan jangka panjang dengan pelanggan dan mitra</li>
            </ul>
        </div>

    </div>
</div>


<!-- Layanan Kami -->
<div class="layanan-section">

    <div class="layanan-title">
        <i class="fa-solid fa-bus"></i>
        <h2>Layanan Kami</h2>
        <i class="fa-solid fa-bus"></i>
    </div>

    <div class="layanan-card">
        <ul>
            <li>Layanan Bus AKDP (Antar Kota Dalam Provinsi)</li>
            <li>Layanan Bus AKAP (Antar Kota Antar Provinsi)</li>
            <li>Pemesanan tiket secara online dan offline</li>
            <li>Pilihan kelas armada: Ekonomi, Executive, dan VIP</li>
            <li>Layanan perjalanan reguler dan jarak jauh</li>
        </ul>

        <p>
            Kami terus mengembangkan rute dan layanan untuk menjangkau lebih banyak tujuan di Indonesia.
        </p>
    </div>

</div>


<div class="keunggulan-section">

    <div class="keunggulan-title">
        <i class="fa-solid fa-medal"></i>
        <h2>Keunggulan Kami</h2>
        <i class="fa-solid fa-medal"></i>
    </div>

    <div class="keunggulan-grid">

        <div class="keunggulan-card border-blue">
            <i class="fa-solid fa-bus"></i>
            <h5>Armada Terawat & Nyaman</h5>
        </div>

        <div class="keunggulan-card border-green">
            <i class="fa-solid fa-user-tie"></i>
            <h5>Pengemudi Profesional</h5>
        </div>

        <div class="keunggulan-card border-yellow">
            <i class="fa-solid fa-money-bill-wave"></i>
            <h5>Harga Transparan</h5>
        </div>

        <div class="keunggulan-card border-cyan">
            <i class="fa-solid fa-mouse-pointer"></i>
            <h5>Reservasi Mudah</h5>
        </div>

        <div class="keunggulan-card border-red">
            <i class="fa-solid fa-smile"></i>
            <h5>Pelayanan Responsif</h5>
        </div>

        <div class="keunggulan-card border-dark">
            <i class="fa-solid fa-shield-halved"></i>
            <h5>Keamanan Prioritas</h5>
        </div>

    </div>

</div>

<div class="armada-section">

    <div class="armada-title">
        <i class="fa-solid fa-bus-simple"></i>
        <h2>Armada & Mitra</h2>
        <i class="fa-solid fa-handshake"></i>
    </div>

    <div class="armada-wrapper">

        <div class="armada-card">
            <i class="fa-solid fa-bus"></i>
            <h5>Bus Ekonomi</h5>
            <p>Perjalanan hemat dan tetap nyaman untuk kebutuhan transportasi sehari-hari.</p>
        </div>

        <div class="armada-card">
            <i class="fa-solid fa-couch"></i>
            <h5>Bus Executive</h5>
            <p>Dilengkapi fasilitas tambahan untuk kenyamanan perjalanan jarak jauh.</p>
        </div>

        <div class="armada-card">
            <i class="fa-solid fa-star"></i>
            <h5>Bus VIP</h5>
            <p>Kenyamanan maksimal dengan fasilitas premium bagi penumpang.</p>
        </div>

        <div class="armada-card wide">
            <i class="fa-solid fa-handshake-angle"></i>
            <h5>Mitra Profesional</h5>
            <p>
                Bekerja sama dengan mitra penyedia armada, bengkel perawatan,
                dan pihak terkait untuk memastikan setiap perjalanan aman dan lancar.
            </p>
        </div>

    </div>

</div>


<!-- Komitmen Kami -->
<div class="komitmen-section">

    <div class="komitmen-title">
        <i class="fa-solid fa-hand-holding-heart"></i>
        <h2>Komitmen Kami</h2>
        <i class="fa-solid fa-shield-heart"></i>
    </div>

    <div class="komitmen-wrapper">

        <div class="komitmen-card">
            <i class="fa-solid fa-shield-halved"></i>
            <p>Menjaga keselamatan sebagai prioritas utama</p>
        </div>

        <div class="komitmen-card">
            <i class="fa-solid fa-star"></i>
            <p>Memberikan pelayanan terbaik di setiap perjalanan</p>
        </div>

        <div class="komitmen-card">
            <i class="fa-solid fa-chart-line"></i>
            <p>Terus meningkatkan kualitas armada dan layanan</p>
        </div>

        <div class="komitmen-card">
            <i class="fa-solid fa-comments"></i>
            <p>Mendengarkan masukan pelanggan untuk perbaikan berkelanjutan</p>
        </div>

    </div>

    <div class="komitmen-footer">
        <p>
            Bagi kami, perjalanan bukan sekadar berpindah tempat,
            tetapi tentang pengalaman yang nyaman dan berkesan.
        </p>
    </div>

</div>


<div class="hubungi-section">

    <div class="hubungi-title">
        <i class="fa-solid fa-headset"></i>
        <h2>Hubungi Kami</h2>
        <i class="fa-solid fa-comments"></i>
    </div>

    <div class="hubungi-wrapper">

        <!-- Info Kontak -->
        <div class="contact-card">
            <h3>Informasi Kontak</h3>
            <ul class="contact-info">
                <li>
                    <i class="fa-solid fa-phone"></i>
                    <span>Layanan Pelanggan: +62 821 2263 1172</span>
                </li>
                <li>
                    <i class="fa-solid fa-envelope"></i>
                    <span>Email: tranziobus@gmail.com</span>
                </li>
                <li>
                    <i class="fa-solid fa-location-dot"></i>
                    <span>
                        Alamat Kantor:<br>
                        Jl. Ahmad Yani No 200A, Pabelan, Sukoharjo, Jawa Tengah
                    </span>
                </li>
            </ul>
        </div>

        <!-- Kritik & Saran -->
        <div class="saran-card">
            <h3>Kritik & Saran</h3>
            <p>Sampaikan masukan Anda untuk membantu kami meningkatkan kualitas layanan.</p>

            <form action="tentangkami.php" method="post">
                <textarea 
                    name="pesan" 
                    placeholder="Tulis kritik atau saran Anda di sini..."
                    required
                ></textarea>

                <button type="submit">
                    <i class="fa-solid fa-paper-plane"></i> Kirim Pesan
                </button>
            </form>
        </div>

    </div>

</div>


    

    <?php include "footer.php"; ?>

<script src="aset/js/dashboard.js"></script>
</body>
</html>