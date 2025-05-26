<?php
session_start();
require_once __DIR__ . '/../config/db.php';

$email = $_POST['loginEmail'] ?? '';
$password = $_POST['loginPassword'] ?? '';

if (!$email || !$password) {
    header("Location: ../front-pages/index.php?error=" . urlencode("Please enter both email and password."));
    exit;
}

// Check if user exists and is verified
$stmt = $conn->prepare("SELECT id, name, password, is_verified FROM users WHERE email=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 1) {
    $stmt->bind_result($user_id, $user_name, $hashed_password, $is_verified);
    $stmt->fetch();

    if (!$is_verified) {
        $stmt->close();
        $conn->close();
        header("Location: ../front-pages/index.php?error=" . urlencode("Please verify your email before logging in."));
        exit;
    }

    if (password_verify($password, $hashed_password)) {
        // Set session variables
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_name'] = $user_name;
        $_SESSION['user_email'] = $email;

        $stmt->close();
        $conn->close();
        header("Location: ../front-pages/index.php?success=" . urlencode("Login successful! Welcome, $user_name."));
        exit;
    } else {
        $stmt->close();
        $conn->close();
        header("Location: ../front-pages/index.php?error=" . urlencode("Incorrect password."));
        exit;
    }
} else {
    $stmt->close();
    $conn->close();
    header("Location: ../front-pages/index.php?error=" . urlencode("No account found with that email."));
    exit;
}
?>