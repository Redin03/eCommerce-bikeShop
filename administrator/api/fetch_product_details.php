<?php
// administrator/api/fetch_product_details.php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$logFile = __DIR__ . '/../../logs/php_errors.log';
if (!is_dir(dirname($logFile))) {
    mkdir(dirname($logFile), 0775, true);
}
ini_set('error_log', $logFile);

require_once __DIR__ . '/../../config/db.php';

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => 'An unknown error occurred.',
    'data' => null
];

if (!isset($_GET['productId'])) {
    $response['message'] = 'Product ID is required.';
    echo json_encode($response);
    exit();
}

$productId = intval($_GET['productId']);
if ($productId <= 0) {
    $response['message'] = 'Invalid Product ID.';
    echo json_encode($response);
    exit();
}

try {
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception("Database connection not established or failed: " . ($conn->connect_error ?? 'Unknown error'));
    }

    $productData = [];

    // Fetch product details
    $stmt = $conn->prepare("SELECT id, name, category_key, subcategory_key, description FROM products WHERE id = ?");
    if (!$stmt) {
        throw new Exception("Prepare statement failed (product): " . $conn->error);
    }
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($productRow = $result->fetch_assoc()) {
        $productData = $productRow;
    } else {
        $response['message'] = 'Product not found.';
        echo json_encode($response);
        exit();
    }
    $stmt->close();

    // Fetch product variations
    $productData['variations'] = [];
    $stmt = $conn->prepare("SELECT color_name, size_name, quantity, price FROM product_variations WHERE product_id = ?");
    if (!$stmt) {
        throw new Exception("Prepare statement failed (variations): " . $conn->error);
    }
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($variationRow = $result->fetch_assoc()) {
        // Convert quantity and price to appropriate types if needed for JS (though JSON will usually handle numbers)
        $variationRow['quantity'] = (int)$variationRow['quantity'];
        $variationRow['price'] = (float)$variationRow['price'];
        $productData['variations'][] = $variationRow;
    }
    $stmt->close();

    // Fetch product images
    $productData['images'] = [];
    $stmt = $conn->prepare("SELECT id, image_path FROM product_images WHERE product_id = ?");
    if (!$stmt) {
        throw new Exception("Prepare statement failed (images): " . $conn->error);
    }
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($imageRow = $result->fetch_assoc()) {
        $productData['images'][] = $imageRow;
    }
    $stmt->close();

    $response['success'] = true;
    $response['message'] = 'Product data fetched successfully.';
    $response['data'] = $productData;

} catch (Exception $e) {
    error_log("Error fetching product details (ID: $productId): " . $e->getMessage());
    $response['message'] = 'Database error: ' . $e->getMessage();
    $response['success'] = false;
} finally {
    if (isset($conn) && $conn instanceof mysqli && !$conn->connect_error) {
        $conn->close();
    }
}

echo json_encode($response);
?>