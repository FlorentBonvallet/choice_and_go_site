<?php
$page_title = "Choice&Go — Voyagez malin entre étudiants";
include __DIR__ . "/includes/header.php";
?>
<section class="hero">
  <div class="container hero-grid">
    <img class="hero-img left" src="assets/img/home-left.jpg" alt="Étudiants avec sacs à dos" />
    <div class="hero-center">
      <h1>Voyagez malin entre étudiants</h1>

      <form class="search-trip" action="search.php" method="get">
        <h2>Recherche de trajet</h2>
        <div class="row">
          <input type="text" name="from" placeholder="Départ..." required />
          <span class="swap">⇄</span>
          <input type="text" name="to" placeholder="Arrivée..." required />
        </div>
        <div class="row passengers">
          <label>Nombre(s) de passager(s)</label>
          <div class="counter">
            <button type="button" class="minus" aria-label="Moins">−</button>
            <input type="number" min="1" value="1" name="pax" />
            <button type="button" class="plus" aria-label="Plus">▶</button>
          </div>
        </div>
        <button class="btn-primary" type="submit">Rechercher</button>
      </form>
    </div>
    <img class="hero-img right" src="assets/img/home-right.jpg" alt="Soirée étudiante" />
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
      <h4>Un large nombre d’horaire</h4>
      <p>Départs tôt le matin ou tard le soir — trouvez un covoiturage qui colle à votre emploi du temps.</p>
    </article>
    <article class="card">
      <h4>Une fiabilité sans faille</h4>
      <p>La sécurité de nos passagers et conducteurs est essentielle. Notez, commentez et choisissez en confiance.</p>
    </article>
  </div>
</section>
<?php include __DIR__ . "/includes/footer.php"; ?>
