<style>
        /*
        NOTE: In a real application, these CSS rules would ideally reside in your main stylesheet (e.g., style.css)
        that is loaded once in index.php, not directly in products.php.
        This prevents redundant style declarations when products.php is loaded dynamically.
        */

        :root {
            --primary: #006A4E;
            --secondary: #FFB703;
            --accent: #00BFA6;
            --bg-light: #F4F4F4;
            --bg-dark: #003D33;
            --text-dark: #1E1E1E;
            --text-light: #FFFFFF;
            --border-gray: #D9D9D9;
            --header-height: 66px;
            --sidebar-width: 250px;
            --sidebar-collapsed-width: 70px;
            --toggle-button-size: 40px;
        }

        /* Styles for the "Add New Product" button */
        .fixed-add-button {
            background-color: var(--accent); /* Use accent color for the button background */
            color: var(--text-light);      /* Light text color */
            border: none;                   /* Remove default border */
            transition: background-color 0.2s ease, color 0.2s ease; /* Smooth transition on hover */
        }

        .fixed-add-button:hover {
            background-color: var(--secondary); /* Secondary color on hover */
            color: var(--text-dark);       /* Darker text on hover for contrast */
        }

        /* Styles for the modal header */
        .modal-header.bg-primary {
            background-color: var(--primary) !important; /* Override Bootstrap's bg-primary with your variable */
            color: var(--text-light);                 /* Light text color for header */
        }

        .modal-header .btn-close {
            filter: invert(1); /* Invert the color of the close button icon to make it white */
        }
        /* Styles for the delete modal header */
        .modal-header.bg-danger {
            background-color: #dc3545 !important; /* Bootstrap danger color */
            color: var(--text-light);
        }

        .modal-header.bg-danger .btn-close {
            filter: invert(1); /* White close button for danger modal */
        }

        /* Styles for modal footer buttons */
        .modal-footer .btn-primary {
            background-color: var(--primary); /* Primary color for the submit button */
            border-color: var(--primary);     /* Ensure border matches */
            color: var(--text-light);         /* Light text */
        }

        .modal-footer .btn-primary:hover {
            background-color: var(--secondary); /* Secondary color on hover */
            border-color: var(--secondary);     /* Border matches hover */
            color: var(--text-dark);          /* Darker text on hover */
        }
         /* Styles for delete button in modal footer */
        .modal-footer .btn-danger {
            background-color: #dc3545; /* Bootstrap danger color */
            border-color: #dc3545;
            color: var(--text-light);
        }

        .modal-footer .btn-danger:hover {
            background-color: #c82333; /* Darker danger on hover */
            border-color: #bd2130;
        }

        .modal-footer .btn-secondary {
            background-color: var(--border-gray); /* Use a neutral color for secondary button */
            border-color: var(--border-gray);
            color: var(--text-dark);
        }

        .modal-footer .btn-secondary:hover {
            background-color: #c0c0c0; /* Slightly darker gray on hover */
            border-color: #c0c0c0;
        }

        /* Additional styles for the page content, if not already in main stylesheet */
        .card {
            border: none;
            border-radius: 0.5rem;
        }
        .table-responsive {
            margin-top: 15px;
        }
        .product-image-thumbnail {
            width: 50px;
            height: 50px;
            object-fit: cover;
            margin-right: 5px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .variation-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85em;
            margin-top: 5px; /* Add some space above the table */
        }
        .variation-table th, .variation-table td {
            border: 1px solid #eee; /* Light border for inner table cells */
            padding: 4px 8px;
            text-align: left;
        }
        .variation-table th {
            background-color: #f8f8f8; /* Light background for inner table header */
            font-weight: bold;
        }
        .variation-table tr:nth-child(even) {
            background-color: #fdfdfd; /* Slightly different background for even rows */
        }
    </style>

<?php
// filepath: c:\xampp\htdocs\BongBicycleShop\administrator\submenu\products.php
$message = '';
$message_type = 'info';

if (isset($_SESSION['toast_message'])) {
    $message = $_SESSION['toast_message'];
    $message_type = ($_SESSION['toast_type'] === 'success') ? 'success' : 'danger';
    unset($_SESSION['toast_message'], $_SESSION['toast_type']);
}

// Include database connection
require_once __DIR__ . '/../../config/db.php'; // Adjust path as necessary

