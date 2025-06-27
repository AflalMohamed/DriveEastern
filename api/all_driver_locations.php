<?php
require '../db.php';
header('Content-Type: application/json');

$stmt = $pdo->query("SELECT ds.driver_id, ds.latitude, ds.longitude, u.name 
                     FROM driver_status ds 
                     JOIN users u ON ds.driver_id = u.id 
                     WHERE u.role = 'driver'");

$drivers = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $drivers[] = [
        'id' => $row['driver_id'],
        'name' => $row['name'],
        'lat' => (float)$row['latitude'],
        'lng' => (float)$row['longitude'],
    ];
}

echo json_encode(['success' => true, 'drivers' => $drivers]);
