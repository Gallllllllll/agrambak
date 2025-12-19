<?php
session_start();
require "../config/database.php";

if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['reservasi_id'])) {
    die("Reservasi tidak ditemukan.");
}

$reservasi_id = $_GET['reservasi_id'];

// Ambil data reservasi
$stmt = $pdo->prepare("SELECT * FROM reservasi WHERE reservasi_id = ?");
$stmt->execute([$reservasi_id]);
$reservasi = $stmt->fetch();
if (!$reservasi) die("Reservasi tidak ditemukan.");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload Pembayaran</title>
</head>
<body>
<h2>Upload Bukti Pembayaran untuk Kode Booking <?= $reservasi['kode_booking'] ?></h2>

<form action="proses_upload_pembayaran.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="reservasi_id" value="<?= $reservasi_id ?>">
    <input type="file" name="bukti" required><br><br>
    <button type="submit">Upload</button>
</form>
</body>
</html>
