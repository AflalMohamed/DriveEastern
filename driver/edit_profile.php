<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'driver') {
    header('Location: login.php');
    exit;
}

$driver_id = $_SESSION['user_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    if (!$name || !$phone || !$email) {
        $message = "Name, phone, and email are required.";
    } elseif ($password && $password !== $password_confirm) {
        $message = "Passwords do not match.";
    } else {
        if ($password) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, email = ?, password_hash = ? WHERE id = ? AND role = 'driver'");
            $stmt->execute([$name, $phone, $email, $hash, $driver_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, email = ? WHERE id = ? AND role = 'driver'");
            $stmt->execute([$name, $phone, $email, $driver_id]);
        }

        $_SESSION['name'] = $name;
        $_SESSION['message'] = "Profile updated successfully.";
        header('Location: driver_dashboard.php');
        exit;
    }
}

$stmt = $pdo->prepare("SELECT name, phone, email FROM users WHERE id = ? AND role = 'driver'");
$stmt->execute([$driver_id]);
$driver = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$driver) {
    die("Driver not found.");
}
?>

<?php include '../includes/header.php'; ?>

<style>
  main.edit-profile {
    max-width: 500px;
    margin: 40px auto 80px;
    background: #fff;
    border-radius: 12px;
    padding: 30px 35px;
    box-shadow: 0 4px 14px rgb(0 0 0 / 0.1);
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  }
  main.edit-profile h2 {
    margin-bottom: 25px;
    color: #f4b400; /* Yellow color */
    font-weight: 700;
  }
  form label {
    display: block;
    margin-top: 15px;
    font-weight: 600;
    color: #333;
  }
  form input[type="text"],
  form input[type="email"],
  form input[type="tel"],
  form input[type="password"] {
    width: 100%;
    padding: 10px 12px;
    margin-top: 6px;
    border: 1.5px solid #ccc;
    border-radius: 7px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
  }
  form input[type="text"]:focus,
  form input[type="email"]:focus,
  form input[type="tel"]:focus,
  form input[type="password"]:focus {
    border-color: #f4b400;
    outline: none;
  }
  .btn-submit {
    margin-top: 30px;
    background-color: #f4b400;
    color: white;
    border: none;
    padding: 12px 28px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.3s ease;
  }
  .btn-submit:hover {
    background-color: #d39e00;
  }
  .message {
    margin-bottom: 20px;
    padding: 15px 20px;
    border-left: 5px solid #28a745;
    background: #d4edda;
    color: #155724;
    border-radius: 5px;
  }
  .error {
    margin-bottom: 20px;
    padding: 15px 20px;
    border-left: 5px solid #dc3545;
    background: #f8d7da;
    color: #721c24;
    border-radius: 5px;
  }
  a.back-link {
    display: inline-block;
    margin-top: 25px;
    color: #f4b400;
    font-weight: 600;
    text-decoration: none;
  }
  a.back-link:hover {
    text-decoration: underline;
  }
</style>

<main class="edit-profile">
  <h2>Edit Profile</h2>

  <?php if ($message): ?>
    <div class="<?= strpos($message, 'success') !== false ? 'message' : 'error' ?>">
      <?= htmlspecialchars($message) ?>
    </div>
  <?php endif; ?>

  <form method="post" action="">
    <label for="name">Name</label>
    <input type="text" id="name" name="name" required value="<?= htmlspecialchars($driver['name']) ?>">

    <label for="phone">Phone</label>
    <input type="tel" id="phone" name="phone" required value="<?= htmlspecialchars($driver['phone']) ?>">

    <label for="email">Email</label>
    <input type="email" id="email" name="email" required value="<?= htmlspecialchars($driver['email']) ?>">

    <label for="password">New Password <small>(leave blank to keep current)</small></label>
    <input type="password" id="password" name="password" autocomplete="new-password">

    <label for="password_confirm">Confirm New Password</label>
    <input type="password" id="password_confirm" name="password_confirm" autocomplete="new-password">

    <button type="submit" class="btn-submit">Save Changes</button>
  </form>

  <a href="driver_dashboard.php" class="back-link">&larr; Back to Dashboard</a>
</main>

<?php include '../includes/footer.php'; ?>
