<?php
session_start(); // Always start the session at the beginning

require_once '../../config/db.php'; // Include your database connection
require_once '../includes/logger.php'; // Include the logger function

// Redirect if already logged in (should be handled by login.php, but good safeguard)
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: ../index.php'); // Redirect to admin dashboard
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $_SESSION['login_message'] = [
            'text' => 'Please enter both username and password.',
            'type' => 'danger'
        ];
        header('Location: ../login.php');
        exit;
    }

    // Prepare statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT id, username, password FROM admin_users WHERE username = ?");
    if ($stmt) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($admin_id, $db_username, $hashed_password);
            $stmt->fetch();

            // Verify the password
            if (password_verify($password, $hashed_password)) {
                // Password is correct, set session variables
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $admin_id;
                $_SESSION['admin_username'] = $db_username;
                $_SESSION['last_login_time'] = time(); // Store login timestamp

                // Update last_login in the database
                $update_stmt = $conn->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
                if ($update_stmt) {
                    $update_stmt->bind_param("i", $admin_id);
                    $update_stmt->execute();
                    $update_stmt->close();
                }

                // Log the successful login activity
                logAdminActivity(
                    $conn,
                    $admin_id, // Use the actual admin_id from the database
                    'ADMIN_LOGIN',
                    "Admin user '{$db_username}' logged in successfully."
                );

                // Redirect to the admin dashboard
                header('Location: ../index.php');
                exit;

            } else {
                // Incorrect password
                $_SESSION['login_message'] = [
                    'text' => 'Invalid username or password. Please try again.',
                    'type' => 'danger'
                ];
            }
        } else {
            // Username not found
            $_SESSION['login_message'] = [
                'text' => 'Invalid username or password. Please try again.',
                'type' => 'danger'
            ];
        }
        $stmt->close();
    } else {
        // Database error during preparation
        $_SESSION['login_message'] = [
            'text' => 'An internal server error occurred. Please try again later.',
            'type' => 'danger'
        ];
        error_log("Login DB prepare error: " . $conn->error); // Log error for debugging
    }

    $conn->close();
    header('Location: ../login.php'); // Redirect back to login page with error
    exit;

} else {
    // If someone tries to access this page directly without POST
    header('Location: ../login.php');
    exit;
}
?>