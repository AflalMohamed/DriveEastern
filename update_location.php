<?php
session_start();
require '../db.php';

header('Content-Type: application/json');

// Check if user logged in and is a driver
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'driver') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$driver_id = $_SESSION['user_id'];

// Get JSON body
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['lat'], $input['lng'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

$lat = filter_var($input['lat'], FILTER_VALIDATE_FLOAT);
$lng = filter_var($input['lng'], FILTER_VALIDATE_FLOAT);

if ($lat === false || $lng === false || $lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid latitude or longitude']);
    exit;
}

// Update or insert driver location (you may have a driver_locations table or driver_status table)
$stmt = $pdo->prepare("UPDATE driver_status SET latitude = ?, longitude = ?, last_update = NOW() WHERE driver_id = ?");
$stmt->execute([$lat, $lng, $driver_id]);

// If no row updated, insert it
if ($stmt->rowCount() === 0) {
    $stmt = $pdo->prepare("INSERT INTO driver_status (driver_id, latitude, longitude, last_update) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$driver_id, $lat, $lng]);
}

echo json_encode(['success' => true]);
