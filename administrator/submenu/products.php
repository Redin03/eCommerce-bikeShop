<?php
session_start();
// Check if the admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    $_SESSION['login_message'] = [
        'text' => 'Please log in to access this page.',
        'type' => 'info'
    ];
    header('Location: ../login.php');
    exit;
}

require_once '../../config/db.php'; // Adjust path if necessary
require_once '../includes/logger.php'; // Adjust path if necessary

// --- Date Filtering Logic ---
$filter_start_date = $_GET['start_date'] ?? '';
$filter_end_date = $_GET['end_date'] ?? '';
// --- Category Filtering Logic (NEW) ---
$filter_category = $_GET['category'] ?? '';


// Fetch all products from the database for the main table
$sql = "
    SELECT
        p.id AS product_id,
        p.name AS product_name,
        p.category,
        p.subcategory,
        p.description,
        p.created_at,
        GROUP_CONCAT(DISTINCT CONCAT(pv.id, ':', pv.size, ':', pv.color, ':', pv.stock, ':', pv.price) ORDER BY pv.id ASC SEPARATOR '||') AS variations_data,
        GROUP_CONCAT(DISTINCT CONCAT(pi.id, ':', pi.image_path) ORDER BY pi.is_main DESC, pi.id ASC SEPARATOR '||') AS images_data
    FROM
        products p
    LEFT JOIN
        product_variations pv ON p.id = pv.product_id
    LEFT JOIN
        product_images pi ON p.id = pi.product_id
";

$conditions = [];
$params = [];
$types = '';

if (!empty($filter_start_date)) {
    $conditions[] = "p.created_at >= ?";
    $params[] = $filter_start_date . " 00:00:00"; // Start of the day
    $types .= 's';
}

if (!empty($filter_end_date)) {
    $conditions[] = "p.created_at <= ?";
    $params[] = $filter_end_date . " 23:59:59"; // End of the day
    $types .= 's';
}

// Add category filter condition (NEW)
if (!empty($filter_category)) {
    $conditions[] = "p.category = ?";
    $params[] = $filter_category;
    $types .= 's';
}


if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$sql .= " GROUP BY p.id ORDER BY p.created_at DESC"; // Order by most recent first

$products = [];
$stmt = $conn->prepare($sql);

if ($stmt) {
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $product = [
                'id' => $row['product_id'],
                'name' => htmlspecialchars($row['product_name']),
                'category' => htmlspecialchars($row['category']),
                'subcategory' => htmlspecialchars($row['subcategory']),
                'description' => htmlspecialchars($row['description']),
                'created_at' => htmlspecialchars($row['created_at']),
                'variations' => [],
                'images' => []
            ];

            // Parse variations data
            if (!empty($row['variations_data'])) {
                $variation_strings = explode('||', $row['variations_data']);
                foreach ($variation_strings as $v_str) {
                    list($id, $size, $color, $stock, $price) = explode(':', $v_str);
                    $product['variations'][] = [
                        'id' => htmlspecialchars($id),
                        'size' => htmlspecialchars($size),
                        'color' => htmlspecialchars($color),
                        'stock' => htmlspecialchars($stock),
                        'price' => htmlspecialchars(number_format($price, 2))
                    ];
                }
            }

            // Parse images data
            if (!empty($row['images_data'])) {
                $image_paths = explode('||', $row['images_data']);
                foreach ($image_paths as $img_str) {
                    list($id, $path) = explode(':', $img_str);
                    $product['images'][] = [
                        'id' => htmlspecialchars($id),
                        'path' => htmlspecialchars($path)
                    ];
                }
            }

            $products[] = $product;
        }
    }
    $result->free();
    $stmt->close();
} else {
    // Log database error
    error_log("Error preparing products fetch query: " . $conn->error);
}


// --- Low Stock Products Logic ---
$low_stock_products = [];
$low_stock_threshold = 10; // Define low stock threshold

