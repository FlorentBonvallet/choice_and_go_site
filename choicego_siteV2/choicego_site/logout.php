<?php
session_start();

// Destroy the session
session_unset();
session_destroy();

// Redirect to login page
$baseDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$location = ($baseDir === '' || $baseDir === '.') ? '/login.php' : $baseDir . '/login.php';

header('Location: ' . $location);
exit;