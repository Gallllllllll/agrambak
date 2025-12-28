<?php
require __DIR__ . '/../../config/database.php';

/*
|--------------------------------------------------------------------------
| DATA TERMINAL (UNTUK DROPDOWN FILTER)
|--------------------------------------------------------------------------
*/
$terminals = $pdo->query("
    SELECT terminal_id, nama_terminal, kota, kode
    FROM terminal
    ORDER BY nama_terminal ASC
")->fetchAll();

/*
|--------------------------------------------------------------------------
| DATA RUTE (UNTUK TABEL)
| NOTE: Filter dilakukan via DataTables (client-side)
|--------------------------------------------------------------------------
*/
$rutes = $pdo->query("
    SELECT
        r.rute_id,

        t1.nama_terminal AS asal_terminal,
        t1.kota AS asal_kota,
        t1.kode AS asal_kode,

        t2.nama_terminal AS tujuan_terminal,
        t2.kota AS tujuan_kota,
        t2.kode AS tujuan_kode

    FROM rute r
    JOIN terminal t1 ON r.asal_id = t1.terminal_id
    JOIN terminal t2 ON r.tujuan_id = t2.terminal_id
    ORDER BY r.rute_id ASC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- CSS -->
    <link rel="stylesheet" href="/agrambak/aset/css/dashboard_admin.css">
    <link rel="stylesheet" href="/agrambak/aset/css/users_admin.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">

    <title>Manajemen Rute</title>
</head>
<body>

<?php include __DIR__ . '/../sidebar.php'; ?>

<div class="main-content">

    <!-- HEADER -->
    <div class="dashboard-header mb-4">
        <div>
            <h1>Daftar Rute</h1>
            <p>Kelola rute perjalanan bus</p>
        </div>
    </div>

    <!-- CARD -->
    <div class="card shadow-sm">
        <div class="card-body">

            <!-- ACTION BAR -->
            <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                <a href="tambah.php" class="btn btn-primary">
                    <i class="fa fa-plus"></i> Tambah Rute
                </a>

                <span class="fw-semibold ms-2">Filter:</span>

                <!-- FILTER ASAL -->
                <select id="filterAsal" class="form-select w-auto">
                    <option value="">Semua Asal</option>
                    <?php foreach ($terminals as $t): ?>
                        <option value="<?= htmlspecialchars($t['nama_terminal']) ?>">
                            <?= htmlspecialchars($t['nama_terminal']) ?>
                            (<?= htmlspecialchars($t['kota']) ?> - <?= htmlspecialchars($t['kode']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>

                <!-- FILTER TUJUAN -->
                <select id="filterTujuan" class="form-select w-auto">
                    <option value="">Semua Tujuan</option>
                    <?php foreach ($terminals as $t): ?>
                        <option value="<?= htmlspecialchars($t['nama_terminal']) ?>">
                            <?= htmlspecialchars($t['nama_terminal']) ?>
                            (<?= htmlspecialchars($t['kota']) ?> - <?= htmlspecialchars($t['kode']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- TABLE -->
            <div class="table-responsive">
                <table id="ruteTable" class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Asal</th>
                            <th>Tujuan</th>
                            <th style="width:160px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rutes as $r): ?>
                        <tr>
                            <td><?= $r['rute_id'] ?></td>
                            <td>
                                <strong><?= htmlspecialchars($r['asal_terminal']) ?></strong><br>
                                <small class="text-muted">
                                    <?= htmlspecialchars($r['asal_kota']) ?> (<?= htmlspecialchars($r['asal_kode']) ?>)
                                </small>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($r['tujuan_terminal']) ?></strong><br>
                                <small class="text-muted">
                                    <?= htmlspecialchars($r['tujuan_kota']) ?> (<?= htmlspecialchars($r['tujuan_kode']) ?>)
                                </small>
                            </td>
                            <td>
                                <a href="edit.php?id=<?= $r['rute_id'] ?>" class="btn btn-sm btn-outline-warning">
                                    Edit
                                </a>
                                <a href="delete.php?id=<?= $r['rute_id'] ?>"
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Yakin ingin hapus rute ini?')">
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
    const table = $('#ruteTable').DataTable({
        pageLength: 10,
        lengthChange: false,
        ordering: true,
        language: {
            search: "Cari:",
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ rute",
            zeroRecords: "Rute tidak ditemukan",
            paginate: {
                previous: "‹",
                next: "›"
            }
        }
    });

    // Filter Asal (kolom 1)
    $('#filterAsal').on('change', function () {
        table.column(1).search(this.value).draw();
    });

    // Filter Tujuan (kolom 2)
    $('#filterTujuan').on('change', function () {
        table.column(2).search(this.value).draw();
    });
});
</script>

</body>
</html>
