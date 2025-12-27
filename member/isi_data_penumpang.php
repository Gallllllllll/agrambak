<?php
session_start();
require "../config/database.php";

if (!isset($_SESSION["user"])) {
    header("Location: ../login.php");
    exit;
}

$user = $_SESSION["user"];

// Ambil data reservasi dari query string
$reservasi_id = $_GET['reservasi_id'] ?? null;
if (!$reservasi_id) {
    die("Reservasi tidak valid.");
}

// Ambil reservasi dan jumlah kursi
$stmt = $pdo->prepare("SELECT * FROM reservasi WHERE reservasi_id = ? AND user_id = ?");
$stmt->execute([$reservasi_id, $user['user_id']]);
$reservasi = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$reservasi) {
    die("Reservasi tidak ditemukan.");
}

// Ambil seat yang dipesan
$stmt = $pdo->prepare("SELECT nomor_kursi FROM seat_booking WHERE penumpang_id IS NULL AND jadwal_id = ?");
$stmt->execute([$reservasi['jadwal_id']]);
$kursi = $stmt->fetchAll(PDO::FETCH_COLUMN);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nama = $_POST['nama'] ?? [];
    $nik = $_POST['nik'] ?? [];
    $email = $_POST['email'] ?? [];
    $telepon = $_POST['telepon'] ?? [];

    if (count($kursi) !== count($nama)) {
        die("Data penumpang tidak lengkap.");
    }

    try {
        $pdo->beginTransaction();

        foreach ($kursi as $i => $seat) {
            // Insert penumpang lengkap
            $stmt = $pdo->prepare("INSERT INTO penumpang (reservasi_id, nama_penumpang, nik, email, telepon, nomor_kursi) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $reservasi_id,
                $nama[$i],
                $nik[$i],
                $email[$i],
                $telepon[$i],
                $seat
            ]);
            $penumpang_id = $pdo->lastInsertId();

            // Update seat_booking dengan penumpang_id
            $stmt = $pdo->prepare("UPDATE seat_booking SET penumpang_id = ? WHERE jadwal_id = ? AND nomor_kursi = ?");
            $stmt->execute([$penumpang_id, $reservasi['jadwal_id'], $seat]);
        }

        $pdo->commit();
        // Redirect ke form upload pembayaran
        header("Location: upload_pembayaran_form.php?reservasi_id=$reservasi_id");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Gagal menyimpan data penumpang: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Isi Data Penumpang</title>
<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .form-group { margin-bottom: 10px; }
</style>
</head>
<body>
<h2>Isi Data Penumpang</h2>
<form method="post">
<?php foreach ($kursi as $i => $seat): ?>
    <fieldset>
        <legend>Kursi <?= htmlspecialchars($seat) ?></legend>
        <div class="form-group">
            <label>Nama:</label>
            <input type="text" name="nama[]" required>
        </div>
        <div class="form-group">
            <label>NIK:</label>
            <input type="text" name="nik[]" required>
        </div>
        <div class="form-group">
            <label>Email:</label>
            <input type="email" name="email[]" required>
        </div>
        <div class="form-group">
            <label>Telepon:</label>
            <input type="text" name="telepon[]" required>
        </div>
    </fieldset>
    <br>
<?php endforeach; ?>
<button type="submit">Simpan Data Penumpang</button>
</form>
</body>
</html>
