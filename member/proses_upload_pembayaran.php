<?php
session_start();
require "../config/database.php";

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reservasi_id = $_POST['reservasi_id'] ?? null;
    $metode = $_POST['metode'] ?? 'Transfer';

    if (!$reservasi_id) die("Reservasi tidak ditemukan.");

    if (!isset($_FILES['bukti']) || $_FILES['bukti']['error'] != 0) {
        die("File bukti transfer belum diupload.");
    }

    $file_tmp = $_FILES['bukti']['tmp_name'];
    $file_ext = strtolower(pathinfo($_FILES['bukti']['name'], PATHINFO_EXTENSION));
    $allowed_ext = ['jpg','jpeg','png','pdf'];
    if (!in_array($file_ext, $allowed_ext)) die("Format file tidak diperbolehkan.");

    $file_name = uniqid('bukti_') . '.' . $file_ext;
    $upload_dir = "../uploads/";
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
    $target_file = $upload_dir . $file_name;

    if (!move_uploaded_file($file_tmp, $target_file)) die("Upload gagal!");

    try {
        // Ambil total_harga reservasi
        $stmt = $pdo->prepare("SELECT total_harga FROM reservasi WHERE reservasi_id = ? AND user_id = ?");
        $stmt->execute([$reservasi_id, $user['id']]);
        $total = $stmt->fetchColumn();
        if (!$total) die("Reservasi tidak ditemukan atau bukan milik Anda.");

        // Insert pembayaran → pending
        // Insert pembayaran → default 'pending'
        $stmt = $pdo->prepare("
            INSERT INTO pembayaran 
                (reservasi_id, metode, bukti_transfer, status, waktu_bayar) 
            VALUES (?, ?, ?, 'pending', NOW())
        ");
        $stmt->execute([$reservasi_id, $metode, $file_name]);

        // Update status reservasi jadi menunggu konfirmasi
        $stmt2 = $pdo->prepare("UPDATE reservasi SET status = 'pending' WHERE reservasi_id = ?");
        $stmt2->execute([$reservasi_id]);

        header("Location: status_pemesanan.php");
        exit;
    } catch (PDOException $e) {
        die("Gagal memproses pembayaran: " . $e->getMessage());
    }
} else {
    die("Metode tidak valid.");
}
