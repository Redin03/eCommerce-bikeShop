<?php
session_start(); // Start the session to access admin_id
header('Content-Type: application/json');

// Disable error display to prevent HTML output breaking JSON response
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once '../../config/db.php';
require_once '../includes/logger.php'; // Include the logger function

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_id = filter_var($_POST['admin_id'] ?? '', FILTER_VALIDATE_INT);

    // Basic validation
    if (!$admin_id) {
        $response['message'] = 'Invalid Admin ID.';
        echo json_encode($response);
        exit;
    }

    // Optional: Prevent deleting the currently logged-in admin
    // IMPORTANT: Make sure $_SESSION['admin_id'] is correctly set upon login
    if (isset($_SESSION['admin_id']) && $admin_id == $_SESSION['admin_id']) {
        $response['message'] = 'You cannot delete your own account.';
        echo json_encode($response);
        exit;
    }

    // Fetch username for logging before deletion
    $username_to_delete = 'Unknown Admin';
    $stmt_user = $conn->prepare("SELECT username FROM admin_users WHERE id = ?");
    if ($stmt_user) {
        $stmt_user->bind_param("i", $admin_id);
        $stmt_user->execute();
        $stmt_user->bind_result($fetched_username);
        $stmt_user->fetch();
        $stmt_user->close();
        if ($fetched_username) {
            $username_to_delete = $fetched_username;
        }
    }

    // Delete admin from the database
    $stmt = $conn->prepare("DELETE FROM admin_users WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $admin_id);
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $response['success'] = true;
                $response['message'] = "Admin user '{$username_to_delete}' (ID: {$admin_id}) deleted successfully.";

                // Log the activity
                $admin_id_performing_action = $_SESSION['admin_id'] ?? 0; // Get actual logged-in admin ID
                logAdminActivity(
                    $conn,
                    $admin_id_performing_action,
                    'DELETE_ADMIN_USER',
                    "Deleted admin user: '{$username_to_delete}' (ID: {$admin_id})"
                );

            } else {
                $response['message'] = 'Admin user not found or already deleted.';
            }
        } else {
            $response['message'] = 'Error deleting admin user: ' . $stmt->error;
        }
        $stmt->close();
    } else {
        $response['message'] = 'Database preparation error: ' . $conn->error;
    }

    $conn->close();
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
// No closing PHP tag to prevent accidental whitespace