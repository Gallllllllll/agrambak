<?php
require "../../middleware/auth.php";
admin_required();
require "../../config/database.php";

/* =======================
   VALIDASI ID
======================= */
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header("Location: index.php");
    exit;
}

/* =======================
   AMBIL DATA JADWAL
======================= */
$stmt = $pdo->prepare("
    SELECT j.*, ba.kapasitas AS kapasitas_lama
    FROM jadwal j
    JOIN bus_armada ba ON j.armada_id = ba.armada_id
    WHERE j.jadwal_id = ?
");
$stmt->execute([$id]);
$jadwal = $stmt->fetch();

if (!$jadwal) {
    header("Location: index.php");
    exit;
}

/* =======================
   AMBIL LIST RUTE
======================= */
$rutes = $pdo->query("
    SELECT r.rute_id,
           ta.nama_terminal AS asal,
           tt.nama_terminal AS tujuan
    FROM rute r
    JOIN terminal ta ON r.asal_id = ta.terminal_id
    JOIN terminal tt ON r.tujuan_id = tt.terminal_id
    ORDER BY asal, tujuan
")->fetchAll();

/* =======================
   AMBIL LIST ARMADA
======================= */
$armadas = $pdo->query("
    SELECT armada_id, nama_bus, kapasitas
    FROM bus_armada
    ORDER BY nama_bus
")->fetchAll();

$error = '';

/* =======================
   PROSES UPDATE
======================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $rute_id       = (int) $_POST['rute_id'];
    $armada_id     = (int) $_POST['armada_id'];
    $tanggal       = $_POST['tanggal'];
    $jam_berangkat = $_POST['jam_berangkat'];
    $jam_tiba      = $_POST['jam_tiba'];
    $harga         = (int) $_POST['harga'];

    if ($jam_tiba <= $jam_berangkat) {
        $error = "Jam tiba harus lebih besar dari jam berangkat.";
    } else {

        /* =======================
           AMBIL KAPASITAS ARMADA BARU
        ======================= */
        $stmtArmada = $pdo->prepare("
            SELECT kapasitas FROM bus_armada WHERE armada_id = ?
        ");
        $stmtArmada->execute([$armada_id]);
        $kapasitas_baru = $stmtArmada->fetchColumn();

        if (!$kapasitas_baru) {
            $error = "Armada tidak valid.";
        } else {

            // Hitung kursi terpakai
            $kursi_terpakai = $jadwal['kapasitas_lama'] - $jadwal['kursi_tersedia'];
            $kursi_tersedia_baru = $kapasitas_baru - $kursi_terpakai;

            if ($kursi_tersedia_baru < 0) {
                $error = "Armada baru tidak mencukupi jumlah reservasi yang sudah ada.";
            } else {

                /* =======================
                   UPDATE DATA
                ======================= */
                $stmt = $pdo->prepare("
                    UPDATE jadwal SET
                        rute_id = ?,
                        armada_id = ?,
                        tanggal = ?,
                        jam_berangkat = ?,
                        jam_tiba = ?,
                        harga = ?,
                        kursi_tersedia = ?
                    WHERE jadwal_id = ?
                ");
                $stmt->execute([
                    $rute_id,
                    $armada_id,
                    $tanggal,
                    $jam_berangkat,
                    $jam_tiba,
                    $harga,
                    $kursi_tersedia_baru,
                    $id
                ]);

                header("Location: index.php");
                exit;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Jadwal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- CSS -->
    <link rel="stylesheet" href="/agrambak/aset/css/dashboard_admin.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
</head>
<body>

<?php include __DIR__ . '/../sidebar.php'; ?>

<div class="main-content">

    <div class="dashboard-header mb-4">
        <div>
            <h1>Edit Jadwal</h1>
            <p>Perbarui jadwal keberangkatan bus</p>
        </div>
    </div>

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
                        <?php foreach ($rutes as $r): ?>
                            <option value="<?= $r['rute_id'] ?>"
                                <?= $r['rute_id'] == $jadwal['rute_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($r['asal']) ?> → <?= htmlspecialchars($r['tujuan']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Armada</label>
                    <select name="armada_id" class="form-select" required>
                        <?php foreach ($armadas as $a): ?>
                            <option value="<?= $a['armada_id'] ?>"
                                <?= $a['armada_id'] == $jadwal['armada_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($a['nama_bus']) ?> | <?= $a['kapasitas'] ?> kursi
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Tanggal</label>
                    <input type="date" name="tanggal" class="form-control"
                           value="<?= $jadwal['tanggal'] ?>" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Jam Berangkat</label>
                    <input type="time" name="jam_berangkat" class="form-control"
                           value="<?= substr($jadwal['jam_berangkat'], 0, 5) ?>" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Jam Tiba</label>
                    <input type="time" name="jam_tiba" class="form-control"
                           value="<?= substr($jadwal['jam_tiba'], 0, 5) ?>" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Harga</label>
                    <input type="number" name="harga" class="form-control"
                           value="<?= $jadwal['harga'] ?>" required>
                </div>

                <div class="col-12 d-flex justify-content-between mt-4">
                    <a href="index.php" class="btn btn-light">
                        <i class="fa-solid fa-arrow-left"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Simpan Perubahan
                    </button>
                </div>

            </form>

        </div>
    </div>

</div>

</body>
</html>
