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
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // --- Validation ---
    if (empty($username) || empty($password) || empty($confirm_password)) {
        $response['message'] = 'All fields (Username, Password, Confirm Password) must be filled.';
        echo json_encode($response);
        exit;
    }
    if ($password !== $confirm_password) {
        $response['message'] = 'Passwords do not match. Please re-enter them carefully.';
        echo json_encode($response);
        exit;
    }
    if (strlen($password) < 8) {
        $response['message'] = 'Password must be at least 8 characters long.';
        echo json_encode($response);
        exit;
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt_check = $conn->prepare("SELECT id FROM admin_users WHERE username = ?");
    if ($stmt_check) {
        $stmt_check->bind_param("s", $username);
        $stmt_check->execute();
        $stmt_check->store_result();
        if ($stmt_check->num_rows > 0) {
            $response['message'] = 'This username is already taken. Please choose a different one.';
            echo json_encode($response);
            $stmt_check->close();
            exit;
        }
        $stmt_check->close();
    } else {
        $response['message'] = 'Database error during username check: ' . $conn->error;
        echo json_encode($response);
        exit;
    }

    // Insert the new admin user
    $stmt = $conn->prepare("INSERT INTO admin_users (username, password) VALUES (?, ?)");

    if ($stmt) {
        $stmt->bind_param("ss", $username, $hashed_password);

        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'New admin user added successfully!';

            // --- IMPORTANT: Log the activity here ---
            // Get the actual logged-in admin's ID from the session
            $admin_id_performing_action = $_SESSION['admin_id'] ?? 0; // Use 0 or a specific 'system' admin ID if not logged in

            logAdminActivity(
                $conn,
                $admin_id_performing_action,
                'ADD_ADMIN_USER',
                "Added new admin user: '{$username}'"
            );

        } else {
            $response['message'] = 'Error adding admin user: ' . $stmt->error;
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