<?php
session_start();
require "../config/database.php";

// Cek login admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

/* =======================
   PROSES ACC / TOLAK
======================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $pembatalan_id = $_POST['pembatalan_id'];
    $aksi = $_POST['aksi']; // setuju / tolak
    $catatan = $_POST['catatan_admin'] ?? '';

    if ($aksi === 'setuju') {

        $pdo->beginTransaction();

        try {
            // 1. Update pembatalan → Disetujui
            $stmt = $pdo->prepare("
                UPDATE pembatalan
                SET status = 'Disetujui',
                    catatan_admin = ?,
                    waktu_respon = NOW()
                WHERE pembatalan_id = ? AND status = 'Menunggu'
            ");
            $stmt->execute([$catatan, $pembatalan_id]);

            // 2. Update reservasi → Dibatalkan
            $stmt = $pdo->prepare("
                UPDATE reservasi
                SET status = 'Dibatalkan'
                WHERE reservasi_id = (
                    SELECT reservasi_id
                    FROM pembatalan
                    WHERE pembatalan_id = ?
                )
            ");
            $stmt->execute([$pembatalan_id]);

            // 3. Update pembayaran → refunded
            $stmt = $pdo->prepare("
                UPDATE pembayaran
                SET status = 'refunded'
                WHERE reservasi_id = (
                    SELECT reservasi_id
                    FROM pembatalan
                    WHERE pembatalan_id = ?
                )
            ");
            $stmt->execute([$pembatalan_id]);

            $pdo->commit();

        } catch (Exception $e) {
            $pdo->rollBack();
            die("Gagal memproses refund");
        }

    } elseif ($aksi === 'tolak') {

        $stmt = $pdo->prepare("
            UPDATE pembatalan
            SET status = 'Ditolak',
                catatan_admin = ?,
                waktu_respon = NOW()
            WHERE pembatalan_id = ? AND status = 'Menunggu'
        ");
        $stmt->execute([$catatan, $pembatalan_id]);
    }

    header("Location: konfirmasi_refund.php");
    exit;
}

/* =======================
   AMBIL DATA REFUND MENUNGGU
======================= */
$stmt = $pdo->query("
    SELECT 
        pb.pembatalan_id,
        r.kode_booking,
        r.total_harga,
        pb.alasan,
        u.nama AS nama_user,
        pb.waktu_ajukan
    FROM pembatalan pb
    JOIN reservasi r ON pb.reservasi_id = r.reservasi_id
    JOIN users u ON pb.user_id = u.user_id
    WHERE pb.status = 'Menunggu'
    ORDER BY pb.waktu_ajukan DESC
");
$refunds = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html>
<head>
    <title>Konfirmasi Refund</title>
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

<h2>Konfirmasi Refund</h2>
<a href="dashboard.php">← Kembali ke Dashboard</a>
<br><br>

<?php if ($refunds): ?>
<table>
<tr>
    <th>Kode Booking</th>
    <th>Nama User</th>
    <th>Total Harga</th>
    <th>Alasan</th>
    <th>Waktu Ajukan</th>
    <th>Aksi</th>
</tr>

<?php foreach ($refunds as $r): ?>
<tr>
    <td><?= htmlspecialchars($r['kode_booking']) ?></td>
    <td><?= htmlspecialchars($r['nama_user']) ?></td>
    <td>Rp<?= number_format($r['total_harga']) ?></td>
    <td><?= htmlspecialchars($r['alasan']) ?></td>
    <td><?= $r['waktu_ajukan'] ?></td>
    <td>
        <form method="POST" style="display:inline;">
            <input type="hidden" name="pembatalan_id" value="<?= $r['pembatalan_id'] ?>">
            <input type="text" name="catatan_admin" placeholder="Catatan Admin">
            <br>
            <button type="submit" name="aksi" value="setuju" class="btn-approve">
                Setuju
            </button>
            <button type="submit" name="aksi" value="tolak" class="btn-reject">
                Tolak
            </button>

        </form>
    </td>
</tr>
<?php endforeach; ?>

</table>
<?php else: ?>
<p><i>Tidak ada refund pending.</i></p>
<?php endif; ?>

</body>
</html>
