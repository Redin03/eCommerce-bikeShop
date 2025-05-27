<?php
session_start();
header('Content-Type: application/json');

ini_set('display_errors', 0); // Disable error display for production
error_reporting(E_ALL); // Log all errors

require_once '../../config/db.php';
require_once '../includes/logger.php'; // For logging admin activities

$response = ['success' => false, 'message' => ''];

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    $response['message'] = 'Unauthorized access. Please log in.';
    echo json_encode($response);
    exit;
}

// Get the admin ID performing the action for logging
$admin_id_performing_action = $_SESSION['admin_id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = filter_var($_POST['product_id'] ?? '', FILTER_VALIDATE_INT);

    if (!$product_id) {
        $response['message'] = 'Invalid Product ID provided.';
        echo json_encode($response);
        exit;
    }

    // Start a transaction to ensure atomicity
    $conn->begin_transaction();

    try {
        // 1. Get image paths associated with the product to delete files from server
        $image_paths = [];
        $stmt_get_images = $conn->prepare("SELECT image_path FROM product_images WHERE product_id = ?");
        if (!$stmt_get_images) {
            throw new Exception('Failed to prepare get image paths statement: ' . $conn->error);
        }
        $stmt_get_images->bind_param("i", $product_id);
        $stmt_get_images->execute();
        $result_get_images = $stmt_get_images->get_result();
        while ($row = $result_get_images->fetch_assoc()) {
            $image_paths[] = $row['image_path'];
        }
        $stmt_get_images->close();

        // 2. Delete product images from the database
        $stmt_delete_images = $conn->prepare("DELETE FROM product_images WHERE product_id = ?");
        if (!$stmt_delete_images) {
            throw new Exception('Failed to prepare delete images statement: ' . $conn->error);
        }
        $stmt_delete_images->bind_param("i", $product_id);
        if (!$stmt_delete_images->execute()) {
            throw new Exception('Failed to delete product images from DB: ' . $stmt_delete_images->error);
        }
        $stmt_delete_images->close();

        // 3. Delete product variations from the database
        $stmt_delete_variations = $conn->prepare("DELETE FROM product_variations WHERE product_id = ?");
        if (!$stmt_delete_variations) {
            throw new Exception('Failed to prepare delete variations statement: ' . $conn->error);
        }
        $stmt_delete_variations->bind_param("i", $product_id);
        if (!$stmt_delete_variations->execute()) {
            throw new Exception('Failed to delete product variations from DB: ' . $stmt_delete_variations->error);
        }
        $stmt_delete_variations->close();

        // 4. Delete the product itself
        $stmt_delete_product = $conn->prepare("DELETE FROM products WHERE id = ?");
        if (!$stmt_delete_product) {
            throw new Exception('Failed to prepare delete product statement: ' . $conn->error);
        }
        $stmt_delete_product->bind_param("i", $product_id);
        if (!$stmt_delete_product->execute()) {
            throw new Exception('Failed to delete product: ' . $stmt_delete_product->error);
        }
        $rows_affected = $stmt_delete_product->affected_rows;
        $stmt_delete_product->close();

        if ($rows_affected === 0) {
            throw new Exception('Product not found or already deleted.');
        }

        // 5. Delete image files from the server
        $upload_dir_base = '../../'; // Adjust this path if your 'uploads' directory is not directly under the project root
        foreach ($image_paths as $path) {
            $full_path = $upload_dir_base . $path;
            if (file_exists($full_path)) {
                unlink($full_path);
            }
        }

        // If all successful, commit transaction
        $conn->commit();
        $response['success'] = true;
        $response['message'] = "Product ID: {$product_id} and associated data deleted successfully.";
        logAdminActivity($conn, $admin_id_performing_action, 'DELETE_PRODUCT', "Deleted product with ID: {$product_id}");

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $response['message'] = 'Product deletion failed: ' . $e->getMessage();
        error_log("Product deletion failed for ID {$product_id}: " . $e->getMessage()); // Log error to PHP error log
    }

    $conn->close();

} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>