<?php
session_start();
require "../config/database.php";

if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION["user"];

// Ambil semua reservasi + pembayaran user
$stmt = $pdo->prepare("
    SELECT 
        r.reservasi_id,
        r.kode_booking,
        r.jumlah_kursi,
        r.total_harga,
        r.status AS reservasi_status,
        p.metode,
        p.status AS pembayaran_status,
        p.bukti_transfer,
        p.waktu_bayar
    FROM reservasi r
    LEFT JOIN pembayaran p ON r.reservasi_id = p.reservasi_id
    WHERE r.user_id = ?
    ORDER BY r.waktu_pesan DESC
");
$stmt->execute([$user['id']]);
$reservasi = $stmt->fetchAll();

// Fungsi tampil status pembayaran/reservasi
function tampil_status($status, $tipe='pembayaran') {
    if ($tipe === 'pembayaran') {
        if (!$status) return '<span class="status-pending">Menunggu</span>';
        switch(strtolower($status)) {
            case 'pending': return '<span class="status-pending">Menunggu</span>';
            case 'paid': return '<span class="status-approved">Lunas</span>';
            case 'rejected': return '<span class="status-rejected">Ditolak</span>';
            default: return htmlspecialchars($status);
        }
    }

    // reservasi
    switch(strtolower($status)) {
        case 'dipesan': return '<span class="status-approved">Dipesan</span>';
        case 'batal': return '<span class="status-rejected">Batal</span>';
        default: return htmlspecialchars($status);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Status Pembayaran</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #333; padding: 8px; text-align: center; }
        th { background: #f0f0f0; }
        .status-pending { color: orange; font-weight: bold; }
        .status-approved { color: green; font-weight: bold; }
        .status-rejected { color: red; font-weight: bold; }
    </style>
</head>
<body>

<h2>Status Pembayaran</h2>
<a href="dashboard.php">← Kembali ke Dashboard</a>
<br><br>

<table>
    <tr>
        <th>Kode Booking</th>
        <th>Jumlah Kursi</th>
        <th>Total Harga</th>
        <th>Metode Pembayaran</th>
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
                <td><?= $r['metode'] ?? '-' ?></td>
                <td><?= tampil_status($r['reservasi_status'], 'reservasi') ?></td>
                <td><?= tampil_status($r['pembayaran_status'], 'pembayaran') ?></td>
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
            <td colspan="8">Belum ada reservasi</td>
        </tr>
    <?php endif; ?>
</table>

</body>
</html>
