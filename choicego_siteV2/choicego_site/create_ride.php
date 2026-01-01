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

/**
 * Calcule la distance (km) entre deux points lat/lon avec la formule de Haversine.
 */
function haversine_distance_km($lat1, $lon1, $lat2, $lon2)
{
    $earth_radius = 6371.0; // rayon terrestre en km
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat / 2) * sin($dLat / 2) +
        cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
        sin($dLon / 2) * sin($dLon / 2);
    $c = 2 * asin(min(1, sqrt($a)));
    return $earth_radius * $c;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    try {
        $lieu_depart = $_POST['from'];
        $lieu_arrivee = $_POST['to'];
        $date = $_POST['date'];
        $time_from = $_POST['time_from'];
        $places = intval($_POST['pax']);

        // Récupération du vehicule_id depuis le formulaire
        $vehicule_id = $_POST['vehicule_id'] ?? null;

        // Récupération des lat/lon
        $depart_lat = $_POST['depart_latitude'] ?? null;
        $depart_lon = $_POST['depart_longitude'] ?? null;
        $arrivee_lat = $_POST['arrivee_latitude'] ?? null;
        $arrivee_lon = $_POST['arrivee_longitude'] ?? null;

        // normalisation / conversion en float si possible
        $dLat = is_numeric($depart_lat) ? floatval($depart_lat) : null;
        $dLon = is_numeric($depart_lon) ? floatval($depart_lon) : null;
        $aLat = is_numeric($arrivee_lat) ? floatval($arrivee_lat) : null;
        $aLon = is_numeric($arrivee_lon) ? floatval($arrivee_lon) : null;

        // Calcul de la distance et du prix par place
        $prix_par_place = 0.00;
        if ($dLat !== null && $dLon !== null && $aLat !== null && $aLon !== null) {
            $distance_km = haversine_distance_km($dLat, $dLon, $aLat, $aLon);

            // Paramètres tarifaires (modifiable) :
            $tarif_par_km = 0.10; // euros par km par place
            $prix_minimum = 2.00; // prix minimum par place en euros

            $calcule = $distance_km * $tarif_par_km;
            // arrondir à 2 décimales et appliquer minimum
            $prix_par_place = round(max($prix_minimum, $calcule), 2);
        }

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
            ':prix_par_place' => $prix_par_place,
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
        <input type="hidden" name="depart_latitude" id="depart_latitude" />
        <input type="hidden" name="depart_longitude" id="depart_longitude" />
        <input type="hidden" name="arrivee_latitude" id="arrivee_latitude" />
        <input type="hidden" name="arrivee_longitude" id="arrivee_longitude" />

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
            <label>
                Véhicule :
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

    const map = L.map('map', { dragging: true, tap: true }).setView([46.2276, 2.2137], 6);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap contributors', maxZoom: 19 }).addTo(map);

    let fromMarker, toMarker;
    let selectingMode = null;


    const fromInput = document.getElementById('from');
    const toInput = document.getElementById('to');
    const mapHint = document.getElementById('map-hint');
    const selectFromBtn = document.getElementById('select-from-btn');
    const selectToBtn = document.getElementById('select-to-btn');
    const clearSelectionBtn = document.getElementById('clear-selection-btn');

    function updateHiddenLatLng() {
        if (fromMarker) {
            document.getElementById('depart_latitude').value = fromMarker.getLatLng().lat;
            document.getElementById('depart_longitude').value = fromMarker.getLatLng().lng;
        }
        if (toMarker) {
            document.getElementById('arrivee_latitude').value = toMarker.getLatLng().lat;
            document.getElementById('arrivee_longitude').value = toMarker.getLatLng().lng;
        }
    }


async function setupGeocoder(inputId, suggestionsId) {
    const input = document.getElementById(inputId);
    const suggestionsList = document.getElementById(suggestionsId);
    const isFrom = inputId === 'from';
    const relatedBtn = isFrom ? document.getElementById('select-from-btn') : document.getElementById('select-to-btn');

    // debounce + abort pour éviter flicker et réponses hors-ordre
    let debounceTimer = null;
    const DEBOUNCE_MS = 300;

    let abortController = null;

    // durée pendant laquelle la liste reste visible après saisie
    let hideTimeout = null;
    const HIDE_DELAY = 5000;

    // keyboard navigation
    let focusedIndex = -1;

    function clearHideTimeout() {
        if (hideTimeout) { clearTimeout(hideTimeout); hideTimeout = null; }
    }
    function scheduleHide(delay = HIDE_DELAY) {
        clearHideTimeout();
        hideTimeout = setTimeout(() => {
            hideSuggestionsNow();
        }, delay);
    }
    function showSuggestions() {
        suggestionsList.classList.add('active');
        if (relatedBtn) relatedBtn.classList.add('suggest-active');
        scheduleHide();
    }
    function hideSuggestionsNow() {
        clearHideTimeout();
        suggestionsList.classList.remove('active');
        if (relatedBtn) relatedBtn.classList.remove('suggest-active');
        focusedIndex = -1;
        updateKeyboardHighlight();
    }

    function updateKeyboardHighlight() {
        const items = suggestionsList.querySelectorAll('li');
        items.forEach((li, i) => {
            if (i === focusedIndex) li.classList.add('keyboard-focus');
            else li.classList.remove('keyboard-focus');
        });
    }

    async function fetchSuggestions(query) {
        // annule la requête précédente
        if (abortController) abortController.abort();
        abortController = new AbortController();
        const signal = abortController.signal;

        // Nominatim - limit to France; adjust viewbox if desired
        const viewbox = '-5.142222,51.089062,9.560016,41.333740';
        const url = `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(query)}&format=json&limit=6&countrycodes=fr&accept-language=fr&addressdetails=1&viewbox=${viewbox}&bounded=1`;

        try {
            const resp = await fetch(url, { signal, headers: { 'User-Agent': 'Choice&Go/1.0 (mailto:contact@votre-domaine)' } });
            if (!resp.ok) throw new Error('Network response not ok');
            const results = await resp.json();
            populateSuggestions(results);
            showSuggestions();
        } catch (err) {
            if (err.name === 'AbortError') return;
            console.error('Geocoder fetch error', err);
            suggestionsList.innerHTML = '';
            hideSuggestionsNow();
        }
    }

    function populateSuggestions(results) {
        suggestionsList.innerHTML = '';
        focusedIndex = -1;
        updateKeyboardHighlight();

        if (!results || results.length === 0) return;

        results.forEach(result => {
            const li = document.createElement('li');
            li.textContent = result.display_name;
            li.tabIndex = -1;
            li.dataset.lat = result.lat;
            li.dataset.lon = result.lon;

            // mousedown pour capturer avant blur
            li.addEventListener('mousedown', (ev) => {
                ev.preventDefault();
                selectSuggestion(result);
            });

            // souris : garder visible
            li.addEventListener('mouseenter', () => {
                clearHideTimeout();
                focusedIndex = Array.from(suggestionsList.children).indexOf(li);
                updateKeyboardHighlight();
            });
            li.addEventListener('mouseleave', () => {
                focusedIndex = -1;
                updateKeyboardHighlight();
                scheduleHide(1000);
            });

            suggestionsList.appendChild(li);
        });
    }

    function selectSuggestion(result) {
    input.value = result.display_name;
    const lat = parseFloat(result.lat), lon = parseFloat(result.lon);
    const icon = isFrom ? departIcon : arriveeIcon;

    if (isFrom) {
        if (fromMarker) map.removeLayer(fromMarker);
        fromMarker = L.marker([lat, lon], { title: 'Départ', icon: icon })
            .addTo(map)
            .bindPopup('Départ')
            .openPopup();
    } else {
        if (toMarker) map.removeLayer(toMarker);
        toMarker = L.marker([lat, lon], { title: 'Arrivée', icon: icon })
            .addTo(map)
            .bindPopup('Arrivée')
            .openPopup();
    }

    if (fromMarker && toMarker) {
        const group = new L.featureGroup([fromMarker, toMarker]);
        map.fitBounds(group.getBounds().pad(0.1));
    } else {
        // centrer si un seul marker présent
        const single = isFrom ? fromMarker : toMarker;
        if (single) map.setView(single.getLatLng(), 13);
    }

    updateHiddenLatLng();
    hideSuggestionsNow();
}

    // input handling with debounce
    input.addEventListener('input', (e) => {
        const query = e.target.value.trim();
        if (debounceTimer) clearTimeout(debounceTimer);
        if (query.length < 2) {
            hideSuggestionsNow();
            return;
        }
        debounceTimer = setTimeout(() => {
            fetchSuggestions(query);
        }, DEBOUNCE_MS);
    });

    // show existing suggestions on focus
    input.addEventListener('focus', () => {
        if (suggestionsList.children.length > 0) showSuggestions();
        else if (input.value.trim().length >= 2) {
            if (debounceTimer) clearTimeout(debounceTimer);
            fetchSuggestions(input.value.trim());
        }
    });

    // allow short delay on blur to let mousedown selection fire
    input.addEventListener('blur', () => scheduleHide(250));

    // click outside to hide
    document.addEventListener('click', (e) => {
        if (!input.contains(e.target) && !suggestionsList.contains(e.target)) hideSuggestionsNow();
    });

    // keyboard navigation
    input.addEventListener('keydown', (e) => {
        const items = suggestionsList.querySelectorAll('li');
        if (items.length === 0) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            focusedIndex = (focusedIndex + 1) % items.length;
            items[focusedIndex].scrollIntoView({ block: 'nearest' });
            updateKeyboardHighlight();
            clearHideTimeout();
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            focusedIndex = (focusedIndex - 1 + items.length) % items.length;
            items[focusedIndex].scrollIntoView({ block: 'nearest' });
            updateKeyboardHighlight();
            clearHideTimeout();
        } else if (e.key === 'Enter') {
            if (focusedIndex >= 0 && items[focusedIndex]) {
                e.preventDefault();
                const li = items[focusedIndex];
                const result = { display_name: li.textContent, lat: li.dataset.lat, lon: li.dataset.lon };
                selectSuggestion(result);
            }
        } else if (e.key === 'Escape') {
            hideSuggestionsNow();
        }
    });

    // keep suggestions visible while hovering the list
    suggestionsList.addEventListener('mouseenter', clearHideTimeout);
    suggestionsList.addEventListener('mouseleave', () => scheduleHide(1000));
}

    const departIcon = L.icon({
        iconUrl: 'https://cdn.jsdelivr.net/gh/pointhi/leaflet-color-markers@master/img/marker-icon-blue.png',
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41]
    });

    const arriveeIcon = L.icon({
        iconUrl: 'https://cdn.jsdelivr.net/gh/pointhi/leaflet-color-markers@master/img/marker-icon-red.png',
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41]
    });

    setupGeocoder('from', 'from-suggestions');
    setupGeocoder('to', 'to-suggestions');

    // CLICK SUR LA CARTE
    map.on('click', async e => {
        if (!selectingMode) return;

        const lat = e.latlng.lat;
        const lon = e.latlng.lng;

        let addressName = 'Lieu sélectionné sur la carte';

        try {
            const response = await fetch(`reverse_geocode.php?lat=${lat}&lon=${lon}`);
            const data = await response.json();

            const addr = data.address || {};

            addressName =
                addr.city
                || addr.town
                || addr.village
                || addr.municipality
                || addr.locality
                || addr.county
                || addr.state
                || data.display_name
                || addressName;

        } catch (e) {
            console.warn('Reverse geocoding impossible', e);
        }

        if (selectingMode === 'from') {
            fromInput.value = addressName;
            if (fromMarker) map.removeLayer(fromMarker);
            fromMarker = L.marker([lat, lon], { title: 'Départ', icon: departIcon }).addTo(map).bindPopup('Départ').openPopup();
        } else if (selectingMode === 'to') {
            toInput.value = addressName;
            if (toMarker) map.removeLayer(toMarker);
            toMarker = L.marker([lat, lon], { title: 'Arrivée', icon: arriveeIcon }).addTo(map).bindPopup('Arrivée').openPopup();
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

    selectFromBtn.addEventListener('click', e => { e.preventDefault(); selectingMode = selectingMode === 'from' ? null : 'from'; updateMapUI(); });
    selectToBtn.addEventListener('click', e => { e.preventDefault(); selectingMode = selectingMode === 'to' ? null : 'to'; updateMapUI(); });
    clearSelectionBtn.addEventListener('click', e => {
        e.preventDefault();
        selectingMode = null;
        if (fromMarker) map.removeLayer(fromMarker);
        if (toMarker) map.removeLayer(toMarker);
        fromMarker = null; toMarker = null;
        fromInput.value = ''; toInput.value = '';
        updateHiddenLatLng();
        updateMapUI();
    });

    function updateMapUI() {
        const mapElement = document.getElementById('map');
        if (selectingMode === 'from') {
            selectFromBtn.classList.add('active'); selectToBtn.classList.remove('active');
            mapElement.classList.add('selecting-mode');
            mapHint.textContent = '📍 Cliquez sur la carte pour sélectionner le point de départ';
            mapHint.classList.add('active'); map.dragging.disable();
        } else if (selectingMode === 'to') {
            selectFromBtn.classList.remove('active'); selectToBtn.classList.add('active');
            mapElement.classList.add('selecting-mode');
            mapHint.textContent = '📍 Cliquez sur la carte pour sélectionner le point d\'arrivée';
            mapHint.classList.add('active'); map.dragging.disable();
        } else {
            selectFromBtn.classList.remove('active'); selectToBtn.classList.remove('active');
            mapElement.classList.remove('selecting-mode'); mapHint.classList.remove('active'); map.dragging.enable();
        }
    }



    document.querySelector('.swap').addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();

        // swap texte
        const fromValue = fromInput.value;
        fromInput.value = toInput.value;
        toInput.value = fromValue;

        // swap coordonnées cachées
        const dLat = document.getElementById('depart_latitude').value;
        const dLng = document.getElementById('depart_longitude').value;

        document.getElementById('depart_latitude').value =
            document.getElementById('arrivee_latitude').value;
        document.getElementById('depart_longitude').value =
            document.getElementById('arrivee_longitude').value;

        document.getElementById('arrivee_latitude').value = dLat;
        document.getElementById('arrivee_longitude').value = dLng;

        //swap markers avec couleurs
        const fromLatLng = fromMarker ? fromMarker.getLatLng() : null;
        const toLatLng = toMarker ? toMarker.getLatLng() : null;

        if (fromMarker) map.removeLayer(fromMarker);
        if (toMarker) map.removeLayer(toMarker);

        fromMarker = null;
        toMarker = null;

        if (toLatLng) {
            fromMarker = L.marker(toLatLng, { title: 'Départ', icon: departIcon })
                .addTo(map)
                .bindPopup('Départ');
        }

        if (fromLatLng) {
            toMarker = L.marker(fromLatLng, { title: 'Arrivée', icon: arriveeIcon })
                .addTo(map)
                .bindPopup('Arrivée');
        }

        // ajuste la vue si les deux markers existent
        if (fromMarker && toMarker) {
            map.fitBounds(
                L.featureGroup([fromMarker, toMarker]).getBounds().pad(0.1)
            );
        }

        // réinitialise l’UI
        selectingMode = null;
        updateMapUI();
    });


</script>

<?php include __DIR__ . "/includes/footer.php"; ?>
