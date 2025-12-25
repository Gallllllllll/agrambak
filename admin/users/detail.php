<?php
require "../../middleware/auth.php";
admin_required();
require __DIR__ . '/../../config/database.php';

$id = $_GET['id'];

$user = $pdo->prepare("SELECT * FROM users WHERE user_id=?");
$user->execute([$id]);
$user = $user->fetch();

$riwayat = $pdo->prepare("
    SELECT r.kode_booking, r.total_harga, r.status, r.waktu_pesan
    FROM reservasi r
    WHERE r.user_id=?
    ORDER BY r.waktu_pesan DESC
");
$riwayat->execute([$id]);
?>

<h3>Detail User</h3>
<p>Nama: <?= $user['nama'] ?></p>
<p>Email: <?= $user['email'] ?></p>
<p>Telepon: <?= $user['telepon'] ?></p>

<h4>Riwayat Pembelian</h4>
<table border="1">
<tr>
    <th>Kode</th>
    <th>Total</th>
    <th>Status</th>
    <th>Waktu</th>
</tr>
<?php foreach ($riwayat as $r): ?>
<tr>
    <td><?= $r['kode_booking'] ?></td>
    <td><?= number_format($r['total_harga']) ?></td>
    <td><?= $r['status'] ?></td>
    <td><?= $r['waktu_pesan'] ?></td>
</tr>
<?php endforeach; ?>
</table>

<a href="index.php">← Kembali</a>
