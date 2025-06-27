<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'passenger') {
    header('Location: login.php');
    exit;
}

$passenger_id = $_SESSION['user_id'];

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['message'] = 'Invalid ride ID.';
    header('Location: passenger_dashboard.php');
    exit;
}

$ride_id = (int)$_GET['id'];

// Only cancel if ride belongs to user and is still 'requested'
$stmt = $pdo->prepare("SELECT * FROM rides WHERE id = ? AND passenger_id = ? AND status = 'requested'");
$stmt->execute([$ride_id, $passenger_id]);
$ride = $stmt->fetch();

if (!$ride) {
    $_SESSION['message'] = 'Ride cannot be canceled or does not exist.';
    header('Location: passenger_dashboard.php');
    exit;
}

// Update ride status
$stmt = $pdo->prepare("UPDATE rides SET status = 'canceled' WHERE id = ?");
$stmt->execute([$ride_id]);

// Make driver available again
$stmt = $pdo->prepare("UPDATE driver_status SET is_available = 1 WHERE driver_id = ?");
$stmt->execute([$ride['driver_id']]);

$_SESSION['message'] = "Ride #$ride_id has been successfully canceled.";
header('Location: passenger_dashboard.php');
exit;
