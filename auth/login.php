<?php
session_start();
require "../config/database.php";

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Metode tidak valid.");
}

$reservasi_id = $_POST['reservasi_id'] ?? null;
$metode = $_POST['metode'] ?? 'transfer'; // HARUS cocok ENUM

if (!$reservasi_id) {
    die("Reservasi tidak ditemukan.");
}

/* ===============================
   VALIDASI FILE UPLOAD
================================ */
if (!isset($_FILES['bukti']) || $_FILES['bukti']['error'] !== 0) {
    die("File bukti transfer belum diupload.");
}

$allowed_ext = ['jpg','jpeg','png','pdf'];
$file_ext = strtolower(pathinfo($_FILES['bukti']['name'], PATHINFO_EXTENSION));

if (!in_array($file_ext, $allowed_ext)) {
    die("Format file tidak diperbolehkan.");
}

$file_name = uniqid('bukti_') . '.' . $file_ext;
$upload_dir = "../uploads/";

if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

if (!move_uploaded_file($_FILES['bukti']['tmp_name'], $upload_dir . $file_name)) {
    die("Upload gagal.");
}

try {
    $pdo->beginTransaction();

    /* ===============================
       AMBIL TOTAL HARGA RESERVASI
    ================================ */
    $stmt = $pdo->prepare("
        SELECT total_harga 
        FROM reservasi 
        WHERE reservasi_id = ? AND user_id = ?
    ");
    $stmt->execute([$reservasi_id, $user['id']]);
    $total = $stmt->fetchColumn();

    if ($total === false) {
        throw new Exception("Reservasi tidak ditemukan.");
    }

    /* ===============================
       CEK APAKAH SUDAH ADA PEMBAYARAN
    ================================ */
    $cek = $pdo->prepare("
        SELECT COUNT(*) 
        FROM pembayaran 
        WHERE reservasi_id = ? AND status = 'pending'
    ");
    $cek->execute([$reservasi_id]);

    if ($cek->fetchColumn() > 0) {
        throw new Exception("Pembayaran sudah pernah diupload, menunggu verifikasi admin.");
    }

    /* ===============================
       INSERT PEMBAYARAN (PENDING)
    ================================ */
    $stmt = $pdo->prepare("
        INSERT INTO pembayaran
        (reservasi_id, metode, jumlah, bukti_transfer, status, waktu_bayar)
        VALUES (?, ?, ?, ?, 'berhasil', NOW())
    ");
    $stmt->execute([
        $reservasi_id,
        $metode,
        (int)$total,
        $file_name
    ]);

    /* ===============================
       UPDATE STATUS RESERVASI
    ================================ */
    $stmt = $pdo->prepare("
        UPDATE reservasi 
        SET status = 'lunas' 
        WHERE reservasi_id = ?
    ");
    $stmt->execute([$reservasi_id]);

    $pdo->commit();

    header("Location: status_pemesanan.php");
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    die("Gagal memproses pembayaran: " . $e->getMessage());
}
