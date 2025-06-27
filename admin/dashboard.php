<?php
session_start();

// Restrict access to admins only
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

require_once '../db.php'; // Provides $pdo

// Allowed tables for counts
$allowedTables = ['users', 'rides', 'reviews'];

// Function to get counts safely
function getCount(PDO $pdo, string $table): int {
    global $allowedTables;
    if (!in_array($table, $allowedTables)) {
        throw new InvalidArgumentException("Invalid table name");
    }
    $stmt = $pdo->query("SELECT COUNT(*) AS count FROM $table");
    $row = $stmt->fetch();
    return $row ? (int)$row['count'] : 0;
}

// Handle fare price update
$fareUpdateMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fare_price'])) {
    $newPrice = filter_var($_POST['fare_price'], FILTER_VALIDATE_FLOAT);
    if ($newPrice !== false && $newPrice >= 0) {
        $stmt = $pdo->prepare("UPDATE fare_price SET price = ? WHERE id = 1");
        if ($stmt->execute([$newPrice])) {
            $fareUpdateMsg = "Fare price updated successfully.";
        } else {
            $fareUpdateMsg = "Failed to update fare price.";
        }
    } else {
        $fareUpdateMsg = "Invalid fare price entered.";
    }
}

// Fetch counts
$totalUsers = getCount($pdo, 'users');
$totalRides = getCount($pdo, 'rides');
$totalReviews = getCount($pdo, 'reviews');

// Fetch current fare price
$stmt = $pdo->query("SELECT price FROM fare_price WHERE id = 1");
$farePrice = $stmt ? (float)$stmt->fetchColumn() : 0.00;

// Fetch recent activities
$recentActivities = [];
$stmt = $pdo->query("SELECT activity_desc AS descr, timestamp FROM activity_log ORDER BY timestamp DESC LIMIT 5");
if ($stmt) {
    while ($row = $stmt->fetch()) {
        $timestamp = strtotime($row['timestamp']);
        $timeDiff = time() - $timestamp;
        $minutes = floor($timeDiff / 60);
        $displayTime = $minutes < 1 ? "Just now" :
                      ($minutes < 60 ? "$minutes mins ago" :
                      (floor($minutes / 60) . ' hour(s) ago'));
        $recentActivities[] = [
            'desc' => $row['descr'],
            'time' => $displayTime,
            'datetime' => date('c', $timestamp)
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Admin Dashboard - Drive Eastern</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <style>
    :root {
      --gold: #ffcc00;
      --gold-dark: #b38600;
      --bg-light: #fff9e6;
      --bg-dark: #2b2b2b;
      --text-dark: #333;
      --text-light: #eee;
    }
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: var(--bg-light);
      color: var(--text-dark);
      margin: 0;
      padding: 1rem 2rem;
      transition: background-color 0.4s, color 0.4s;
    }
    body.dark {
      background: var(--bg-dark);
      color: var(--text-light);
    }
    .dashboard-container {
      max-width: 960px;
      margin: auto;
      background: white;
      border-radius: 16px;
      box-shadow: 0 8px 24px rgba(255, 204, 0, 0.3);
      padding: 2rem 3rem;
    }
    body.dark .dashboard-container {
      background: #1f1f1f;
    }
    h1 {
      color: var(--gold-dark);
      margin-top: 0;
    }
    nav ul {
      list-style: none;
      padding: 0;
      display: flex;
      flex-wrap: wrap;
      gap: 1.5rem;
      margin: 1rem 0 2rem;
    }
    nav ul li a {
      text-decoration: none;
      color: var(--gold);
      border: 2px solid var(--gold);
      padding: 0.5rem 1rem;
      border-radius: 10px;
      font-weight: bold;
      transition: 0.3s;
    }
    nav ul li a:hover {
      background: var(--gold);
      color: var(--text-dark);
    }
    .summary {
      display: flex;
      flex-wrap: wrap;
      gap: 1.5rem;
      margin-bottom: 2rem;
    }
    .card {
      flex: 1;
      background: var(--gold);
      color: #4d3900;
      border-radius: 12px;
      padding: 1.5rem;
      text-align: center;
      font-weight: bold;
      box-shadow: 0 6px 16px rgba(204, 153, 0, 0.4);
    }
    body.dark .card {
      background: #b38600;
      color: #fff6cc;
    }
    .card .number {
      font-size: 2.8rem;
      margin-bottom: 0.4rem;
    }
    .activities {
      background: #fff;
      border-radius: 12px;
      padding: 1rem 1.5rem;
      box-shadow: 0 4px 20px rgba(255, 204, 0, 0.15);
      max-height: 230px;
      overflow-y: auto;
      color: #665500;
    }
    body.dark .activities {
      background: #2a2a2a;
      color: #f7e4a1;
    }
    .activity-item {
      border-bottom: 1px solid #f0e68c;
      padding: 0.5rem 0;
      display: flex;
      justify-content: space-between;
    }
    .activity-time {
      margin-left: 1rem;
      font-style: italic;
      color: #b38600;
    }
    .search-container {
      max-width: 480px;
      margin-bottom: 2rem;
    }
    .search-container input {
      width: 100%;
      padding: 0.6rem 1rem;
      font-size: 1rem;
      border-radius: 10px;
      border: 2px solid var(--gold);
    }
    body.dark .search-container input {
      background: #4b4b4b;
      color: white;
      border-color: #b38600;
    }
    #darkModeToggle {
      position: fixed;
      top: 1rem;
      right: 1rem;
      background: var(--gold);
      color: var(--text-dark);
      padding: 0.5rem 1rem;
      border: none;
      border-radius: 10px;
      font-weight: bold;
      cursor: pointer;
    }
    #darkModeToggle:hover {
      background: var(--gold-dark);
      color: white;
    }
    /* Fare price form styling */
    .fare-price-settings {
      margin-bottom: 2rem;
      background: var(--bg-light);
      padding: 1rem;
      border-radius: 12px;
      box-shadow: 0 4px 10px rgba(255, 204, 0, 0.2);
    }
    body.dark .fare-price-settings {
      background: #1f1f1f;
    }
    .fare-price-settings h2 {
      color: var(--gold-dark);
      margin-top: 0;
    }
    .fare-price-settings form {
      display: flex;
      align-items: center;
      gap: 1rem;
      flex-wrap: wrap;
    }
    .fare-price-settings label {
      font-weight: 700;
      color: #b38600;
    }
    .fare-price-settings input[type="number"] {
      padding: 0.5rem;
      border: 2px solid var(--gold);
      border-radius: 10px;
      width: 120px;
      font-size: 1rem;
    }
    body.dark .fare-price-settings input[type="number"] {
      background: #4b4b4b;
      color: white;
      border-color: #b38600;
    }
    .fare-price-settings button {
      background: var(--gold);
      border: none;
      padding: 0.5rem 1rem;
      border-radius: 10px;
      font-weight: bold;
      cursor: pointer;
      color: #4d3900;
      transition: background-color 0.3s;
    }
    .fare-price-settings button:hover {
      background: var(--gold-dark);
      color: white;
    }
    @media (max-width: 680px) {
      .summary {
        flex-direction: column;
      }
      nav ul {
        flex-direction: column;
      }
      .fare-price-settings form {
        flex-direction: column;
        align-items: flex-start;
      }
      .fare-price-settings input[type="number"] {
        width: 100%;
      }
      .fare-price-settings button {
        width: 100%;
      }
    }
  </style>
