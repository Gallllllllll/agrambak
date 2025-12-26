<?php
require "../../config/database.php";

$id = $_GET['id'];

$stmt = $pdo->prepare("DELETE FROM jadwal WHERE jadwal_id=?");
$stmt->execute([$id]);

header("Location: index.php");
