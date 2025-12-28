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
        // Ambil reservasi_id dari pembatalan
        $stmt = $pdo->prepare("SELECT reservasi_id FROM pembatalan WHERE pembatalan_id=? AND status='Menunggu'");
        $stmt->execute([$pembatalan_id]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$res) {
            throw new Exception("Refund tidak ditemukan atau sudah diproses.");
        }

        $reservasi_id = $res['reservasi_id'];

        // Update status refund
        $stmt = $pdo->prepare("UPDATE pembatalan SET status='Disetujui', catatan_admin=?, waktu_respon=NOW() WHERE pembatalan_id=?");
        $stmt->execute([$catatan, $pembatalan_id]);

        // Update status reservasi menjadi Dibatalkan
        $stmt = $pdo->prepare("UPDATE reservasi SET status='Dibatalkan' WHERE reservasi_id=?");
        $stmt->execute([$reservasi_id]);

        // Update pembayaran menjadi refunded
        $stmt = $pdo->prepare("UPDATE pembayaran SET status='refunded' WHERE reservasi_id=?");
        $stmt->execute([$reservasi_id]);

        // Kembalikan kursi menjadi kosong
        $stmt = $pdo->prepare("UPDATE seat_booking SET status='kosong' WHERE reservasi_id=?");
        $stmt->execute([$reservasi_id]);

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Gagal memproses refund: " . $e->getMessage());
    }


    } elseif ($aksi === 'tolak') {
        $stmt = $pdo->prepare("UPDATE pembatalan SET status='Ditolak', catatan_admin=?, waktu_respon=NOW() WHERE pembatalan_id=? AND status='Menunggu'");
        $stmt->execute([$catatan, $pembatalan_id]);
    }

    header("Location: konfirmasi_refund.php");
    exit;
}

/* =======================
   AMBIL DATA REFUND MENUNGGU
======================= */
$stmt = $pdo->query("
    SELECT pb.pembatalan_id, r.kode_booking, r.total_harga, pb.alasan,
           u.nama AS nama_user, pb.waktu_ajukan
    FROM pembatalan pb
    JOIN reservasi r ON pb.reservasi_id = r.reservasi_id
    JOIN users u ON pb.user_id = u.user_id
    WHERE pb.status='Menunggu'
    ORDER BY pb.waktu_ajukan DESC
");
$refunds = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Konfirmasi Refund</title>

<link rel="stylesheet" href="../aset/css/dashboard_admin.css">
<link rel="stylesheet" href="../aset/css/users_admin.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
</head>
<body>

<?php include __DIR__ . '/sidebar.php'; ?>

<div class="main-content">

    <div class="dashboard-header mb-3">
        <h1>Konfirmasi Refund</h1>
        <p>Kelola permintaan refund pengguna</p>
    </div>

    <div class="table-responsive">
        <?php if ($refunds): ?>
        <table id="refundTable" class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>Kode Booking</th>
                    <th>Nama User</th>
                    <th>Total Harga</th>
                    <th>Alasan</th>
                    <th>Waktu Ajukan</th>
                    <th style="width:180px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($refunds as $r): ?>
                <tr>
                    <td><?= htmlspecialchars($r['kode_booking']) ?></td>
                    <td><?= htmlspecialchars($r['nama_user']) ?></td>
                    <td>Rp <?= number_format($r['total_harga'], 0, ',', '.') ?></td>
                    <td><?= htmlspecialchars($r['alasan']) ?></td>
                    <td><?= $r['waktu_ajukan'] ?></td>
                    <td>
                        <form method="POST" class="d-flex flex-column flex-sm-row gap-1">
                            <input type="hidden" name="pembatalan_id" value="<?= $r['pembatalan_id'] ?>">
                            <input type="text" name="catatan_admin" class="form-control form-control-sm mb-1 mb-sm-0" placeholder="Catatan Admin">
                            <button type="submit" name="aksi" value="setuju" class="btn btn-sm btn-outline-success">Setuju</button>
                            <button type="submit" name="aksi" value="tolak" class="btn btn-sm btn-outline-danger">Tolak</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p><i>Tidak ada refund pending.</i></p>
        <?php endif; ?>
    </div>

</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function () {
    $('#refundTable').DataTable({
        pageLength: 10,
        lengthChange: false,
        ordering: true,
        language: {
            search: "Cari:",
            paginate: { previous: "‹", next: "›" },
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ refund",
            infoEmpty: "Data kosong",
            zeroRecords: "Data tidak ditemukan"
        }
    });
});
</script>

</body>
</html>
