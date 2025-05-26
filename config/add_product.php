<?php
// add_product.php

header('Content-Type: application/json');

// 1. Database Connection (replace with your actual connection)
$host = 'localhost';
$db   = 'bong_bike_shop';
$user = 'your_db_user'; // e.g., 'root'
$pass = 'your_db_password'; // e.g., 'password'
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}

// 2. Handle POST Request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productName = $_POST['productName'] ?? '';
    $category = $_POST['category'] ?? ''; // This is the key, not the full name
    $subcategory = $_POST['subcategory'] ?? ''; // This is the key
    $price = $_POST['price'] ?? 0;
    $stock = $_POST['stock'] ?? 0;
    $description = $_POST['description'] ?? '';
    $colors = json_decode($_POST['colors'] ?? '[]', true); // Decode JSON string
    $sizes = json_decode($_POST['sizes'] ?? '[]', true);   // Decode JSON string

    // Basic validation
    if (empty($productName) || empty($category) || empty($subcategory) || $price <= 0 || $stock < 0) {
        echo json_encode(['success' => false, 'message' => 'Missing required product fields or invalid values.']);
        exit();
    }

    $pdo->beginTransaction();

    try {
        // 3. Insert into products table
        $stmt = $pdo->prepare("INSERT INTO products (name, category_key, subcategory_key, price, stock_quantity, description) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$productName, $category, $subcategory, $price, $stock, $description]);
        $productId = $pdo->lastInsertId();

        // 4. Handle Colors
        if (!empty($colors)) {
            $stmtColors = $pdo->prepare("INSERT INTO product_colors (product_id, color_name) VALUES (?, ?)");
            foreach ($colors as $color) {
                $stmtColors->execute([$productId, $color]);
            }
        }

        // 5. Handle Sizes
        if (!empty($sizes)) {
            $stmtSizes = $pdo->prepare("INSERT INTO product_sizes (product_id, size_name) VALUES (?, ?)");
            foreach ($sizes as $size) {
                $stmtSizes->execute([$productId, $size]);
            }
        }

        // 6. Handle Image Uploads
        $uploadDir = '../uploads/products/'; // Make sure this directory exists and is writable!
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true); // Create directory if it doesn't exist
        }

        if (isset($_FILES['productImages']) && is_array($_FILES['productImages']['name'])) {
            $stmtImages = $pdo->prepare("INSERT INTO product_images (product_id, image_path) VALUES (?, ?)");
            $totalFiles = count($_FILES['productImages']['name']);

            for ($i = 0; $i < $totalFiles; $i++) {
                $fileName = $_FILES['productImages']['name'][$i];
                $fileTmpName = $_FILES['productImages']['tmp_name'][$i];
                $fileError = $_FILES['productImages']['error'][$i];
                $fileType = $_FILES['productImages']['type'][$i];
                $fileSize = $_FILES['productImages']['size'][$i]; // Not strictly needed for this example, but useful for validation

                // Validate file type and size (important for security)
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                if ($fileError === UPLOAD_ERR_OK && in_array($fileType, $allowedTypes)) {
                    // Generate a unique file name to prevent overwrites
                    $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
                    $newFileName = uniqid('prod_') . '.' . $fileExt;
                    $uploadPath = $uploadDir . $newFileName;
                    $relativePath = 'uploads/products/' . $newFileName; // Path to store in DB

                    if (move_uploaded_file($fileTmpName, $uploadPath)) {
                        $stmtImages->execute([$productId, $relativePath]);
                    } else {
                        // Log error: Failed to move uploaded file
                        error_log("Failed to move uploaded file: $fileTmpName to $uploadPath");
                    }
                } else {
                    // Log error: Invalid file or upload error
                    error_log("Invalid file or upload error for: $fileName, Error: $fileError, Type: $fileType");
                }
            }
        }

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Product added successfully!', 'productId' => $productId]);

    } catch (\PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Failed to add product: ' . $e->getMessage()]);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>