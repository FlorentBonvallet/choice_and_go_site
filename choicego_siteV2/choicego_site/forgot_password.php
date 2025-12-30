<?php
session_start();
$page_title = "Mot de passe oublié — Choice&Go";
include __DIR__ . "/includes/db.php";

// Si vous avez installé PHPMailer via Composer, chargez l'autoload
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} else {
    // composer non présent : on continue mais l'envoi via SMTP ne fonctionnera pas
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Configuration SMTP — adaptez ces valeurs à votre serveur
// Exemple : Gmail avec application spécifique ou serveur SMTP tiers
$smtpHost = 'smtp-relay.brevo.com';
$smtpPort = 587;
$smtpUser = 'apikey';
$smtpPass = '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';
$smtpSecure = PHPMailer::ENCRYPTION_STARTTLS;
$fromEmail = 'florentbonvallet@gmail.com';
$fromName = 'Choice&Go';

define('PASSWORD_PEPPER', 's3cureP3pp3r!@#');

$flash = '';





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

            $subject = 'Réinitialisation de votre mot de passe — Choice&Go';
            $bodyText = "Bonjour,\n\nPour réinitialiser votre mot de passe, cliquez sur ce lien (valable 1 heure) :\n\n" . $resetUrl . "\n\nSi vous n'avez pas demandé cette réinitialisation, ignorez ce message.\n\nChoice&Go";

            $sent = false;

            // Si PHPMailer est disponible, utilisez SMTP (recommandé)
            if (class_exists('\PHPMailer\PHPMailer\PHPMailer')) {
                try {
                    $mail = new PHPMailer(true);
                    // $mail->SMTPDebug = SMTP::DEBUG_SERVER; // décommenter pour debug
                    $mail->isSMTP();
                    $mail->Host = $smtpHost;
                    $mail->SMTPAuth = true;
                    $mail->Username = $smtpUser;
                    $mail->Password = $smtpPass;
                    $mail->SMTPSecure = $smtpSecure;
                    $mail->Port = $smtpPort;

                    $mail->setFrom($fromEmail, $fromName);
                    $mail->addAddress($user['email']);
                    $mail->Subject = $subject;
                    $mail->Body = $bodyText;
                    $mail->AltBody = $bodyText;

                    $mail->send();
                    $sent = true;
                } catch (Exception $e) {
                    error_log('PHPMailer error: ' . $mail->ErrorInfo);
                    $sent = false;
                }
            } else {
                // Fallback : essayer la fonction mail() native
                $headers = 'From: ' . $fromEmail . "\r\n" .
                    'Reply-To: ' . $fromEmail . "\r\n" .
                    'X-Mailer: PHP/' . phpversion();
                $sent = @mail($user['email'], $subject, $bodyText, $headers);
            }

            if ($sent) {
                $flash = $genericMsg;
            } else {
                // Affiche le lien pour tester en local si l'envoi a échoué
                $flash = '<p class="flash success">E‑mail non envoyé (serveur mail non configuré) — utilisez ce lien pour tester :</p>';
                $flash .= '<p class="help"><a href="' . htmlspecialchars($resetUrl, ENT_QUOTES) . '">' . htmlspecialchars($resetUrl, ENT_QUOTES) . '</a></p>';
            }
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
<?php include __DIR__ . "/includes/footer.php"; ?>