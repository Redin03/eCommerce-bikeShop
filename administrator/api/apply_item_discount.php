<?php
session_start();
header('Content-Type: application/json');

// Disable error display to prevent HTML output breaking JSON response
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once '../../config/db.php'; // Adjust path if necessary
require_once '../includes/logger.php'; // Adjust path if necessary

$response = ['success' => false, 'message' => ''];

// Check if the admin is logged in (Crucial for all admin actions)
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    $response['message'] = 'Unauthorized access. Please log in.';
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = filter_var($_POST['product_id'] ?? '', FILTER_VALIDATE_INT);
    $variation_id = filter_var($_POST['variation_id'] ?? '', FILTER_VALIDATE_INT); // Optional
    $discount_percentage = filter_var($_POST['discount_percentage'] ?? '', FILTER_VALIDATE_FLOAT);
    $discount_expiry_date = trim($_POST['discount_expiry_date'] ?? '');

    // Input Validation
    if (!$product_id) {
        $response['message'] = 'Invalid product selected.';
        echo json_encode($response);
        exit;
    }
    if ($discount_percentage === false || $discount_percentage < 0 || $discount_percentage > 100) {
        $response['message'] = 'Invalid discount percentage. Must be between 0 and 100.';
        echo json_encode($response);
        exit;
    }

    // Prepare expiry date for database (NULL if empty)
    $expiry_date_db = !empty($discount_expiry_date) ? $discount_expiry_date : NULL;

    $conn->begin_transaction(); // Start transaction for atomicity

    try {
        if (!empty($variation_id)) {
            // Apply discount to a specific variation
            $stmt = $conn->prepare("UPDATE product_variations SET discount_percentage = ?, discount_expiry_date = ? WHERE id = ? AND product_id = ?");
            if (!$stmt) {
                throw new Exception("Failed to prepare variation update statement: " . $conn->error);
            }
            $stmt->bind_param("dsii", $discount_percentage, $expiry_date_db, $variation_id, $product_id);

        } else {
            // Apply discount to all variations of the selected product
            $stmt = $conn->prepare("UPDATE product_variations SET discount_percentage = ?, discount_expiry_date = ? WHERE product_id = ?");
            if (!$stmt) {
                throw new Exception("Failed to prepare product variations update statement: " . $conn->error);
            }
            $stmt->bind_param("dsi", $discount_percentage, $expiry_date_db, $product_id);
        }

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $conn->commit(); // Commit transaction
                $response['success'] = true;
                $response['message'] = 'Discount applied successfully!';

                // Log the activity
                $admin_id_performing_action = $_SESSION['admin_id'] ?? 0;
                $log_description = "Applied {$discount_percentage}% discount to product ID: {$product_id}";
                if (!empty($variation_id)) {
                    $log_description .= ", Variation ID: {$variation_id}";
                }
                if (!empty($expiry_date_db)) {
                    $log_description .= " until {$expiry_date_db}";
                }
                logAdminActivity($conn, $admin_id_performing_action, 'APPLY_ITEM_DISCOUNT', $log_description);

            } else {
                $response['message'] = 'No variations updated. Product or variation not found, or no changes made.';
            }
        } else {
            throw new Exception("Failed to execute update statement: " . $stmt->error);
        }
        $stmt->close();

    } catch (Exception $e) {
        $conn->rollback(); // Rollback transaction on error
        $response['message'] = 'Database error: ' . $e->getMessage();
        error_log("Error in apply_item_discount.php: " . $e->getMessage());
    }

    $conn->close();
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>