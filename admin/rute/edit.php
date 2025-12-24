<?php
require __DIR__ . '/../../config/database.php';

$id = $_GET['id'];
$rute = $pdo->prepare("SELECT * FROM rute WHERE rute_id = ?");
$rute->execute([$id]);
$rute = $rute->fetch();

$terminals = $pdo->query("SELECT * FROM terminal ORDER BY nama_terminal")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $asal_id = $_POST['asal_id'];
    $tujuan_id = $_POST['tujuan_id'];

    $stmt = $pdo->prepare("UPDATE rute SET asal_id = ?, tujuan_id = ? WHERE rute_id = ?");
    $stmt->execute([$asal_id, $tujuan_id, $id]);
    header("Location: index.php");
}
?>

<h2>Edit Rute</h2>
<form method="post">
    Asal:
    <select name="asal_id" required>
        <?php foreach($terminals as $t): ?>
            <option value="<?= $t['terminal_id'] ?>" <?= $t['terminal_id'] == $rute['asal_id'] ? 'selected' : '' ?>>
                <?= $t['nama_terminal'] ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    Tujuan:
    <select name="tujuan_id" required>
        <?php foreach($terminals as $t): ?>
            <option value="<?= $t['terminal_id'] ?>" <?= $t['terminal_id'] == $rute['tujuan_id'] ? 'selected' : '' ?>>
                <?= $t['nama_terminal'] ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <button type="submit">Update</button>
</form>
