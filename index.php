<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
    <title>Bus Ticket</title>
</head>
<body>

<nav>
    <a href="index.php">Home</a>
    <?php if (!isset($_SESSION["user"])): ?>
        <a href="auth/login.php">Login</a>
        <a href="auth/register.php">Register</a>
    <?php else: ?>
        <a href="auth/logout.php">Logout</a>
    <?php endif ?>
</nav>

<h1>Website Pemesanan Tiket Bus</h1>
<p>Cari dan pesan tiket bus dengan mudah</p>

</body>
</html>
