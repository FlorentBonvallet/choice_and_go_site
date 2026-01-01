<?php
session_start();
$page_title = "Détails du trajet — Choice&Go";
require_once __DIR__ . "/includes/db.php";

// Utilisateur connecté requis
if (empty($_SESSION['user_id'])) {
    $baseDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    $location = ($baseDir === '' || $baseDir === '.') ? '/login.php' : $baseDir . '/login.php';
    header('Location: ' . $location);
    exit;
}

$userId = (int) $_SESSION['user_id'];
$trajetId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($trajetId <= 0) {
    // Redirection simple si id manquant
    $baseDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    $location = ($baseDir === '' || $baseDir === '.') ? '/reservations.php' : $baseDir . '/reservations.php';
    header('Location: ' . $location);
    exit;
}

include __DIR__ . "/includes/header.php";

// Récupère le trajet et vérifie que l'utilisateur est le conducteur
$trajetStmt = $pdo->prepare('SELECT trajet_id, conducteur_id, lieu_depart, lieu_arrivee, date_heure_depart FROM trajets WHERE trajet_id = :id LIMIT 1');
$trajetStmt->execute([':id' => $trajetId]);
$trajet = $trajetStmt->fetch();

if (!$trajet) {
    echo '<section class="container"><p class="flash error">Trajet introuvable.</p></section>';
    include __DIR__ . "/includes/footer.php";
    exit;
}

if ((int)$trajet['conducteur_id'] !== $userId) {
    echo '<section class="container"><p class="flash error">Accès refusé — vous n\'êtes pas le conducteur de ce trajet.</p></section>';
    include __DIR__ . "/includes/footer.php";
    exit;
}

// Récupère les réservations liées au trajet avec infos passagers
$resStmt = $pdo->prepare('
    SELECT 
        r.reservation_id,
        r.nombre_passager,
        r.date_reservation,
        u.utilisateur_id,
        u.prenom,
        u.nom,
        u.email,
        u.telephone
    FROM reservations r
    JOIN utilisateurs u ON r.passager_id = u.utilisateur_id
    WHERE r.trajet_id = :trajet_id
    ORDER BY r.date_reservation ASC
');
$resStmt->execute([':trajet_id' => $trajetId]);
$reservations = $resStmt->fetchAll();

// Calcul du total de places réservées
$totalReserved = 0;
foreach ($reservations as $r) {
    $totalReserved += (int)$r['nombre_passager'];
}

?>
<section class="container">
  <h1>Détails du trajet</h1>

  <div class="trip-summary">
    <div><strong>Trajet :</strong> <?php echo htmlspecialchars($trajet['lieu_depart']); ?> → <?php echo htmlspecialchars($trajet['lieu_arrivee']); ?></div>
    <div><strong>Date / Heure :</strong> <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($trajet['date_heure_depart']))); ?></div>
    <div style="margin-top:.5rem;"><a href="reservations.php" class="btn-outline">← Retour aux réservations</a></div>
  </div>

  <h2 style="margin-top:1.25rem;">Passagers ayant réservé (<?php echo count($reservations); ?>)</h2>

  <?php if (count($reservations) === 0): ?>
    <div class="empty-state">
      <p>Aucune réservation pour ce trajet.</p>
    </div>
  <?php else: ?>
    <div class="reservations-list">
        <div class="table-wrapper">
            <table class="table">
                <thead>
                  <tr>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Places réservées</th>
                    <th>Date de réservation</th>
                    <th>Contact</th>
                  </tr>
                </thead>
                <tbody>
                <?php foreach ($reservations as $r): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($r['nom']); ?></td>
                    <td><?php echo htmlspecialchars($r['prenom']); ?></td>
                    <td><?php echo (int)$r['nombre_passager']; ?></td>
                    <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($r['date_reservation']))); ?></td>
                    <td>
                      <?php
                        $contacts = [];
                        if (!empty($r['telephone'])) $contacts[] = '📞 ' . htmlspecialchars($r['telephone']);
                        if (!empty($r['email'])) $contacts[] = '✉️ ' . htmlspecialchars($r['email']);
                        echo implode(' — ', $contacts) ?: '-';
                      ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

      <div class="summary" style="margin-top:1rem;">
        <strong>Total de places réservées :</strong> <?php echo $totalReserved; ?>
      </div>
    </div>
        </div>
  <?php endif; ?>
</section>

<?php include __DIR__ . "/includes/footer.php"; ?>