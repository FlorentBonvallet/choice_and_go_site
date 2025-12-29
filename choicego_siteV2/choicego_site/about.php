<?php
$page_title = "À propos — Choice&Go";
include __DIR__ . "/includes/db.php";
include __DIR__ . "/includes/header.php";
?>

<section class="about-hero">
  <div class="container">
    <h1>À propos de Choice&Go</h1>
    <p class="subtitle">La plateforme de covoiturage pensée pour les étudiants</p>
  </div>
</section>

<section class="about-content container">
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

<style>
.about-hero {
  background: linear-gradient(135deg, var(--brand) 0%, var(--brand-dark) 100%);
  color: white;
  padding: 60px 20px;
  text-align: center;
  margin-bottom: 40px;
}

.about-hero h1 {
  font-size: 2.5rem;
  margin-bottom: 10px;
}

.about-hero .subtitle {
  font-size: 1.2rem;
  opacity: 0.95;
}

.about-content {
  max-width: 1000px;
  margin: 0 auto;
  padding: 20px;
}

.about-section {
  margin-bottom: 60px;
}

.about-section h2 {
  color: var(--brand);
  font-size: 2rem;
  margin-bottom: 20px;
  border-bottom: 3px solid var(--accent);
  padding-bottom: 10px;
}

.about-section p {
  line-height: 1.8;
  font-size: 1.1rem;
  color: #333;
}

.features-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 30px;
  margin-top: 30px;
}

.feature-item {
  text-align: center;
  padding: 20px;
  border-radius: 12px;
  background: #f8f9fa;
  transition: transform 0.3s, box-shadow 0.3s;
}

.feature-item:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
}

.feature-icon {
  font-size: 3rem;
  margin-bottom: 15px;
}

.feature-item h3 {
  color: var(--brand);
  margin-bottom: 10px;
}

.feature-item p {
  font-size: 0.95rem;
  line-height: 1.6;
}

.steps {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 25px;
  margin-top: 30px;
}

.step {
  text-align: center;
  padding: 25px 15px;
}

.step-number {
  width: 60px;
  height: 60px;
  background: var(--brand);
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.8rem;
  font-weight: bold;
  margin: 0 auto 15px;
}

.step h3 {
  color: var(--brand-dark);
  margin-bottom: 10px;
}

.step p {
  font-size: 0.95rem;
  color: #666;
}

.values-list {
  list-style: none;
  padding: 0;
  margin-top: 20px;
}

.values-list li {
  padding: 15px 20px;
  margin-bottom: 15px;
  background: #f8f9fa;
  border-left: 4px solid var(--brand);
  border-radius: 8px;
  font-size: 1.05rem;
  line-height: 1.6;
}

.values-list strong {
  color: var(--brand);
}

.cta-section {
  text-align: center;
  background: linear-gradient(135deg, #e6f0ff 0%, #fff 100%);
  padding: 50px 30px;
  border-radius: 16px;
  margin-top: 60px;
}

.cta-section h2 {
  border: none;
  margin-bottom: 15px;
}

.cta-buttons {
  display: flex;
  gap: 20px;
  justify-content: center;
  flex-wrap: wrap;
  margin-top: 30px;
}

.btn-primary, .btn-secondary {
  padding: 14px 30px;
  border-radius: 12px;
  text-decoration: none;
  font-weight: 600;
  font-size: 1rem;
  transition: all 0.3s;
  display: inline-block;
}

.btn-primary {
  background: var(--brand);
  color: white;
  box-shadow: 0 6px 0 var(--brand-dark);
}

.btn-primary:hover {
  transform: translateY(-2px);
}

.btn-secondary {
  background: white;
  color: var(--brand);
  border: 3px solid var(--brand);
}

.btn-secondary:hover {
  background: var(--accent);
}

@media (max-width: 768px) {
  .about-hero h1 {
    font-size: 2rem;
  }

  .about-hero .subtitle {
    font-size: 1rem;
  }

  .features-grid,
  .steps {
    grid-template-columns: 1fr;
  }

  .cta-buttons {
    flex-direction: column;
    align-items: center;
  }

  .btn-primary, .btn-secondary {
    width: 100%;
    max-width: 300px;
  }
}
</style>

<?php include __DIR__ . "/includes/footer.php"; ?>