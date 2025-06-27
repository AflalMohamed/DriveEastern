<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['driver', 'passenger'])) {
    http_response_code(403);
    exit('Unauthorized');
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

if (!isset($_GET['ride_id']) || !is_numeric($_GET['ride_id'])) {
    http_response_code(400);
    exit('Invalid ride ID');
}

$ride_id = $_GET['ride_id'];

$stmt = $pdo->prepare("SELECT driver_id, passenger_id FROM rides WHERE id = ?");
$stmt->execute([$ride_id]);
$ride = $stmt->fetch();

if (!$ride || !in_array($user_id, [$ride['driver_id'], $ride['passenger_id']])) {
    http_response_code(403);
    exit('Unauthorized for this ride');
}

$target_id = ($role === 'driver') ? $ride['passenger_id'] : $ride['driver_id'];

$stmt = $pdo->prepare("SELECT latitude, longitude, updated_at FROM live_locations WHERE user_id = ?");
$stmt->execute([$target_id]);
$data = $stmt->fetch();

header('Content-Type: application/json');
echo json_encode($data ?: []);
