<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../front-pages/my_account.php?error=" . urlencode("Please login first."));
    exit;
}

$user_id = $_SESSION['user_id'];
$name = $_POST['name'] ?? '';
$gender = $_POST['gender'] ?? '';
$contact_number = $_POST['contact_number'] ?? '';

if (!$name || !$gender || !$contact_number) {
    header("Location: ../front-pages/my_account.php?error=" . urlencode("All fields are required."));
    exit;
}

// Fetch old data to compare for description
$stmt_old = $conn->prepare("SELECT name, gender, contact_number FROM users WHERE id=?");
$stmt_old->bind_param("i", $user_id);
$stmt_old->execute();
$stmt_old->bind_result($old_name, $old_gender, $old_contact_number);
$stmt_old->fetch();
$stmt_old->close();

$stmt = $conn->prepare("UPDATE users SET name=?, gender=?, contact_number=? WHERE id=?");
$stmt->bind_param("sssi", $name, $gender, $contact_number, $user_id);
if ($stmt->execute()) {
    // Log the activity to customer_activity_logs table
    $description = "Profile updated: ";
    $changes = [];
    if ($old_name !== $name) {
        $changes[] = "Name from '{$old_name}' to '{$name}'";
    }
    if ($old_gender !== $gender) {
        $changes[] = "Gender from '{$old_gender}' to '{$gender}'";
    }
    if ($old_contact_number !== $contact_number) {
        $changes[] = "Contact Number from '{$old_contact_number}' to '{$contact_number}'";
    }
    $description .= implode(", ", $changes) . ".";

    // If no changes, still log as an update attempt for transparency
    if (empty($changes)) {
        $description = "Profile update attempted with no changes.";
    }

    $activity_type = "Profile Update";
    // Use customer_activity_logs table
    $stmt_log = $conn->prepare("INSERT INTO customer_activity_logs (user_id, activity_type, description) VALUES (?, ?, ?)");
    $stmt_log->bind_param("iss", $user_id, $activity_type, $description);
    $stmt_log->execute();
    $stmt_log->close();

    header("Location: ../front-pages/my_account.php?success=" . urlencode("Profile updated successfully!"));
    exit;
} else {
    header("Location: ../front-pages/my_account.php?error=" . urlencode("Failed to update profile."));
    exit;
}
?>