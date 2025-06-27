<?php
session_start();
require '../db.php';

header('Content-Type: application/json');

$driver_id = intval($_GET['driver_id'] ?? 0);
if ($driver_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid driver ID']);
    exit;
}

// Fetch location from driver_status or driver_locations (your choice)
$stmt = $pdo->prepare("SELECT latitude, longitude FROM driver_status WHERE driver_id = ?");
$stmt->execute([$driver_id]);
$location = $stmt->fetch(PDO::FETCH_ASSOC);

if ($location && $location['latitude'] !== null && $location['longitude'] !== null) {
    echo json_encode([
        'success' => true,
        'latitude' => floatval($location['latitude']),
        'longitude' => floatval($location['longitude'])
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Location not found'
    ]);
}
