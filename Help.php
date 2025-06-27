<?php
session_start();
$message = $_SESSION['message'] ?? null;
$messageType = $_SESSION['messageType'] ?? null;

// Clear message after displaying (flash message)
unset($_SESSION['message']);
unset($_SESSION['messageType']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Contact Us - EasternDrive</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
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

      body {
        font-family: var(--font-family);
        background: var(--background);
        color: var(--primary-black);
        margin: 0;
        padding: 3rem 1rem;
        display: flex;
        justify-content: center;
        align-items: flex-start;
        min-height: 100vh;
      }

      .contact-container {
        background: var(--card-bg);
        padding: 2.5rem 3rem;
        border-radius: var(--border-radius);
        box-shadow: 0 6px 16px var(--shadow-light);
        max-width: 480px;
        width: 100%;
        transition: box-shadow var(--transition);
      }

      .contact-container:hover {
        box-shadow: 0 10px 28px var(--shadow-hover);
      }

      h2 {
        margin-bottom: 2rem;
        font-weight: 800;
        font-size: 2.25rem;
        color: var(--primary-black);
        text-align: center;
      }

      label {
        display: block;
        margin: 1.25rem 0 0.5rem;
        font-weight: 600;
        font-size: 1rem;
        color: var(--primary-gray);
      }

      input[type="text"], input[type="email"], textarea {
        width: 100%;
        padding: 0.85rem 1.1rem;
        border: 2px solid #ddd;
        border-radius: 12px;
        font-size: 1rem;
        color: var(--primary-black);
        transition: border-color var(--transition), box-shadow var(--transition);
        outline-offset: 2px;
        font-family: var(--font-family);
      }

      input[type="text"]:focus, input[type="email"]:focus, textarea:focus {
        border-color: var(--primary-yellow);
        box-shadow: 0 0 8px var(--primary-yellow-dark);
        outline: none;
      }

      textarea {
        min-height: 140px;
        resize: vertical;
      }

      input[type="submit"] {
        margin-top: 2.5rem;
        background: var(--primary-yellow);
        border: none;
        color: var(--primary-black);
        padding: 1rem 0;
        width: 100%;
        font-size: 1.2rem;
        font-weight: 700;
        border-radius: var(--border-radius);
        cursor: pointer;
        box-shadow: 0 6px 14px rgba(255, 204, 0, 0.4);
        transition: background var(--transition), box-shadow var(--transition), transform var(--transition);
        font-family: var(--font-family);
      }

      input[type="submit"]:hover,
      input[type="submit"]:focus-visible {
        background: var(--primary-yellow-dark);
        transform: translateY(-4px);
        box-shadow: 0 8px 20px rgba(230, 184, 0, 0.5);
        outline: none;
      }

      .message {
        margin-top: 1.5rem;
        padding: 1rem 1.25rem;
        border-radius: var(--border-radius);
        font-weight: 600;
        font-size: 1rem;
        text-align: center;
        letter-spacing: 0.02em;
        user-select: none;
      }

      .success {
        background-color: #e6f4ea;
        color: #256029;
        border: 1.5px solid #81c784;
        box-shadow: 0 0 8px #81c784aa;
      }

      .error {
        background-color: #ffebeb;
        color: #b71c1c;
        border: 1.5px solid #e57373;
        box-shadow: 0 0 8px #e57373aa;
      }

      @media (max-width: 480px) {
        body {
          padding: 2rem 1rem;
        }

        .contact-container {
          padding: 2rem 1.5rem;
        }

        h2 {
          font-size: 1.75rem;
        }
      }
    </style>
</head>
<body>

<div class="contact-container" role="main" aria-labelledby="contactTitle">
    <h2 id="contactTitle">Contact Us</h2>

    <?php if ($message): ?>
      <div class="message <?= htmlspecialchars($messageType) ?>" role="alert">
          <?= htmlspecialchars($message) ?>
      </div>
    <?php endif; ?>

    <form action="contact-handler.php" method="post" novalidate>
        <label for="name">Name *</label>
        <input type="text" id="name" name="name" required placeholder="Your full name" aria-required="true" />

        <label for="email">Email *</label>
        <input type="email" id="email" name="email" required placeholder="your.email@example.com" aria-required="true" />

        <label for="subject">Subject *</label>
        <input type="text" id="subject" name="subject" required placeholder="Subject of your message" aria-required="true" />

        <label for="message">Message *</label>
        <textarea id="message" name="message" required placeholder="Write your message here..." aria-required="true"></textarea>

        <input type="submit" value="Send Message" />
    </form>
</div>

</body>
</html>
