<?php
$page_title = "Création de trajet — Choice&Go";
include __DIR__ . "/includes/header.php";
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet-control-geocoder/2.4.0/Control.Geocoder.min.css" />
<style>
  #map {
    height: 400px;
    margin: 1rem 0;
    border-radius: 8px;
    border: 1px solid #e0e0e0;
  }
  #map.selecting-mode {
    cursor: crosshair;
  }
  .geocoder-suggestions {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #ccc;
    border-top: none;
    max-height: 200px;
    overflow-y: auto;
    z-index: 10;
    list-style: none;
    margin: 0;
    padding: 0;
    display: none;
  }
  .geocoder-suggestions.active {
    display: block;
  }
  .geocoder-suggestions li {
    padding: 0.5rem;
    cursor: pointer;
    border-bottom: 1px solid #f0f0f0;
  }
  .geocoder-suggestions li:hover {
    background-color: #f5f5f5;
  }
  .location-input-wrapper {
    position: relative;
  }
  .map-hint {
    padding: 0.75rem;
    background-color: #e8f4f8;
    border-left: 4px solid #0066cc;
    border-radius: 4px;
    margin-bottom: 1rem;
    font-size: 0.9rem;
    color: #333;
    display: none;
  }
  .map-hint.active {
    display: block;
  }
  .map-buttons {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 1rem;
  }
  .map-btn {
    flex: 1;
    padding: 0.75rem;
    border: 2px solid #ddd;
    background: white;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.9rem;
    transition: all 0.2s;
  }
  .map-btn:hover {
    border-color: #0066cc;
    background-color: #f0f7ff;
  }
  .map-btn.active {
    border-color: #0066cc;
    background-color: #0066cc;
    color: white;
  }
</style>

<section class="container make-ride">
  <h1>CRÉATION DE TRAJET</h1>

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

