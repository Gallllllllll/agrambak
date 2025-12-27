<?php
require "../config/database.php";

$asal    = $_GET['asal'] ?? '';
$tujuan  = $_GET['tujuan'] ?? '';
$tanggal = $_GET['tanggal'] ?? '';

$sql = "
SELECT 
    j.jadwal_id,
    j.tanggal,
    j.jam_berangkat,
    j.jam_tiba,
    j.harga,
    ba.nama_bus
FROM jadwal j
JOIN rute r ON j.rute_id = r.rute_id
JOIN bus_armada ba ON j.armada_id = ba.armada_id
WHERE r.asal_id = ?
AND r.tujuan_id = ?
AND j.tanggal = ?
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$asal, $tujuan, $tanggal]);
$data = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Hasil Pencarian Tiket</title>
<link rel="stylesheet" href="../aset/css/nav.css">
<style>
body {
    background: #2f405a;
    color: #333;
}
.container {
    max-width: 900px;
    margin: auto;
}
.card {
    background: #fff;
    border-radius: 20px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 10px 25px rgba(0,0,0,.15);
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.card-body p {
    margin: 6px 0;
}
.harga { font-weight: bold; color: #2c3e50; }
.btn {
    text-decoration: none;
    background: #27ae60;
    color: #fff;
    padding: 8px 16px;
    border-radius: 10px;
    font-weight: bold;
    transition: 0.2s;
}
.btn:hover { background: #219150; }
.empty {
    background: #fff;
    padding: 15px;
    border-radius: 10px;
    text-align: center;
    color: #888;
}
@media (max-width:600px){
    .card { flex-direction: column; align-items: flex-start; gap: 10px; }
    .btn { width: 100%; text-align: center; }
}
</style>
</head>
<body>

<?php include __DIR__ . "/nav.php"; ?>

<div class="container">
<h2 style="color:white;">Hasil Pencarian Tiket</h2>

<?php if(!$data): ?>
    <div class="empty">Tidak ada jadwal tersedia.</div>
<?php endif; ?>

<?php foreach($data as $row): ?>
    <div class="card">
        <div class="card-body">
            <p><b>Bus:</b> <?= htmlspecialchars($row['nama_bus']) ?></p>
            <p><b>Jam:</b> <?= $row['jam_berangkat'] ?> - <?= $row['jam_tiba'] ?></p>
            <p class="harga">Rp<?= number_format($row['harga'],0,',','.') ?></p>
        </div>
        <div>
            <a class="btn" href="pilih_kursi.php?jadwal_id=<?= $row['jadwal_id'] ?>">Pesan</a>
        </div>
    </div>
<?php endforeach; ?>

</div>

</body>
</html>
