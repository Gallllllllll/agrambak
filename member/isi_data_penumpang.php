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

// pastikan reservasi milik user
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
   AMBIL KURSI DARI SESSION
================================ */
if (!isset($_SESSION['selected_seats'][$reservasi_id])) {
    die("Data kursi tidak ditemukan atau sudah kadaluarsa.");
}

$kursi = $_SESSION['selected_seats'][$reservasi_id];

// karena halaman ini 1 penumpang
$seat = $kursi[0];

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

        // insert penumpang
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

        // ambil penumpang_id yang baru dibuat
        $penumpang_id = $pdo->lastInsertId();

        // update seat_booking agar terhubung dengan penumpang
        $stmtUpdateSeat = $pdo->prepare("
            UPDATE seat_booking
            SET penumpang_id = ?
            WHERE jadwal_id = ? AND nomor_kursi = ?
        ");
        $stmtUpdateSeat->execute([$penumpang_id, $reservasi['jadwal_id'], $seat]);

        $pdo->commit();

        header("Location: upload_pembayaran_form.php?reservasi_id=$reservasi_id");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();

        // gagal → tandai reservasi
        $pdo->prepare("
            UPDATE reservasi
            SET status = 'gagal'
            WHERE reservasi_id = ?
        ")->execute([$reservasi_id]);

        die("Gagal menyimpan data penumpang: " . $e->getMessage());
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
    padding: 15px;
    box-shadow: 0 8px 20px rgba(0,0,0,.15);
    border: none;
}

legend {
    font-weight: bold;
}

.form-group {
    display: flex;
    flex-direction: column;
    margin-bottom: 12px;
}

label {
    font-weight: bold;
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
</style>
</head>

<body>
<?php include __DIR__ . "/nav.php"; ?>

<h2>Isi Data Penumpang</h2>

<form method="post">
    <fieldset>
        <div class="form-group">
            <legend>Kursi <?= htmlspecialchars($seat) ?></legend>
        </div>
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
