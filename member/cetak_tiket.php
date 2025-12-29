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

/* =========================
   AMBIL DATA RESERVASI
========================= */
$stmt = $pdo->prepare("
    SELECT 
        r.*,
        j.tanggal,
        j.jam_berangkat,
        j.jam_tiba,
        ba.nama_bus,

        ta.nama_terminal AS terminal_asal,
        tt.nama_terminal AS terminal_tujuan,

        p.status AS pembayaran_status

    FROM reservasi r
    JOIN jadwal j ON r.jadwal_id = j.jadwal_id
    JOIN bus_armada ba ON j.armada_id = ba.armada_id

    JOIN rute ru ON j.rute_id = ru.rute_id
    JOIN terminal ta ON ru.asal_id = ta.terminal_id
    JOIN terminal tt ON ru.tujuan_id = tt.terminal_id

    LEFT JOIN pembayaran p 
        ON p.reservasi_id = r.reservasi_id
        AND p.payment_id = (
            SELECT MAX(payment_id)
            FROM pembayaran
            WHERE reservasi_id = r.reservasi_id
        )

    WHERE r.reservasi_id = ? AND r.user_id = ?
");
$stmt->execute([$reservasi_id, $user['user_id']]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);
// =========================
// AMBIL NOMOR KURSI
// =========================

$stmtKursi = $pdo->prepare("
    SELECT nomor_kursi
    FROM penumpang
    WHERE reservasi_id = ?
    ORDER BY nomor_kursi
");
$stmtKursi->execute([$reservasi_id]);
$kursi_list = $stmtKursi->fetchAll(PDO::FETCH_COLUMN);


$nomor_kursi = $kursi_list[0] ?? '-';

if (!$data) die("Data tidak ditemukan.");
if ($data['pembayaran_status'] !== 'berhasil') {
    die("Pembayaran belum LUNAS, tiket tidak bisa dicetak.");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Tiket Bus - <?= htmlspecialchars($data['kode_booking']) ?></title>

<style>
body {
    font-family: Arial, sans-serif;
    background: #f4f4f4;
}

.tiket {
    border: 2px solid #333;
    padding: 20px;
    width: 420px;
    margin: 40px auto;
    background: #fff;
    border-radius: 15px;
}

h2 {
    text-align: center;
    margin-bottom: 10px;
}

.kode {
    text-align: center;
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 15px;
}

table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

table td {
    padding: 6px 8px;
    vertical-align: top;
}

table td:first-child {
    width: 45%;
    font-weight: bold;
    color: #333;
}

hr {
    margin: 12px 0;
}

.note {
    text-align: center;
    font-size: 13px;
    margin-top: 10px;
}

.button-group {
    text-align: center;
    margin-top: 15px;
}

button {
    padding: 6px 12px;
    margin: 5px;
    cursor: pointer;
}

@media print {
    button {
        display: none;
    }
}
</style>
</head>

<body>

<div class="tiket">
    <h2>Tiket Bus</h2>
    <div class="kode">Kode Booking: <?= htmlspecialchars($data['kode_booking']) ?></div>

    <table>
        <tr>
            <td>Nama Bus</td>
            <td><?= htmlspecialchars($data['nama_bus']) ?></td>
        </tr>
        <tr>
            <td>Terminal Asal</td>
            <td><?= htmlspecialchars($data['terminal_asal']) ?></td>
        </tr>
        <tr>
            <td>Terminal Tujuan</td>
            <td><?= htmlspecialchars($data['terminal_tujuan']) ?></td>
        </tr>
        <tr>
            <td>Tanggal Keberangkatan</td>
            <td><?= date('d F Y', strtotime($data['tanggal'])) ?></td>
        </tr>
        <tr>
            <td>Jam Berangkat</td>
            <td><?= htmlspecialchars($data['jam_berangkat']) ?></td>
        </tr>
        <tr>
            <td>Jam Tiba</td>
            <td><?= htmlspecialchars($data['jam_tiba']) ?></td>
        </tr>
        <tr>
            <td>Nomor Kursi</td>
            <td><?= htmlspecialchars($nomor_kursi) ?></td>
        </tr>
        <tr>
            <td>Total Harga</td>
            <td>Rp <?= number_format($data['total_harga'], 0, ',', '.') ?></td>
        </tr>
    </table>

    <hr>

    <div class="note">
        Silakan tunjukkan tiket ini saat naik bus
    </div>

    <div class="button-group">
        <button onclick="window.print()">Cetak Tiket</button>
        <button onclick="window.location.href='status_pemesanan.php'">← Kembali</button>
    </div>
</div>

</body>
</html>
