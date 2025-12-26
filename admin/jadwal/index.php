<?php
require "../../config/database.php";

$stmt = $pdo->query("
    SELECT j.*, 
           ta.nama_terminal AS asal,
           tt.nama_terminal AS tujuan,
           ba.nama_bus
    FROM jadwal j
    JOIN rute r ON j.rute_id = r.rute_id
    JOIN terminal ta ON r.asal_id = ta.terminal_id
    JOIN terminal tt ON r.tujuan_id = tt.terminal_id
    JOIN bus_armada ba ON j.armada_id = ba.armada_id
    ORDER BY j.tanggal, j.jam_berangkat
");
$jadwal = $stmt->fetchAll();
?>

<h2>Data Jadwal</h2>
<a href="tambah.php">+ Tambah Jadwal</a>

<table border="1" cellpadding="6">
<tr>
  <th>Rute</th>
  <th>Tanggal</th>
  <th>Jam</th>
  <th>Armada</th>
  <th>Harga</th>
  <th>Aksi</th>
</tr>

<?php foreach ($jadwal as $j): ?>
<tr>
  <td><?= $j['asal'] ?> → <?= $j['tujuan'] ?></td>
  <td><?= $j['tanggal'] ?></td>
  <td><?= $j['jam_berangkat'] ?> - <?= $j['jam_tiba'] ?></td>
  <td><?= $j['nama_bus'] ?></td>
  <td>Rp<?= number_format($j['harga']) ?></td>
  <td>
    <a href="edit.php?id=<?= $j['jadwal_id'] ?>">Edit</a> |
    <a href="hapus.php?id=<?= $j['jadwal_id'] ?>" 
       onclick="return confirm('Hapus jadwal?')">Hapus</a>
  </td>
</tr>
<?php endforeach ?>
</table>
