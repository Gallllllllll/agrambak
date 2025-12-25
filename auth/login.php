<?php
session_start();
require "../config/database.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST["email"];
    $password = md5($_POST["password"]); // SESUAI DB

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND password = ?");
    $stmt->execute([$email, $password]);
    $user = $stmt->fetch();

    if ($user) {
        $_SESSION["user"] = [
            "id" => $user["user_id"],   // <-- sesuaikan dengan kolom di DB
            "name" => $user["name"],
            "email" => $user["email"],
            "role" => $user["role"]
        ];


        if ($user["role"] === "admin") {
            header("Location: ../admin/dashboard.php");
        } else {
            header("Location: ../member/dashboard.php");
        }
        exit;
    } else {
        $error = "Email atau password salah";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
</head>
<body>

<h2>Login</h2>

<?php if ($error): ?>
    <p style="color:red"><?= $error ?></p>
<?php endif; ?>

<form method="POST">
    <label>Email</label><br>
    <input type="email" name="email" required><br><br>

    <label>Password</label><br>
    <input type="password" name="password" required><br><br>

    <button type="submit">Login</button>

    <p align="center">
        Belum punya akun? <a href="register.php">Register</a>
    </p>
</form>

</body>
</html>
