<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

// Handle toggle availability action
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $driver_id = (int)$_GET['toggle'];

    // Get current availability
    $stmt = $pdo->prepare("SELECT is_available FROM driver_status WHERE driver_id = ?");
    $stmt->execute([$driver_id]);
    $status = $stmt->fetchColumn();

    if ($status !== false) {
        $newStatus = $status ? 0 : 1;
        $stmt = $pdo->prepare("UPDATE driver_status SET is_available = ? WHERE driver_id = ?");
        $stmt->execute([$newStatus, $driver_id]);
    }

    header('Location: manage_drivers.php');
    exit;
}

// Fetch all drivers and their status
$sql = "
    SELECT u.id, u.name, u.email, u.phone, ds.is_available
    FROM users u
    LEFT JOIN driver_status ds ON u.id = ds.driver_id
    WHERE u.role = 'driver'
    ORDER BY u.name
";
$stmt = $pdo->query($sql);
$drivers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h1>Manage Drivers</h1>
<a href="dashboard.php">← Back to Dashboard</a>

<table border="1" cellpadding="8" cellspacing="0" style="border-collapse: collapse; width:100%; max-width:900px; margin-top:15px;">
    <thead>
        <tr style="background:#f2f2f2;">
            <th>Driver ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Availability</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
    <?php if ($drivers): ?>
        <?php foreach ($drivers as $driver): ?>
            <tr>
                <td><?= htmlspecialchars($driver['id']) ?></td>
                <td><?= htmlspecialchars($driver['name']) ?></td>
                <td><?= htmlspecialchars($driver['email']) ?></td>
                <td><?= htmlspecialchars($driver['phone']) ?></td>
                <td><?= isset($driver['is_available']) && $driver['is_available'] ? 'Available' : 'Unavailable' ?></td>
                <td>
                    <a href="?toggle=<?= $driver['id'] ?>">
                        <?= (isset($driver['is_available']) && $driver['is_available']) ? 'Set Unavailable' : 'Set Available' ?>
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr><td colspan="6">No drivers found.</td></tr>
    <?php endif; ?>
    </tbody>
</table>
