<?php
session_start();
require '../db.php';

if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleteId = (int) $_POST['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM rides WHERE id = ?");
    $stmt->execute([$deleteId]);
    header("Location: view_rides.php");
    exit;
}

try {
    $sql = "
        SELECT 
            r.id, r.pickup_location, r.dropoff_location, r.fare, r.status, r.created_at,
            p.name AS passenger_name, d.name AS driver_name
        FROM rides r
        LEFT JOIN users p ON r.passenger_id = p.id
        LEFT JOIN users d ON r.driver_id = d.id
        ORDER BY r.created_at DESC
    ";
    $stmt = $pdo->query($sql);
    $rides = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching rides: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Admin Panel - Ride History</title>
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
  .container {
    max-width: 1100px;
    margin: 0 auto;
    background: #fff;
    border-radius: 10px;
    padding: 1.75rem 2rem;
    box-shadow: 0 10px 25px rgba(0,0,0,0.08);
  }

  /* Actions Bar */
  .actions {
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1.75rem;
  }
  .actions a, .actions button {
    font-weight: 600;
    font-size: 1rem;
    padding: 0.45rem 1.1rem;
    border-radius: 5px;
    border: none;
    cursor: pointer;
    text-decoration: none;
    transition: background-color 0.25s ease, color 0.25s ease;
    user-select: none;
  }
  .actions a {
    color: #2980b9;
    background: #ecf0f1;
    border: 1.8px solid transparent;
  }
  .actions a:hover, .actions a:focus {
    background: #2980b9;
    color: #fff;
    border-color: #2980b9;
  }
  .print-btn {
    background: #27ae60;
    color: #fff;
  }
  .print-btn:hover, .print-btn:focus {
    background: #219150;
  }
  .download-btn {
    background: #f39c12;
    color: #fff;
  }
  .download-btn:hover, .download-btn:focus {
    background: #d78e0e;
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
  }
  tbody td[data-label="Fare"] {
    font-weight: 600;
    color: #27ae60;
  }
  tbody td[data-label="Status"] {
    text-transform: capitalize;
    font-weight: 600;
    color: #34495e;
  }

  /* Delete Button */
  .delete-btn {
    background-color: #e74c3c;
    color: #fff;
    border-radius: 6px;
    border: none;
    padding: 0.4rem 0.8rem;
    font-size: 0.88rem;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.3s ease;
  }
  .delete-btn:hover, .delete-btn:focus {
    background-color: #c0392b;
  }

  /* Responsive */
  @media (max-width: 900px) {
    .actions {
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
    tbody td[data-label="Action"] {
      padding-left: 0;
      text-align: right;
      position: static;
    }
    tbody td[data-label="Action"] button {
      width: 100%;
      max-width: 120px;
    }
  }
</style>
</head>
<body>

<div class="container">
  <h1>Ride History</h1>

  <div class="actions" role="navigation" aria-label="Page actions">
    <a href="dashboard.php" aria-label="Go back to dashboard">← Back to Dashboard</a>
    <button class="print-btn" type="button" aria-label="Print page" onclick="window.print()">🖨️ Print</button>
    <button class="download-btn" type="button" aria-label="Download rides history PDF" onclick="downloadPDF()">⬇️ Download PDF</button>
  </div>

  <table id="rideTable" role="table" aria-describedby="rideTableSummary">
    <caption id="rideTableSummary" class="sr-only">Ride history with details such as passenger, driver, locations, fare, status, and date</caption>
    <thead>
      <tr>
        <th scope="col">Ride ID</th>
        <th scope="col">Passenger</th>
        <th scope="col">Driver</th>
        <th scope="col">Pickup</th>
        <th scope="col">Dropoff</th>
        <th scope="col">Fare</th>
        <th scope="col">Status</th>
        <th scope="col">Created</th>
        <th scope="col">Action</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($rides)): ?>
        <?php foreach ($rides as $ride): ?>
          <tr>
            <td data-label="Ride ID"><?= htmlspecialchars($ride['id']) ?></td>
            <td data-label="Passenger"><?= htmlspecialchars($ride['passenger_name'] ?? 'N/A') ?></td>
            <td data-label="Driver"><?= htmlspecialchars($ride['driver_name'] ?? 'N/A') ?></td>
            <td data-label="Pickup"><?= htmlspecialchars($ride['pickup_location']) ?></td>
            <td data-label="Dropoff"><?= htmlspecialchars($ride['dropoff_location']) ?></td>
            <td data-label="Fare">$<?= number_format($ride['fare'], 2) ?></td>
            <td data-label="Status"><?= ucfirst($ride['status']) ?></td>
            <td data-label="Created"><?= htmlspecialchars($ride['created_at']) ?></td>
            <td data-label="Action">
              <form method="POST" onsubmit="return confirm('Are you sure you want to delete this ride?');" aria-label="Delete ride <?= htmlspecialchars($ride['id']) ?>">
                <input type="hidden" name="delete_id" value="<?= $ride['id'] ?>">
                <button class="delete-btn" type="submit">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="9" style="text-align:center; padding: 1.5rem; color: #7f8c8d;">No rides found.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- jsPDF + autoTable -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.29/jspdf.plugin.autotable.min.js"></script>
<script>
  async function downloadPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    doc.setFontSize(18);
    doc.setTextColor('#34495e');
    doc.text("Ride History", 14, 20);

    doc.setFontSize(12);
    doc.setTextColor(100);

    const table = document.getElementById("rideTable");
    const headers = [];
    const data = [];

    // Get headers except Action column
    table.querySelectorAll("thead th").forEach((th, i) => {
      if (i < 8) headers.push(th.textContent.trim());
    });

    // Get rows data except Action column
    table.querySelectorAll("tbody tr").forEach(row => {
      const rowData = [];
      row.querySelectorAll("td").forEach((td, i) => {
        if (i < 8) rowData.push(td.textContent.trim());
      });
      data.push(rowData);
    });

    doc.autoTable({
      head: [headers],
      body: data,
      startY: 30,
      theme: 'striped',
      styles: { fontSize: 10, cellPadding: 4 },
      headStyles: { fillColor: [243, 156, 18], textColor: 255, fontStyle: 'bold' },
      alternateRowStyles: { fillColor: [250, 250, 250] },
      margin: { left: 14, right: 14 }
    });

    doc.save("ride_history.pdf");
  }
</script>

</body>
</html>
