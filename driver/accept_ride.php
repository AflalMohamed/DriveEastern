<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'driver') {
    header('Location: login.php');
    exit;
}

$driver_id = $_SESSION['user_id'];

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['message'] = "Invalid ride ID.";
    header('Location: driver_dashboard.php');
    exit;
}

$ride_id = (int)$_GET['id'];

// Validate the ride belongs to the driver and is still in 'requested' status
$stmt = $pdo->prepare("SELECT * FROM rides WHERE id = ? AND driver_id = ? AND status = 'requested'");
$stmt->execute([$ride_id, $driver_id]);
$ride = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ride) {
    $_SESSION['message'] = "Ride not found or already accepted.";
    header('Location: driver_dashboard.php');
    exit;
}

// Update ride status to 'accepted'
$stmt = $pdo->prepare("UPDATE rides SET status = 'accepted', accepted_at = NOW() WHERE id = ? AND driver_id = ?");
$stmt->execute([$ride_id, $driver_id]);

$_SESSION['message'] = "Ride #$ride_id accepted successfully.";
header('Location: driver_dashboard.php');
exit;
?>
