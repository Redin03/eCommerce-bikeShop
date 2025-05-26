<?php
// api/logout.php

session_start(); // Start the session to access session variables

// Set up error logging (optional but good practice)
ini_set('display_errors', 0); // Do not display errors to the user in production
ini_set('log_errors', 1);
$logFile = __DIR__ . '/../../logs/php_errors.log'; // Adjust path to your general PHP error log
if (!is_dir(dirname($logFile))) {
    mkdir(dirname($logFile), 0775, true);
}
ini_set('error_log', $logFile);

// Log the logout action (for auditing purposes)
$userLogFile = __DIR__ . '/../../logs/user_actions.log'; // Adjust path to your user action log
if (!is_dir(dirname($userLogFile))) {
    mkdir(dirname($userLogFile), 0775, true);
}

// Get user info before destroying the session
$username = $_SESSION['admin_username'] ?? 'Unknown';
$userId = $_SESSION['admin_user_id'] ?? 'N/A';

$logMessage = "[" . date('Y-m-d H:i:s') . "] Admin logout for user: '$username' (ID: $userId).";
file_put_contents($userLogFile, $logMessage . PHP_EOL, FILE_APPEND);
error_log($logMessage); // Also log to the general PHP error log

// Unset all session variables
$_SESSION = array();

// If it's desired to kill the session, also delete the session cookie.
// Note: This will destroy the session, and not just the session data!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finally, destroy the session
session_destroy();

// Set a toast message for successful logout
$_SESSION['toast_message'] = 'You have been successfully logged out.';
$_SESSION['toast_type'] = 'success';

// Redirect to the login page (relative path from api/logout.php to administrator/login.php)
header('Location: ../login.php');
exit(); // Important: Stop script execution after redirect
?>