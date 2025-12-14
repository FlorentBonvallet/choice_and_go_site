<?php
// reverse_geocode.php

header('Content-Type: application/json; charset=utf-8');

if (!isset($_GET['lat'], $_GET['lon'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing parameters']);
    exit;
}

$lat = floatval($_GET['lat']);
$lon = floatval($_GET['lon']);

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
        'header'  => "User-Agent: ChoiceAndGo/1.0 (contact@choiceandgo.fr)\r\n"
    ]
];

$context = stream_context_create($opts);
$response = file_get_contents($url, false, $context);

if ($response === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Nominatim unreachable']);
    exit;
}

echo $response;
