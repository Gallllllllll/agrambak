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
if (isset($_POST['kode_booking'])) {
    $kode = trim($_POST['kode_booking']);

    $stmt = $pdo->prepare("
        SELECT 
            r.reservasi_id,
            r.jadwal_id,
            r.waktu_checkin,
            p.status AS pembayaran_status,
            b.status AS refund_status
        FROM reservasi r
        LEFT JOIN pembayaran p 
            ON p.reservasi_id = r.reservasi_id
            AND p.waktu_bayar = (
                SELECT MAX(waktu_bayar) 
                FROM pembayaran 
                WHERE reservasi_id = r.reservasi_id
            )
        LEFT JOIN pembatalan b 
            ON b.reservasi_id = r.reservasi_id
        WHERE r.kode_booking = ?
        AND r.user_id = ?
    ");
    $stmt->execute([$kode, $user['user_id']]);
    $res = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$res) {
        $message = "Kode booking tidak ditemukan.";

    } elseif ($res['pembayaran_status'] !== 'berhasil') {
        $message = "Check-in gagal. Tiket belum lunas atau pembayaran gagal.";

    } elseif (!empty($res['refund_status']) && $res['refund_status'] === 'Disetujui') {
        $message = "Check-in tidak dapat dilakukan. Tiket telah direfund.";

    } elseif (!empty($res['waktu_checkin'])) {
        $message = "Tiket ini sudah check-in.";

    } else {

        try {
            $pdo->beginTransaction();

            // simpan waktu check-in
            $pdo->prepare("
                UPDATE reservasi 
                SET waktu_checkin = NOW() 
                WHERE reservasi_id = ?
            ")->execute([$res['reservasi_id']]);

            // update kursi jadi terisi
            $stmt = $pdo->prepare("
                UPDATE seat_booking 
                SET status = 'terisi' 
                WHERE jadwal_id = ?
                AND penumpang_id = ?
            ");
            $stmt->execute([$res['jadwal_id'], $user['user_id']]);

            $pdo->commit();
            $message = "Check-in berhasil.";

        } catch (Exception $e) {
            $pdo->rollBack();
            $message = "Check-in gagal. Silakan coba lagi.";
        }
    }

}


/* =====================
   AMBIL DATA RESERVASI
===================== */
$stmt = $pdo->prepare("
    SELECT 
        r.*, 
        p.status AS pembayaran_status,
        b.status AS refund_status,

        j.tanggal,
        j.jam_berangkat,
        j.jam_tiba,

        ta.nama_terminal AS terminal_asal,
        tt.nama_terminal AS terminal_tujuan

    FROM reservasi r

    JOIN jadwal j ON r.jadwal_id = j.jadwal_id
    JOIN rute ru ON j.rute_id = ru.rute_id
    JOIN terminal ta ON ru.asal_id = ta.terminal_id
    JOIN terminal tt ON ru.tujuan_id = tt.terminal_id

    LEFT JOIN pembayaran p 
        ON p.reservasi_id = r.reservasi_id
        AND p.waktu_bayar = (
            SELECT MAX(waktu_bayar) 
            FROM pembayaran 
            WHERE reservasi_id = r.reservasi_id
        )

    LEFT JOIN pembatalan b 
        ON b.reservasi_id = r.reservasi_id

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
<link rel="stylesheet" href="../aset/css/footer.css">
<link rel="icon" href="../aset/img/logo-tranzio2.png" type="image/x-icon">

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
.badge-refund { background: #f2994a; }

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

/* ===== ROUTE INFO ===== */
.route-box {
    margin-top: 18px;
    margin-bottom: 10px;
    padding-left: 10px;
}

.route-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
}

.route-icon {
    width: 14px;
    height: 14px;
    background: #2d9cdb;
    border-radius: 50%;
    margin-top: 6px;
    position: relative;
}

.route-line {
    width: 2px;
    height: 28px;
    background: #2d9cdb;
    margin-left: 6px;
    margin-bottom: 6px;
}

.route-info strong {
    font-size: 16px;
    color: #2f405a;
}

.route-info span {
    display: block;
    font-weight: 600;
    color: #333;
}

.route-info small {
    color: #777;
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
<button class="btn btn-checkin" 
        style="width:100%; margin-bottom:25px; padding:14px; font-size:16px;"
        onclick="openCheckinModal()">
    Check-In Tiket
</button>

<?php if ($message): ?>
    <div class="alert"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<?php if ($reservasi): ?>
<?php foreach ($reservasi as $r): ?>

<?php
$status = $r['pembayaran_status'] ?? 'belum_bayar';
$badgeClass = 'badge-unpaid';
$badgeText  = 'Gagal';

if ($status === 'berhasil') {
    $badgeClass = 'badge-paid';
    $badgeText  = 'Lunas';
} elseif ($status === 'pending') {
    $badgeClass = 'badge-pending';
    $badgeText  = 'Menunggu';
}

// jika refund disetujui
if (!empty($r['refund_status']) && $r['refund_status'] === 'Disetujui') {
    $badgeClass = 'badge-refund';
    $badgeText  = 'Refund Disetujui';
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

    <div class="route-box">

        <div class="route-item">
            <div class="route-icon"></div>
            <div class="route-info">
                <strong><?= $r['jam_berangkat'] ?></strong>
                <span><?= htmlspecialchars($r['terminal_asal']) ?></span>
                <small><?= date('d F Y', strtotime($r['tanggal'])) ?></small>
            </div>
        </div>

        <div class="route-line"></div>

        <div class="route-item">
            <div class="route-icon"></div>
            <div class="route-info">
                <strong><?= $r['jam_tiba'] ?></strong>
                <span><?= htmlspecialchars($r['terminal_tujuan']) ?></span>
                <small><?= date('d F Y', strtotime($r['tanggal'])) ?></small>
            </div>
        </div>

    </div>


    <div class="ticket-body">
        <p><strong>Jumlah Kursi:</strong> <?= $r['jumlah_kursi'] ?></p>
        <p><strong>Total Harga:</strong> Rp<?= number_format($r['total_harga'],0,',','.') ?></p>
    </div>

    <div class="ticket-actions">
        <a href="detail_reservasi.php?reservasi_id=<?= $r['reservasi_id'] ?>" class="btn btn-detail">Detail</a>

        <?php
        // tombol Refund hanya aktif jika pembayaran Lunas, belum check-in, dan refund belum disetujui
        if ($status === 'berhasil' && empty($r['waktu_checkin']) && (empty($r['refund_status']) || $r['refund_status'] !== 'Disetujui')): ?>
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
<!-- MODAL CHECK-IN -->
<div id="checkinModal" style="
    display:none;
    position:fixed;
    inset:0;
    background:rgba(0,0,0,.6);
    justify-content:center;
    align-items:center;
    z-index:9999;
">
    <div style="background:#fff; padding:25px; border-radius:15px; width:320px;">
        <h3>Check-In Tiket</h3>

        <form method="POST">
            <input type="text" name="kode_booking"
                   placeholder="Masukkan kode booking"
                   required
                   style="width:100%; padding:10px; margin:10px 0;">
            <button class="btn btn-checkin" style="width:100%;">
                Check-In
            </button>
        </form>

        <button onclick="closeCheckinModal()" 
                style="margin-top:10px; width:100%; border-radius:15px;">
            Batal
        </button>
    </div>
</div>

<script>
function openCheckinModal() {
    document.getElementById('checkinModal').style.display = 'flex';
}
function closeCheckinModal() {
    document.getElementById('checkinModal').style.display = 'none';
}
</script>

<?php include __DIR__ . "/footer.php"; ?>
</body>
</html>
