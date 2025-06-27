<?php
session_start();
require '../db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'driver') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$driver_id = $_SESSION['user_id'];
$lat = isset($_POST['latitude']) ? floatval($_POST['latitude']) : null;
$lng = isset($_POST['longitude']) ? floatval($_POST['longitude']) : null;

if ($lat !== null && $lng !== null) {
    $stmt = $pdo->prepare("
        REPLACE INTO driver_locations (driver_id, latitude, longitude, updated_at)
        VALUES (?, ?, ?, NOW())
    ");
    $stmt->execute([$driver_id, $lat, $lng]);
    echo json_encode(['success' => true]);
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid coordinates']);
}
