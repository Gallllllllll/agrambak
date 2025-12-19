<?php
session_start();
require "../config/database.php";

// Cek login admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Konfirmasi atau tolak pembayaran
if (isset($_GET['action'], $_GET['payment_id'])) {
    $action = $_GET['action'];
    $payment_id = $_GET['payment_id'];

    // Ambil reservasi_id dari pembayaran
    $stmt = $pdo->prepare("SELECT reservasi_id FROM pembayaran WHERE payment_id = ?");
    $stmt->execute([$payment_id]);
    $payment = $stmt->fetch();

    if ($payment) {
        $reservasi_id = $payment['reservasi_id'];

        if ($action === 'confirm') {
            // Update status pembayaran & reservasi
            $stmt1 = $pdo->prepare("UPDATE pembayaran SET status = 'confirmed' WHERE payment_id = ?");
            $stmt1->execute([$payment_id]);

            $stmt2 = $pdo->prepare("UPDATE reservasi SET status = 'confirmed' WHERE reservasi_id = ?");
            $stmt2->execute([$reservasi_id]);

        } elseif ($action === 'reject') {
            $stmt1 = $pdo->prepare("UPDATE pembayaran SET status = 'rejected' WHERE payment_id = ?");
            $stmt1->execute([$payment_id]);

            $stmt2 = $pdo->prepare("UPDATE reservasi SET status = 'rejected' WHERE reservasi_id = ?");
            $stmt2->execute([$reservasi_id]);
        }
    }
}

// Ambil semua pembayaran pending
$stmt = $pdo->prepare("
    SELECT p.payment_id, p.reservasi_id, p.bukti_transfer, p.status, r.user_id, r.jadwal_id, r.jumlah_kursi
    FROM pembayaran p
    JOIN reservasi r ON p.reservasi_id = r.reservasi_id
    WHERE p.status = 'pending'
    ORDER BY p.waktu_bayar DESC
");
$stmt->execute();
$payments = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Konfirmasi Pembayaran</title>
</head>
<body>
<h2>Konfirmasi Pembayaran</h2>

<table border="1" cellpadding="5" cellspacing="0">
    <tr>
        <th>Payment ID</th>
        <th>Reservasi ID</th>
        <th>User ID</th>
        <th>Jadwal ID</th>
        <th>Jumlah Kursi</th>
        <th>Bukti Transfer</th>
        <th>Status</th>
        <th>Aksi</th>
    </tr>
    <?php foreach ($payments as $pay): ?>
    <tr>
        <td><?= $pay['payment_id'] ?></td>
        <td><?= $pay['reservasi_id'] ?></td>
        <td><?= $pay['user_id'] ?></td>
        <td><?= $pay['jadwal_id'] ?></td>
        <td><?= $pay['jumlah_kursi'] ?></td>
        <td>
            <?php if($pay['bukti_transfer']): ?>
                <a href="../uploads/<?= $pay['bukti_transfer'] ?>" target="_blank">Lihat</a>
            <?php else: ?>
                Tidak ada
            <?php endif; ?>
        </td>
        <td><?= $pay['status'] ?></td>
        <td>
            <a href="?action=confirm&payment_id=<?= $pay['payment_id'] ?>">Konfirmasi</a> | 
            <a href="?action=reject&payment_id=<?= $pay['payment_id'] ?>">Tolak</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
</body>
</html>
