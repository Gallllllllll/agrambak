<?php
require "../../middleware/auth.php";
admin_required();
require "../../config/database.php";

$q = $_GET['q'] ?? '';
$role = $_GET['role'] ?? '';

$limit = 5;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $limit;

// COUNT
$countSql = "SELECT COUNT(*) FROM users WHERE 1";
$params = [];

if ($q) {
    $countSql .= " AND (nama LIKE ? OR email LIKE ?)";
    $params[] = "%$q%";
    $params[] = "%$q%";
}
if ($role) {
    $countSql .= " AND role=?";
    $params[] = $role;
}

$stmt = $pdo->prepare($countSql);
$stmt->execute($params);
$totalData = $stmt->fetchColumn();
$totalPage = ceil($totalData / $limit);

// DATA
$sql = "SELECT * FROM users WHERE 1";
if ($q) {
    $sql .= " AND (nama LIKE ? OR email LIKE ?)";
}
if ($role) {
    $sql .= " AND role=?";
}
$sql .= " ORDER BY user_id DESC LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();
?>

<h2>Manajemen User</h2>

<form method="GET">
    <input type="text" name="q" placeholder="Cari nama/email" value="<?= htmlspecialchars($q) ?>">
    <select name="role">
        <option value="">Semua Role</option>
        <option value="user" <?= $role=='user'?'selected':'' ?>>User</option>
        <option value="admin" <?= $role=='admin'?'selected':'' ?>>Admin</option>
    </select>
    <button>Filter</button>
</form>

<table border="1" width="100%">
<tr>
    <th>No</th>
    <th>Nama</th>
    <th>Email</th>
    <th>Telepon</th>
    <th>Role</th>
    <th>Aksi</th>
</tr>

<?php foreach ($users as $i => $u): ?>
<tr>
    <td><?= $offset + $i + 1 ?></td>
    <td><?= htmlspecialchars($u['nama']) ?></td>
    <td><?= $u['email'] ?></td>
    <td><?= $u['telepon'] ?></td>
    <td><?= $u['role'] ?></td>
    <td>
        <a href="detail.php?id=<?= $u['user_id'] ?>">Detail</a> |
        <a href="edit.php?id=<?= $u['user_id'] ?>">Edit</a> |
        <a href="hapus.php?id=<?= $u['user_id'] ?>" onclick="return confirm('Hapus user?')">Hapus</a>
    </td>
</tr>
<?php endforeach; ?>
</table>

<?php for ($i=1;$i<=$totalPage;$i++): ?>
<a href="?page=<?= $i ?>&q=<?= urlencode($q) ?>&role=<?= $role ?>"
   style="<?= $page==$i?'font-weight:bold':'' ?>">
<?= $i ?>
</a>
<?php endfor; ?>
