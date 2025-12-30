<?php
require "../../middleware/auth.php";
admin_required();
require "../../config/database.php";

/* =======================
   AMBIL DATA RUTE
======================= */
$rutes = $pdo->query("
    SELECT r.rute_id,
           ta.nama_terminal AS asal,
           tt.nama_terminal AS tujuan
    FROM rute r
    JOIN terminal ta ON r.asal_id = ta.terminal_id
    JOIN terminal tt ON r.tujuan_id = tt.terminal_id
    ORDER BY ta.nama_terminal, tt.nama_terminal
")->fetchAll();

/* =======================
   AMBIL DATA ARMADA
======================= */
$armadas = $pdo->query("
    SELECT armada_id, nama_bus, kapasitas
    FROM bus_armada
    ORDER BY nama_bus
")->fetchAll();

$error = '';

/* =======================
   SIMPAN DATA
======================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $rute_id       = (int) $_POST['rute_id'];
    $armada_id     = (int) $_POST['armada_id'];
    $tanggal       = $_POST['tanggal'];
    $jam_berangkat = $_POST['jam_berangkat'];
    $jam_tiba      = $_POST['jam_tiba'];
    $harga         = (int) $_POST['harga'];

    /* =======================
       VALIDASI DASAR
    ======================= */
    if ($jam_tiba <= $jam_berangkat) {
        $error = "Jam tiba harus lebih besar dari jam berangkat.";
    } else {

        // Ambil kapasitas armada
        $stmtArmada = $pdo->prepare("
            SELECT kapasitas 
            FROM bus_armada 
            WHERE armada_id = ?
        ");
        $stmtArmada->execute([$armada_id]);
        $kapasitas = $stmtArmada->fetchColumn();

        if (!$kapasitas || $kapasitas <= 0) {
            $error = "Armada tidak valid atau kapasitas kosong.";
        } else {

            // INSERT jadwal (kursi_tersedia = kapasitas)
            $stmt = $pdo->prepare("
                INSERT INTO jadwal
                (rute_id, armada_id, tanggal, jam_berangkat, jam_tiba, harga, kursi_tersedia)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $rute_id,
                $armada_id,
                $tanggal,
                $jam_berangkat,
                $jam_tiba,
                $harga,
                $kapasitas
            ]);

            header("Location: index.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Jadwal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../../aset/img/logo-tranzio2.png" type="image/x-icon">

    <!-- CSS -->
    <link rel="stylesheet" href="../../aset/css/dashboard_admin.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
</head>
<body>

<?php include __DIR__ . '/../sidebar.php'; ?>

<div class="main-content">

    <!-- HEADER -->
    <div class="dashboard-header mb-4">
        <div>
            <h1>Tambah Jadwal</h1>
            <p>Buat jadwal keberangkatan bus baru</p>
        </div>
    </div>

    <!-- FORM CARD -->
    <div class="card shadow-sm">
        <div class="card-body">

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="post" class="row g-3">

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Rute</label>
                    <select name="rute_id" class="form-select" required>
                        <option value="">-- Pilih Rute --</option>
                        <?php foreach ($rutes as $r): ?>
                            <option value="<?= $r['rute_id'] ?>">
                                <?= htmlspecialchars($r['asal']) ?> → <?= htmlspecialchars($r['tujuan']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Armada</label>
                    <select name="armada_id" class="form-select" required>
                        <option value="">-- Pilih Armada --</option>
                        <?php foreach ($armadas as $a): ?>
                            <option value="<?= $a['armada_id'] ?>">
                                <?= htmlspecialchars($a['nama_bus']) ?> | <?= $a['kapasitas'] ?> kursi
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Tanggal</label>
                    <input type="date" name="tanggal" class="form-control" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Jam Berangkat</label>
                    <input type="time" name="jam_berangkat" class="form-control" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Jam Tiba</label>
                    <input type="time" name="jam_tiba" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Harga</label>
                    <input type="number"
                           name="harga"
                           class="form-control"
                           min="0"
                           step="10000"
                           required>
                </div>

                <!-- ACTION -->
                <div class="col-12 d-flex justify-content-between mt-4">
                    <a href="index.php" class="btn btn-light">
                        <i class="fa-solid fa-arrow-left"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Simpan Jadwal
                    </button>
                </div>

            </form>

        </div>
    </div>

</div>

</body>
</html>