</head>
<body>
  <button id="darkModeToggle" aria-pressed="false">Dark Mode 🌙</button>

  <div class="dashboard-container">
    <h1>Admin Dashboard</h1>

    <nav>
      <ul>
        <li><a href="view_users.php">View Users</a></li>
        <li><a href="view_rides.php">View Rides</a></li>
        <li><a href="view_reviews.php">View Reviews</a></li>
        <li><a href="notifications.php">Notifications</a></li>
        <li><a href="../logout.php">Logout</a></li>
      </ul>
    </nav>

    <div class="search-container">
      <label for="quickSearch" style="font-weight:700; color:#b38600;">Quick Search</label>
      <input type="search" id="quickSearch" placeholder="Search users or rides..." />
      <small style="color:#b38600;">Type and press Enter to search.</small>
    </div>

    <section class="summary">
      <div class="card">
        <div class="number"><?= number_format($totalUsers) ?></div>
        <div>Total Users</div>
      </div>
      <div class="card">
        <div class="number"><?= number_format($totalRides) ?></div>
        <div>Total Rides</div>
      </div>
      <div class="card">
        <div class="number"><?= number_format($totalReviews) ?></div>
        <div>Total Reviews</div>
      </div>
    </section>

    <!-- Fare Price Settings Section -->
    <section class="fare-price-settings">
      <h2>Fare Price Settings</h2>

      <?php if ($fareUpdateMsg): ?>
        <p style="color:<?= strpos($fareUpdateMsg, 'success') !== false ? 'green' : 'red' ?>; font-weight:bold;"><?= htmlspecialchars($fareUpdateMsg) ?></p>
      <?php endif; ?>

      <form method="post" action="">
        <label for="fare_price">Current Fare Price (Rs):</label>
        <input
          type="number"
          step="0.01"
          min="0"
          id="fare_price"
          name="fare_price"
          value="<?= htmlspecialchars(number_format($farePrice, 2)) ?>"
          required
        />
        <button type="submit">Update</button>
      </form>
    </section>

    <section class="activities">
      <h2>Recent Activity</h2>
      <?php foreach ($recentActivities as $activity): ?>
        <div class="activity-item">
          <span><?= htmlspecialchars($activity['desc']) ?></span>
          <time class="activity-time" datetime="<?= htmlspecialchars($activity['datetime']) ?>">
            <?= htmlspecialchars($activity['time']) ?>
          </time>
        </div>
      <?php endforeach; ?>
    </section>
  </div>

  <script>
    const toggle = document.getElementById('darkModeToggle');
    const body = document.body;

    if (localStorage.getItem('darkMode') === 'enabled') {
      body.classList.add('dark');
      toggle.setAttribute('aria-pressed', 'true');
      toggle.textContent = 'Light Mode ☀️';
    }

    toggle.addEventListener('click', () => {
      const isDark = body.classList.toggle('dark');
      localStorage.setItem('darkMode', isDark ? 'enabled' : 'disabled');
      toggle.setAttribute('aria-pressed', isDark.toString());
      toggle.textContent = isDark ? 'Light Mode ☀️' : 'Dark Mode 🌙';
    });

    document.getElementById('quickSearch').addEventListener('keydown', function(e) {
      if (e.key === 'Enter') {
        const value = this.value.trim();
        if (value.toLowerCase().includes('ride')) {
          window.location.href = 'view_rides.php?search=' + encodeURIComponent(value);
        } else {
          window.location.href = 'view_users.php?search=' + encodeURIComponent(value);
        }
      }
    });
  </script>
</body>
</html>
