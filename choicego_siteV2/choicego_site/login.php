<?php


//Define the secret pepper value (same as in register.php)
define('PASSWORD_PEPPER', 's3cureP3pp3r!@#');

// Hash a password by adding the pepper before hashing
function hashPasswordWithPepper($password) {
    return password_hash($password . PASSWORD_PEPPER, PASSWORD_DEFAULT);
}
//Verify a password by adding the pepper before checking
function verifyPasswordWithPepper($password, $hash) {
    return password_verify($password . PASSWORD_PEPPER, $hash);
}



session_start();

$page_title = "Connexion — Choice&Go";
include __DIR__ . "/includes/db.php";

$flash = '';

// If already logged in, go to profile
if (!empty($_SESSION['user_id'])) {
  $baseDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
  $location = ($baseDir === '' || $baseDir === '.') ? '/profile.php' : $baseDir . '/profile.php';
  header('Location: ' . $location);
  exit;
}

// Handle POST authentication before any HTML output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';

  if ($email === '' || $password === '') {
    $flash = '<p class="flash error">Veuillez saisir l\'e‑mail et le mot de passe.</p>';
  } else {
    $stmt = $pdo->prepare('SELECT utilisateur_id, mot_de_passe FROM utilisateurs WHERE email = :email');
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();
  // Check if user exists and verify password with pepper
   if ($user && !empty($user['mot_de_passe']) && verifyPasswordWithPepper($password, $user['mot_de_passe'])) {
      // Successful login
      session_regenerate_id(true);
      $_SESSION['user_id'] = (int) $user['utilisateur_id'];

      // Redirect relative to current script directory to preserve subfolder
      $baseDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
      $location = ($baseDir === '' || $baseDir === '.') ? '/profile.php' : $baseDir . '/profile.php';

      if (!headers_sent()) {
        header('Location: ' . $location);
        exit;
      } else {
        // Fallback JS redirect if headers already sent
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        echo '<script>location.href = ' . json_encode($scheme . '://' . $host . $location) . ';</script>';
        echo '<noscript><meta http-equiv="refresh" content="0;url=' . htmlspecialchars($scheme . '://' . $host . $location, ENT_QUOTES) . '"></noscript>';
        exit;
      }
    } else {
      $flash = '<p class="flash error">E‑mail ou mot de passe incorrect.</p>';
    }
  }
}

include __DIR__ . "/includes/header.php";
?>
<section class="container auth">
  <h1>CONNEXION</h1>

  <?php echo $flash; ?>

  <form class="auth-form" method="post" action="login.php">
    <label>Adresse mail :
      <input type="email" name="email" placeholder="Ex : martin.dupont@gmail.com" required />
    </label>
    <label>Mot de passe :
      <div class="password-field">
        <input type="password" name="password" placeholder="Ex : R95c@sd?4" required />
        <button type="button" class="toggle-eye" aria-label="Afficher le mot de passe">👁️</button>
      </div>
    </label>
    <p class="help"><a href="forgot_password.php">Mot de passe oublié ?</a></p>
    <button class="btn-primary" type="submit">Connexion</button>

    <p class="help">Pas encore de compte ? <a href="register.php">Inscrivez-vous</a></p>
  </form>
</section>
<?php include __DIR__ . "/includes/footer.php"; ?>
