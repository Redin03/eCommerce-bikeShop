<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../front-pages/my_account.php?error=" . urlencode("Please login first."));
    exit;
}
require_once __DIR__ . '/db.php';

$user_id = $_SESSION['user_id'];

// Delete all activity logs for this user
$stmt = $conn->prepare("DELETE FROM customer_activity_logs WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->close();

// Log the clear action itself
$activity_type = "Activity Log";
$description = "Cleared all activity history.";
$stmt_log = $conn->prepare("INSERT INTO customer_activity_logs (user_id, activity_type, description) VALUES (?, ?, ?)");
$stmt_log->bind_param("iss", $user_id, $activity_type, $description);
$stmt_log->execute();
$stmt_log->close();

$conn->close();

header("Location: ../front-pages/my_account.php?tab=activity_log&success=" . urlencode("Activity history cleared."));
exit;