<?php
session_start();
require 'db.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['action'])) {
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

function register($pdo, $name, $email, $password, $role) {
    // Check existing email
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) return ['error' => 'Email already registered'];

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $email, $hash, $role]);
    $user_id = $pdo->lastInsertId();

    // If driver, set availability
    if ($role === 'driver') {
        $stmt = $pdo->prepare("INSERT INTO driver_status (driver_id, is_available) VALUES (?, 1)");
        $stmt->execute([$user_id]);
    }
    return ['success' => true, 'message' => "Registered as $role"];
}

function login($pdo, $email, $password) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['name'] = $user['name'];
        return ['success' => true, 'role' => $user['role'], 'name' => $user['name']];
    }
    return ['error' => 'Invalid email or password'];
}

switch ($input['action']) {
    case 'register':
        $name = trim($input['name'] ?? '');
        $email = trim($input['email'] ?? '');
        $password = $input['password'] ?? '';
        $role = $input['role'] ?? 'passenger';

        if (!$name || !$email || !$password || !in_array($role, ['passenger', 'driver'])) {
            echo json_encode(['error' => 'Missing or invalid fields']);
            exit;
        }

        echo json_encode(register($pdo, $name, $email, $password, $role));
        break;

    case 'login':
        $email = trim($input['email'] ?? '');
        $password = $input['password'] ?? '';
        if (!$email || !$password) {
            echo json_encode(['error' => 'Missing fields']);
            exit;
        }
        echo json_encode(login($pdo, $email, $password));
        break;

    case 'logout':
        session_destroy();
        echo json_encode(['success' => true]);
        break;

    default:
        echo json_encode(['error' => 'Unknown action']);
}
