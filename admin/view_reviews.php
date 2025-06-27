<?php
session_start();
require '../db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Handle delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_ride_id'])) {
    $deleteId = intval($_POST['delete_ride_id']);
    $deleteStmt = $pdo->prepare("DELETE FROM rides WHERE id = ?");
    $deleteStmt->execute([$deleteId]);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

$sql = "
    SELECT r.id, r.rating, r.review, r.created_at,
           p.name AS passenger_name,
           d.name AS driver_name
    FROM rides r
    LEFT JOIN users p ON r.passenger_id = p.id
    LEFT JOIN users d ON r.driver_id = d.id
    ORDER BY r.created_at DESC
";
try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $pdo->query($sql);
    $rides = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<h2>Database error:</h2><p>" . htmlspecialchars($e->getMessage()) . "</p>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Passenger Reviews & Ratings - Admin Dashboard</title>

<style>
  /* Reset & base */
  *, *::before, *::after {
    box-sizing: border-box;
  }
  body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f4f6f8;
    color: #2c3e50;
    margin: 0;
    padding: 2.5rem 1rem;
    line-height: 1.5;
  }
  h1 {
    font-weight: 700;
    font-size: 2.25rem;
    margin-bottom: 1.5rem;
    color: #34495e;
    text-align: center;
  }

  /* Container */
  main {
    max-width: 1100px;
    margin: 0 auto;
    background: #fff;
    border-radius: 10px;
    padding: 1.75rem 2rem;
    box-shadow: 0 10px 25px rgba(0,0,0,0.08);
  }

  /* Back link */
  .back-link {
    display: inline-block;
    color: #2980b9;
    font-weight: 600;
    margin-bottom: 1.5rem;
    text-decoration: none;
    transition: color 0.25s ease;
  }
  .back-link:hover, .back-link:focus {
    color: #1a5f8b;
    outline: none;
  }

  /* Action Buttons */
  .action-buttons {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    margin-bottom: 1.75rem;
    flex-wrap: wrap;
  }
  .action-buttons button {
    background: #f39c12;
    color: #fff;
    font-weight: 600;
    font-size: 1rem;
    padding: 0.6rem 1.25rem;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    user-select: none;
    transition: background-color 0.25s ease;
  }
  .action-buttons button:hover, .action-buttons button:focus {
    background: #d78e0e;
    outline: none;
  }

  /* Table */
  table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 0.45rem;
    font-size: 0.95rem;
  }
  thead th {
    background: #f39c12;
    color: #fff;
    font-weight: 700;
    padding: 0.75rem 1rem;
    text-align: left;
    border-radius: 8px 8px 0 0;
    user-select: none;
  }
  tbody tr {
    background: #fff;
    box-shadow: 0 1px 5px rgb(0 0 0 / 0.05);
  }
  tbody tr:hover {
    background: #f9f9f9;
  }
  tbody td {
    padding: 0.85rem 1rem;
    vertical-align: middle;
    color: #34495e;
    word-break: break-word;
  }
  tbody td[data-label="Rating"] {
    font-weight: 700;
    color: #d35400;
  }

  /* Action cell */
  .actions-cell {
    text-align: right;
  }
  .actions-cell form {
    margin: 0;
  }
  .delete-btn {
    background-color: #e74c3c;
    color: #fff;
    border-radius: 6px;
    border: none;
    padding: 0.4rem 0.9rem;
    font-size: 0.88rem;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.3s ease;
  }
  .delete-btn:hover, .delete-btn:focus {
    background-color: #c0392b;
    outline: none;
  }

  /* Responsive */
  @media (max-width: 900px) {
    .action-buttons {
      justify-content: center;
    }
  }
  @media (max-width: 700px) {
    table, thead, tbody, th, td, tr {
      display: block;
    }
    thead tr {
      display: none;
    }
    tbody tr {
      margin-bottom: 1rem;
      box-shadow: 0 1px 6px rgba(0,0,0,0.1);
      border-radius: 10px;
      padding: 1rem;
      background: #fff;
    }
    tbody td {
      padding-left: 50%;
      position: relative;
      text-align: left;
      padding-top: 0.7rem;
      padding-bottom: 0.7rem;
      white-space: normal;
      word-break: break-word;
    }
    tbody td::before {
      content: attr(data-label);
      position: absolute;
      left: 1rem;
      top: 50%;
      transform: translateY(-50%);
      font-weight: 700;
      color: #7f8c8d;
      white-space: nowrap;
      font-size: 0.9rem;
    }
    .actions-cell {
      padding-left: 0;
      text-align: right;
      position: static;
    }
    .actions-cell button {
      width: 100%;
      max-width: 120px;
    }
  }
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.29/jspdf.plugin.autotable.min.js"></script>
</head>

