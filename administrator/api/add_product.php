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
$price = $_POST['price'] ?? '';
$description = $_POST['description'] ?? '';
$variations = $_POST['variations'] ?? [];

error_log("Received POST data: " . print_r($_POST, true));
error_log("Received FILES data: " . print_r($_FILES, true));

if (empty($productName) || empty($categoryKey) || empty($subcategoryKey) || !is_numeric($price) || $price <= 0) {
    error_log("Validation failed: Missing or invalid product details. Name: '$productName', Cat: '$categoryKey', SubCat: '$subcategoryKey', Price: '$price'");
    set_toast_and_redirect('Missing or invalid product details. Please fill all required fields correctly.', 'danger', $redirectUrl, $conn);
}

if (empty($variations)) {
    error_log("Validation failed: No product variations provided.");
    set_toast_and_redirect('Please add at least one product variation (color, size, quantity).', 'danger', $redirectUrl, $conn);
}

$productName = htmlspecialchars(trim($productName), ENT_QUOTES, 'UTF-8');
$description = htmlspecialchars(trim($description), ENT_QUOTES, 'UTF-8');
$price = floatval($price);

$conn->begin_transaction();

try {
    $stmt = $conn->prepare("INSERT INTO products (name, category_key, subcategory_key, price, description) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) {
        throw new Exception("Prepare statement failed for products: " . $conn->error);
    }
    $stmt->bind_param("sssis", $productName, $categoryKey, $subcategoryKey, $price, $description);
    if (!$stmt->execute()) {
        throw new Exception("Execute statement failed for products: " . $stmt->error);
    }
    $productId = $conn->insert_id;
    $stmt->close();
    error_log("Product inserted, ID: " . $productId);

    $stmtVariations = $conn->prepare("INSERT INTO product_variations (product_id, color_name, size_name, quantity) VALUES (?, ?, ?, ?)");
    if (!$stmtVariations) {
        throw new Exception("Prepare statement failed for variations: " . $conn->error);
    }
    foreach ($variations as $variation) {
        $color = htmlspecialchars(trim($variation['color'] ?? ''), ENT_QUOTES, 'UTF-8');
        $size = htmlspecialchars(trim($variation['size'] ?? ''), ENT_QUOTES, 'UTF-8');
        $quantity = intval($variation['quantity'] ?? 0);

        if (empty($color) || empty($size) || $quantity < 0) {
            error_log("Skipping invalid variation entry: Color: '$color', Size: '$size', Quantity: '$quantity'");
            continue;
        }

        $stmtVariations->bind_param("issi", $productId, $color, $size, $quantity);
        if (!$stmtVariations->execute()) {
            if ($conn->errno === 1062) {
                error_log("Duplicate variation (color: $color, size: $size) skipped for product $productId.");
            } else {
                throw new Exception("Failed to insert variation (color: $color, size: $size, qty: $quantity) for product $productId: " . $stmtVariations->error);
            }
        }
    }
    $stmtVariations->close();
    error_log("Variations inserted/processed for product ID: " . $productId);

    $uploadDir = __DIR__ . '/../../uploads/products/';
    $relativePathBase = 'uploads/products/';

    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0775, true)) {
            throw new Exception('Failed to create upload directory: ' . $uploadDir . '. Check permissions.');
        }
        error_log("Created upload directory: " . $uploadDir);
    }

    if (isset($_FILES['productImages']) && is_array($_FILES['productImages']['name'])) {
        $totalFiles = count($_FILES['productImages']['name']);
        $stmtImages = $conn->prepare("INSERT INTO product_images (product_id, image_path) VALUES (?, ?)");
        if (!$stmtImages) {
            throw new Exception("Prepare statement failed for images: " . $conn->error);
        }

        for ($i = 0; $i < $totalFiles; $i++) {
            if (!empty($_FILES['productImages']['tmp_name'][$i]) && $_FILES['productImages']['error'][$i] === UPLOAD_ERR_OK) {
                $fileName = $_FILES['productImages']['name'][$i];
                $fileTmpName = $_FILES['productImages']['tmp_name'][$i];

                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mimeType = $finfo->file($fileTmpName);
                $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

                if (in_array($mimeType, $allowedMimeTypes)) {
                    $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
                    $newFileName = uniqid('prod_') . '.' . strtolower($fileExt);
                    $destinationPath = $uploadDir . $newFileName;
                    $relativePathForDb = $relativePathBase . $newFileName;

                    if (move_uploaded_file($fileTmpName, $destinationPath)) {
                        $stmtImages->bind_param("is", $productId, $relativePathForDb);
                        if (!$stmtImages->execute()) {
                            error_log("Failed to insert image path into DB for '$newFileName': " . $stmtImages->error);
                        } else {
                            error_log("Image uploaded and path saved: " . $relativePathForDb);
                        }
                    } else {
                        error_log("Failed to move uploaded file '$fileName' to '$destinationPath'. Check folder permissions.");
                    }
                } else {
                    error_log("Security Alert: Disallowed file type '$mimeType' attempted for product ID $productId. Filename: $fileName");
                }
            } else {
                if ($_FILES['productImages']['error'][$i] !== UPLOAD_ERR_NO_FILE) {
                    error_log("File upload error for index $i (filename: " . $_FILES['productImages']['name'][$i] . "). PHP error code: " . $_FILES['productImages']['error'][$i] . ". Check php.ini upload limits.");
                } else {
                    error_log("No file uploaded for index $i.");
                }
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
    error_log("User action logged: " . $logMessage); // Also log to PHP error log for redundancy

    $conn->commit();
    set_toast_and_redirect('Product added successfully!', 'success', $redirectUrl, $conn);

} catch (Exception $e) {
    $conn->rollback();
    error_log("Transaction rolled back. Error: " . $e->getMessage());
    set_toast_and_redirect('Error adding product: ' . $e->getMessage(), 'danger', $redirectUrl, $conn);
} finally {
    if ($conn && !$conn->connect_error) {
        $conn->close();
        error_log("Database connection closed.");
    }
}
?>