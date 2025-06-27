<?php
session_start();
require 'db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? '';

    // Basic validation
    if (!$name || !$email || !$phone || !$password || !$confirm_password || !$role) {
        $error = 'Please fill all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (!in_array($role, ['passenger', 'driver'])) {
        $error = 'Invalid user role selected.';
    } else {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email already registered.';
        } else {
            // Hash password
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            // Insert user with phone and created_at = NOW()
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role, phone, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$name, $email, $password_hash, $role, $phone]);

            // If driver, add to driver_status as available by default
            if ($role === 'driver') {
                $driver_id = $pdo->lastInsertId();
                $stmt2 = $pdo->prepare("INSERT INTO driver_status (driver_id, is_available) VALUES (?, 1)");
                $stmt2->execute([$driver_id]);
            }

            $success = 'Registration successful! You can now <a href="login.php">login</a>.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Register - Drive Eastern</title>

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet" />

  <style>
    :root {
      --primary-yellow: #ffcc00;
      --primary-yellow-dark: #e6b800;
      --primary-black: #1a1a1a;
      --primary-gray: #4a4a4a;
      --background: #ffffff;
      --card-bg: #f9f9f9;
      --shadow-light: rgba(0, 0, 0, 0.05);
      --shadow-hover: rgba(0, 0, 0, 0.15);
      --border-radius: 14px;
      --font-family: 'Inter', sans-serif;
      --transition: 0.3s ease;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: var(--font-family);
    }

    body {
      background: var(--background);
      color: var(--primary-black);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      padding: 3rem 1.5rem;
    }

    h1 {
      font-weight: 800;
      font-size: 2.5rem;
      margin-bottom: 1.5rem;
      color: var(--primary-black);
    }

    form {
      background: var(--card-bg);
      padding: 2.5rem 3rem;
      border-radius: var(--border-radius);
      box-shadow: 0 3px 15px var(--shadow-light);
      width: 100%;
      max-width: 420px;
      box-sizing: border-box;
      transition: box-shadow var(--transition);
    }
    form:hover {
      box-shadow: 0 6px 30px var(--shadow-hover);
    }

    input[type="text"],
    input[type="email"],
    input[type="password"],
    select {
      width: 100%;
      padding: 0.9rem 1rem;
      margin-bottom: 1.2rem;
      border: 2px solid #ddd;
      border-radius: var(--border-radius);
      font-size: 1rem;
      transition: border-color var(--transition);
    }
    input[type="text"]:focus,
    input[type="email"]:focus,
    input[type="password"]:focus,
    select:focus {
      border-color: var(--primary-yellow);
      outline: none;
    }

    button[type="submit"] {
      background-color: var(--primary-yellow);
      border: none;
      color: var(--primary-black);
      font-weight: 700;
      font-size: 1.2rem;
      padding: 1rem 0;
      width: 100%;
      border-radius: var(--border-radius);
      cursor: pointer;
      box-shadow: 0 6px 14px rgba(255, 204, 0, 0.4);
      transition: background-color var(--transition), box-shadow var(--transition), transform var(--transition);
    }
    button[type="submit"]:hover,
    button[type="submit"]:focus-visible {
      background-color: var(--primary-yellow-dark);
      box-shadow: 0 8px 20px rgba(230, 184, 0, 0.5);
      transform: translateY(-3px);
      outline: none;
    }

    .error, .success {
      max-width: 420px;
      margin-bottom: 1.5rem;
      padding: 0.9rem 1rem;
      border-radius: var(--border-radius);
      font-weight: 600;
      font-size: 1rem;
      box-sizing: border-box;
      width: 100%;
      max-width: 420px;
      word-wrap: break-word;
    }
    .error {
      background: #ffe3e3;
      color: #cc0000;
      border: 1.5px solid #cc0000;
    }
    .success {
      background: #eaffd6;
      color: #5a7a00;
      border: 1.5px solid #5a7a00;
    }

    p.login-link {
      margin-top: 1.5rem;
      font-size: 1rem;
      color: var(--primary-gray);
    }
    p.login-link a {
      color: var(--primary-yellow);
      font-weight: 600;
      text-decoration: none;
      transition: color var(--transition);
    }
    p.login-link a:hover,
    p.login-link a:focus-visible {
      color: var(--primary-yellow-dark);
      outline: none;
      text-decoration: underline;
    }

    @media (max-width: 480px) {
      body {
        padding: 2rem 1rem;
      }
      form {
        padding: 2rem 1.5rem;
      }
    }
  </style>
</head>
<body>

  <h1>Register</h1>

  <?php if ($error): ?>
    <div class="error" role="alert"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <?php if ($success): ?>
    <div class="success" role="alert"><?= $success ?></div>
  <?php else: ?>

  <form method="POST" action="register.php" novalidate>
    <input type="text" name="name" placeholder="Full Name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" />
    <input type="email" name="email" placeholder="Email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" />
    <input type="text" name="phone" placeholder="Phone Number" required value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" />
    <input type="password" name="password" placeholder="Password" required />
    <input type="password" name="confirm_password" placeholder="Confirm Password" required />
    
    <select name="role" required aria-label="Select role">
      <option value="">-- Select Role --</option>
      <option value="passenger" <?= (($_POST['role'] ?? '') === 'passenger') ? 'selected' : '' ?>>Passenger</option>
      <option value="driver" <?= (($_POST['role'] ?? '') === 'driver') ? 'selected' : '' ?>>Driver</option>
    </select>

    <button type="submit">Register</button>
  </form>

  <p class="login-link">Already have an account? <a href="login.php">Login here</a>.</p>

  <?php endif; ?>

</body>
</html>
