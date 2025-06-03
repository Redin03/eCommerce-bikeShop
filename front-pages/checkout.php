<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?error=" . urlencode("Please login to continue."));
    exit;
}

$user_id = $_SESSION['user_id'];
$cart_ids = $_POST['cart_ids'] ?? [];

// Re-establish connection for user data if it was closed before
if (!isset($conn) || $conn->connect_error) {
    require_once __DIR__ . '/../config/db.php'; // Re-open connection
}

// Fetch user's shipping address for pre-filling the form
$stmt_user = $conn->prepare("SELECT name, email, contact_number, shipping_address FROM users WHERE id=?");
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$stmt_user->bind_result($user_name, $user_email, $user_contact_number, $shipping_address_json);
$stmt_user->fetch();
$stmt_user->close();

$shipping_address = null;
if ($shipping_address_json) {
    $shipping_address = json_decode($shipping_address_json, true);
}

if (empty($cart_ids)) {
    header("Location: my_account.php?tab=cart&error=" . urlencode("Please select at least one item to checkout."));
    exit;
}

// Prepare placeholders for SQL IN clause
$placeholders = implode(',', array_fill(0, count($cart_ids), '?'));
$types = str_repeat('i', count($cart_ids));

// Fetch selected cart items
$sql = "SELECT c.id AS cart_id, c.quantity, pv.size, pv.color, pv.price AS variation_price, pv.discount_percentage, pv.discount_expiry_date, p.name AS product_name, (SELECT image_path FROM product_images WHERE product_id = p.id AND is_main = 1 LIMIT 1) AS main_image
        FROM cart c
        JOIN product_variations pv ON c.variation_id = pv.id
        JOIN products p ON pv.product_id = p.id
        WHERE c.user_id = ? AND c.id IN ($placeholders)";
