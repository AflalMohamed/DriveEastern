<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'driver') {
    header('Location: login.php');
    exit;
}

$driver_id = $_SESSION['user_id'];
$name = $_SESSION['name'];
$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);

// Handle accept ride — assign driver_id and status if ride is unassigned
if (isset($_GET['accept'])) {
    $ride_id = (int)$_GET['accept'];

    // Only accept if the ride is still unassigned and requested (atomic update)
    $stmt = $pdo->prepare("UPDATE rides SET driver_id = ?, status = 'accepted', accepted_at = NOW() WHERE id = ? AND status = 'requested' AND driver_id IS NULL");
    $stmt->execute([$driver_id, $ride_id]);

    if ($stmt->rowCount() > 0) {
        // Mark driver busy
        $pdo->prepare("UPDATE driver_status SET is_available = 0 WHERE driver_id = ?")->execute([$driver_id]);

        $_SESSION['message'] = "Ride #$ride_id accepted.";
    } else {
        $_SESSION['message'] = "Ride #$ride_id is no longer available.";
    }
    header('Location: driver_dashboard.php');
    exit;
}

// Handle reject ride (optional)
if (isset($_GET['reject'])) {
    $ride_id = (int)$_GET['reject'];
    $stmt = $pdo->prepare("UPDATE rides SET status = 'rejected', rejected_at = NOW() WHERE id = ? AND driver_id = ? AND status = 'requested'");
    $stmt->execute([$ride_id, $driver_id]);
    $_SESSION['message'] = "Ride #$ride_id rejected.";
    header('Location: driver_dashboard.php');
    exit;
}

// Handle complete ride
if (isset($_GET['complete'])) {
    $ride_id = (int)$_GET['complete'];
    $pdo->prepare("UPDATE rides SET status = 'completed', completed_at = NOW() WHERE id = ? AND driver_id = ? AND status = 'accepted'")
        ->execute([$ride_id, $driver_id]);
    $pdo->prepare("UPDATE driver_status SET is_available = 1 WHERE driver_id = ?")->execute([$driver_id]);
    $_SESSION['message'] = "Ride #$ride_id marked as completed.";
    header('Location: driver_dashboard.php');
    exit;
}

// Handle hide completed ride
if (isset($_GET['delete'])) {
    $ride_id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("SELECT id FROM rides WHERE id = ? AND driver_id = ? AND status = 'completed'");
    $stmt->execute([$ride_id, $driver_id]);
    if ($stmt->fetch()) {
        $pdo->prepare("UPDATE rides SET driver_deleted = 1 WHERE id = ?")->execute([$ride_id]);
        $_SESSION['message'] = "Ride #$ride_id hidden.";
    } else {
        $_SESSION['message'] = "Cannot hide ride #$ride_id.";
    }
    header('Location: driver_dashboard.php');
    exit;
}

// Fetch rides assigned to this driver with status requested or accepted
$stmt = $pdo->prepare("SELECT rides.*, users.phone AS passenger_phone FROM rides JOIN users ON rides.passenger_id = users.id WHERE rides.driver_id = ? AND rides.status IN ('requested', 'accepted') ORDER BY rides.created_at ASC LIMIT 1");
$stmt->execute([$driver_id]);
$current = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch unassigned ride requests (status = requested, driver_id IS NULL)
$availableStmt = $pdo->query("SELECT rides.*, users.name AS passenger_name, users.phone AS passenger_phone FROM rides JOIN users ON rides.passenger_id = users.id WHERE rides.status = 'requested' AND rides.driver_id IS NULL ORDER BY rides.created_at ASC");

// Fetch ride history (completed and not hidden)
$history = $pdo->prepare("SELECT * FROM rides WHERE driver_id = ? AND status = 'completed' AND driver_deleted = 0 ORDER BY completed_at DESC LIMIT 10");
$history->execute([$driver_id]);

// Calculate total earnings
$total_earnings = $pdo->prepare("SELECT COALESCE(SUM(fare), 0) FROM rides WHERE driver_id = ? AND status = 'completed' AND driver_deleted = 0");
$total_earnings->execute([$driver_id]);

// Calculate average rating
$avg_rating = $pdo->prepare("SELECT AVG(rating) FROM rides WHERE driver_id = ? AND rating IS NOT NULL");
$avg_rating->execute([$driver_id]);
$avg_rating = $avg_rating->fetchColumn();
$avg_rating = $avg_rating ? round($avg_rating, 2) : 'No ratings yet';

