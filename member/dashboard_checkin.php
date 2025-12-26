<?php
session_start();
require "../config/database.php";

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];
$message = '';
$seats = [];
$reservasi = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reservasi_id = $_POST['reservasi_id'] ?? '';
    if (!$reservasi_id) {
        $message = "ID reservasi wajib diisi.";
    } else {
        // Ambil reservasi + jadwal + pembayaran
        $stmt = $pdo->prepare("
            SELECT r.reservasi_id, r.jadwal_id, r.user_id, r.waktu_checkin,
                   r.jumlah_kursi, p.status AS p_status,
                   j.tanggal, ba.nama_bus
            FROM reservasi r
            LEFT JOIN pembayaran p ON p.reservasi_id = r.reservasi_id
                AND p.waktu_bayar = (SELECT MAX(waktu_bayar) FROM pembayaran WHERE reservasi_id = r.reservasi_id)
            JOIN jadwal j ON r.jadwal_id = j.jadwal_id
            JOIN bus_armada ba ON j.armada_id = ba.armada_id
            WHERE r.reservasi_id = ?
        ");
        $stmt->execute([$reservasi_id]);
        $reservasi = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$reservasi) {
            $message = "Reservasi tidak ditemukan.";
        } elseif ($reservasi['p_status'] !== 'berhasil') {
            $message = "Pembayaran belum valid.";
        } elseif (!empty($reservasi['waktu_checkin'])) {
            $message = "Reservasi sudah check-in.";
        } else {
            // Ambil kursi yang dipesan user
            $stmt2 = $pdo->prepare("SELECT nomor_kursi, status FROM seat_booking WHERE jadwal_id=? AND penumpang_id=?");
            $stmt2->execute([$reservasi['jadwal_id'], $reservasi['user_id']]);
            $seats = $stmt2->fetchAll(PDO::FETCH_ASSOC);

            // Update reservasi & kursi
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("UPDATE reservasi SET waktu_checkin=NOW() WHERE reservasi_id=?");
            $stmt->execute([$reservasi_id]);

            $stmt = $pdo->prepare("UPDATE seat_booking SET status='terisi' WHERE jadwal_id=? AND penumpang_id=?");
            $stmt->execute([$reservasi['jadwal_id'], $reservasi['user_id']]);

            $pdo->commit();

            $message = "Check-in berhasil!";

            // Refresh kursi setelah update
            $stmt2->execute([$reservasi['jadwal_id'], $reservasi['user_id']]);
            $seats = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Check-In Penumpang</title>
    <style>
        .seat { display:inline-block; width:30px; height:30px; margin:3px; text-align:center; line-height:30px; border:1px solid #333; }
        .kosong { background:#2ecc71; color:#000; }
        .diblock { background:#f1c40f; color:#000; }
        .terisi { background:#e74c3c; color:#fff; }
        .seat-container { margin-top:10px; }
    </style>
</head>
<body>
<h2>Check-In Penumpang</h2>

<form method="POST">
    <label>ID Reservasi:</label><br>
    <input type="number" name="reservasi_id" required>
    <button type="submit">Check-In</button>
</form>
<a href="dashboard.php">Dashboard</a>

<?php if ($message): ?>
    <p><strong><?= htmlspecialchars($message) ?></strong></p>
<?php endif; ?>

<?php if ($reservasi): ?>
    <p>Nama Bus: <?= htmlspecialchars($reservasi['nama_bus']) ?><br>
       Tanggal: <?= htmlspecialchars($reservasi['tanggal']) ?><br>
       Total Kursi: <?= htmlspecialchars($reservasi['jumlah_kursi']) ?>
    </p>
    <div class="seat-container">
        <?php foreach ($seats as $s): ?>
            <div class="seat <?= $s['status'] ?>"><?= $s['nomor_kursi'] ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

</body>
</html>
