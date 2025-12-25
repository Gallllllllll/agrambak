<?php
require "../config/database.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = trim($_POST['nama']);
    $email    = trim($_POST['email']);
    $telepon  = trim($_POST['telepon']);
    $password = md5($_POST['password']); // 🔴 MD5

    // cek email
    $cek = $pdo->prepare("SELECT user_id FROM users WHERE email=?");
    $cek->execute([$email]);

    if ($cek->rowCount() > 0) {
        die("Email sudah terdaftar");
    }

    $stmt = $pdo->prepare("
        INSERT INTO users (nama, email, password, telepon, role)
        VALUES (?, ?, ?, ?, 'user')
    ");
    $stmt->execute([$nama, $email, $password, $telepon]);

    // auto login
    session_start();
    $_SESSION['user'] = [
        'id'    => $pdo->lastInsertId(),
        'nama'  => $nama,
        'email' => $email,
        'role'  => 'user'
    ];

    header("Location: ../member/dashboard.php");
    exit;
}
