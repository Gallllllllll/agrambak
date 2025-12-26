<?php
session_start();
require "../../config/database.php";

if (!isset($_SESSION['user'])) {
    echo "User belum login.";
    exit;
}

// Tentukan user_id dari session
if (is_array($_SESSION['user']) && isset($_SESSION['user']['id'])) {
    $user_id = $_SESSION['user']['id'];
} elseif (is_numeric($_SESSION['user'])) {
    $user_id = $_SESSION['user'];
} else {
    echo "User tidak ditemukan.";
    exit;
}

// Ambil data user
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "User tidak ditemukan.";
    exit;
}

// Folder untuk upload
$uploadsDir = '../../uploads/';

// Proses form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'] ?? '';
    $email = $_POST['email'] ?? '';
    $telepon = $_POST['telepon'] ?? '';
    $alamat = $_POST['alamat'] ?? '';

    // Handle upload foto
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['foto']['tmp_name'];
        $fileName = basename($_FILES['foto']['name']);
        $targetPath = $uploadsDir . $fileName;

        if (move_uploaded_file($fileTmp, $targetPath)) {
            $fotoDb = $fileName;
        } else {
            $fotoDb = $user['foto'];
        }
    } else {
        $fotoDb = $user['foto'];
    }

    // Update database
    $stmt = $pdo->prepare("UPDATE users SET nama = ?, email = ?, telepon = ?, alamat = ?, foto = ? WHERE user_id = ?");
    $stmt->execute([$nama, $email, $telepon, $alamat, $fotoDb, $user_id]);

    // Redirect ke index.php
    header("Location: index.php");
    exit;
}

// Foto untuk sidebar
$fotoPath = $uploadsDir . ($user['foto'] ?? '');
if (!empty($user['foto']) && file_exists($fotoPath)) {
    $fotoToShow = $fotoPath;
} else {
    $fotoToShow = $uploadsDir . 'default.png';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Edit Profil</title>
<style>
    /* Reset sederhana */
    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
        font-family: Arial, sans-serif;
        background-color: #f4f6f8;
        color: #333;
    }

    .container {
        display: flex;
        min-height: 100vh;
    }

    /* Sidebar */
    .sidebar {
        width: 250px;
        background-color: #3498db;
        color: #fff;
        padding: 20px;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .sidebar img {
        width: 120px;
        height: 120px;
        object-fit: cover;
        border-radius: 50%;
        margin-bottom: 15px;
        border: 3px solid #fff;
    }

    .sidebar h2 {
        font-size: 20px;
        margin-bottom: 5px;
        text-align: center;
    }

    .sidebar p {
        font-size: 14px;
        margin-bottom: 5px;
        text-align: center;
        word-break: break-word;
    }

    /* Main content */
    .main {
        flex: 1;
        padding: 30px;
    }

    .main h2 {
        margin-bottom: 20px;
        color: #333;
    }

    form {
        background-color: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        max-width: 600px;
        margin: auto;
    }

    form div {
        margin-bottom: 15px;
    }

    label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
        color: #555;
    }

    input[type="text"],
    input[type="email"],
    textarea,
    input[type="file"] {
        width: 100%;
        padding: 8px 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
        font-size: 14px;
    }

    textarea {
        resize: vertical;
        min-height: 80px;
    }

    button {
        background-color: #27ae60;
        color: #fff;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 14px;
        transition: background-color 0.3s ease;
    }

    button:hover {
        background-color: #1e8449;
    }

    @media (max-width: 768px) {
        .container {
            flex-direction: column;
        }
        .sidebar {
            width: 100%;
            flex-direction: row;
            justify-content: flex-start;
            padding: 15px;
        }
        .sidebar img {
            width: 80px;
            height: 80px;
            margin-right: 15px;
        }
        .sidebar h2, .sidebar p {
            text-align: left;
        }
        .main {
            padding: 15px;
        }
        form {
            width: 100%;
            padding: 15px;
        }
    }
</style>
</head>
<body>
<div class="container">
    <div class="sidebar">
        <img src="<?= htmlspecialchars($fotoToShow) ?>" alt="Foto Profil">
        <div>
            <h2><?= htmlspecialchars($user['nama']) ?></h2>
            <p><?= htmlspecialchars($user['email']) ?></p>
            <p><?= htmlspecialchars($user['telepon'] ?? '-') ?></p>
        </div>
    </div>
    <div class="main">
        <h2>Edit Profil</h2>
        <form method="post" enctype="multipart/form-data">
            <div>
                <label>Nama:</label>
                <input type="text" name="nama" value="<?= htmlspecialchars($user['nama']) ?>" required>
            </div>
            <div>
                <label>Email:</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>
            <div>
                <label>Telepon:</label>
                <input type="text" name="telepon" value="<?= htmlspecialchars($user['telepon'] ?? '') ?>">
            </div>
            <div>
                <label>Alamat:</label>
                <textarea name="alamat"><?= htmlspecialchars($user['alamat'] ?? '') ?></textarea>
            </div>
            <div>
                <label>Foto Profil:</label>
                <input type="file" name="foto" accept="image/*">
            </div>
            <div>
                <button type="submit">Simpan</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
