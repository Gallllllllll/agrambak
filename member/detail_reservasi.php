<?php
session_start();
require "../config/database.php";

if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION["user"];
$reservasi_id = $_GET['reservasi_id'] ?? die("Reservasi tidak ditemukan");

// Ambil data reservasi + pembayaran
$stmt = $pdo->prepare("
    SELECT 
        r.*,

        p.status AS pembayaran_status,
        p.metode,
        p.bukti_transfer,
        p.waktu_bayar,

        j.tanggal,
        j.jam_berangkat,
        j.jam_tiba,

        ta.nama_terminal AS terminal_asal,
        ta.kota AS kota_asal,

        tt.nama_terminal AS terminal_tujuan,
        tt.kota AS kota_tujuan

    FROM reservasi r

    LEFT JOIN pembayaran p 
        ON r.reservasi_id = p.reservasi_id

    JOIN jadwal j 
        ON r.jadwal_id = j.jadwal_id

    JOIN rute ru 
        ON j.rute_id = ru.rute_id

    JOIN terminal ta 
        ON ru.asal_id = ta.terminal_id

    JOIN terminal tt 
        ON ru.tujuan_id = tt.terminal_id

    WHERE r.reservasi_id = ?
      AND r.user_id = ?
");

$stmt->execute([$reservasi_id, $user['user_id']]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) die("Data tidak ditemukan");

// Ambil data penumpang untuk reservasi ini
$stmt2 = $pdo->prepare("SELECT * FROM penumpang WHERE reservasi_id = ?");
$stmt2->execute([$reservasi_id]);
$penumpang_list = $stmt2->fetchAll(PDO::FETCH_ASSOC);

// Gunakan jumlah penumpang sebagai jumlah kursi
$jumlah_kursi = count($penumpang_list);
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Detail Reservasi - <?= htmlspecialchars($data['kode_booking']) ?></title>
<link rel="stylesheet" href="../aset/css/nav.css">
<link rel="stylesheet" href="../aset/css/footer.css">
<link rel="icon" href="../aset/img/logo-tranzio2.png" type="image/x-icon">
<style>
body {
    font-family: Arial, sans-serif;
    background: #2f405a;
    color: #333;
}

h2 {
    color: white;
    text-align: center;
    margin-bottom: 20px;
}

.card {
    background: #fff;
    border-radius: 15px;
    padding: 20px;
    margin: 0 20px 25px 20px;
    margin-bottom: 25px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    border: 1px solid #ddd;
}

.card h3 {
    margin-top: 0;
    margin-bottom: 15px;
    color: #2c3e50;
}

.card p {
    margin: 8px 0;
}

.table-wrapper {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

table th, table td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: left;
}

table th {
    background-color: #1f3556;
    font-weight: bold;
    color: white;
}

table tr:nth-child(even) {
    background-color: #f9f9f9;
}

/* Tombol */
.button-group {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin: 15px 20px 50px 20px;    
}

.button-group a, .button-group button {
    padding: 10px 15px;
    border-radius: 12px;
    font-size: 14px;
    font-weight: bold;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: 0.2s;
}

.button-back {
    background-color: #3498db;
    color: white;
}

.button-back:hover {
    background-color: #2d80c7;
}

.button-print {
    background-color: #27ae60;
    color: white;
}

.button-print:hover {
    background-color: #219150;
}

.info-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid #8f9398ff;
}

.info-table th {
    width: 35%;
    background: #1f3556;
    color: white;
    padding: 10px 14px;
    text-align: left;
    font-weight: bold;
}

.info-table td {
    background: #ffffff;
    padding: 10px 14px;
}


@media(max-width:600px){
    .card {
        padding: 15px;
    }
    table th, table td {
        padding: 6px;
    }
    .button-group {
        flex-direction: column;
    }
    .button-group a, .button-group button {
        width: 100%;
        text-align: center;
    }
}
</style>
</head>
<body>

<?php include __DIR__ . "/nav.php"; ?>

<h2>Detail Reservasi - <?= htmlspecialchars($data['kode_booking']) ?></h2>

<div class="card">
    <h3>Informasi Reservasi</h3>

    <div class="table-wrapper">
        <table class="info-table">
            <tr>
                <th>Kode Booking</th>
                <td><?= htmlspecialchars($data['kode_booking']) ?></td>
            </tr>
            <tr>
                <th>Jumlah Kursi</th>
                <td><?= $jumlah_kursi ?></td>
            </tr>
            <tr>
                <th>Lokasi Berangkat</th>
                <td><?= htmlspecialchars($data['terminal_asal']) ?></td>
            </tr>
            <tr>
                <th>Lokasi Tujuan</th>
                <td><?= htmlspecialchars($data['terminal_tujuan']) ?></td>
            </tr>
            <tr>
                <th>Jam Berangkat</th>
                <td><?= htmlspecialchars($data['jam_berangkat']) ?></td>
            </tr>
            <tr>
                <th>Jam Tiba</th>
                <td><?= htmlspecialchars($data['jam_tiba']) ?></td>
            </tr>
            <tr>
                <th>Total Harga</th>
                <td>Rp<?= number_format($data['total_harga'],0,',','.') ?></td>
            </tr>
            <tr>
                <th>Status Pembayaran</th>
                <td><?= strtoupper($data['pembayaran_status'] ?? 'MENUNGGU') ?></td>
            </tr>
            <tr>
                <th>Metode Pembayaran</th>
                <td><?= $data['metode'] ?? '-' ?></td>
            </tr>
            <tr>
                <th>Bukti Transfer</th>
                <td>
                    <?php if ($data['bukti_transfer']): ?>
                        <a href="../uploads/<?= htmlspecialchars($data['bukti_transfer']) ?>" target="_blank">Lihat Bukti</a>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th>Waktu Bayar</th>
                <td><?= $data['waktu_bayar'] ?? '-' ?></td>
            </tr>
        </table>
    </div>
</div>


<div class="card">
    <h3>Daftar Penumpang</h3>
    <div class="table-wrapper">
        <table>
            <tr>
                <th>No</th>
                <th>Nama Penumpang</th>
                <th>Nomor Kursi</th>
            </tr>
            <?php foreach ($penumpang_list as $index => $penumpang): ?>
            <tr>
                <td><?= $index + 1 ?></td>
                <td><?= htmlspecialchars($penumpang['nama_penumpang']) ?></td>
                <td><?= htmlspecialchars($penumpang['nomor_kursi']) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>

<div class="button-group">
    <a href="status_pemesanan.php" class="button-back">← Kembali</a>

    <?php if ($data['pembayaran_status'] === 'berhasil'): ?>
        <a href="cetak_tiket.php?reservasi_id=<?= $data['reservasi_id'] ?>" target="_blank" class="button-print">🧾 Cetak Tiket</a>
    <?php else: ?>
        <button disabled class="button-print">🧾 Cetak Tiket</button>
    <?php endif; ?>
</div>
<?php include __DIR__ . "/footer.php"; ?>
</body>
</html>
