<?php
session_start();
require "../config/database.php";

// Cek login admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

/* =======================
   AMBIL DATA RESERVASI
======================= */
$stmt = $pdo->query("
    SELECT r.reservasi_id, r.kode_booking, r.jumlah_kursi, r.total_harga, r.status, r.waktu_pesan,
           u.nama AS nama_user, j.tanggal, j.jam_berangkat, j.jam_tiba, ba.nama_bus
    FROM reservasi r
    JOIN users u ON r.user_id = u.user_id
    JOIN jadwal j ON r.jadwal_id = j.jadwal_id
    JOIN bus_armada ba ON j.armada_id = ba.armada_id
    ORDER BY r.waktu_pesan DESC
");
$reservasis = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" href="../aset/img/logo-tranzio2.png" type="image/x-icon">
<title>Daftar Reservasi</title>

<link rel="stylesheet" href="../aset/css/dashboard_admin.css">
<link rel="stylesheet" href="../aset/css/users_admin.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
</head>
<body>

<?php include __DIR__ . '/sidebar.php'; ?>

<div class="main-content">

<div class="dashboard-header">
        <div>
            <h1>Daftar Reservasi</h1>
            <p>Kelola semua reservasi pengguna</p>
        </div>
</div>

<div class="table-responsive">
    <?php if ($reservasis): ?>
    <table id="reservasiTable" class="table table-striped align-middle">
        <thead>
            <tr>
                <th>Kode Booking</th>
                <th>Nama User</th>
                <th>Bus</th>
                <th>Tanggal / Jam</th>
                <th>Jumlah Kursi</th>
                <th>Total Harga</th>
                <th>Status</th>
                <th>Waktu Pesan</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reservasis as $r): ?>
            <tr>
                <td><?= htmlspecialchars($r['kode_booking']) ?></td>
                <td><?= htmlspecialchars($r['nama_user']) ?></td>
                <td><?= htmlspecialchars($r['nama_bus']) ?></td>
                <td><?= htmlspecialchars($r['tanggal']) ?> <br> <?= htmlspecialchars($r['jam_berangkat']) ?> - <?= htmlspecialchars($r['jam_tiba']) ?></td>
                <td><?= $r['jumlah_kursi'] ?></td>
                <td>Rp <?= number_format($r['total_harga'], 0, ',', '.') ?></td>
                <td><?= htmlspecialchars($r['status']) ?></td>
                <td><?= $r['waktu_pesan'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
        <p><i>Tidak ada reservasi.</i></p>
    <?php endif; ?>
</div>

</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function () {
    $('#reservasiTable').DataTable({
        pageLength: 10,
        lengthChange: false,
        ordering: true,
        language: {
            search: "Cari:",
            paginate: { previous: "‹", next: "›" },
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ reservasi",
            infoEmpty: "Data kosong",
            zeroRecords: "Data tidak ditemukan"
        }
    });
});
</script>

</body>
</html>
