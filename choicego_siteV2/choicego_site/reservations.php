<?php
session_start();

$page_title = "Mes réservations — Choice&Go";
include __DIR__ . "/includes/db.php";
include __DIR__ . "/includes/header.php";

// Require logged-in user
if (empty($_SESSION['user_id'])) {
    $baseDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    $location = ($baseDir === '' || $baseDir === '.') ? '/login.php' : $baseDir . '/login.php';
    header('Location: ' . $location);
    exit;
}

$userId = (int) $_SESSION['user_id'];

// Get user info for display
$userStmt = $pdo->prepare('SELECT prenom, nom FROM utilisateurs WHERE utilisateur_id = :id');
$userStmt->execute([':id' => $userId]);
$user = $userStmt->fetch();

// Fetch user's reservations (as passenger) — includes lat/lon
$reservationsStmt = $pdo->prepare('
    SELECT 
        r.reservation_id,
        r.trajet_id,
        r.nombre_passager,
        r.date_reservation,
        t.date_heure_depart,
        t.lieu_depart,
        t.lieu_arrivee,
        t.prix_par_place,
        t.depart_latitude AS depart_lat,
        t.depart_longitude AS depart_lon,
        t.arrivee_latitude AS arrivee_lat,
        t.arrivee_longitude AS arrivee_lon,
        u.prenom AS conducteur_prenom,
        u.nom AS conducteur_nom,
        u.telephone AS conducteur_telephone
    FROM reservations r
    JOIN trajets t ON r.trajet_id = t.trajet_id
    JOIN utilisateurs u ON t.conducteur_id = u.utilisateur_id
    WHERE r.passager_id = :user_id
    ORDER BY t.date_heure_depart DESC
');
$reservationsStmt->execute([':user_id' => $userId]);
$reservations = $reservationsStmt->fetchAll();

// Fetch user's rides as driver (trajets créés) — includes lat/lon
$ridesStmt = $pdo->prepare('
    SELECT 
        t.trajet_id,
        t.date_heure_depart,
        t.lieu_depart,
        t.lieu_arrivee,
        t.places_disponibles,
        t.prix_par_place,
        t.statut_trajet,
        t.depart_latitude AS depart_lat,
        t.depart_longitude AS depart_lon,
        t.arrivee_latitude AS arrivee_lat,
        t.arrivee_longitude AS arrivee_lon,
        COUNT(r.reservation_id) AS nombre_reservations,
        SUM(r.nombre_passager) AS places_reservees
    FROM trajets t
    LEFT JOIN reservations r ON t.trajet_id = r.trajet_id
    WHERE t.conducteur_id = :user_id
    GROUP BY t.trajet_id
    ORDER BY t.date_heure_depart DESC
');
$ridesStmt->execute([':user_id' => $userId]);
$rides = $ridesStmt->fetchAll();

// Handle cancellation of reservations
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_reservation'])) {
    $reservationId = (int) ($_POST['reservation_id'] ?? 0);

    // Verify the reservation belongs to the current user
    $checkStmt = $pdo->prepare('SELECT utilisateur_id FROM reservations WHERE reservation_id = :id');
    $checkStmt->execute([':id' => $reservationId]);
    $res = $checkStmt->fetch();

    if ($res && $res['utilisateur_id'] == $userId) {
        try {
            // Delete reservation
            $deleteStmt = $pdo->prepare('DELETE FROM reservations WHERE reservation_id = :id');
            $deleteStmt->execute([':id' => $reservationId]);

            // Restore available seats
            $restoreSeats = $pdo->prepare('UPDATE trajets SET places_disponibles = places_disponibles + :places WHERE trajet_id = :trajet_id');
            $restoreSeats->execute([
                ':places' => $res['nombre_places'],
                ':trajet_id' => $res['trajet_id']
            ]);

            $message = '<p class="flash success">Réservation annulée avec succès.</p>';
        } catch (Exception $e) {
            $message = '<p class="flash error">Erreur lors de l\'annulation : ' . htmlspecialchars($e->getMessage()) . '</p>';
        }

        // Refresh reservations
        $reservationsStmt->execute([':user_id' => $userId]);
        $reservations = $reservationsStmt->fetchAll();
    } else {
        $message = '<p class="flash error">Erreur : réservation non trouvée ou accès non autorisé.</p>';
    }
}
?>

<!-- Leaflet CSS (chargé ici) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />

<section class="container reservations-container">
    <h1>MES RÉSERVATIONS</h1>
    
    <?php if (!empty($message))
        echo $message; ?>

    <!-- Passenger Reservations Section -->
    <h2 class="section-title">📌 Mes trajets en tant que passager</h2>
    
    <?php if (count($reservations) === 0): ?>
        <div class="empty-state">
            <p>Vous n'avez pas encore de réservations.</p>
            <p><a href="search.php">Rechercher un trajet</a></p>
        </div>
    <?php else: ?>
        <?php foreach ($reservations as $res):
            $departTime = date('H:i', strtotime($res['date_heure_depart']));
            $departDate = date('d/m/Y', strtotime($res['date_heure_depart']));
            $totalPrice = (float) $res['prix_par_place'] * (int) $res['nombre_passager'];
            $isPassed = strtotime($res['date_heure_depart']) < time();
            ?>
            <div class="reservation-card">
                <div class="card-header">
                    <div class="card-title">
                        <?php echo htmlspecialchars($res['lieu_depart']); ?> → <?php echo htmlspecialchars($res['lieu_arrivee']); ?>
                    </div>
                </div>

                <div class="route-info">
                    📍 <strong><?php echo htmlspecialchars($res['lieu_depart']); ?></strong>
                    <span class="icon">→</span>
                    <strong><?php echo htmlspecialchars($res['lieu_arrivee']); ?></strong>
                </div>

                <div class="card-details">
                    <div class="detail-item">
                        <span class="detail-label">Date</span>
                        <span class="detail-value"><?php echo $departDate; ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Heure</span>
                        <span class="detail-value"><?php echo $departTime; ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Nombre de places</span>
                        <span class="detail-value"><span class="passengers-count"><?php echo (int) $res['nombre_passager']; ?> place(s)</span></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Prix par place</span>
                        <span class="detail-value"><?php echo number_format((float) $res['prix_par_place'], 2, ',', ' '); ?>€</span>
                    </div>
                </div>

                <div class="driver-info">
                    <div class="driver-info-title">🚗 Informations du conducteur</div>
                    <div class="driver-name">
                        <?php echo htmlspecialchars($res['conducteur_prenom'] . ' ' . $res['conducteur_nom']); ?>
                    </div>
                    <?php if ($res['conducteur_telephone']): ?>
                        <div class="driver-contact">
                            📞 <?php echo htmlspecialchars($res['conducteur_telephone']); ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="total-cost">
                    Coût total : <?php echo number_format($totalPrice, 2, ',', ' '); ?>€
                </div>

                <div class="card-actions">
                    <?php if (!$isPassed): ?>
                        <form method="post" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir annuler cette réservation ?');">
                            <input type="hidden" name="cancel_reservation" value="1">
                            <input type="hidden" name="reservation_id" value="<?php echo (int) $res['reservation_id']; ?>">
                            <button type="submit" class="btn-cancel">Annuler la réservation</button>
                        </form>
                    <?php endif; ?>

                    <!-- Voir la carte -->
                    <?php
                    $dlat = $res['depart_lat'] ?? '';
                    $dlon = $res['depart_lon'] ?? '';
                    $alat = $res['arrivee_lat'] ?? '';
                    $alon = $res['arrivee_lon'] ?? '';
                    ?>
                    <button type="button" class="btn-outline view-map-btn"
                        data-depart-lat="<?php echo htmlspecialchars($dlat); ?>"
                        data-depart-lon="<?php echo htmlspecialchars($dlon); ?>"
                        data-arrivee-lat="<?php echo htmlspecialchars($alat); ?>"
                        data-arrivee-lon="<?php echo htmlspecialchars($alon); ?>"
                        data-title="<?php echo htmlspecialchars($res['lieu_depart'] . ' → ' . $res['lieu_arrivee']); ?>">
                        Voir la carte
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Driver Rides Section -->
    <h2 class="section-title">🚗 Mes trajets en tant que conducteur</h2>
    
    <?php if (count($rides) === 0): ?>
        <div class="empty-state">
            <p>Vous n'avez pas encore créé de trajets.</p>
            <p><a href="create_ride.php">Créer un trajet</a></p>
        </div>
    <?php else: ?>
        <?php foreach ($rides as $ride):
            $departTime = date('H:i', strtotime($ride['date_heure_depart']));
            $departDate = date('d/m/Y', strtotime($ride['date_heure_depart']));
            $placesReserved = (int) ($ride['places_reservees'] ?? 0);
            $placesAvailable = (int) $ride['places_disponibles'];
            $isPassed = strtotime($ride['date_heure_depart']) < time();
            ?>
            <div class="ride-card">
                <div class="card-header">
                    <div class="card-title">
                        <?php echo htmlspecialchars($ride['lieu_depart']); ?> → <?php echo htmlspecialchars($ride['lieu_arrivee']); ?>
                    </div>
                    <span class="status-badge status-<?php echo $ride['statut_trajet']; ?>">
                        <?php echo ucfirst($ride['statut_trajet']); ?>
                    </span>
                </div>

                <div class="route-info">
                    📍 <strong><?php echo htmlspecialchars($ride['lieu_depart']); ?></strong>
                    <span class="icon">→</span>
                    <strong><?php echo htmlspecialchars($ride['lieu_arrivee']); ?></strong>
                </div>

                <div class="card-details">
                    <div class="detail-item">
                        <span class="detail-label">Date</span>
                        <span class="detail-value"><?php echo $departDate; ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Heure</span>
                        <span class="detail-value"><?php echo $departTime; ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Places disponibles</span>
                        <span class="detail-value"><?php echo $placesAvailable; ?> place(s)</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Réservations</span>
                        <span class="detail-value"><span class="passengers-count"><?php echo $placesReserved; ?> place(s) réservée(s)</span></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Prix par place</span>
                        <span class="detail-value"><?php echo number_format((float) $ride['prix_par_place'], 2, ',', ' '); ?>€</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Taux d'occupation</span>
                        <span class="detail-value">
                            <?php
                            $totalPlaces = $placesAvailable + $placesReserved;
                            $occupancyRate = $totalPlaces > 0 ? round(($placesReserved / $totalPlaces) * 100) : 0;
                            echo $occupancyRate . '%';
                            ?>
                        </span>
                    </div>
                </div>

                <div class="card-actions">
                    <a href="ride_details.php?id=<?php echo (int) $ride['trajet_id']; ?>" class="btn-outline">Voir les détails</a>

                    <!-- Voir la carte pour le conducteur -->
                    <?php
                    $dlat = $ride['depart_lat'] ?? '';
                    $dlon = $ride['depart_lon'] ?? '';
                    $alat = $ride['arrivee_lat'] ?? '';
                    $alon = $ride['arrivee_lon'] ?? '';
                    ?>
                    <button type="button" class="btn-outline view-map-btn"
                        data-depart-lat="<?php echo htmlspecialchars($dlat); ?>"
                        data-depart-lon="<?php echo htmlspecialchars($dlon); ?>"
                        data-arrivee-lat="<?php echo htmlspecialchars($alat); ?>"
                        data-arrivee-lon="<?php echo htmlspecialchars($alon); ?>"
                        data-title="<?php echo htmlspecialchars($ride['lieu_depart'] . ' → ' . $ride['lieu_arrivee']); ?>">
                        Voir la carte
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</section>

<!-- Map modal -->
<div id="map-modal" class="map-modal" aria-hidden="true">
  <div class="map-modal-backdrop" data-close="true"></div>
  <div class="map-modal-panel" role="dialog" aria-modal="true" aria-labelledby="map-modal-title">
    <div class="map-modal-header">
      <h3 id="map-modal-title">Carte</h3>
      <div>
        <label style="font-weight:600;margin-right:8px;"><input type="checkbox" id="show-my-location"> Ma position</label>
        <button class="btn-outline" id="map-modal-close" aria-label="Fermer">Fermer</button>
      </div>
    </div>
    <div id="map-modal-body" style="height:480px;">
      <div id="map-view" style="width:100%;height:100%;"></div>
    </div>
  </div>
</div>

<!-- Leaflet JS + modal/map logic -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<script>
(function(){
  let map = null;
  let markers = [];
  let myLocationMarker = null;

  function openModal(title, dlat, dlon, alat, alon) {
    document.getElementById('map-modal').setAttribute('aria-hidden', 'false');
    document.getElementById('map-modal-title').textContent = title || 'Carte';

    // init map if needed
    if (!map) {
      map = L.map('map-view', { dragging: true, tap: true }).setView([46.2276, 2.2137], 6);
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap contributors', maxZoom: 19 }).addTo(map);
    }

    // remove existing markers
    markers.forEach(m => map.removeLayer(m));
    markers = [];

    // add depart marker
    if (dlat && dlon) {
      const m1 = L.marker([parseFloat(dlat), parseFloat(dlon)]).addTo(map).bindPopup('Départ').openPopup();
      markers.push(m1);
    }

    // add arrivee marker
    if (alat && alon) {
      const m2 = L.marker([parseFloat(alat), parseFloat(alon)]).addTo(map).bindPopup('Arrivée').openPopup();
      markers.push(m2);
    }

    // fit bounds
    if (markers.length === 1) {
      map.setView(markers[0].getLatLng(), 13);
    } else if (markers.length > 1) {
      const group = L.featureGroup(markers);
      map.fitBounds(group.getBounds().pad(0.15));
    }

    // update my location checkbox state
    document.getElementById('show-my-location').checked = false;
    if (myLocationMarker) {
      map.removeLayer(myLocationMarker);
      myLocationMarker = null;
    }
  }

  function closeModal() {
    document.getElementById('map-modal').setAttribute('aria-hidden', 'true');
  }

  // delegate click on view-map buttons
  document.addEventListener('click', function(e){
    const btn = e.target.closest('.view-map-btn');
    if (!btn) return;
    const dlat = btn.dataset.departLat || '';
    const dlon = btn.dataset.departLon || '';
    const alat = btn.dataset.arriveeLat || '';
    const alon = btn.dataset.arriveeLon || '';
    const title = btn.dataset.title || 'Carte';
    openModal(title, dlat, dlon, alat, alon);
  });

  // close handlers
  document.getElementById('map-modal-close').addEventListener('click', closeModal);
  document.querySelector('.map-modal-backdrop').addEventListener('click', closeModal);

  // show my location toggle
  document.getElementById('show-my-location').addEventListener('change', function(e){
    if (!map) return;
    if (this.checked) {
      if (!navigator.geolocation) {
        alert('Géolocalisation non disponible dans votre navigateur.');
        this.checked = false;
        return;
      }
      navigator.geolocation.getCurrentPosition(function(pos){
        const lat = pos.coords.latitude;
        const lon = pos.coords.longitude;
        if (myLocationMarker) map.removeLayer(myLocationMarker);
        myLocationMarker = L.marker([lat, lon], {title: 'Ma position'}).addTo(map).bindPopup('Ma position').openPopup();
        // include in bounds with other markers
        const all = markers.slice();
        all.push(myLocationMarker);
        if (all.length === 1) map.setView(all[0].getLatLng(), 13);
        else {
          const group = L.featureGroup(all);
          map.fitBounds(group.getBounds().pad(0.15));
        }
      }, function(err){
        alert('Impossible de récupérer la position : ' + (err.message || 'erreur'));
        document.getElementById('show-my-location').checked = false;
      }, { enableHighAccuracy: true, timeout: 10000 });
    } else {
      if (myLocationMarker) {
        map.removeLayer(myLocationMarker);
        myLocationMarker = null;
      }
      // refit to markers only
      if (markers.length === 1) map.setView(markers[0].getLatLng(), 13);
      else if (markers.length > 1) {
        const group = L.featureGroup(markers);
        map.fitBounds(group.getBounds().pad(0.15));
      }
    }
  });
})();
</script>

<?php include __DIR__ . "/includes/footer.php"; ?>