$stmt = $conn->prepare($sql);
$params = array_merge([$user_id], $cart_ids);
$stmt->bind_param('i' . $types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$items = [];
$total = 0;
while ($item = $result->fetch_assoc()) {
    $current_price = (float)$item['variation_price'];
    $display_price = $current_price;
    if ($item['discount_percentage'] !== null && $item['discount_expiry_date'] !== null) {
        $discount_expiry_timestamp = strtotime($item['discount_expiry_date']);
        if (time() <= $discount_expiry_timestamp) {
            $discount_amount = $current_price * ($item['discount_percentage'] / 100);
            $display_price = $current_price - $discount_amount;
        }
    }
    $subtotal = $display_price * $item['quantity'];
    $total += $subtotal;
    $item['display_price'] = $display_price;
    $item['subtotal'] = $subtotal;
    $items[] = $item;
}
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Bong Bicycle Shop</title>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">

  <link rel="icon" type="image/png" href="../assets/images/favicon/favicon.svg">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC"
        crossorigin="anonymous">
  <style>
    :root {
      --primary: #006A4E;
      --secondary: #FFB703;
      --accent: #00BFA6;
      --bg-light: #F4F4F4;
      --bg-dark: #003D33;
      --text-dark: #1E1E1E;
      --text-light: #FFFFFF;
      --border-gray: #D9D9D9;
    }

    body {
      background-color: var(--bg-light);
      color: var(--text-dark);
      font-family: 'Montserrat', sans-serif;
    }

    .navbar {
      background-color: var(--primary);
    }

    .navbar-brand,
    .nav-link {
      color: var(--text-light) !important;
      position: relative;
      padding-bottom: 5px;
      font-weight: 500;
    }

    .nav-link:hover {
      color: var(--secondary) !important;
    }

    .nav-link.active::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 100%;
      height: 3px;
      background-color: var(--secondary);
    }

    .btn-accent {
      background-color: var(--accent);
      color: var(--text-light);
    }

    .btn-accent:hover {
      background-color: var(--secondary);
      color: var(--text-dark);
    }

    .hero-section {
      background-color: var(--bg-light);
      padding: 80px 20px;
      text-align: center;
    }

    .hero-section h1 {
      color: var(--primary);
    }

    .hero-section p {
      max-width: 600px;
      margin: 20px auto;
    }

    footer {
      background-color: var(--bg-dark);
      color: var(--text-light);
      padding: 40px 0;
    }

    .footer-link {
      color: var(--text-light);
      text-decoration: none;
    }

    .footer-link:hover {
      color: var(--secondary);
    }

    .border-top {
      border-top: 1px solid var(--border-gray);
    }

    .navbar-logo {
      width: 40px;
      height: 40px;
      margin-right: 10px;
      object-fit: contain;
    }

    .navbar-brand-text {
      font-weight: 600;
      font-size: 1.2rem;
    }

    /* Styles for the wizard steps */
    .checkout-wizard .nav-link {
        color: var(--text-dark) !important;
        background-color: var(--bg-light);
        border: 1px solid var(--border-gray);
        margin-right: 5px;
        border-radius: .25rem .25rem 0 0;
        display: flex; /* Use flexbox for icon and text alignment */
        align-items: center; /* Vertically align items */
        justify-content: center; /* Center items horizontally */
    }
    .checkout-wizard .nav-link.active {
        color: var(--text-light) !important;
        background-color: var(--accent);
        border-color: var(--accent);
    }
    .checkout-wizard .nav-link.active::after {
        display: none; /* Hide the underline for wizard tabs */
    }
    .checkout-wizard .nav-item {
        flex-grow: 1;
        text-align: center;
    }
    .checkout-wizard .nav-link.disabled {
        pointer-events: none;
        opacity: 0.6;
    }

    /* Step indicator icon styles */
    .checkout-wizard .nav-link .step-icon {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background-color: var(--border-gray); /* Default gray circle */
        color: var(--text-light);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8em;
        font-weight: bold;
        margin-right: 8px; /* Space between icon and text */
        transition: background-color 0.3s ease, color 0.3s ease;
    }

    /* Style for completed steps */
    .checkout-wizard .nav-item.step-done .nav-link .step-icon {
        background-color: var(--accent); /* Accent color for completed steps */
        color: var(--text-light); /* Ensure checkmark is visible */
    }

    /* Adjust padding for steps to accommodate icon */
    .checkout-wizard .nav-link {
        padding: 10px 15px; /* Adjust as needed */
    }

    /* Hide default icon/number in step-icon when checkmark is present */
    .checkout-wizard .nav-item.step-done .nav-link .step-icon i.bi-check {
        display: inline-block; /* Show checkmark */
    }

    .checkout-wizard .nav-item.step-done .nav-link .step-icon span.step-number {
        display: none; /* Hide number */
    }

    /* For current active step, keep number visible */
    .checkout-wizard .nav-link.active .step-icon {
        background-color: var(--primary); /* Keep primary color for active */
    }
    .checkout-wizard .nav-link.active .step-icon span.step-number {
        display: inline-block;
    }
    .checkout-wizard .nav-link.active .step-icon i.bi-check {
        display: none; /* Hide checkmark on active step */
    }

    /* Product card styles */
    .product-checkout-item {
        display: flex;
        align-items: center;
        padding: 15px;
        border: 1px solid var(--border-gray);
        border-radius: .25rem;
        margin-bottom: 15px;
        background-color: #fff;
    }
    .product-checkout-item img {
        width: 80px;
        height: 80px;
        object-fit: cover;
        margin-right: 15px;
        border-radius: .25rem;
    }
    .product-details {
        flex-grow: 1;
    }
    .product-details h6 {
        margin-bottom: 5px;
        color: var(--primary);
        font-weight: 600;
    }
    .product-details p {
        margin-bottom: 0;
        font-size: 0.9em;
        color: var(--text-dark);
    }
    .product-summary {
        text-align: right;
    }
    .product-summary .quantity {
        font-weight: 600;
        color: var(--primary);
    }
    .product-summary .subtotal {
        font-size: 1.1em;
        font-weight: 700;
        color: var(--accent);
    }

    /* GCash QR and Upload styles */
    #gcashPaymentDetails {
        margin-top: 15px;
        padding: 15px;
        border: 1px solid var(--border-gray);
        border-radius: .25rem;
        background-color: #f9f9f9;
        display: none; /* Hidden by default */
    }
    #gcashPaymentDetails img {
        max-width: 150px;
        height: auto;
        display: block;
        margin: 0 auto 15px auto;
        border: 1px solid var(--border-gray);
    }
  </style>
