<?php
session_start();
require "../config/database.php";

if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION["user"];

// Ambil semua reservasi + status pembayaran terakhir
$stmt = $pdo->prepare("
    SELECT 
        r.reservasi_id,
        r.kode_booking,
        r.jumlah_kursi,
        r.total_harga,
        r.waktu_pesan,
        p.status AS pembayaran_status,
        p.payment_id
    FROM reservasi r
    LEFT JOIN pembayaran p
        ON p.reservasi_id = r.reservasi_id
        AND p.payment_id = (
            SELECT MAX(payment_id) 
            FROM pembayaran 
            WHERE reservasi_id = r.reservasi_id
        )
    WHERE r.user_id = ?
    ORDER BY r.waktu_pesan DESC
");
$stmt->execute([$user['id']]);
$reservasi = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Status Pemesanan</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #333; padding: 8px; text-align: center; }
        th { background: #f0f0f0; }
        button { padding: 5px 10px; margin: 2px; cursor: pointer; border: none; border-radius: 5px; color: #fff; }
        a { text-decoration: none; }
        .status-berhasil { background-color: green; }
        .status-pending { background-color: orange; }
        .status-gagal { background-color: red; }
        .status-belum { background-color: gray; }
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
    <th>Status Pembayaran</th>
    <th>Aksi</th>
</tr>

<?php if ($reservasi): ?>
    <?php foreach ($reservasi as $r): ?>
    <tr>
        <td><?= htmlspecialchars($r['kode_booking']) ?></td>
        <td><?= $r['jumlah_kursi'] ?></td>
        <td>Rp<?= number_format($r['total_harga'], 0, ',', '.') ?></td>
        <td>
            <?php
            $status = $r['pembayaran_status'] ?? 'belum_bayar';
            $class = '';
            $text = '';

            switch ($status) {
                case 'berhasil':   // admin ACC
                    $class = 'status-berhasil';
                    $text = 'LUNAS';
                    break;
                case 'pending':    // menunggu ACC
                    $class = 'status-pending';
                    $text = 'MENUNGGU VERIFIKASI';
                    break;
                case 'gagal':      // admin reject
                    $class = 'status-gagal';
                    $text = 'DITOLAK';
                    break;
                default:           // NULL atau belum bayar
                    $class = 'status-belum';
                    $text = 'BELUM BAYAR';
            }
            ?>
            <button class="<?= $class ?>"><?= $text ?></button>
        </td>
        <td>
            <a href="detail_reservasi.php?reservasi_id=<?= $r['reservasi_id'] ?>">
                <button style="background-color:#007bff;">Detail</button>
            </a>

            <?php if (!$r['payment_id'] || $status === 'gagal'): ?>
                <a href="upload_pembayaran_form.php?reservasi_id=<?= $r['reservasi_id'] ?>">
                    <button style="background-color:#28a745;">Upload Pembayaran</button>
                </a>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
<?php else: ?>
<tr>
    <td colspan="5">Belum ada reservasi</td>
</tr>
<?php endif; ?>
</table>

</body>
</html>
