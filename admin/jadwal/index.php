<?php
require "../../config/database.php";

$stmt = $pdo->query("
    SELECT j.*, 
           ta.nama_terminal AS asal,
           tt.nama_terminal AS tujuan,
           ba.nama_bus
    FROM jadwal j
    JOIN rute r ON j.rute_id = r.rute_id
    JOIN terminal ta ON r.asal_id = ta.terminal_id
    JOIN terminal tt ON r.tujuan_id = tt.terminal_id
    JOIN bus_armada ba ON j.armada_id = ba.armada_id
    ORDER BY j.tanggal, j.jam_berangkat
");
$jadwal = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Jadwal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- CSS -->
    <link rel="stylesheet" href="/agrambak/aset/css/dashboard_admin.css">
    <link rel="stylesheet" href="/agrambak/aset/css/users_admin.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
</head>
<body>

<?php include __DIR__ . '/../sidebar.php'; ?>

<div class="main-content">

    <!-- HEADER -->
    <div class="dashboard-header mb-4">
        <div>
            <h1>Data Jadwal</h1>
            <p>Kelola jadwal keberangkatan bus</p>
        </div>
    </div>

    <!-- TABLE CARD -->
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-end mb-3">
                <a href="tambah.php" class="btn btn-primary">
                    <i class="fa fa-plus"></i> Tambah Jadwal
                </a>
            </div>
            <div class="table-responsive">
                <table id="jadwalTable" class="table table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Rute</th>
                            <th>Tanggal</th>
                            <th>Jam</th>
                            <th>Armada</th>
                            <th>Harga</th>
                            <th style="width:160px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($jadwal as $j): ?>
                        <tr>
                            <td>
                                <?= htmlspecialchars($j['asal']) ?>
                                →
                                <?= htmlspecialchars($j['tujuan']) ?>
                            </td>
                            <td><?= date('d-m-Y', strtotime($j['tanggal'])) ?></td>
                            <td>
                                <?= substr($j['jam_berangkat'], 0, 5) ?>
                                -
                                <?= substr($j['jam_tiba'], 0, 5) ?>
                            </td>
                            <td><?= htmlspecialchars($j['nama_bus']) ?></td>
                            <td>Rp <?= number_format($j['harga'], 0, ',', '.') ?></td>
                            <td>
                                <a href="edit.php?id=<?= $j['jadwal_id'] ?>"
                                   class="btn btn-sm btn-outline-warning">
                                   Edit
                                </a>
                                <a href="hapus.php?id=<?= $j['jadwal_id'] ?>"
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Hapus jadwal ini?')">
                                   Hapus
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

</div>

<!-- JS -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
$(function () {
    $('#jadwalTable').DataTable({
        pageLength: 10,
        lengthChange: false,
        ordering: true,
        language: {
            search: "Cari:",
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ jadwal",
            zeroRecords: "Data jadwal tidak ditemukan",
            paginate: {
                previous: "‹",
                next: "›"
            }
        }
    });
});
</script>

</body>
</html>
