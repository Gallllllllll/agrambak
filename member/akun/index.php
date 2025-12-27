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
    <title>Profil Saya</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .profile { max-width: 400px; margin: auto; }
        .profile img { max-width: 150px; display: block; margin-bottom: 10px; }
        .profile div { margin-bottom: 8px; }
        .btn-edit { display: inline-block; padding: 5px 10px; background:#3498db; color:#fff; text-decoration:none; border-radius:5px; }
    </style>
</head>
<body>
<div class="profile">
    <h2>Profil Saya</h2>

    <?php if (!empty($user['foto'])): ?>
        <img src="../../uploads/<?= htmlspecialchars($user['foto']) ?>" alt="Foto Profil">
    <?php else: ?>
        <div>Belum ada foto</div>
    <?php endif; ?>

    <div><strong>Nama:</strong> <?= htmlspecialchars($user['nama']) ?></div>
    <div><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></div>
    <div><strong>Telepon:</strong> <?= htmlspecialchars($user['telepon'] ?? '-') ?></div>
    <div><strong>Alamat:</strong> <?= htmlspecialchars($user['alamat'] ?? '-') ?></div>

    <a href="edit.php" class="btn-edit">Edit Profil</a>
</div>
</body>
</html>
