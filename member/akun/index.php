<?php
session_start();
require "../../config/database.php";

if (!isset($_SESSION['user'])) {
    echo "User belum login.";
    exit;
}

// Tentukan user_id dari session
if (is_array($_SESSION['user']) && isset($_SESSION['user']['user_id'])) {
    $user_id = $_SESSION['user']['user_id'];
} elseif (is_numeric($_SESSION['user'])) {
    $user_id = $_SESSION['user'];
} else {
    echo "User tidak ditemukan.";
    exit;
}

// Ambil data user
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "User tidak ditemukan.";
    exit;
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

        <form action="upload_foto.php" method="post" enctype="multipart/form-data">
            <label for="foto">Unggah Foto:</label><br>
            <input type="file" name="foto" id="foto" accept="image/*" required><br><br>
            <button type="submit">Simpan Foto</button>
        </form>
    </div>
</body>
</html>
