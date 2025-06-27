<?php
session_start();
require 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'passenger') {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['action'])) {
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

if ($input['action'] === 'book') {
    $pickup = trim($input['pickup_location'] ?? '');
    $dropoff = trim($input['dropoff_location'] ?? '');

    if (!$pickup || !$dropoff) {
        echo json_encode(['error' => 'Pickup and dropoff required']);
        exit;
    }

    // Find first available driver
    $stmt = $pdo->prepare("SELECT driver_id FROM driver_status WHERE is_available = 1 LIMIT 1");
    $stmt->execute();
    $driver = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$driver) {
        echo json_encode(['error' => 'No drivers available now']);
        exit;
    }

    $driver_id = $driver['driver_id'];
    $fare = 100; // stub fixed fare, extend with distance calc

    $stmt = $pdo->prepare("INSERT INTO rides (passenger_id, driver_id, pickup_location, dropoff_location, status, fare) VALUES (?, ?, ?, ?, 'requested', ?)");
    $stmt->execute([$user_id, $driver_id, $pickup, $dropoff, $fare]);

    // Mark driver unavailable
    $stmt = $pdo->prepare("UPDATE driver_status SET is_available = 0 WHERE driver_id = ?");
    $stmt->execute([$driver_id]);

    echo json_encode(['success' => true, 'message' => 'Ride booked!']);
    exit;
}

echo json_encode(['error' => 'Unknown action']);
