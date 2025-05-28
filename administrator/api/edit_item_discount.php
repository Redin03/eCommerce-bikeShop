<?php
// api/edit_item_discount.php
session_start();
header('Content-Type: application/json');

ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once '../../config/db.php';
require_once '../includes/logger.php';

$response = ['success' => false, 'message' => ''];

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    $response['message'] = 'Unauthorized access. Please log in.';
    echo json_encode($response);
    exit;
}

$admin_id_performing_action = $_SESSION['admin_id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = filter_var($_POST['product_id'] ?? '', FILTER_VALIDATE_INT);
    $variation_id = filter_var($_POST['variation_id'] ?? '', FILTER_VALIDATE_INT);
    $discount_percentage = filter_var($_POST['discount_percentage'] ?? '', FILTER_VALIDATE_FLOAT);
    $discount_expiry_date = trim($_POST['discount_expiry_date'] ?? '');

    if (!$product_id) {
        $response['message'] = 'Invalid product ID provided.';
        echo json_encode($response);
        exit;
    }
    if ($discount_percentage === false || $discount_percentage < 0 || $discount_percentage > 100) {
        $response['message'] = 'Invalid discount percentage. Must be a number between 0 and 100.';
        echo json_encode($response);
        exit;
    }

    // Prepare expiry date for database
    $expiry_date_db = !empty($discount_expiry_date) ? $discount_expiry_date : NULL;

    $conn->begin_transaction();

    try {
        if (!empty($variation_id)) {
            // Update a specific variation
            $stmt = $conn->prepare("UPDATE product_variations SET discount_percentage = ?, discount_expiry_date = ? WHERE product_id = ? AND id = ?");
            if (!$stmt) {
                throw new Exception("Failed to prepare statement for specific variation update: " . $conn->error);
            }
            $stmt->bind_param("dsii", $discount_percentage, $expiry_date_db, $product_id, $variation_id);
        } else {
            // Update all variations for a product (if no specific variation ID is provided)
            $stmt = $conn->prepare("UPDATE product_variations SET discount_percentage = ?, discount_expiry_date = ? WHERE product_id = ?");
            if (!$stmt) {
                throw new Exception("Failed to prepare statement for product-wide update: " . $conn->error);
            }
            $stmt->bind_param("dsi", $discount_percentage, $expiry_date_db, $product_id);
        }

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $conn->commit();
                $response['success'] = true;
                $response['message'] = 'Discount updated successfully!';

                $log_description = "Updated discount for product ID: {$product_id}";
                if (!empty($variation_id)) {
                    $log_description .= ", Variation ID: {$variation_id}";
                }
                $log_description .= " to {$discount_percentage}%";
                if (!empty($expiry_date_db)) {
                    $log_description .= " expiring on {$expiry_date_db}";
                } else {
                    $log_description .= " with no expiry date.";
                }
                logAdminActivity($conn, $admin_id_performing_action, 'EDIT_ITEM_DISCOUNT', $log_description);

            } else {
                $response['message'] = 'No variations updated. Product/variation not found or no changes made.';
            }
        } else {
            throw new Exception("Failed to execute update statement: " . $stmt->error);
        }
        $stmt->close();

    } catch (Exception $e) {
        $conn->rollback();
        $response['message'] = 'Database error: ' . $e->getMessage();
        error_log("Error in edit_item_discount.php: " . $e->getMessage());
    } finally {
        $conn->close();
    }
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>