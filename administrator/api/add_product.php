<?php
session_start();
header('Content-Type: application/json');

// Disable error display to prevent HTML output breaking JSON response
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once '../../config/db.php'; // Adjust path to your database connection
require_once '../includes/logger.php'; // Adjust path to your logger function

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if admin is logged in
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        $response['message'] = 'Unauthorized access.';
        echo json_encode($response);
        exit;
    }

    $admin_id = $_SESSION['admin_id'] ?? 0; // Get the logged-in admin's ID

    // 1. Sanitize and Validate Product General Details
    $name = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $subcategory = trim($_POST['subcategory'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($name) || empty($category) || empty($subcategory) || empty($description)) {
        $response['message'] = 'Product name, category, sub-category, and description are required.';
        echo json_encode($response);
        exit;
    }

    // 2. Validate Variations
    $variations = $_POST['variations'] ?? [];
    if (empty($variations) || !is_array($variations)) {
        $response['message'] = 'At least one product variation is required.';
        echo json_encode($response);
        exit;
    }

    foreach ($variations as $index => $variation) {
        $size = trim($variation['size'] ?? '');
        $color = trim($variation['color'] ?? '');
        $stock = filter_var($variation['stock'] ?? '', FILTER_VALIDATE_INT);
        $price = filter_var($variation['price'] ?? '', FILTER_VALIDATE_FLOAT);

        if (empty($size) || empty($color) || $stock === false || $stock < 0 || $price === false || $price < 0) {
            $response['message'] = "Invalid or missing data for variation #" . ($index + 1) . ". Ensure size, color, valid stock (non-negative integer), and valid price (non-negative number) are provided.";
            echo json_encode($response);
            exit;
        }
    }

    // 3. Handle File Uploads
    $uploaded_images = $_FILES['product_images'] ?? [];
    $image_paths = [];
    $upload_dir = '../../uploads/product_images/'; // Relative path from api/add_product.php

    // Create upload directory if it doesn't exist
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true); // 0777 permissions, recursive
    }

    // Process uploaded files if any
    if (isset($uploaded_images['name']) && is_array($uploaded_images['name']) && count($uploaded_images['name']) > 0) {
        $total_files = count($uploaded_images['name']);
        if ($total_files > 5) { // Limit to 5 images
            $response['message'] = 'You can upload a maximum of 5 images per product.';
            echo json_encode($response);
            exit;
        }

        for ($i = 0; $i < $total_files; $i++) {
            if ($uploaded_images['error'][$i] === UPLOAD_ERR_OK) {
                $file_name = $uploaded_images['name'][$i];
                $file_tmp_name = $uploaded_images['tmp_name'][$i];
                $file_size = $uploaded_images['size'][$i];
                $file_type = $uploaded_images['type'][$i];
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

                // Basic file validation
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                if (!in_array($file_ext, $allowed_extensions)) {
                    $response['message'] = "Invalid file type for " . htmlspecialchars($file_name) . ". Only JPG, JPEG, PNG, GIF are allowed.";
                    echo json_encode($response);
                    exit;
                }

                if ($file_size > 5 * 1024 * 1024) { // Max 5MB
                    $response['message'] = "File " . htmlspecialchars($file_name) . " is too large. Maximum size is 5MB.";
                    echo json_encode($response);
                    exit;
                }

                // Generate unique file name
                $new_file_name = uniqid('img_', true) . '.' . $file_ext;
                $target_file_path = $upload_dir . $new_file_name;

                if (move_uploaded_file($file_tmp_name, $target_file_path)) {
                    $image_paths[] = $target_file_path;
                } else {
                    $response['message'] = "Failed to upload image: " . htmlspecialchars($file_name);
                    echo json_encode($response);
                    exit;
                }
            } else if ($uploaded_images['error'][$i] !== UPLOAD_ERR_NO_FILE) {
                // Handle other upload errors besides no file being selected
                $response['message'] = "File upload error for " . htmlspecialchars($uploaded_images['name'][$i]) . ": Code " . $uploaded_images['error'][$i];
                echo json_encode($response);
                exit;
            }
        }
    }


    // Start database transaction
    $conn->begin_transaction();

    try {
        // 4. Insert into products table
        $stmt_product = $conn->prepare("INSERT INTO products (name, category, subcategory, description) VALUES (?, ?, ?, ?)");
        if (!$stmt_product) {
            throw new Exception("Product statement preparation failed: " . $conn->error);
        }
        $stmt_product->bind_param("ssss", $name, $category, $subcategory, $description);
        if (!$stmt_product->execute()) {
            throw new Exception("Product insertion failed: " . $stmt_product->error);
        }
        $product_id = $conn->insert_id;
        $stmt_product->close();

        // 5. Insert into product_variations table
        $stmt_variation = $conn->prepare("INSERT INTO product_variations (product_id, size, color, stock, price) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt_variation) {
            throw new Exception("Variation statement preparation failed: " . $conn->error);
        }
        foreach ($variations as $variation) {
            $size = trim($variation['size']);
            $color = trim($variation['color']);
            $stock = filter_var($variation['stock'], FILTER_VALIDATE_INT);
            $price = filter_var($variation['price'], FILTER_VALIDATE_FLOAT);

            $stmt_variation->bind_param("issid", $product_id, $size, $color, $stock, $price);
            if (!$stmt_variation->execute()) {
                throw new Exception("Variation insertion failed: " . $stmt_variation->error);
            }
        }
        $stmt_variation->close();

        // 6. Insert into product_images table
        if (!empty($image_paths)) {
            $stmt_image = $conn->prepare("INSERT INTO product_images (product_id, image_path, is_main) VALUES (?, ?, ?)");
            if (!$stmt_image) {
                throw new Exception("Image statement preparation failed: " . $conn->error);
            }
            foreach ($image_paths as $index => $path) {
                // Set the first image as main, others as not main (optional logic)
                $is_main = ($index === 0) ? 1 : 0;
                // Store relative path (e.g., uploads/product_images/img_uniqueid.jpg)
                $relative_path = str_replace('../../', '', $path);
                $stmt_image->bind_param("isi", $product_id, $relative_path, $is_main);
                if (!$stmt_image->execute()) {
                    throw new Exception("Image insertion failed: " . $stmt_image->error);
                }
            }
            $stmt_image->close();
        }

        // If all operations succeed, commit the transaction
        $conn->commit();

        $response['success'] = true;
        $response['message'] = 'Product "' . htmlspecialchars($name) . '" added successfully!';

        // Log the activity
        logAdminActivity(
            $conn,
            $admin_id,
            'ADD_PRODUCT',
            "Added new product: '{$name}' (ID: {$product_id})"
        );

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        // Delete any partially uploaded files if rollback occurs
        foreach ($image_paths as $path) {
            if (file_exists($path)) {
                unlink($path);
            }
        }
        $response['message'] = 'Error adding product: ' . $e->getMessage();
        // Log the error internally for debugging
        error_log("Error adding product: " . $e->getMessage());
    } finally {
        $conn->close();
    }

} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>