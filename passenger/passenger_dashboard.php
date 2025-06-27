<?php
/****************************************************************************************
 *  passenger_dashboard.php  —  Drive‑Eastern                                           *
 *  Complete, ready‑to‑run file (PHP + HTML + JS) with live map tracking.              *
 ****************************************************************************************/

session_start();
require '../db.php';

// ────────────────────────── Authorise passenger ──────────────────────────
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'passenger') {
    header('Location: login.php');
    exit;
}

$passenger_id = $_SESSION['user_id'];
$name         = $_SESSION['name']     ?? 'Passenger';
$message      = $_SESSION['message']  ?? '';
unset($_SESSION['message']);

// Fare settings
try {
    $fare_per_km  = (float)$pdo->query("SELECT price FROM fare_price WHERE id = 1")->fetchColumn();
    $minimum_fare = 100.0;
} catch (PDOException $e) {
    $fare_per_km  = 60.0;
    $minimum_fare = 100.0;
}

function calcFare(float $km, float $perKm, float $min): float {
    return max($min, round($km * $perKm));
}

// Cancel ride
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_ride_id'])) {
    $rideId = (int)$_POST['cancel_ride_id'];
    $q = $pdo->prepare("SELECT driver_id FROM rides WHERE id = ? AND passenger_id = ? AND status IN ('requested','accepted')");
    $q->execute([$rideId, $passenger_id]);
    if ($row = $q->fetch()) {
        $pdo->prepare("UPDATE rides SET status = 'cancelled' WHERE id = ?")->execute([$rideId]);
        if ($row['driver_id']) {
            $pdo->prepare("UPDATE driver_status SET is_available = 1 WHERE driver_id = ?")->execute([$row['driver_id']]);
        }
        $message = 'Your ride has been cancelled.';
    } else {
        $message = 'Unable to cancel ride. Ride not found or already completed.';
    }
}

// New ride request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pickup_location'], $_POST['dropoff_location'], $_POST['distance']) && !isset($_POST['cancel_ride_id'])) {
    $pickup   = trim(filter_var($_POST['pickup_location'], FILTER_SANITIZE_STRING));
    $dropoff  = trim(filter_var($_POST['dropoff_location'], FILTER_SANITIZE_STRING));
    $distance = (float)$_POST['distance'];

    if ($pickup === '' || $dropoff === '' || $distance <= 0) {
        $message = 'Please fill all fields and enter a valid distance.';
    } else {
        $dupe = $pdo->prepare("SELECT 1 FROM rides WHERE passenger_id = ? AND status IN ('requested','accepted') LIMIT 1");
        $dupe->execute([$passenger_id]);
        if ($dupe->fetch()) {
            $message = 'You already have an active ride.';
        } else {
            $fare = calcFare($distance, $fare_per_km, $minimum_fare);
            $ins = $pdo->prepare("
                INSERT INTO rides (passenger_id, driver_id, pickup_location, dropoff_location, fare, status, created_at)
                VALUES (?, NULL, ?, ?, ?, 'requested', NOW())
            ");
            $ins->execute([$passenger_id, $pickup, $dropoff, $fare]);
            $ride_id = $pdo->lastInsertId();

            $note = "New ride request from $pickup to $dropoff. Fare: Rs.$fare. Ride ID: $ride_id";
            $notify = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
            foreach ($pdo->query("SELECT driver_id FROM driver_status WHERE is_available = 1") as $row) {
                $notify->execute([$row['driver_id'], $note]);
            }

            $message = "Ride requested! Fare: Rs.$fare. Waiting for driver to accept.";
        }
    }
}

// Rating
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rate_ride_id'], $_POST['rating']) && !isset($_POST['cancel_ride_id'])) {
    $rideId = (int)$_POST['rate_ride_id'];
    $rating = (int)$_POST['rating'];
    $review = trim(filter_var($_POST['review'] ?? '', FILTER_SANITIZE_STRING));
    if ($rating >= 1 && $rating <= 5) {
        $pdo->prepare("UPDATE rides SET rating = ?, review = ? WHERE id = ? AND passenger_id = ? AND status = 'completed'")
            ->execute([$rating, $review, $rideId, $passenger_id]);
        $message = 'Thank you for your feedback!';
    } else {
        $message = 'Please provide a valid rating between 1 and 5.';
    }
}

