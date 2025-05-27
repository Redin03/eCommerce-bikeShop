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
    $new_password = $_POST['new_password'] ?? '';
    $confirm_new_password = $_POST['confirm_new_password'] ?? '';

    // Basic validation
    if (!$admin_id) {
        $response['message'] = 'Invalid Admin ID.';
        echo json_encode($response);
        exit;
    }
    if (empty($new_password) || empty($confirm_new_password)) {
        $response['message'] = 'New password fields cannot be empty.';
        echo json_encode($response);
        exit;
    }
    if ($new_password !== $confirm_new_password) {
        $response['message'] = 'New passwords do not match.';
        echo json_encode($response);
        exit;
    }
    if (strlen($new_password) < 8) {
        $response['message'] = 'New password must be at least 8 characters long.';
        echo json_encode($response);
        exit;
    }

    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Update password in the database
    $stmt = $conn->prepare("UPDATE admin_users SET password = ? WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("si", $hashed_password, $admin_id);
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Admin password reset successfully.';

            // Fetch username for logging
            $username = 'Unknown Admin';
            $stmt_user = $conn->prepare("SELECT username FROM admin_users WHERE id = ?");
            if ($stmt_user) {
                $stmt_user->bind_param("i", $admin_id);
                $stmt_user->execute();
                $stmt_user->bind_result($fetched_username);
                $stmt_user->fetch();
                $stmt_user->close();
                if ($fetched_username) {
                    $username = $fetched_username;
                }
            }

            // Log the activity
            $admin_id_performing_action = $_SESSION['admin_id'] ?? 0; // Get actual logged-in admin ID
            logAdminActivity(
                $conn,
                $admin_id_performing_action,
                'RESET_ADMIN_PASSWORD',
                "Reset password for admin: '{$username}' (ID: {$admin_id})"
            );

        } else {
            $response['message'] = 'Error resetting password: ' . $stmt->error;
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