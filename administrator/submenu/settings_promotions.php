<?php
session_start();
// Check if the admin is logged in (Crucial for all admin pages)
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    $_SESSION['login_message'] = [
        'text' => 'Please log in to access this page.',
        'type' => 'info'
    ];
    header('Location: ../login.php');
    exit;
}

require_once '../../config/db.php'; // Include your database connection
require_once '../includes/logger.php'; // Include the logger function

// Fetch all products to populate the dropdown for item-specific discounts
$products = [];
$sql_products = "SELECT id, name FROM products ORDER BY name ASC";
$result_products = $conn->query($sql_products);
if ($result_products) {
    while ($row = $result_products->fetch_assoc()) {
        $products[] = $row;
    }
    $result_products->free();
} else {
    error_log("Error fetching products for promotions: " . $conn->error);
}

// Fetch existing general promotions (assuming a 'discount_codes' table)
// This part is for displaying existing general discounts, you would need to implement fetching logic
$general_discounts = [];
// Example:
// $stmt_general = $conn->prepare("SELECT * FROM discount_codes ORDER BY expiry_date DESC");
// if ($stmt_general) { $stmt_general->execute(); $result_general = $stmt_general->get_result(); while ($row = $result_general->fetch_assoc()) { $general_discounts[] = $row; } $stmt_general->close();}


// Fetch existing item-specific discounts (assuming discount_percentage and discount_expiry_date in product_variations)
$item_discounts = [];
$sql_item_discounts = "
    SELECT
        pv.id AS variation_id,
        pv.product_id,
        p.name AS product_name,
        pv.size,
        pv.color,
        pv.discount_percentage,
        pv.discount_expiry_date
    FROM
        product_variations pv
    JOIN
        products p ON pv.product_id = p.id
    WHERE
        pv.discount_percentage IS NOT NULL AND pv.discount_percentage > 0
    ORDER BY
        p.name ASC, pv.size ASC, pv.color ASC";
$result_item_discounts = $conn->query($sql_item_discounts);
if ($result_item_discounts) {
    while ($row = $result_item_discounts->fetch_assoc()) {
        $item_discounts[] = $row;
    }
    $result_item_discounts->free();
} else {
    error_log("Error fetching item-specific discounts: " . $conn->error);
}


$conn->close(); // Close the connection after fetching necessary data
?>

