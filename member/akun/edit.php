<?php
session_start();
require "../../config/database.php";

if (!isset($_SESSION['user'])) {
    echo "User belum login.";
    exit;
}

// Tentukan user_id dari session
if (is_array($_SESSION['user']) && isset($_SESSION['user']['id'])) {
    $user_id = $_SESSION['user']['id'];
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

// Folder untuk upload
$uploadsDir = '../../uploads/';

// Proses form submit
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'] ?? '';
    $email = $_POST['email'] ?? '';
    $telepon = $_POST['telepon'] ?? '';
    $alamat = $_POST['alamat'] ?? '';

    // Handle upload foto
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['foto']['tmp_name'];
        $fileName = basename($_FILES['foto']['name']);
        $targetPath = $uploadsDir . $fileName;

        // Pindahkan file ke folder uploads
        if (move_uploaded_file($fileTmp, $targetPath)) {
            $fotoDb = $fileName;
        } else {
            $fotoDb = $user['foto']; // pakai foto lama kalau gagal
        }
    } else {
        $fotoDb = $user['foto']; // pakai foto lama kalau tidak upload
    }

    // Update database
    $stmt = $pdo->prepare("UPDATE users SET nama = ?, email = ?, telepon = ?, alamat = ?, foto = ? WHERE user_id = ?");
    $stmt->execute([$nama, $email, $telepon, $alamat, $fotoDb, $user_id]);

    $message = 'Profil berhasil diperbarui.';
    // Refresh data
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Profil</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .profile { max-width: 400px; margin: auto; }
        .profile img { max-width: 150px; display: block; margin-bottom: 10px; border-radius: 5px; }
        .profile div { margin-bottom: 8px; }
        .btn-save { display: inline-block; padding: 5px 10px; background:#27ae60; color:#fff; text-decoration:none; border-radius:5px; border:none; cursor:pointer; }
        .message { color: green; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="profile">
        <h2>Edit Profil</h2>

        <?php
        $fotoPath = $uploadsDir . ($user['foto'] ?? '');
        if (!empty($user['foto']) && file_exists($fotoPath)) {
            $fotoToShow = $fotoPath;
        } else {
            $fotoToShow = $uploadsDir . 'default.png';
        }
        ?>
        <img src="<?= htmlspecialchars($fotoToShow) ?>" alt="Foto Profil">

        <?php if ($message): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <div>
                <label>Nama:</label><br>
                <input type="text" name="nama" value="<?= htmlspecialchars($user['nama']) ?>" required>
            </div>
            <div>
                <label>Email:</label><br>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>
            <div>
                <label>Telepon:</label><br>
                <input type="text" name="telepon" value="<?= htmlspecialchars($user['telepon'] ?? '') ?>">
            </div>
            <div>
                <label>Alamat:</label><br>
                <textarea name="alamat"><?= htmlspecialchars($user['alamat'] ?? '') ?></textarea>
            </div>
            <div>
                <label>Foto Profil:</label><br>
                <input type="file" name="foto" accept="image/*">
            </div>
            <div style="margin-top:10px;">
                <button type="submit" class="btn-save">Simpan</button>
            </div>
        </form>
    </div>
</body>
</html>
