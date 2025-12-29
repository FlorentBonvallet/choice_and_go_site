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
        <p>06 99 99 XX XX</p>
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

<style>
.contact-hero {
  background: linear-gradient(135deg, var(--brand) 0%, var(--brand-dark) 100%);
  color: white;
  padding: 60px 20px;
  text-align: center;
  margin-bottom: 50px;
}

.contact-hero h1 {
  font-size: 2.5rem;
  margin-bottom: 10px;
}

.contact-hero .subtitle {
  font-size: 1.2rem;
  opacity: 0.95;
  margin: 0;
}

.contact-content {
  max-width: 1200px;
  margin: 0 auto 60px;
  padding: 20px;
}

.contact-grid {
  display: grid;
  grid-template-columns: 1.5fr 1fr;
  gap: 40px;
  align-items: start;
}

.contact-form-wrapper h2,
.contact-info h2 {
  color: var(--brand);
  font-size: 1.8rem;
  margin-bottom: 20px;
  border-bottom: 3px solid var(--accent);
  padding-bottom: 10px;
}

/* Formulaire */
.contact-form {
  background: white;
  padding: 30px;
  border-radius: 16px;
  border: 2px solid #dbeafe;
  box-shadow: 0 8px 24px rgba(0,0,0,.08);
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  font-weight: 600;
  margin-bottom: 8px;
  color: var(--ink);
}

.form-group input,
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 12px 16px;
  border: 3px solid var(--brand);
  border-radius: 12px;
  font-size: 1rem;
  font-family: inherit;
  outline: none;
  transition: border-color 0.3s, box-shadow 0.3s;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  border-color: var(--brand-dark);
  box-shadow: 0 0 0 3px rgba(43, 139, 220, 0.1);
}

.form-group textarea {
  resize: vertical;
  min-height: 120px;
}

.btn-large {
  width: 100%;
  padding: 16px 24px;
  font-size: 1.1rem;
  margin-top: 10px;
}

/* Alertes */
.alert {
  padding: 16px 20px;
  border-radius: 12px;
  margin-bottom: 20px;
}

.alert-success {
  background: #e7fbe7;
  border: 2px solid #66d266;
  color: #2d5a2d;
}

.alert-error {
  background: #ffe7e7;
  border: 2px solid #ff6b6b;
  color: #8b2c2c;
}

.alert strong {
  display: block;
  margin-bottom: 5px;
  font-size: 1.1rem;
}

.alert p {
  margin: 0;
}

/* Informations de contact */
.contact-info {
  position: sticky;
  top: 100px;
}

.info-card {
  background: white;
  padding: 25px;
  border-radius: 16px;
  border: 2px solid #dbeafe;
  box-shadow: 0 8px 24px rgba(0,0,0,.08);
  margin-bottom: 20px;
  transition: transform 0.3s, box-shadow 0.3s;
}

.info-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 12px 32px rgba(43,139,220,.15);
}

.info-icon {
  font-size: 2.5rem;
  margin-bottom: 10px;
}

.info-card h3 {
  color: var(--brand);
  margin: 10px 0;
  font-size: 1.3rem;
}

.info-card p {
  margin: 5px 0;
  font-size: 1.05rem;
}

.info-card a {
  color: var(--brand);
  text-decoration: none;
  font-weight: 600;
}

.info-card a:hover {
  text-decoration: underline;
}

.info-detail {
  display: block;
  color: var(--muted);
  font-size: 0.9rem;
  margin-top: 5px;
}

.social-links {
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-top: 10px;
}

.social-btn {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 16px;
  background: #f8f9fa;
  border: 2px solid #dbeafe;
  border-radius: 10px;
  text-decoration: none;
  color: var(--ink);
  font-weight: 600;
  transition: all 0.3s;
}

.social-btn:hover {
  background: var(--accent);
  border-color: var(--brand);
  transform: translateX(5px);
}

.social-btn img {
  width: 20px;
  height: 20px;
}

/* Responsive */
@media (max-width: 980px) {
  .contact-grid {
    grid-template-columns: 1fr;
    gap: 30px;
  }

  .contact-info {
    position: static;
  }

  .contact-hero h1 {
    font-size: 2rem;
  }

  .contact-hero .subtitle {
    font-size: 1rem;
  }

  .contact-form {
    padding: 20px;
  }
}

@media (max-width: 580px) {
  .contact-hero {
    padding: 40px 20px;
  }

  .contact-form-wrapper h2,
  .contact-info h2 {
    font-size: 1.5rem;
  }
}
</style>

<?php include __DIR__ . "/includes/footer.php"; ?>
