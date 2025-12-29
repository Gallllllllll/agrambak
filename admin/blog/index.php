<?php
require "../../middleware/auth.php";
admin_required();
require "../../config/database.php";

$blogs = $pdo->query("
    SELECT b.*, u.nama AS penulis 
    FROM blog b
    JOIN users u ON b.user_id = u.user_id
    ORDER BY b.created_at DESC
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" href="../../aset/img/logo-tranzio2.png" type="image/x-icon">

<link rel="stylesheet" href="../../aset/css/dashboard_admin.css">
<link rel="stylesheet" href="../../aset/css/users_admin.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" rel="stylesheet">

<title>Daftar Blog</title>
    <style>
        img.thumbnail {
            width: 80px;
            height: auto;
            border-radius: 5px;
        }
        .d-flex .btn {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        @media (max-width: 768px) {
            img.thumbnail { width: 60px; }
        }
    </style>
</head>
<body>

<?php include __DIR__ . '/../sidebar.php'; ?>

<div class="main-content">
    <div class="dashboard-header mb-4">
        <div>
            <h1>Daftar Blog</h1>
            <p>Kelola semua artikel blog</p>
        </div>
    </div>

    <div class="mb-3">
        <a href="tambah.php" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Tambah Blog
        </a>
    </div>

    <div class="table-responsive">
        <table id="blogTable" class="table table-striped align-middle">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Judul</th>
                    <th>Penulis</th>
                    <th>Gambar</th>
                    <th>Created</th>
                    <th>Updated</th>
                    <th style="width:140px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($blogs as $b): ?>
                <tr>
                    <td><?= $b['blog_id'] ?></td>
                    <td><?= htmlspecialchars($b['judul']) ?></td>
                    <td><?= htmlspecialchars($b['penulis']) ?></td>
                    <td>
                        <?php if($b['gambar']): ?>
                            <img src="../../uploads/<?= $b['gambar'] ?>" alt="Gambar Blog" class="thumbnail">
                        <?php endif; ?>
                    </td>
                    <td><?= $b['created_at'] ?></td>
                    <td><?= $b['updated_at'] ?></td>
                    <td>
                        <div class="d-flex gap-2">
                            <a href="edit.php?blog_id=<?= $b['blog_id'] ?>" class="btn btn-sm btn-outline-warning">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                            <a href="hapus.php?blog_id=<?= $b['blog_id'] ?>" class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('Yakin hapus?')">
                               <i class="fa-solid fa-trash"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- JS -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function () {
    $('#blogTable').DataTable({
        pageLength: 10,
        lengthChange: false,
        ordering: true,
        language: {
            search: "Cari:",
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ blog",
            zeroRecords: "Blog tidak ditemukan",
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
