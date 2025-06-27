<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'passenger') {
    header('Location: login.php');
    exit;
}

$ride_id = $_POST['ride_id'] ?? null;
$rating = $_POST['rating'] ?? null;
$review = trim($_POST['review'] ?? '');

if (is_numeric($ride_id) && is_numeric($rating) && $rating >= 1 && $rating <= 5) {
    $stmt = $pdo->prepare("SELECT * FROM rides WHERE id = ? AND passenger_id = ? AND status = 'completed' AND rating IS NULL");
    $stmt->execute([$ride_id, $_SESSION['user_id']]);
    $ride = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($ride) {
        $stmt = $pdo->prepare("UPDATE rides SET rating = ?, review = ? WHERE id = ?");
        $stmt->execute([$rating, $review, $ride_id]);
        $_SESSION['message'] = 'Thanks for your feedback!';
    } else {
        $_SESSION['message'] = 'Invalid or already rated ride.';
    }
}

header('Location: passenger_dashboard.php');
exit;
