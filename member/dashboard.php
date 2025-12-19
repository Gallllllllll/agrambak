<?php
require "../middleware/auth.php";
login_required();
require "../config/database.php";

/**
 * Ambil semua terminal
 */
$terminals = $pdo->query("
    SELECT terminal_id, kota, nama_terminal
    FROM terminal
    ORDER BY kota ASC
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Pemesanan</title>
    <style>
        body { font-family: Arial; background:#f5f5f5; }
        .container {
            width: 500px;
            margin: 50px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
        }
        select, input, button {
            width: 100%;
            padding: 8px;
            margin-top: 10px;
        }
        button {
            background: #007bff;
            color: #fff;
            border: none;
            cursor: pointer;
        }
        nav a {
            margin-right: 10px;
        }
    </style>
</head>
<body>

<nav>
    <a href="dashboard.php">Dashboard</a>
    <a href="../auth/logout.php">Logout</a>
</nav>

<div class="container">
    <h2>Cari Tiket Bus</h2>

    <form action="hasil_tiket.php" method="GET">

        <!-- ASAL -->
        <label>Terminal Asal</label>
        <select name="asal" required>
            <option value="">-- Pilih Asal --</option>
            <?php foreach ($terminals as $t): ?>
                <option value="<?= $t['terminal_id'] ?>">
                    <?= $t['kota'] ?> - <?= $t['nama_terminal'] ?>
                </option>
            <?php endforeach; ?>
        </select>

        <!-- TUJUAN -->
        <label>Terminal Tujuan</label>
        <select name="tujuan" required>
            <option value="">-- Pilih Tujuan --</option>
            <?php foreach ($terminals as $t): ?>
                <option value="<?= $t['terminal_id'] ?>">
                    <?= $t['kota'] ?> - <?= $t['nama_terminal'] ?>
                </option>
            <?php endforeach; ?>
        </select>

        <!-- TANGGAL -->
        <label>Tanggal Berangkat</label>
        <input type="date" name="tanggal" required>

        <button type="submit">Cari Tiket</button>
    </form>
</div>

</body>
</html>
