<?php
session_start();
require "../../config/database.php";


/* =======================
   AMBIL DATA RUTE
======================= */
$rute = $pdo->query("
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
$armada = $pdo->query("
    SELECT armada_id,
           nama_bus,
           kapasitas,
           deskripsi
    FROM bus_armada
    ORDER BY nama_bus
")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tambah Jadwal</title>
</head>
<body>

<h2>Tambah Jadwal</h2>

<form action="simpan.php" method="POST">

    <label>Rute</label><br>
    <select name="rute_id" required>
        <option value="">-- Pilih Rute --</option>
        <?php foreach ($rute as $r): ?>
            <option value="<?= $r['rute_id'] ?>">
                <?= $r['asal'] ?> → <?= $r['tujuan'] ?>
            </option>
        <?php endforeach ?>
    </select>
    <br><br>

    <label>Armada</label><br>
    <select name="armada_id" required>
        <option value="">-- Pilih Armada --</option>
        <?php foreach ($armada as $a): ?>
            <option value="<?= $a['armada_id'] ?>">
                <?= $a['nama_bus'] ?> | <?= $a['kapasitas'] ?> kursi
            </option>
        <?php endforeach ?>
    </select>

    <br><br>

    <label>Tanggal</label><br>
    <input type="date" name="tanggal" required>
    <br><br>

    <label>Jam Berangkat</label><br>
    <input type="time" name="jam_berangkat" required>
    <br><br>

    <label>Jam Tiba</label><br>
    <input type="time" name="jam_tiba" required>
    <br><br>

    <label>Harga</label><br>
    <input type="number" name="harga" required>
    <br><br>

    <button type="submit">Simpan Jadwal</button>

</form>

</body>
</html>