<h2 class="mb-4">Settings: Promotions & Discount</h2>
<div class="card shadow-sm p-4 mb-4">
    <h5>Apply Discount to Specific Product/Variation</h5>
    <form data-api-endpoint="api/apply_item_discount.php" id="applyItemDiscountForm">
        <div class="mb-3">
            <label for="selectProduct" class="form-label">Select Product</label>
            <select class="form-select" id="selectProduct" name="product_id" required>
                <option value="">-- Select a Product --</option>
                <?php foreach ($products as $product): ?>
                    <option value="<?= htmlspecialchars($product['id']) ?>"><?= htmlspecialchars($product['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3" id="productVariationsContainer" style="display: none;">
            <label for="selectVariation" class="form-label">Select Specific Variation (Optional)</label>
            <select class="form-select" id="selectVariation" name="variation_id">
                <option value="">-- Apply to All Variations of this Product --</option>
                </select>
            <small class="text-muted">Leave blank to apply discount to all variations of the selected product.</small>
        </div>
        <div class="mb-3">
            <label for="itemDiscountValue" class="form-label">Discount Percentage (%)</label>
            <input type="number" class="form-control" id="itemDiscountValue" name="discount_percentage" step="0.01" min="0" max="100" placeholder="e.g., 15" required>
        </div>
        <div class="mb-3">
            <label for="itemDiscountExpiryDate" class="form-label">Discount Expiry Date (Optional)</label>
            <input type="date" class="form-control" id="itemDiscountExpiryDate" name="discount_expiry_date">
            <small class="text-muted">Leave empty for no expiry for this specific item discount.</small>
        </div>
        <div data-form-message class="mt-3"></div> <button type="submit" class="btn btn-accent"><i class="bi bi-tag-fill me-2"></i>Apply Item Discount</button>
        </form>
</div>

<div class="card shadow-sm mt-4">
    <div class="card-header">
        Existing Promotions & Discounts
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Details</th>
                        <th>Value</th>
                        <th>Expires On</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($general_discounts)): ?>
                        <?php foreach ($general_discounts as $discount): ?>
                            <tr>
                                <td>General</td>
                                <td>Code: <?= htmlspecialchars($discount['code']) ?></td>
                                <td><?= htmlspecialchars($discount['value']) ?>%</td>
                                <td><?= !empty($discount['expiry_date']) ? htmlspecialchars(date('Y-m-d', strtotime($discount['expiry_date']))) : 'No Expiry' ?></td>
                                <td>
                                    <button class="btn btn-sm btn-danger"><i class="bi bi-trash me-1"></i>Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <?php if (!empty($item_discounts)): ?>
                        <?php foreach ($item_discounts as $item_discount): ?>
                            <tr>
                                <td>Item Specific</td>
                                <td>
                                    Product: <?= htmlspecialchars($item_discount['product_name']) ?><br>
                                    <?php if (!empty($item_discount['variation_id'])): ?>
                                        (Size: <?= htmlspecialchars($item_discount['size']) ?>, Color: <?= htmlspecialchars($item_discount['color']) ?>)
                                    <?php else: ?>
                                        (All variations)
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($item_discount['discount_percentage']) ?>%</td>
                                <td><?= !empty($item_discount['discount_expiry_date']) ? htmlspecialchars(date('Y-m-d', strtotime($item_discount['discount_expiry_date']))) : 'No Expiry' ?></td>
                                <td>
                                    <button class="btn btn-warning edit-item-discount-btn me-2"
                                            data-bs-toggle="modal" data-bs-target="#editItemDiscountModal"
                                            data-product-id="<?= htmlspecialchars($item_discount['product_id']) ?>"
                                            data-variation-id="<?= htmlspecialchars($item_discount['variation_id']) ?>"
                                            data-discount-percentage="<?= htmlspecialchars($item_discount['discount_percentage']) ?>"
                                            data-discount-expiry-date="<?= !empty($item_discount['discount_expiry_date']) ? htmlspecialchars(date('Y-m-d', strtotime($item_discount['discount_expiry_date']))) : '' ?>">
                                        <i class="bi bi-pencil-square"></i> Edit Discount
                                    </button>
                                    <button class="btn btn-danger remove-item-discount-btn"
                                            data-bs-toggle="modal" data-bs-target="#removeItemDiscountModal"
                                            data-product-id="<?= htmlspecialchars($item_discount['product_id']) ?>"
                                            data-variation-id="<?= htmlspecialchars($item_discount['variation_id']) ?>">
                                        <i class="bi bi-trash"></i> Remove Discount
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <?php if (empty($general_discounts) && empty($item_discounts)): ?>
                        <tr>
                            <td colspan="5" class="text-center">No promotions or item discounts currently listed.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="editItemDiscountModal" tabindex="-1" aria-labelledby="editItemDiscountModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editItemDiscountModalLabel">Edit Item Discount</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form data-api-endpoint="api/apply_item_discount.php" id="modalEditItemDiscountForm">
                <div class="modal-body">
                    <input type="hidden" id="editModalProductId" name="product_id">
                    <input type="hidden" id="editModalVariationId" name="variation_id">
                    <div class="mb-3">
                        <label for="editModalProductName" class="form-label">Product</label>
                        <input type="text" class="form-control" id="editModalProductName" readonly>
                    </div>
                    <div class="mb-3" id="editModalVariationDetailsContainer">
                        <label for="editModalVariationDetails" class="form-label">Variation</label>
                        <input type="text" class="form-control" id="editModalVariationDetails" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="editModalDiscountValue" class="form-label">Discount Percentage (%)</label>
                        <input type="number" class="form-control" id="editModalDiscountValue" name="discount_percentage" step="0.01" min="0" max="100" required>
                    </div>
                    <div class="mb-3">
                        <label for="editModalExpiryDate" class="form-label">Expiry Date (Optional)</label>
                        <input type="date" class="form-control" id="editModalExpiryDate" name="discount_expiry_date">
                        <small class="text-muted">Leave empty for no expiry.</small>
                    </div>
                    <div data-form-message class="mt-3"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-2"></i>Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="removeItemDiscountModal" tabindex="-1" aria-labelledby="removeItemDiscountModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="removeItemDiscountModalLabel">Remove Item Discount</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form data-api-endpoint="api/remove_item_discount.php" id="modalRemoveItemDiscountForm">
                <div class="modal-body">
                    <input type="hidden" id="removeModalProductId" name="product_id">
                    <input type="hidden" id="removeModalVariationId" name="variation_id">
                    <p>Are you sure you want to remove the discount from:</p>
                    <p><strong>Product: <span id="removeModalProductName"></span></strong></p>
                    <p><strong id="removeModalVariationDetails"></strong></p>
                    <div data-form-message class="mt-3"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger"><i class="bi bi-trash me-2"></i>Confirm Remove</button>
                </div>
            </form>
        </div>
    </div>
</div>