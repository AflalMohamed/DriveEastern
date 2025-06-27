<?php
session_start();
require 'db.php';

header('Content-Type: application/json');

// Ensure driver is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'driver') {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$driver_id = $_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true);

// Validate JSON input
if (!$input || !isset($input['action'])) {
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

// Get available rides for this driver
if ($input['action'] === 'get_rides') {
    $stmt = $pdo->prepare("
        SELECT r.*, u.name AS passenger_name 
        FROM rides r 
        JOIN users u ON r.passenger_id = u.id 
        WHERE r.driver_id = ? 
          AND r.status IN ('requested', 'accepted') 
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$driver_id]);
    $rides = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'rides' => $rides]);
    exit;
}

// Accept or complete ride
if (in_array($input['action'], ['accept', 'complete'])) {
    $ride_id = $input['ride_id'] ?? 0;

    if (!$ride_id) {
        echo json_encode(['error' => 'Ride ID required']);
        exit;
    }

    if ($input['action'] === 'accept') {
        // Accept the ride
        $stmt = $pdo->prepare("
            UPDATE rides 
            SET status = 'accepted', accepted_at = NOW() 
            WHERE id = ? AND driver_id = ? AND status = 'requested'
        ");
        $stmt->execute([$ride_id, $driver_id]);

        echo json_encode(['success' => true, 'message' => 'Ride accepted']);
        exit;
    }

    if ($input['action'] === 'complete') {
        // Complete the ride and mark driver as available
        $stmt = $pdo->prepare("
            UPDATE rides 
            SET status = 'completed', completed_at = NOW() 
            WHERE id = ? AND driver_id = ? AND status = 'accepted'
        ");
        $stmt->execute([$ride_id, $driver_id]);

        $stmt = $pdo->prepare("UPDATE driver_status SET is_available = 1 WHERE driver_id = ?");
        $stmt->execute([$driver_id]);

        echo json_encode(['success' => true, 'message' => 'Ride completed']);
        exit;
    }
}

// Unknown action
echo json_encode(['error' => 'Unknown action']);
exit;
