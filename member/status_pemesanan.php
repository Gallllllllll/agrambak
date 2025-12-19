<?php
session_start();
require "../config/database.php";

if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION["user"];

// Ambil reservasi member beserta status pembayaran
$stmt = $pdo->prepare("
    SELECT r.reservasi_id, r.kode_booking, r.jumlah_kursi, r.total_harga, r.status AS reservasi_status,
           p.metode, p.status AS pembayaran_status, p.bukti_transfer, p.waktu_bayar
    FROM reservasi r
    LEFT JOIN pembayaran p ON r.reservasi_id = p.reservasi_id
    WHERE r.user_id = ?
    ORDER BY r.waktu_pesan DESC
");
$stmt->execute([$user['id']]);
$reservasi = $stmt->fetchAll();

// Fungsi untuk menampilkan status dengan warna
function tampil_status($status, $tipe = 'reservasi', $reservasi_status = null) {
    // Jika reservasi sudah dipesan tapi pembayaran null, tetap tampil Lunas
    if ($tipe === 'pembayaran') {
        if (strtolower($reservasi_status) === 'dipesan') {
            return '<span class="status-approved">Lunas</span>';
        }
        if (!$status) {
            return '<span class="status-pending">Menunggu</span>';
        }
    }

    if (!$status) {
        return $tipe === 'pembayaran' ? '<span class="status-pending">Menunggu</span>' : '<span>-</span>';
    }

    switch (strtolower($status)) {
        case 'pending':
            return '<span class="status-pending">Menunggu</span>';
        case 'dipesan':
            return '<span class="status-approved">Dipesan</span>';
        case 'batal':
            return '<span class="status-rejected">Batal</span>';
        case 'paid':
            return '<span class="status-approved">Lunas</span>';
        case 'rejected':
            return '<span class="status-rejected">Ditolak</span>';
        default:
            return '<span>' . htmlspecialchars($status) . '</span>';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Status Pemesanan</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #333; padding: 8px; text-align: center; }
        th { background-color: #f0f0f0; }
        .status-pending { color: orange; font-weight: bold; }
        .status-approved { color: green; font-weight: bold; }
        .status-rejected { color: red; font-weight: bold; }
    </style>
</head>
<body>

<h2>Status Pemesanan</h2>
<a href="dashboard.php">← Kembali ke Dashboard</a>
<br><br>

<table>
    <tr>
        <th>Kode Booking</th>
        <th>Jumlah Kursi</th>
        <th>Total Harga</th>
        <th>Status Reservasi</th>
        <th>Status Pembayaran</th>
        <th>Bukti Transfer</th>
        <th>Waktu Bayar</th>
    </tr>

    <?php if ($reservasi): ?>
        <?php foreach ($reservasi as $r): ?>
            <tr>
                <td><?= htmlspecialchars($r['kode_booking']) ?></td>
                <td><?= $r['jumlah_kursi'] ?></td>
                <td>Rp<?= number_format($r['total_harga']) ?></td>
                <td><?= tampil_status($r['reservasi_status'], 'reservasi') ?></td>
                <td><?= tampil_status($r['pembayaran_status'], 'pembayaran', $r['reservasi_status']) ?></td>
                <td>
                    <?php if ($r['bukti_transfer']): ?>
                        <a href="../uploads/<?= htmlspecialchars($r['bukti_transfer']) ?>" target="_blank">Lihat</a>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
                <td><?= $r['waktu_bayar'] ?? '-' ?></td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="7">Belum ada reservasi</td>
        </tr>
    <?php endif; ?>
</table>

</body>
</html>
