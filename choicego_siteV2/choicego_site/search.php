<?php
$page_title = "Résultats — Choice&Go";
include __DIR__ . "/includes/header.php";
include __DIR__ . '/includes/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
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

// Handle reservation submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['make_reservation'])) {
    if (!isset($_SESSION['user_id'])) {
        $message = '<p class="flash error">Vous devez être connecté pour faire une réservation. <a href="login.php">Se connecter</a></p>';
    } else {
        $trajet_id = (int)($_POST['trajet_id'] ?? 0);
        $nombre_places = (int)($_POST['nombre_places'] ?? 1);
        $user_id = (int)$_SESSION['user_id'];
        
        // Verify the ride exists and has enough available seats
        $rideCheck = $pdo->prepare('SELECT trajet_id, places_disponibles, conducteur_id FROM trajets WHERE trajet_id = :id');
        $rideCheck->execute([':id' => $trajet_id]);
        $ride = $rideCheck->fetch();
        
        if (!$ride) {
            $message = '<p class="flash error">Le trajet n\'existe pas.</p>';
        } elseif ($ride['conducteur_id'] == $user_id) {
            $message = '<p class="flash error">Vous ne pouvez pas réserver votre propre trajet.</p>';
        } elseif ($ride['places_disponibles'] < $nombre_places) {
            $message = '<p class="flash error">Nombre de places insuffisant. Places disponibles : ' . $ride['places_disponibles'] . '</p>';
        } else {
            // Check if user already has a reservation for this ride
            $existingCheck = $pdo->prepare('SELECT COUNT(*) FROM reservations WHERE trajet_id = :trajet_id AND passager_id = :user_id');
            $existingCheck->execute([':trajet_id' => $trajet_id, ':user_id' => $user_id]);
            if ($existingCheck->fetchColumn() > 0) {
                $message = '<p class="flash error">Vous avez déjà une réservation pour ce trajet.</p>';
            } else {
                try {
                    // Reserve exactly the number of places requested by the user
                    // Even if the ride has more places available, we only reserve what was needed
                    $placesToReserve = min($nombre_places, $ride['places_disponibles']);
                    
                    // Insert reservation with the exact number of places needed (without statut_reservation)
                    $insertRes = $pdo->prepare('
                        INSERT INTO reservations (trajet_id, passager_id, nombre_passager, date_reservation)
                        VALUES (:trajet_id, :user_id, :nombre_places, NOW())
                    ');
                    $insertRes->execute([
                        ':trajet_id' => $trajet_id,
                        ':user_id' => $user_id,
                        ':nombre_places' => $placesToReserve
                    ]);
                    
                    // Update available seats - deduct only the exact number reserved
                    $updateSeats = $pdo->prepare('UPDATE trajets SET places_disponibles = places_disponibles - :places WHERE trajet_id = :id');
                    $updateSeats->execute([
                        ':places' => $placesToReserve,
                        ':id' => $trajet_id
                    ]);
                    
                    $message = '<p class="flash success">Réservation confirmée pour ' . $placesToReserve . ' place(s) ! <a href="reservations.php">Voir vos réservations</a></p>';
                    
                    // Refresh results
                    $stmt->execute([
                        ':from' => "%{$from}%",
                        ':to'   => "%{$to}%",
                        ':pax'  => $pax
                    ]);
                    $results = $stmt->fetchAll();
                } catch (Exception $e) {
                    $message = '<p class="flash error">Erreur lors de la réservation : ' . htmlspecialchars($e->getMessage()) . '</p>';
                }
            }
        }
    }
}
?>
<section class="container results">
  <h1>Résultats de recherche</h1>
  <p>Trajets pour <strong><?php echo htmlspecialchars($from); ?></strong> → <strong><?php echo htmlspecialchars($to); ?></strong> pour <?php echo $pax; ?> passager(s).</p>
  
  <?php if (!empty($message)) echo $message; ?>
  
  <?php if (count($results) === 0): ?>
    <p style="text-align: center; padding: 2rem; background: #f5f5f5; border-radius: 8px;">
      Aucun trajet disponible pour cette recherche.
    </p>
  <?php else: ?>
  <ul class="rides">
    <?php foreach ($results as $ride): ?>
    <li class="ride">
      <div class="line">
        <strong><?php echo date('H:i', strtotime($ride['date_heure_depart'])); ?></strong> — 
        <?php echo htmlspecialchars($ride['lieu_depart']); ?> → <?php echo htmlspecialchars($ride['lieu_arrivee']); ?> — 
        <?php echo number_format((float)$ride['prix'], 2, ',', ' '); ?>€
      </div>
      <div class="ride-details">
        <span class="available-seats">Places disponibles : <?php echo (int)$ride['places_disponibles']; ?></span>
        <form method="post">
          <input type="hidden" name="trajet_id" value="<?php echo (int)$ride['trajet_id']; ?>">
          <input type="hidden" name="nombre_places" value="<?php echo $pax; ?>">
          <button type="submit" name="make_reservation" class="btn-primary">Réserver</button>
        </form>
      </div>
    </li>
    <?php endforeach; ?>
  </ul>
  <?php endif; ?>
</section>

<style>
  .ride-details {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 0.75rem;
    padding-top: 0.75rem;
    border-top: 1px solid #eee;
  }

  .available-seats {
    font-size: 0.9rem;
    color: #666;
  }

  .btn-primary {
    background: var(--brand);
    border: 0;
    color: #fff;
    border-radius: 6px;
    padding: 0.5rem 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
  }

  .btn-primary:hover {
    background: var(--brand-dark);
    transform: translateY(-1px);
  }
</style>

<?php include __DIR__ . "/includes/footer.php"; ?>