</head>
<body>
<?php include '../components/navigation.php'; ?>

<div class="container py-5">
  <?php if (empty($items)): ?>
    <div class="alert alert-warning">No items selected for checkout.</div>
  <?php else: ?>
    <div class="card shadow-sm rounded-0 mb-4">
      <div class="card-body">
        <h3 class="mb-4" style="color:var(--primary);"><i class="bi bi-bag-check"></i> Checkout</h3>

        <ul class="nav nav-pills nav-justified mb-4 checkout-wizard" id="checkoutWizard" role="tablist">
          <li class="nav-item" role="presentation" id="step-product-info">
            <button class="nav-link active" id="product-info-tab" data-bs-toggle="pill" data-bs-target="#product-info" type="button" role="tab" aria-controls="product-info" aria-selected="true">
              <span class="step-icon">
                <span class="step-number">1</span>
                <i class="bi bi-check"></i>
              </span>
              Product Information
            </button>
          </li>
          <li class="nav-item" role="presentation" id="step-shipping-info">
            <button class="nav-link" id="shipping-info-tab" data-bs-toggle="pill" data-bs-target="#shipping-info" type="button" role="tab" aria-controls="shipping-info" aria-selected="false" disabled>
              <span class="step-icon">
                <span class="step-number">2</span>
                <i class="bi bi-check"></i>
              </span>
              Shipping Information
            </button>
          </li>
          <li class="nav-item" role="presentation" id="step-review-order">
            <button class="nav-link" id="review-order-tab" data-bs-toggle="pill" data-bs-target="#review-order" type="button" role="tab" aria-controls="review-order" aria-selected="false" disabled>
              <span class="step-icon">
                <span class="step-number">3</span>
                <i class="bi bi-check"></i>
              </span>
              Review & Place Order
            </button>
          </li>
        </ul>

        <form id="checkoutForm" method="POST" action="../config/place_order.php" enctype="multipart/form-data">
          <?php foreach ($cart_ids as $cid): ?>
            <input type="hidden" name="cart_ids[]" value="<?php echo htmlspecialchars($cid); ?>">
          <?php endforeach; ?>

          <div class="tab-content" id="checkoutWizardContent">
            <div class="tab-pane fade show active" id="product-info" role="tabpanel" aria-labelledby="product-info-tab">
              <h5 class="mb-3" style="color:var(--primary);">Selected Products</h5>
              <div class="product-list">
                <?php foreach ($items as $item): ?>
                  <div class="product-checkout-item">
                    <img src="../<?php echo htmlspecialchars($item['main_image'] ?? 'assets/images/no_image.png'); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                    <div class="product-details">
                      <h6><?php echo htmlspecialchars($item['product_name']); ?></h6>
                      <?php
                        $details = [];
                        if (!empty($item['color']) && $item['color'] !== 'Not Available') $details[] = 'Color: ' . htmlspecialchars($item['color']);
                        if (!empty($item['size']) && $item['size'] !== 'Not Available') $details[] = 'Size: ' . htmlspecialchars($item['size']);
                        if (!empty($details)) {
                            echo '<p>' . implode(' | ', $details) . '</p>';
                        }
                      ?>
                      <p>Quantity: <span class="quantity"><?php echo htmlspecialchars($item['quantity']); ?></span></p>
                    </div>
                    <div class="product-summary">
                      <p class="subtotal">₱<?php echo number_format($item['subtotal'], 2); ?></p>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
              <div class="text-end mt-3">
                <h5 class="total-amount">Total: <span style="color:var(--primary);">₱<?php echo number_format($total, 2); ?></span></h5>
              </div>
              <div class="d-flex justify-content-end">
                <button type="button" class="btn btn-accent" id="nextToShipping">Next: Shipping Information <i class="bi bi-arrow-right"></i></button>
              </div>
            </div>

            <div class="tab-pane fade" id="shipping-info" role="tabpanel" aria-labelledby="shipping-info-tab">
              <h5 class="mb-3" style="color:var(--primary);">Shipping Details & Payment Method</h5>
              <div class="row g-3">
                <div class="col-md-6">
                  <label for="fullName" class="form-label">Full Name</label>
                  <input type="text" class="form-control" id="fullName" name="full_name" value="<?php echo htmlspecialchars($user_name ?? ''); ?>" required>
                </div>
                <div class="col-md-6">
                  <label for="email" class="form-label">Email</label>
                  <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user_email ?? ''); ?>" required>
                </div>
                <div class="col-md-6">
                  <label for="contactNumber" class="form-label">Contact Number</label>
                  <input type="text" class="form-control" id="contactNumber" name="contact_number" value="<?php echo htmlspecialchars($user_contact_number ?? ''); ?>" required>
                </div>
                 <div class="col-md-6">
                  <label for="paymentMethod" class="form-label">Payment Method</label>
                  <select class="form-select" id="paymentMethod" name="payment_method" required>
                    <option value="">Select Payment Method</option>
                    <option value="Store Pickup">Store Pickup</option>
                    <option value="Cash on Delivery (COD)">Cash on Delivery (COD)</option>
                    <option value="GCash">GCash</option>
                    </select>
                </div>
                <div class="col-12" id="gcashPaymentDetails" style="display:none;">
                    <p class="text-center">Scan the QR code to pay via GCash:</p>
                    <img src="../assets/images/gcash_qr.png" alt="GCash QR Code" class="img-fluid mb-3">
                    <label for="proofOfPayment" class="form-label">Upload Proof of Payment (GCash Screenshot)</label>
                    <input type="file" class="form-control" id="proofOfPayment" name="proof_of_payment" accept="image/*">
                    <small class="text-muted">Please upload a screenshot of your successful GCash transaction.</small>
                </div>
                <div class="col-md-4">
                  <label for="region" class="form-label">Region</label>
                  <select id="region" name="region" class="form-select" required>
                    <option value="">Select Region</option>
                  </select>
                </div>
                <div class="col-md-4">
                  <label for="province" class="form-label">Province</label>
                  <select id="province" name="province" class="form-select" required>
                    <option value="">Select Province</option>
                  </select>
                </div>
                <div class="col-md-4">
                  <label for="city" class="form-label">City/Municipality</label>
                  <select id="city" name="city" class="form-select" required>
                    <option value="">Select City/Municipality</option>
                  </select>
                </div>
                <div class="col-12">
                  <label for="barangay" class="form-label">Barangay</label>
                  <input type="text" class="form-control" id="barangay" name="barangay" placeholder="Enter Barangay" value="<?php echo htmlspecialchars($shipping_address['barangay'] ?? ''); ?>" required>
                </div>
                <div class="col-12">
                  <label for="street" class="form-label">Street Address</label>
                  <input type="text" class="form-control" id="street" name="street" placeholder="1234 Main St" value="<?php echo htmlspecialchars($shipping_address['street'] ?? ''); ?>" required>
                </div>
              </div>
              <div class="d-flex justify-content-between mt-4">
                <button type="button" class="btn btn-secondary" id="prevToProducts"><i class="bi bi-arrow-left"></i> Previous</button>
                <button type="button" class="btn btn-accent" id="nextToReview">Next: Review Order <i class="bi bi-arrow-right"></i></button>
              </div>
            </div>

            <div class="tab-pane fade" id="review-order" role="tabpanel" aria-labelledby="review-order-tab">
              <h5 class="mb-3" style="color:var(--primary);">Review Your Order</h5>
              <h6>Product Details:</h6>
              <div class="product-list mb-4" id="reviewProductList">
                </div>
              <div class="text-end mt-3">
                <h5 class="total-amount">Total: <span style="color:var(--primary);" id="reviewTotalAmount">₱0.00</span></h5>
              </div>

              <h6>Shipping Information:</h6>
              <div class="card mb-4">
                <div class="card-body" id="reviewShippingDetails">
                  </div>
              </div>

              <h6>Payment Method:</h6>
              <div class="card mb-4">
                <div class="card-body" id="reviewPaymentMethod">
                  </div>
              </div>

              <div class="d-flex justify-content-between mt-4">
                <button type="button" class="btn btn-secondary" id="prevToShipping"><i class="bi bi-arrow-left"></i> Previous</button>
                <button type="submit" class="btn btn-accent btn-md" id="placeOrderBtn"><i class="bi bi-bag-check"></i> Place Order</button>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  <?php endif; ?>
