<?php
/**
 * Reverse Geocoding Proxy
 * Proxies requests to Nominatim with rate limiting and caching.
 */

session_start();

header('Content-Type: application/json; charset=utf-8');

// Rate limiting: max 10 requests per minute per session
$rate_limit = 10;
$rate_window = 60; // seconds

if (!isset($_SESSION['geocode_requests'])) {
    $_SESSION['geocode_requests'] = [];
}

// Clean old requests
$now = time();
$_SESSION['geocode_requests'] = array_filter(
    $_SESSION['geocode_requests'],
    fn($timestamp) => ($now - $timestamp) < $rate_window
);

// Check rate limit
if (count($_SESSION['geocode_requests']) >= $rate_limit) {
    http_response_code(429);
    echo json_encode(['error' => 'Too many requests. Please wait a moment.']);
    exit;
}

// Log this request
$_SESSION['geocode_requests'][] = $now;

// Validate parameters
if (!isset($_GET['lat'], $_GET['lon'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing parameters']);
    exit;
}

$lat = filter_var($_GET['lat'], FILTER_VALIDATE_FLOAT);
$lon = filter_var($_GET['lon'], FILTER_VALIDATE_FLOAT);

// Validate coordinate ranges
if ($lat === false || $lon === false || $lat < -90 || $lat > 90 || $lon < -180 || $lon > 180) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid coordinates']);
    exit;
}

// Simple file-based cache (optional - can improve performance)
$cache_dir = sys_get_temp_dir() . '/geocode_cache';
$cache_key = md5("reverse_{$lat}_{$lon}");
$cache_file = $cache_dir . '/' . $cache_key . '.json';
$cache_ttl = 86400; // 24 hours

if (is_file($cache_file) && (time() - filemtime($cache_file)) < $cache_ttl) {
    echo file_get_contents($cache_file);
    exit;
}

$url = "https://nominatim.openstreetmap.org/reverse?"
     . http_build_query([
        'lat' => $lat,
        'lon' => $lon,
        'format' => 'json',
        'zoom' => 14,
        'addressdetails' => 1
     ]);

$opts = [
    'http' => [
        'method'  => 'GET',
        'header'  => "User-Agent: ChoiceAndGo/1.0 (contact@choiceandgo.fr)\r\n",
        'timeout' => 5
    ]
];

$context = stream_context_create($opts);
$response = @file_get_contents($url, false, $context);

if ($response === false) {
    http_response_code(502);
    echo json_encode(['error' => 'Nominatim service unavailable']);
    exit;
}

// Cache the response
if (!is_dir($cache_dir)) {
    @mkdir($cache_dir, 0755, true);
}
@file_put_contents($cache_file, $response);

echo $response;
