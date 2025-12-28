<?php
require "../../middleware/auth.php";
admin_required();
require __DIR__ . '/../../config/database.php';

/*
|--------------------------------------------------------------------------
| VALIDASI ID
|--------------------------------------------------------------------------
*/
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header("Location: index.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| AMBIL DATA RUTE
|--------------------------------------------------------------------------
*/
$stmt = $pdo->prepare("SELECT * FROM rute WHERE rute_id = ?");
$stmt->execute([$id]);
$rute = $stmt->fetch();

if (!$rute) {
    header("Location: index.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| AMBIL DATA TERMINAL (LENGKAP)
|--------------------------------------------------------------------------
*/
$terminals = $pdo->query("
    SELECT terminal_id, nama_terminal, kota, kode
    FROM terminal
    ORDER BY nama_terminal ASC
")->fetchAll();

/*
|--------------------------------------------------------------------------
| PROSES UPDATE
|--------------------------------------------------------------------------
*/
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $asal_id   = $_POST['asal_id'] ?? '';
    $tujuan_id = $_POST['tujuan_id'] ?? '';

    if ($asal_id === '' || $tujuan_id === '') {
        $error = "Asal dan tujuan wajib dipilih.";
    } elseif ($asal_id === $tujuan_id) {
        $error = "Terminal asal dan tujuan tidak boleh sama.";
    } else {
        $stmt = $pdo->prepare("
            UPDATE rute 
            SET asal_id = ?, tujuan_id = ?
            WHERE rute_id = ?
        ");
        $stmt->execute([$asal_id, $tujuan_id, $id]);

        header("Location: index.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Rute</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- CSS -->
    <link rel="stylesheet" href="/agrambak/aset/css/dashboard_admin.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
</head>
<body>

<?php include __DIR__ . '/../sidebar.php'; ?>

<div class="main-content">

    <!-- HEADER -->
    <div class="dashboard-header mb-4">
        <div>
            <h1>Edit Rute</h1>
            <p>Perbarui rute perjalanan bus</p>
        </div>
    </div>

    <!-- FORM -->
    <div class="card shadow-sm">
        <div class="card-body">

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="post" class="row g-3">

                <!-- ASAL -->
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Terminal Asal</label>
                    <select name="asal_id" class="form-select" required>
                        <option value="">-- Pilih Terminal Asal --</option>
                        <?php foreach ($terminals as $t): ?>
                            <option value="<?= $t['terminal_id'] ?>"
                                <?= $t['terminal_id'] == $rute['asal_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($t['nama_terminal']) ?>
                                (<?= htmlspecialchars($t['kota']) ?> - <?= htmlspecialchars($t['kode']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- TUJUAN -->
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Terminal Tujuan</label>
                    <select name="tujuan_id" class="form-select" required>
                        <option value="">-- Pilih Terminal Tujuan --</option>
                        <?php foreach ($terminals as $t): ?>
                            <option value="<?= $t['terminal_id'] ?>"
                                <?= $t['terminal_id'] == $rute['tujuan_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($t['nama_terminal']) ?>
                                (<?= htmlspecialchars($t['kota']) ?> - <?= htmlspecialchars($t['kode']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- ACTION -->
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
