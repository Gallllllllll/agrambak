<?php
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];

// Path script sekarang, misal /UAS/bus-ticket/admin
$scriptName = dirname($_SERVER['SCRIPT_NAME']);

// Root project = folder sebelum /admin
$projectRoot = explode('/admin', $scriptName)[0];

// Base URL project (untuk assets dan link di luar admin)
$BASE_URL = $protocol . '://' . $host . $projectRoot . '/';

// Base URL admin (opsional, supaya link admin lebih ringkas)
$BASE_ADMIN_URL = $BASE_URL . 'admin/';
?>
