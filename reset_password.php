<?php
session_start();
$page_title = "Réinitialiser le mot de passe — Choice&Go";
include __DIR__ . "/includes/db.php";

define('PASSWORD_PEPPER', 's3cureP3pp3r!@#');

$flash = '';
$showForm = false;
$token = $_GET['token'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (empty($token)) {
        $flash = '<p class="flash error">Lien invalide.</p>';
    } else {
        $token_hash = hash('sha256', $token);
        $stmt = $pdo->prepare('SELECT utilisateur_id, reset_expires_at FROM utilisateurs WHERE reset_token_hash = :hash LIMIT 1');
        $stmt->execute([':hash' => $token_hash]);
        $row = $stmt->fetch();

        if (!$row) {
            $flash = '<p class="flash error">Lien invalide ou expiré.</p>';
        } else {
            $expires = new DateTimeImmutable($row['reset_expires_at']);
            if ($expires < new DateTimeImmutable()) {
                $flash = '<p class="flash error">Ce lien a expiré.</p>';
            } else {
                $showForm = true;
            }
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if ($password === '' || $password_confirm === '') {
        $flash = '<p class="flash error">Veuillez remplir les deux champs de mot de passe.</p>';
        $showForm = true;
    } elseif ($password !== $password_confirm) {
        $flash = '<p class="flash error">Les mots de passe ne correspondent pas.</p>';
        $showForm = true;
    } elseif (strlen($password) < 8) {
        $flash = '<p class="flash error">Le mot de passe doit contenir au moins 8 caractères.</p>';
        $showForm = true;
    } else {
        $token_hash = hash('sha256', $token);
        $stmt = $pdo->prepare('SELECT utilisateur_id, reset_expires_at FROM utilisateurs WHERE reset_token_hash = :hash LIMIT 1');
        $stmt->execute([':hash' => $token_hash]);
        $row = $stmt->fetch();

        if (!$row || new DateTimeImmutable($row['reset_expires_at']) < new DateTimeImmutable()) {
            $flash = '<p class="flash error">Lien invalide ou expiré.</p>';
        } else {
            // Met à jour le mot de passe et supprime le token
            $hashed = password_hash($password . PASSWORD_PEPPER, PASSWORD_DEFAULT);
            $update = $pdo->prepare('UPDATE utilisateurs SET mot_de_passe = :pwd, reset_token_hash = NULL, reset_expires_at = NULL WHERE utilisateur_id = :uid');
            $update->execute([':pwd' => $hashed, ':uid' => $row['utilisateur_id']]);

            $_SESSION['flash_success'] = 'Mot de passe réinitialisé. Vous pouvez vous connecter.';
            $baseDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
            $location = ($baseDir === '' || $baseDir === '.') ? '/login.php' : $baseDir . '/login.php';
            header('Location: ' . $location);
            exit;
        }
    }
}

include __DIR__ . "/includes/header.php";
?>
<section class="container auth">
  <h1>Réinitialiser le mot de passe</h1>

  <?php echo $flash; ?>

  <?php if ($showForm): ?>
  <form class="auth-form" method="post" action="reset_password.php">
    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token, ENT_QUOTES); ?>" />
    <label>Nouveau mot de passe :
      <input type="password" name="password" placeholder="Nouveau mot de passe" required />
    </label>
    <label>Confirmer :
      <input type="password" name="password_confirm" placeholder="Confirmez le mot de passe" required />
    </label>
    <button class="btn-primary" type="submit">Définir le nouveau mot de passe</button>
  </form>
  <?php else: ?>
    <p class="help"><a href="forgot_password.php">Demander un nouveau lien de réinitialisation</a></p>
  <?php endif; ?>

</section>
<?php include __DIR__ . "/includes/footer.php"; ?>