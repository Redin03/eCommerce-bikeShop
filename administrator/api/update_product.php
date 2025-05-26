<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$logFile = __DIR__ . '/../../logs/php_errors.log';
if (!is_dir(dirname($logFile))) {
    // Attempt to create directory with more permissive rights, then restrict
    if (!mkdir(dirname($logFile), 0777, true) && !is_dir(dirname($logFile))) {
        // Failed to create, and it still doesn't exist
        error_log("Failed to create log directory: " . dirname($logFile));
        // Fallback or exit - but for logging, we'll just try to use a different log path if possible
    }
    // After creation, try to set to desired permissions (0775)
    chmod(dirname($logFile), 0775);
}
ini_set('error_log', $logFile);

require_once __DIR__ . '/../../config/db.php';

$redirectUrl = $_POST['redirect_url'] ?? '../submenu/products.php';

function set_toast_and_redirect($message, $type, $url, $conn = null) { // Made $conn optional
    $_SESSION['toast_message'] = $message;
    $_SESSION['toast_type'] = $type;
    if ($conn && $conn->ping()) { // Use ping to check if connection is still alive
        $conn->close();
    }
    header('Location: ' . $url);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("Invalid request method detected: " . $_SERVER['REQUEST_METHOD']);
    set_toast_and_redirect('Invalid request method.', 'danger', $redirectUrl); // Pass null conn
}

$productId = $_POST['productId'] ?? null;
$productName = $_POST['productName'] ?? '';
$categoryKey = $_POST['categoryKey'] ?? '';
$subcategoryKey = $_POST['subcategoryKey'] ?? '';
$description = $_POST['description'] ?? '';
$variations = $_POST['variations'] ?? [];
$imagesToDelete = $_POST['imagesToDelete'] ?? ''; // This will be a comma-separated string of IDs

error_log("Received POST data for update: " . print_r($_POST, true));
error_log("Received FILES data for update: " . print_r($_FILES, true));

if (empty($productId) || !is_numeric($productId) || empty($productName) || empty($categoryKey) || empty($subcategoryKey)) {
    error_log("Validation failed: Missing or invalid product details for update. ID: '$productId', Name: '$productName', Cat: '$categoryKey', SubCat: '$subcategoryKey'");
    set_toast_and_redirect('Missing or invalid product details for update. Please fill all required fields correctly.', 'danger', $redirectUrl, $conn);
}

$productName = htmlspecialchars(trim($productName), ENT_QUOTES, 'UTF-8');
$description = htmlspecialchars(trim($description), ENT_QUOTES, 'UTF-8');
$productId = intval($productId);


$conn->begin_transaction();