$products = [];
try {
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception("Database connection not established or failed: " . ($conn->connect_error ?? 'Unknown error'));
    }

    // Prepare SQL statement to fetch products along with their variations and images
    // Using LEFT JOIN to ensure products without variations or images are still listed
    $sql = "
        SELECT
            p.id,
            p.name,
            p.category_key,
            p.subcategory_key,
            GROUP_CONCAT(DISTINCT CONCAT(pv.color_name, '|', pv.size_name, '|', pv.quantity, '|', pv.price) SEPARATOR ';') AS variations,
            GROUP_CONCAT(DISTINCT pi.image_path ORDER BY pi.display_order ASC SEPARATOR ';') AS images
        FROM
            products p
        LEFT JOIN
            product_variations pv ON p.id = pv.product_id
        LEFT JOIN
            product_images pi ON p.id = pi.product_id
        GROUP BY
            p.id
        ORDER BY
            p.id DESC;
    ";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare statement failed: " . $conn->error);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        // Process variations and images
        $row['parsed_variations'] = [];
        if (!empty($row['variations'])) {
            $variation_strings = explode(';', $row['variations']);
            foreach ($variation_strings as $v_str) {
                list($color, $size, $qty, $price) = explode('|', $v_str);
                $row['parsed_variations'][] = [
                    'color_name' => $color,
                    'size_name' => $size,
                    'quantity' => $qty,
                    'price' => $price
                ];
            }
        }
        $row['parsed_images'] = !empty($row['images']) ? explode(';', $row['images']) : [];
        $products[] = $row;
    }
    $stmt->close();

} catch (Exception $e) {
    error_log("Error fetching products: " . $e->getMessage());
    $message = "Error loading products: " . $e->getMessage();
    $message_type = "danger";
} finally {
    if (isset($conn) && $conn instanceof mysqli && !$conn->connect_error) {
        $conn->close();
    }
}
?>

<h2 class="mb-4">Product Management</h2>
 <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="alert alert-info" role="alert">
        This is the content for the **Products** page. Here you can manage bicycle inventory, add new products, update details, and more.
    </div>

<button class="btn mb-3 fixed-add-button" data-bs-toggle="modal" data-bs-target="#addProductModal">
  <i class="bi bi-plus-circle me-2"></i> Add New Product
</button>

