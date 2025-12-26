<?php
session_start();
require "../config/database.php";

/* ===============================
   CEK LOGIN
================================ */
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];

/* ===============================
   CEK METHOD
================================ */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Metode tidak valid.");
}

/* ===============================
   INPUT
================================ */
$reservasi_id = $_POST['reservasi_id'] ?? null;
$metode = $_POST['metode'] ?? 'transfer';

if (!$reservasi_id) {
    die("Reservasi tidak ditemukan.");
}

/* ===============================
   VALIDASI FILE
================================ */
if (!isset($_FILES['bukti']) || $_FILES['bukti']['error'] !== 0) {
    die("Bukti pembayaran wajib diupload.");
}

$allowed_ext = ['jpg','jpeg','png','pdf'];
$file_ext = strtolower(pathinfo($_FILES['bukti']['name'], PATHINFO_EXTENSION));

if (!in_array($file_ext, $allowed_ext)) {
    die("Format file tidak diperbolehkan.");
}

$upload_dir = "../uploads/";
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$file_name = uniqid('bukti_') . '.' . $file_ext;
$file_path = $upload_dir . $file_name;

if (!move_uploaded_file($_FILES['bukti']['tmp_name'], $file_path)) {
    die("Gagal upload file.");
}

try {
    $pdo->beginTransaction();

    /* ===============================
       CEK RESERVASI USER
    ================================ */
    $stmt = $pdo->prepare("
        SELECT total_harga, status
        FROM reservasi
        WHERE reservasi_id = ? AND user_id = ?
    ");
    $stmt->execute([$reservasi_id, $user['id']]);
    $reservasi = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reservasi) {
        throw new Exception("Reservasi tidak ditemukan.");
    }

    if ($reservasi['status'] !== 'Dipesan') {
        throw new Exception("Reservasi sudah check-in atau dibatalkan.");
    }

    /* ===============================
       CEK PEMBAYARAN GANDA
    ================================ */
    $cek = $pdo->prepare("
        SELECT COUNT(*)
        FROM pembayaran
        WHERE reservasi_id = ?
    ");
    $cek->execute([$reservasi_id]);

    if ($cek->fetchColumn() > 0) {
        throw new Exception("Pembayaran sudah dilakukan.");
    }

    /* ===============================
       INSERT PEMBAYARAN (AUTO BERHASIL)
    ================================ */
    $stmt = $pdo->prepare("
        INSERT INTO pembayaran
        (reservasi_id, metode, jumlah, bukti_transfer, status, waktu_bayar)
        VALUES (?, ?, ?, ?, 'berhasil', NOW())
    ");
    $stmt->execute([
        $reservasi_id,
        $metode,
        (int)$reservasi['total_harga'],
        $file_name
    ]);

    $pdo->commit();

    header("Location: status_pemesanan.php?success=1");
    exit;

} catch (Exception $e) {
    $pdo->rollBack();

    if (file_exists($file_path)) {
        unlink($file_path);
    }

    die("Gagal memproses pembayaran: " . $e->getMessage());
}
