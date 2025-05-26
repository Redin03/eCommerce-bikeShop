<?php
// api/add_admin_user.php
session_start();

// Set up error logging (ensure this is correctly configured)
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
    error_log("[Admin Add Error] Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    set_toast_and_redirect('Invalid request method.', 'danger', $redirectUrl, $conn);
}

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if (empty($username) || empty($password) || empty($confirm_password)) {
    error_log("[Admin Add Error] Missing required fields.");
    set_toast_and_redirect('All fields are required.', 'danger', $redirectUrl, $conn);
}

if ($password !== $confirm_password) {
    error_log("[Admin Add Error] Passwords do not match.");
    set_toast_and_redirect('Passwords do not match.', 'danger', $redirectUrl, $conn);
}

// Password hashing
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
if ($hashed_password === false) {
    error_log("[Admin Add Error] Password hashing failed.");
    set_toast_and_redirect('Error processing password.', 'danger', $redirectUrl, $conn);
}

// Transaction for atomicity
$conn->begin_transaction();

try {
    // Check if username already exists
    $stmt = $conn->prepare("SELECT id FROM admin_users WHERE username = ?");
    if (!$stmt) {
        throw new Exception("Prepare statement failed (username check): " . $conn->error);
    }
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        throw new Exception("Username '$username' already exists.");
    }
    $stmt->close();

    // Insert new admin user
    $stmt = $conn->prepare("INSERT INTO admin_users (username, password) VALUES (?, ?)");
    if (!$stmt) {
        throw new Exception("Prepare statement failed (insert): " . $conn->error);
    }
    $stmt->bind_param("ss", $username, $hashed_password);
    if (!$stmt->execute()) {
        throw new Exception('Failed to add admin account: ' . $stmt->error);
    }
    $newUserId = $conn->insert_id; // Get the ID of the newly inserted user
    $stmt->close();

    // --- NEW: Log the successful admin user addition ---
    $userLogFile = __DIR__ . '/../../logs/user_actions.log';
    // Ensure the logs directory exists
    if (!is_dir(dirname($userLogFile))) {
        mkdir(dirname($userLogFile), 0775, true);
    }
    $logMessage = "[" . date('Y-m-d H:i:s') . "] User added a new admin account: '$username' (ID: $newUserId).";
    file_put_contents($userLogFile, $logMessage . PHP_EOL, FILE_APPEND);
    error_log("[Admin Add Log] " . $logMessage); // Also log to PHP error log for redundancy
    // --- END NEW ---

    $conn->commit();
    set_toast_and_redirect('Admin account added successfully!', 'success', $redirectUrl, $conn);

} catch (Exception $e) {
    $conn->rollback();
    error_log("[Admin Add Error] Transaction rolled back. Error: " . $e->getMessage());
    set_toast_and_redirect('Error adding admin account: ' . $e->getMessage(), 'danger', $redirectUrl, $conn);
} finally {
    if (isset($conn) && $conn instanceof mysqli && !$conn->connect_error) {
        $conn->close();
    }
}
?>