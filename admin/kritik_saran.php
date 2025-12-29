<?php
session_start();
require "../config/database.php";

// Cek login admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

/* =======================
   PROSES HAPUS KRITIK/SARAN
======================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hapus_id'])) {
    $hapus_id = $_POST['hapus_id'];
    $stmt = $pdo->prepare("DELETE FROM kritik_saran WHERE saran_id=?");
    $stmt->execute([$hapus_id]);
    header("Location: kritik_saran_admin.php");
    exit;
}

/* =======================
   AMBIL DATA KRITIK/SARAN
======================= */
$stmt = $pdo->query("
    SELECT saran_id, pesan, created_at
    FROM kritik_saran
    ORDER BY created_at DESC
");
$saran_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kelola Kritik & Saran</title>

<link rel="icon" href="../aset/img/logo-tranzio2.png" type="image/x-icon">
<link rel="stylesheet" href="../aset/css/dashboard_admin.css">
<link rel="stylesheet" href="../aset/css/users_admin.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
</head>
<body>

<?php include __DIR__ . '/sidebar.php'; ?>

<div class="main-content">

<div class="dashboard-header">
    <div>
        <h1>Kritik & Saran</h1>
        <p>Kelola semua masukan dari pengguna</p>
    </div>
</div>

<div class="table-responsive">
    <?php if ($saran_list): ?>
    <table id="saranTable" class="table table-striped align-middle">
        <thead>
            <tr>
                <th>Pesan</th>
                <th>Waktu Kirim</th>
                <th style="width:120px">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($saran_list as $s): ?>
            <tr>
                <td><?= nl2br(htmlspecialchars($s['pesan'])) ?></td>
                <td><?= $s['created_at'] ?></td>
                <td>
                    <form method="POST">
                        <input type="hidden" name="hapus_id" value="<?= $s['saran_id'] ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus pesan ini?')">
                            <i class="fa-solid fa-trash"></i> Hapus
                        </button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
        <p><i>Tidak ada kritik atau saran.</i></p>
    <?php endif; ?>
</div>

</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function () {
    $('#saranTable').DataTable({
        pageLength: 10,
        lengthChange: false,
        ordering: true,
        language: {
            search: "Cari:",
            paginate: { previous: "‹", next: "›" },
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ pesan",
            infoEmpty: "Data kosong",
            zeroRecords: "Data tidak ditemukan"
        }
    });
});
</script>

</body>
</html>
