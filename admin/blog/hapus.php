<?php
require "../../middleware/auth.php";
admin_required();
require "../../config/database.php";

$blog_id = $_GET['blog_id'] ?? die("Blog tidak ditemukan");

// Ambil gambar dulu
$stmt = $pdo->prepare("SELECT gambar FROM blog WHERE blog_id=?");
$stmt->execute([$blog_id]);
$blog = $stmt->fetch();
if($blog && $blog['gambar'] && file_exists("../../uploads/".$blog['gambar'])){
    unlink("../../uploads/".$blog['gambar']);
}

// Hapus blog
$stmt = $pdo->prepare("DELETE FROM blog WHERE blog_id=?");
$stmt->execute([$blog_id]);

header("Location: index.php");
exit;
