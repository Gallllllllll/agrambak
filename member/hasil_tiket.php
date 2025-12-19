<?php
require "../config/database.php";

$asal    = $_GET['asal'];
$tujuan  = $_GET['tujuan'];
$tanggal = $_GET['tanggal'];

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

<h2>Hasil Pencarian Tiket</h2>

<?php if (count($data) == 0): ?>
    <p>Tidak ada jadwal tersedia.</p>
<?php endif; ?>

<?php foreach ($data as $row): ?>
    <div style="border:1px solid #ccc; padding:10px; margin-bottom:10px">
        <p><b>Bus:</b> <?= $row['nama_bus'] ?></p>
        <p><b>Jam:</b> <?= $row['jam_berangkat'] ?> - <?= $row['jam_tiba'] ?></p>
        <p><b>Harga:</b> Rp<?= number_format($row['harga']) ?></p>
        <a href="pilih_kursi.php?jadwal_id=<?= $row['jadwal_id'] ?>">
            Pesan
        </a>
    </div>
<?php endforeach; ?>
