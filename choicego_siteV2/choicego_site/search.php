<?php
$page_title = "Résultats — Choice&Go";
include __DIR__ . "/includes/header.php";
include __DIR__ . '/includes/db.php';

$from = trim($_GET['from'] ?? '');
$to   = trim($_GET['to'] ?? '');
$pax  = max(1, (int)($_GET['pax'] ?? 1));

$sql = "SELECT trajets.*, trajets.prix_par_place AS prix FROM trajets
        WHERE statut_trajet = 'ouvert'
          AND lieu_depart LIKE :from
          AND lieu_arrivee LIKE :to
          AND places_disponibles >= :pax
        ORDER BY date_heure_depart ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute([
  ':from' => "%{$from}%",
  ':to'   => "%{$to}%",
  ':pax'  => $pax
]);

$results = $stmt->fetchAll();
?>
<section class="container results">
  <h1>Résultats de recherche</h1>
  <p>Trajets pour <strong><?php echo $from; ?></strong> → <strong><?php echo $to; ?></strong> pour <?php echo $pax; ?> passager(s).</p>
  <ul class="rides">
    <?php foreach ($results as $ride): ?>
    <li class="ride">
      <div class="line">
        <strong><?php echo date('H:i', strtotime($ride['date_heure_depart'])); ?></strong> — 
        <?php echo htmlspecialchars($ride['lieu_depart']); ?> → <?php echo htmlspecialchars($ride['lieu_arrivee']); ?> — 
        <?php echo number_format((float)$ride['prix'], 2, ',', ' '); ?>€
      </div>
      <button class="btn-outline">Réserver (démo)</button>
    </li>
    <?php endforeach; ?>
  </ul>
</section>
<?php include __DIR__ . "/includes/footer.php"; ?>
