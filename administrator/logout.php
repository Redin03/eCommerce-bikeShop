<?php
session_start();

// Include database connection and logger
require_once '../config/db.php'; // Adjust path if necessary
require_once 'includes/logger.php'; // Adjust path if necessary

// Get the admin's username before destroying the session
$admin_username = $_SESSION['admin_username'] ?? 'Unknown Admin'; // Fallback if username isn't set

// Log the logout action
logAdminActivity($conn, $_SESSION['admin_id'] ?? 0, 'ADMIN_LOGOUT', "Admin user '{$admin_username}' logged out.");

// Unset all of the session variables.
$_SESSION = array();

// Finally, destroy the session.
session_destroy();

// Redirect to login page
header("Location: login.php");
exit;
?>