<?php
session_start();
require_once __DIR__ . "/includes/db.php"; // connexion PDO

$page_title = "Création de trajet — Choice&Go";
include __DIR__ . "/includes/header.php";

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    die("Erreur : utilisateur non connecté.");
}

$conducteur_id = $_SESSION['user_id'];

// Récupère tous les véhicules du conducteur
$stmtVeh = $pdo->prepare("SELECT vehicule_id, marque, modele, couleur, immatriculation FROM vehicules WHERE utilisateur_id = ?");
$stmtVeh->execute([$conducteur_id]);
$vehicules = $stmtVeh->fetchAll(PDO::FETCH_ASSOC);


// Gestion du formulaire POST
$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    try {
        $lieu_depart = $_POST['from'];
        $lieu_arrivee = $_POST['to'];
        $date = $_POST['date'];
        $time_from = $_POST['time_from'];
        $places = intval($_POST['pax']);

        // Récupération des lat/lon
        $depart_lat = $_POST['depart_latitude'] ?? null;
        $depart_lon = $_POST['depart_longitude'] ?? null;
        $arrivee_lat = $_POST['arrivee_latitude'] ?? null;
        $arrivee_lon = $_POST['arrivee_longitude'] ?? null;

        $date_heure_depart = $date . ' ' . $time_from . ':00';

        $sql = "INSERT INTO trajets (
                    conducteur_id,
                    vehicule_id,
                    lieu_depart,
                    lieu_arrivee,
                    date_heure_depart,
                    places_disponibles,
                    prix_par_place,
                    statut_trajet,
                    depart_latitude,
                    depart_longitude,
                    arrivee_latitude,
                    arrivee_longitude
                ) VALUES (
                    :conducteur_id,
                    :vehicule_id,
                    :lieu_depart,
                    :lieu_arrivee,
                    :date_heure_depart,
                    :places_disponibles,
                    :prix_par_place,
                    'ouvert',
                    :depart_latitude,
                    :depart_longitude,
                    :arrivee_latitude,
                    :arrivee_longitude
                )";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':conducteur_id' => $conducteur_id,
            ':vehicule_id' => $vehicule_id,
            ':lieu_depart' => $lieu_depart,
            ':lieu_arrivee' => $lieu_arrivee,
            ':date_heure_depart' => $date_heure_depart,
            ':places_disponibles' => $places,
            ':prix_par_place' => 0.00,
            ':depart_latitude' => $depart_lat,
            ':depart_longitude' => $depart_lon,
            ':arrivee_latitude' => $arrivee_lat,
            ':arrivee_longitude' => $arrivee_lon
        ]);

        $successMessage = "Trajet créé avec succès !";

    } catch (Exception $e) {
        $errorMessage = "Erreur lors de la création du trajet : " . $e->getMessage();
    }
}
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet-control-geocoder/2.4.0/Control.Geocoder.min.css" />
<style>
    /* ton CSS existant */
    #map {
        height: 400px;
        margin: 1rem 0;
        border-radius: 8px;
        border: 1px solid #e0e0e0;
        position: relative;
        z-index: 0;
    }
    #map.selecting-mode { cursor: crosshair; }
    .geocoder-suggestions { position: absolute; top:100%; left:0; right:0; background:white; border:1px solid #ccc; border-top:none; max-height:200px; overflow-y:auto; z-index:10; list-style:none; margin:0; padding:0; display:none; }
    .geocoder-suggestions.active { display:block; }
    .geocoder-suggestions li { padding:0.5rem; cursor:pointer; border-bottom:1px solid #f0f0f0; }
    .geocoder-suggestions li:hover { background-color:#f5f5f5; }
    .location-input-wrapper { position:relative; }
    .map-hint { padding:0.75rem; background-color:#e8f4f8; border-left:4px solid #0066cc; border-radius:4px; margin-bottom:1rem; font-size:0.9rem; color:#333; display:none; }
    .map-hint.active { display:block; }
    .map-buttons { display:flex; gap:0.5rem; margin-bottom:1rem; }
    .map-btn { flex:1; padding:0.75rem; border:2px solid #ddd; background:white; border-radius:4px; cursor:pointer; font-size:0.9rem; transition:all 0.2s; }
    .map-btn:hover { border-color:#0066cc; background-color:#f0f7ff; }
    .map-btn.active { border-color:#0066cc; background-color:#0066cc; color:white; }
</style>

<section class="container make-ride">
  <h1>CRÉATION DE TRAJET</h1>

  <?php if ($successMessage): ?>
    <p class="flash success"><?= htmlspecialchars($successMessage) ?></p>
  <?php elseif ($errorMessage): ?>
    <p class="flash error"><?= htmlspecialchars($errorMessage) ?></p>
  <?php endif; ?>

  <form class="ride-form" method="post" action="create_ride.php">
    <div class="row">
      <input type="date" name="date" placeholder="Date" required />
    </div>

    <div class="row two">
      <div class="location-input-wrapper">
        <input type="text" id="from" name="from" placeholder="Départ..." required />
        <ul id="from-suggestions" class="geocoder-suggestions"></ul>
      </div>
      <button type="button" class="swap" aria-label="Intervertir départ et arrivée" title="Intervertir départ et arrivée">⇄</button>
      <div class="location-input-wrapper">
        <input type="text" id="to" name="to" placeholder="Arrivée..." required />
        <ul id="to-suggestions" class="geocoder-suggestions"></ul>
      </div>
    </div>

    <div class="map-hint" id="map-hint"></div>

    <div class="map-buttons">
      <button type="button" class="map-btn" id="select-from-btn">📍 Cliquer sur la carte pour départ</button>
      <button type="button" class="map-btn" id="select-to-btn">📍 Cliquer sur la carte pour arrivée</button>
      <button type="button" class="map-btn" id="clear-selection-btn">✕ Annuler sélection</button>
    </div>

    <div id="map"></div>

    <!-- hidden fields pour lat/lon -->
    <input type="hidden" name="depart_latitude" id="depart_latitude">
    <input type="hidden" name="depart_longitude" id="depart_longitude">
    <input type="hidden" name="arrivee_latitude" id="arrivee_latitude">
    <input type="hidden" name="arrivee_longitude" id="arrivee_longitude">

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
      <div class="row">
  <label>Véhicule :
    <select name="vehicule_id" required>
      <?php if (count($vehicules) === 0): ?>
        <option value="">Aucun véhicule disponible</option>
      <?php else: ?>
        <?php foreach ($vehicules as $v): ?>
          <option value="<?= htmlspecialchars($v['vehicule_id']) ?>">
            <?= htmlspecialchars("{$v['marque']} {$v['modele']} ({$v['couleur']}, {$v['immatriculation']})") ?>
          </option>
        <?php endforeach; ?>
      <?php endif; ?>
    </select>
  </label>
</div>
    <button class="btn-primary" type="submit" name="submit">Valider</button>
  </form>
</section>

<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet-control-geocoder/2.4.0/Control.Geocoder.min.js"></script>
<script>
// INITIALISATION DE LA CARTE ET DES MARKERS
const map = L.map('map', { dragging:true, tap:true }).setView([46.2276,2.2137],6);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{ attribution:'© OpenStreetMap contributors', maxZoom:19 }).addTo(map);

let fromMarker, toMarker;
let selectingMode = null;

const fromInput = document.getElementById('from');
const toInput = document.getElementById('to');
const mapHint = document.getElementById('map-hint');
const selectFromBtn = document.getElementById('select-from-btn');
const selectToBtn = document.getElementById('select-to-btn');
const clearSelectionBtn = document.getElementById('clear-selection-btn');

function updateHiddenLatLng() {
    if(fromMarker){
        document.getElementById('depart_latitude').value = fromMarker.getLatLng().lat;
        document.getElementById('depart_longitude').value = fromMarker.getLatLng().lng;
    }
    if(toMarker){
        document.getElementById('arrivee_latitude').value = toMarker.getLatLng().lat;
        document.getElementById('arrivee_longitude').value = toMarker.getLatLng().lng;
    }
}

// --- TON JS EXISTANT POUR GÉOCODAGE ET MARKERS ---
async function setupGeocoder(inputId,suggestionsId){
    const input = document.getElementById(inputId);
    const suggestionsList = document.getElementById(suggestionsId);
    const isFrom = inputId==='from';
    input.addEventListener('input',async e=>{
        const query = e.target.value.trim();
        if(query.length<2){ suggestionsList.classList.remove('active'); return; }
        try{
            const response = await fetch(`https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(query)}&format=json&limit=5&countrycodes=fr`);
            const results = await response.json();
            suggestionsList.innerHTML='';
            if(results.length>0){
                results.forEach(result=>{
                    const li=document.createElement('li');
                    li.textContent=result.display_name;
                    li.dataset.lat=result.lat;
                    li.dataset.lon=result.lon;
                    li.addEventListener('click',()=>{
                        input.value=result.display_name;
                        suggestionsList.classList.remove('active');
                        const lat=parseFloat(result.lat), lon=parseFloat(result.lon);
                        if(isFrom){
                            if(fromMarker) map.removeLayer(fromMarker);
                            fromMarker=L.marker([lat,lon],{title:'Départ'}).addTo(map).bindPopup('Départ');
                        } else{
                            if(toMarker) map.removeLayer(toMarker);
                            toMarker=L.marker([lat,lon],{title:'Arrivée'}).addTo(map).bindPopup('Arrivée');
                        }
                        if(fromMarker&&toMarker){ const group=new L.featureGroup([fromMarker,toMarker]); map.fitBounds(group.getBounds().pad(0.1)); }
                        updateHiddenLatLng();
                    });
                    suggestionsList.appendChild(li);
                });
                suggestionsList.classList.add('active');
            }
        }catch(err){ console.error(err); }
    });
    document.addEventListener('click',e=>{
        if(e.target!==input){ suggestionsList.classList.remove('active'); }
    });
}

setupGeocoder('from','from-suggestions');
setupGeocoder('to','to-suggestions');

// CLICK SUR LA CARTE AVEC NOM DE VILLE/VILLAGE
map.on('click', async e => {
    if (!selectingMode) return;

    const lat = e.latlng.lat;
    const lon = e.latlng.lng;

    let addressName = `${lat.toFixed(4)}, ${lon.toFixed(4)}`; // fallback simple

    try {
        const response = await fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lon}&format=json&zoom=10&addressdetails=1`);
        const data = await response.json();

        // Récupère la ville/village le plus proche
        addressName = data.address?.city || data.address?.town || data.address?.village || data.address?.county || data.display_name || addressName;
    } catch (error) {
        console.warn('Erreur reverse geocoding:', error);
    }

    if (selectingMode === 'from') {
        fromInput.value = addressName;
        if (fromMarker) map.removeLayer(fromMarker);
        fromMarker = L.marker([lat, lon], { title: 'Départ' }).addTo(map).bindPopup('Départ').openPopup();
    } else if (selectingMode === 'to') {
        toInput.value = addressName;
        if (toMarker) map.removeLayer(toMarker);
        toMarker = L.marker([lat, lon], { title: 'Arrivée' }).addTo(map).bindPopup('Arrivée').openPopup();
    }

    // Met à jour les champs cachés lat/lon
    updateHiddenLatLng();

    if (fromMarker && toMarker) {
        const group = new L.featureGroup([fromMarker, toMarker]);
        map.fitBounds(group.getBounds().pad(0.1));
    }

    selectingMode = null;
    updateMapUI();
});

selectFromBtn.addEventListener('click',e=>{ e.preventDefault(); selectingMode=selectingMode==='from'?null:'from'; updateMapUI(); });
selectToBtn.addEventListener('click',e=>{ e.preventDefault(); selectingMode=selectingMode==='to'?null:'to'; updateMapUI(); });
clearSelectionBtn.addEventListener('click',e=>{
    e.preventDefault();
    selectingMode=null;
    if(fromMarker) map.removeLayer(fromMarker);
    if(toMarker) map.removeLayer(toMarker);
    fromMarker=null; toMarker=null;
    fromInput.value=''; toInput.value='';
    updateHiddenLatLng();
    updateMapUI();
});

function updateMapUI(){
    const mapElement=document.getElementById('map');
    if(selectingMode==='from'){
        selectFromBtn.classList.add('active'); selectToBtn.classList.remove('active');
        mapElement.classList.add('selecting-mode');
        mapHint.textContent='📍 Cliquez sur la carte pour sélectionner le point de départ';
        mapHint.classList.add('active'); map.dragging.disable();
    } else if(selectingMode==='to'){
        selectFromBtn.classList.remove('active'); selectToBtn.classList.add('active');
        mapElement.classList.add('selecting-mode');
        mapHint.textContent='📍 Cliquez sur la carte pour sélectionner le point d\'arrivée';
        mapHint.classList.add('active'); map.dragging.disable();
    } else{
        selectFromBtn.classList.remove('active'); selectToBtn.classList.remove('active');
        mapElement.classList.remove('selecting-mode'); mapHint.classList.remove('active'); map.dragging.enable();
    }
}

document.querySelector('.swap').addEventListener('click',e=>{
    e.preventDefault();
    [fromInput.value,toInput.value]=[toInput.value,fromInput.value];
    [fromMarker,toMarker]=[toMarker,fromMarker];
    updateHiddenLatLng();
    if(fromMarker&&toMarker){ const group=new L.featureGroup([fromMarker,toMarker]); map.fitBounds(group.getBounds().pad(0.1)); }
});
</script>

<?php include __DIR__ . "/includes/footer.php"; ?>
