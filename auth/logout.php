<?php
session_start();
session_unset();
session_destroy();

// Optional: Remove session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Redirect to home with a success toast
header("Location: ../front-pages/index.php?success=" . urlencode("You have been logged out successfully."));
exit;
?>