<?php
session_start();
require "../config/database.php";

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];

if (!isset($_GET['reservasi_id'])) {
    die("Reservasi tidak ditemukan");
}

$reservasi_id = $_GET['reservasi_id'];
$sukses = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $alasan = trim($_POST['alasan'] ?? '');
    if (!$alasan) die("Alasan harus diisi");

    // Cegah double refund
    $cek = $pdo->prepare("SELECT 1 FROM pembatalan WHERE reservasi_id = ?");
    $cek->execute([$reservasi_id]);

    if ($cek->fetch()) {
        die("Refund sudah pernah diajukan.");
    }

    $stmt = $pdo->prepare("
        INSERT INTO pembatalan (reservasi_id, user_id, alasan, status, waktu_ajukan)
        VALUES (?, ?, ?, 'Menunggu', NOW())
    ");
    $stmt->execute([$reservasi_id, $user['user_id'], $alasan]);

    $sukses = true;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Ajukan Refund</title>
<link rel="stylesheet" href="../aset/css/nav.css">
<style>
body {
    font-family: Arial, sans-serif;
    background: #2f405a;
    color: #333;
}

h2 {
    color: white;
    text-align: center;
    margin-bottom: 20px;
}

.card {
    background: #fff;
    border-radius: 15px;
    padding: 20px;
    margin: 0 20px 25px 20px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    border: 1px solid #ddd;
}

.card h3 {
    margin-top: 0;
    margin-bottom: 15px;
    color: #2c3e50;
}

.card p {
    margin: 8px 0;
}

textarea {
    width: 100%;
    padding: 10px;
    border-radius: 8px;
    border: 1px solid #ccc;
    resize: vertical;
}

.button-group {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-top: 15px;
}

.button-group a, .button-group button {
    padding: 10px 15px;
    border-radius: 12px;
    font-size: 14px;
    font-weight: bold;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: 0.2s;
}

.button-back {
    background-color: #3498db;
    color: white;
}

.button-back:hover {
    background-color: #2d80c7;
}

.button-submit {
    background-color: #e74c3c;
    color: white;
}

.button-submit:hover {
    background-color: #c0392b;
}

@media(max-width:600px){
    .card {
        padding: 15px;
    }
    .button-group {
        flex-direction: column;
    }
    .button-group a, .button-group button {
        width: 100%;
        text-align: center;
    }
}
</style>
</head>
<body>

<?php include __DIR__ . "/nav.php"; ?>

<h2>Ajukan Refund</h2>

<div class="card">
    <?php if ($sukses): ?>
        <h3>✅ Refund berhasil diajukan</h3>
        <p>Menunggu konfirmasi admin.</p>
        <div class="button-group">
            <a href="status_pemesanan.php" class="button-back">← Kembali ke Status Pemesanan</a>
        </div>
    <?php else: ?>
        <form method="POST">
            <label>Alasan Refund:</label><br>
            <textarea name="alasan" required rows="4"></textarea>
            <div class="button-group">
                <button type="submit" class="button-submit">Ajukan Refund</button>
                <a href="status_pemesanan.php" class="button-back">← Kembali</a>
            </div>
        </form>
    <?php endif; ?>
</div>

</body>
</html>
