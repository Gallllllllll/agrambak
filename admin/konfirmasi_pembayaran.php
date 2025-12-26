<?php
session_start();
require "../config/database.php";

// Cek login admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Tangani aksi ACC / Reject
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_id = $_POST['payment_id'];
    $aksi = $_POST['aksi']; // 'berhasil' atau 'gagal'

    if (in_array($aksi, ['berhasil', 'gagal'])) {
        $stmt = $pdo->prepare("UPDATE pembayaran SET status = ? WHERE payment_id = ?");
        $stmt->execute([$aksi, $payment_id]);
    }

    header("Location: konfirmasi_pembayaran.php");
    exit;
}


// Ambil semua pembayaran pending terbaru
$stmt = $pdo->query("
    SELECT r.reservasi_id, r.kode_booking, r.jumlah_kursi, r.total_harga,
           p.payment_id, p.metode, p.bukti_transfer, p.waktu_bayar
    FROM reservasi r
    JOIN pembayaran p ON r.reservasi_id = p.reservasi_id
    WHERE p.status = 'berhasil'
    ORDER BY p.waktu_bayar DESC
");
$pembayaranPending = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Konfirmasi Pembayaran</title>
    <style>
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #333; padding: 8px; text-align: center; }
    th { background: #f0f0f0; }
    button { padding: 5px 10px; margin: 2px; cursor: pointer; border: none; }
    .btn-approve { background-color: #4CAF50; color: white; }
    .btn-reject { background-color: #f44336; color: white; }
    </style>
</head>
<body>

<h2>Konfirmasi Pembayaran</h2>
<a href="dashboard.php">← Kembali ke Dashboard</a>
<br><br>

<?php if ($pembayaranPending): ?>
<table>
<tr>
    <th>Kode Booking</th>
    <th>Jumlah Kursi</th>
    <th>Total Harga</th>
    <th>Metode</th>
    <th>Bukti Transfer</th>
    <th>Waktu Bayar</th>
    <th>Aksi</th>
</tr>

<?php foreach ($pembayaranPending as $p): ?>
<tr>
    <td><?= htmlspecialchars($p['kode_booking']) ?></td>
    <td><?= $p['jumlah_kursi'] ?></td>
    <td>Rp<?= number_format($p['total_harga']) ?></td>
    <td><?= htmlspecialchars($p['metode'] ?? '-') ?></td>
    <td>
        <?php if ($p['bukti_transfer']): ?>
            <a href="../uploads/<?= htmlspecialchars($p['bukti_transfer']) ?>" target="_blank">Lihat</a>
        <?php else: ?>
            -
        <?php endif; ?>
    </td>
    <td><?= $p['waktu_bayar'] ?></td>
    <td>
        <form method="POST" style="display:inline;">
            <input type="hidden" name="payment_id" value="<?= $p['payment_id'] ?>">
            <button type="submit" name="aksi" value="berhasil" class="btn-approve">ACC</button>
            <button type="submit" name="aksi" value="gagal" class="btn-reject">Reject</button>

        </form>
    </td>
</tr>
<?php endforeach; ?>

</table>
<?php else: ?>
<p><i>Tidak ada pembayaran pending.</i></p>
<?php endif; ?>

</body>
</html>
