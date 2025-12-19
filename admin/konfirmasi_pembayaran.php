<?php
require "../middleware/auth.php";
admin_required();
require "../config/database.php";

// Proses approve/reject
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_id = $_POST['payment_id'];
    $action = $_POST['action'];

    // Ambil reservasi_id terkait
    $stmt = $pdo->prepare("SELECT reservasi_id FROM pembayaran WHERE payment_id = ?");
    $stmt->execute([$payment_id]);
    $reservasi_id = $stmt->fetchColumn();

    if (!$reservasi_id) {
        die("Reservasi tidak ditemukan untuk pembayaran ini.");
    }

    if ($action === 'approve') {
        // Update pembayaran menjadi paid
        $stmt = $pdo->prepare("UPDATE pembayaran SET status = 'paid' WHERE payment_id = ?");
        $stmt->execute([$payment_id]);

        // Update reservasi menjadi dipesan
        $stmt2 = $pdo->prepare("UPDATE reservasi SET status = 'dipesan' WHERE reservasi_id = ?");
        $stmt2->execute([$reservasi_id]);
    } elseif ($action === 'reject') {
        // Update pembayaran menjadi rejected
        $stmt = $pdo->prepare("UPDATE pembayaran SET status = 'rejected' WHERE payment_id = ?");
        $stmt->execute([$payment_id]);

        // Update reservasi menjadi batal
        $stmt2 = $pdo->prepare("UPDATE reservasi SET status = 'batal' WHERE reservasi_id = ?");
        $stmt2->execute([$reservasi_id]);
    }

    header("Location: konfirmasi_pembayaran.php");
    exit;
}

// Ambil pembayaran pending
$stmt = $pdo->query("
    SELECT p.payment_id, p.reservasi_id, p.ref_number, p.metode, p.jumlah, p.bukti_transfer, p.status,
           r.kode_booking, r.user_id
    FROM pembayaran p
    JOIN reservasi r ON p.reservasi_id = r.reservasi_id
    WHERE p.status = 'pending'
    ORDER BY p.waktu_bayar ASC
");
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Konfirmasi Pembayaran</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #333; padding: 8px; text-align: center; }
        th { background-color: #f0f0f0; }
        .btn { padding: 5px 10px; margin: 2px; cursor: pointer; border: none; border-radius: 4px; }
        .approve { background-color: green; color: #fff; }
        .reject { background-color: red; color: #fff; }
    </style>
</head>
<body>

<h2>Konfirmasi Pembayaran</h2>

<?php if ($payments): ?>
    <table>
        <tr>
            <th>Kode Booking</th>
            <th>Metode</th>
            <th>Jumlah</th>
            <th>Bukti Transfer</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
        <?php foreach ($payments as $p): ?>
            <tr>
                <td><?= htmlspecialchars($p['kode_booking']) ?></td>
                <td><?= htmlspecialchars($p['metode']) ?></td>
                <td>Rp<?= number_format($p['jumlah']) ?></td>
                <td>
                    <?php if ($p['bukti_transfer']): ?>
                        <a href="../uploads/<?= htmlspecialchars($p['bukti_transfer']) ?>" target="_blank">Lihat</a>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($p['status']) ?></td>
                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="payment_id" value="<?= $p['payment_id'] ?>">
                        <button type="submit" name="action" value="approve" class="btn approve">Approve</button>
                        <button type="submit" name="action" value="reject" class="btn reject">Reject</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php else: ?>
    <p>Tidak ada pembayaran pending.</p>
<?php endif; ?>

<br>
<a href="dashboard.php">← Kembali ke Dashboard Admin</a>

</body>
</html>
