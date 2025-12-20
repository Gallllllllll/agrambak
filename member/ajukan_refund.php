<?php
session_start();
require "../config/database.php";

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];

if (!isset($_GET['reservasi_id'])) die("Reservasi tidak ditemukan");

$reservasi_id = $_GET['reservasi_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $alasan = $_POST['alasan'] ?? '';
    if (!$alasan) die("Alasan harus diisi");

    $stmt = $pdo->prepare("
        INSERT INTO pembatalan (reservasi_id, user_id, alasan, status, waktu_ajukan)
        VALUES (?, ?, ?, 'pending', NOW())
    ");
    $stmt->execute([$reservasi_id, $user['id'], $alasan]);

    echo "Permintaan refund berhasil diajukan. Menunggu konfirmasi admin.";
    exit;
}
?>

<form method="POST">
    <label>Alasan Refund:</label><br>
    <textarea name="alasan" required></textarea><br><br>
    <button type="submit">Ajukan Refund</button>
</form>
