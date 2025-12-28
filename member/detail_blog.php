<?php
require "../config/database.php";

$blog_id = $_GET['blog_id'] ?? null;
if (!$blog_id) {
    die("Blog tidak ditemukan.");
}

$stmt = $pdo->prepare("
    SELECT b.*, u.nama AS penulis
    FROM blog b
    JOIN users u ON b.user_id = u.user_id
    WHERE b.blog_id = ?
");
$stmt->execute([$blog_id]);
$blog = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$blog) {
    die("Blog tidak ditemukan.");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($blog['judul']) ?></title>

    <link rel="stylesheet" href="../aset/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        .blog-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .blog-header h1 {
            font-size: 32px;
            margin-bottom: 10px;
            color: #222;
        }

        .blog-meta {
            color: #777;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .blog-image img {
            width: 100%;
            border-radius: 10px;
            margin-bottom: 25px;
        }

        .blog-content {
            font-size: 16px;
            line-height: 1.8;
            color: #333;
            text-align: justify;
        }

        .btn-back {
            display: inline-block;
            margin-top: 30px;
            text-decoration: none;
            background: #4e73df;
            color: #fff;
            padding: 10px 18px;
            border-radius: 6px;
        }

        .btn-back:hover {
            background: #224abe;
        }

        @media (max-width: 768px) {
            .blog-header h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>

<?php include "nav.php"; ?>

<div class="blog-container">

    <div class="blog-header">
        <h1><?= htmlspecialchars($blog['judul']) ?></h1>
        <div class="blog-meta">
            <i class="fa-regular fa-user"></i> <?= htmlspecialchars($blog['penulis']) ?>
            &nbsp; | &nbsp;
            <i class="fa-regular fa-calendar"></i>
            <?= date('d M Y', strtotime($blog['created_at'])) ?>
        </div>
    </div>

    <?php if ($blog['gambar']): ?>
        <div class="blog-image">
            <img src="../uploads/<?= htmlspecialchars($blog['gambar']) ?>" alt="Gambar Blog">
        </div>
    <?php endif; ?>

    <div class="blog-content">
        <?= nl2br(htmlspecialchars($blog['konten'])) ?>
    </div>

    <a href="dashboard.php" class="btn-back">
        ← Kembali ke Dashboard
    </a>

</div>

<?php include "footer.php"; ?>

</body>
</html>