<div class="card shadow-sm p-4">
  <h5>Existing Products</h5>
  <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
    <table class="table table-striped table-hover">
      <thead>
        <tr>
          <th>Product ID</th>
          <th>Name</th>
          <th>Category</th>
          <th>Variations</th> <th>Images</th>     <th>Actions</th>
          </tr>
      </thead>
      <tbody>
        <?php if (empty($products)): ?>
            <tr>
                <td colspan="6" class="text-center text-muted">No products found. Add a new product to see it here.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($products as $product): ?>
                <tr>
                    <td><?php echo htmlspecialchars($product['id']); ?></td>
                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                    <td><?php echo htmlspecialchars($product['category_key']); ?> / <?php echo htmlspecialchars($product['subcategory_key']); ?></td>
                    <td>
                        <?php if (!empty($product['parsed_variations'])): ?>
                            <table class="variation-table">
                                <thead>
                                    <tr>
                                        <th>Color</th>
                                        <th>Size</th>
                                        <th>Qty</th>
                                        <th>Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($product['parsed_variations'] as $variation): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($variation['color_name']); ?></td>
                                            <td><?php echo htmlspecialchars($variation['size_name']); ?></td>
                                            <td><?php echo htmlspecialchars($variation['quantity']); ?></td>
                                            <td><?php echo htmlspecialchars(number_format($variation['price'], 2)); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            *No variations*
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!empty($product['parsed_images'])): ?>
                            <?php foreach ($product['parsed_images'] as $image_path): ?>
                                <img src="/ecommerce-bikeshop/<?php echo htmlspecialchars($image_path); ?>" alt="Product Image" class="product-image-thumbnail">
                            <?php endforeach; ?>
                        <?php else: ?>
                            *No images*
                        <?php endif; ?>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-warning me-1 edit-product-btn" title="Edit"
                                data-bs-toggle="modal" data-bs-target="#editProductModal"
                                data-product-id="<?php echo htmlspecialchars($product['id']); ?>">
                            <i class="bi bi-pencil"></i>
                        </button>
                       <button class="btn btn-sm btn-danger delete-product-btn" title="Delete"
                              data-bs-toggle="modal" data-bs-target="#deleteProductModal"
                              data-product-id="<?php echo htmlspecialchars($product['id']); ?>"
                              data-product-name="<?php echo htmlspecialchars($product['name']); ?>">
                          <i class="bi bi-trash"></i>
                      </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content">
      <div class="modal-header bg-primary text-light">
        <h5 class="modal-title" id="addProductModalLabel"><i class="bi bi-plus-circle me-2"></i> Add New Product</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="addProductForm" action="api/add_product.php" method="POST" enctype="multipart/form-data">
          <div class="row mb-3">
            <div class="col-12">
              <label for="newProductName" class="form-label">Product Name</label>
              <input type="text" class="form-control" id="newProductName" name="productName" required>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label for="newProductCategory" class="form-label">Category</label>
              <select class="form-select" id="newProductCategory" name="categoryKey" required>
                <option selected disabled value="">Select Category</option>
              </select>
            </div>
            <div class="col-md-6">
              <label for="newProductSubcategory" class="form-label">Subcategory</label>
              <select class="form-select" id="newProductSubcategory" name="subcategoryKey" required disabled>
                <option selected disabled value="">Select Subcategory</option>
              </select>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-12">
              <label class="form-label d-block">Product Variations (Colors, Sizes, Quantity, and Price)</label>
              <div class="input-group mb-2">
                <input type="text" class="form-control" id="newProductColorInput" placeholder="e.g., Red, Blue">
                <input type="text" class="form-control" id="newProductSizeInput" placeholder="e.g., S, M, 29er">
                <input type="number" class="form-control" id="newProductQuantityInput" min="0" value="0" placeholder="Quantity">
                <input type="number" class="form-control" id="newProductVariationPriceInput" step="0.01" min="0" placeholder="Price (e.g., 9000.00)">
                <button class="btn btn-outline-secondary" type="button" id="addVariationBtn"><i class="bi bi-plus-lg"></i> Add Variation</button>
              </div>
              <div id="productVariationsContainer" class="mt-2 border p-2 rounded bg-light">
                <p class="text-muted text-center" id="noVariationsMessage">No variations added yet.</p>
              </div>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-12">
              <label for="newProductDescription" class="form-label">Description</label>
              <textarea class="form-control" id="newProductDescription" name="description" rows="3"></textarea>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-12">
              <label for="newProductImages" class="form-label">Product Images</label>
              <input class="form-control" type="file" id="newProductImages" name="productImages[]" multiple accept="image/*">
              <div id="imagePreviewContainer" class="d-flex flex-wrap mt-2">
                </div>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-accent">Save Product</button>
          </div>

          <input type="hidden" name="redirect_url" value="http://localhost/ecommerce-bikeshop/administrator/index.php#products">
        </form>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content">
      <div class="modal-header bg-warning text-dark"> <h5 class="modal-title" id="editProductModalLabel"><i class="bi bi-pencil-square me-2"></i> Edit Product</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="editProductForm" action="api/update_product.php" method="POST" enctype="multipart/form-data">
          <input type="hidden" id="editProductId" name="productId">

          <div class="row mb-3">
            <div class="col-12">
              <label for="editProductName" class="form-label">Product Name</label>
              <input type="text" class="form-control" id="editProductName" name="productName" required>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label for="editProductCategory" class="form-label">Category</label>
              <select class="form-select" id="editProductCategory" name="categoryKey" required>
                <option selected disabled value="">Select Category</option>
              </select>
            </div>
            <div class="col-md-6">
              <label for="editProductSubcategory" class="form-label">Subcategory</label>
              <select class="form-select" id="editProductSubcategory" name="subcategoryKey" required disabled>
                <option selected disabled value="">Select Subcategory</option>
              </select>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-12">
              <label class="form-label d-block">Product Variations (Colors, Sizes, Quantity, and Price)</label>
              <div class="input-group mb-2">
                <input type="text" class="form-control" id="editProductColorInput" placeholder="e.g., Red, Blue">
                <input type="text" class="form-control" id="editProductSizeInput" placeholder="e.g., S, M, 29er">
                <input type="number" class="form-control" id="editProductQuantityInput" min="0" value="0" placeholder="Quantity">
                <input type="number" class="form-control" id="editProductVariationPriceInput" step="0.01" min="0" placeholder="Price (e.g., 9000.00)">
                <button class="btn btn-outline-secondary" type="button" id="editAddVariationBtn"><i class="bi bi-plus-lg"></i> Add Variation</button>
              </div>
              <div id="editProductVariationsContainer" class="mt-2 border p-2 rounded bg-light">
                <p class="text-muted text-center" id="editNoVariationsMessage">No variations added yet.</p>
              </div>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-12">
              <label for="editProductDescription" class="form-label">Description</label>
              <textarea class="form-control" id="editProductDescription" name="description" rows="3"></textarea>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-12">
              <label for="editProductImages" class="form-label">Product Images</label>
              <input class="form-control" type="file" id="editProductImages" name="productImages[]" multiple accept="image/*">
              <div id="editImagePreviewContainer" class="d-flex flex-wrap mt-2">
                </div>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-warning">Update Product</button>
          </div>

          <input type="hidden" name="redirect_url" value="http://localhost/ecommerce-bikeshop/administrator/index.php#products">
        </form>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="deleteProductModal" tabindex="-1" aria-labelledby="deleteProductModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-light">
        <h5 class="modal-title" id="deleteProductModalLabel"><i class="bi bi-exclamation-triangle me-2"></i> Confirm Deletion</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Are you sure you want to delete product ID <strong id="modalProductId"></strong> (<strong id="modalProductName"></strong>)? This action cannot be undone.
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <form id="deleteProductForm" action="api/delete_product.php" method="POST">
          <input type="hidden" name="productId" id="deleteProductId">
          <input type="hidden" name="redirect_url" value="http://localhost/ecommerce-bikeshop/administrator/index.php#products">
          <button type="submit" class="btn btn-danger">Delete Product</button>
        </form>
      </div>
    </div>
  </div>
</div>