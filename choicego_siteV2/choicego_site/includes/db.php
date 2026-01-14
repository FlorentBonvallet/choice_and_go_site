<?php
// includes/db.php
$dsn = 'mysql:host=127.0.0.1;port=3306;dbname=ChoiceAndGo';
$dbUser = 'root';
$dbPass = ''; //Mettre le mot de passe pour se connecter a phpmyadmin

$options = [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES => false,
];

try {
  $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
} catch (PDOException $e) {
  http_response_code(500);
  echo 'Database connection failed: ' . htmlspecialchars($e->getMessage());
  exit;
}