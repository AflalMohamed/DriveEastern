<?php
header('Content-Type: application/json');
require '../db.php';

$driver_id = intval($_GET['driver_id'] ?? 0);
if ($driver_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid driver ID']);
    exit;
}

$stmt = $pdo->prepare("SELECT latitude, longitude, updated_at FROM driver_locations WHERE driver_id = ?");
$stmt->execute([$driver_id]);
$loc = $stmt->fetch(PDO::FETCH_ASSOC);

if ($loc) {
    echo json_encode([
        'success' => true,
        'latitude' => floatval($loc['latitude']),
        'longitude' => floatval($loc['longitude']),
        'updated_at' => $loc['updated_at']
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Location not available']);
}
