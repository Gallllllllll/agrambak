<?php
session_start();
require "../config/database.php";

if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION["user"];

// Validasi jadwal
if (!isset($_GET['jadwal_id']) || !is_numeric($_GET['jadwal_id'])) {
    die("Jadwal tidak ditemukan.");
}
$jadwal_id = (int)$_GET['jadwal_id'];

// Ambil data jadwal
$stmt = $pdo->prepare("
    SELECT j.jadwal_id, j.tanggal, j.jam_berangkat, j.jam_tiba, j.harga,
           ba.nama_bus, ba.kapasitas
    FROM jadwal j
    JOIN bus_armada ba ON j.armada_id = ba.armada_id
    WHERE j.jadwal_id = ?
");
$stmt->execute([$jadwal_id]);
$jadwal = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$jadwal) die("Jadwal tidak ditemukan");

// Ambil status kursi yang sudah terisi
$stmt2 = $pdo->prepare("
    SELECT nomor_kursi
    FROM seat_booking
    WHERE jadwal_id = ?
    AND (
        status = 'terisi' OR status = 'diblock'
        OR (status = 'diblock' AND blocked_until > NOW())
    )

");
$stmt2->execute([$jadwal_id]);
$filled = $stmt2->fetchAll(PDO::FETCH_COLUMN);

// Ambil seatmap bus
$stmt3 = $pdo->prepare("
    SELECT nomor_kursi
    FROM seat_map
    WHERE tipe_id = (
        SELECT tipe_id FROM bus_armada WHERE armada_id = (
            SELECT armada_id FROM jadwal WHERE jadwal_id = ?
        )
    )
    ORDER BY nomor_kursi
");
$stmt3->execute([$jadwal_id]);
$seatmap = $stmt3->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Pilih Kursi</title>

<link rel="stylesheet" href="../aset/css/nav.css">
<link rel="stylesheet" href="../aset/css/footer.css">

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
    max-width: 720px;
    margin: 0 auto 30px;
    background: #fff;
    padding: 20px;
    border-radius: 20px;
    box-shadow: 0 10px 25px rgba(0,0,0,.15);
}

.bus-container {
    background: #bdc3c7;
    border-radius: 20px;
    padding: 20px;
}

.bus-top {
    width: 60%;
    height: 30px;
    background: #34495e;
    border-radius: 15px 15px 0 0;
    margin: 0 auto 15px;
    color: #fff;
    text-align: right;
    padding-right: 20px;
    line-height: 30px;
    font-weight: bold;
}

.seat-row {
    display: flex;
    justify-content: center;
    margin-bottom: 5px;
}

.seat {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    text-align: center;
    line-height: 40px;
    font-weight: bold;
    margin: 6px;
    border: 1px solid #333;
    user-select: none;
}

.seat.kosong { background: #2ecc71; cursor: pointer; }
.seat.terisi { background: #e74c3c; cursor: not-allowed; }
.seat.selected { background: #3498db; color: #fff; }

.aisle { width: 100px; }

button {
    display: block;
    margin: 25px auto 0;
    background: #27ae60;
    color: #fff;
    border: none;
    padding: 12px 25px;
    border-radius: 10px;
    font-size: 16px;
    cursor: pointer;
}
button:hover { background: #219150; }

@media (max-width:600px) {
    .seat { width:30px; height:30px; line-height:30px; font-size:12px; margin:3px; }
    .aisle { width:20px; }
}
</style>
</head>

<body>

<?php include __DIR__ . "/nav.php"; ?>

<h2>
    Pilih Kursi – <?= htmlspecialchars($jadwal['nama_bus']) ?>
    (<?= htmlspecialchars($jadwal['tanggal']) ?>)
</h2>

<form method="POST" action="proses_booking.php">
<input type="hidden" name="jadwal_id" value="<?= $jadwal_id ?>">

<div class="bus-container">
<div class="bus-top">Supir</div>

<?php
$rows = array_chunk($seatmap, 4);
foreach ($rows as $row):
?>
<div class="seat-row">
<?php foreach (array_slice($row, 0, 2) as $seat):
    $status = in_array($seat, $filled) ? 'terisi' : 'kosong';
?>
<div class="seat <?= $status ?>" data-seat="<?= $seat ?>">
    <?= $seat ?>
    <input type="radio" name="kursi" value="<?= $seat ?>" hidden <?= $status === 'terisi' ? 'disabled' : '' ?>>
</div>
<?php endforeach; ?>

<div class="aisle"></div>

<?php foreach (array_slice($row, 2, 2) as $seat):
    $status = in_array($seat, $filled) ? 'terisi' : 'kosong';
?>
<div class="seat <?= $status ?>" data-seat="<?= $seat ?>">
    <?= $seat ?>
    <input type="radio" name="kursi" value="<?= $seat ?>" hidden <?= $status === 'terisi' ? 'disabled' : '' ?>>
</div>
<?php endforeach; ?>


</div>
<?php endforeach; ?>
<div style="margin-top:15px; text-align:center; font-size:14px;">
    <span style="display:inline-block;width:20px;height:20px;background:#2ecc71;border-radius:5px;"></span>
    Kursi Kosong
    &nbsp;&nbsp;
    <span style="display:inline-block;width:20px;height:20px;background:#e74c3c;border-radius:5px;"></span>
    Kursi Terisi
    &nbsp;&nbsp;
    <span style="display:inline-block;width:20px;height:20px;background:#3498db;border-radius:5px;"></span>
    Dipilih
</div>
</div>

<button type="submit">Pesan</button>
</form>

<?php include __DIR__ . "/footer.php"; ?>

<script>
document.querySelectorAll('.seat').forEach(seat => {
    if (seat.classList.contains('terisi')) return;

    seat.addEventListener('click', () => {
        document.querySelectorAll('.seat.selected').forEach(s => {
            s.classList.remove('selected');
            s.querySelector('input').checked = false;
        });

        seat.classList.add('selected');
        seat.querySelector('input').checked = true;
    });
});

document.querySelector('form').addEventListener('submit', e => {
    if (!document.querySelector('input[name="kursi"]:checked')) {
        e.preventDefault();
        alert('Silakan pilih 1 kursi terlebih dahulu');
    }
});
</script>


</body>
</html>
