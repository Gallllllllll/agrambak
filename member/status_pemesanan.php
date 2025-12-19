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
?>

<!DOCTYPE html>
<html>
<head>
    <title>Status Pemesanan</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid #333;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #f0f0f0;
        }
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
                <td class="status-<?= strtolower($r['reservasi_status']) ?>"><?= $r['reservasi_status'] ?? '-' ?></td>
                <td class="status-<?= strtolower($r['pembayaran_status']) ?>"><?= $r['pembayaran_status'] ?? 'Menunggu' ?></td>
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
