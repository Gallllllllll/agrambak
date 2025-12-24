<?php
require "../../middleware/auth.php";
admin_required();
require "../../config/database.php";

// ================== FILTER ==================
$q = $_GET['q'] ?? '';
$tipe_id = $_GET['tipe_id'] ?? '';
$sort = $_GET['sort'] ?? 'baru';

// ================== PAGINATION ==================
$limit = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1);
$offset = ($page - 1) * $limit;

// ================== COUNT DATA ==================
$countSql = "
    SELECT COUNT(*) 
    FROM bus_armada b
    WHERE 1
";
$params = [];

if ($q !== '') {
    $countSql .= " AND b.nama_bus LIKE ?";
    $params[] = "%$q%";
}
if ($tipe_id !== '') {
    $countSql .= " AND b.tipe_id = ?";
    $params[] = $tipe_id;
}

$stmt = $pdo->prepare($countSql);
$stmt->execute($params);
$totalData = $stmt->fetchColumn();
$totalPage = ceil($totalData / $limit);

// ================== DATA ARMADA ==================
$sql = "
    SELECT b.*, t.nama_tipe
    FROM bus_armada b
    LEFT JOIN armada_tipe t ON b.tipe_id = t.tipe_id
    WHERE 1
";

if ($q !== '') {
    $sql .= " AND b.nama_bus LIKE ?";
}
if ($tipe_id !== '') {
    $sql .= " AND b.tipe_id = ?";
}

$sql .= $sort === 'lama'
    ? " ORDER BY b.armada_id ASC"
    : " ORDER BY b.armada_id DESC";

$sql .= " LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$armada = $stmt->fetchAll();

// ================== TIPE BUS ==================
$tipeBus = $pdo->query("SELECT * FROM armada_tipe")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manajemen Armada Bus</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #333; padding: 8px; text-align: center; }
        th { background: #f0f0f0; }
        form { margin-bottom: 15px; }
        input, select, button { padding: 6px; }
        a { text-decoration: none; }
    </style>
</head>
<body>

<h2>Manajemen Armada Bus</h2>
<a href="../dashboard.php">← Kembali ke Dashboard</a>

<hr>

<!-- ================== FILTER ================== -->
<form method="GET">
    <input type="text" name="q" placeholder="Cari nama bus..." value="<?= htmlspecialchars($q) ?>">

    <select name="tipe_id">
        <option value="">-- Semua Tipe --</option>
        <?php foreach ($tipeBus as $t): ?>
            <option value="<?= $t['tipe_id'] ?>" <?= $tipe_id == $t['tipe_id'] ? 'selected' : '' ?>>
                <?= $t['nama_tipe'] ?>
            </option>
        <?php endforeach; ?>
    </select>

    <select name="sort">
        <option value="baru" <?= $sort == 'baru' ? 'selected' : '' ?>>Terbaru</option>
        <option value="lama" <?= $sort == 'lama' ? 'selected' : '' ?>>Terlama</option>
    </select>

    <button type="submit">Filter</button>
</form>

<!-- ================== TABEL ================== -->
<table>
<thead>
<tr>
    <th>No</th>
    <th>Nama Bus</th>
    <th>Tipe</th>
    <th>Kapasitas</th>
    <th>Deskripsi</th>
    <th>Aksi</th>
</tr>
</thead>

<tbody id="data-armada">
<?php if ($armada): ?>
    <?php foreach ($armada as $i => $b): ?>
    <tr>
        <td><?= $offset + $i + 1 ?></td>
        <td><?= htmlspecialchars($b['nama_bus']) ?></td>
        <td><?= $b['nama_tipe'] ?? '-' ?></td>
        <td><?= $b['kapasitas'] ?></td>
        <td><?= htmlspecialchars($b['deskripsi']) ?></td>
        <td>
            <a href="edit.php?id=<?= $b['armada_id'] ?>">Edit</a> |
            <a href="hapus.php?id=<?= $b['armada_id'] ?>" onclick="return confirm('Hapus bus ini?')">Hapus</a>
        </td>
    </tr>
    <?php endforeach; ?>
<?php else: ?>
<tr><td colspan="6">Data tidak ditemukan</td></tr>
<?php endif; ?>
</tbody>
</table>

<!-- ================== PAGINATION ================== -->
<?php if ($totalPage > 1): ?>
<div style="margin-top:15px;">
<?php for ($i = 1; $i <= $totalPage; $i++): ?>
    <a href="?page=<?= $i ?>&q=<?= urlencode($q) ?>&tipe_id=<?= $tipe_id ?>&sort=<?= $sort ?>"
       style="<?= $page == $i ? 'font-weight:bold;' : '' ?>">
        <?= $i ?>
    </a>
<?php endfor; ?>
</div>
<?php endif; ?>

<!-- ================== LIVE SEARCH ================== -->
<script>
const searchInput = document.querySelector('input[name="q"]');
const tableBody = document.getElementById('data-armada');

searchInput.addEventListener('keyup', function () {
    fetch('ajax_list.php?q=' + encodeURIComponent(this.value))
        .then(res => res.text())
        .then(html => tableBody.innerHTML = html);
});
</script>

</body>
</html>
