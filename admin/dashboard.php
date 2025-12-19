<?php
require "../middleware/auth.php";
admin_required();
require "../config/database.php";

$total = $pdo->query("SELECT COUNT(*) total FROM reservasi")->fetch();
$pendapatan = $pdo->query("SELECT SUM(total_harga) total FROM reservasi")->fetch();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Admin</title>
    <style>
        nav a {
            margin-right: 15px;
            text-decoration: none;
            color: blue;
        }
        nav a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<h1>Dashboard Admin</h1>

<nav>
    <a href="dashboard.php">Home</a>
    <a href="konfirmasi_pembayaran.php">Konfirmasi Pembayaran</a>
    <a href="logout.php">Logout</a>
</nav>

<hr>

<p>Total Reservasi: <?= $total["total"] ?></p>
<p>Total Pendapatan: Rp<?= number_format($pendapatan["total"]) ?></p>

</body>
</html>
