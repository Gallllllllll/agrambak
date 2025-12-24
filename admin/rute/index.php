<?php
require __DIR__ . '/../../config/database.php'; // sesuaikan path

// Ambil list terminal untuk dropdown
$terminals = $pdo->query("SELECT terminal_id, nama_terminal FROM terminal ORDER BY nama_terminal ASC")->fetchAll();

// Filter
$asalFilter = isset($_GET['asal']) ? (int)$_GET['asal'] : '';
$tujuanFilter = isset($_GET['tujuan']) ? (int)$_GET['tujuan'] : '';

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page > 1) ? ($page - 1) * $limit : 0;

// Query dengan filter
$where = [];
$params = [];
if ($asalFilter) {
    $where[] = "r.asal_id = :asal";
    $params[':asal'] = $asalFilter;
}
if ($tujuanFilter) {
    $where[] = "r.tujuan_id = :tujuan";
    $params[':tujuan'] = $tujuanFilter;
}

$whereSQL = '';
if ($where) {
    $whereSQL = "WHERE " . implode(" AND ", $where);
}

// Ambil total data dengan filter
$stmtTotal = $pdo->prepare("SELECT COUNT(*) as total FROM rute r $whereSQL");
$stmtTotal->execute($params);
$totalData = $stmtTotal->fetch()['total'];
$totalPages = ceil($totalData / $limit);

// Ambil data rute dengan filter dan pagination
$stmt = $pdo->prepare("
    SELECT r.rute_id, t1.nama_terminal AS asal, t2.nama_terminal AS tujuan
    FROM rute r
    JOIN terminal t1 ON r.asal_id = t1.terminal_id
    JOIN terminal t2 ON r.tujuan_id = t2.terminal_id
    $whereSQL
    ORDER BY r.rute_id ASC
    LIMIT :start, :limit
");
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val, PDO::PARAM_INT);
}
$stmt->bindValue(':start', $start, PDO::PARAM_INT);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->execute();
$rutes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Data Rute</title>
</head>
<body>
    <h1>Daftar Rute</h1>
    <a href="create.php">Tambah Rute</a>

    <!-- Form Filter -->
    <form method="GET" style="margin-top:20px;">
        Asal:
        <select name="asal">
            <option value="">-- Semua --</option>
            <?php foreach($terminals as $t): ?>
                <option value="<?= $t['terminal_id'] ?>" <?= ($asalFilter == $t['terminal_id']) ? 'selected' : '' ?>>
                    <?= $t['nama_terminal'] ?>
                </option>
            <?php endforeach; ?>
        </select>
        Tujuan:
        <select name="tujuan">
            <option value="">-- Semua --</option>
            <?php foreach($terminals as $t): ?>
                <option value="<?= $t['terminal_id'] ?>" <?= ($tujuanFilter == $t['terminal_id']) ? 'selected' : '' ?>>
                    <?= $t['nama_terminal'] ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Filter</button>
    </form>

    <table border="1" cellpadding="10" cellspacing="0" style="margin-top:20px;">
        <tr>
            <th>ID</th>
            <th>Asal</th>
            <th>Tujuan</th>
            <th>Aksi</th>
        </tr>
        <?php foreach ($rutes as $rute): ?>
        <tr>
            <td><?= $rute['rute_id'] ?></td>
            <td><?= $rute['asal'] ?></td>
            <td><?= $rute['tujuan'] ?></td>
            <td>
                <a href="edit.php?id=<?= $rute['rute_id'] ?>">Edit</a> |
                <a href="delete.php?id=<?= $rute['rute_id'] ?>" onclick="return confirm('Yakin ingin hapus?')">Hapus</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <!-- Pagination -->
    <div style="margin-top:20px;">
        <?php if($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>&asal=<?= $asalFilter ?>&tujuan=<?= $tujuanFilter ?>">Prev</a>
        <?php endif; ?>

        <?php for($i=1; $i <= $totalPages; $i++): ?>
            <?php if($i == $page): ?>
                <strong><?= $i ?></strong>
            <?php else: ?>
                <a href="?page=<?= $i ?>&asal=<?= $asalFilter ?>&tujuan=<?= $tujuanFilter ?>"><?= $i ?></a>
            <?php endif; ?>
        <?php endfor; ?>

        <?php if($page < $totalPages): ?>
            <a href="?page=<?= $page + 1 ?>&asal=<?= $asalFilter ?>&tujuan=<?= $tujuanFilter ?>">Next</a>
        <?php endif; ?>
    </div>
</body>
</html>
