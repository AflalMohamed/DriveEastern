<?php
session_start();
require '../db.php';

if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

// Handle delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleteId = (int) $_POST['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$deleteId]);
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

try {
    $stmt = $pdo->query("SELECT id, name, email, phone, role, created_at FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching users: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Admin Panel - All Users</title>

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
  tbody td[data-label="Role"] {
    text-transform: capitalize;
    font-weight: 600;
    color: #34495e;
  }
  tbody td[data-label="Registered At"] {
    color: #7f8c8d;
    font-size: 0.9rem;
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
      white-space: normal;
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

<!-- jsPDF + autoTable -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.29/jspdf.plugin.autotable.min.js"></script>

</head>
<body>

<div class="container">
  <h1>All Users</h1>

  <nav class="actions" aria-label="Page actions">
    <a href="dashboard.php" aria-label="Go back to dashboard">← Back to Dashboard</a>
    <button class="print-btn" type="button" aria-label="Print page" onclick="window.print()">🖨️ Print</button>
    <button class="download-btn" type="button" aria-label="Download users list PDF" onclick="downloadPDF()">⬇️ Download PDF</button>
  </nav>

  <table id="userTable" role="table" aria-describedby="userTableSummary">
    <caption id="userTableSummary" class="sr-only">List of all users with details such as ID, name, email, phone, role, and registration date</caption>
    <thead>
      <tr>
        <th scope="col">ID</th>
        <th scope="col">Name</th>
        <th scope="col">Email</th>
        <th scope="col">Phone</th>
        <th scope="col">Role</th>
        <th scope="col">Registered At</th>
        <th scope="col">Action</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($users)): ?>
        <?php foreach ($users as $user): ?>
          <tr>
            <td data-label="ID"><?= htmlspecialchars($user['id']) ?></td>
            <td data-label="Name"><?= htmlspecialchars($user['name']) ?></td>
            <td data-label="Email"><?= htmlspecialchars($user['email']) ?></td>
            <td data-label="Phone"><?= htmlspecialchars($user['phone']) ?></td>
            <td data-label="Role"><?= htmlspecialchars(ucfirst($user['role'])) ?></td>
            <td data-label="Registered At"><?= htmlspecialchars($user['created_at']) ?></td>
            <td data-label="Action">
              <form method="POST" onsubmit="return confirm('Are you sure you want to delete user <?= htmlspecialchars(addslashes($user['name'])) ?>? This action cannot be undone.');" aria-label="Delete user <?= htmlspecialchars($user['name']) ?>" style="margin:0;">
                <input type="hidden" name="delete_id" value="<?= $user['id'] ?>">
                <button class="delete-btn" type="submit">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="7" style="text-align:center; padding: 2rem;">No users found.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<script>
  function downloadPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    doc.setFontSize(18);
    doc.text("All Users", 14, 22);
    doc.setFontSize(11);
    doc.setTextColor(100);

    // Collect table data for autoTable
    const columns = [
      { header: "ID", dataKey: "id" },
      { header: "Name", dataKey: "name" },
      { header: "Email", dataKey: "email" },
      { header: "Phone", dataKey: "phone" },
      { header: "Role", dataKey: "role" },
      { header: "Registered At", dataKey: "created_at" }
    ];

    // Extract rows from the HTML table
    const rows = [];
    const trs = document.querySelectorAll("#userTable tbody tr");
    trs.forEach(tr => {
      const cells = tr.querySelectorAll("td");
      if(cells.length === 7){
        rows.push({
          id: cells[0].textContent.trim(),
          name: cells[1].textContent.trim(),
          email: cells[2].textContent.trim(),
          phone: cells[3].textContent.trim(),
          role: cells[4].textContent.trim(),
          created_at: cells[5].textContent.trim()
        });
      }
    });

    doc.autoTable({
      columns: columns,
      body: rows,
      startY: 30,
      theme: 'striped',
      headStyles: { fillColor: '#f39c12', textColor: 255, fontStyle: 'bold' },
      alternateRowStyles: { fillColor: [245, 245, 245] },
      styles: { fontSize: 9 }
    });

    doc.save('all-users.pdf');
  }
</script>

</body>
</html>
