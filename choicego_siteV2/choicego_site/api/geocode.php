<?php
// api/geocode.php
// Proxy endpoint for reverse geocoding to avoid CORS issues

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
  http_response_code(405);
  echo json_encode(['error' => 'Method not allowed']);
  exit;
}

$lat = $_GET['lat'] ?? null;
$lon = $_GET['lon'] ?? null;

if (!$lat || !$lon) {
  http_response_code(400);
  echo json_encode(['error' => 'Missing lat or lon parameters']);
  exit;
}

// Validate that lat/lon are numeric
if (!is_numeric($lat) || !is_numeric($lon)) {
  http_response_code(400);
  echo json_encode(['error' => 'Invalid lat or lon format']);
  exit;
}

$lat = floatval($lat);
$lon = floatval($lon);

// Try cURL first, fallback to file_get_contents
function fetchNominatim($url) {
  // Method 1: Try cURL
  if (extension_loaded('curl')) {
    $ch = curl_init();
    
    curl_setopt_array($ch, [
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_TIMEOUT => 10,
      CURLOPT_USERAGENT => 'ChoiceGo-Rideshare/1.0 (PHP)',
      CURLOPT_SSL_VERIFYPEER => false,
      CURLOPT_SSL_VERIFYHOST => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_MAXREDIRS => 5,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($response !== false) {
      return [
        'response' => $response,
        'httpCode' => $httpCode,
        'error' => null,
        'method' => 'cURL'
      ];
    } else {
      return [
        'response' => null,
        'httpCode' => null,
        'error' => 'cURL: ' . $curlError,
        'method' => 'cURL'
      ];
    }
  }

  // Method 2: Fallback to file_get_contents with stream context
  if (ini_get('allow_url_fopen')) {
    $context = stream_context_create([
      'http' => [
        'method' => 'GET',
        'header' => [
          'User-Agent: ChoiceGo-Rideshare/1.0 (PHP)',
        ],
        'timeout' => 10,
      ],
      'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
      ]
    ]);

    $response = @file_get_contents($url, false, $context);
    
    if ($response !== false) {
      return [
        'response' => $response,
        'httpCode' => 200,
        'error' => null,
        'method' => 'file_get_contents'
      ];
    } else {
      return [
        'response' => null,
        'httpCode' => null,
        'error' => 'file_get_contents: Failed to fetch URL',
        'method' => 'file_get_contents'
      ];
    }
  }

  return [
    'response' => null,
    'httpCode' => null,
    'error' => 'No suitable method available (cURL disabled, allow_url_fopen disabled)',
    'method' => 'none'
  ];
}

try {
  // Build Nominatim URL
  $url = sprintf(
    'https://nominatim.openstreetmap.org/reverse?format=json&lat=%f&lon=%f&zoom=18&addressdetails=1',
    $lat,
    $lon
  );

  $result = fetchNominatim($url);

  if ($result['error']) {
    http_response_code(503);
    echo json_encode([
      'error' => 'Failed to reach geocoding service',
      'details' => $result['error'],
      'method' => $result['method']
    ]);
    exit;
  }

  if ($result['httpCode'] !== 200) {
    http_response_code($result['httpCode'] >= 400 ? $result['httpCode'] : 503);
    echo json_encode([
      'error' => 'Nominatim returned HTTP ' . $result['httpCode'],
      'method' => $result['method']
    ]);
    exit;
  }

  // Verify it's valid JSON
  $decoded = json_decode($result['response']);
  if ($decoded === null) {
    http_response_code(502);
    echo json_encode([
      'error' => 'Invalid JSON response from Nominatim',
      'method' => $result['method']
    ]);
    exit;
  }

  // Return the response as-is
  echo $result['response'];
  
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode([
    'error' => 'Server error: ' . $e->getMessage()
  ]);
}