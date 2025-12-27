<?php
session_start();
require "../../config/database.php";

// Cek login
if (!isset($_SESSION['user']) || !is_array($_SESSION['user'])) {
    header("Location: ../../login.php");
    exit;
}

// Ambil user_id dari session
$user_id = $_SESSION['user']['user_id'] ?? null;
if (!$user_id) {
    die("User tidak ditemukan di session.");
}

// Ambil data user dari database
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User tidak ditemukan di database.");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profil Saya</title>
<style>
* { box-sizing: border-box; }

body {
    margin: 0;
    font-family: 'Segoe UI', sans-serif;
    background: #2f405a;
    color: #333;
}

/* Container full-width */
.container {
    max-width: 900px;
    margin: 30px auto;
    padding: 20px;
}

/* Profil utama */
.profile-header {
    display: flex;
    flex-direction: column;
    align-items: center;
    background: #fff;
    padding: 30px;
    border-radius: 20px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    margin-bottom: 20px;
}

.profile-header img {
    width: 140px;
    height: 140px;
    border-radius: 50%;
    object-fit: cover;
    margin-bottom: 15px;
}

.profile-header .initial {
    width: 140px;
    height: 140px;
    border-radius: 50%;
    background: #3498db;
    color: white;
    font-size: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 15px;
}

.profile-header h2 {
    margin: 0 0 10px;
    color: #1b2b4b;
}

/* Card informasi */
.info-card {
    background: #fff;
    padding: 20px 25px;
    border-radius: 20px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    margin-bottom: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.info-card strong {
    font-weight: 600;
    color: #1b2b4b;
}

.info-card span {
    color: #555;
    font-size: 14px;
}

/* Tombol edit */
.btn-edit {
    display: inline-block;
    padding: 10px 20px;
    margin-top: 10px;
    background:#2d9cdb;
    color:#fff;
    text-decoration:none;
    border-radius:10px;
    font-size: 14px;
    text-align: center;
}

/* Responsive */
@media (max-width: 600px) {
    .info-card {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }
}
</style>
</head>
<body>

<?php include "../../partials/navbar.php"; ?>

<div class="container">

    <!-- Profil utama -->
    <div class="profile-header">
        <?php if (!empty($user['foto'])): ?>
            <img src="../../uploads/<?= htmlspecialchars($user['foto']) ?>" alt="Foto Profil">
        <?php else: ?>
            <div class="initial"><?= strtoupper(substr($user['nama'],0,1)) ?></div>
        <?php endif; ?>

        <h2><?= htmlspecialchars($user['nama']) ?></h2>
        <a href="edit.php" class="btn-edit">Edit Profil</a>
    </div>

    <!-- Informasi Profil -->
    <div class="info-card">
        <strong>Email</strong>
        <span><?= htmlspecialchars($user['email']) ?></span>
    </div>

    <div class="info-card">
        <strong>Telepon</strong>
        <span><?= htmlspecialchars($user['telepon'] ?? '-') ?></span>
    </div>

    <div class="info-card">
        <strong>Alamat</strong>
        <span><?= htmlspecialchars($user['alamat'] ?? '-') ?></span>
    </div>

</div>

</body>
</html>
