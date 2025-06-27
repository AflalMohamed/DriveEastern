<?php
session_start();
require 'db.php';

if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

$message = '';

// Fetch current fare settings
$stmt = $pdo->query("SELECT * FROM fare_settings LIMIT 1");
$settings = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $base_fare = floatval($_POST['base_fare'] ?? 0);
    $per_km_rate = floatval($_POST['per_km_rate'] ?? 0);

    if ($base_fare <= 0 || $per_km_rate <= 0) {
        $message = "Please enter valid positive values.";
    } else {
        if ($settings) {
            $stmt = $pdo->prepare("UPDATE fare_settings SET base_fare = ?, per_km_rate = ? WHERE id = ?");
            $stmt->execute([$base_fare, $per_km_rate, $settings['id']]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO fare_settings (base_fare, per_km_rate) VALUES (?, ?)");
            $stmt->execute([$base_fare, $per_km_rate]);
        }
        $message = "Fare settings updated successfully.";
        // Refresh settings
        $stmt = $pdo->query("SELECT * FROM fare_settings LIMIT 1");
        $settings = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>

<h1>Fare Settings</h1>
<a href="dashboard.php">← Back to Dashboard</a>

<?php if ($message): ?>
    <div style="padding:10px; background:#d4edda; color:#155724; border-radius:5px; margin-top:10px;"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<form method="POST" style="max-width:400px; margin-top:15px;">
    <div>
        <label for="base_fare">Base Fare (R):</label><br />
        <input type="number" step="0.01" id="base_fare" name="base_fare" required value="<?= htmlspecialchars($settings['base_fare'] ?? '100.00') ?>" style="width:100%; padding:8px;" />
    </div>
    <div style="margin-top:10px;">
        <label for="per_km_rate">Per Kilometer Rate (R):</label><br />
        <input type="number" step="0.01" id="per_km_rate" name="per_km_rate" required value="<?= htmlspecialchars($settings['per_km_rate'] ?? '10.00') ?>" style="width:100%; padding:8px;" />
    </div>
    <div style="margin-top:15px;">
        <button type="submit" style="padding:10px 20px; background:#007bff; color:white; border:none; border-radius:5px;">Update Settings</button>
    </div>
</form>
