<?php
require "../../middleware/auth.php";
admin_required();
require "../../config/database.php";

$id = $_GET['id'];

$pdo->prepare("DELETE FROM users WHERE user_id=?")->execute([$id]);

header("Location: index.php");
exit;
