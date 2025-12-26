<?php
session_start();
require "../../config/database.php";

if (!isset($_SESSION['user'])) {
    header("Location: ../../login.php");
    exit;
}

$user_id = $_SESSION['user'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Profil</title>
</head>
<body>
    <h1>Edit Profil</h1>
    <form action="update.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="user_id" value="<?= $user_id ?>">
        <p>
            <label>Nama:</label><br>
            <input type="text" name="nama" value="<?= htmlspecialchars($user['nama']) ?>" required>
        </p>
        <p>
            <label>Telepon:</label><br>
            <input type="text" name="telepon" value="<?= htmlspecialchars($user['telepon']) ?>">
        </p>
        <p>
            <label>Alamat:</label><br>
            <textarea name="alamat"><?= htmlspecialchars($user['alamat']) ?></textarea>
        </p>
        <p>
            <label>Foto Profil:</label><br>
            <input type="file" name="foto">
        </p>
        <p>
            <button type="submit">Simpan</button>
        </p>
    </form>
    <a href="index.php">Kembali</a>
</body>
</html>
