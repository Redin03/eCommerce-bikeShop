<?php
// api/delete_product.php
session_start();

// Set up error logging
ini_set('display_errors', 0); // Do not display errors to the user in production
ini_set('log_errors', 1);
$logFile = __DIR__ . '/../../logs/php_errors.log';
if (!is_dir(dirname($logFile))) {
    mkdir(dirname($logFile), 0775, true);
}
ini_set('error_log', $logFile);

// Include database connection
require_once __DIR__ . '/../../config/db.php';

// Define the redirect URL after processing
// It's crucial to set a default or validate this for security
$redirectUrl = $_POST['redirect_url'] ?? '../index.php#products'; // Default to products page

// Function to set a toast message in session and redirect
function set_toast_and_redirect($message, $type, $url, $conn_obj) {
    $_SESSION['toast_message'] = $message;
    $_SESSION['toast_type'] = $type;
    if ($conn_obj && $conn_obj instanceof mysqli && !$conn_obj->connect_error) {
        $conn_obj->close();
    }
    header('Location: ' . $url);
    exit();
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("[Product Delete Error] Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    set_toast_and_redirect('Invalid request method for product deletion.', 'danger', $redirectUrl, $conn);
}

// Get the product ID from the POST data
// The name of the input is 'productId' as defined in products.php
$productIdToDelete = $_POST['productId'] ?? null;
$productNameToDelete = ''; // Initialize product name variable for logging (optional, but good for logs)

error_log("[Product Delete Debug] Received POST data: " . print_r($_POST, true));

// Validate the product ID
if (empty($productIdToDelete) || !filter_var($productIdToDelete, FILTER_VALIDATE_INT)) {
    error_log("[Product Delete Error] Invalid or missing Product ID for deletion: " . ($productIdToDelete ?? 'null'));
    set_toast_and_redirect('Invalid or missing Product ID for deletion.', 'danger', $redirectUrl, $conn);
}

try {
    if (!isset($conn) || !$conn instanceof mysqli || $conn->connect_error) {
        throw new Exception("Database connection not established or failed: " . ($conn->connect_error ?? 'Unknown error'));
    }

    // --- NEW: Fetch product name BEFORE deleting, for logging purposes ---
    // Assuming your products table has a 'name' column and 'id' column
    $stmt_fetch_product_name = $conn->prepare("SELECT name FROM products WHERE id = ?");
    if (!$stmt_fetch_product_name) {
        throw new Exception("Prepare statement failed (fetch product name): " . $conn->error);
    }
    $stmt_fetch_product_name->bind_param("i", $productIdToDelete);
    $stmt_fetch_product_name->execute();
    $result_product_name = $stmt_fetch_product_name->get_result();
    if ($row = $result_product_name->fetch_assoc()) {
        $productNameToDelete = $row['name'];
    }
    $stmt_fetch_product_name->close();
    // --- END NEW ---

    // Prepare statement to delete the product
    // Assuming your products table is named 'products' and has an 'id' column
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    if (!$stmt) {
        throw new Exception("Prepare statement failed for delete: " . $conn->error);
    }

    $stmt->bind_param("i", $productIdToDelete); // "i" for integer type

    if (!$stmt->execute()) {
        throw new Exception('Failed to delete product: ' . $stmt->error);
    }

    // Check if any row was affected
    if ($stmt->affected_rows > 0) {
        // --- NEW: Log the successful product deletion ---
        $productLogFile = __DIR__ . '/../../logs/product_actions.log';
        // Ensure the logs directory exists
        if (!is_dir(dirname($productLogFile))) {
            mkdir(dirname($productLogFile), 0775, true);
        }
        $logMessage = "[" . date('Y-m-d H:i:s') . "] Product deleted: '$productNameToDelete' (ID: $productIdToDelete).";
        file_put_contents($productLogFile, $logMessage . PHP_EOL, FILE_APPEND);
        error_log("[Product Delete Log] " . $logMessage); // Also log to PHP error log for redundancy
        // --- END NEW ---

        error_log("[Product Delete Success] Product with ID " . $productIdToDelete . " deleted successfully.");
        set_toast_and_redirect('Product "' . htmlspecialchars($productNameToDelete) . '" (ID: ' . $productIdToDelete . ') deleted successfully!', 'success', $redirectUrl, $conn);
    } else {
        error_log("[Product Delete Info] No product found with ID " . $productIdToDelete . " or already deleted.");
        set_toast_and_redirect('No product found with that ID or it was already deleted.', 'info', $redirectUrl, $conn);
    }

    $stmt->close();

} catch (Exception $e) {
    error_log("[Product Delete Error] Error during deletion: " . $e->getMessage());
    set_toast_and_redirect('Error deleting product: ' . $e->getMessage(), 'danger', $redirectUrl, $conn);
} finally {
    if (isset($conn) && $conn instanceof mysqli && !$conn->connect_error) {
        $conn->close();
    }
}
?>