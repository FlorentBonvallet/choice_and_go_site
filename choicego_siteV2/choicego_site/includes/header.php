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
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0" />
  <title><?php echo $page_title ?? "Choice&Go"; ?></title>

  <base href="<?php echo htmlspecialchars($baseHref, ENT_QUOTES); ?>">
  <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body>
  <!-- Mobile overlay for menu -->
  <div class="mobile-overlay" id="mobile-overlay"></div>
  
  <header class="site-header">
    <div class="container header-inner">
      <a class="logo" href="index.php">
        <img src="assets/img/logo.png" alt="Choice&Go" />
      </a>

      <a class="slogan">Un slogant vraiment super génial</a>

      <!-- Mobile menu toggle button -->
      <button class="mobile-menu-toggle" id="mobile-menu-toggle" aria-label="Ouvrir le menu" aria-expanded="false">
        ☰
      </button>

      <nav class="top-actions" id="top-actions">
        <!-- Close button for mobile menu -->
        <button class="mobile-menu-close" id="mobile-menu-close" aria-label="Fermer le menu">✕</button>
        <a class="btn-link" href="create_ride.php"> Ajouter un trajet</a>
        <?php if (isset($_SESSION['user_id'])): ?>
        <a class="btn-link" href="reservations.php"> Mes réservations</a>
        <?php endif; ?>
        <a class="avatar" href="<?php echo $avatarHref; ?>" title="Mon compte">👤</a>
      </nav>
    </div>
  </header>
  <main class="page">
