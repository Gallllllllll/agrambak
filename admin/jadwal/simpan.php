<?php
session_start();
require "../../config/database.php";


/* =======================
   AMBIL DATA FORM
======================= */
$rute_id       = $_POST['rute_id'];
$armada_id     = $_POST['armada_id'];
$tanggal       = $_POST['tanggal'];
$jam_berangkat = $_POST['jam_berangkat'];
$jam_tiba      = $_POST['jam_tiba'];
$harga         = $_POST['harga'];

/* =======================
   VALIDASI ARMADA
======================= */
$stmt = $pdo->prepare("
    SELECT kapasitas
    FROM bus_armada
    WHERE armada_id = ?
");
$stmt->execute([$armada_id]);
$armada = $stmt->fetch();

if (!$armada) {
    die("Armada tidak valid");
}

$kursi_tersedia = $armada['kapasitas'];

/* =======================
   CEK BENTROK JADWAL
======================= */
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM jadwal
    WHERE armada_id = ?
      AND tanggal = ?
      AND (
          jam_berangkat < ?
          AND jam_tiba > ?
      )
");
$stmt->execute([
    $armada_id,
    $tanggal,
    $jam_tiba,
    $jam_berangkat
]);

if ($stmt->fetchColumn() > 0) {
    die("Armada sudah digunakan pada jam tersebut");
}

/* =======================
   SIMPAN JADWAL
======================= */
$stmt = $pdo->prepare("
    INSERT INTO jadwal
    (rute_id, armada_id, tanggal, jam_berangkat, jam_tiba, harga, kursi_tersedia)
    VALUES (?,?,?,?,?,?,?)
");

$stmt->execute([
    $rute_id,
    $armada_id,
    $tanggal,
    $jam_berangkat,
    $jam_tiba,
    $harga,
    $kursi_tersedia
]);

header("Location: index.php?success=1");
exit;
