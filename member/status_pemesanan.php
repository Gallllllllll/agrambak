<?php
session_start();
require "../config/database.php";

if (!isset($_SESSION["user"])) {
    header("Location: dashboard.php");
    exit;
}

$user = $_SESSION["user"];
$message = "";

/* =====================
   PROSES CHECK-IN
===================== */
if (isset($_POST['checkin_reservasi_id'])) {
    $rid = $_POST['checkin_reservasi_id'];

    $stmt = $pdo->prepare("SELECT jadwal_id, waktu_checkin FROM reservasi WHERE reservasi_id=? AND user_id=?");
    $stmt->execute([$rid, $user['user_id']]);
    $res = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$res) {
        $message = "Reservasi tidak ditemukan.";
    } elseif (!empty($res['waktu_checkin'])) {
        $message = "Reservasi sudah check-in.";
    } else {
        $pdo->beginTransaction();

        $pdo->prepare("UPDATE reservasi SET waktu_checkin=NOW() WHERE reservasi_id=?")
            ->execute([$rid]);

        $stmt = $pdo->prepare("SELECT nomor_kursi FROM seat_booking WHERE jadwal_id=? AND penumpang_id=?");
        $stmt->execute([$res['jadwal_id'], $user['user_id']]);
        $kursi = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $stmt = $pdo->prepare("UPDATE seat_booking SET status='terisi' WHERE jadwal_id=? AND nomor_kursi=?");
        foreach ($kursi as $k) {
            $stmt->execute([$res['jadwal_id'], $k]);
        }

        $pdo->commit();
        $message = "Check-in berhasil.";
    }
}

/* =====================
   AMBIL DATA RESERVASI
===================== */
$stmt = $pdo->prepare("
    SELECT r.*, 
           p.status AS pembayaran_status,
           b.status AS refund_status
    FROM reservasi r
    LEFT JOIN pembayaran p ON p.reservasi_id = r.reservasi_id
        AND p.waktu_bayar = (
            SELECT MAX(waktu_bayar) FROM pembayaran WHERE reservasi_id = r.reservasi_id
        )
    LEFT JOIN pembatalan b ON b.reservasi_id = r.reservasi_id
    WHERE r.user_id = ?
    ORDER BY r.waktu_pesan DESC
");
$stmt->execute([$user['user_id']]);
$reservasi = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tiket Saya</title>
<link rel="stylesheet" href="../aset/css/nav.css">

<style>
* { box-sizing: border-box; }

body {
    margin: 0;
    font-family: 'Segoe UI', sans-serif;
    background: #2f405a;
    color: #333;
}

/* ===== CONTAINER ===== */
.container {
    max-width: 900px;
    margin: auto;
    padding: 20px;
}

/* ===== MESSAGE ===== */
.alert {
    background: #e3f2fd;
    padding: 12px 16px;
    border-radius: 10px;
    margin-bottom: 20px;
}

/* ===== TICKET CARD ===== */
.ticket-card {
    background: #fff;
    border-radius: 20px;
    padding: 20px;
    margin-bottom: 25px;
    box-shadow: 0 10px 25px rgba(0,0,0,.15);
}

.ticket-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.ticket-header small {
    color: #777;
}

.ticket-header h3 {
    margin: 4px 0 0;
    letter-spacing: 1px;
}

/* ===== BADGE ===== */
.badge {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: bold;
    color: white;
}
.badge-paid { background: #6fcf97; }
.badge-pending { background: #f2c94c; }
.badge-unpaid { background: #eb5757; }
.badge-checkin { background: #3498db; }

/* ===== BODY ===== */
.ticket-body {
    margin-top: 15px;
    font-size: 14px;
}
.ticket-body p {
    margin: 6px 0;
}

/* ===== ACTIONS ===== */
.ticket-actions {
    margin-top: 15px;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.ticket-status {
    display: flex;
    align-items: center;
    gap: 8px; /* jarak antara badge dan logo */
}

.badge-logo-right {
    height: 20px; /* ukuran logo */
    width: auto;
}


.btn {
    border: none;
    padding: 8px 14px;
    border-radius: 10px;
    font-size: 13px;
    color: #fff;
    cursor: pointer;
    text-decoration: none;
}

.btn-detail { background: #2d9cdb; }
.btn-checkin { background: #27ae60; }
.btn-refund { background: #f2994a; }
.btn-disabled {
    background: #bdbdbd;
    cursor: not-allowed;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 600px) {
    .ticket-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    .ticket-actions {
        flex-direction: column;
    }
    .btn {
        width: 100%;
        text-align: center;
    }
}
</style>
</head>

<body>

<?php include __DIR__ . "/nav.php"; ?>

<div class="container">

<h2 style="color:white;">Tiket Saya</h2>

<?php if ($message): ?>
    <div class="alert"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<?php if ($reservasi): ?>
<?php foreach ($reservasi as $r): ?>

<?php
$status = $r['pembayaran_status'] ?? 'belum_bayar';
$badgeClass = 'badge-unpaid';
$badgeText  = 'Belum Lunas';

if ($status === 'berhasil') {
    $badgeClass = 'badge-paid';
    $badgeText  = 'Lunas';
} elseif ($status === 'pending') {
    $badgeClass = 'badge-pending';
    $badgeText  = 'Menunggu';
}

if (!empty($r['waktu_checkin'])) {
    $badgeClass = 'badge-checkin';
    $badgeText  = 'Sudah Check-In';
}
?>

<div class="ticket-card">

    <div class="ticket-header">
        <div>
            <small>Kode Pemesanan</small>
            <h3><?= htmlspecialchars($r['kode_booking']) ?></h3>
        </div>
        <div class="ticket-status">
            <span class="badge <?= $badgeClass ?>"><?= $badgeText ?></span>
            <img src="../assets/logo-tranzio.png" alt="Tranzio" class="badge-logo-right">
        </div>
    </div>


    <div class="ticket-body">
        <p><strong>Jumlah Kursi:</strong> <?= $r['jumlah_kursi'] ?></p>
        <p><strong>Total Harga:</strong> Rp<?= number_format($r['total_harga'],0,',','.') ?></p>
    </div>

    <div class="ticket-actions">
        <a href="detail_reservasi.php?reservasi_id=<?= $r['reservasi_id'] ?>" class="btn btn-detail">Detail</a>

        <?php if ($status === 'berhasil' && empty($r['waktu_checkin'])): ?>
            <form method="POST" style="display:inline;">
                <input type="hidden" name="checkin_reservasi_id" value="<?= $r['reservasi_id'] ?>">
                <button class="btn btn-checkin">Check-In</button>
            </form>
        <?php else: ?>
            <button class="btn btn-disabled" disabled>Check-In</button>
        <?php endif; ?>

        <?php if ($status === 'berhasil' && empty($r['waktu_checkin'])): ?>
            <a href="ajukan_refund.php?reservasi_id=<?= $r['reservasi_id'] ?>" class="btn btn-refund">Refund</a>
        <?php else: ?>
            <button class="btn btn-disabled" disabled>Refund</button>
        <?php endif; ?>
    </div>


</div>

<?php endforeach; ?>
<?php else: ?>
<p style="color:white;">Belum ada tiket.</p>
<?php endif; ?>

</div>

</body>
</html>
