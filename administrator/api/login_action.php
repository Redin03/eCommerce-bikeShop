<?php
session_start();

// Set up error logging
ini_set('display_errors', 0); // Do not display errors to the user in production
ini_set('log_errors', 1);
$logFile = __DIR__ . '/../../logs/php_errors.log'; // Path to your general PHP error log
if (!is_dir(dirname($logFile))) {
    mkdir(dirname($logFile), 0775, true);
}
ini_set('error_log', $logFile);

// Include database connection
require_once __DIR__ . '/../../config/db.php'; // Adjust path as necessary

// Function to set a toast message and redirect
function set_toast_and_redirect($message, $type, $url, $conn_obj = null) {
    $_SESSION['toast_message'] = $message;
    $_SESSION['toast_type'] = $type;
    if ($conn_obj && $conn_obj instanceof mysqli && !$conn_obj->connect_error) {
        $conn_obj->close();
    }
    header('Location: ' . $url);
    exit();
}

$loginPage = '../login.php'; // The admin login page

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("[Login Process Error] Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    set_toast_and_redirect('Invalid request method.', 'danger', $loginPage);
}

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    error_log("[Login Process Error] Missing username or password.");
    set_toast_and_redirect('Please enter both username and password.', 'danger', $loginPage);
}

try {
    if (!isset($conn) || !$conn instanceof mysqli || $conn->connect_error) {
        throw new Exception("Database connection not established or failed: " . ($conn->connect_error ?? 'Unknown error'));
    }

    // Prepare statement to fetch user by username
    $stmt = $conn->prepare("SELECT id, username, password FROM admin_users WHERE username = ?");
    if (!$stmt) {
        throw new Exception("Prepare statement failed: " . $conn->error);
    }
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $user['password'])) {
            // Password is correct, set session variables
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_user_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];

            // Update last_login time
            $update_stmt = $conn->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
            if ($update_stmt) {
                $update_stmt->bind_param("i", $user['id']);
                $update_stmt->execute();
                $update_stmt->close();
            } else {
                error_log("[Login Success] Failed to update last_login for user ID: " . $user['id'] . " - " . $conn->error);
            }

            // Log the successful login
            $userLogFile = __DIR__ . '/../../logs/user_actions.log';
            if (!is_dir(dirname($userLogFile))) {
                mkdir(dirname($userLogFile), 0775, true);
            }
            $logMessage = "[" . date('Y-m-d H:i:s') . "] Admin login successful for user: '$username' (ID: " . $user['id'] . ").";
            file_put_contents($userLogFile, $logMessage . PHP_EOL, FILE_APPEND);
            error_log("[Login Success] " . $logMessage);

            // Redirect to the admin dashboard
            set_toast_and_redirect('Login successful!', 'success', '../index.php', $conn); // Redirect to your main admin page

        } else {
            // Password does not match
            error_log("[Login Failed] Invalid password for user: '$username'.");
            set_toast_and_redirect('Invalid username or password.', 'danger', $loginPage, $conn);
        }
    } else {
        // Username not found
        error_log("[Login Failed] Username '$username' not found.");
        set_toast_and_redirect('Invalid username or password.', 'danger', $loginPage, $conn);
    }

    $stmt->close();

} catch (Exception $e) {
    error_log("[Login Process Error] Exception: " . $e->getMessage());
    set_toast_and_redirect('An error occurred during login. Please try again later.', 'danger', $loginPage, $conn);
} finally {
    if (isset($conn) && $conn instanceof mysqli && !$conn->connect_error) {
        $conn->close();
    }
}
?>