$sql_low_stock = "
    SELECT
        p.id AS product_id,
        p.name AS product_name,
        p.category,
        p.subcategory,
        pv.id AS variation_id,
        pv.size,
        pv.color,
        pv.stock,
        pv.price,
        pi.image_path AS main_image_path
    FROM
        products p
    JOIN
        product_variations pv ON p.id = pv.product_id
    LEFT JOIN
        product_images pi ON p.id = pi.product_id AND pi.is_main = 1
    WHERE
        pv.stock <= ?
    ORDER BY
        p.name ASC, pv.size ASC, pv.color ASC
";

$stmt_low_stock = $conn->prepare($sql_low_stock);
if ($stmt_low_stock) {
    $stmt_low_stock->bind_param('i', $low_stock_threshold);
    $stmt_low_stock->execute();
    $result_low_stock = $stmt_low_stock->get_result();

    if ($result_low_stock && $result_low_stock->num_rows > 0) {
        while ($row_low_stock = $result_low_stock->fetch_assoc()) {
            $low_stock_products[] = [
                'product_id' => htmlspecialchars($row_low_stock['product_id']),
                'product_name' => htmlspecialchars($row_low_stock['product_name']),
                'category' => htmlspecialchars($row_low_stock['category']),
                'subcategory' => htmlspecialchars($row_low_stock['subcategory']),
                'variation_id' => htmlspecialchars($row_low_stock['variation_id']),
                'size' => htmlspecialchars($row_low_stock['size']),
                'color' => htmlspecialchars($row_low_stock['color']),
                'stock' => htmlspecialchars($row_low_stock['stock']),
                'price' => htmlspecialchars(number_format($row_low_stock['price'], 2)),
                'image_path' => htmlspecialchars($row_low_stock['main_image_path'] ?? 'path/to/default/image.jpg') // Provide a default if no main image
            ];
        }
    }
    $result_low_stock->free();
    $stmt_low_stock->close();
} else {
    error_log("Error preparing low stock query: " . $conn->error);
}

// --- Stock History Logic ---
$stock_history_records = [];
// This query assumes you have a `stock_history` table as described above
$sql_stock_history = "
    SELECT
        sh.id AS history_id,
        p.name AS product_name,
        pv.size,
        pv.color,
        sh.quantity_changed,
        sh.change_type,
        sh.changed_at
        -- Optional: Add admin_id if you want to show who made the change
        -- a.username AS admin_username
    FROM
        stock_history sh
    JOIN
        products p ON sh.product_id = p.id
    JOIN
        product_variations pv ON sh.variation_id = pv.id
    WHERE
        sh.quantity_changed > 0 -- Only show added stock for this view
    ORDER BY
        sh.changed_at DESC
    LIMIT 100; -- Limit to recent 100 records for performance
";

$stmt_stock_history = $conn->prepare($sql_stock_history);
if ($stmt_stock_history) {
    $stmt_stock_history->execute();
    $result_stock_history = $stmt_stock_history->get_result();

    if ($result_stock_history && $result_stock_history->num_rows > 0) {
        while ($row_history = $result_stock_history->fetch_assoc()) {
            $stock_history_records[] = [
                'history_id' => htmlspecialchars($row_history['history_id']),
                'product_name' => htmlspecialchars($row_history['product_name']),
                'size' => htmlspecialchars($row_history['size']),
                'color' => htmlspecialchars($row_history['color']),
                'quantity_changed' => htmlspecialchars($row_history['quantity_changed']),
                'change_type' => htmlspecialchars($row_history['change_type']),
                'changed_at' => htmlspecialchars($row_history['changed_at']),
                // 'admin_username' => htmlspecialchars($row_history['admin_username'] ?? 'N/A') // Uncomment if joining with admins table
            ];
        }
    }
    $result_stock_history->free();
    $stmt_stock_history->close();
} else {
    error_log("Error preparing stock history query: " . $conn->error);
}

$conn->close();

?>

