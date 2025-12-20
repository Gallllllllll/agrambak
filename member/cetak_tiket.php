<?php
require "../config/database.php";
require "../vendor/autoload.php";

use Dompdf\Dompdf;

session_start();
if (!isset($_SESSION["user"])) {
    die("Akses ditolak");
}

if (!isset($_GET['id'])) {
    die("ID tidak ditemukan");
}

$reservasi_id = $_GET['id'];
$user_id = $_SESSION['user']['id'];

$stmt = $pdo->prepare("
    SELECT 
        r.*, j.tanggal, j.jam_berangkat, j.jam_tiba,
        b.nama_bus
    FROM reservasi r
    JOIN jadwal j ON r.jadwal_id = j.jadwal_id
    JOIN bus_armada b ON j.armada_id = b.armada_id
    JOIN pembayaran p ON r.reservasi_id = p.reservasi_id
    WHERE r.reservasi_id = ?
      AND r.user_id = ?
      AND r.status = 'dipesan'
      AND p.status = 'paid'
");
$stmt->execute([$reservasi_id, $user_id]);
$data = $stmt->fetch();

if (!$data) {
    die("Tiket belum bisa dicetak");
}

$html = "
<h2>TIKET BUS</h2>
<hr>
<p><b>Kode Booking:</b> {$data['kode_booking']}</p>
<p><b>Bus:</b> {$data['nama_bus']}</p>
<p><b>Tanggal:</b> {$data['tanggal']}</p>
<p><b>Jam:</b> {$data['jam_berangkat']} - {$data['jam_tiba']}</p>
<p><b>Jumlah Kursi:</b> {$data['jumlah_kursi']}</p>
<p><b>Total:</b> Rp" . number_format($data['total_harga']) . "</p>
<hr>
<p><i>Tunjukkan tiket ini saat check-in</i></p>
";

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("tiket_{$data['kode_booking']}.pdf", ["Attachment" => false]);
