<?php
require "../../middleware/auth.php";
admin_required();
require "../../config/database.php";

$id = $_GET['id'];

$stmt = $pdo->prepare("DELETE FROM bus_armada WHERE armada_id = ?");
$stmt->execute([$id]);

header("Location: index.php");
exit;
