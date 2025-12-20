<?php
session_start();
require "../config/database.php";

if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION["user"];
$reservasi_id = $_GET['reservasi_id'] ?? die("Reservasi tidak ditemukan");

// Ambil data reservasi + pembayaran
$stmt = $pdo->prepare("
    SELECT r.*, p.status AS pembayaran_status, p.metode, p.bukti_transfer, p.waktu_bayar
    FROM reservasi r
    LEFT JOIN pembayaran p ON r.reservasi_id = p.reservasi_id
    WHERE r.reservasi_id = ? AND r.user_id = ?
");
$stmt->execute([$reservasi_id, $user['id']]);
$data = $stmt->fetch();

if (!$data) die("Data tidak ditemukan");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Detail Reservasi</title>
</head>
<body>
<h2>Detail Reservasi - <?= $data['kode_booking'] ?></h2>
<a href="status_pemesanan.php">← Kembali</a>
<br><br>

<p><b>Jumlah Kursi:</b> <?= $data['jumlah_kursi'] ?></p>
<p><b>Total Harga:</b> Rp<?= number_format($data['total_harga']) ?></p>
<p><b>Status Pembayaran:</b> <?= strtoupper($data['pembayaran_status'] ?? 'MENUNGGU') ?></p>
<p><b>Metode:</b> <?= $data['metode'] ?? '-' ?></p>
<p><b>Bukti Transfer:</b> 
    <?php if ($data['bukti_transfer']): ?>
        <a href="../uploads/<?= $data['bukti_transfer'] ?>" target="_blank">Lihat</a>
    <?php else: ?>
        -
    <?php endif; ?>
</p>
<p><b>Waktu Bayar:</b> <?= $data['waktu_bayar'] ?? '-' ?></p>

<?php if ($data['pembayaran_status'] === 'berhasil'): ?>
    <a href="cetak_tiket.php?reservasi_id=<?= $data['reservasi_id'] ?>" target="_blank">
        <button style="background-color: #007bff; color:white; padding:5px 10px;">🧾 Cetak Tiket</button>
    </a>
<?php else: ?>
    <p><i>Tiket dapat dicetak setelah pembayaran lunas</i></p>
<?php endif; ?>

</body>
</html>