</div>

<?php include '../components/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
        crossorigin="anonymous"></script>

<script>
  // Pass the PHP $shipping_address data to JavaScript
  const address = <?php echo json_encode($shipping_address ?? []); ?>;
</script>
<script src="../assets/js/my_account.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const productInfoTabBtn = document.getElementById('product-info-tab');
    const shippingInfoTabBtn = document.getElementById('shipping-info-tab');
    const reviewOrderTabBtn = document.getElementById('review-order-tab');

    const stepProductInfo = document.getElementById('step-product-info');
    const stepShippingInfo = document.getElementById('step-shipping-info');
    const stepReviewOrder = document.getElementById('step-review-order');

    const nextToShippingBtn = document.getElementById('nextToShipping');
    const prevToProductsBtn = document.getElementById('prevToProducts');
    const nextToReviewBtn = document.getElementById('nextToReview');
    const prevToShippingBtn = document.getElementById('prevToShipping');
    // const placeOrderBtn = document.getElementById('placeOrderBtn'); // No direct click listener needed if it's a submit button

    const checkoutForm = document.getElementById('checkoutForm');

    // Store cart items data from PHP
    const cartItems = <?php echo json_encode($items); ?>;
    const totalAmount = <?php echo json_encode($total); ?>;

    const paymentMethodSelect = document.getElementById('paymentMethod');
    const gcashPaymentDetailsDiv = document.getElementById('gcashPaymentDetails');
    const proofOfPaymentInput = document.getElementById('proofOfPayment');

    // Function to update review tab content
    function updateReviewTab() {
        let productHtml = '';
        cartItems.forEach(item => {
            let details = [];
            if (item.size && item.size !== 'Not Available') details.push('Size: ' + item.size);
            if (item.color && item.color !== 'Not Available') details.push('Color: ' + item.color);
            const imagePath = item.main_image ? '../' + item.main_image : '../assets/images/no_image.png';

            productHtml += `
                <div class="product-checkout-item">
                    <img src="${imagePath}" alt="${item.product_name}">
                    <div class="product-details">
                        <h6>${item.product_name}</h6>
                        ${details.length > 0 ? '<p>' + details.join(' | ') + '</p>' : ''}
                        <p>Quantity: <span class="quantity">${item.quantity}</span></p>
                    </div>
                    <div class="product-summary">
                        <p class="subtotal">₱${parseFloat(item.subtotal).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</p>
                    </div>
                </div>
            `;
        });
        document.getElementById('reviewProductList').innerHTML = productHtml;
        document.getElementById('reviewTotalAmount').textContent = '₱' + parseFloat(totalAmount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        const fullName = document.getElementById('fullName').value;
        const email = document.getElementById('email').value;
        const contactNumber = document.getElementById('contactNumber').value;
        const street = document.getElementById('street').value;
        const barangay = document.getElementById('barangay').value;
        const regionText = document.getElementById('region').options[document.getElementById('region').selectedIndex].text;
        const provinceText = document.getElementById('province').options[document.getElementById('province').selectedIndex].text;
        const cityText = document.getElementById('city').options[document.getElementById('city').selectedIndex].text;
        const paymentMethodValue = paymentMethodSelect.value;
        let proofOfPaymentStatus = '';
        if (paymentMethodValue === 'GCash' && proofOfPaymentInput.files.length > 0) {
            proofOfPaymentStatus = '<br><small class="text-success">Proof of payment uploaded.</small>';
        } else if (paymentMethodValue === 'GCash' && proofOfPaymentInput.files.length === 0) {
             proofOfPaymentStatus = '<br><small class="text-danger">Proof of payment not uploaded.</small>';
        }

        document.getElementById('reviewShippingDetails').innerHTML = `
            <p><strong>Recipient:</strong> ${fullName}</p>
            <p><strong>Email:</strong> ${email}</p>
            <p><strong>Contact:</strong> ${contactNumber}</p>
            <p><strong>Address:</strong> ${street}, Brgy. ${barangay}, ${cityText}, ${provinceText}, ${regionText}</p>
        `;
        document.getElementById('reviewPaymentMethod').innerHTML = `Payment Method: ${paymentMethodValue}${proofOfPaymentStatus}`;
    }

    // Handle payment method selection change
    paymentMethodSelect.addEventListener('change', function() {
        if (this.value === 'GCash') {
            gcashPaymentDetailsDiv.style.display = 'block';
            proofOfPaymentInput.setAttribute('required', 'required');
        } else {
            gcashPaymentDetailsDiv.style.display = 'none';
            proofOfPaymentInput.removeAttribute('required');
            proofOfPaymentInput.value = ''; // Clear selected file
        }
    });

    // Step 1: Product Info -> Step 2: Shipping Info
    nextToShippingBtn.addEventListener('click', function() {
        const bsTab = new bootstrap.Tab(shippingInfoTabBtn);
        bsTab.show();
        shippingInfoTabBtn.classList.remove('disabled');
        stepProductInfo.classList.add('step-done'); // Mark step 1 as done
    });

    // Step 2: Shipping Info -> Step 1: Product Info
    prevToProductsBtn.addEventListener('click', function() {
        const bsTab = new bootstrap.Tab(productInfoTabBtn);
        bsTab.show();
        stepProductInfo.classList.remove('step-done'); // Remove done from step 1 if going back
        stepShippingInfo.classList.remove('step-done'); // Ensure step 2 is not marked done if going back
    });

    // Step 2: Shipping Info -> Step 3: Review Order
    nextToReviewBtn.addEventListener('click', function() {
        // Client-side validation for shipping information
        const shippingFields = [
            'fullName', 'email', 'contactNumber', 'street', 'barangay', 'paymentMethod'
        ];
        const dropdownFields = [
            'region', 'province', 'city'
        ];

        let allFieldsFilled = true;

        shippingFields.forEach(fieldId => {
            const input = document.getElementById(fieldId);
            if (!input.value.trim()) {
                input.classList.add('is-invalid');
                allFieldsFilled = false;
            } else {
                input.classList.remove('is-invalid');
            }
        });

        dropdownFields.forEach(fieldId => {
            const select = document.getElementById(fieldId);
            if (!select.value) { // Check if a value is selected (not the default empty option)
                select.classList.add('is-invalid');
                allFieldsFilled = false;
            } else {
                select.classList.remove('is-invalid');
            }
        });

        // Specific validation for GCash proof of payment
        if (paymentMethodSelect.value === 'GCash' && proofOfPaymentInput.hasAttribute('required') && proofOfPaymentInput.files.length === 0) {
            proofOfPaymentInput.classList.add('is-invalid');
            allFieldsFilled = false;
        } else {
            proofOfPaymentInput.classList.remove('is-invalid');
        }


        if (allFieldsFilled) {
            updateReviewTab(); // Populate review tab before showing
            const bsTab = new bootstrap.Tab(reviewOrderTabBtn);
            bsTab.show();
            reviewOrderTabBtn.classList.remove('disabled');
            stepShippingInfo.classList.add('step-done'); // Mark step 2 as done
        } else {
            alert('Please fill in all required shipping information fields, including proof of payment for GCash if selected.');
        }
    });

    // Step 3: Review Order -> Step 2: Shipping Info
    prevToShippingBtn.addEventListener('click', function() {
        const bsTab = new bootstrap.Tab(shippingInfoTabBtn);
        bsTab.show();
        stepReviewOrder.classList.remove('step-done'); // Remove done from step 3 if going back
        stepShippingInfo.classList.remove('step-done'); // Ensure step 2 is not marked done if going back from review
    });


    // Prevent direct tab clicks and control navigation with buttons
    document.querySelectorAll('.checkout-wizard .nav-link').forEach(tab => {
        tab.addEventListener('click', function(event) {
            if (this.classList.contains('disabled')) {
                event.preventDefault();
                event.stopPropagation();
            }
        });
    });

    // Handle tab shown events to manage active step and 'done' classes
    document.getElementById('checkoutWizard').addEventListener('shown.bs.tab', function (event) {
        const activeTabId = event.target.id;
        if (activeTabId === 'product-info-tab') {
            stepShippingInfo.classList.remove('step-done');
            stepReviewOrder.classList.remove('step-done');
        } else if (activeTabId === 'shipping-info-tab') {
            stepReviewOrder.classList.remove('step-done');
        }
    });
});
</script>
</body>
</html>