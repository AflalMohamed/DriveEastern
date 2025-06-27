<?php
session_start();

// No DB needed unless you want to add admin data in the future
// require '../config/db.php'; // You can comment this out for now if not used

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Hardcoded admin credentials
    if ($username === 'admin@gmail.com' && $password === '123') {
        $_SESSION['admin'] = true;

        // ✅ Adjust this path to your correct dashboard file
        header('Location: /admin/dashboard.php');
        exit;
    } else {
        $error = "Invalid credentials.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
</head>
<body>
    <h2>Admin Login</h2>
    <form method="POST">
        <label>Username:
            <input type="text" name="username" required>
        </label><br><br>
        <label>Password:
            <input type="password" name="password" required>
        </label><br><br>
        <button type="submit">Login</button>
    </form>
    <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
</body>
</html>
