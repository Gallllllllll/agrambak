<?php
require "../../middleware/auth.php";
admin_required();
require "../../config/database.php";

$id = $_GET['id'];

if ($_POST) {
    $stmt = $pdo->prepare("
        UPDATE users SET nama=?, email=?, telepon=?, role=? WHERE user_id=?
    ");
    $stmt->execute([
        $_POST['nama'],
        $_POST['email'],
        $_POST['telepon'],
        $_POST['role'],
        $id
    ]);
    header("Location: index.php");
    exit;
}

$user = $pdo->prepare("SELECT * FROM users WHERE user_id=?");
$user->execute([$id]);
$user = $user->fetch();
?>

<form method="POST">
    <input name="nama" value="<?= $user['nama'] ?>"><br>
    <input name="email" value="<?= $user['email'] ?>"><br>
    <input name="telepon" value="<?= $user['telepon'] ?>"><br>
    <select name="role">
        <option value="user" <?= $user['role']=='user'?'selected':'' ?>>User</option>
        <option value="admin" <?= $user['role']=='admin'?'selected':'' ?>>Admin</option>
    </select><br>
    <button>Simpan</button>
</form>
