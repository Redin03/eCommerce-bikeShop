<?php
// api/delete_admin_user.php
session_start();

// Set up error logging
ini_set('display_errors', 0); // Do not display errors to the user in production
ini_set('log_errors', 1);
$logFile = __DIR__ . '/../../logs/php_errors.log';
if (!is_dir(dirname($logFile))) {
    mkdir(dirname($logFile), 0775, true);
}
ini_set('error_log', $logFile);

// Include database connection
require_once __DIR__ . '/../../config/db.php';

// Define the redirect URL after processing
$redirectUrl = $_POST['redirect_url'] ?? '../submenu/settings_users.php';

// Function to set a toast message in session and redirect
function set_toast_and_redirect($message, $type, $url, $conn_obj) {
    $_SESSION['toast_message'] = $message;
    $_SESSION['toast_type'] = $type;
    if ($conn_obj && $conn_obj instanceof mysqli && !$conn_obj->connect_error) {
        $conn_obj->close();
    }
    header('Location: ' . $url);
    exit();
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("[Admin User Delete Error] Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    set_toast_and_redirect('Invalid request method for deletion.', 'danger', $redirectUrl, $conn);
}

// Get the user ID from the POST data
$userIdToDelete = $_POST['user_id'] ?? null;
$usernameToDelete = ''; // Initialize username variable for logging

error_log("[Admin User Delete Debug] Received POST data: " . print_r($_POST, true));

// Validate the user ID
if (empty($userIdToDelete) || !filter_var($userIdToDelete, FILTER_VALIDATE_INT)) {
    error_log("[Admin User Delete Error] Invalid or missing User ID for deletion: " . ($userIdToDelete ?? 'null'));
    set_toast_and_redirect('Invalid or missing User ID for deletion.', 'danger', $redirectUrl, $conn);
}

try {
    if (!isset($conn) || !$conn instanceof mysqli || $conn->connect_error) {
        throw new Exception("Database connection not established or failed: " . ($conn->connect_error ?? 'Unknown error'));
    }

    // --- NEW: Fetch username BEFORE deleting, for logging purposes ---
    $stmt_fetch_username = $conn->prepare("SELECT username FROM admin_users WHERE id = ?");
    if (!$stmt_fetch_username) {
        throw new Exception("Prepare statement failed (fetch username): " . $conn->error);
    }
    $stmt_fetch_username->bind_param("i", $userIdToDelete);
    $stmt_fetch_username->execute();
    $result_username = $stmt_fetch_username->get_result();
    if ($row = $result_username->fetch_assoc()) {
        $usernameToDelete = $row['username'];
    }
    $stmt_fetch_username->close();
    // --- END NEW ---

    // Prepare statement to delete the user
    $stmt = $conn->prepare("DELETE FROM admin_users WHERE id = ?");
    if (!$stmt) {
        throw new Exception("Prepare statement failed for delete: " . $conn->error);
    }

    $stmt->bind_param("i", $userIdToDelete); // "i" for integer type

    if (!$stmt->execute()) {
        throw new Exception('Failed to delete admin account: ' . $stmt->error);
    }

    // Check if any row was affected
    if ($stmt->affected_rows > 0) {
        // --- NEW: Log the successful admin user deletion ---
        $userLogFile = __DIR__ . '/../../logs/user_actions.log';
        // Ensure the logs directory exists
        if (!is_dir(dirname($userLogFile))) {
            mkdir(dirname($userLogFile), 0775, true);
        }
        $logMessage = "[" . date('Y-m-d H:i:s') . "] User deleted admin account: '$usernameToDelete' (ID: $userIdToDelete).";
        file_put_contents($userLogFile, $logMessage . PHP_EOL, FILE_APPEND);
        error_log("[Admin Delete Log] " . $logMessage); // Also log to PHP error log for redundancy
        // --- END NEW ---

        error_log("[Admin User Delete Success] Admin user with ID " . $userIdToDelete . " deleted successfully.");
        set_toast_and_redirect('Admin account deleted successfully!', 'success', $redirectUrl, $conn);
    } else {
        error_log("[Admin User Delete Info] No admin user found with ID " . $userIdToDelete . " or already deleted.");
        set_toast_and_redirect('No admin account found with that ID or it was already deleted.', 'info', $redirectUrl, $conn);
    }

    $stmt->close();

} catch (Exception $e) {
    error_log("[Admin User Delete Error] Error during deletion: " . $e->getMessage());
    set_toast_and_redirect('Error deleting admin account: ' . $e->getMessage(), 'danger', $redirectUrl, $conn);
} finally {
    if (isset($conn) && $conn instanceof mysqli && !$conn->connect_error) {
        $conn->close();
    }
}
?>