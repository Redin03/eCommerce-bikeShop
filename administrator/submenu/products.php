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
    </style>

<?php
// filepath: c:\xampp\htdocs\BongBicycleShop\administrator\submenu\products.php
session_start();
$message = '';
$message_type = 'info';

if (isset($_SESSION['toast_message'])) {
    $message = $_SESSION['toast_message'];
    $message_type = ($_SESSION['toast_type'] === 'success') ? 'success' : 'danger';
    unset($_SESSION['toast_message'], $_SESSION['toast_type']);
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
        This is the content for the <strong>Products</strong> page. Here you can manage bicycle inventory, add new products, update details, and more.
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
          <th>Price</th>
          <th>Actions</th>
          </tr>
      </thead>
      <tbody>
        <tr>
          <td>PROD001</td>
          <td>Road Bike X-Speed</td>
          <td>Road Bikes</td>
          <td>$1200.00</td>
          <td>
            <button class="btn btn-sm btn-info me-1"><i class="bi bi-eye"></i></button>
            <button class="btn btn-sm btn-warning me-1"><i class="bi bi-pencil"></i></button>
            <button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
          </td>
        </tr>
        <tr>
          <td>PROD002</td>
          <td>Mountain Slayer 29er</td>
          <td>Mountain Bikes</td>
          <td>$950.00</td>
          <td>
            <button class="btn btn-sm btn-info me-1"><i class="bi bi-eye"></i></button>
            <button class="btn btn-sm btn-warning me-1"><i class="bi bi-pencil"></i></button>
            <button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
          </td>
        </tr>
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
              <label for="newProductPrice" class="form-label">Price</label>
              <input type="number" class="form-control" id="newProductPrice" name="price" step="0.01" min="0" required>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-12">
              <label class="form-label d-block">Product Variations (Colors, Sizes, and Stock)</label>
              <div class="input-group mb-2">
                <input type="text" class="form-control" id="newProductColorInput" placeholder="e.g., Red, Blue">
                <input type="text" class="form-control" id="newProductSizeInput" placeholder="e.g., S, M, 29er">
                <input type="number" class="form-control" id="newProductQuantityInput" min="0" value="0" placeholder="Quantity">
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

          <input type="hidden" name="redirect_url" value="http://localhost/bongbicycleshop/administrator/index.php#products">
        </form>
      </div>
    </div>
  </div>
</div>