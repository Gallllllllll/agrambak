<?php
session_start();
require "../config/database.php";

/* ===============================
   CEK LOGIN PETUGAS / USER
================================ */
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

/* ===============================
   CEK METHOD
================================ */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Metode tidak valid.");
}

/* ===============================
   AMBIL INPUT
================================ */
$reservasi_id = $_POST['reservasi_id'] ?? null;

if (!$reservasi_id) {
    die("Reservasi tidak ditemukan.");
}

try {
    $pdo->beginTransaction();

    /* ===============================
       AMBIL DATA RESERVASI + PEMBAYARAN
    ================================ */
    $stmt = $pdo->prepare("
        SELECT r.status AS r_status, p.status AS p_status
        FROM reservasi r
        LEFT JOIN pembayaran p ON p.reservasi_id = r.reservasi_id
        WHERE r.reservasi_id = ?
        FOR UPDATE
    ");
    $stmt->execute([$reservasi_id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        throw new Exception("Reservasi tidak ditemukan.");
    }

    if ($data['p_status'] !== 'berhasil') {
        throw new Exception("Pembayaran belum valid.");
    }

    if ($data['r_status'] !== 'Dipesan') {
        throw new Exception("Reservasi sudah check-in atau dibatalkan.");
    }

    /* ===============================
       UPDATE STATUS RESERVASI
    ================================ */
    $stmt = $pdo->prepare("
        UPDATE reservasi 
        SET status = 'Check-In'
        WHERE reservasi_id = ?
    ");
    $stmt->execute([$reservasi_id]);

    /* ===============================
       UPDATE STATUS KURSI → terisi
    ================================ */
    $stmt = $pdo->prepare("
        UPDATE seat_booking
        SET status = 'terisi'
        WHERE reservasi_id = ?
    ");
    $stmt->execute([$reservasi_id]);

    $pdo->commit();

    echo "Check-in berhasil!";

} catch (Exception $e) {
    $pdo->rollBack();
    die("Gagal check-in: " . $e->getMessage());
}
