<?php
// api/clear_logs_process.php
session_start();

// Set up error logging
ini_set('display_errors', 0);
ini_set('log_errors', 1);
$logFile = __DIR__ . '/../../logs/php_errors.log';
if (!is_dir(dirname($logFile))) {
    mkdir(dirname($logFile), 0775, true);
}
ini_set('error_log', $logFile);

$userActionLogFile = __DIR__ . '/../../logs/user_actions.log';

// Helper function to set session message and redirect
function set_session_message_and_redirect($message, $type, $target_url) {
    $_SESSION['log_clear_message'] = $message;
    $_SESSION['log_clear_type'] = $type;
    header('Location: ' . $target_url);
    exit();
}

$redirectUrl = '../index.php#log_history';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    set_session_message_and_redirect('Invalid request method.', 'danger', $redirectUrl);
}

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    error_log("[Log Clear Failed] Unauthorized attempt to clear logs. IP: " . $_SERVER['REMOTE_ADDR']);
    set_session_message_and_redirect('Unauthorized access. Please log in.', 'danger', $redirectUrl);
}

if (!isset($_POST['action']) || $_POST['action'] !== 'clear') {
    set_session_message_and_redirect('Invalid action specified for log clearing.', 'danger', $redirectUrl);
}

try {
    // Check if the log file exists and is writable (though file_put_contents implies it is)
    if (!file_exists($userActionLogFile)) {
        set_session_message_and_redirect('Log file does not exist. Nothing to clear.', 'info', $redirectUrl);
    }

    // --- REVISED FILE CLEARING LOGIC ---
    $handle = @fopen($userActionLogFile, 'w'); // Open in 'w' mode (write, truncates file to zero length)
    if ($handle === false) {
        // This will catch more specific locking or permission issues that file_put_contents might mask
        throw new Exception("Failed to open log file for writing (fopen failed). Check permissions/locking.");
    }
    fclose($handle); // Close the file, committing the truncation
    // --- END REVISED FILE CLEARING LOGIC ---


    // Log the action of clearing the logs
    $adminUsername = $_SESSION['admin_username'] ?? 'Unknown Admin';
    $logMessage = "[" . date('Y-m-d H:i:s') . "] Log history cleared by Admin: '$adminUsername' (ID: " . ($_SESSION['admin_user_id'] ?? 'N/A') . ").";
    
    // Use FILE_APPEND to add the new log message to the now empty file
    if (!file_put_contents($userActionLogFile, $logMessage . PHP_EOL, FILE_APPEND)) {
         // If writing the log message fails, it's still a problem.
         error_log("[Log Clear Warning] Failed to log clear action itself after clearing the file.");
    }
    error_log($logMessage);

    set_session_message_and_redirect('Log history cleared successfully!', 'success', $redirectUrl);

} catch (Exception $e) {
    error_log("[Log Clear Error] " . $e->getMessage());
    set_session_message_and_redirect('Failed to clear log history: ' . $e->getMessage(), 'danger', $redirectUrl);
}
?>