<h2 class="mb-4">Product Management</h2>
<div class="card shadow-sm mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Filter Products</span>
    </div>
    <div class="card-body">
        <form id="productsFilterForm" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="productStartDate" class="form-label">From Date (Added On):</label>
                <input type="date" class="form-control" id="productStartDate" name="start_date" value="<?php echo htmlspecialchars($filter_start_date); ?>">
            </div>
            <div class="col-md-4">
                <label for="productEndDate" class="form-label">To Date (Added On):</label>
                <input type="date" class="form-control" id="productEndDate" name="end_date" value="<?php echo htmlspecialchars($filter_end_date); ?>">
            </div>
            <div class="col-md-4">
                <label for="productCategoryFilter" class="form-label">Category:</label>
                <select class="form-select" id="productCategoryFilter" name="category">
                    <option value="">All Categories</option>
                    <option value="Bikes" <?php echo ($filter_category === 'Bikes') ? 'selected' : ''; ?>>Bikes</option>
                    <option value="Accessories" <?php echo ($filter_category === 'Accessories') ? 'selected' : ''; ?>>Accessories</option>
                    <option value="Parts & Components" <?php echo ($filter_category === 'Parts & Components') ? 'selected' : ''; ?>>Parts & Components</option>
                    <option value="Apparel" <?php echo ($filter_category === 'Apparel') ? 'selected' : ''; ?>>Apparel</option>
                </select>
            </div>
            <div class="col-md-auto">
                <button type="submit" class="btn btn-accent"><i class="bi bi-funnel me-2"></i>Apply Filter</button>
                <button type="submit" class="btn btn-secondary ms-2" id="resetProductsFilterBtn"><i class="bi bi-arrow-counterclockwise me-2"></i>Reset Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Products Overview</span>
        <div>
            <button class="btn btn-warning me-2" data-bs-toggle="modal" data-bs-target="#lowStockAlertModal">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>Low Stock Alert (<?= count($low_stock_products) ?>)
            </button>
            <button class="btn btn-info me-2" data-bs-toggle="modal" data-bs-target="#stockHistoryModal">
                <i class="bi bi-clock-history me-2"></i>Stock History (<?= count($stock_history_records) ?>)
            </button>
            <button class="btn btn-accent" data-bs-toggle="modal" data-bs-target="#addProductModal">
                <i class="bi bi-plus-circle me-2"></i>Add New Product
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Sub-Category</th>
                        <th>Variations</th>
                        <th>Added On</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($products)): ?>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?= $product['id'] ?></td>
                                <td>
                                    <?php if (!empty($product['images'])): ?>
                                        <img src="../<?= $product['images'][0]['path'] ?>" alt="<?= $product['name'] ?>" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                                        <?php if (count($product['images']) > 1): ?>
                                            <span class="badge bg-secondary">+<?= count($product['images']) - 1 ?></span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        No Image
                                    <?php endif; ?>
                                </td>
                                <td><?= $product['name'] ?></td>
                                <td><?= $product['category'] ?></td>
                                <td><?= $product['subcategory'] ?></td>
                                <td>
                                    <?php if (!empty($product['variations'])): ?>
                                        <ul class="list-unstyled mb-0 small">
                                            <?php foreach ($product['variations'] as $index => $variation): ?>
                                                <li>
                                                    <strong><?= $variation['size'] ?></strong> / <?= $variation['color'] ?>: (Stock: <?= $variation['stock'] ?>, Price: ₱<?= $variation['price'] ?>)
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php else: ?>
                                        No Variations
                                    <?php endif; ?>
                                </td>
                                <td><?= date('Y-m-d', strtotime($product['created_at'])) ?></td>
                                <td>
                                    <button class="btn btn-sm btn-warning me-2 edit-product-btn mb-2" data-bs-toggle="modal" data-bs-target="#editProductModal" data-product-id="<?= $product['id'] ?>">
                                        <i class="bi bi-pencil"></i> Edit
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-product-btn" data-bs-toggle="modal" data-bs-target="#deleteProductModal" data-product-id="<?= $product['id'] ?>">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">No products found for the selected filter criteria.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addProductModalLabel">Add New Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form data-api-endpoint="api/add_product.php" enctype="multipart/form-data">
                <div class="modal-body">
                    <h6 class="mb-3">Product Details</h6>
                    <div class="mb-3">
                        <label for="productNameInput" class="form-label">Product Name</label>
                        <input type="text" class="form-control" id="productNameInput" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="productCategory" class="form-label">Category</label>
                        <select class="form-select" id="productCategory" name="category" required>
                            <option value="">Select Category</option>
                            <option value="Bikes">Bikes</option>
                            <option value="Accessories">Accessories</option>
                            <option value="Parts & Components">Parts & Components</option>
                            <option value="Apparel">Apparel</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="productSubCategory" class="form-label">Sub-Category</label>
                        <select class="form-select" id="productSubCategory" name="subcategory" required disabled>
                            <option value="">Select Sub-Category</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="productDescriptionInput" class="form-label">Description</label>
                        <textarea class="form-control" id="productDescriptionInput" name="description" rows="3" required></textarea>
                    </div>

                    <h6 class="mt-4 mb-3">Product Variations</h6>
                    <div id="productVariationsContainer">
                        <div class="card mb-2 product-variation-card">
                            <div class="card-body">
                                <h7 class="card-title text-muted">Variation #1</h7>
                                <button type="button" class="btn-close float-end remove-variation-btn" aria-label="Remove variation"></button>
                                <div class="row g-2">
                                    <div class="col-md-3">
                                        <label for="variationSize_0" class="form-label">Size</label>
                                        <input type="text" class="form-control" id="variationSize_0" name="variations[0][size]" placeholder="e.g., Small, 27.5in" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="variationColor_0" class="form-label">Color</label>
                                        <input type="text" class="form-control" id="variationColor_0" name="variations[0][color]" placeholder="e.g., Red, Blue" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="variationStock_0" class="form-label">Stock</label>
                                        <input type="number" class="form-control" id="variationStock_0" name="variations[0][stock]" min="0" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="variationPrice_0" class="form-label">Price</label>
                                        <input type="number" class="form-control" id="variationPrice_0" name="variations[0][price]" step="0.01" min="0" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="button" id="addVariationBtn" class="btn btn-sm btn-outline-secondary mt-3">
                        <i class="bi bi-plus-circle me-2"></i>Add Another Variation
                    </button>

                    <h6 class="mt-4 mb-3">Product Images</h6>
                    <div class="mb-3">
                        <label for="productImagesInput" class="form-label">Upload Images (Max 5)</label>
                        <input type="file" class="form-control" id="productImagesInput" name="product_images[]" accept="image/*" multiple>
                        <small class="text-muted">You can select multiple image files (JPG, PNG, GIF).</small>
                    </div>

                    <div data-form-message class="mt-3"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-accent">Add Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProductModalLabel">Edit Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form data-api-endpoint="api/update_product.php" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" id="editProductId" name="product_id">

                    <h6 class="mb-3">Product Details</h6>
                    <div class="mb-3">
                        <label for="editProductNameInput" class="form-label">Product Name</label>
                        <input type="text" class="form-control" id="editProductNameInput" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="editProductCategory" class="form-label">Category</label>
                        <select class="form-select" id="editProductCategory" name="category" required>
                            <option value="">Select Category</option>
                            <option value="Bikes">Bikes</option>
                            <option value="Accessories">Accessories</option>
                            <option value="Parts & Components">Parts & Components</option>
                            <option value="Apparel">Apparel</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="editProductSubCategory" class="form-label">Sub-Category</label>
                        <select class="form-select" id="editProductSubCategory" name="subcategory" required disabled>
                            <option value="">Select Sub-Category</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="editProductDescriptionInput" class="form-label">Description</label>
                        <textarea class="form-control" id="editProductDescriptionInput" name="description" rows="3" required></textarea>
                    </div>

                    <h6 class="mt-4 mb-3">Product Variations</h6>
                    <div id="editProductVariationsContainer">
                        </div>
                    <button type="button" id="editAddVariationBtn" class="btn btn-sm btn-outline-secondary mt-3">
                        <i class="bi bi-plus-circle me-2"></i>Add Another Variation
                    </button>


                    <h6 class="mt-4 mb-3">Product Images</h6>
                    <div id="editProductImagesDisplay" class="d-flex flex-wrap gap-2 mb-3">
                        </div>
                    <div class="mb-3">
                        <label for="editProductImagesInput" class="form-label">Upload New Images (Max 5 total)</label>
                        <input type="file" class="form-control" id="editProductImagesInput" name="new_product_images[]" accept="image/*" multiple>
                        <small class="text-muted">Select new image files to upload. Existing images can be removed above.</small>
                    </div>

                    <div data-form-message class="mt-3"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-accent">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteProductModal" tabindex="-1" aria-labelledby="deleteProductModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteProductModalLabel">Confirm Product Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form data-api-endpoint="api/delete_product.php">
                <div class="modal-body">
                    <input type="hidden" id="deleteProductId" name="product_id">
                    <p class="lead">Are you sure you want to delete product ID: <strong id="deleteProductName"></strong>?</p>
                    <p class="text-danger">This action will permanently remove the product, all its variations, and images. This cannot be undone.</p>
                    <div data-form-message class="mt-3"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="lowStockAlertModal" tabindex="-1" aria-labelledby="lowStockAlertModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="lowStockAlertModalLabel"><i class="bi bi-exclamation-triangle-fill me-2 text-warning"></i>Low Stock Products</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php if (!empty($low_stock_products)): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover small">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Product Name</th>
                                    <th>Category</th>
                                    <th>Variation</th>
                                    <th>Current Stock</th>
                                    <th>Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($low_stock_products as $item): ?>
                                    <tr>
                                        <td>
                                            <?php if (!empty($item['image_path'])): ?>
                                                <img src="../<?= $item['image_path'] ?>" alt="<?= $item['product_name'] ?>" class="img-thumbnail" style="width: 40px; height: 40px; object-fit: cover;">
                                            <?php else: ?>
                                                No Image
                                            <?php endif; ?>
                                        </td>
                                        <td><?= $item['product_name'] ?></td>
                                        <td><?= $item['category'] ?></td>
                                        <td>Size: <?= $item['size'] ?>, Color: <?= $item['color'] ?></td>
                                        <td><span class="badge bg-danger"><?= $item['stock'] ?></span></td>
                                        <td>₱<?= $item['price'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-success" role="alert">
                        <i class="bi bi-check-circle me-2"></i>Great! All products currently have sufficient stock units.
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="stockHistoryModal" tabindex="-1" aria-labelledby="stockHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="stockHistoryModalLabel"><i class="bi bi-clock-history me-2"></i>Stock History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php if (!empty($stock_history_records)): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover small">
                            <thead>
                                <tr>
                                    <th>Product Name</th>
                                    <th>Variation</th>
                                    <th>Quantity Added</th>
                                    <th>Type</th>
                                    <th>Date/Time</th>
                                    </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stock_history_records as $record): ?>
                                    <tr>
                                        <td><?= $record['product_name'] ?></td>
                                        <td>Size: <?= $record['size'] ?>, Color: <?= $record['color'] ?></td>
                                        <td><span class="badge bg-success">+<?= $record['quantity_changed'] ?></span></td>
                                        <td><?= ucfirst(str_replace('_', ' ', $record['change_type'])) ?></td>
                                        <td><?= date('Y-m-d H:i:s', strtotime($record['changed_at'])) ?></td>
                                        </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info" role="alert">
                        <i class="bi bi-info-circle me-2"></i>
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>