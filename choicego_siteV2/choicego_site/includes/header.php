<?php
// includes/header.php

// Ensure session is started so we can check auth state
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Compute the app's base path relative to the web root (works in subfolders)
$docRoot = rtrim(str_replace('\\','/', realpath($_SERVER['DOCUMENT_ROOT'] ?? '')), '/');
$appRoot = rtrim(str_replace('\\','/', realpath(dirname(__DIR__))), '/');

$basePath = '';
if ($docRoot && $appRoot && strpos($appRoot, $docRoot) === 0) {
  $basePath = substr($appRoot, strlen($docRoot));
}
$baseHref = ($basePath === '' ? '/' : $basePath . '/');

// Avatar link: profile when logged in, otherwise login
$avatarHref = isset($_SESSION['user_id']) ? 'profile.php' : 'login.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo $page_title ?? "Choice&Go"; ?></title>

  <base href="<?php echo htmlspecialchars($baseHref, ENT_QUOTES); ?>">
  <link rel="stylesheet" href="assets/css/style.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body>
  <header class="site-header">
    <div class="container header-inner">
      <a class="logo" href="index.php">
        <img src="assets/img/logo.png" alt="Choice&Go" />
      </a>

      <form class="search-bar" action="index.php" method="get">
        <span class="icon">🔍</span>
        <input type="text" name="q" placeholder="Rechercher..." value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>" />
        <button type="submit" aria-label="Rechercher"></button>
      </form>

      <nav class="top-actions">
        <a class="btn-link" href="create_ride.php">➕ Ajouter un trajet</a>
        <a class="avatar" href="<?php echo $avatarHref; ?>" title="Mon compte">👤</a>
      </nav>
    </div>
  </header>
  <main class="page">
