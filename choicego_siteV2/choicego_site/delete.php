<?php
require 'db.php';
session_start();

if (!isset($_GET['type'], $_GET['id'])) {
    die('Requête invalide');
}

$type = $_GET['type'];
$id = (int) $_GET['id'];
$userId = $_SESSION['user_id'];

switch ($type) {

    case 'vehicle':
        $stmt = $pdo->prepare("
            DELETE FROM vehicules
            WHERE vehicule_id = :id AND utilisateur_id = :uid
        ");
        break;

    case 'ride':
        $stmt = $pdo->prepare("
            DELETE FROM trajets
            WHERE trajet_id = :id AND conducteur_id = :uid
        ");
        break;

    case 'reservation':
        $stmt = $pdo->prepare("
            DELETE FROM reservations
            WHERE reservation_id = :id AND utilisateur_id = :uid
        ");
        break;

    default:
        die('Type invalide');
}

$stmt->execute([
    'id' => $id,
    'uid' => $userId
]);

header('Location: ' . $_SERVER['HTTP_REFERER']);
exit;
