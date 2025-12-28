<?php
session_start();
require "../config/database.php";

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];

if (!isset($_GET['reservasi_id'])) {
    die("Reservasi tidak ditemukan.");
}

$reservasi_id = $_GET['reservasi_id'];

// Ambil data reservasi + pembayaran terakhir
$stmt = $pdo->prepare("
    SELECT r.*, j.tanggal, j.jam_berangkat, j.jam_tiba, b.nama_bus, p.status AS pembayaran_status
    FROM reservasi r
    JOIN jadwal j ON r.jadwal_id = j.jadwal_id
    JOIN bus_armada b ON j.armada_id = b.armada_id
    LEFT JOIN pembayaran p ON r.reservasi_id = p.reservasi_id
        AND p.payment_id = (
            SELECT MAX(payment_id) FROM pembayaran WHERE reservasi_id = r.reservasi_id
        )
    WHERE r.reservasi_id = ? AND r.user_id = ?
");
$stmt->execute([$reservasi_id, $user['user_id']]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) die("Data tidak ditemukan.");
if ($data['pembayaran_status'] !== 'berhasil') die("Pembayaran belum LUNAS, tiket tidak bisa dicetak.");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tiket Bus - <?= htmlspecialchars($data['kode_booking']) ?></title>
    <style>
        body { font-family: Arial, sans-serif; }
        .tiket { border: 2px dashed #333; padding: 20px; width: 400px; margin: 50px auto; }
        h2 { text-align: center; }
        p { margin: 5px 0; }
        .kode { font-size: 20px; font-weight: bold; text-align: center; }
        button { margin-top: 20px; padding: 5px 10px; cursor: pointer; }
    </style>
</head>
<body>

<div class="tiket">
    <h2>Tiket Bus</h2>
    <p class="kode">Kode Booking: <?= htmlspecialchars($data['kode_booking']) ?></p>
    <hr>
    <p><b>Nama Bus:</b> <?= htmlspecialchars($data['nama_bus']) ?></p>
    <p><b>Tanggal:</b> <?= htmlspecialchars($data['tanggal']) ?></p>
    <p><b>Jam Berangkat:</b> <?= htmlspecialchars($data['jam_berangkat']) ?></p>
    <p><b>Jam Tiba:</b> <?= htmlspecialchars($data['jam_tiba']) ?></p>
    <p><b>Jumlah Kursi:</b> <?= $data['jumlah_kursi'] ?></p>
    <p><b>Total Harga:</b> Rp<?= number_format($data['total_harga']) ?></p>
    <hr>
    <p style="text-align:center;">Silakan tunjukkan tiket ini saat naik bus</p>
    <div style="text-align:center;">
        <button onclick="window.print()">🖨️ Cetak Tiket</button>
        
    </div>
    <div style="text-align:center;">
        <button onclick="window.location.href='status_pemesanan.php'">← Kembali</button>
    </div>
</div>

</body>
</html>
