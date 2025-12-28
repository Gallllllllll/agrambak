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
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Blog - Admin</title>
    <link rel="stylesheet" href="../../aset/css/dashboard_admin.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .main-content {
            padding: 20px;
            max-width: 700px;
            margin: auto;
        }

        h2 {
            margin-bottom: 20px;
            color: #4e73df;
        }

        .form-card {
            background-color: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        label {
            font-weight: 600;
        }

        input[type="text"],
        textarea,
        input[type="file"] {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }

        textarea {
            resize: vertical;
        }

        button {
            background-color: #4e73df;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #224abe;
        }

        a.back-btn {
            display: inline-block;
            margin-bottom: 15px;
            text-decoration: none;
            color: #4e73df;
        }

        a.back-btn:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="main-content">
    <h2>Tambah Blog</h2>
    <a href="index.php" class="back-btn"><i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Blog</a>

    <div class="form-card">
        <form method="post" enctype="multipart/form-data">
            <label>Judul:</label>
            <input type="text" name="judul" placeholder="Masukkan judul blog" required>

            <label>Konten:</label>
            <textarea name="konten" rows="10" placeholder="Tulis konten blog di sini..." required></textarea>

            <label>Gambar:</label>
            <input type="file" name="gambar" accept="image/*">

            <button type="submit"><i class="fa-solid fa-floppy-disk"></i> Simpan Blog</button>
        </form>
    </div>
</div>
</body>
</html>
