<?php
require "../../middleware/auth.php";
admin_required();
require "../../config/database.php";

$tipe = $pdo->query("SELECT * FROM armada_tipe")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("
        INSERT INTO bus_armada (tipe_id, nama_bus, kapasitas, deskripsi)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([
        $_POST['tipe_id'],
        $_POST['nama_bus'],
        $_POST['kapasitas'],
        $_POST['deskripsi']
    ]);

    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../../aset/img/logo-tranzio2.png" type="image/x-icon">

    <!-- CSS GLOBAL -->
    <link rel="stylesheet" href="../../aset/css/dashboard_admin.css">
    <link rel="stylesheet" href="../../aset/css/users_admin.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <title>Tambah Armada Bus</title>
</head>
<body>
<?php include __DIR__ . '/../sidebar.php'; ?>

<div class="main-content">

    <!-- HEADER -->
    <div class="dashboard-header mb-4">
        <div>
            <h1>Tambah Armada Bus</h1>
            <p>Tambahkan data armada baru</p>
        </div>
    </div>

    <!-- FORM CARD -->
    <div class="card shadow-sm">
        <div class="card-body">

            <form method="POST">

                <div class="mb-3">
                    <label class="form-label fw-semibold">Tipe Bus</label>
                    <select name="tipe_id" class="form-select" required>
                        <option value="">-- Pilih Tipe Bus --</option>
                        <?php foreach ($tipe as $t): ?>
                            <option value="<?= $t['tipe_id'] ?>">
                                <?= htmlspecialchars($t['nama_tipe']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Nama Bus</label>
                    <input type="text"
                           name="nama_bus"
                           class="form-control"
                           placeholder="Contoh: Bus Pariwisata Executive"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Kapasitas</label>
                    <input type="number"
                           name="kapasitas"
                           class="form-control"
                           placeholder="Jumlah kursi"
                           required>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Deskripsi</label>
                    <textarea name="deskripsi"
                              class="form-control"
                              rows="3"
                              placeholder="Opsional"></textarea>
                </div>

                <!-- ACTION -->
                <div class="d-flex justify-content-between">
                    <a href="index.php" class="btn btn-light">
                        <i class="fa-solid fa-arrow-left"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save me-1"></i> Simpan Armada
                    </button>
                </div>

            </form>

        </div>
    </div>

</div>
</body>
</html>