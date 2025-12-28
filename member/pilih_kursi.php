<?php
session_start();
require "../config/database.php";

if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION["user"];

// Validasi jadwal_id
if (!isset($_GET['jadwal_id']) || !is_numeric($_GET['jadwal_id'])) {
    die("Jadwal tidak ditemukan.");
}
$jadwal_id = (int)$_GET['jadwal_id'];

/* ===============================
   AMBIL DATA JADWAL
================================ */
$stmt = $pdo->prepare("
    SELECT j.jadwal_id, j.tanggal, j.jam_berangkat, j.jam_tiba, j.harga,
           ba.nama_bus, ba.kapasitas
    FROM jadwal j
    JOIN bus_armada ba ON j.armada_id = ba.armada_id
    WHERE j.jadwal_id = ?
");
$stmt->execute([$jadwal_id]);
$jadwal = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$jadwal) die("Jadwal tidak ditemukan.");

/* ===============================
   AMBIL STATUS KURSI
================================ */
$stmt2 = $pdo->prepare("
    SELECT nomor_kursi, status 
    FROM seat_booking 
    WHERE jadwal_id = ?
");
$stmt2->execute([$jadwal_id]);
$rawSeats = $stmt2->fetchAll(PDO::FETCH_ASSOC);

// Mapping status ke kelas CSS: terisi, diblock, kosong
$seats = [];
$statusMap = [
    'kosong' => 'kosong',
    'terisi' => 'terisi',
    'diblock' => 'diblock'
];
foreach($rawSeats as $s) {
    $seats[$s['nomor_kursi']] = $statusMap[$s['status']] ?? 'terisi';
}

/* ===============================
   SEAT MAP
================================ */
$stmt3 = $pdo->prepare("
    SELECT nomor_kursi 
    FROM seat_map 
    WHERE tipe_id = (
        SELECT tipe_id 
        FROM bus_armada 
        WHERE armada_id = (
            SELECT armada_id FROM jadwal WHERE jadwal_id = ?
        )
    )
    ORDER BY nomor_kursi
");
$stmt3->execute([$jadwal_id]);
$seatmap = $stmt3->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Pilih Kursi</title>
    <link rel="stylesheet" href="../aset/css/nav.css">
    <link rel="stylesheet" href="../aset/css/footer.css">
    <style>
        body {
            background: #2f405a;
            color: #333;
            font-family: Arial, sans-serif;
        }
        h2 {
            color: white;
            text-align: center;
            margin-top: 20px;
        }
        form {
            max-width: 700px;
            margin: 20px auto;
            background: #fff;
            padding: 20px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,.15);
        }

        /* Container bus */
        .bus-container {
            width: 100%;
            background: #bdc3c7;
            border-radius: 20px;
            padding: 20px;
            position: relative;
        }

        /* Atap bus */
        .bus-top {
            width: 60%;
            height: 30px;
            background: #34495e;
            border-radius: 15px 15px 0 0;
            margin: 0 auto 15px auto;
            text-align: right;
            padding-right: 20px;
            line-height: 30px;
            color: #fff;
            font-weight: bold;
        }

        /* Baris kursi */
        .seat-row {
            display: flex;
            justify-content: center; /* kursi lebih rapat ke tengah */
            margin-bottom: 5px; /* jarak antar baris */
        }

        /* Kursi */
        .seat {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            text-align: center;
            line-height: 40px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.2s;
            border: 1px solid #333;
            user-select: none;
            margin: 8px; /* jarak antar kursi lebih rapat */
        }
        .seat.kosong { background: #2ecc71; }
        .seat.diblock { background: #f1c40f; cursor:not-allowed; }
        .seat.terisi { background: #e74c3c; cursor:not-allowed; }
        .seat.selected { background: #3498db; color:#fff; }

        /* Lorong */
        .aisle {
            width: 120px; /* lorong lebih sempit */
        }

        /* Tombol */
        button {
            display: block;
            margin: 20px auto 0 auto;
            background: #27ae60;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            font-size: 16px;
            cursor: pointer;
            transition: 0.2s;
        }
        button:hover { background: #219150; }

        @media(max-width:600px){
            .seat { width:30px; height:30px; line-height:30px; font-size:12px; margin:1px; }
            .aisle { width:15px; }
            .bus-top { width:80%; height:25px; line-height:25px; font-size:12px; }
        }
    </style>
</head>
<body>
<?php include __DIR__ . "/nav.php"; ?>

<h2>Pilih Kursi – <?= htmlspecialchars($jadwal['nama_bus']) ?> (<?= $jadwal['tanggal'] ?>)</h2>

<form method="POST" action="proses_booking.php">
    <input type="hidden" name="jadwal_id" value="<?= $jadwal_id ?>">

    <div class="bus-container">
        <div class="bus-top">Supir</div>

        <?php
        $rowSeats = array_chunk($seatmap, 4); 
        foreach($rowSeats as $row):
        ?>
        <div class="seat-row">
            <!-- Kursi kiri -->
            <?php foreach(array_slice($row,0,2) as $seat):
                $status = $seats[$seat] ?? 'kosong';
                $disabled = ($status !== 'kosong');
            ?>
                <div class="seat <?= $status ?>" data-seat="<?= $seat ?>">
                    <?= $seat ?>
                    <input type="checkbox" name="kursi[]" value="<?= $seat ?>" style="display:none;" <?= $disabled ? 'disabled' : '' ?>>
                </div>
            <?php endforeach; ?>

            <!-- Lorong -->
            <div class="aisle"></div>

            <!-- Kursi kanan -->
            <?php foreach(array_slice($row,2,2) as $seat):
                $status = $seats[$seat] ?? 'kosong';
                $disabled = ($status !== 'kosong');
            ?>
                <div class="seat <?= $status ?>" data-seat="<?= $seat ?>">
                    <?= $seat ?>
                    <input type="checkbox" name="kursi[]" value="<?= $seat ?>" style="display:none;" <?= $disabled ? 'disabled' : '' ?>>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <button type="submit">Pesan</button>
</form>
<?php include __DIR__ . "/footer.php"; ?>
<script>
document.querySelectorAll('.seat.kosong').forEach(seat => {
    seat.addEventListener('click', () => {
        const checkbox = seat.querySelector('input[type="checkbox"]');
        checkbox.checked = !checkbox.checked;
        seat.classList.toggle('selected', checkbox.checked);
    });
});
</script>
</body>
</html>
