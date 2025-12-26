<?php
session_start();
require "../../config/database.php";

if (!isset($_SESSION['user'])) {
    header("Location: ../../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $nama = $_POST['nama'];
    $telepon = $_POST['telepon'];
    $alamat = $_POST['alamat'];

    $foto_name = null;
    if(isset($_FILES['foto']) && $_FILES['foto']['error'] == 0){
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $foto_name = 'user_'.$user_id.'_'.time().'.'.$ext;
        move_uploaded_file($_FILES['foto']['tmp_name'], 'uploads/'.$foto_name);
    }

    if($foto_name){
        $stmt = $pdo->prepare("UPDATE users SET nama=?, telepon=?, alamat=?, foto=? WHERE user_id=?");
        $stmt->execute([$nama, $telepon, $alamat, $foto_name, $user_id]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET nama=?, telepon=?, alamat=? WHERE user_id=?");
        $stmt->execute([$nama, $telepon, $alamat, $user_id]);
    }

    header("Location: index.php");
    exit;
}
?>
