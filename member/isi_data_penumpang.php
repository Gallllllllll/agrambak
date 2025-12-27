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
<link rel="stylesheet" href="../aset/css/nav.css">
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

form {
    max-width: 800px;
    margin: 0 auto;
}

fieldset {
    background: #fff;
    border-radius: 15px;
    padding: 20px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    margin-bottom: 20px;
    border: 1px solid #ddd;
}

legend {
    font-weight: bold;
    color: #2c3e50;
    background: #fff;        /* background putih */
    padding: 5px 15px;       /* ruang di sekitar teks */
    border-radius: 10px;     /* radius membulat */
    /* opsional, sedikit bayangan */
    display: inline-block;   /* supaya radius terlihat rapi */
    margin-bottom: 10px;     /* jarak ke konten di bawah */
}


.form-grid {
    display: flex;
    flex-direction: column;  /* susun input menurun */
    gap: 10px;               /* jarak antar input */
}


.form-group {
    display: flex;
    flex-direction: column;  /* label di atas input */
     /* lebar minimum */
}



label {
    font-weight: bold;
    margin-bottom: 5px;
    color: #2c3e50;
}

input[type="text"],
input[type="email"] {
    padding: 10px 12px;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 14px;
    transition: border-color 0.2s;
}

input[type="text"]:focus,
input[type="email"]:focus {
    border-color: #3498db;
    outline: none;
}

button {
    display: block;
    margin: 20px auto 0;
    background: #27ae60;
    color: #fff;
    border: none;
    padding: 12px 25px;
    border-radius: 10px;
    font-size: 16px;
    cursor: pointer;
    transition: background 0.2s;
    font-weight: bold;
}

button:hover {
    background: #219150;
}

@media (max-width: 600px) {
    .form-grid {
        grid-template-columns: 1fr;
    }

    input[type="text"],
    input[type="email"] {
        font-size: 13px;
    }

    button {
        width: 100%;
        font-size: 15px;
    }
}

</style>
</head>
<body>
<?php include __DIR__ . "/nav.php"; ?>

<h2>Isi Data Penumpang</h2>

<form method="post">
<?php foreach ($kursi as $i => $seat): ?>
    <fieldset>
        <legend>Kursi <?= htmlspecialchars($seat) ?></legend>
        <div class="form-grid">
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
        </div>
    </fieldset>
<?php endforeach; ?>
<button type="submit">Simpan Data Penumpang</button>
</form>
</body>
</html>
