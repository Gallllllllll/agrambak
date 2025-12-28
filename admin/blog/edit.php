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
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Blog</title>

    <link rel="stylesheet" href="../../aset/css/dashboard_admin.css">
    <link rel="stylesheet" href="../../aset/css/users_admin.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <style>
        .main-content {
            padding: 20px;
        }

        .card-form {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            padding: 20px;
            width: 100%;
            max-width: 100%;
        }

        .form-label {
            font-weight: 600;
        }

        .img-preview {
            max-width: 150px;
            border-radius: 6px;
            margin-bottom: 10px;
            display: block;
        }

        .btn-primary {
            background-color: #4e73df;
            border: none;
        }

        .btn-primary:hover {
            background-color: #224abe;
        }

        @media (max-width: 768px) {
            .card-form {
                padding: 15px;
            }
        }
    </style>
</head>
<body>

<?php include __DIR__ . '/../sidebar.php'; ?>

<div class="main-content">
    <h2 class="mb-4 text-primary">
        <i class="fa-solid fa-pen-to-square"></i> Edit Blog
    </h2>

    <div class="card-form">
        <form method="post" enctype="multipart/form-data">

            <div class="mb-3">
                <label class="form-label">Judul Blog</label>
                <input type="text" name="judul"
                       class="form-control"
                       value="<?= htmlspecialchars($blog['judul']) ?>"
                       required>
            </div>

            <div class="mb-3">
                <label class="form-label">Konten</label>
                <textarea name="konten"
                          class="form-control"
                          rows="8"
                          required><?= htmlspecialchars($blog['konten']) ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Gambar</label><br>

                <?php if ($blog['gambar']): ?>
                    <img src="../../uploads/<?= $blog['gambar'] ?>" class="img-preview">
                <?php endif; ?>

                <input type="file" name="gambar" class="form-control" accept="image/*">
                <small class="text-muted">Kosongkan jika tidak ingin mengganti gambar</small>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-save"></i> Update
                </button>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fa-solid fa-arrow-left"></i> Kembali
                </a>
            </div>

        </form>
    </div>
</div>

</body>
</html>