<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet-control-geocoder/2.4.0/Control.Geocoder.min.js"></script>
<script>
  // Initialize map centered on France
  const map = L.map('map', {
    dragging: true,
    tap: true
  }).setView([46.2276, 2.2137], 6);
  
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors',
    maxZoom: 19
  }).addTo(map);

  let fromMarker, toMarker;
  let selectingMode = null;
  
  const fromInput = document.getElementById('from');
  const toInput = document.getElementById('to');
  const mapHint = document.getElementById('map-hint');
  const selectFromBtn = document.getElementById('select-from-btn');
  const selectToBtn = document.getElementById('select-to-btn');
  const clearSelectionBtn = document.getElementById('clear-selection-btn');

  // Geocoding suggestions handler
  async function setupGeocoder(inputId, suggestionsId) {
    const input = document.getElementById(inputId);
    const suggestionsList = document.getElementById(suggestionsId);
    const isFrom = inputId === 'from';

    input.addEventListener('input', async (e) => {
      const query = e.target.value.trim();
      
      if (query.length < 2) {
        suggestionsList.classList.remove('active');
        return;
      }

      try {
        const response = await fetch(
          `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(query)}&format=json&limit=5&countrycodes=fr`
        );
        const results = await response.json();

        suggestionsList.innerHTML = '';
        
        if (results.length > 0) {
          results.forEach(result => {
            const li = document.createElement('li');
            li.textContent = result.display_name;
            li.dataset.lat = result.lat;
            li.dataset.lon = result.lon;
            
            li.addEventListener('click', () => {
              input.value = result.display_name;
              suggestionsList.classList.remove('active');
              
              const lat = parseFloat(result.lat);
              const lon = parseFloat(result.lon);
              
              if (isFrom) {
                if (fromMarker) map.removeLayer(fromMarker);
                fromMarker = L.marker([lat, lon], { title: 'Départ' })
                  .addTo(map)
                  .bindPopup('Départ');
              } else {
                if (toMarker) map.removeLayer(toMarker);
                toMarker = L.marker([lat, lon], { title: 'Arrivée' })
                  .addTo(map)
                  .bindPopup('Arrivée');
              }
              
              if (fromMarker && toMarker) {
                const group = new L.featureGroup([fromMarker, toMarker]);
                map.fitBounds(group.getBounds().pad(0.1));
              }
            });
            
            suggestionsList.appendChild(li);
          });
          
          suggestionsList.classList.add('active');
        }
      } catch (error) {
        console.error('Geocoding error:', error);
      }
    });

    document.addEventListener('click', (e) => {
      if (e.target !== input) {
        suggestionsList.classList.remove('active');
      }
    });
  }

  setupGeocoder('from', 'from-suggestions');
  setupGeocoder('to', 'to-suggestions');

  // Map click handler - now uses PHP proxy with better error handling
  const mapClickHandler = async (e) => {
    if (!selectingMode) return;

    const lat = e.latlng.lat;
    const lon = e.latlng.lng;

    console.log(`Clicked at: ${lat}, ${lon}`);

    try {
      const url = `api/geocode.php?lat=${lat}&lon=${lon}`;
      console.log('Fetching:', url);
      
      const response = await fetch(url);
      const result = await response.json();
      
      console.log('Geocode response:', result, 'Status:', response.status);
      
      // Use fallback if API fails
      let addressName;
      if (!response.ok) {
        console.warn('Geocoding API error, using coordinates as fallback');
        addressName = `${lat.toFixed(4)}, ${lon.toFixed(4)}`;
      } else {
        addressName = result.address?.name || 
                     result.address?.city || 
                     result.address?.town || 
                     result.address?.village || 
                     result.address?.county ||
                     result.display_name || 
                     `${lat.toFixed(4)}, ${lon.toFixed(4)}`;
      }

      console.log('Address:', addressName);

      if (selectingMode === 'from') {
        fromInput.value = addressName;
        if (fromMarker) map.removeLayer(fromMarker);
        fromMarker = L.marker([lat, lon], { title: 'Départ' })
          .addTo(map)
          .bindPopup('Départ')
          .openPopup();
      } else if (selectingMode === 'to') {
        toInput.value = addressName;
        if (toMarker) map.removeLayer(toMarker);
        toMarker = L.marker([lat, lon], { title: 'Arrivée' })
          .addTo(map)
          .bindPopup('Arrivée')
          .openPopup();
      }

      if (fromMarker && toMarker) {
        const group = new L.featureGroup([fromMarker, toMarker]);
        map.fitBounds(group.getBounds().pad(0.1));
      }

      selectingMode = null;
      updateMapUI();
    } catch (error) {
      console.error('Reverse geocoding error:', error);
      // Even on complete failure, use coordinates
      const lat = e.latlng.lat;
      const lon = e.latlng.lng;
      const fallbackAddress = `${lat.toFixed(4)}, ${lon.toFixed(4)}`;
      
      if (selectingMode === 'from') {
        fromInput.value = fallbackAddress;
        if (fromMarker) map.removeLayer(fromMarker);
        fromMarker = L.marker([lat, lon], { title: 'Départ' })
          .addTo(map)
          .bindPopup('Départ')
          .openPopup();
      } else if (selectingMode === 'to') {
        toInput.value = fallbackAddress;
        if (toMarker) map.removeLayer(toMarker);
        toMarker = L.marker([lat, lon], { title: 'Arrivée' })
          .addTo(map)
          .bindPopup('Arrivée')
          .openPopup();
      }

      if (fromMarker && toMarker) {
        const group = new L.featureGroup([fromMarker, toMarker]);
        map.fitBounds(group.getBounds().pad(0.1));
      }

      selectingMode = null;
      updateMapUI();
    }
  };
  
  map.on('click', mapClickHandler);

  selectFromBtn.addEventListener('click', (e) => {
    e.preventDefault();
    selectingMode = selectingMode === 'from' ? null : 'from';
    updateMapUI();
  });

  selectToBtn.addEventListener('click', (e) => {
    e.preventDefault();
    selectingMode = selectingMode === 'to' ? null : 'to';
    updateMapUI();
  });

  clearSelectionBtn.addEventListener('click', (e) => {
    e.preventDefault();
    selectingMode = null;
    if (fromMarker) map.removeLayer(fromMarker);
    if (toMarker) map.removeLayer(toMarker);
    fromMarker = null;
    toMarker = null;
    fromInput.value = '';
    toInput.value = '';
    updateMapUI();
  });

  function updateMapUI() {
    const mapElement = document.getElementById('map');
    
    if (selectingMode === 'from') {
      selectFromBtn.classList.add('active');
      selectToBtn.classList.remove('active');
      mapElement.classList.add('selecting-mode');
      mapHint.textContent = '📍 Cliquez sur la carte pour sélectionner le point de départ';
      mapHint.classList.add('active');
      map.dragging.disable();
    } else if (selectingMode === 'to') {
      selectFromBtn.classList.remove('active');
      selectToBtn.classList.add('active');
      mapElement.classList.add('selecting-mode');
      mapHint.textContent = '📍 Cliquez sur la carte pour sélectionner le point d\'arrivée';
      mapHint.classList.add('active');
      map.dragging.disable();
    } else {
      selectFromBtn.classList.remove('active');
      selectToBtn.classList.remove('active');
      mapElement.classList.remove('selecting-mode');
      mapHint.classList.remove('active');
      map.dragging.enable();
    }
  }

  // Swap button functionality
  document.querySelector('.swap').addEventListener('click', (e) => {
    e.preventDefault();
    [fromInput.value, toInput.value] = [toInput.value, fromInput.value];
    [fromMarker, toMarker] = [toMarker, fromMarker];
    
    if (fromMarker && toMarker) {
      const group = new L.featureGroup([fromMarker, toMarker]);
      map.fitBounds(group.getBounds().pad(0.1));
    }
  });
</script>

<?php include __DIR__ . "/includes/footer.php"; ?>
