<?php
session_start();

function login_required() {
    if (
        !isset($_SESSION['user']) ||
        (
            !is_array($_SESSION['user']) &&
            !is_numeric($_SESSION['user'])
        )
    ) {
        // jangan langsung ke login, arahkan ke halaman publik
        header("Location: ../index.php?error=login_required");
        exit;
    }
}

function admin_required() {
    login_required();

    // pastikan user berupa array dan punya role
    if (
        !is_array($_SESSION['user']) ||
        !isset($_SESSION['user']['role']) ||
        $_SESSION['user']['role'] !== 'admin'
    ) {
        http_response_code(403);
        die("Akses ditolak");
    }
}
