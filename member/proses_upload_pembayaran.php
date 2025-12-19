<?php
session_start();
require "../config/database.php";

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['reservasi_id'])) {
        die("Reservasi tidak ditemukan.");
    }

    $reservasi_id = $_POST['reservasi_id'];

    if (!isset($_FILES['bukti']) || $_FILES['bukti']['error'] != 0) {
        die("File bukti transfer belum diupload.");
    }

    $file_tmp = $_FILES['bukti']['tmp_name'];
    $file_name = basename($_FILES['bukti']['name']);
    $upload_dir = "../uploads/";

    // Pastikan folder uploads ada
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $target_file = $upload_dir . $file_name;

    if (move_uploaded_file($file_tmp, $target_file)) {
        // Simpan data pembayaran
        $stmt = $pdo->prepare("
            INSERT INTO pembayaran (reservasi_id, bukti_transfer, status, waktu_bayar) 
            VALUES (?, ?, 'pending', NOW())
        ");
        try {
            $stmt->execute([$reservasi_id, $file_name]);

            // Update status reservasi jadi menunggu konfirmasi
            $stmt2 = $pdo->prepare("UPDATE reservasi SET status = 'pending' WHERE reservasi_id = ?");
            $stmt2->execute([$reservasi_id]);

            // Redirect ke halaman status pemesanan
            header("Location: status_pemesanan.php");
            exit;

        } catch (PDOException $e) {
            echo "Gagal memproses pembayaran: " . $e->getMessage();
        }
    } else {
        echo "Upload gagal!";
    }
} else {
    echo "Metode tidak valid.";
}
