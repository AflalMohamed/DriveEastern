<?php
session_start();
require_once 'db.php'; // Your PDO connection file

// Simple validation and sanitization
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

// Check for empty fields
if (!$name || !$email || !$subject || !$message) {
    $_SESSION['message'] = "All fields are required.";
    $_SESSION['messageType'] = "error";
    header('Location: Help.php');
    exit();
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['message'] = "Invalid email address.";
    $_SESSION['messageType'] = "error";
    header('Location: Help.php');
    exit();
}

// Insert message into the database
try {
    $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, subject, message, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$name, $email, $subject, $message]);

    $_SESSION['message'] = "Thank you for your message! We will get back to you soon.";
    $_SESSION['messageType'] = "success";

} catch (Exception $e) {
    // Optionally log error for debugging:
    // error_log($e->getMessage());

    $_SESSION['message'] = "An error occurred while sending your message. Please try again later.";
    $_SESSION['messageType'] = "error";
}

// Redirect back to contact page to show message
header('Location: Help.php');
exit();
