<?php
session_start();
require "../config/database.php";

if (!isset($_SESSION["user"])) {
    header("Location: ../login.php");
    exit;
}

$user = $_SESSION["user"];

$reservasi_id = $_GET['reservasi_id'] ?? null;
if (!$reservasi_id || !is_numeric($reservasi_id)) {
    die("Reservasi tidak valid.");
}

// ======================
// AMBIL DATA RESERVASI
// ======================
$stmt = $pdo->prepare("
    SELECT * FROM reservasi
    WHERE reservasi_id=? AND user_id=?
");
$stmt->execute([$reservasi_id, $user['user_id']]);
$reservasi = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reservasi) {
    die("Reservasi tidak ditemukan.");
}

// ======================
// AMBIL KURSI DARI SESSION
// ======================
if (!isset($_SESSION['selected_seats'][$reservasi_id])) {
    die("Data kursi tidak ditemukan atau sudah kadaluarsa.");
}

$kursi = $_SESSION['selected_seats'][$reservasi_id];

// ======================
// PROSES UPLOAD
// ======================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $metode = $_POST['metode'];
    $file   = $_FILES['bukti_transfer'];

    // ======================
    // CEK KURSI MASIH TERSEDIA
    // ======================
    $stmtCheck = $pdo->prepare("
        SELECT status, blocked_until
        FROM seat_booking
        WHERE jadwal_id=? AND nomor_kursi=?
    ");

    foreach ($kursi as $s) {
        $stmtCheck->execute([$reservasi['jadwal_id'], $s]);
        $seat = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        // Kalau kursinya benar-benar sudah terisi orang lain
        if (!$seat || $seat['status'] === 'terisi') {
            die("Kursi $s sudah tidak tersedia.");
        }
    }


    // ======================
    // UPLOAD FILE
    // ======================
    $nama_file = time() . "_" . basename($file['name']);
    move_uploaded_file($file['tmp_name'], "../uploads/" . $nama_file);

    try {
        $pdo->beginTransaction();

        // ======================
        // INSERT PEMBAYARAN
        // ======================
        $ref_number = 'TRX-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)),0,6));

        $stmt = $pdo->prepare("
            INSERT INTO pembayaran
            (reservasi_id, ref_number, metode, jumlah, bukti_transfer, status, waktu_bayar)
            VALUES (?, ?, ?, ?, ?, 'berhasil', NOW())
        ");
        $stmt->execute([
            $reservasi_id,
            $ref_number,
            $metode,
            $reservasi['total_harga'],
            $nama_file
        ]);

        // ======================
        // UPDATE KURSI JADI TERISI
        // ======================
        $stmtUpdate = $pdo->prepare("
            UPDATE seat_booking
            SET status='terisi',
                blocked_until=NULL
            WHERE jadwal_id=?
                AND nomor_kursi=?
                AND status='diblock'
        ");

        foreach ($kursi as $s) {
            $stmtUpdate->execute([$reservasi['jadwal_id'], $s]);
        }

        // ======================
        // UPDATE RESERVASI
        // ======================
        $stmt = $pdo->prepare("
            UPDATE reservasi SET status='lunas'
            WHERE reservasi_id=?
        ");
        $stmt->execute([$reservasi_id]);

        $pdo->commit();

        // ======================
        // BERSIHKAN SESSION
        // ======================
        unset($_SESSION['selected_seats'][$reservasi_id]);

        header("Location: status_pemesanan.php?reservasi_id=$reservasi_id");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        die("Gagal upload pembayaran: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Upload Pembayaran - <?= $reservasi['kode_booking'] ?></title>
<link rel="stylesheet" href="../aset/css/nav.css">
<link rel="icon" href="../aset/img/logo-tranzio2.png" type="image/x-icon">
<style>
body {
    background: #2f405a;
    color: #333;
}

h2 {
    color: #fff;
    text-align: center;
    margin-bottom: 20px;
}

a {
    color: #3498db;
    text-decoration: none;
    font-weight: bold;
}

a:hover {
    text-decoration: underline;
}

form {
    max-width: 600px;
    margin: 0 auto;
    background: #fff;
    padding: 20px 25px;
    border-radius: 15px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    border: 1px solid #ddd;
}

label {
    display: block;
    font-weight: bold;
    margin-bottom: 5px;
    color: #2c3e50;
}

input[type="file"],
select {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 14px;
    margin-bottom: 15px;
    transition: border-color 0.2s;
}

input[type="file"]:focus,
select:focus {
    border-color: #3498db;
    outline: none;
}

button {
    display: block;
    width: 100%;
    background: #27ae60;
    color: #fff;
    border: none;
    padding: 12px;
    border-radius: 10px;
    font-size: 16px;
    cursor: pointer;
    transition: background 0.2s;
    font-weight: bold;
}

button:hover {
    background: #219150;
}

@media(max-width:600px){
    form {
        padding: 15px;
    }
}
</style>
</head>
<body>
<?php include __DIR__ . "/nav.php"; ?>

<h2>Upload Pembayaran - <?= htmlspecialchars($reservasi['kode_booking']) ?></h2>
<br><br>

<form method="POST" enctype="multipart/form-data">
    <label>Metode Pembayaran:</label>
    <select name="metode" required>
        <option value="transfer">Transfer Bank</option>
        <option value="ovo">OVO</option>
        <option value="gopay">GoPay</option>
    </select>

    <label>Bukti Transfer:</label>
    <input type="file" name="bukti_transfer" required>

    <button type="submit">Upload</button>
</form>
</body>
</html>
