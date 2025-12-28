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
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Blog - Admin</title>
    <link rel="stylesheet" href="../../aset/css/dashboard_admin.css">
    <link rel="stylesheet" href="../../aset/css/users_admin.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .main-content {
            padding: 20px;
        }

        h2 {
            margin-bottom: 20px;
            color: #4e73df;
        }

        .btn-add {
            display: inline-block;
            margin-bottom: 15px;
            background-color: #4e73df;
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
        }

        .btn-add:hover {
            background-color: #224abe;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            border-radius: 5px;
            overflow: hidden;
        }

        th, td {
            padding: 12px 10px;
            text-align: left;
        }

        th {
            background-color: #4e73df;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f7f7f7;
        }

        img.thumbnail {
            width: 100px;
            height: auto;
            border-radius: 5px;
        }

        a.action-btn {
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 5px;
            color: white;
            margin-right: 5px;
        }

        a.edit-btn {
            background-color: #1cc88a;
        }

        a.edit-btn:hover {
            background-color: #17a673;
        }

        a.delete-btn {
            background-color: #e74a3b;
        }

        a.delete-btn:hover {
            background-color: #c82333;
        }

        /* ===============================
        RESPONSIVE TABLE
        ================================ */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }

        /* Mobile */
        @media (max-width: 768px) {

            h2 {
                font-size: 20px;
            }

            .btn-add {
                width: 100%;
                text-align: center;
                margin-bottom: 15px;
            }

            table {
                font-size: 13px;
                min-width: 700px;
            }

            th, td {
                padding: 8px;
                white-space: nowrap;
            }

            img.thumbnail {
                width: 70px;
            }

            td .d-flex {
                justify-content: flex-start;
                align-items: center;
            }

            .action-btn {
                display: inline-flex;
                align-items: center;
                gap: 5px; /* jarak ikon dan teks */
                text-decoration: none;
                padding: 5px 10px;
                border-radius: 5px;
                color: white;
                font-size: 14px;
            }

            .action-btn:hover {
                opacity: 0.9;
            }

        }

    </style>
</head>
<body>
<?php include __DIR__ . '/../sidebar.php'; ?>

<div class="main-content">
    <h2>Daftar Blog</h2>
    <a href="tambah.php" class="btn-add"><i class="fa-solid fa-plus"></i> Tambah Blog</a>
    <div class="table-responsive">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Judul</th>
                <th>Penulis</th>
                <th>Gambar</th>
                <th>Created</th>
                <th>Updated</th>
                <th>Aksi</th>
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
                            <a href="edit.php?blog_id=<?= $b['blog_id'] ?>" class="action-btn edit-btn">
                                <i class="fa-solid fa-pen"></i> Edit
                            </a>
                            <a href="hapus.php?blog_id=<?= $b['blog_id'] ?>" class="action-btn delete-btn" 
                            onclick="return confirm('Yakin hapus?')">
                                <i class="fa-solid fa-trash"></i> Hapus
                            </a>
                        </div>
                    </td>

                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>

</body>
</html>
