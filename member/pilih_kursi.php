<?php
session_start();
require "../config/database.php";

if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION["user"];

if (!isset($_GET['jadwal_id'])) {
    die("Jadwal tidak ditemukan.");
}

$jadwal_id = $_GET['jadwal_id'];

// Ambil info jadwal dan bus
$stmt = $pdo->prepare("
    SELECT j.jadwal_id, j.tanggal, j.jam_berangkat, j.jam_tiba, j.harga,
           ba.nama_bus, ba.kapasitas
    FROM jadwal j
    JOIN bus_armada ba ON j.armada_id = ba.armada_id
    WHERE j.jadwal_id = ?
");
$stmt->execute([$jadwal_id]);
$jadwal = $stmt->fetch();

if (!$jadwal) die("Jadwal tidak ditemukan.");

// Ambil seat booking yang sudah dibooking
$stmt2 = $pdo->prepare("SELECT nomor_kursi, status FROM seat_booking WHERE jadwal_id = ?");
$stmt2->execute([$jadwal_id]);
$seats = $stmt2->fetchAll(PDO::FETCH_KEY_PAIR);

// Ambil seat map sesuai tipe bus
$stmt3 = $pdo->prepare("
    SELECT nomor_kursi 
    FROM seat_map 
    WHERE tipe_id = (SELECT tipe_id FROM bus_armada WHERE armada_id = (SELECT armada_id FROM jadwal WHERE jadwal_id = ?))
    ORDER BY nomor_kursi
");
$stmt3->execute([$jadwal_id]);
$seatmap = $stmt3->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Pilih Kursi</title>
    <style>
        .seat { display:inline-block; width:40px; height:40px; margin:5px; text-align:center; line-height:40px; border:1px solid #333; cursor:pointer; }
        .terisi { background-color:#f00; color:#fff; cursor:not-allowed; }
        .kosong { background-color:#0f0; }
        .selected { background-color:#00f; color:#fff; }
    </style>
</head>
<body>
<h2>Pilih Kursi untuk <?= $jadwal['nama_bus'] ?> (<?= $jadwal['tanggal'] ?>)</h2>

<form method="POST" action="proses_booking.php">
    <input type="hidden" name="jadwal_id" value="<?= $jadwal_id ?>">
    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">

    <div>
        <?php foreach ($seatmap as $seat): 
            $status = isset($seats[$seat]) ? $seats[$seat] : 'kosong';
        ?>
            <div class="seat <?= $status ?>" data-seat="<?= $seat ?>">
                <?= $seat ?>
                <input type="checkbox" name="kursi[]" value="<?= $seat ?>" style="display:none;" <?= $status != 'kosong' ? 'disabled' : '' ?>>
            </div>
        <?php endforeach; ?>
    </div>
    <br>
    <button type="submit">Pesan</button>
</form>

<script>
const seatDivs = document.querySelectorAll('.seat.kosong');
seatDivs.forEach(div => {
    div.addEventListener('click', () => {
        const checkbox = div.querySelector('input[type="checkbox"]');
        if(checkbox.checked){
            checkbox.checked = false;
            div.classList.remove('selected');
        } else {
            checkbox.checked = true;
            div.classList.add('selected');
        }
    });
});
</script>
</body>
</html>
