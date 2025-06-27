<?php
require_once '../db.php';
header('Content-Type: application/json');

try {
    $stmt = $pdo->query("
        SELECT ds.driver_id AS id, u.name, ds.latitude, ds.longitude
        FROM driver_status ds
        JOIN users u ON u.id = ds.driver_id
        WHERE u.role = 'driver' AND ds.latitude IS NOT NULL AND ds.longitude IS NOT NULL
    ");
    $drivers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'drivers' => $drivers]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