try {
    // 1. Update Product Details
    $stmt = $conn->prepare("UPDATE products SET name = ?, category_key = ?, subcategory_key = ?, description = ? WHERE id = ?");
    if (!$stmt) {
        throw new Exception("Prepare statement failed for product update: " . $conn->error);
    }
    $stmt->bind_param("ssssi", $productName, $categoryKey, $subcategoryKey, $description, $productId);
    if (!$stmt->execute()) {
        throw new Exception("Execute statement failed for product update: " . $stmt->error);
    }
    $stmt->close();
    error_log("Product (ID: $productId) details updated.");

    // 2. Handle Product Variations
    // Fetch existing variations for this product
    $existingVariations = [];
    $stmt = $conn->prepare("SELECT id, color_name, size_name, quantity, price FROM product_variations WHERE product_id = ?");
    if (!$stmt) {
        throw new Exception("Prepare statement failed to fetch existing variations: " . $conn->error);
    }
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $existingVariations[$row['color_name'] . '|' . $row['size_name']] = $row;
    }
    $stmt->close();
    error_log("Existing variations fetched for product ID: $productId. Count: " . count($existingVariations));

    $variationsToInsert = [];
    $variationsToUpdate = [];
    $foundVariations = []; // To track variations that are still present in the POST data

    foreach ($variations as $variation) {
        $color = htmlspecialchars(trim($variation['color'] ?? ''), ENT_QUOTES, 'UTF-8');
        $size = htmlspecialchars(trim($variation['size'] ?? ''), ENT_QUOTES, 'UTF-8');
        $quantity = intval($variation['quantity'] ?? 0);
        $price = floatval($variation['price'] ?? 0); // Ensure price is included

        if (empty($color) || empty($size) || $quantity < 0 || $price < 0) {
            error_log("Skipping invalid variation entry during update: Color: '$color', Size: '$size', Quantity: '$quantity', Price: '$price'");
            continue;
        }

        $key = $color . '|' . $size;
        $foundVariations[$key] = true;

        if (isset($existingVariations[$key])) {
            // Variation exists, check if needs update
            $existing = $existingVariations[$key];
            // Compare as strings to avoid floating point precision issues if needed, or cast to float
            if ($existing['quantity'] != $quantity || (float)$existing['price'] != $price) {
                $variationsToUpdate[] = [
                    'id' => $existing['id'],
                    'quantity' => $quantity,
                    'price' => $price
                ];
            }
        } else {
            // New variation
            $variationsToInsert[] = [
                'color_name' => $color,
                'size_name' => $size,
                'quantity' => $quantity,
                'price' => $price
            ];
        }
    }

    // Delete variations not found in the submitted data
    $idsToDelete = [];
    foreach ($existingVariations as $key => $existing) {
        if (!isset($foundVariations[$key])) {
            $idsToDelete[] = $existing['id'];
        }
    }

    if (!empty($idsToDelete)) {
        $placeholders = implode(',', array_fill(0, count($idsToDelete), '?'));
        $stmtDelete = $conn->prepare("DELETE FROM product_variations WHERE id IN ($placeholders)");
        if (!$stmtDelete) {
            throw new Exception("Prepare statement failed for variation deletion: " . $conn->error);
        }
        $types = str_repeat('i', count($idsToDelete));
        $stmtDelete->bind_param($types, ...$idsToDelete);
        if (!$stmtDelete->execute()) {
            throw new Exception("Execute statement failed for variation deletion: " . $stmtDelete->error);
        }
        $stmtDelete->close();
        error_log("Deleted variations: " . implode(', ', $idsToDelete));
    }

    // Update existing variations
    if (!empty($variationsToUpdate)) {
        $stmtUpdate = $conn->prepare("UPDATE product_variations SET quantity = ?, price = ? WHERE id = ?");
        if (!$stmtUpdate) {
            throw new Exception("Prepare statement failed for variation update: " . $conn->error);
        }
        foreach ($variationsToUpdate as $var) {
            $stmtUpdate->bind_param("idi", $var['quantity'], $var['price'], $var['id']);
            if (!$stmtUpdate->execute()) {
                throw new Exception("Failed to update variation ID " . $var['id'] . ": " . $stmtUpdate->error);
            }
        }
        $stmtUpdate->close();
        error_log("Updated " . count($variationsToUpdate) . " variations.");
    }

    // Insert new variations
    if (!empty($variationsToInsert)) {
        $stmtInsert = $conn->prepare("INSERT INTO product_variations (product_id, color_name, size_name, quantity, price) VALUES (?, ?, ?, ?, ?)");
        if (!$stmtInsert) {
            throw new Exception("Prepare statement failed for new variation insertion: " . $conn->error);
        }
        foreach ($variationsToInsert as $var) {
            $stmtInsert->bind_param("issid", $productId, $var['color_name'], $var['size_name'], $var['quantity'], $var['price']);
            if (!$stmtInsert->execute()) {
                throw new Exception("Failed to insert new variation (color: " . $var['color_name'] . ", size: " . $var['size_name'] . "): " . $stmtInsert->error);
            }
        }
        $stmtInsert->close();
        error_log("Inserted " . count($variationsToInsert) . " new variations.");
    }
    error_log("All variations processed for product ID: " . $productId);


    // 3. Handle Product Images
    $uploadDir = __DIR__ . '/../../uploads/products/';
    $relativePathBase = 'uploads/products/';

    if (!is_dir($uploadDir)) {
        // Attempt to create directory with more permissive rights, then restrict
        if (!mkdir($uploadDir, 0777, true) && !is_dir($uploadDir)) {
            throw new Exception('Failed to create upload directory: ' . $uploadDir . '. Check permissions.');
        }
        chmod($uploadDir, 0775); // Set desired permissions after creation
        error_log("Created upload directory: " . $uploadDir);
    }

    // Delete marked images
    if (!empty($imagesToDelete)) {
        $idsToDeleteArray = array_map('intval', explode(',', $imagesToDelete));
        // Fetch paths to delete files from disk
        // Using `?` placeholders for security to prevent SQL injection
        $placeholders = implode(',', array_fill(0, count($idsToDeleteArray), '?'));
        $stmt = $conn->prepare("SELECT image_path FROM product_images WHERE id IN ($placeholders)");
        if (!$stmt) {
            throw new Exception("Prepare statement failed to fetch images for deletion: " . $conn->error);
        }
        $types = str_repeat('i', count($idsToDeleteArray));
        $stmt->bind_param($types, ...$idsToDeleteArray);
        $stmt->execute();
        $result = $stmt->get_result();
        $pathsToDeleteFromDisk = [];
        while ($row = $result->fetch_assoc()) {
            $pathsToDeleteFromDisk[] = __DIR__ . '/../../' . $row['image_path'];
        }
        $stmt->close();

        // Delete from database
        $stmtDelete = $conn->prepare("DELETE FROM product_images WHERE id IN ($placeholders)");
        if (!$stmtDelete) {
            throw new Exception("Prepare statement failed for image deletion: " . $conn->error);
        }
        $stmtDelete->bind_param($types, ...$idsToDeleteArray);
        if (!$stmtDelete->execute()) {
            throw new Exception("Execute statement failed for image deletion: " . $stmtDelete->error);
        }
        $stmtDelete->close();
        error_log("Deleted image records from DB: " . implode(', ', $idsToDeleteArray));

        // Delete files from disk
        foreach ($pathsToDeleteFromDisk as $path) {
            // Check if the path is within the allowed upload directory to prevent directory traversal
            if (strpos(realpath($path), realpath($uploadDir)) === 0) {
                if (file_exists($path)) {
                    if (unlink($path)) {
                        error_log("Deleted image file from disk: " . $path);
                    } else {
                        error_log("Failed to delete image file from disk: " . $path . ". Check permissions.");
                    }
                } else {
                    error_log("Image file not found on disk, skipping deletion: " . $path);
                }
            } else {
                error_log("Security Alert: Attempted to delete file outside upload directory: " . $path);
            }
        }
    }


    // Add new images (if any)
    // Check if $_FILES['productImages'] exists and if any files were actually uploaded
    if (isset($_FILES['productImages']) && is_array($_FILES['productImages']['name']) && array_filter($_FILES['productImages']['name'])) {
        $totalFiles = count($_FILES['productImages']['name']);
        $stmtImages = $conn->prepare("INSERT INTO product_images (product_id, image_path) VALUES (?, ?)");
        if (!$stmtImages) {
            throw new Exception("Prepare statement failed for new images insertion: " . $conn->error);
        }

        for ($i = 0; $i < $totalFiles; $i++) {
            // Ensure the file was genuinely uploaded via HTTP POST and no errors occurred
            if (isset($_FILES['productImages']['tmp_name'][$i]) && $_FILES['productImages']['error'][$i] === UPLOAD_ERR_OK) {
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

                    // Move the uploaded file
                    if (move_uploaded_file($fileTmpName, $destinationPath)) {
                        $stmtImages->bind_param("is", $productId, $relativePathForDb);
                        if (!$stmtImages->execute()) {
                            error_log("Failed to insert new image path into DB for '$newFileName': " . $stmtImages->error);
                        } else {
                            error_log("New image uploaded and path saved: " . $relativePathForDb);
                        }
                    } else {
                        error_log("Failed to move new uploaded file '$fileName' to '$destinationPath'. Check folder permissions.");
                    }
                } else {
                    error_log("Security Alert: Disallowed file type '$mimeType' attempted during update for product ID $productId. Filename: $fileName");
                }
            } else {
                // Log specific upload errors, but ignore UPLOAD_ERR_NO_FILE as it's not an error if no new files were intended
                if (isset($_FILES['productImages']['error'][$i]) && $_FILES['productImages']['error'][$i] !== UPLOAD_ERR_NO_FILE) {
                    error_log("File upload error for new image at index $i (filename: " . ($_FILES['productImages']['name'][$i] ?? 'N/A') . "). PHP error code: " . $_FILES['productImages']['error'][$i] . ". Check php.ini upload limits and post_max_size.");
                }
            }
        }
        $stmtImages->close();
    }
    error_log("All images processed for product ID: " . $productId);

    // Log the successful product update
    $logFile = __DIR__ . '/../../logs/user_actions.log';
    $logMessage = "[" . date('Y-m-d H:i:s') . "] User updated product: '$productName' (ID: $productId).";
    if (!is_dir(dirname($logFile))) {
        mkdir(dirname($logFile), 0775, true);
    }
    file_put_contents($logFile, $logMessage . PHP_EOL, FILE_APPEND);
    error_log("User action logged: " . $logMessage);

    $conn->commit();
    set_toast_and_redirect('Product updated successfully!', 'success', $redirectUrl, $conn);

} catch (Exception $e) {
    $conn->rollback();
    error_log("Transaction rolled back for update. Error: " . $e->getMessage());
    set_toast_and_redirect('Error updating product: ' . $e->getMessage(), 'danger', $redirectUrl, $conn);
} finally {
    if ($conn && $conn->ping()) { // Ensure connection is still active before closing
        $conn->close();
        error_log("Database connection closed.");
    }
}
?>