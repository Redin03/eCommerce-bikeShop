<?php
// api/reset_admin_password.php
session_start();

ini_set('display_errors', 0); // Do not display errors to the user in production
ini_set('log_errors', 1);
$logFile = __DIR__ . '/../../logs/php_errors.log';
if (!is_dir(dirname($logFile))) {
    mkdir(dirname($logFile), 0775, true);
}
ini_set('error_log', $logFile);

require_once __DIR__ . '/../../config/db.php';

$redirectUrl = $_POST['redirect_url'] ?? '../submenu/settings_users.php';

function set_toast_and_redirect($message, $type, $url, $conn_obj) {
    $_SESSION['toast_message'] = $message;
    $_SESSION['toast_type'] = $type;
    if ($conn_obj && $conn_obj instanceof mysqli && !$conn_obj->connect_error) {
        $conn_obj->close();
    }
    header('Location: ' . $url);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("[Admin Reset Error] Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    set_toast_and_redirect('Invalid request method.', 'danger', $redirectUrl, $conn);
}

$userId = $_POST['user_id'] ?? '';
$newPassword = $_POST['new_password'] ?? '';
$confirmNewPassword = $_POST['confirm_new_password'] ?? '';

if (empty($userId) || empty($newPassword) || empty($confirmNewPassword)) {
    error_log("[Admin Reset Error] Missing required fields for user ID: " . $userId);
    set_toast_and_redirect('All password fields are required.', 'danger', $redirectUrl, $conn);
}

if ($newPassword !== $confirmNewPassword) {
    error_log("[Admin Reset Error] New passwords do not match for user ID: " . $userId);
    set_toast_and_redirect('New passwords do not match.', 'danger', $redirectUrl, $conn);
}

// Password hashing
$hashed_password = password_hash($newPassword, PASSWORD_DEFAULT);
if ($hashed_password === false) {
    error_log("[Admin Reset Error] Password hashing failed for user ID: " . $userId);
    set_toast_and_redirect('Error processing new password.', 'danger', $redirectUrl, $conn);
}

// Transaction for atomicity
$conn->begin_transaction();

try {
    // Check if user exists (optional, but good practice)
    $stmt = $conn->prepare("SELECT username FROM admin_users WHERE id = ?");
    if (!$stmt) {
        throw new Exception("Prepare statement failed (user check): " . $conn->error);
    }
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows === 0) {
        throw new Exception("User with ID $userId not found.");
    }
    $stmt->bind_result($username);
    $stmt->fetch();
    $stmt->close();

    // Update the admin user's password
    $stmt = $conn->prepare("UPDATE admin_users SET password = ? WHERE id = ?");
    if (!$stmt) {
        throw new Exception("Prepare statement failed (update): " . $conn->error);
    }
    $stmt->bind_param("si", $hashed_password, $userId);
    if (!$stmt->execute()) {
        throw new Exception('Failed to reset admin password: ' . $stmt->error);
    }
    $stmt->close();

    // Log the successful password reset
    $userLogFile = __DIR__ . '/../../logs/user_actions.log';
    if (!is_dir(dirname($userLogFile))) {
        mkdir(dirname($userLogFile), 0775, true);
    }
    $logMessage = "[" . date('Y-m-d H:i:s') . "] Admin user '$username' (ID: $userId) password has been reset.";
    file_put_contents($userLogFile, $logMessage . PHP_EOL, FILE_APPEND);
    error_log("[Admin Reset Log] " . $logMessage); // Also log to PHP error log for redundancy

    $conn->commit();
    set_toast_and_redirect("Password for '$username' reset successfully!", 'success', $redirectUrl, $conn);

} catch (Exception $e) {
    $conn->rollback();
    error_log("[Admin Reset Error] Transaction rolled back for user ID: " . $userId . ". Error: " . $e->getMessage());
    set_toast_and_redirect('Error resetting password: ' . $e->getMessage(), 'danger', $redirectUrl, $conn);
} finally {
    if (isset($conn) && $conn instanceof mysqli && !$conn->connect_error) {
        $conn->close();
    }
}
?>