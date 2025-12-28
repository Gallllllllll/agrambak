<?php
require "../../middleware/auth.php";
admin_required();
require "../../config/database.php";

$q = $_GET['q'] ?? '';
$role = $_GET['role'] ?? '';

// DATA
$sql = "SELECT * FROM users WHERE 1";

$params = [];

if ($q) {
    $sql .= " AND (nama LIKE ? OR email LIKE ?)";
    $params[] = "%$q%";
    $params[] = "%$q%";
}
if ($role) {
    $sql .= " AND role = ?";
    $params[] = $role;
}

$sql .= " ORDER BY user_id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- CSS GLOBAL -->
    <link rel="stylesheet" href="../../aset/css/dashboard_admin.css">
    <link rel="stylesheet" href="../../aset/css/users_admin.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <title>Manajemen User</title>
</head>
<body>

<?php include __DIR__ . '/../sidebar.php'; ?>

<div class="main-content">

    <div class="dashboard-header">
        <div>
            <h1>Manajemen User</h1>
            <p>Kelola data pengguna</p>
        </div>
    </div>

    <div class="table-responsive">
        <table id="userTable" class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Telepon</th>
                    <th>Role</th>
                    <th style="width:160px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $i => $u): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= htmlspecialchars($u['nama']) ?></td>
                    <td><?= $u['email'] ?></td>
                    <td><?= $u['telepon'] ?></td>
                    <td>
                        <span class="badge <?= $u['role']=='admin'?'bg-primary':'bg-secondary' ?>">
                            <?= ucfirst($u['role']) ?>
                        </span>
                    </td>
                    <td>
                        <a href="detail.php?id=<?= $u['user_id'] ?>" class="btn btn-sm btn-outline-info">Detail</a>
                        <a href="edit.php?id=<?= $u['user_id'] ?>" class="btn btn-sm btn-outline-warning">Edit</a>
                        <a href="hapus.php?id=<?= $u['user_id'] ?>"
                           class="btn btn-sm btn-outline-danger"
                           onclick="return confirm('Hapus user?')">
                           Hapus
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>


</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function () {
    $('#userTable').DataTable({
        pageLength: 10,
        lengthChange: false,
        ordering: true,
        language: {
            search: "Cari:",
            paginate: {
                previous: "‹",
                next: "›"
            },
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
            infoEmpty: "Data kosong",
            zeroRecords: "Data tidak ditemukan"
        }
    });
});
</script>
<script>
$(document).ready(function () {

    var table = $('#userTable').DataTable();

    // Buat dropdown role
    var roleFilter = `
        <label style="margin-right:12px">
            Role:
            <select id="filterRole" class="form-select form-select-sm d-inline-block" style="width:140px">
                <option value="">Semua</option>
                <option value="Admin">Admin</option>
                <option value="User">User</option>
            </select>
        </label>
    `;

    // Sisipkan sebelum input search bawaan
    $('#userTable_filter').prepend(roleFilter);

    // Filter ke kolom Role (index 4)
    $('#filterRole').on('change', function () {
        table.column(4).search(this.value).draw();
    });

});
</script>

</body>
</html>
