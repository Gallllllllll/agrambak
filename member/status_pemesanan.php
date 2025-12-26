<?php
session_start();
require "../config/database.php";

if (!isset($_SESSION["user"])) {
    header("Location: dashboard.php");
    exit;
}

$user = $_SESSION["user"];
$message = '';

// Proses Check-In
if (isset($_POST['checkin_reservasi_id'])) {
    $rid = $_POST['checkin_reservasi_id'];

    // Ambil reservasi & waktu_checkin + jadwal_id
    $stmt = $pdo->prepare("SELECT jadwal_id, waktu_checkin FROM reservasi WHERE reservasi_id=? AND user_id=?");
    $stmt->execute([$rid, $user['id']]);
    $res = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$res) {
        $message = "Reservasi tidak ditemukan.";
    } elseif (!empty($res['waktu_checkin'])) {
        $message = "Reservasi sudah check-in.";
    } else {
        $pdo->beginTransaction();

        // Update waktu_checkin di tabel reservasi
        $stmt = $pdo->prepare("UPDATE reservasi SET waktu_checkin=NOW() WHERE reservasi_id=?");
        $stmt->execute([$rid]);

        // Ambil semua kursi yang dipesan (penumpang_id = user id)
        $stmt = $pdo->prepare("SELECT nomor_kursi FROM seat_booking WHERE jadwal_id=? AND penumpang_id=?");
        $stmt->execute([$res['jadwal_id'], $user['id']]);
        $kursi = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Update status kursi menjadi terisi
        $stmt = $pdo->prepare("UPDATE seat_booking SET status='terisi' WHERE jadwal_id=? AND nomor_kursi=?");
        foreach ($kursi as $k) {
            $stmt->execute([$res['jadwal_id'], $k]);
        }

        $pdo->commit();
        $message = "Check-In berhasil!";
    }
}

// Ambil semua reservasi + status pembayaran + refund
$stmt = $pdo->prepare("
    SELECT 
        r.reservasi_id,
        r.jadwal_id,
        r.kode_booking,
        r.jumlah_kursi,
        r.total_harga,
        r.waktu_checkin,
        p.status AS pembayaran_status,
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
        .status-checkin { background-color: #3498db; }
        .btn-refund { background-color: #f39c12; }
        a { text-decoration: none; }
    </style>
</head>
<body>

<h2>Status Pemesanan</h2>
<a href="dashboard.php">← Kembali ke Dashboard</a>
<br><br>

<?php if ($message): ?>
    <p><strong><?= htmlspecialchars($message) ?></strong></p>
<?php endif; ?>

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

            // Cek waktu_checkin
            if (!empty($r['waktu_checkin'])) {
                $class = 'status-checkin';
                $text = 'SUDAH CHECK-IN';
            }
            ?>
            <button class="<?= $class ?>"><?= $text ?></button>
        </td>
        <td>
            <?php
            // Jika sudah check-in, tombol refund disable dengan warna abu-abu
            if (!empty($r['waktu_checkin'])) {
                echo "<button class='btn-refund' style='background-color:#7f8c8d;' disabled title='Sudah check-in, refund tidak bisa diajukan'>Ajukan Refund</button>";
            } else {
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
            }
            ?>
        </td>

        <td>
            <a href="detail_reservasi.php?reservasi_id=<?= $r['reservasi_id'] ?>">
                <button style="background-color:#007bff;">Detail</button>
            </a>

            <?php if (empty($r['waktu_checkin'])): ?>
                <!-- Form Check-In -->
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="checkin_reservasi_id" value="<?= $r['reservasi_id'] ?>">
                    <button type="submit" style="background-color:#3498db;">Check-In</button>
                </form>
            <?php endif; ?>
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
