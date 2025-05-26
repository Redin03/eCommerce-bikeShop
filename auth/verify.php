<?php
require_once __DIR__ . '/../config/db.php';

$code = $_GET['code'] ?? '';
if ($code) {
    $stmt = $conn->prepare("SELECT id FROM users WHERE verification_code=? AND is_verified=0");
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows == 1) {
        $stmt->close();
        $stmt = $conn->prepare("UPDATE users SET is_verified=1, verification_code=NULL WHERE verification_code=?");
        $stmt->bind_param("s", $code);
        $stmt->execute();
        $stmt->close();
        $conn->close();
        $msg = "Your email has been verified! You can now login.";
        header("Location: ../front-pages/index.php?success=" . urlencode($msg));
        exit;
    } else {
        $stmt->close();
        $conn->close();
        $msg = "Invalid or already used verification link.";
        header("Location: ../front-pages/index.php?error=" . urlencode($msg));
        exit;
    }
} else {
    $conn->close();
    $msg = "Invalid verification code.";
    header("Location: ../front-pages/index.php?error=" . urlencode($msg));
    exit;
}
?>