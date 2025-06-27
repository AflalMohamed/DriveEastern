<?php
session_start();

// Restrict access to admins only
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

require_once '../db.php'; // your PDO connection

// Fetch all contact messages, most recent first
$stmt = $pdo->query("SELECT id, name, email, subject, message, created_at FROM contact_messages ORDER BY created_at DESC");
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Admin Notifications - Contact Messages</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet" />
  <style>
    :root {
      --color-primary: #b38600;          /* primary yellow */
      --color-primary-dark: #7a5c00;     /* darker yellow/golden */
      --color-success: #28a745;
      --color-danger: #dc3545;
      --color-bg: #fff9e6;               /* warm light yellowish background */
      --color-card-bg: #ffffff;
      --color-text-primary: #333333;
      --color-text-secondary: #997700;
      --border-radius: 12px;
      --box-shadow: 0 4px 20px rgba(179, 134, 0, 0.25);
      --transition: 0.3s ease;
      --max-width: 900px;
    }

    * {
      box-sizing: border-box;
    }

    body {
      font-family: 'Inter', sans-serif;
      background: var(--color-bg);
      margin: 0;
      padding: 2.5rem 1rem;
      display: flex;
      justify-content: center;
      color: var(--color-text-primary);
      min-height: 100vh;
    }

    main {
      max-width: var(--max-width);
      width: 100%;
    }

    h1 {
      font-weight: 700;
      font-size: 2.5rem;
      color: var(--color-primary);
      margin-bottom: 1rem;
      text-align: center;
      user-select: none;
    }

    a.back-link {
      display: inline-block;
      margin-bottom: 2rem;
      font-weight: 600;
      color: var(--color-primary);
      text-decoration: none;
      border: 2px solid var(--color-primary);
      padding: 0.4rem 1rem;
      border-radius: var(--border-radius);
      transition: background-color var(--transition), color var(--transition);
      user-select: none;
    }

    a.back-link:hover,
    a.back-link:focus-visible {
      background-color: var(--color-primary);
      color: white;
      outline: none;
    }

    .message {
      background: var(--color-card-bg);
      border-radius: var(--border-radius);
      box-shadow: var(--box-shadow);
      padding: 1.5rem 2rem;
      margin-bottom: 1.5rem;
      position: relative;
      transition: box-shadow var(--transition);
    }

    .message:hover {
      box-shadow: 0 8px 32px rgba(179, 134, 0, 0.35);
    }

    .message h3 {
      font-weight: 700;
      font-size: 1.3rem;
      margin: 0 0 0.25rem 0;
      color: var(--color-primary-dark);
      user-select: text;
    }

    .message small {
      color: var(--color-text-secondary);
      font-size: 0.9rem;
      user-select: text;
      display: block;
      margin-bottom: 1rem;
    }

    .message p {
      font-size: 1rem;
      line-height: 1.5;
      white-space: pre-wrap;
      color: var(--color-text-primary);
      user-select: text;
      margin: 0 0 1.25rem 0;
    }

    form.delete-form {
      position: absolute;
      top: 1.5rem;
      right: 2rem;
    }

    form.delete-form button {
      background-color: var(--color-danger);
      border: none;
      color: white;
      font-weight: 600;
      padding: 0.4rem 0.85rem;
      font-size: 0.9rem;
      border-radius: 8px;
      cursor: pointer;
      box-shadow: 0 2px 8px rgba(220, 53, 69, 0.4);
      transition: background-color var(--transition), box-shadow var(--transition);
      user-select: none;
    }

    form.delete-form button:hover,
    form.delete-form button:focus-visible {
      background-color: #b02a37;
      box-shadow: 0 4px 12px rgba(176, 42, 55, 0.6);
      outline: none;
    }

    /* Responsive */
    @media (max-width: 600px) {
      body {
        padding: 2rem 1rem;
      }
      .message {
        padding: 1.25rem 1.5rem;
      }
      form.delete-form {
        position: static;
        margin-top: 1rem;
        text-align: right;
      }
    }
  </style>
</head>
<body>
  <main role="main" aria-label="Admin Notifications - Contact Messages">

    <h1>Admin Notifications</h1>

    <a href="../index.php" class="back-link" aria-label="Back to Dashboard">← Back to Dashboard</a>

    <?php if (count($messages) === 0): ?>
      <p style="text-align:center; font-size:1.2rem; color: var(--color-text-secondary); user-select:none;">
        No contact messages found.
      </p>
    <?php else: ?>
      <?php foreach ($messages as $msg): ?>
        <article class="message" role="article" aria-label="Contact message from <?= htmlspecialchars($msg['name']) ?>">
          <h3><?= htmlspecialchars($msg['subject']) ?></h3>
          <small>
            From: <?= htmlspecialchars($msg['name']) ?> &lt;<?= htmlspecialchars($msg['email']) ?>&gt; &mdash;
            <?= date('F j, Y, g:i a', strtotime($msg['created_at'])) ?>
          </small>
          <p><?= nl2br(htmlspecialchars($msg['message'])) ?></p>

          <form class="delete-form" action="delete-message.php" method="post" onsubmit="return confirm('Are you sure you want to delete this message?');" aria-label="Delete message from <?= htmlspecialchars($msg['name']) ?>">
            <input type="hidden" name="id" value="<?= (int)$msg['id'] ?>" />
            <button type="submit" aria-describedby="deleteHint">Delete</button>
          </form>
        </article>
      <?php endforeach; ?>
    <?php endif; ?>

  </main>
</body>
</html>
