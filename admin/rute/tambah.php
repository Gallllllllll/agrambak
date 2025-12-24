<?php
require __DIR__ . '/../../config/database.php';

// Ambil semua terminal untuk dropdown
$terminals = $pdo->query("SELECT * FROM terminal ORDER BY nama_terminal")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $asal_id = $_POST['asal_id'];
    $tujuan_id = $_POST['tujuan_id'];

    $stmt = $pdo->prepare("INSERT INTO rute (asal_id, tujuan_id) VALUES (?, ?)");
    $stmt->execute([$asal_id, $tujuan_id]);
    header("Location: index.php");
}
?>

<h2>Tambah Rute</h2>
<form method="post">
    Asal:
    <select name="asal_id" required>
        <?php foreach($terminals as $t): ?>
            <option value="<?= $t['terminal_id'] ?>"><?= $t['nama_terminal'] ?></option>
        <?php endforeach; ?>
    </select><br><br>

    Tujuan:
    <select name="tujuan_id" required>
        <?php foreach($terminals as $t): ?>
            <option value="<?= $t['terminal_id'] ?>"><?= $t['nama_terminal'] ?></option>
        <?php endforeach; ?>
    </select><br><br>

    <button type="submit">Simpan</button>
</form>
