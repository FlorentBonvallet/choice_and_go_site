<?php
$page_title = "Choice&Go — Voyagez malin entre étudiants";
include __DIR__ . "/includes/db.php";
include __DIR__ . "/includes/header.php";

// Récupérer les lieux distincts en base
$departures = $pdo->query("SELECT DISTINCT lieu_depart FROM trajets ORDER BY lieu_depart ASC")->fetchAll(PDO::FETCH_COLUMN);
$arrivals = $pdo->query("SELECT DISTINCT lieu_arrivee FROM trajets ORDER BY lieu_arrivee ASC")->fetchAll(PDO::FETCH_COLUMN);
?>

<section class="hero">
  <div class="container hero-grid">
    <img class="hero-img left" src="assets/img/etudiantEnCour.jpeg" alt="Étudiants avec sacs à dos" />
    <div class="hero-center">
      <h1>Voyagez malin entre étudiants</h1>

      <form class="search-trip" action="search.php" method="get">
        <h2>Recherche de trajet</h2>
        <div class="row">
          <div class="location-input-wrapper">
            <input list="departures" id="from" name="from" placeholder="Départ..." required class="location-input" />
            <datalist id="departures">
              <?php foreach ($departures as $d): ?>
                <option value="<?= htmlspecialchars($d) ?>"></option>
              <?php endforeach; ?>
            </datalist>
          </div>

          <button type="button" class="swap" aria-label="Intervertir départ et arrivée" title="Intervertir départ et arrivée">⇄</button>

          <div class="location-input-wrapper">
            <input list="arrivals" id="to" name="to" placeholder="Arrivée..." required class="location-input" />
            <datalist id="arrivals">
              <?php foreach ($arrivals as $a): ?>
                <option value="<?= htmlspecialchars($a) ?>"></option>
              <?php endforeach; ?>
            </datalist>
          </div>
        </div>

        <div class="row passengers">
          <label>Nombre(s) de passager(s)</label>
          <div class="counter">
            <button type="button" class="minus" aria-label="Moins">-</button>
            <input type="number" min="1" value="1" name="pax" />
            <button type="button" class="plus" aria-label="Plus">+</button>
          </div>
        </div>

        <button class="btn-primary" type="submit">Rechercher</button>
      </form>
    </div>
    <img class="hero-img right" src="assets/img/etudiantEnSoiree.jpg" alt="Soirée étudiante" />
  </div>
</section>

<section class="advantages container">
  <h3>Vous êtes étudiants, nos priorité :</h3>
  <div class="cards">
    <article class="card">
      <h4>Des Trajets à bas prix</h4>
      <p>Partagez les frais avec d'autres étudiants et réduisez vos coûts de transport vers vos lieux d'étude.</p>
    </article>
    <article class="card">
      <h4>Un large nombre d'horaire</h4>
      <p>Départs tôt le matin ou tard le soir — trouvez un covoiturage qui colle à votre emploi du temps.</p>
    </article>
    <article class="card">
      <h4>Une fiabilité sans faille</h4>
      <p>La sécurité de nos passagers et conducteurs est essentielle. Notez, commentez et choisissez en confiance.</p>
    </article>
  </div>
</section>

<style>
.location-input-wrapper {
    position: relative;
    flex: 1;
}

.location-input {
    width: 100%;
    padding: 12px 14px;
    border: 3px solid var(--brand);
    border-radius: 999px;
    font-size: 15px;
    outline: 0;
}

.swap {
    font-size: 22px;
    background: #fff;
    border: 3px solid var(--brand);
    border-radius: 999px;
    padding: 0 10px;
    cursor: pointer;
    transition: all 0.2s;
}

.swap:hover {
    background: var(--accent);
    border-color: var(--brand-dark);
}

.counter input {
    width: 70px;
    text-align: center;
    padding: 8px;
    border: 3px solid var(--brand);
    border-radius: 12px;
}

.counter button {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    border: 0;
    background: #e6f0ff;
}

.btn-primary {
    background: var(--brand);
    border: 0;
    color: #fff;
    border-radius: 12px;
    padding: 12px 20px;
    font-weight: 600;
    cursor: pointer;
    box-shadow: 0 6px 0 var(--brand-dark);
}

.btn-primary:hover {
    transform: translateY(-1px);
}
</style>

<script>
const fromInput = document.getElementById('from');
const toInput = document.getElementById('to');

// Swap des champs départ/arrivée
document.querySelector('.swap').addEventListener('click', e => {
    e.preventDefault();
    const temp = fromInput.value;
    fromInput.value = toInput.value;
    toInput.value = temp;
});
</script>

<?php include __DIR__ . "/includes/footer.php"; ?>
