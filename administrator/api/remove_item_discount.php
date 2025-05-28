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

    // Input Validation
    if (!$product_id) {
        $response['message'] = 'Invalid product selected.';
        echo json_encode($response);
        exit;
    }

    $conn->begin_transaction(); // Start transaction

    try {
        if (!empty($variation_id)) {
            // Remove discount from a specific variation
            $stmt = $conn->prepare("UPDATE product_variations SET discount_percentage = NULL, discount_expiry_date = NULL WHERE id = ? AND product_id = ?");
            if (!$stmt) {
                throw new Exception("Failed to prepare variation removal statement: " . $conn->error);
            }
            $stmt->bind_param("ii", $variation_id, $product_id);
        } else {
            // Remove discount from all variations of the selected product
            $stmt = $conn->prepare("UPDATE product_variations SET discount_percentage = NULL, discount_expiry_date = NULL WHERE product_id = ?");
            if (!$stmt) {
                throw new Exception("Failed to prepare product variations removal statement: " . $conn->error);
            }
            $stmt->bind_param("i", $product_id);
        }

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $conn->commit(); // Commit transaction
                $response['success'] = true;
                $response['message'] = 'Discount removed successfully!';

                // Log the activity
                $admin_id_performing_action = $_SESSION['admin_id'] ?? 0;
                $log_description = "Removed discount from product ID: {$product_id}";
                if (!empty($variation_id)) {
                    $log_description .= ", Variation ID: {$variation_id}";
                }
                logAdminActivity($conn, $admin_id_performing_action, 'REMOVE_ITEM_DISCOUNT', $log_description);

            } else {
                $response['message'] = 'No variations updated. Product or variation not found, or no discount to remove.';
            }
        } else {
            throw new Exception("Failed to execute removal statement: " . $stmt->error);
        }
        $stmt->close();

    } catch (Exception $e) {
        $conn->rollback(); // Rollback transaction on error
        $response['message'] = 'Database error: ' . $e->getMessage();
        error_log("Error in remove_item_discount.php: " . $e->getMessage());
    }

    $conn->close();
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>