// Data for view
$current = $pdo->prepare("
    SELECT r.*, u.name AS driver_name, r.driver_id
    FROM rides r
    LEFT JOIN users u ON u.id = r.driver_id
    WHERE r.passenger_id = ? AND r.status IN ('requested','accepted')
    ORDER BY r.created_at DESC
    LIMIT 1
");
$current->execute([$passenger_id]);
$currentRide = $current->fetch(PDO::FETCH_ASSOC);

$history = $pdo->prepare("SELECT * FROM rides WHERE passenger_id = ? ORDER BY created_at DESC LIMIT 5");
$history->execute([$passenger_id]);
$rides = $history->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<link href="https://unpkg.com/leaflet/dist/leaflet.css" rel="stylesheet" />

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold">Welcome, <?= htmlspecialchars($name) ?>!</h3>
        <a href="manage_profile.php" class="btn btn-outline-dark">Manage Profile</a>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-info shadow-sm"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header bg-dark text-white">Book a New Ride</div>
        <div class="card-body">
            <form method="POST" novalidate>
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label" for="pickup_location">Pickup Location</label>
                        <input class="form-control" id="pickup_location" name="pickup_location" required />
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="dropoff_location">Dropoff Location</label>
                        <input class="form-control" id="dropoff_location" name="dropoff_location" required />
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" for="distance">Distance (km)</label>
                        <input class="form-control" type="number" step="0.1" min="0.1" id="distance" name="distance" required />
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary w-100" type="submit">Book Ride</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php if ($currentRide): ?>
    <div class="card shadow mb-4">
        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
            <span>Live Ride – Driver: <?= htmlspecialchars($currentRide['driver_name'] ?? 'Pending') ?></span>
            <?php if ($currentRide['status'] === 'requested' || $currentRide['status'] === 'accepted'): ?>
            <form class="mb-0" method="POST" onsubmit="return confirm('Cancel this ride?');">
                <input type="hidden" name="cancel_ride_id" value="<?= (int)$currentRide['id'] ?>" />
                <button class="btn btn-danger btn-sm" type="submit">Cancel Ride</button>
            </form>
            <?php endif; ?>
        </div>
        <div class="card-body p-0">
            <div id="live-ride-map" style="height: 450px;"></div>
        </div>
    </div>
    <?php endif; ?>

    <div class="card shadow mb-5">
        <div class="card-header bg-secondary text-white">Recent Ride History</div>
        <div class="card-body">
            <?php if ($rides): ?>
            <ul class="list-group">
                <?php foreach ($rides as $r): ?>
                <li class="list-group-item">
                    <strong><?= htmlspecialchars($r['pickup_location']) ?></strong> →
                    <strong><?= htmlspecialchars($r['dropoff_location']) ?></strong> –
                    Rs.<?= number_format($r['fare'], 2) ?> (<?= ucfirst($r['status']) ?>)
                    <br /><small class="text-muted">Date: <?= htmlspecialchars($r['created_at']) ?></small>
                    <?php if ($r['status'] === 'completed' && empty($r['rating'])): ?>
                    <form class="mt-2" method="POST" novalidate>
                        <input type="hidden" name="rate_ride_id" value="<?= (int)$r['id'] ?>" />
                        <div class="row g-2">
                            <div class="col-md-2">
                                <select class="form-select" name="rating" required>
                                    <option value="">Rate</option>
                                    <?php for ($i=1;$i<=5;$i++): ?><option value="<?= $i ?>"><?= $i ?></option><?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-6"><input class="form-control" name="review" placeholder="Optional review" /></div>
                            <div class="col-md-2"><button class="btn btn-outline-primary" type="submit">Submit</button></div>
                        </div>
                    </form>
                    <?php elseif (!empty($r['rating'])): ?>
                    <div class="text-muted mt-1">
                        Rated: <?= (int)$r['rating'] ?>/5<?= $r['review'] ? ' – '.htmlspecialchars($r['review']) : '' ?>
                    </div>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php else: ?>
            <p class="text-muted">No ride history yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<?php if ($currentRide): ?>
<script>
const map = L.map('live-ride-map').setView([6.9271, 79.8612], 12);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

const driverIcon = L.icon({
    iconUrl: 'https://cdn-icons-png.flaticon.com/512/194/194535.png',
    iconSize: [32, 32],
    iconAnchor: [16, 32]
});
const passengerIcon = L.icon({
    iconUrl: 'https://cdn-icons-png.flaticon.com/512/1077/1077114.png',
    iconSize: [28, 28],
    iconAnchor: [14, 28]
});

let driverMarker, passengerMarker;
const rideId = <?= (int)$currentRide['id'] ?>;
const driverId = <?= (int)$currentRide['driver_id'] ?>;

async function fetchDriverLocation() {
    try {
        const res = await fetch(`../api/driver_location_api.php?driver_id=${driverId}`);
        const data = await res.json();
        if (data.success && data.latitude && data.longitude) {
            const pos = [data.latitude, data.longitude];
            if (!driverMarker) {
                driverMarker = L.marker(pos, { icon: driverIcon }).addTo(map).bindPopup('Driver');
            } else {
                driverMarker.setLatLng(pos);
            }
        }
    } catch (e) {
        console.error('Driver location error', e);
    }
}

function updatePassengerLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(pos => {
            const location = [pos.coords.latitude, pos.coords.longitude];
            if (!passengerMarker) {
                passengerMarker = L.marker(location, { icon: passengerIcon }).addTo(map).bindPopup('You');
            } else {
                passengerMarker.setLatLng(location);
            }
        });
    }
}

function adjustBounds() {
    const group = L.featureGroup([driverMarker, passengerMarker].filter(Boolean));
    if (group.getLayers().length > 0) {
        map.fitBounds(group.getBounds().pad(0.2));
    }
}

function updateMap() {
    fetchDriverLocation().then(() => {
        updatePassengerLocation();
        adjustBounds();
    });
}

updateMap();
setInterval(updateMap, 10000);
</script>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
