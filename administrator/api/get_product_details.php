<?php
session_start();
header('Content-Type: application/json');

ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once '../../config/db.php';
require_once '../includes/logger.php';

$response = ['success' => false, 'message' => '', 'data' => null];

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    $response['message'] = 'Unauthorized access.';
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $product_id = filter_var($_GET['product_id'] ?? '', FILTER_VALIDATE_INT);

    if (!$product_id) {
        $response['message'] = 'Invalid product ID provided.';
        echo json_encode($response);
        exit;
    }

    $product_data = [];
    $variations = [];
    $images = [];

    // Fetch product general details
    $stmt_product = $conn->prepare("SELECT id, name, category, subcategory, description FROM products WHERE id = ?");
    if ($stmt_product) {
        $stmt_product->bind_param("i", $product_id);
        $stmt_product->execute();
        $result_product = $stmt_product->get_result();
        if ($result_product->num_rows > 0) {
            $product_data = $result_product->fetch_assoc();
        } else {
            $response['message'] = 'Product not found.';
            echo json_encode($response);
            $stmt_product->close();
            $conn->close();
            exit;
        }
        $stmt_product->close();
    } else {
        $response['message'] = 'Failed to prepare product statement: ' . $conn->error;
        echo json_encode($response);
        $conn->close();
        exit;
    }

    // Fetch product variations
    $stmt_variations = $conn->prepare("SELECT id, size, color, stock, price FROM product_variations WHERE product_id = ? ORDER BY id ASC");
    if ($stmt_variations) {
        $stmt_variations->bind_param("i", $product_id);
        $stmt_variations->execute();
        $result_variations = $stmt_variations->get_result();
        while ($row = $result_variations->fetch_assoc()) {
            $variations[] = $row;
        }
        $stmt_variations->close();
    } else {
        $response['message'] = 'Failed to prepare variations statement: ' . $conn->error;
        echo json_encode($response);
        $conn->close();
        exit;
    }

    // Fetch product images
    $stmt_images = $conn->prepare("SELECT id, image_path, is_main FROM product_images WHERE product_id = ? ORDER BY is_main DESC, id ASC");
    if ($stmt_images) {
        $stmt_images->bind_param("i", $product_id);
        $stmt_images->execute();
        $result_images = $stmt_images->get_result();
        while ($row = $result_images->fetch_assoc()) {
            $images[] = $row;
        }
        $stmt_images->close();
    } else {
        $response['message'] = 'Failed to prepare images statement: ' . $conn->error;
        echo json_encode($response);
        $conn->close();
        exit;
    }

    $response['success'] = true;
    $response['message'] = 'Product details fetched successfully.';
    $response['data'] = [
        'product' => $product_data,
        'variations' => $variations,
        'images' => $images
    ];

    $conn->close();

} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>