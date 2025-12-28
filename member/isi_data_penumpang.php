<?php
session_start();
require "../config/database.php";

if (!isset($_SESSION["user"])) {
    header("Location: ../login.php");
    exit;
}

$user = $_SESSION["user"];

/* ===============================
   VALIDASI RESERVASI
================================ */
$reservasi_id = $_GET['reservasi_id'] ?? null;
if (!$reservasi_id || !is_numeric($reservasi_id)) {
    die("Reservasi tidak valid.");
}

$stmt = $pdo->prepare("
    SELECT * FROM reservasi 
    WHERE reservasi_id = ? 
    AND user_id = ?
");
$stmt->execute([$reservasi_id, $user['user_id']]);
$reservasi = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reservasi) {
    die("Reservasi tidak ditemukan.");
}

/* ===============================
   AMBIL KURSI (1 SAJA)
================================ */
// Ambil kursi dari reservasi, jika sudah ada
$stmt = $pdo->prepare("
    SELECT nomor_kursi 
    FROM seat_booking 
    WHERE reservasi_id = ?
    LIMIT 1
");
$stmt->execute([$reservasi_id]);
$seat = $stmt->fetchColumn();

if (!$seat) {
    // Jika belum ada, ambil kursi kosong dari jadwal
    $stmt2 = $pdo->prepare("
        SELECT nomor_kursi 
        FROM seat_booking 
        WHERE jadwal_id = ? AND status = 'kosong'
        LIMIT 1
    ");
    $stmt2->execute([$reservasi['jadwal_id']]);
    $seat = $stmt2->fetchColumn();

    if ($seat) {
        // Buat seat_booking untuk reservasi baru
        $pdo->prepare("
            INSERT INTO seat_booking (jadwal_id, nomor_kursi, penumpang_id, status, reservasi_id)
            VALUES (?, ?, NULL, 'kosong', ?)
        ")->execute([$reservasi['jadwal_id'], $seat, $reservasi_id]);
    } else {
        // Tidak ada kursi sama sekali
        $pdo->prepare("
            UPDATE reservasi 
            SET status = 'gagal' 
            WHERE reservasi_id = ?
        ")->execute([$reservasi_id]);

        die("Kursi tidak ditemukan atau reservasi dibatalkan.");
    }
}


/* ===============================
   SIMPAN DATA PENUMPANG
================================ */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nama    = trim($_POST['nama'] ?? '');
    $nik     = trim($_POST['nik'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $telepon = trim($_POST['telepon'] ?? '');

    if (!$nama || !$nik || !$email || !$telepon) {
        die("Semua data penumpang wajib diisi.");
    }

    try {
        $pdo->beginTransaction();

        // Insert penumpang
        $stmt = $pdo->prepare("
            INSERT INTO penumpang 
            (reservasi_id, nama_penumpang, nik, email, telepon, nomor_kursi)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $reservasi_id,
            $nama,
            $nik,
            $email,
            $telepon,
            $seat
        ]);

        $penumpang_id = $pdo->lastInsertId();

        // Update seat_booking
        $stmt = $pdo->prepare("
            UPDATE seat_booking 
            SET penumpang_id = ?
            WHERE reservasi_id = ? 
            AND nomor_kursi = ?
        ");
        $stmt->execute([$penumpang_id, $reservasi_id, $seat]);

        // Update status reservasi → BERHASIL
        $stmt = $pdo->prepare("
            UPDATE reservasi 
            SET status = 'berhasil'
            WHERE reservasi_id = ?
        ");
        $stmt->execute([$reservasi_id]);

        $pdo->commit();

        // Kembali ke dashboard
        header("Location: upload_pembayaran_form.php?reservasi_id=$reservasi_id");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();

        // Gagal total
        $pdo->prepare("
            UPDATE reservasi 
            SET status = 'gagal'
            WHERE reservasi_id = ?
        ")->execute([$reservasi_id]);

        die("Gagal menyimpan data penumpang.");
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Isi Data Penumpang</title>
<link rel="stylesheet" href="../aset/css/nav.css">
<style>
body {
    background: #2f405a;
    color: #333;
    font-family: Arial, sans-serif;
}

h2 {
    color: #fff;
    text-align: center;
    margin: 20px 0;
}

form {
    max-width: 600px;
    margin: 0 auto;
}

fieldset {
    background: #fff;
    border-radius: 15px;
    padding: 20px;
    box-shadow: 0 8px 20px rgba(0,0,0,.15);
    border: none;
}

legend {
    font-weight: bold;
    color: #2c3e50;
    padding: 5px 15px;
    background: #fff;
    border-radius: 10px;
}

.form-group {
    display: flex;
    flex-direction: column;
    margin-bottom: 12px;
}

label {
    font-weight: bold;
    margin-bottom: 5px;
}

input {
    padding: 10px;
    border-radius: 8px;
    border: 1px solid #ccc;
}

button {
    display: block;
    margin: 20px auto;
    padding: 12px 25px;
    background: #27ae60;
    color: #fff;
    border: none;
    border-radius: 10px;
    font-size: 16px;
    cursor: pointer;
}
button:hover {
    background: #219150;
}
</style>
</head>
<body>

<?php include __DIR__ . "/nav.php"; ?>

<h2>Isi Data Penumpang</h2>

<form method="post">
    <fieldset>
        <legend>Kursi <?= htmlspecialchars($seat) ?></legend>

        <div class="form-group">
            <label>Nama</label>
            <input type="text" name="nama" required>
        </div>

        <div class="form-group">
            <label>NIK</label>
            <input type="text" name="nik" required>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required>
        </div>

        <div class="form-group">
            <label>Telepon</label>
            <input type="text" name="telepon" required>
        </div>
    </fieldset>

    <button type="submit">Simpan</button>
</form>

</body>
</html>
