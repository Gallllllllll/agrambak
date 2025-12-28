<?php
session_start();
require "../config/database.php";

if (!isset($_SESSION["user"])) {
    header("Location: ../login.php");
    exit;
}

$user = $_SESSION["user"];

// Ambil jadwal dan kursi dari POST
$jadwal_id = $_POST['jadwal_id'] ?? null;
$kursi = $_POST['kursi'] ?? null;

if (!$jadwal_id) die("Jadwal tidak ditemukan.");

// Pastikan kursi selalu array
if (!is_array($kursi)) $kursi = [$kursi];
if (empty($kursi)) die("Pilih minimal 1 kursi.");

// Ambil harga dari jadwal
$stmt = $pdo->prepare("SELECT harga FROM jadwal WHERE jadwal_id=?");
$stmt->execute([$jadwal_id]);
$jadwal = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$jadwal) die("Jadwal tidak ditemukan.");

$total_harga = count($kursi) * $jadwal['harga'];

try {
    $pdo->beginTransaction();

    // Buat reservasi baru dengan status pending
    $kode_booking = "BKS".rand(1000,9999);
    $stmt = $pdo->prepare("INSERT INTO reservasi 
        (user_id, jadwal_id, kode_booking, jumlah_kursi, total_harga, status, waktu_pesan)
        VALUES (?, ?, ?, ?, ?, 'pending', NOW())
    ");
    $stmt->execute([$user['user_id'], $jadwal_id, $kode_booking, count($kursi), $total_harga]);
    $reservasi_id = $pdo->lastInsertId();

    // Prepare statement untuk cek kursi dan insert/update
    $stmtCheck = $pdo->prepare("SELECT * FROM seat_booking WHERE jadwal_id=? AND nomor_kursi=?");
    $stmtInsert = $pdo->prepare("INSERT INTO seat_booking 
        (jadwal_id, nomor_kursi, penumpang_id, status, reservasi_id) 
        VALUES (?, ?, NULL, 'kosong', ?)");
    $stmtUpdate = $pdo->prepare("UPDATE seat_booking SET reservasi_id=? WHERE jadwal_id=? AND nomor_kursi=?");

    foreach ($kursi as $seat) {
        $stmtCheck->execute([$jadwal_id, $seat]);
        $row = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            // Kursi sudah ada
            if ($row['status'] === 'terisi' || $row['status'] === 'diblock') {
                throw new Exception("Kursi $seat sudah tidak tersedia.");
            }
            // Update reservasi_id untuk kursi kosong yang lama
            $stmtUpdate->execute([$reservasi_id, $jadwal_id, $seat]);
        } else {
            // Insert kursi baru
            $stmtInsert->execute([$jadwal_id, $seat, $reservasi_id]);
        }
    }

    $pdo->commit();

    // Simpan kursi di session untuk upload pembayaran
    $_SESSION['selected_seats'][$reservasi_id] = $kursi;

    // Redirect ke halaman isi data penumpang
    header("Location: isi_data_penumpang.php?reservasi_id=$reservasi_id");
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    die("Gagal memproses booking: " . $e->getMessage());
}
