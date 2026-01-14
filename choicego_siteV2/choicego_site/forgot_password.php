<?php
session_start();
$page_title = "Mot de passe oublié — Choice&Go";
include __DIR__ . "/includes/db.php";

define('PASSWORD_PEPPER', 's3cureP3pp3r!@#');

$flash = '';
$popupContent = ''; // Contenu du mail pour la popup

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if ($email === '') {
        $flash = '<p class="flash error">Veuillez saisir votre adresse mail.</p>';
    } else {
        $stmt = $pdo->prepare('SELECT utilisateur_id, email FROM utilisateurs WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        $genericMsg = '<p class="flash success">Un e‑mail de réinitialisation a été envoyé si ce compte existe.</p>';

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $token_hash = hash('sha256', $token);
            $expires_at = (new DateTimeImmutable('+1 hour'))->format('Y-m-d H:i:s');

            $update = $pdo->prepare('UPDATE utilisateurs SET reset_token_hash = :hash, reset_expires_at = :expires WHERE utilisateur_id = :uid');
            $update->execute([
                ':hash' => $token_hash,
                ':expires' => $expires_at,
                ':uid' => $user['utilisateur_id']
            ]);

            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $baseDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
            $path = ($baseDir === '' || $baseDir === '.') ? '/reset_password.php' : $baseDir . '/reset_password.php';
            $resetUrl = $scheme . '://' . $host . $path . '?token=' . urlencode($token);

            // Contenu HTML du mail pour la popup
            $popupContent = "
                <div style='font-family: Arial, sans-serif; color:#333; line-height:1.5;'>
                    <h2 style='color:#007bff;'>Choice&Go</h2>
                    <p>Bonjour,</p>
                    <p>Vous avez demandé à réinitialiser votre mot de passe. Cliquez sur le bouton ci-dessous pour créer un nouveau mot de passe (valable 1 heure) :</p>
                    <p style='text-align:center; margin:2rem 0;'>
                        <a href='{$resetUrl}' style='background:#007bff; color:#fff; padding:12px 24px; border-radius:6px; text-decoration:none; display:inline-block;'>Réinitialiser le mot de passe</a>
                    </p>
                    <p>Si vous n'avez pas demandé cette réinitialisation, ignorez ce message.</p>
                    <hr style='border:none; border-top:1px solid #ccc; margin:1rem 0;'>
                    <p style='font-size:0.9rem; color:#666;'>Choice&Go - Support</p>
                </div>
            ";

            $flash = $genericMsg;
        } else {
            $flash = $genericMsg;
        }
    }
}

include __DIR__ . "/includes/header.php";
?>

<section class="container auth">
  <h1>Mot de passe oublié</h1>

  <?php echo $flash; ?>

  <form class="auth-form" method="post" action="forgot_password.php">
    <label>Adresse mail :
      <input type="email" name="email" placeholder="Ex : martin.dupont@gmail.com" required />
    </label>

    <button class="btn-primary" type="submit">Envoyer le lien de réinitialisation</button>

    <p class="help"><a href="login.php">Retour à la connexion</a></p>
  </form>
</section>

<?php if ($popupContent): ?>
<!-- Popup -->
<div id="mailPopup" class="popup-mail">
  <div class="popup-content">
    <span class="popup-close">&times;</span>
    <?php echo $popupContent; ?>
  </div>
</div>


<script>
  document.querySelector('.popup-close').addEventListener('click', () => {
    document.getElementById('mailPopup').style.display = 'none';
  });
</script>
<?php endif; ?>

<?php include __DIR__ . "/includes/footer.php"; ?>
