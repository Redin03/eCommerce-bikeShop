<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../front-pages/my_account.php?error=" . urlencode("Please login first."));
    exit;
}

$user_id = $_SESSION['user_id'];
$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Fetch current password hash
$stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($password_hash);
$stmt->fetch();
$stmt->close();

if (!password_verify($current_password, $password_hash)) {
    header("Location: ../front-pages/my_account.php?tab=profile&error=" . urlencode("Current password is incorrect."));
    exit;
}

if ($new_password !== $confirm_password) {
    header("Location: ../front-pages/my_account.php?tab=profile&error=" . urlencode("New passwords do not match."));
    exit;
}

if (strlen($new_password) < 6) {
    header("Location: ../front-pages/my_account.php?tab=profile&error=" . urlencode("Password must be at least 6 characters."));
    exit;
}

// Update password
$new_hash = password_hash($new_password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
$stmt->bind_param("si", $new_hash, $user_id);
$stmt->execute();
$stmt->close();

// Log the activity to customer_activity_logs table
$activity_type = "Account";
$description = "Changed account password.";
$stmt_log = $conn->prepare("INSERT INTO customer_activity_logs (user_id, activity_type, description) VALUES (?, ?, ?)");
$stmt_log->bind_param("iss", $user_id, $activity_type, $description);
$stmt_log->execute();
$stmt_log->close();

$conn->close();

header("Location: ../front-pages/my_account.php?tab=profile&success=" . urlencode("Password changed successfully."));
exit;