<body>
<main>
  <h1>Passenger Reviews & Ratings</h1>

  <a href="dashboard.php" class="back-link" aria-label="Back to dashboard">← Back to Dashboard</a>

  <div class="action-buttons" role="region" aria-label="Page actions">
    <button id="printBtn" type="button" aria-label="Print page">🖨️ Print</button>
    <button id="downloadPdfBtn" type="button" aria-label="Download reviews as PDF">⬇️ Download PDF</button>
  </div>

  <table id="ridesTable" role="table" aria-describedby="ridesTableDesc" aria-live="polite">
    <caption id="ridesTableDesc" class="sr-only">List of passenger ride reviews and ratings</caption>
    <thead>
      <tr>
        <th scope="col">Ride ID</th>
        <th scope="col">Passenger</th>
        <th scope="col">Driver</th>
        <th scope="col">Rating</th>
        <th scope="col">Review</th>
        <th scope="col">Date</th>
        <th scope="col">Action</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($rides && count($rides) > 0): ?>
        <?php foreach ($rides as $ride): ?>
          <tr>
            <td data-label="Ride ID"><?= htmlspecialchars($ride['id']) ?></td>
            <td data-label="Passenger"><?= htmlspecialchars($ride['passenger_name'] ?? 'N/A') ?></td>
            <td data-label="Driver"><?= htmlspecialchars($ride['driver_name'] ?? 'N/A') ?></td>
            <td data-label="Rating"><?= $ride['rating'] !== null ? intval($ride['rating']) . '/5' : 'N/A' ?></td>
            <td data-label="Review"><?= htmlspecialchars($ride['review'] ?? 'N/A') ?></td>
            <td data-label="Date"><?= htmlspecialchars(date('M j, Y', strtotime($ride['created_at']))) ?></td>
            <td class="actions-cell">
              <form method="POST" onsubmit="return confirm('Are you sure you want to delete this ride?');" aria-label="Delete ride ID <?= htmlspecialchars($ride['id']) ?>">
                <input type="hidden" name="delete_ride_id" value="<?= htmlspecialchars($ride['id']) ?>" />
                <button class="delete-btn" type="submit">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="7" style="text-align:center; font-style:italic;">No reviews found.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</main>

<script>
document.getElementById('printBtn').addEventListener('click', () => window.print());

document.getElementById('downloadPdfBtn').addEventListener('click', () => {
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF();

  doc.setFontSize(18);
  doc.setTextColor('#34495e');
  doc.text("Passenger Reviews & Ratings", 14, 22);

  doc.setFontSize(11);
  doc.setTextColor(80);

  doc.autoTable({
    startY: 30,
    head: [['Ride ID', 'Passenger', 'Driver', 'Rating', 'Review', 'Date']],
    body: Array.from(document.querySelectorAll('#ridesTable tbody tr')).map(row => {
      const cells = row.querySelectorAll('td');
      return Array.from(cells).slice(0, 6).map(td => td.textContent.trim());
    }),
    theme: 'striped',
    headStyles: { fillColor: '#f39c12', textColor: 255 },
    styles: { textColor: '#2c3e50' },
    alternateRowStyles: { fillColor: [245, 245, 245] },
    margin: { top: 30 }
  });

  doc.save('passenger_reviews.pdf');
});
</script>

</body>
</html>
