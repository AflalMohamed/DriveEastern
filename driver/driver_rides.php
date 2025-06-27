<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'driver') {
    header('Location: login.php');
    exit;
}

$driver_id = $_SESSION['user_id'];
$name = $_SESSION['name'];

// Get ride history for driver
$stmt = $pdo->prepare("
    SELECT r.*, p.name AS passenger_name 
    FROM rides r 
    LEFT JOIN users p ON r.passenger_id = p.id 
    WHERE r.driver_id = ? 
    ORDER BY r.created_at DESC
");
$stmt->execute([$driver_id]);
$rides = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<h1>Your Ride History</h1>

<?php if (count($rides) === 0): ?>
    <p>You have not been assigned any rides yet.</p>
<?php else: ?>
<table border="1" cellpadding="10" cellspacing="0" style="border-collapse: collapse; width: 100%; max-width: 800px;">
    <thead>
        <tr style="background:#007bff; color:white;">
            <th>Ride ID</th>
            <th>Passenger</th>
            <th>Pickup</th>
            <th>Dropoff</th>
            <th>Fare</th>
            <th>Status</th>
            <th>Booked At</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($rides as $ride): ?>
        <tr>
            <td><?=htmlspecialchars($ride['id'])?></td>
            <td><?=htmlspecialchars($ride['passenger_name'] ?? 'N/A')?></td>
            <td><?=htmlspecialchars($ride['pickup_location'])?></td>
            <td><?=htmlspecialchars($ride['dropoff_location'])?></td>
            <td>$<?=htmlspecialchars($ride['fare'])?></td>
            <td><?=htmlspecialchars(ucfirst($ride['status']))?></td>
            <td><?=htmlspecialchars($ride['created_at'])?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<p><a href="driver_dashboard.php">Back to Dashboard</a></p>

<?php include __DIR__ . '/../includes/footer.php'; ?>
