<?php
session_start();
require "../config/database.php";

if (!isset($_SESSION["user"])) {
    header("Location: ../login.php");
    exit;
}

$user = $_SESSION["user"];

$jadwal_id = $_POST['jadwal_id'] ?? null;
$kursi     = $_POST['kursi'] ?? null;

if (!$jadwal_id || !$kursi) {
    die("Data tidak lengkap.");
}

if (!is_array($kursi)) {
    $kursi = [$kursi];
}

// ambil harga
$stmt = $pdo->prepare("SELECT harga FROM jadwal WHERE jadwal_id=?");
$stmt->execute([$jadwal_id]);
$jadwal = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$jadwal) die("Jadwal tidak ditemukan.");

$total_harga = count($kursi) * $jadwal['harga'];

try {
    $pdo->beginTransaction();

    // bersihkan block expired
    $pdo->exec("
        UPDATE seat_booking
        SET status='kosong', blocked_until=NULL
        WHERE status='diblock' AND blocked_until < NOW()
    ");

    // buat reservasi
    $kode_booking = "BKS" . rand(1000,9999);
    $stmt = $pdo->prepare("
        INSERT INTO reservasi
        (user_id, jadwal_id, kode_booking, jumlah_kursi, total_harga, status, waktu_pesan)
        VALUES (?, ?, ?, ?, ?, 'pending', NOW())
    ");
    $stmt->execute([
        $user['user_id'],
        $jadwal_id,
        $kode_booking,
        count($kursi),
        $total_harga
    ]);
    $reservasi_id = $pdo->lastInsertId();

    // lock kursi
    $stmtCheck = $pdo->prepare("
        SELECT status, blocked_until
        FROM seat_booking
        WHERE jadwal_id=? AND nomor_kursi=?
        FOR UPDATE
    ");

    $stmtUpdate = $pdo->prepare("
        UPDATE seat_booking
        SET status='diblock',
            blocked_until = DATE_ADD(NOW(), INTERVAL 5 MINUTE)
        WHERE jadwal_id=? AND nomor_kursi=?
    ");

    $stmtInsert = $pdo->prepare("
        INSERT INTO seat_booking
        (jadwal_id, nomor_kursi, status, blocked_until)
        VALUES (?, ?, 'diblock', DATE_ADD(NOW(), INTERVAL 5 MINUTE))
    ");

    foreach ($kursi as $seat) {
        $stmtCheck->execute([$jadwal_id, $seat]);
        $row = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            if ($row['status'] === 'terisi'
                || ($row['status'] === 'diblock' && $row['blocked_until'] > date('Y-m-d H:i:s'))
            ) {
                throw new Exception("Kursi $seat sudah tidak tersedia.");
            }
            $stmtUpdate->execute([$jadwal_id, $seat]);
        } else {
            $stmtInsert->execute([$jadwal_id, $seat]);
        }
    }

    $pdo->commit();

    $_SESSION['selected_seats'][$reservasi_id] = $kursi;

    header("Location: isi_data_penumpang.php?reservasi_id=$reservasi_id");
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    die($e->getMessage());
}