<?php
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

$redirectUrl = $_POST['redirect_url'] ?? '../submenu/products.php';

function set_toast_and_redirect($message, $type, $url, $conn) {
    $_SESSION['toast_message'] = $message;
    $_SESSION['toast_type'] = $type;
    if ($conn && !$conn->connect_error) {
        $conn->close();
    }
    header('Location: ' . $url);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("Invalid request method detected: " . $_SERVER['REQUEST_METHOD']);
    set_toast_and_redirect('Invalid request method.', 'danger', $redirectUrl, $conn);
}

$productName = $_POST['productName'] ?? '';
$categoryKey = $_POST['categoryKey'] ?? '';
$subcategoryKey = $_POST['subcategoryKey'] ?? '';
// REMOVED: $price = $_POST['price'] ?? 0.00; // Price is now per variation
$description = $_POST['description'] ?? '';
$variations = $_POST['variations'] ?? [];

// Basic validation
if (empty($productName) || empty($categoryKey) || empty($subcategoryKey)) {
    set_toast_and_redirect('Product name, category, and subcategory are required.', 'danger', $redirectUrl, $conn);
}

// Validate if variations are provided if needed, or if a base price is expected
if (empty($variations)) {
    // You might want to enforce that at least one variation is added, or have a fallback base price
    set_toast_and_redirect('At least one product variation (with color, size, quantity, and price) is required.', 'danger', $redirectUrl, $conn);
}

$conn->begin_transaction();

try {
    // 1. Insert into products table
    // The 'price' column has been removed from the 'products' table.
    $stmtProduct = $conn->prepare("INSERT INTO products (name, category_key, subcategory_key, description) VALUES (?, ?, ?, ?)");
    if (!$stmtProduct) {
        throw new Exception("Prepare statement failed (products): " . $conn->error);
    }
    $stmtProduct->bind_param("ssss", $productName, $categoryKey, $subcategoryKey, $description);
    if (!$stmtProduct->execute()) {
        throw new Exception('Failed to add product: ' . $stmtProduct->error);
    }
    $productId = $conn->insert_id;
    $stmtProduct->close();
    error_log("Product '$productName' added with ID: $productId");

    // 2. Insert into product_variations table
    if (!empty($variations)) {
        $stmtVariations = $conn->prepare("INSERT INTO product_variations (product_id, color_name, size_name, quantity, price) VALUES (?, ?, ?, ?, ?)"); // NEW: Added price column
        if (!$stmtVariations) {
            throw new Exception("Prepare statement failed (variations): " . $conn->error);
        }
        foreach ($variations as $variation) {
            $color = trim($variation['color'] ?? '');
            $size = trim($variation['size'] ?? '');
            $quantity = intval($variation['quantity'] ?? 0);
            $variationPrice = floatval($variation['price'] ?? 0.00); // NEW: Get variation price

            if (empty($color) || empty($size) || $quantity < 0 || $variationPrice < 0) { // Added price validation
                // Skip invalid variations or log a warning
                error_log("Skipping invalid variation for product $productId: Color='$color', Size='$size', Quantity='$quantity', Price='$variationPrice'");
                continue;
            }

            $stmtVariations->bind_param("issid", $productId, $color, $size, $quantity, $variationPrice); // NEW: 'd' for double/float price
            if (!$stmtVariations->execute()) {
                throw new Exception("Failed to add variation for product $productId: " . $stmtVariations->error);
            }
        }
        $stmtVariations->close();
        error_log("Variations added for product ID: $productId");
    } else {
        error_log("No variations provided for product ID: $productId. This might be an error if variations are mandatory.");
    }

    // 3. Handle product images
    if (isset($_FILES['productImages']) && is_array($_FILES['productImages']['name'])) {
        $uploadDir = __DIR__ . '/../../uploads/products/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $stmtImages = $conn->prepare("INSERT INTO product_images (product_id, image_path) VALUES (?, ?)");
        if (!$stmtImages) {
            throw new Exception("Prepare statement failed (images): " . $conn->error);
        }

        for ($i = 0; $i < count($_FILES['productImages']['name']); $i++) {
            if ($_FILES['productImages']['error'][$i] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['productImages']['tmp_name'][$i];
                $fileName = basename($_FILES['productImages']['name'][$i]);
                $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $newFileName = uniqid('prod_') . '.' . $fileExt;
                $destPath = $uploadDir . $newFileName;
                $relativePath = 'uploads/products/' . $newFileName; // Path to store in DB

                if (move_uploaded_file($fileTmpPath, $destPath)) {
                    $stmtImages->bind_param("is", $productId, $relativePath);
                    if (!$stmtImages->execute()) {
                        error_log("Failed to insert image path into DB for product $productId: " . $stmtImages->error);
                    }
                } else {
                    error_log("Failed to move uploaded file from $fileTmpPath to $destPath.");
                }
            } else if ($_FILES['productImages']['error'][$i] !== UPLOAD_ERR_NO_FILE) {
                // Log other upload errors, but ignore UPLOAD_ERR_NO_FILE (no file selected)
                error_log("File upload error for index $i (filename: " . $_FILES['productImages']['name'][$i] . "). PHP error code: " . $_FILES['productImages']['error'][$i] . ". Check php.ini upload limits.");
            } else {
                error_log("No file uploaded for index $i."); // This can be removed if UPLOAD_ERR_NO_FILE is common
            }
        }
        $stmtImages->close();
    }
    error_log("All images processed for product ID: " . $productId);

    // Log the successful product addition
    $logFile = __DIR__ . '/../../logs/user_actions.log';
    $logMessage = "[" . date('Y-m-d H:i:s') . "] User added product: '$productName' (ID: $productId), Category: '$categoryKey', Subcategory: '$subcategoryKey'.";

    // Ensure the logs directory exists
    if (!is_dir(dirname($logFile))) {
        mkdir(dirname($logFile), 0775, true);
    }
    file_put_contents($logFile, $logMessage . PHP_EOL, FILE_APPEND);
    error_log("User action logged: " . $logMessage);

    $conn->commit();
    set_toast_and_redirect('Product added successfully!', 'success', $redirectUrl, $conn);

} catch (Exception $e) {
    $conn->rollback();
    error_log("Transaction rolled back. Error: " . $e->getMessage());
    set_toast_and_redirect('Error adding product: ' . $e->getMessage(), 'danger', $redirectUrl, $conn);
} finally {
    if (isset($conn) && $conn instanceof mysqli && !$conn->connect_error) {
        $conn->close();
    }
}
?>