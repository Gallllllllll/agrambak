<?php
session_start();

function login_required() {
    if (!isset($_SESSION["user"])) {
        header("Location: ../auth/login.php");
        exit;
    }
}

function admin_required() {
    login_required();
    if ($_SESSION["user"]["role"] !== "admin") {
        die("Akses ditolak");
    }
}
