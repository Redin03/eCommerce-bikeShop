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
    // Sanitize and validate inputs
    $product_id = filter_var($_POST['product_id'] ?? '', FILTER_VALIDATE_INT);
    $name = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $subcategory = trim($_POST['subcategory'] ?? '');
    $description = trim($_POST['description'] ?? '');

    $variations_data = $_POST['variations'] ?? []; // Array of arrays
    $existing_images_to_keep = $_POST['existing_images_to_keep'] ?? []; // Array of image IDs to keep
    $new_images = $_FILES['new_product_images'] ?? []; // Array of uploaded files

    if (!$product_id) {
        $response['message'] = 'Invalid Product ID provided.';
        echo json_encode($response);
        exit;
    }
    if (empty($name) || empty($category) || empty($description)) {
        $response['message'] = 'Product Name, Category, and Description cannot be empty.';
        echo json_encode($response);
        exit;
    }
    if (!is_array($variations_data) || count($variations_data) === 0) {
        $response['message'] = 'At least one product variation is required.';
        echo json_encode($response);
        exit;
    }

    // Start a transaction
    $conn->begin_transaction();

    try {
        // 1. Update Product General Details
        $stmt_product = $conn->prepare("UPDATE products SET name = ?, category = ?, subcategory = ?, description = ? WHERE id = ?");
        if (!$stmt_product) {
            throw new Exception('Failed to prepare product update statement: ' . $conn->error);
        }
        $stmt_product->bind_param("ssssi", $name, $category, $subcategory, $description, $product_id);
        if (!$stmt_product->execute()) {
            throw new Exception('Failed to update product details: ' . $stmt_product->error);
        }
        $stmt_product->close();

        // 2. Manage Product Variations
        $current_variation_data = [];
        // Fetch existing variations (and their current stock) for this product
        $stmt_fetch_variations = $conn->prepare("SELECT id, stock FROM product_variations WHERE product_id = ?");
        if (!$stmt_fetch_variations) {
            throw new Exception('Failed to prepare fetch variations statement: ' . $conn->error);
        }
        $stmt_fetch_variations->bind_param("i", $product_id);
        $stmt_fetch_variations->execute();
        $result_fetch_variations = $stmt_fetch_variations->get_result();
        while ($row = $result_fetch_variations->fetch_assoc()) {
            $current_variation_data[$row['id']] = $row['stock'];
        }
        $stmt_fetch_variations->close();

        $submitted_variation_ids = [];
        $stmt_stock_history = $conn->prepare("INSERT INTO stock_history (product_id, variation_id, quantity_changed, change_type, admin_id) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt_stock_history) {
            throw new Exception("Stock history statement preparation failed: " . $conn->error);
        }

        foreach ($variations_data as $variation) {
            $variation_id = filter_var($variation['id'] ?? '', FILTER_VALIDATE_INT);
            $size = trim($variation['size'] ?? '');
            $color = trim($variation['color'] ?? '');
            $new_stock = filter_var($variation['stock'] ?? 0, FILTER_VALIDATE_INT);
            $price = filter_var($variation['price'] ?? 0.0, FILTER_VALIDATE_FLOAT);

            if (empty($size) || empty($color) || $new_stock === false || $price === false) {
                throw new Exception('Invalid variation data provided. Size, Color, Stock, and Price are required.');
            }

            if ($variation_id && isset($current_variation_data[$variation_id])) {
                // Update existing variation
                $old_stock = $current_variation_data[$variation_id];
                $stmt_update_variation = $conn->prepare("UPDATE product_variations SET size = ?, color = ?, stock = ?, price = ? WHERE id = ? AND product_id = ?");
                if (!$stmt_update_variation) {
                    throw new Exception('Failed to prepare variation update statement: ' . $conn->error);
                }
                $stmt_update_variation->bind_param("ssiidi", $size, $color, $new_stock, $price, $variation_id, $product_id);
                if (!$stmt_update_variation->execute()) {
                    throw new Exception('Failed to update variation: ' . $stmt_update_variation->error);
                }
                $stmt_update_variation->close();
                $submitted_variation_ids[] = $variation_id; // Add to list of kept variations

                // Log stock change if increased
                if ($new_stock > $old_stock) {
                    $quantity_added = $new_stock - $old_stock;
                    $change_type = 'stock_added';
                    $stmt_stock_history->bind_param("iiisi", $product_id, $variation_id, $quantity_added, $change_type, $admin_id_performing_action);
                    if (!$stmt_stock_history->execute()) {
                        throw new Exception("Stock history insertion failed for existing variation update: " . $stmt_stock_history->error);
                    }
                }
            } else {
                // Insert new variation
                $stmt_insert_variation = $conn->prepare("INSERT INTO product_variations (product_id, size, color, stock, price) VALUES (?, ?, ?, ?, ?)");
                if (!$stmt_insert_variation) {
                    throw new Exception('Failed to prepare variation insert statement: ' . $conn->error);
                }
                $stmt_insert_variation->bind_param("issid", $product_id, $size, $color, $new_stock, $price);
                if (!$stmt_insert_variation->execute()) {
                    throw new Exception('Failed to insert new variation: ' . $stmt_insert_variation->error);
                }
                $newly_inserted_variation_id = $conn->insert_id;
                $stmt_insert_variation->close();

                // Log initial stock for the new variation
                if ($new_stock > 0) { // Only log if initial stock is positive
                    $change_type = 'initial_stock';
                    $stmt_stock_history->bind_param("iiisi", $product_id, $newly_inserted_variation_id, $new_stock, $change_type, $admin_id_performing_action);
                    if (!$stmt_stock_history->execute()) {
                        throw new Exception("Stock history insertion failed for new variation: " . $stmt_stock_history->error);
                    }
                }
            }
        }
        $stmt_stock_history->close(); // Close the stock history statement

        // Delete variations that were removed from the form
        $variations_to_delete = array_diff(array_keys($current_variation_data), $submitted_variation_ids);
        if (!empty($variations_to_delete)) {
            $placeholders = implode(',', array_fill(0, count($variations_to_delete), '?'));
            $stmt_delete_variations = $conn->prepare("DELETE FROM product_variations WHERE id IN ($placeholders) AND product_id = ?");
            if (!$stmt_delete_variations) {
                throw new Exception('Failed to prepare delete variations statement: ' . $conn->error);
            }
            // Bind parameters dynamically
            $types = str_repeat('i', count($variations_to_delete)) . 'i'; // All are integers, plus product_id
            $bind_params = array_merge($variations_to_delete, [$product_id]);
            $stmt_delete_variations->bind_param($types, ...$bind_params);
            if (!$stmt_delete_variations->execute()) {
                throw new Exception('Failed to delete old variations: ' . $stmt_delete_variations->error);
            }
            $stmt_delete_variations->close();
        }

        // 3. Manage Product Images
        $upload_dir = '../../uploads/product_images/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Fetch existing images for this product from DB
        $existing_images_db = [];
        $stmt_fetch_images = $conn->prepare("SELECT id, image_path FROM product_images WHERE product_id = ?");
        if (!$stmt_fetch_images) {
            throw new Exception('Failed to prepare fetch images statement: ' . $conn->error);
        }
        $stmt_fetch_images->bind_param("i", $product_id);
        $stmt_fetch_images->execute();
        $result_fetch_images = $stmt_fetch_images->get_result();
        while ($row = $result_fetch_images->fetch_assoc()) {
            $existing_images_db[$row['id']] = $row['image_path'];
        }
        $stmt_fetch_images->close();

        // Determine images to delete (not in existing_images_to_keep array)
        $images_to_delete = array_diff(array_keys($existing_images_db), $existing_images_to_keep);

        if (!empty($images_to_delete)) {
            $placeholders = implode(',', array_fill(0, count($images_to_delete), '?'));
            $stmt_delete_images_db = $conn->prepare("DELETE FROM product_images WHERE id IN ($placeholders) AND product_id = ?");
            if (!$stmt_delete_images_db) {
                throw new Exception('Failed to prepare delete images statement: ' . $conn->error);
            }
            $types = str_repeat('i', count($images_to_delete)) . 'i';
            $bind_params = array_merge($images_to_delete, [$product_id]);
            $stmt_delete_images_db->bind_param($types, ...$bind_params);
            if (!$stmt_delete_images_db->execute()) {
                throw new Exception('Failed to delete old images from DB: ' . $stmt_delete_images_db->error);
            }
            $stmt_delete_images_db->close();

            // Delete actual image files from server
            foreach ($images_to_delete as $img_id) {
                $file_path = $existing_images_db[$img_id];
                if (file_exists('../../' . $file_path)) { // Adjust path based on your file structure
                    unlink('../../' . $file_path);
                }
            }
        }

        // Handle new image uploads
        if (!empty($new_images['name'][0])) { // Check if new images were actually uploaded
            $image_count = count($new_images['name']);
            $current_total_images = count($existing_images_to_keep); // Images kept from previous state

            if (($current_total_images + $image_count) > 5) {
                throw new Exception('You can upload a maximum of 5 images per product (including existing ones).');
            }

            $stmt_insert_image = $conn->prepare("INSERT INTO product_images (product_id, image_path, is_main) VALUES (?, ?, ?)");
            if (!$stmt_insert_image) {
                throw new Exception('Failed to prepare new image insert statement: ' . $conn->error);
            }

            foreach ($new_images['name'] as $key => $image_name) {
                $file_tmp = $new_images['tmp_name'][$key];
                $file_ext = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

                if (!in_array($file_ext, $allowed_extensions)) {
                    throw new Exception("Invalid image file type for {$image_name}. Only JPG, JPEG, PNG, GIF are allowed.");
                }

                $new_file_name = uniqid('prod_img_') . '.' . $file_ext;
                $target_file = $upload_dir . $new_file_name;
                $db_path = 'uploads/product_images/' . $new_file_name; // Path to store in DB

                if (move_uploaded_file($file_tmp, $target_file)) {
                    $is_main = ($current_total_images + $key == 0) ? 1 : 0; // First new image if no existing or if no existing images were kept
                    $stmt_insert_image->bind_param("isi", $product_id, $db_path, $is_main);
                    if (!$stmt_insert_image->execute()) {
                        throw new Exception('Failed to insert new image into DB: ' . $stmt_insert_image->error);
                    }
                } else {
                    throw new Exception("Failed to upload image: {$image_name}.");
                }
            }
            $stmt_insert_image->close();
        }

        // If all successful, commit transaction
        $conn->commit();
        $response['success'] = true;
        $response['message'] = 'Product updated successfully!';
        logAdminActivity($conn, $admin_id_performing_action, 'UPDATE_PRODUCT', "Updated product: {$name} (ID: {$product_id})");

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $response['message'] = 'Product update failed: ' . $e->getMessage();
        error_log("Product update failed for ID {$product_id}: " . $e->getMessage()); // Log error to PHP error log
    }

    $conn->close();

} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>