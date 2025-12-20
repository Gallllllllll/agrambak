<?php
session_start();
require "../config/database.php";

if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION["user"];

if (!isset($_GET['id'])) {
    die("Reservasi tidak ditemukan");
}

$reservasi_id = $_GET['id'];

$stmt = $pdo->prepare("
    SELECT 
        r.*, 
        j.tanggal, j.jam_berangkat, j.jam_tiba,
        b.nama_bus,
        p.status AS pembayaran_status,
        p.metode,
        p.waktu_bayar
    FROM reservasi r
    JOIN jadwal j ON r.jadwal_id = j.jadwal_id
    JOIN bus_armada b ON j.armada_id = b.armada_id
    LEFT JOIN pembayaran p ON r.reservasi_id = p.reservasi_id
    WHERE r.reservasi_id = ? AND r.user_id = ?
");
$stmt->execute([$reservasi_id, $user['id']]);
$data = $stmt->fetch();

if (!$data) {
    die("Data tidak ditemukan");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Detail Reservasi</title>
</head>
<body>

<h2>Detail Reservasi</h2>

<p><b>Kode Booking:</b> <?= $data['kode_booking'] ?></p>
<p><b>Bus:</b> <?= $data['nama_bus'] ?></p>
<p><b>Tanggal:</b> <?= $data['tanggal'] ?></p>
<p><b>Jam:</b> <?= $data['jam_berangkat'] ?> - <?= $data['jam_tiba'] ?></p>
<p><b>Jumlah Kursi:</b> <?= $data['jumlah_kursi'] ?></p>
<p><b>Total:</b> Rp<?= number_format($data['total_harga']) ?></p>
<p><b>Metode Pembayaran:</b> <?= $data['metode'] ?? '-' ?></p>
<p><b>Status Reservasi:</b> <?= strtoupper($data['status']) ?></p>
<p><b>Status Pembayaran:</b> <?= strtoupper($data['pembayaran_status'] ?? 'MENUNGGU') ?></p>

<hr>

<?php if ($data['status'] === 'dipesan' && $data['pembayaran_status'] === 'paid'): ?>
    <a href="cetak_tiket.php?id=<?= $data['reservasi_id'] ?>" target="_blank">
        🧾 Cetak Tiket (PDF)
    </a>
<?php else: ?>
    <p><i>Tiket dapat dicetak setelah pembayaran lunas</i></p>
<?php endif; ?>

<br><br>
<a href="status_pemesanan.php">← Kembali</a>

</body>
</html>
