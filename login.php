<?php
session_start();
require 'db.php'; // Your PDO connection script

$error = '';

// Hardcoded admin credentials
$adminEmail = 'admin@gmail.com';
$adminPassword = '123'; // set your admin password here

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $error = 'Please enter email and password.';
    } else {
        // Check if admin login (hardcoded)
        if ($email === $adminEmail && $password === $adminPassword) {
            // Set admin session data
            $_SESSION['user_id'] = 0; // or some dummy id for admin
            $_SESSION['role'] = 'admin';
            $_SESSION['name'] = 'Administrator';

            header('Location: admin/dashboard.php');
            exit;
        }

        // Otherwise, check database for normal users
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];

            if ($user['role'] === 'passenger') {
                header('Location: passenger/passenger_dashboard.php');
                exit;
            } elseif ($user['role'] === 'driver') {
                header('Location: driver/driver_dashboard.php');
                exit;
            } else {
                $error = 'Unknown user role.';
            }
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Login - Drive Eastern</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet" />
  <script src="https://cdn.lordicon.com/lordicon.js"></script>

  <style>
    :root {
      --primary-yellow: #ffcc00;
      --dark-yellow: #e6b800;
      --black: #222;
      --gray: #666;
      --white: #fff;
      --bg: linear-gradient(120deg, #fefcea 0%, #f1da36 100%);
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: 'Inter', sans-serif;
    }

    body {
      background: var(--bg);
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 2rem;
      position: relative;
    }

    body::before {
      content: "";
      position: absolute;
      top: 0;
      left: 0;
      background: url('https://cdn.pixabay.com/photo/2017/10/30/17/54/taxi-2902942_1280.png') no-repeat center center;
      background-size: contain;
      opacity: 0.07;
      width: 100%;
      height: 100%;
      z-index: 0;
    }

    .container {
      background: var(--white);
      padding: 3rem 2rem;
      border-radius: 18px;
      max-width: 400px;
      width: 100%;
      z-index: 1;
      box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
      position: relative;
    }

    .icon-box {
      text-align: center;
      margin-bottom: 1.5rem;
    }

    .icon-box lord-icon {
      width: 90px;
      height: 90px;
    }

    h1 {
      font-weight: 800;
      font-size: 2.2rem;
      color: var(--black);
      text-align: center;
      margin-bottom: 1rem;
    }

    .error {
      background: #ffe3e3;
      color: #b00020;
      padding: 0.8rem;
      border-radius: 10px;
      text-align: center;
      margin-bottom: 1.2rem;
    }

    form {
      display: flex;
      flex-direction: column;
    }

    input[type="email"],
    input[type="password"] {
      padding: 0.9rem 1.1rem;
      margin-bottom: 1.1rem;
      font-size: 1rem;
      border-radius: 12px;
      border: 2px solid #ddd;
      transition: 0.3s;
    }

    input:focus {
      border-color: var(--primary-yellow);
      box-shadow: 0 0 8px rgba(255, 204, 0, 0.4);
      outline: none;
    }

    button {
      padding: 0.9rem;
      background: var(--primary-yellow);
      font-weight: bold;
      font-size: 1rem;
      border: none;
      border-radius: 12px;
      cursor: pointer;
      transition: background 0.3s ease;
    }

    button:hover {
      background: var(--dark-yellow);
    }

    p.register-link {
      text-align: center;
      margin-top: 1.5rem;
      font-size: 0.95rem;
    }

    p.register-link a {
      color: var(--dark-yellow);
      text-decoration: none;
      font-weight: 600;
    }

    p.register-link a:hover {
      text-decoration: underline;
    }

    @media (max-width: 480px) {
      .container {
        padding: 2rem 1.5rem;
      }
    }
  </style>
</head>
<body>

<div class="container">
  <div class="icon-box">
    <lord-icon
      src="https://cdn.lordicon.com/tdrtiskw.json"
      trigger="loop"
      delay="1000"
      colors="primary:#ffcc00,secondary:#121331"
      style="width:90px;height:90px">
    </lord-icon>
  </div>

  <h1>Drive Eastern Login</h1>

  <?php if ($error): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST" action="login.php" novalidate>
    <input
      type="email"
      name="email"
      placeholder="Email address"
      required
      value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
    />
    <input
      type="password"
      name="password"
      placeholder="Password"
      required
    />
    <button type="submit">Login</button>
  </form>

  <p class="register-link">
    New to Drive Eastern? <a href="register.php">Create account</a>
  </p>
</div>

</body>
</html>
