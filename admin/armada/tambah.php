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

<h2>Tambah Armada Bus</h2>
<a href="index.php">← Kembali</a>

<form method="POST">
    <label>Tipe Bus</label><br>
    <select name="tipe_id" required>
        <?php foreach ($tipe as $t): ?>
            <option value="<?= $t['tipe_id'] ?>"><?= $t['nama_tipe'] ?></option>
        <?php endforeach; ?>
    </select><br><br>

    <label>Nama Bus</label><br>
    <input type="text" name="nama_bus" required><br><br>

    <label>Kapasitas</label><br>
    <input type="number" name="kapasitas" required><br><br>

    <label>Deskripsi</label><br>
    <textarea name="deskripsi"></textarea><br><br>

    <button type="submit">Simpan</button>
</form>
