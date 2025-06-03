
<?php
session_start();
require_once __DIR__ . '/../config/db.php'; // Ensure this path is correct

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?error=" . urlencode("Please login to access your account."));
    exit;
}

$user_id = $_SESSION['user_id'];

// --- Fetch User Data ---
// Re-establish connection for user data if it was closed before
if (!isset($conn) || $conn->connect_error) {
    require_once __DIR__ . '/../config/db.php'; // Re-open connection
}
$stmt = $conn->prepare("SELECT name, email, gender, contact_number, shipping_address, profile_image FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($name, $email, $gender, $contact_number, $shipping_address, $profile_image);
$stmt->fetch();
$stmt->close();
// After fetching user data, $conn is implicitly closed if it's the only reference and goes out of scope, or explicitly if it's closed in db.php include.
// To be safe and consistent with activity log, we will re-open for cart.

$address = null;
if ($shipping_address) {
    $address = json_decode($shipping_address, true);
}

// --- Fetch Cart Items and Count ---
$cartItems = [];
$cart_total_amount = 0;
$cart_item_count = 0; // Initialize cart item count

// Re-establish connection for cart items and count
if (!isset($conn) || $conn->connect_error) {
    require_once __DIR__ . '/../config/db.php'; // Re-open connection
}

// Query to get total item count in cart (sum of quantities)
$stmt_count = $conn->prepare("SELECT SUM(quantity) AS total_quantity FROM cart WHERE user_id = ?");
if ($stmt_count) {
    $stmt_count->bind_param("i", $user_id);
    $stmt_count->execute();
    $result_count = $stmt_count->get_result();
    $row_count = $result_count->fetch_assoc();
    $cart_item_count = $row_count['total_quantity'] ?? 0;
    $stmt_count->close();
}


$sql_cart = "SELECT
                c.id AS cart_id,
                c.quantity,
                pv.id AS variation_id,
                pv.size,
                pv.color,
                pv.price AS variation_price,
                pv.discount_percentage,
                pv.discount_expiry_date,
                p.name AS product_name,
                (SELECT image_path FROM product_images WHERE product_id = p.id AND is_main = 1 LIMIT 1) AS main_image
             FROM
                cart c
             JOIN
                product_variations pv ON c.variation_id = pv.id
             JOIN
                products p ON pv.product_id = p.id
             WHERE
                c.user_id = ?";

$stmt_cart = $conn->prepare($sql_cart);
if ($stmt_cart) {
    $stmt_cart->bind_param("i", $user_id);
    $stmt_cart->execute();
    $result_cart = $stmt_cart->get_result();

    if ($result_cart->num_rows > 0) {
        while ($item = $result_cart->fetch_assoc()) {
            $current_price = (float)$item['variation_price'];
            $display_price = $current_price;
            $is_discounted = false;

            if ($item['discount_percentage'] !== null && $item['discount_expiry_date'] !== null) {
                $discount_expiry_timestamp = strtotime($item['discount_expiry_date']);
                if (time() <= $discount_expiry_timestamp) { // Check if discount is still active
                    $discount_amount = $current_price * ($item['discount_percentage'] / 100);
                    $display_price = $current_price - $discount_amount;
                    $is_discounted = true;
                }
            }

            $subtotal = $display_price * $item['quantity'];
            $cart_total_amount += $subtotal;

            $cartItems[] = [
                'cart_id' => $item['cart_id'],
                'product_name' => $item['product_name'],
                'size' => $item['size'],
                'color' => $item['color'],
                'quantity' => $item['quantity'],
                'original_price' => $current_price,
                'display_price' => $display_price,
                'is_discounted' => $is_discounted,
                'main_image' => $item['main_image'],
                'subtotal' => $subtotal
            ];
        }
    }
    $stmt_cart->close();
}
// $conn is implicitly closed here. It will be re-opened for activity log.

// --- Tab persistence logic ---
$active_tab = 'profile'; // Default active tab
if (isset($_GET['tab']) && in_array($_GET['tab'], ['profile', 'cart', 'orders', 'activity_log', 'ticket'])) {
    $active_tab = $_GET['tab'];
} else if (isset($_SESSION['active_tab']) && in_array($_SESSION['active_tab'], ['profile', 'cart', 'orders', 'activity_log', 'ticket'])) {
    $active_tab = $_SESSION['active_tab'];
}

// Clear active_tab from session after use
unset($_SESSION['active_tab']);

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
</head>

<style>
.list-group-item.active,
    .list-group-item.active:focus,
    .list-group-item.active:hover {
      background-color: var(--accent) !important;
      color: var(--text-light) !important;
      border: none;
    }

.settings-menu-parent.active,
.settings-menu-parent.active:focus,
.settings-menu-parent.active:hover {
  background-color: transparent !important;
  color: inherit !important;
  border: none;
}

  /* Remove arrows from number input */
input[type=number].no-arrow::-webkit-inner-spin-button, 
input[type=number].no-arrow::-webkit-outer-spin-button { 
  -webkit-appearance: none;
  margin: 0;
}
input[type=number].no-arrow {
  -moz-appearance: textfield;
}
</style>
<body>
<?php include '../components/navigation.php'; ?>


<?php
$success = isset($_GET['success']) ? urldecode($_GET['success']) : '';
$error = isset($_GET['error']) ? urldecode($_GET['error']) : '';
?>
<div aria-live="polite" aria-atomic="true" class="position-relative">
  <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
    <?php if ($success): ?>
      <div class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true"
           data-bs-autohide="true" data-bs-delay="5000" id="successToast">
        <div class="d-flex">
          <div class="toast-body">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?php echo htmlspecialchars($success); ?>
          </div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
      </div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="toast align-items-center text-bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true"
           data-bs-autohide="true" data-bs-delay="5000" id="errorToast">
        <div class="d-flex">
          <div class="toast-body">
            <i class="bi bi-x-circle-fill me-2"></i>
            <?php echo htmlspecialchars($error); ?>
          </div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<div class="container-fluid mt-5">
  <div class="row">
    <div class="col-md-3 px-3 mb-4"><div class="card shadow-sm rounded-0">
        <div class="card-body text-center">
          <?php if (!empty($profile_image)): ?>
            <img src="../uploads/profile/<?php echo htmlspecialchars($profile_image); ?>" alt="Profile Image" class="rounded-circle mb-2" style="width:100px;height:100px;object-fit:cover;">
          <?php else: ?>
            <i class="bi bi-person-circle" style="font-size:3rem;color:var(--primary);"></i>
          <?php endif; ?>
          <h4 class="mt-3 mb-1" style="color:var(--primary);"><?php echo htmlspecialchars($name); ?></h4>
          <p class="mb-3 text-muted"><?php echo htmlspecialchars($email); ?></p>
          <form action="../config/upload_profile_image.php" method="POST" enctype="multipart/form-data" class="mt-2">
            <input type="file" name="profile_image" accept="image/*" class="form-control form-control-sm mb-2" style="max-width:200px;display:inline-block;" required>
            <button type="submit" class="btn btn-accent btn-sm">Upload Profile</button>
          </form>
        </div>
        <div class="list-group list-group-flush">
          <a href="#profile" class="list-group-item list-group-item-action <?php echo ($active_tab == 'profile') ? 'active' : ''; ?>" data-bs-toggle="tab">
            <i class="bi bi-person me-2"></i> My Profile
          </a>
          <a href="#orders" class="list-group-item list-group-item-action <?php echo ($active_tab == 'orders') ? 'active' : ''; ?>" data-bs-toggle="tab">
            <i class="bi bi-bag-check me-2"></i> My Orders
          </a>
          <a href="#cart" class="list-group-item list-group-item-action <?php echo ($active_tab == 'cart') ? 'active' : ''; ?>" data-bs-toggle="tab">
            <i class="bi bi-cart me-2"></i> My Cart
            <?php if ($cart_item_count > 0): ?>
                <span class="badge bg-danger rounded-pill float-end"><?php echo $cart_item_count; ?></span>
            <?php endif; ?>
          </a>
          <a href="#ticket" class="list-group-item list-group-item-action <?php echo ($active_tab == 'ticket') ? 'active' : ''; ?>" data-bs-toggle="tab">
            <i class="bi bi-ticket-perforated me-2"></i> My Ticket
          </a>
          <a href="#activity_log" class="list-group-item list-group-item-action <?php echo ($active_tab == 'activity_log') ? 'active' : ''; ?>" data-bs-toggle="tab">
            <i class="bi bi-activity me-2"></i> Activity Log
          </a>
          <a href="../auth/logout.php" class="list-group-item list-group-item-action text-danger">
            <i class="bi bi-box-arrow-right me-2"></i> Logout
          </a>
        </div>
      </div>
    </div>
    <div class="col-md-9 px-3"><div class="tab-content card shadow-sm p-4 rounded-0">
        <div class="tab-pane fade <?php echo ($active_tab == 'profile') ? 'show active' : ''; ?>" id="profile">
          <h5 class="mb-3" style="color:var(--primary);">My Profile</h5>
          <p><strong>Name:</strong> <?php echo htmlspecialchars($name); ?></p>
          <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
          <p><strong>Gender:</strong> <?php echo htmlspecialchars($gender ?? ''); ?></p>
          <p><strong>Contact Number:</strong> <?php echo htmlspecialchars($contact_number ?? ''); ?></p>
          <?php if ($address): ?>
            <div class="mb-3">
              <strong>Shipping Address:</strong><br>
              <?php echo htmlspecialchars($address['street'] ?? ''); ?>
              <?php if (!empty($address['barangay'])) echo ', Barangay ' . htmlspecialchars($address['barangay']); ?>
              <?php if (!empty($address['city'])) echo ', ' . htmlspecialchars($address['city']); ?>
              <?php if (!empty($address['province'])) echo ', ' . htmlspecialchars($address['province']); ?>
              <?php if (!empty($address['region'])) echo ', ' . htmlspecialchars($address['region']); ?>
            </div>
            <button type="button" class="btn btn-accent btn-sm mb-3" data-bs-toggle="modal" data-bs-target="#editProfileModal">
              <i class="bi bi-pencil-square"></i> Edit Profile
            </button>
            <button type="button" class="btn btn-accent btn-sm mb-3" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                <i class="bi bi-key"></i> Change Password
            </button>
            
          <?php else: ?>
            <div class="mb-3 text-muted"><em>No shipping address saved yet.</em></div>
          <?php endif; ?>
          <hr>
          <h6 class="mb-2" style="color:var(--primary);">Edit Shipping Address</h6>
          <form id="shippingAddressForm" method="POST" action="../config/save_address.php">
            <div class="mb-2">
              <label for="region" class="form-label">Region</label>
              <select id="region" name="region" class="form-select" required>
                <option value="">Select Region</option>
              </select>
            </div>
            <div class="mb-2">
              <label for="province" class="form-label">Province</label>
              <select id="province" name="province" class="form-select" required>
                <option value="">Select Province</option>
              </select>
            </div>
            <div class="mb-2">
              <label for="city" class="form-label">City/Municipality</label>
              <select id="city" name="city" class="form-select" required>
                <option value="">Select City/Municipality</option>
              </select>
            </div>
            <div class="mb-2">
              <label for="barangay" class="form-label">Barangay</label>
              <input id="barangay" name="barangay" class="form-control" type="text" placeholder="Enter Barangay" required
                value="<?php echo isset($address['barangay']) ? htmlspecialchars($address['barangay']) : ''; ?>">
            </div>
            <div class="mb-2">
              <label for="street" class="form-label">Street Address</label>
              <input id="street" name="street" class="form-control" type="text" placeholder="Enter Street Address" required
                value="<?php echo isset($address['street']) ? htmlspecialchars($address['street']) : ''; ?>">
            </div>
            <button type="submit" class="btn btn-accent mt-2">Save Address</button>
          </form>
        </div>
       <div class="tab-pane fade <?php echo ($active_tab == 'cart') ? 'show active' : ''; ?>" id="cart">
          <h5 class="mb-3" style="color:var(--primary);">My Cart</h5>
          <?php if (!empty($cartItems)): ?>
            <form id="checkoutForm" method="POST" action="checkout.php">
              <div class="table-responsive" style="max-height: 800px; overflow-y: auto;">
                <table class="table table-hover align-middle">
                  <thead>
                    <tr>
                      <th scope="col">Select All
                        <input type="checkbox" id="selectAllCart" />
                      </th>
                      <th scope="col">Product Image</th>
                      <th scope="col">Product Details</th>
                      <th scope="col">Price</th>
                      <th scope="col">Quantity</th>
                      <th scope="col">Subtotal</th>
                      <th scope="col">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php foreach ($cartItems as $item): ?>
                    <tr>
                      <td>
                        <input type="checkbox" name="cart_ids[]" value="<?php echo htmlspecialchars($item['cart_id']); ?>" class="cart-item-checkbox" data-price="<?php echo $item['display_price']; ?>" data-qty="<?php echo $item['quantity']; ?>" data-subtotal="<?php echo $item['subtotal']; ?>" />
                      </td>
                      <td>
                        <?php
                        $imagePath = !empty($item['main_image']) ? '../' . htmlspecialchars($item['main_image']) : '../assets/images/no_image.png';
                        ?>
                        <img src="<?php echo $imagePath; ?>" class="img-thumbnail" alt="<?php echo htmlspecialchars($item['product_name']); ?>" style="width: 60px; height: 60px; object-fit: cover;">
                      </td>
                      <td>
                        <div><strong><?php echo htmlspecialchars($item['product_name']); ?></strong></div>
                        <div>
                          <?php
                            $details = [];
                            if (!empty($item['size']) && $item['size'] !== 'Not Available') $details[] = 'Size: ' . htmlspecialchars($item['size']);
                            if (!empty($item['color']) && $item['color'] !== 'Not Available') $details[] = 'Color: ' . htmlspecialchars($item['color']);
                            echo implode(' | ', $details);
                          ?>
                        </div>
                      </td>
                      <td>
                        <?php if ($item['is_discounted']): ?>
                          <span class="text-muted text-decoration-line-through">₱<?php echo number_format($item['original_price'], 2); ?></span>
                          <strong class="ms-1">₱<?php echo number_format($item['display_price'], 2); ?></strong>
                        <?php else: ?>
                          <strong>₱<?php echo number_format($item['display_price'], 2); ?></strong>
                        <?php endif; ?>
                      </td>
                      <td>
                        <div class="update-quantity-form d-flex align-items-center" data-cart-id="<?php echo $item['cart_id']; ?>">
                          <button type="button" class="btn btn-outline-secondary btn-sm btn-qty-minus" aria-label="Decrease quantity">-</button>
                          <input type="number" name="quantity" min="1" value="<?php echo htmlspecialchars($item['quantity']); ?>" class="form-control form-control-sm mx-1 text-center no-arrow" style="width:60px;" readonly>
                          <button type="button" class="btn btn-outline-secondary btn-sm btn-qty-plus" aria-label="Increase quantity">+</button>
                          <input type="hidden" name="cart_id" value="<?php echo htmlspecialchars($item['cart_id']); ?>">
                        </div>
                      </td>
                      <td>
                        ₱<span class="item-subtotal"><?php echo number_format($item['subtotal'], 2); ?></span>
                      </td>
                      <td>
                        <form action="../config/remove_from_cart.php" method="POST" class="remove-from-cart-form d-inline">
                          <input type="hidden" name="cart_id" value="<?php echo htmlspecialchars($item['cart_id']); ?>">
                           <button type="button"
                                class="btn btn-danger btn-sm remove-from-cart-btn"
                                data-cart-id="<?php echo htmlspecialchars($item['cart_id']); ?>">
                          <i class="bi bi-trash"></i>
                        </button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
                  <tfoot>
                    <tr>
                      <th colspan="5" class="text-end">Total:</th>
                      <th colspan="2">₱<span id="cartTotalAmount">0.00</span></th>
                    </tr>
                  </tfoot>
                </table>
              </div>
              <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
                <button class="btn btn-accent btn-md" type="submit" id="proceedCheckoutBtn" disabled>
                  <i class="bi bi-cash-stack"></i> Proceed to Checkout
                </button>
              </div>
            </form>
          <?php else: ?>
            <div class="alert alert-info text-center" role="alert">
              <i class="bi bi-info-circle me-2"></i>Your cart is empty. Start shopping now!
            </div>
          <?php endif; ?>
        </div>

        <div class="tab-pane fade <?php echo ($active_tab == 'ticket') ? 'show active' : ''; ?>" id="ticket">
          <h5 class="mb-3" style="color:var(--primary);">My Ticket</h5>
          <p>Your support tickets will appear here.</p>
          <!-- You can add a table or ticket submission form here -->
        </div>

        <div class="tab-pane fade <?php echo ($active_tab == 'orders') ? 'show active' : ''; ?>" id="orders">
          <!-- My Orders content here -->
           <h5 class="mb-3" style="color:var(--primary);">My Orders</h5>
          <p>Your Orders will appear here.</p>
        </div>

        <div class="tab-pane fade <?php echo ($active_tab == 'activity_log') ? 'show active' : ''; ?>" id="activity_log">
          <h5 class="mb-3" style="color:var(--primary);">My Activity Log</h5>
          <form method="POST" action="../config/clear_activity_log.php" class="mb-3">
            <button type="submit" name="clear_activity_log" class="btn btn-danger btn-sm">
              <i class="bi bi-trash"></i> Clear All History
            </button>
          </form>
          <div class="table-responsive" style="max-height: 800px; overflow-y: auto;">
            <table class="table table-striped table-hover">
              <thead>
                <tr>
                  <th>Date & Time</th>
                  <th>Activity Type</th>
                  <th>Description</th>
                </tr>
              </thead>
              <tbody>
                <?php
                // Re-establish connection for this block as it was closed earlier
                // This ensures the connection is fresh for fetching activity logs
                require_once __DIR__ . '/../config/db.php';
                $user_id = $_SESSION['user_id'];
                // Select from customer_activity_logs table
                $stmt_logs = $conn->prepare("SELECT activity_type, description, activity_timestamp FROM customer_activity_logs WHERE user_id=? ORDER BY activity_timestamp DESC LIMIT 100");
                $stmt_logs->bind_param("i", $user_id);
                $stmt_logs->execute();
                $result_logs = $stmt_logs->get_result();

                if ($result_logs->num_rows > 0) {
                    while ($log = $result_logs->fetch_assoc()) {
                        echo '<tr>';
                        echo '<td>' . htmlspecialchars(date('M d, Y h:i A', strtotime($log['activity_timestamp']))) . '</td>';
                        echo '<td>' . htmlspecialchars($log['activity_type']) . '</td>';
                        echo '<td>' . htmlspecialchars($log['description']) . '</td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="3" class="text-center">No activity history found.</td></tr>';
                }
                $stmt_logs->close();
                $conn->close(); // Close connection after fetching logs for this block
                ?>
              </tbody>
            </table>
          </div>
        </div>
        </div>
    </div>
  </div>
</div>


<div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" method="POST" action="../config/edit_profile.php">
      <div class="modal-header">
        <h5 class="modal-title" id="editProfileModalLabel">Edit Profile</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label for="editName" class="form-label">Name</label>
          <input type="text" class="form-control" id="editName" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
        </div>
        <div class="mb-3">
          <label for="editGender" class="form-label">Gender</label>
          <select class="form-select" id="editGender" name="gender" required>
            <option value="Male" <?php if($gender=='Male') echo 'selected'; ?>>Male</option>
            <option value="Female" <?php if($gender=='Female') echo 'selected'; ?>>Female</option>
            <option value="Prefer not to say" <?php if($gender=='Prefer not to say') echo 'selected'; ?>>Prefer not to say</option>
          </select>
        </div>
        <div class="mb-3">
          <label for="editContact" class="form-label">Contact Number</label>
          <input type="text" class="form-control" id="editContact" name="contact_number" value="<?php echo htmlspecialchars($contact_number); ?>" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-accent">Save Changes</button>
      </div>
    </form>
  </div>
</div>


<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="../config/change_password.php" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="changePasswordModalLabel"> Change Password</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label for="current_password" class="form-label">Current Password</label>
          <input type="password" class="form-control" id="current_password" name="current_password" required>
        </div>
        <div class="mb-3">
          <label for="new_password" class="form-label">New Password</label>
          <input type="password" class="form-control" id="new_password" name="new_password" required>
        </div>
        <div class="mb-3">
          <label for="confirm_password" class="form-label">Confirm New Password</label>
          <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-accent">Save Changes</button>
      </div>
    </form>
  </div>
</div>
<br><br><br>

<?php include '../components/footer.php'; ?>


<script>
  const address = <?php echo json_encode($address ?? []); ?>;
  const activeTab = '<?php echo $active_tab; ?>'; // Pass the active tab to JavaScript
</script>

<script src="../assets/js/my_account.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
        crossorigin="anonymous"></script>
</body>
</html>








