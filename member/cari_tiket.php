<?php
require "../middleware/auth.php";
login_required();
require "../config/database.php";

$terminal = $pdo->query("SELECT * FROM terminal")->fetchAll();
?>

<form action="hasil_tiket.php" method="GET">
    <select name="asal">
        <?php foreach ($terminal as $t): ?>
            <option value="<?= $t["terminal_id"] ?>">
                <?= $t["kota"] ?>
            </option>
        <?php endforeach ?>
    </select>

    <select name="tujuan">
        <?php foreach ($terminal as $t): ?>
            <option value="<?= $t["terminal_id"] ?>">
                <?= $t["kota"] ?>
            </option>
        <?php endforeach ?>
    </select>

    <input type="date" name="tanggal" required>
    <button type="submit">Cari Tiket</button>
</form>
