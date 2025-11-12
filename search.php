<?php
$page_title = "Résultats — Choice&Go";
include __DIR__ . "/includes/header.php";
$from = htmlspecialchars($_GET['from'] ?? '');
$to = htmlspecialchars($_GET['to'] ?? '');
$pax = (int)($_GET['pax'] ?? 1);
?>
<section class="container results">
  <h1>Résultats de recherche</h1>
  <p>Trajets pour <strong><?php echo $from; ?></strong> → <strong><?php echo $to; ?></strong> pour <?php echo $pax; ?> passager(s).</p>
  <ul class="rides">
    <li class="ride">
      <div class="line"><strong>08:15</strong> — <?php echo $from; ?> → <?php echo $to; ?> — 5€</div>
      <button class="btn-outline">Réserver (démo)</button>
    </li>
    <li class="ride">
      <div class="line"><strong>12:30</strong> — <?php echo $from; ?> → <?php echo $to; ?> — 7€</div>
      <button class="btn-outline">Réserver (démo)</button>
    </li>
  </ul>
</section>
<?php include __DIR__ . "/includes/footer.php"; ?>
