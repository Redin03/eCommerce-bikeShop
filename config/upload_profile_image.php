<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../front-pages/my_account.php?error=" . urlencode("Please login first."));
    exit;
}

$user_id = $_SESSION['user_id'];

if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
    $fileTmp = $_FILES['profile_image']['tmp_name'];
    $fileName = basename($_FILES['profile_image']['name']);
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];

    if (!in_array($fileExt, $allowed)) {
        header("Location: ../front-pages/my_account.php?error=" . urlencode("Invalid file type."));
        exit;
    }

    $newName = 'user_' . $user_id . '_' . time() . '.' . $fileExt;
    $uploadDir = '../uploads/profile/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    if (move_uploaded_file($fileTmp, $uploadDir . $newName)) {
        // Save filename in DB
        require 'db.php';
        $stmt = $conn->prepare("UPDATE users SET profile_image=? WHERE id=?");
        $stmt->bind_param("si", $newName, $user_id);
        $stmt->execute();
        $stmt->close();
        $conn->close();
        header("Location: ../front-pages/my_account.php?success=" . urlencode("Profile image updated!"));
        exit;
    } else {
        header("Location: ../front-pages/my_account.php?error=" . urlencode("Upload failed."));
        exit;
    }
} else {
    header("Location: ../front-pages/my_account.php?error=" . urlencode("No file uploaded."));
    exit;
}
?>