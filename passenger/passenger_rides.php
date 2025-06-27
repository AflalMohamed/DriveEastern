<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'passenger') {
    header('Location: login.php');
    exit;
}

$passenger_id = $_SESSION['user_id'];
$name = $_SESSION['name'];

// Fetch rides for passenger, order by newest first
$stmt = $pdo->prepare("SELECT r.*, d.name AS driver_name FROM rides r LEFT JOIN users d ON r.driver_id = d.id WHERE r.passenger_id = ? ORDER BY r.created_at DESC");
$stmt->execute([$passenger_id]);
$rides = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<h1>Your Ride History</h1>

<?php if (count($rides) === 0): ?>
    <p>You have no rides booked yet.</p>
<?php else: ?>
<table border="1" cellpadding="10" cellspacing="0" style="border-collapse: collapse; width: 100%; max-width: 800px;">
    <thead>
        <tr style="background:#007bff; color:white;">
            <th>Ride ID</th>
            <th>Driver</th>
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
            <td><?=htmlspecialchars($ride['driver_name'] ?? 'N/A')?></td>
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

<p><a href="passenger_dashboard.php">Back to Dashboard</a></p>

<?php include __DIR__ . '/../includes/footer.php'; ?>
