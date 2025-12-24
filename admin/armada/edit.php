<?php
require "../../middleware/auth.php";
admin_required();
require "../../config/database.php";

$id = $_GET['id'];

$armada = $pdo->prepare("SELECT * FROM bus_armada WHERE armada_id = ?");
$armada->execute([$id]);
$data = $armada->fetch();

if (!$data) die("Data tidak ditemukan");

$tipe = $pdo->query("SELECT * FROM armada_tipe")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("
        UPDATE bus_armada
        SET tipe_id=?, nama_bus=?, kapasitas=?, deskripsi=?
        WHERE armada_id=?
    ");
    $stmt->execute([
        $_POST['tipe_id'],
        $_POST['nama_bus'],
        $_POST['kapasitas'],
        $_POST['deskripsi'],
        $id
    ]);

    header("Location: index.php");
    exit;
}
?>

<h2>Edit Armada Bus</h2>
<a href="index.php">← Kembali</a>

<form method="POST">
    <label>Tipe Bus</label><br>
    <select name="tipe_id">
        <?php foreach ($tipe as $t): ?>
            <option value="<?= $t['tipe_id'] ?>" <?= $t['tipe_id']==$data['tipe_id']?'selected':'' ?>>
                <?= $t['nama_tipe'] ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <label>Nama Bus</label><br>
    <input type="text" name="nama_bus" value="<?= $data['nama_bus'] ?>" required><br><br>

    <label>Kapasitas</label><br>
    <input type="number" name="kapasitas" value="<?= $data['kapasitas'] ?>" required><br><br>

    <label>Deskripsi</label><br>
    <textarea name="deskripsi"><?= $data['deskripsi'] ?></textarea><br><br>

    <button type="submit">Update</button>
</form>
