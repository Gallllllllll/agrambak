<?php
session_start();
require "../config/database.php";

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];

if (!isset($_GET['reservasi_id'])) {
    die("Reservasi tidak ditemukan");
}

$reservasi_id = $_GET['reservasi_id'];
$sukses = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $alasan = trim($_POST['alasan'] ?? '');
    if (!$alasan) die("Alasan harus diisi");

    // Cegah double refund
    $cek = $pdo->prepare("SELECT 1 FROM pembatalan WHERE reservasi_id = ?");
    $cek->execute([$reservasi_id]);

    if ($cek->fetch()) {
        die("Refund sudah pernah diajukan.");
    }

    $stmt = $pdo->prepare("
        INSERT INTO pembatalan (reservasi_id, user_id, alasan, status, waktu_ajukan)
        VALUES (?, ?, ?, 'Menunggu', NOW())
    ");
    $stmt->execute([$reservasi_id, $user['id'], $alasan]);


    $sukses = true;
}
?>

<?php if ($sukses): ?>
    <h3>✅ Refund berhasil diajukan</h3>
    <p>Menunggu konfirmasi admin.</p>
    <a href="status_pemesanan.php">
        <button>Kembali ke Status Pemesanan</button>
    </a>
<?php else: ?>
    <form method="POST">
        <label>Alasan Refund:</label><br>
        <textarea name="alasan" required></textarea><br><br>
        <button type="submit">Ajukan Refund</button>
        <br><br>
        <a href="status_pemesanan.php">← Kembali</a>
    </form>
<?php endif; ?>
