<?php
session_start();
require "../config/database.php";

if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION["user"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $jadwal_id = $_POST['jadwal_id'];
    $kursi = $_POST['kursi'] ?? [];

    if (empty($kursi)) {
        die("Pilih minimal 1 kursi.");
    }

    $jumlah_kursi = count($kursi);

    // Ambil harga dari jadwal
    $stmt = $pdo->prepare("SELECT harga FROM jadwal WHERE jadwal_id = ?");
    $stmt->execute([$jadwal_id]);
    $jadwal = $stmt->fetch();
    if (!$jadwal) die("Jadwal tidak ditemukan.");

    $total_harga = $jumlah_kursi * $jadwal['harga'];

    try {
        $pdo->beginTransaction();

        // Insert ke reservasi
        $stmt = $pdo->prepare("INSERT INTO reservasi (user_id, jadwal_id, kode_booking, jumlah_kursi, total_harga, status, waktu_pesan) VALUES (?, ?, ?, ?, ?, 'pending', NOW())");
        $kode_booking = "BKS" . rand(1000,9999);
        $stmt->execute([$user['id'], $jadwal_id, $kode_booking, $jumlah_kursi, $total_harga]);

        $reservasi_id = $pdo->lastInsertId();

        // Insert penumpang & seat_booking
        foreach ($kursi as $seat) {
            // Insert penumpang
            $stmt = $pdo->prepare("INSERT INTO penumpang (reservasi_id, nama_penumpang, nomor_kursi) VALUES (?, ?, ?)");
            $stmt->execute([$reservasi_id, "Penumpang $seat", $seat]);
            $penumpang_id = $pdo->lastInsertId();

            // Insert seat_booking
            $stmt = $pdo->prepare("INSERT INTO seat_booking (jadwal_id, nomor_kursi, penumpang_id, status) VALUES (?, ?, ?, 'booked')");
            $stmt->execute([$jadwal_id, $seat, $penumpang_id]);
        }

        $pdo->commit();

        // Redirect ke form upload pembayaran
        header("Location: upload_pembayaran_form.php?reservasi_id=$reservasi_id");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        die("Gagal memproses booking: " . $e->getMessage());
    }
}
?>
