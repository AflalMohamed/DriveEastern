<?php
session_start();
require '../config/db.php';

if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'driver') {
    header('Location: driver_dashboard.php');
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $message = 'Please fill all fields.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'driver' LIMIT 1");
        $stmt->execute([$email]);
        $driver = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($driver && password_verify($password, $driver['password'])) {
            $_SESSION['user_id'] = $driver['id'];
            $_SESSION['name'] = $driver['name'];
            $_SESSION['role'] = 'driver';
            header('Location: driver_dashboard.php');
            exit;
        } else {
            $message = 'Invalid email or password.';
        }
    }
}
?>

<?php include '../includes/header.php'; ?>

<h1>Driver Login</h1>

<?php if ($message): ?>
    <div style="color:red;"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<form method="POST" style="max-width:300px;">
    <div>
        <label>Email:</label><br />
        <input type="email" name="email" required style="width:100%; padding:8px;" />
    </div>
    <div style="margin-top:10px;">
        <label>Password:</label><br />
        <input type="password" name="password" required style="width:100%; padding:8px;" />
    </div>
    <div style="margin-top:15px;">
        <button type="submit" style="padding:10px 20px;">Login</button>
    </div>
</form>

<?php include '../includes/footer.php'; ?>
