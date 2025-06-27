<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Detect if user is logged in and role
$logged_in = isset($_SESSION['user_id']);
$role = $_SESSION['role'] ?? '';
$name = $_SESSION['name'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Drive Eastern — Reliable Taxi Services</title>
  <meta name="description" content="Drive Eastern - Your trusted ride partner in the Eastern region offering safe, fast, and reliable taxi services." />
  <link rel="icon" href="favicon.ico" />
  <style>
    :root {
      --color-primary: #f5a623; /* Taxi yellow */
      --color-primary-dark: #d48806;
      --color-text-dark: #1a1a1a;
      --color-bg-light: #fffdf3;
      --color-bg-muted: #f9f7f1;
      --color-text-muted: #555555;
      --font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      --transition-speed: 0.3s;
      --border-radius: 6px;
      --shadow-light: rgba(0, 0, 0, 0.1);
      --shadow-hover: rgba(0, 0, 0, 0.15);
      --focus-outline: 2px solid var(--color-primary-dark);
    }

    /* Reset */
    *, *::before, *::after {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      padding: 0;
      font-family: var(--font-family);
      background-color: var(--color-bg-light);
      color: var(--color-text-dark);
      line-height: 1.6;
      -webkit-font-smoothing: antialiased;
      -moz-osx-font-smoothing: grayscale;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    a {
      color: var(--color-primary);
      text-decoration: none;
      font-weight: 600;
      transition: color var(--transition-speed);
    }
    a:hover,
    a:focus-visible {
      color: var(--color-primary-dark);
      outline: none;
      text-decoration: underline;
    }

    a:focus-visible {
      outline: var(--focus-outline);
      outline-offset: 2px;
    }

    nav {
      background-color: var(--color-primary);
      color: #fff;
      padding: 1rem 2rem;
      box-shadow: 0 3px 8px var(--shadow-light);
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      position: sticky;
      top: 0;
      z-index: 999;
      user-select: none;
    }

    .nav-left,
    .nav-right {
      display: flex;
      align-items: center;
      gap: 1.25rem;
      flex-wrap: wrap;
    }

    .nav-left a,
    .nav-right a,
    .nav-right span {
      color: #fff;
      font-weight: 600;
      font-size: 1rem;
      user-select: text;
    }

    .nav-left a:first-child {
      font-size: 1.3rem;
      font-weight: 700;
      letter-spacing: 0.05em;
    }

    .nav-right span {
      cursor: default;
    }

    /* Main content styling */
    main.main-content {
      max-width: 900px;
      margin: 3rem auto 4rem;
      padding: 0 1rem;
      text-align: center;
      flex-grow: 1;
    }

    h1 {
      font-size: 2.75rem;
      font-weight: 800;
      margin-bottom: 0.5rem;
      color: var(--color-primary-dark);
      line-height: 1.1;
    }

    p {
      font-size: 1.15rem;
      color: var(--color-text-muted);
      max-width: 600px;
      margin: 0 auto;
      line-height: 1.5;
    }

    footer {
      background-color: var(--color-bg-muted);
      text-align: center;
      padding: 1rem 1rem;
      font-size: 0.9rem;
      color: var(--color-text-muted);
      border-top: 1px solid #e0dcbc;
    }

    /* Responsive adjustments */
    @media (max-width: 640px) {
      nav {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.75rem;
      }

      .nav-left,
      .nav-right {
        width: 100%;
        justify-content: flex-start;
      }

      main.main-content {
        margin: 2rem 1rem 3rem;
      }
    }
  </style>
</head>
<body>

<nav role="navigation" aria-label="Primary Navigation">
  <div class="nav-left">
    <a href="#" aria-label="Drive Eastern homepage" title="Drive Eastern Homepage">🚕 Drive Eastern</a>
    <a href="../about.php" title="About Drive Eastern">About Us</a>
    <a href="../Help.php" title="Help and Support">Help</a>
  </div>
  <div class="nav-right">
    <?php if ($logged_in): ?>
      <span aria-live="polite" title="User Greeting">Hello, <?=htmlspecialchars($name)?>!</span>
      <?php if ($role === 'passenger'): ?>
        <a href="../passenger/passenger_dashboard.php" title="Passenger Dashboard">Dashboard</a>
      <?php elseif ($role === 'driver'): ?>
        <a href="../driver/driver_dashboard.php" title="Driver Dashboard">Dashboard</a>
      <?php endif; ?>
      <a href="../logout.php" role="link" title="Logout">Logout</a>
    <?php else: ?>
      <a href="../login.php" role="link" title="Login">Login</a>
      <a href="../register.php" role="link" title="Register">Register</a>
    <?php endif; ?>
  </div>
</nav>

<main class="main-content" role="main">
  <h1>Welcome to Drive Eastern</h1>
  <p>Your trusted ride partner across the Eastern region. Safe, fast, and reliable taxi services at your fingertips.</p>
</main>

</body>
</html>
