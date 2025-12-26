<?php
require "../middleware/auth.php";
admin_required();
require "../config/database.php";

// Statistik
$totalReservasi = $pdo->query("SELECT COUNT(*) FROM reservasi")->fetchColumn();
$totalPendapatan = $pdo->query("
    SELECT SUM(jumlah) 
    FROM pembayaran 
    WHERE status = 'berhasil'
")->fetchColumn();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Admin</title>
    <style>
        body { font-family: Arial; background: #f4f6f8; }
        h1 { margin-bottom: 5px; }

        nav {
            background: #333;
            padding: 10px;
        }
        nav a {
            color: white;
            margin-right: 15px;
            text-decoration: none;
            font-weight: bold;
        }
        nav a:hover {
            text-decoration: underline;
        }

        .cards {
            display: flex;
            gap: 20px;
            margin-top: 20px;
        }

        .card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            width: 220px;
            box-shadow: 0 2px 5px rgba(0,0,0,.1);
        }

        .card h3 {
            margin: 0;
            font-size: 16px;
            color: #555;
        }

        .card p {
            font-size: 22px;
            margin-top: 10px;
            font-weight: bold;
        }
    </style>
</head>
<body>

<h1>Dashboard Admin</h1>
<p>Selamat datang, <b>Administrator</b></p>

<nav>
    <a href="dashboard.php">Dashboard</a>
    <a href="users/index.php">CRUD User</a>
    <a href="armada/index.php">CRUD Armada</a>
    <a href="rute/index.php">CRUD Rute</a>
    <a href="jadwal/index.php">CRUD Jadwal</a>
    <a href="konfirmasi_pembayaran.php">Konfirmasi Pembayaran</a>
    <a href="konfirmasi_refund.php">Konfirmasi Refund</a>
    <a href="../auth/logout.php">Logout</a>
</nav>


<div class="cards">
    <div class="card">
        <h3>Total Reservasi</h3>
        <p><?= $totalReservasi ?></p>
    </div>

    <div class="card">
        <h3>Total Pendapatan</h3>
        <p>Rp<?= number_format($totalPendapatan ?? 0, 0, ',', '.') ?></p>
    </div>
</div>

</body>
</html>
