<?php
$page_title = "Création de trajet — Choice&Go";
include __DIR__ . "/includes/header.php";
?>
<section class="container make-ride">
  <h1>CRÉATION DE TRAJET</h1>

  <form class="ride-form" method="post" action="/create_ride.php">
    <div class="row">
      <input type="date" name="date" placeholder="Date" required />
    </div>

    <div class="row two">
      <input type="text" name="from" placeholder="Départ..." required />
      <span class="swap">⇄</span>
      <input type="text" name="to" placeholder="Arrivée..." required />
    </div>

    <div class="row two">
      <input type="time" name="time_from" placeholder="Heure de départ" required />
      <input type="time" name="time_to" placeholder="Heure d'arrivée" required />
    </div>

    <div class="row passengers">
      <label>Nombre(s) de passager(s)</label>
      <div class="counter">
        <button type="button" class="minus">−</button>
        <input type="number" min="1" value="1" name="pax" />
        <button type="button" class="plus">▶</button>
      </div>
    </div>

    <button class="btn-primary" type="submit" name="submit">Valider</button>

    <?php if (!empty($_POST)): ?>
      <p class="flash success">Trajet créé (démo). Vous avez proposé : <?php echo htmlspecialchars($_POST['from'] . ' → ' . $_POST['to']); ?> le <?php echo htmlspecialchars($_POST['date']); ?>.</p>
    <?php endif; ?>
  </form>
</section>
<?php include __DIR__ . "/includes/footer.php"; ?>
