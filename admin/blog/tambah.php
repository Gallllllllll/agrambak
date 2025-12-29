<?php
require "../../middleware/auth.php";
admin_required();
require "../../config/database.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = $_POST['judul'];
    $konten = $_POST['konten'];
    $user_id = $_SESSION['user']['user_id'];

    $gambar = null;
    if(isset($_FILES['gambar']) && $_FILES['gambar']['error'] === 0){
        $ext = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
        $gambar = uniqid() . ".$ext";
        move_uploaded_file($_FILES['gambar']['tmp_name'], "../../uploads/$gambar");
    }

    $stmt = $pdo->prepare("INSERT INTO blog (user_id, judul, konten, gambar, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
    $stmt->execute([$user_id, $judul, $konten, $gambar]);

    header("Location: index.php");
    exit;
}
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

    <title>Tambah Blog</title>
</head>
<body>
    <?php include __DIR__ . '/../sidebar.php'; ?>

<div class="main-content">
    <div class="dashboard-header mb-4">
        <div>
            <h1>Tambah Blog</h1>
            <p>Tambahkan artikel blog baru</p>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="judul" class="form-label"><strong>Judul</strong></label>
                    <input type="text" class="form-control" id="judul" name="judul" required>
                </div>
                <div class="mb-3">
                    <label for="konten" class="form-label"><strong>Konten</strong></label>
                    <textarea class="form-control" id="konten" name="konten" rows="10" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="gambar" class="form-label"><strong>Gambar</strong></label>
                    <input type="file" class="form-control" id="gambar" name="gambar" accept="image/*">
                </div>
                <!-- ACTION -->
                <div class="col-12 d-flex justify-content-between mt-4">
                    <a href="index.php" class="btn btn-light">
                        <i class="fa-solid fa-arrow-left"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Posting
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>