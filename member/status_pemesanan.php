<?php
session_start();
require "../config/database.php";

if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION["user"];

// Ambil semua reservasi + status pembayaran terakhir + status refund
$stmt = $pdo->prepare("
    SELECT 
        r.reservasi_id,
        r.kode_booking,
        r.jumlah_kursi,
        r.total_harga,
        r.status AS reservasi_status,
        p.status AS pembayaran_status,
        p.payment_id,
        b.status AS refund_status
    FROM reservasi r
    LEFT JOIN pembayaran p ON p.reservasi_id = r.reservasi_id
        AND p.waktu_bayar = (SELECT MAX(waktu_bayar) FROM pembayaran WHERE reservasi_id = r.reservasi_id)
    LEFT JOIN pembatalan b ON b.reservasi_id = r.reservasi_id
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
        .status-paid { background-color: green; }
        .status-pending { background-color: orange; }
        .status-rejected { background-color: red; }
        .status-belum { background-color: gray; }
        .btn-refund { background-color: #f39c12; }
        a { text-decoration: none; }
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
    <th>Refund</th>
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
                case 'berhasil':
                    $class = 'status-paid';
                    $text = 'LUNAS';
                    break;
                case 'pending':
                    $class = 'status-pending';
                    $text = 'MENUNGGU VERIFIKASI';
                    break;
                case 'gagal':
                    $class = 'status-rejected';
                    $text = 'DITOLAK';
                    break;
                default:
                    $class = 'status-belum';
                    $text = 'BELUM BAYAR';
            }
            ?>
            <button class="<?= $class ?>"><?= $text ?></button>
        </td>
        <td>
            <?php
            switch ($r['refund_status']) {
                case 'Menunggu':
                    echo "<button class='status-pending'>PENDING REFUND</button>";
                    break;
                case 'Disetujui':
                    echo "<button class='status-paid'>DISETUJUI</button>";
                    break;
                case 'Ditolak':
                    echo "<button class='status-rejected'>DITOLAK</button>";
                    break;
                default:
                    ?>
                    <a href="ajukan_refund.php?reservasi_id=<?= $r['reservasi_id'] ?>">
                        <button class="btn-refund">Ajukan Refund</button>
                    </a>
                    <?php
            }
            ?>
        </td>



        <td>
            <a href="detail_reservasi.php?reservasi_id=<?= $r['reservasi_id'] ?>">
                <button style="background-color:#007bff;">Detail</button>
            </a>
        </td>
    </tr>
    <?php endforeach; ?>
<?php else: ?>
<tr>
    <td colspan="6">Belum ada reservasi</td>
</tr>
<?php endif; ?>
</table>

</body>
</html>
