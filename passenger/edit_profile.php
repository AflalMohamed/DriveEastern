<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'passenger') {
    header('Location: login.php');
    exit;
}

$passenger_id = $_SESSION['user_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);

    if ($name === '' || $email === '' || $phone === '') {
        $message = 'All fields are required.';
    } else {
        $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?");
        $stmt->execute([$name, $email, $phone, $passenger_id]);

        $_SESSION['name'] = $name;
        $message = 'Profile updated successfully.';
    }
}

$stmt = $pdo->prepare("SELECT name, email, phone FROM users WHERE id = ?");
$stmt->execute([$passenger_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<?php include '../includes/header.php'; ?>

<main style="max-width: 600px; margin: 2rem auto; background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
  <h2>Edit Profile</h2>

  <?php if ($message): ?>
    <div style="padding: 1rem; margin-bottom: 1rem; background: #e6f4ea; border: 1px solid #2e7d32; color: #2e7d32; border-radius: 6px;">
      <?= htmlspecialchars($message) ?>
    </div>
  <?php endif; ?>

  <form method="POST" style="display: flex; flex-direction: column; gap: 1rem;">
    <label>
      Name
      <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid #ccc;">
    </label>

    <label>
      Email
      <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid #ccc;">
    </label>

    <label>
      Phone
      <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" required style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid #ccc;">
    </label>

    <button type="submit" style="background: #007bff; color: white; padding: 0.6rem 1.2rem; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;">
      Save Changes
    </button>

    <a href="driver_dashboard.php" style="margin-top: 0.5rem; display: inline-block; text-decoration: none; color: #555;">← Back to Dashboard</a>
  </form>
</main>

<?php include '../includes/footer.php'; ?>
