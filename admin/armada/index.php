<?php
require "../../middleware/auth.php";
admin_required();
require "../../config/database.php";

$sql = "
    SELECT b.*, t.nama_tipe
    FROM bus_armada b
    LEFT JOIN armada_tipe t ON b.tipe_id = t.tipe_id
    ORDER BY b.armada_id DESC
";
$armada = $pdo->query($sql)->fetchAll();

$tipeBus = $pdo->query("SELECT * FROM armada_tipe")->fetchAll();
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

    <title>Manajemen Armada Bus</title>
</head>
<body>
    <?php include __DIR__ . '/../sidebar.php'; ?>

    <div class="main-content">

        <div class="dashboard-header mb-4">
            <div>
                <h1>Manajemen Armada</h1>
                <p>Kelola data armada bus</p>
            </div>
        </div>

<div class="card shadow-sm">
    <div class="card-body">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <!-- SELECT FILTER -->
             <div class="ms-auto">
                <a href="tambah.php" class="btn btn-primary">
                    <i class="fa fa-plus"></i> Tambah Armada
                </a>
            </div>
            <div>
                
                <select id="filterTipe" class="form-select">
                    <option value="">Semua Tipe</option>
                    <?php foreach ($tipeBus as $t): ?>
                        <option value="<?= htmlspecialchars($t['nama_tipe']) ?>">
                            <?= htmlspecialchars($t['nama_tipe']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table id="armadaTable" class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Bus</th>
                        <th>Tipe</th>
                        <th>Kapasitas</th>
                        <th>Deskripsi</th>
                        <th style="width:140px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($armada as $i => $b): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($b['nama_bus']) ?></td>
                        <td><?= $b['nama_tipe'] ?? '-' ?></td>
                        <td><?= $b['kapasitas'] ?></td>
                        <td><?= htmlspecialchars($b['deskripsi'] ?? '-') ?></td>
                        <td>
                            <a href="edit.php?id=<?= $b['armada_id'] ?>" class="btn btn-sm btn-outline-warning">Edit</a>
                            <a href="hapus.php?id=<?= $b['armada_id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus bus ini?')">Hapus</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>


</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
$(function () {
    // Inisialisasi DataTable
    const table = $('#armadaTable').DataTable({
        pageLength: 10,
        lengthChange: false,
        ordering: true,
        language: {
            search: "Cari:",
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ armada",
            zeroRecords: "Data tidak ditemukan",
            paginate: { previous: "‹", next: "›" }
        },
        initComplete: function () {
            // Pindahkan filter Tipe Bus ke samping search box
            const dataTableContainer = $(this.api().table().container());
            $('#filterTipe').appendTo(dataTableContainer.find('.dataTables_filter')).css({
                display: 'inline-block',
                width: 'auto',
                marginLeft: '10px'
            });
        }
    });

    // Filter kolom Tipe Bus
    $('#filterTipe').on('change', function () {
        table.column(2).search(this.value).draw();
    });
});
</script>


</body>
</html>