<?php
// administrator/includes/logger.php

/**
 * Logs an administrative action to the activity_logs table.
 *
 * @param mysqli $conn The database connection object.
 * @param int $admin_id The ID of the admin performing the action.
 * @param string $action_type A short, descriptive type of action (e.g., 'ADD_USER', 'UPDATE_PRODUCT').
 * @param string $description A detailed description of the action, including user/product names etc.
 * @return bool True on success, false on failure.
 */
function logAdminActivity($conn, $admin_id, $action_type, $description) {
    // Get the IP address of the user
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';

    $stmt = $conn->prepare("INSERT INTO activity_logs (admin_id, action_type, description, ip_address) VALUES (?, ?, ?, ?)");

    if ($stmt) {
        $stmt->bind_param("isss", $admin_id, $action_type, $description, $ip_address);
        if ($stmt->execute()) {
            $stmt->close();
            return true;
        } else {
            error_log("Error logging activity: " . $stmt->error); // Log to PHP error log
            $stmt->close();
            return false;
        }
    } else {
        error_log("Error preparing log statement: " . $conn->error); // Log to PHP error log
        return false;
    }
}
?>