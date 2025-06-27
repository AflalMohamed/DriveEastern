<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'passenger') {
    header('Location: login.php');
    exit;
}

$passenger_id = $_SESSION['user_id'];
$name = $_SESSION['name'];
$message = '';

// Fare calculation at R$60 per kilometer (rounded up)
fdefine('PER_KM_RATE', 60.00);

function calculateFare($distance) {
    return round($distance * 60);
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pickup = trim($_POST['pickup_location'] ?? '');
    $dropoff = trim($_POST['dropoff_location'] ?? '');
    $distance = floatval($_POST['distance'] ?? 0);

    if ($pickup === '' || $dropoff === '' || $distance <= 0) {
        $message = 'Please fill all fields and enter a valid distance.';
    } else {
        // Check for active ride
        $stmt = $pdo->prepare("SELECT * FROM rides WHERE passenger_id = ? AND status IN ('requested', 'accepted') LIMIT 1");
        $stmt->execute([$passenger_id]);
        $existingRide = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingRide) {
            $message = 'You already have an active ride. Please wait until it is completed or canceled.';
        } else {
            // Find available driver
            $stmt = $pdo->prepare("SELECT driver_id FROM driver_status WHERE is_available = 1 LIMIT 1");
            $stmt->execute();
            $driver = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$driver) {
                $message = 'Sorry, no drivers are available at the moment. Please try again later.';
            } else {
                $driver_id = $driver['driver_id'];
                $fare = calculateFare($distance);

                // Insert new ride
                $stmt = $pdo->prepare("INSERT INTO rides (passenger_id, driver_id, pickup_location, dropoff_location, fare, status, created_at) VALUES (?, ?, ?, ?, ?, 'requested', NOW())");
                $stmt->execute([$passenger_id, $driver_id, $pickup, $dropoff, $fare]);

                // Mark driver unavailable
                $stmt = $pdo->prepare("UPDATE driver_status SET is_available = 0 WHERE driver_id = ?");
                $stmt->execute([$driver_id]);

                $message = "Your ride has been booked! Fare: Rs." . number_format($fare, 2) . ". Driver ID: $driver_id.";
            }
        }
    }
}

// Fetch recent rides
$stmt = $pdo->prepare("SELECT * FROM rides WHERE passenger_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$passenger_id]);
$rides = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<h1>Welcome, <?= htmlspecialchars($name) ?>!</h1>

<?php if ($message): ?>
    <div style="padding:10px; background-color:#d4edda; color:#155724; border-radius:5px; margin-bottom:15px;">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<h2>Book a New Ride</h2>

<form method="POST" style="max-width:400px;">
    <div>
        <label for="pickup_location">Pickup Location:</label><br />
        <input type="text" id="pickup_location" name="pickup_location" required style="width:100%; padding:8px;" value="<?= htmlspecialchars($_POST['pickup_location'] ?? '') ?>" />
    </div>
    <div style="margin-top:10px;">
        <label for="dropoff_location">Dropoff Location:</label><br />
        <input type="text" id="dropoff_location" name="dropoff_location" required style="width:100%; padding:8px;" value="<?= htmlspecialchars($_POST['dropoff_location'] ?? '') ?>" />
    </div>
    <div style="margin-top:10px;">
        <label for="distance">Estimated Distance (in km):</label><br />
        <input type="number" step="0.1" min="0" id="distance" name="distance" required style="width:100%; padding:8px;" value="<?= htmlspecialchars($_POST['distance'] ?? '') ?>" />
    </div>
    <div style="margin-top:15px;">
        <button type="submit" style="padding:10px 20px; background:#007bff; color:white; border:none; border-radius:5px;">Book Ride</button>
    </div>
</form>

<?php if ($rides): ?>
    <h2 style="margin-top:40px;">Your Recent Rides</h2>
    <table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; width:100%; max-width:700px;">
        <thead>
            <tr style="background:#f2f2f2;">
                <th>Ride ID</th>
                <th>Pickup</th>
                <th>Dropoff</th>
                <th>Fare</th>
                <th>Status</th>
                <th>Created</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($rides as $ride): ?>
            <tr>
                <td><?= $ride['id'] ?></td>
                <td><?= htmlspecialchars($ride['pickup_location']) ?></td>
                <td><?= htmlspecialchars($ride['dropoff_location']) ?></td>
                <td>Rs.<?= number_format($ride['fare'], 2) ?></td>
                <td><?= ucfirst($ride['status']) ?></td>
                <td><?= $ride['created_at'] ?></td>
                <td>
                    <?php if ($ride['status'] === 'requested'): ?>
                        <a href="cancel_ride.php?id=<?= $ride['id'] ?>" onclick="return confirm('Cancel this ride?')" style="color:red;">Cancel</a>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
