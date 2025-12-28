<?php
session_start();
require "config/database.php";

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

</body>
</html>