include '../includes/header.php';
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-warning fw-bold">Driver Dashboard</h2>
        <a href="edit_profile.php" class="btn btn-warning text-dark fw-bold">Manage Profile</a>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-warning"> <?= htmlspecialchars($message) ?> </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-8">
            <!-- Current assigned ride -->
            <div class="card shadow-sm mb-4 border-warning">
                <div class="card-header bg-warning text-dark fw-bold">Current Ride</div>
                <div class="card-body">
                    <?php if ($current): ?>
                        <dl class="row">
                            <dt class="col-sm-4">Ride ID:</dt><dd class="col-sm-8"><?= $current['id'] ?></dd>
                            <dt class="col-sm-4">Pickup:</dt><dd class="col-sm-8"><?= htmlspecialchars($current['pickup_location']) ?></dd>
                            <dt class="col-sm-4">Dropoff:</dt><dd class="col-sm-8"><?= htmlspecialchars($current['dropoff_location']) ?></dd>
                            <dt class="col-sm-4">Fare:</dt><dd class="col-sm-8">Rs. <?= number_format($current['fare'], 2) ?></dd>
                            <dt class="col-sm-4">Status:</dt><dd class="col-sm-8"><?= ucfirst($current['status']) ?></dd>
                            <dt class="col-sm-4">Passenger Phone:</dt><dd class="col-sm-8"><a href="tel:<?= htmlspecialchars($current['passenger_phone']) ?>"><?= htmlspecialchars($current['passenger_phone']) ?></a></dd>
                        </dl>
                        <div class="mt-3">
                            <?php if ($current['status'] === 'requested'): ?>
                                <a href="?accept=<?= $current['id'] ?>" class="btn btn-success fw-bold">Accept Ride</a>
                                <a href="?reject=<?= $current['id'] ?>" class="btn btn-danger fw-bold ms-2" onclick="return confirm('Reject ride #<?= $current['id'] ?>?')">Reject Ride</a>
                            <?php elseif ($current['status'] === 'accepted'): ?>
                                <a href="?complete=<?= $current['id'] ?>" class="btn btn-dark fw-bold">Complete Ride</a>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <p>No active ride assigned.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Available unassigned rides -->
            <div class="card shadow-sm mb-4 border-info">
                <div class="card-header bg-info text-white fw-bold">Available Ride Requests</div>
                <div class="card-body">
                    <?php if ($availableStmt->rowCount() > 0): ?>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Pickup</th>
                                    <th>Dropoff</th>
                                    <th>Fare</th>
                                    <th>Passenger</th>
                                    <th>Phone</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($availableStmt as $ride): ?>
                                    <tr>
                                        <td><?= $ride['id'] ?></td>
                                        <td><?= htmlspecialchars($ride['pickup_location']) ?></td>
                                        <td><?= htmlspecialchars($ride['dropoff_location']) ?></td>
                                        <td>Rs. <?= number_format($ride['fare'], 2) ?></td>
                                        <td><?= htmlspecialchars($ride['passenger_name']) ?></td>
                                        <td><a href="tel:<?= htmlspecialchars($ride['passenger_phone']) ?>"><?= htmlspecialchars($ride['passenger_phone']) ?></a></td>
                                        <td><a href="?accept=<?= $ride['id'] ?>" class="btn btn-success btn-sm">Accept</a></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No available ride requests at the moment.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Ride history -->
            <div class="card shadow-sm border-warning">
                <div class="card-header bg-warning text-dark fw-bold">Ride History</div>
                <div class="card-body">
                    <?php if ($history->rowCount()): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="table-warning">
                                    <tr>
                                        <th>ID</th><th>Pickup</th><th>Dropoff</th><th>Fare</th><th>Completed</th><th>Rating</th><th>Review</th><th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($history as $ride): ?>
                                        <tr>
                                            <td><?= $ride['id'] ?></td>
                                            <td><?= htmlspecialchars($ride['pickup_location']) ?></td>
                                            <td><?= htmlspecialchars($ride['dropoff_location']) ?></td>
                                            <td>Rs. <?= number_format($ride['fare'], 2) ?></td>
                                            <td><?= $ride['completed_at'] ?></td>
                                            <td><?= $ride['rating'] ?? 'N/A' ?></td>
                                            <td><?= htmlspecialchars($ride['review'] ?? '') ?></td>
                                            <td><a href="?delete=<?= $ride['id'] ?>" class="text-danger fw-bold" onclick="return confirm('Hide ride #<?= $ride['id'] ?>?')">✖</a></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p>No completed rides yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-warning">
                <div class="card-header bg-warning text-dark fw-bold">Summary</div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between"><strong>Total Earnings:</strong><span>Rs. <?= number_format($total_earnings->fetchColumn(), 2) ?></span></li>
                        <li class="list-group-item d-flex justify-content-between"><strong>Avg. Rating:</strong><span><?= htmlspecialchars($avg_rating) ?></span></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
function sendLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(pos => {
            fetch('update_driver_location.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `latitude=${pos.coords.latitude}&longitude=${pos.coords.longitude}`
            }).catch(console.error);
        });
    }
}
setInterval(sendLocation, 10000); // Send every 10 seconds
sendLocation();
</script>

<?php include '../includes/footer.php'; ?>
