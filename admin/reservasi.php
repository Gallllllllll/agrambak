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

<!-- Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">

<!-- Font Awesome -->
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
            <thead class="table-dark">
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
                    <td>
                        <?= htmlspecialchars($r['tanggal']) ?><br>
                        <?= htmlspecialchars($r['jam_berangkat']) ?> - <?= htmlspecialchars($r['jam_tiba']) ?>
                    </td>
                    <td><?= $r['jumlah_kursi'] ?></td>
                    <td>Rp <?= number_format($r['total_harga'], 0, ',', '.') ?></td>
                    <td>
                        <span class="badge bg-<?= $r['status'] === 'PAID' ? 'success' : ($r['status'] === 'PENDING' ? 'warning' : 'secondary') ?>">
                            <?= htmlspecialchars($r['status']) ?>
                        </span>
                    </td>
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

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- DataTables -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<!-- DataTables Buttons -->
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>

<!-- PDFMake -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>

<script>
$(document).ready(function () {
    $('#reservasiTable').DataTable({
        pageLength: 10,
        lengthChange: false,
        ordering: true,
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'pdfHtml5',
                text: '<i class="fa fa-file-pdf"></i> Export PDF',
                className: 'btn btn-danger mb-3',
                title: 'Laporan Daftar Reservasi Tranzio',
                orientation: 'landscape',
                pageSize: 'A4',
                exportOptions: {
                    columns: ':visible'
                }
            }
        ],
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
