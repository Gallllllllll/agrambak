<?php
session_start();
require "../config/database.php";

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = trim($_POST['nama']);
    $email    = trim($_POST['email']);
    $telepon  = trim($_POST['telepon']);
    $password = md5($_POST['password']); // sesuai DB

    // cek email sudah ada atau belum
    $cek = $pdo->prepare("SELECT user_id FROM users WHERE email=?");
    $cek->execute([$email]);

    if ($cek->rowCount() > 0) {
        $error = "Email sudah terdaftar";
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO users (nama, email, password, telepon, role)
            VALUES (?, ?, ?, ?, 'user')
        ");
        $stmt->execute([$nama, $email, $password, $telepon]);

        // auto login
        $_SESSION['user'] = [
            'user_id' => $pdo->lastInsertId(),
            'nama'    => $nama,
            'email'   => $email,
            'role'    => 'user'
        ];

        header("Location: ../member/dashboard.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Register - Tranzio</title>
<link rel="icon" href="../assets/logo-tranzio.png" type="image/x-icon">

<!-- Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
body {
    background: linear-gradient(to right, #384e90ff, #1a2c63ff);
    font-family: Arial, sans-serif;
    height: 100vh;
}

.login-container {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100%;
}

.login-card {
    background: white;
    padding: 40px 30px;
    border-radius: 10px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.2);
    width: 350px;
}

.login-card h2 {
    text-align: center;
    margin-bottom: 25px;
    color: #4e73df;
}

.form-control {
    margin-bottom: 15px;
    border-radius: 5px;
}

.btn-login {
    width: 100%;
    background-color: #4e73df;
    color: white;
    font-weight: bold;
    border-radius: 5px;
}

.btn-login:hover {
    background-color: #224abe;
}

.error-message {
    color: red;
    text-align: center;
    margin-bottom: 15px;
}

.logo {
    display: block;
    margin: 0 auto 20px;
    width: 100px;
}

.login-card .btn-back {
    margin-top: 10px;
    width: 100%;
    border: 1px solid #4e73df;
    color: #4e73df;
    font-weight: bold;
    border-radius: 5px;
    background: transparent;
}

.login-card .btn-back:hover {
    background-color: #4e73df;
    color: white;
}
</style>
</head>

<body>
<div class="login-container">
    <div class="login-card">
        <img src="../assets/logo-tranzio.png" alt="Tranzio" class="logo">
        <h2>Register</h2>

        <?php if ($error): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="text" name="nama" class="form-control" placeholder="Nama Lengkap" required>
            <input type="email" name="email" class="form-control" placeholder="Email" required>
            <input type="text" name="telepon" class="form-control" placeholder="Telepon" required>
            <input type="password" name="password" class="form-control" placeholder="Password" required>
            <button type="submit" class="btn btn-login">Register</button>
        </form>

        <div class="text-center mt-3">
            <p class="mb-2">
                Sudah punya akun? 
                <a href="login.php" style="color:#4e73df; font-weight:600;">Login</a>
            </p>
            <a href="../index.php" class="btn btn-back">
                <i class="fa fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
</div>
</body>
</html>
