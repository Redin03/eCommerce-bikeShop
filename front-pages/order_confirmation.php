<?php
session_start();
require_once __DIR__ . '/../config/db.php'; // Your database connection file

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?error=" . urlencode("Please login to view your order."));
    exit;
}

// Check for the session flag to ensure direct access is prevented
if (!isset($_SESSION['order_confirmed']) || $_SESSION['order_confirmed'] !== true) {
    // If not coming from a valid order confirmation flow, redirect
    header("Location: my_account.php?tab=orders&error=" . urlencode("Access to order confirmation page denied."));
    exit;
} else {
    // Unset the flag immediately to prevent re-access via back button
    unset($_SESSION['order_confirmed']);
}


$user_id = $_SESSION['user_id'];
$order_id = $_GET['order_id'] ?? null;
$reference_number = $_GET['ref'] ?? null;
$status = $_GET['status'] ?? 'error'; // 'success' or 'error'
$message = $_GET['message'] ?? '';

$order_details = null;
$order_items = [];
$shipping_address_parsed = null;

if ($order_id) {
    // Fetch order details
    $sql_order = "SELECT * FROM orders WHERE id = ? AND user_id = ?";
    $stmt_order = $conn->prepare($sql_order);
    $stmt_order->bind_param("ii", $order_id, $user_id);
    $stmt_order->execute();
    $result_order = $stmt_order->get_result();
    $order_details = $result_order->fetch_assoc();
    $stmt_order->close();

    if ($order_details) {
        // Parse shipping address JSON
        $shipping_address_parsed = json_decode($order_details['shipping_address'], true);

        // Fetch order items
        $sql_items = "SELECT oi.*, p.name AS product_name, (SELECT image_path FROM product_images WHERE product_id = p.id AND is_main = 1 LIMIT 1) AS main_image, pv.size, pv.color
                      FROM order_items oi
                      JOIN products p ON oi.product_id = p.id
                      JOIN product_variations pv ON oi.variation_id = pv.id
                      WHERE oi.order_id = ?";
        $stmt_items = $conn->prepare($sql_items);
        $stmt_items->bind_param("i", $order_id);
        $stmt_items->execute();
        $result_items = $stmt_items->get_result();
        while ($item = $result_items->fetch_assoc()) {
            $order_items[] = $item;
        }
        $stmt_items->close();
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Order Confirmation - Bong Bicycle Shop</title>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">

  <link rel="icon" type="image/png" href="../assets/images/favicon/favicon.svg">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC"
        crossorigin="anonymous">
  <style>
    /* Product card styles (reused from checkout) */
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
        object-fit: contain;
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
  </style>
</head>
<body>
<?php include '../components/navigation.php'; ?>

<div class="container py-5">
  <div class="card shadow-sm rounded-0 mb-4">
    <div class="card-body">
      <?php if ($status === 'success' && $order_details): ?>
        <div class="alert alert-success text-center" role="alert">
          <i class="bi bi-check-circle-fill me-2"></i>
          Order Placed Successfully!
        </div>
        <h3 class="mb-4 text-center" style="color:var(--primary);">Order Summary</h3>
        <p class="lead text-center">Your Reference Number: <strong class="text-accent"><?php echo htmlspecialchars($order_details['reference_number']); ?></strong></p>
        <hr>

        <div class="row">
          <div class="col-md-6 mb-4">
            <h5 class="mb-3" style="color:var(--primary);">Order Details</h5>
            <p><strong>Order ID:</strong> <?php echo htmlspecialchars($order_details['id']); ?></p>
            <p><strong>Order Date:</strong> <?php echo htmlspecialchars(date('M d, Y h:i A', strtotime($order_details['order_date']))); ?></p>
            <p><strong>Order Status:</strong> <?php echo htmlspecialchars($order_details['order_status']); ?></p>
            <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($order_details['payment_method']); ?></p>
            <?php if ($order_details['payment_method'] === 'GCash' && $order_details['proof_of_payment_image']): ?>
              <p><strong>Proof of Payment:</strong> <a href="../<?php echo htmlspecialchars($order_details['proof_of_payment_image']); ?>" target="_blank">View Image</a></p>
            <?php endif; ?>
            <p><strong>Total Amount:</strong> ₱<?php echo number_format($order_details['total_amount'], 2); ?></p>
          </div>
          <div class="col-md-6 mb-4">
            <h5 class="mb-3" style="color:var(--primary);">Shipping Information</h5>
            <p><strong>Recipient:</strong> <?php echo htmlspecialchars($order_details['full_name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($order_details['email']); ?></p>
            <p><strong>Contact:</strong> <?php echo htmlspecialchars($order_details['contact_number']); ?></p>
            <p><strong>Address:</strong>
              <?php
                if ($order_details['payment_method'] === 'Store Pickup') {
                    echo "Store Pickup (No shipping address required)";
                } else if ($shipping_address_parsed) {
                    echo htmlspecialchars($shipping_address_parsed['street'] ?? '') . ', Brgy. ' .
                         htmlspecialchars($shipping_address_parsed['barangay'] ?? '') . ', ' .
                         htmlspecialchars($shipping_address_parsed['city'] ?? '') . ', ' .
                         htmlspecialchars($shipping_address_parsed['province'] ?? '') . ', ' .
                         htmlspecialchars($shipping_address_parsed['region'] ?? '');
                }
              ?>
            </p>
          </div>
        </div>

        <h5 class="mb-3" style="color:var(--primary);">Products Ordered</h5>
        <div class="product-list mb-4">
          <?php if (!empty($order_items)): ?>
            <?php foreach ($order_items as $item): ?>
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
                  <p class="subtotal">₱<?php echo number_format($item['subtotal_at_order'], 2); ?></p>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p class="text-muted">No products found for this order.</p>
          <?php endif; ?>
        </div>
        

        <div class="d-flex justify-content-center mt-4">
          <a href="my_account.php?tab=orders" class="btn btn-secondary me-2"><i class="bi bi-box-seam me-2"></i>View My Orders</a>
          <a href="my_account.php?tab=cart" class="btn btn-accent"><i class="bi bi-cart me-2"></i>Continue Shopping</a>
        </div>

      <?php else: ?>
        <div class="alert alert-danger text-center" role="alert">
          <i class="bi bi-x-circle-fill me-2"></i>
          <?php echo htmlspecialchars($message ?: "There was an error processing your order or the order could not be found."); ?>
        </div>
        <div class="text-center">
          <a href="checkout.php" class="btn btn-secondary me-2"><i class="bi bi-arrow-left me-2"></i>Back to Checkout</a>
          <a href="my_account.php?tab=cart" class="btn btn-accent"><i class="bi bi-cart me-2"></i>Continue Shopping</a>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include '../components/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
        crossorigin="anonymous"></script>

<script>
  // Prevents navigating back to this page after a successful order confirmation.
  // This replaces the current history entry, so the back button skips this page.
  if (window.history.replaceState) {
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');
    const order_id = urlParams.get('order_id');

    // Only replace state if the order was successfully placed and an order_id is present
    if (status === 'success' && order_id) {
      window.history.replaceState(null, document.title, window.location.href);
    }
  }
</script>
</body>
</html>