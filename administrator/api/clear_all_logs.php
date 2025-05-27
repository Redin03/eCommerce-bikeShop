<?php
session_start();
header('Content-Type: application/json');

// Initialize a response array
$response = ['success' => false, 'message' => 'An unknown error occurred.'];

// Disable error display to prevent HTML output breaking JSON response
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Check for authentication
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    $response['message'] = 'Authentication required. Please log in.';
    echo json_encode($response);
    exit;
}

// Include necessary files
require_once '../../config/db.php'; // Adjust path if necessary
require_once '../includes/logger.php'; // Adjust path if necessary

// Only proceed if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
    exit;
}

// Get the admin ID and username from the session for logging purposes
$admin_id_performing_action = $_SESSION['admin_id'] ?? null;
$admin_username_performing_action = $_SESSION['admin_username'] ?? 'Unknown Admin';

// Before deleting, let's get the count of logs to be deleted for a meaningful message
$total_logs_count = 0;
$count_stmt = $conn->prepare("SELECT COUNT(*) FROM activity_logs");
if ($count_stmt) {
    $count_stmt->execute();
    $count_stmt->bind_result($total_logs_count);
    $count_stmt->fetch();
    $count_stmt->close();
} else {
    error_log("Error preparing count statement for clear_all_logs: " . $conn->error);
}


// Execute the DELETE ALL query
$stmt = $conn->prepare("DELETE FROM activity_logs"); // No WHERE clause means delete all
if ($stmt) {
    if ($stmt->execute()) {
        // Check if any rows were actually affected, if not, it means the table was already empty
        $rows_affected = $stmt->affected_rows;
        $stmt->close();

        if ($rows_affected > 0 || $total_logs_count == 0) { // If logs were deleted, or if it was empty to begin with
            $response['success'] = true;
            $response['message'] = "Successfully cleared all {$rows_affected} activity log entries.";

            // Log this action: Who cleared all logs?
            // This log entry will be the only one remaining after the clear operation
            logAdminActivity(
                $conn,
                $admin_id_performing_action,
                'CLEAR_ALL_LOGS',
                "Admin user '{$admin_username_performing_action}' manually cleared all {$rows_affected} activity logs."
            );

        } else {
            $response['message'] = "No activity log entries were found to clear.";
        }
    } else {
        $response['message'] = 'Error clearing activity logs: ' . $stmt->error;
        error_log("Error executing clear_all_logs: " . $stmt->error);
    }
} else {
    $response['message'] = 'Database preparation error: ' . $conn->error;
    error_log("Database preparation error (clear_all_logs): " . $conn->error);
}

// Close DB connection before echoing
$conn->close();

// Always echo the JSON response at the very end
echo json_encode($response);
exit; // Crucial: ensures no further output is sent