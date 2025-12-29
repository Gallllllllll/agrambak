<?php
require "../../middleware/auth.php";
admin_required();
require "../../config/database.php";

$blog_id = $_GET['blog_id'] ?? die("Blog tidak ditemukan");

$stmt = $pdo->prepare("SELECT * FROM blog WHERE blog_id = ?");
$stmt->execute([$blog_id]);
$blog = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$blog) die("Blog tidak ditemukan");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul  = $_POST['judul'];
    $konten = $_POST['konten'];

    $gambar = $blog['gambar'];
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === 0) {
        $ext = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
        $gambar = uniqid('blog_') . "." . $ext;
        move_uploaded_file($_FILES['gambar']['tmp_name'], "../../uploads/$gambar");
    }

    $stmt = $pdo->prepare("
        UPDATE blog 
        SET judul = ?, konten = ?, gambar = ?, updated_at = NOW() 
        WHERE blog_id = ?
    ");
    $stmt->execute([$judul, $konten, $gambar, $blog_id]);

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

    <title>Edit Blog</title>
</head>
<body>
    <?php include __DIR__ . '/../sidebar.php'; ?>

<div class="main-content">
    <div class="dashboard-header mb-4">
        <div>
            <h1>Edit Blog</h1>
            <p>Perbarui artikel blog</p>
        </div>
    </div>
    <div class="card shadow-sm">
        <div class="card-body">
            <form method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="judul" class="form-label"><strong>Judul</strong></label>
                    <input type="text" class="form-control" id="judul" name="judul" value="<?= htmlspecialchars($blog['judul']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="konten" class="form-label"><strong>Konten</strong></label>
                    <textarea class="form-control" id="konten" name="konten" rows="10" required><?= htmlspecialchars($blog['konten']) ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="gambar" class="form-label"><strong>Gambar</strong></label><br>
                    <?php if ($blog['gambar']): ?>
                        <img src="../../uploads/<?= $blog['gambar'] ?>" alt="Gambar Blog" style="max-width: 200px; margin-bottom: 10px;">
                    <?php endif; ?>
                    <input type="file" class="form-control" id="gambar" name="gambar" accept="image/*">
                    <small class="text-muted">Biarkan kosong jika tidak ingin mengubah gambar</small>
                </div>
                <!-- ACTION -->
                <div class="col-12 d-flex justify-content-between mt-4">
                    <a href="index.php" class="btn btn-light">
                        <i class="fa-solid fa-arrow-left"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
</body>
</html>