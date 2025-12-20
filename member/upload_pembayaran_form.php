<?php
session_start();
require "../config/database.php";

function generateRefNumber() {
    return 'TRX-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
}


if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION["user"];

if (!isset($_GET['reservasi_id'])) {
    die("Reservasi tidak ditemukan");
}

$reservasi_id = $_GET['reservasi_id'];

// Ambil data reservasi
$stmt = $pdo->prepare("SELECT * FROM reservasi WHERE reservasi_id = ? AND user_id = ?");
$stmt->execute([$reservasi_id, $user['id']]);
$reservasi = $stmt->fetch();

if (!$reservasi) die("Reservasi tidak ditemukan");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $metode = $_POST['metode'];
    $file = $_FILES['bukti_transfer'];

    // Upload file
    $nama_file = time() . "_" . basename($file['name']);
    move_uploaded_file($file['tmp_name'], "../uploads/" . $nama_file);

    $jumlah = (int) $reservasi['total_harga'];



    // Insert ke tabel pembayaran
    $ref_number = generateRefNumber();

    $stmt = $pdo->prepare("
        INSERT INTO pembayaran 
        (reservasi_id, ref_number, metode, jumlah, bukti_transfer, status, waktu_bayar)
        VALUES (?, ?, ?, ?, ?, 'pending', NOW())
    ");
    $stmt->execute([
        $reservasi_id,
        $ref_number,
        $metode,
        $jumlah,
        $nama_file
    ]);


    header("Location: status_pemesanan.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload Pembayaran</title>
</head>
<body>
<h2>Upload Pembayaran - <?= $reservasi['kode_booking'] ?></h2>
<a href="status_pemesanan.php">← Kembali</a>
<br><br>

<form method="POST" enctype="multipart/form-data">
    <label>Metode Pembayaran:</label>
    <select name="metode" required>
        <option value="transfer">Transfer Bank</option>
        <option value="ovo">OVO</option>
        <option value="gopay">GoPay</option>
    </select>
    <br><br>
    <label>Bukti Transfer:</label>
    <input type="file" name="bukti_transfer" required>
    <br><br>
    <button type="submit">Upload</button>
</form>
</body>
</html>
