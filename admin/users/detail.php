<?php
require "../../middleware/auth.php";
admin_required();
require __DIR__ . '/../../config/database.php';

function e($value, $fallback = '<span class="text-muted fst-italic">Belum diisi</span>') {
    return $value !== null && $value !== ''
        ? htmlspecialchars($value)
        : $fallback;
}

$id = $_GET['id'];

$user = $pdo->prepare("SELECT * FROM users WHERE user_id=?");
$user->execute([$id]);
$user = $user->fetch();

$riwayat = $pdo->prepare("
    SELECT r.kode_booking, r.total_harga, r.status, r.waktu_pesan
    FROM reservasi r
    WHERE r.user_id=?
    ORDER BY r.waktu_pesan DESC
");
$riwayat->execute([$id]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- CSS GLOBAL -->
    <link rel="stylesheet" href="/agrambak/aset/css/dashboard_admin.css">
    <link rel="stylesheet" href="/agrambak/aset/css/users_admin.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <title>Detail User</title>
</head>
<body>
    <?php include __DIR__ . '/../sidebar.php'; ?>

    <div class="main-content">

    <div class="dashboard-header mb-4">
        <div>
            <h1>Detail User</h1>
            <p>Informasi pengguna & riwayat transaksi</p>
        </div>
    </div>

    <!-- INFO USER -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header fw-semibold">
            Informasi User
        </div>
        <div class="card-body">
            <div class="row mb-2">
                <div class="col-md-3 text-muted">Nama</div>
                <div class="col-md-9 fw-semibold">
                    <?= e($user['nama']) ?>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-md-3 text-muted">Email</div>
                <div class="col-md-9">
                    <?= e($user['email']) ?>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-md-3 text-muted">Telepon</div>
                <div class="col-md-9">
                    <?= e($user['telepon']) ?>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-md-3 text-muted">Alamat</div>
                <div class="col-md-9">
                    <?= e($user['alamat']) ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 text-muted">Role</div>
                <div class="col-md-9">
                    <span class="badge <?= $user['role']=='admin'?'bg-primary':'bg-secondary' ?>">
                        <?= ucfirst($user['role']) ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- RIWAYAT -->
    <div class="card shadow-sm mt-4">
        <div class="card-header">
            Riwayat Booking
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="bookingTable" class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Kode Booking</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Waktu Pesan</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($riwayat as $r): ?>
                        <tr>
                            <td><?= htmlspecialchars($r['kode_booking']) ?></td>
                            <td>Rp<?= number_format($r['total_harga'], 0, ',', '.') ?></td>
                            <td>
                                <span class="badge 
                                    <?= $r['status']=='berhasil'?'bg-success':
                                    ($r['status']=='pending'?'bg-warning':'bg-secondary') ?>">
                                    <?= ucfirst($r['status']) ?>
                                </span>
                            </td>
                            <td><?= date('d M Y H:i', strtotime($r['waktu_pesan'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>


    <div class="mt-4">
        <a href="index.php" class="btn btn-outline-secondary">
            ← Kembali ke Manajemen User
        </a>
    </div>

</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
$(function () {
    $('#bookingTable').DataTable({
        pageLength: 5,
        lengthChange: false,
        order: [[3, 'desc']], // sort by waktu pesan
        language: {
            search: "Cari booking:",
            paginate: {
                previous: "‹",
                next: "›"
            },
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ booking",
            zeroRecords: "Tidak ada data",
            infoEmpty: "Belum ada riwayat booking"
        }
    });
});
</script>

</body>
</html>