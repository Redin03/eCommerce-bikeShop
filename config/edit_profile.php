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

$stmt = $conn->prepare("UPDATE users SET name=?, gender=?, contact_number=? WHERE id=?");
$stmt->bind_param("sssi", $name, $gender, $contact_number, $user_id);
if ($stmt->execute()) {
    header("Location: ../front-pages/my_account.php?success=" . urlencode("Profile updated successfully!"));
    exit;
} else {
    header("Location: ../front-pages/my_account.php?error=" . urlencode("Failed to update profile."));
    exit;
}
?>