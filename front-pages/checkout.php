<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?error=" . urlencode("Please login to continue."));
    exit;
}

$user_id = $_SESSION['user_id'];
$cart_ids = $_POST['cart_ids'] ?? [];

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

  <!-- Bootstrap Icons CDN -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <!-- Google Font: Montserrat -->
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">

  <!-- Favicon -->
  <link rel="icon" type="image/png" href="../assets/images/favicon/favicon.svg">

  <!-- Bootstrap 5 CSS -->
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
  </style>
</head>
<body>
<!-- Navbar -->
<?php include '../components/navigation.php'; ?>

<div class="container py-5">
  <h3 class="mb-4" style="color:var(--primary);"><i class="bi bi-bag-check"></i> Checkout</h3>
  <?php if (empty($items)): ?>
    <div class="alert alert-warning">No items selected for checkout.</div>
  <?php else: ?>
    <div class="card shadow-sm rounded-0 mb-4">
      <div class="card-body">
        <table class="table table-bordered align-middle">
          <thead>
            <tr>
              <th>Product</th>
              <th>Details</th>
              <th>Price</th>
              <th>Qty</th>
              <th>Subtotal</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($items as $item): ?>
              <tr>
                <td>
                  <img src="../<?php echo htmlspecialchars($item['main_image'] ?? 'assets/images/no_image.png'); ?>" style="width:60px;height:60px;object-fit:cover;">
                  <br>
                  <?php echo htmlspecialchars($item['product_name']); ?>
                </td>
                <td>
                  <?php
                    $details = [];
                    if (!empty($item['size']) && $item['size'] !== 'Not Available') $details[] = 'Size: ' . htmlspecialchars($item['size']);
                    if (!empty($item['color']) && $item['color'] !== 'Not Available') $details[] = 'Color: ' . htmlspecialchars($item['color']);
                    echo implode(' | ', $details);
                  ?>
                </td>
                <td>₱<?php echo number_format($item['display_price'], 2); ?></td>
                <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                <td>₱<?php echo number_format($item['subtotal'], 2); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr>
              <th colspan="4" class="text-end">Total:</th>
              <th>₱<?php echo number_format($total, 2); ?></th>
            </tr>
          </tfoot>
        </table>
        <form method="POST" action="../config/place_order.php">
          <?php foreach ($cart_ids as $cid): ?>
            <input type="hidden" name="cart_ids[]" value="<?php echo htmlspecialchars($cid); ?>">
          <?php endforeach; ?>
          <button type="submit" class="btn btn-accent btn-lg mt-3"><i class="bi bi-bag-check"></i> Place Order</button>
        </form>
        <a href="my_account.php?tab=cart" class="btn btn-secondary mt-3 ms-2">Back to Cart</a>
      </div>
    </div>
  <?php endif; ?>
</div>

<!-- Footer -->
<?php include '../components/footer.php'; ?>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" 
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" 
        crossorigin="anonymous"></script>
</body>
</html>