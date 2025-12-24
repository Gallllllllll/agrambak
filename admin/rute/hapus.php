<?php
require __DIR__ . '/../../config/database.php';

$id = $_GET['id'];
$stmt = $pdo->prepare("DELETE FROM rute WHERE rute_id = ?");
$stmt->execute([$id]);

header("Location: index.php");
