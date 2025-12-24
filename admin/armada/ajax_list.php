<?php
require "../../middleware/auth.php";
admin_required();
require "../../config/database.php";

$q = $_GET['q'] ?? '';

$sql = "
    SELECT b.*, t.nama_tipe
    FROM bus_armada b
    LEFT JOIN armada_tipe t ON b.tipe_id = t.tipe_id
    WHERE b.nama_bus LIKE ?
    ORDER BY b.armada_id DESC
    LIMIT 10
";

$stmt = $pdo->prepare($sql);
$stmt->execute(["%$q%"]);
$data = $stmt->fetchAll();

if (!$data) {
    echo "<tr><td colspan='6'>Data tidak ditemukan</td></tr>";
    exit;
}

foreach ($data as $i => $b):
?>
<tr>
    <td><?= $i+1 ?></td>
    <td><?= htmlspecialchars($b['nama_bus']) ?></td>
    <td><?= $b['nama_tipe'] ?? '-' ?></td>
    <td><?= $b['kapasitas'] ?></td>
    <td><?= htmlspecialchars($b['deskripsi']) ?></td>
    <td>
        <a href='edit.php?id=<?= $b['armada_id'] ?>'>Edit</a>
        <a href='hapus.php?id=<?= $b['armada_id'] ?>'
        onclick="return confirm('Hapus data?')">Hapus</a>
    </td>
</tr>
<?php endforeach; ?>
