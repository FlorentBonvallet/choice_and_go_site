<?php
session_start();
$page_title = "À propos — Choice&Go";
require_once __DIR__ . "/includes/flash.php";
include __DIR__ . "/includes/header.php";
?>

<section class="about-hero">
  <div class="container">
    <h1>À propos de Choice&Go</h1>
    <p class="subtitle">La plateforme de covoiturage pensée pour les étudiants</p>
  </div>
</section>

<section class="about-content container">
  <?= flash_render() ?>

  <div class="about-section">
    <h2>Notre Mission</h2>
    <p>
      Choice&Go est né d'un constat simple : les étudiants ont besoin de solutions de transport
      abordables et flexibles pour se rendre à leurs cours, événements et sorties. Notre mission
      est de faciliter la mobilité étudiante en connectant conducteurs et passagers au sein d'une
      communauté de confiance.
    </p>
  </div>

  <div class="about-section">
    <h2>Pourquoi Choice&Go ?</h2>
    <div class="features-grid">
      <div class="feature-item">
        <div class="feature-icon">💰</div>
        <h3>Économique</h3>
        <p>Partagez les frais de transport et réduisez votre budget déplacements de manière significative.</p>
      </div>
      <div class="feature-item">
        <div class="feature-icon">🤝</div>
        <h3>Convivial</h3>
        <p>Rencontrez d'autres étudiants, élargissez votre réseau et voyagez dans une ambiance sympathique.</p>
      </div>
      <div class="feature-item">
        <div class="feature-icon">🌱</div>
        <h3>Écologique</h3>
        <p>Réduisez votre empreinte carbone en partageant les trajets plutôt que de voyager seul.</p>
      </div>
      <div class="feature-item">
        <div class="feature-icon">⏰</div>
        <h3>Flexible</h3>
        <p>Des horaires variés pour s'adapter à votre emploi du temps chargé d'étudiant.</p>
      </div>
    </div>
  </div>

  <div class="about-section">
    <h2>Comment ça marche ?</h2>
    <div class="steps">
      <div class="step">
        <div class="step-number">1</div>
        <h3>Inscrivez-vous</h3>
        <p>Créez votre compte gratuitement en quelques clics.</p>
      </div>
      <div class="step">
        <div class="step-number">2</div>
        <h3>Recherchez ou proposez</h3>
        <p>Trouvez un trajet ou publiez le vôtre si vous êtes conducteur.</p>
      </div>
      <div class="step">
        <div class="step-number">3</div>
        <h3>Réservez</h3>
        <p>Confirmez votre place en toute simplicité.</p>
      </div>
      <div class="step">
        <div class="step-number">4</div>
        <h3>Voyagez !</h3>
        <p>Profitez de votre trajet en toute sérénité.</p>
      </div>
    </div>
  </div>

  <div class="about-section">
    <h2>Nos Valeurs</h2>
    <ul class="values-list">
      <li><strong>Sécurité :</strong> Vérification des profils et système de notation pour voyager en confiance.</li>
      <li><strong>Communauté :</strong> Créer du lien entre étudiants et favoriser l'entraide.</li>
      <li><strong>Simplicité :</strong> Une plateforme intuitive et facile à utiliser au quotidien.</li>
      <li><strong>Accessibilité :</strong> Des tarifs adaptés au budget étudiant.</li>
    </ul>
  </div>

  <div class="about-section cta-section">
    <h2>Rejoignez la communauté Choice&Go</h2>
    <p>Des milliers d'étudiants utilisent déjà Choice&Go pour leurs déplacements quotidiens.</p>
    <div class="cta-buttons">
      <a href="register.php" class="btn-primary">Créer un compte</a>
      <a href="search.php" class="btn-secondary">Rechercher un trajet</a>
    </div>
  </div>
</section>


<?php include __DIR__ . "/includes/footer.php"; ?>