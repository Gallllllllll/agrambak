<?php
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];

// AUTO detect root folder
$scriptName = dirname($_SERVER['SCRIPT_NAME']);
$projectFolder = explode('/admin', $scriptName)[0];

$BASE_URL = rtrim($protocol . '://' . $host . $projectFolder, '/');
