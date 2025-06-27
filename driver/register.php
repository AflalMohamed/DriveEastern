<?php
session_start();
require '../config/db.php';

if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'driver') {
    header('Location: driver_dashboard.php');
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($name === '' || $email === '' || $password === '' || $confirm_password === '') {
        $message = 'Please fill all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Invalid email format.';
    } elseif ($password !== $confirm_password) {
        $message = 'Passwords do not match.';
    } else {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $message = 'Email already registered.';
        } else {
            // Insert new driver user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'driver')");
            $stmt->execute([$name, $email, $hashed_password]);

            // Get new driver id
            $driver_id = $pdo->lastInsertId();

            // Set driver status as available by default
            $stmt = $pdo->prepare("INSERT INTO driver_status (driver_id, is_available) VALUES (?, 1)");
            $stmt->execute([$driver_id]);

            $_SESSION['message'] = 'Registration successful. You can now login.';
            header('Location: login.php');
            exit;
        }
    }
}
?>

<?php include '../includes/header.php'; ?>

<h1>Driver Registration</h1>

<?php if ($message): ?>
    <div style="color:red;"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<form method="POST" style="max-width:400px;">
    <div>
        <label>Name:</label><br />
        <input type="text" name="name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" style="width:100%; padding:8px;" />
    </div>
    <div style="margin-top:10px;">
        <label>Email:</label><br />
        <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" style="width:100%; padding:8px;" />
    </div>
    <div style="margin-top:10px;">
        <label>Password:</label><br />
        <input type="password" name="password" required style="width:100%; padding:8px;" />
    </div>
    <div style="margin-top:10px;">
        <label>Confirm Password:</label><br />
        <input type="password" name="confirm_password" required style="width:100%; padding:8px;" />
    </div>
    <div style="margin-top:15px;">
        <button type="submit" style="padding:10px 20px;">Register</button>
    </div>
</form>

<?php include '../includes/footer.php'; ?>
