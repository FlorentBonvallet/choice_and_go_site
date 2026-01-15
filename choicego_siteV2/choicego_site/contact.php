<?php
$page_title = "Contact — Choice&Go";
include __DIR__ . "/includes/header.php";

// Traitement du formulaire de contact
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $subject = trim($_POST['subject'] ?? '');
  $message = trim($_POST['message'] ?? '');

  if (empty($name) || empty($email) || empty($subject) || empty($message)) {
    $error = "Tous les champs sont obligatoires.";
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = "Adresse email invalide.";
  } else {
    // Ici, vous pourriez envoyer un email ou enregistrer en base de données
    // Pour l'instant, on simule un succès
    $success = true;

    // Exemple d'envoi d'email (à configurer selon votre serveur)
    // $to = "contact@choicego.com";
    // $headers = "From: " . $email . "\r\n" . "Reply-To: " . $email;
    // mail($to, $subject, $message, $headers);
  }
}
?>

<section class="contact-hero">
  <div class="container">
    <h1>Contactez-nous</h1>
    <p class="subtitle">Une question ? Une suggestion ? Notre équipe est là pour vous écouter !</p>
  </div>
</section>

<section class="contact-content container">
  <div class="contact-grid">

    <!-- Formulaire de contact -->
    <div class="contact-form-wrapper">
      <h2>Envoyez-nous un message</h2>

      <?php if ($success): ?>
        <div class="alert alert-success">
          <strong>✓ Message envoyé !</strong>
          <p>Merci pour votre message. Nous vous répondrons dans les plus brefs délais.</p>
        </div>
      <?php endif; ?>

      <?php if ($error): ?>
        <div class="alert alert-error">
          <strong>✗ Erreur</strong>
          <p><?php echo htmlspecialchars($error); ?></p>
        </div>
      <?php endif; ?>

      <form class="contact-form" method="POST" action="contact.php">
        <div class="form-group">
          <label for="name">Nom complet *</label>
          <input type="text" id="name" name="name" required placeholder="Votre nom"
                 value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
        </div>

        <div class="form-group">
          <label for="email">Email *</label>
          <input type="email" id="email" name="email" required placeholder="votre@email.com"
                 value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
        </div>

        <div class="form-group">
          <label for="subject">Sujet *</label>
          <select id="subject" name="subject" required>
            <option value="">-- Choisissez un sujet --</option>
            <option value="Question générale">Question générale</option>
            <option value="Problème technique">Problème technique</option>
            <option value="Suggestion">Suggestion d'amélioration</option>
            <option value="Signalement">Signalement</option>
            <option value="Partenariat">Partenariat</option>
            <option value="Autre">Autre</option>
          </select>
        </div>

        <div class="form-group">
          <label for="message">Message *</label>
          <textarea id="message" name="message" required rows="6"
                    placeholder="Écrivez votre message ici..."><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
        </div>

        <button type="submit" class="btn-primary btn-large">Envoyer le message</button>
      </form>
    </div>

    <!-- Informations de contact -->
    <div class="contact-info">
      <h2>Nos coordonnées</h2>

      <div class="info-card">
        <div class="info-icon">📧</div>
        <h3>Email</h3>
        <p><a href="mailto:contact@choicego.com">contact@choicego.com</a></p>
        <span class="info-detail">Réponse sous 24-48h</span>
      </div>

      <div class="info-card">
        <div class="info-icon">📱</div>
        <h3>Téléphone</h3>
        <p>06 99 99 45 45</p>
        <span class="info-detail">Lun-Ven : 9h-18h</span>
      </div>

      <div class="info-card">
        <div class="info-icon">💬</div>
        <h3>Réseaux sociaux</h3>
        <div class="social-links">
          <a href="https://www.facebook.com/profile.php?id=61585930944496" target="_blank" rel="noopener noreferrer" class="social-btn">
            <img src="assets/img/Facebook_Logo.png" alt="Facebook" width="20" height="20"> Facebook
          </a>
          <a href="https://www.instagram.com/choiceandgo/" target="_blank" rel="noopener noreferrer" class="social-btn">
            <img src="assets/img/Instagram_icon.png" alt="Instagram" width="20" height="20"> Instagram
          </a>
          <a href="https://x.com/ChoiceGo181414" target="_blank" rel="noopener noreferrer" class="social-btn">
            <img src="assets/img/twitter_logo.png" alt="Twitter" width="20" height="20"> Twitter
          </a>
        </div>
      </div>

      <div class="info-card">
        <div class="info-icon">❓</div>
        <h3>Questions fréquentes</h3>
        <p>Consultez notre <a href="about.php">page À propos</a> ou notre FAQ pour des réponses rapides.</p>
      </div>
    </div>

  </div>
</section>



<?php include __DIR__ . "/includes/footer.php"; ?>
