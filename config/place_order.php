<?php
session_start();
require_once 'db.php'; // Your database connection file

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php?error=" . urlencode("Please login to place an order."));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../front-pages/checkout.php?error=" . urlencode("Invalid request method."));
    exit;
}

$user_id = $_SESSION['user_id'];

// Retrieve form data
$cart_ids = $_POST['cart_ids'] ?? [];
$full_name = htmlspecialchars(trim($_POST['full_name'] ?? ''));
$email = htmlspecialchars(trim($_POST['email'] ?? ''));
$contact_number = htmlspecialchars(trim($_POST['contact_number'] ?? ''));
$payment_method = htmlspecialchars(trim($_POST['payment_method'] ?? ''));
$region = htmlspecialchars(trim($_POST['region'] ?? ''));
$province = htmlspecialchars(trim($_POST['province'] ?? ''));
$city = htmlspecialchars(trim($_POST['city'] ?? ''));
$barangay = htmlspecialchars(trim($_POST['barangay'] ?? ''));
$street = htmlspecialchars(trim($_POST['street'] ?? ''));

// Validate essential fields
if (empty($cart_ids) || empty($full_name) || empty($email) || empty($contact_number) || empty($payment_method)) {
    header("Location: ../front-pages/checkout.php?error=" . urlencode("All required fields must be filled."));
    exit;
}

// Validate shipping address fields only if not 'Store Pickup'
if ($payment_method !== 'Store Pickup') {
    if (empty($region) || empty($province) || empty($city) || empty($barangay) || empty($street)) {
        header("Location: ../front-pages/checkout.php?error=" . urlencode("All shipping address fields must be filled for delivery."));
        exit;
    }
}

// Combine shipping address into a JSON string for easy storage
$shipping_address_data = [
    'region' => $region,
    'province' => $province,
    'city' => $city,
    'barangay' => $barangay,
    'street' => $street
];
$shipping_address_json = json_encode($shipping_address_data);

$proof_of_payment_image_path = null;

// Handle GCash proof of payment upload
if ($payment_method === 'GCash') {
    if (isset($_FILES['proof_of_payment']) && $_FILES['proof_of_payment']['error'] === UPLOAD_ERR_OK) {
        $file_tmp_name = $_FILES['proof_of_payment']['tmp_name'];
        $file_name = $_FILES['proof_of_payment']['name'];
        $file_size = $_FILES['proof_of_payment']['size'];
        $file_type = $_FILES['proof_of_payment']['type'];

        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($file_type, $allowed_types)) {
            header("Location: ../front-pages/checkout.php?error=" . urlencode("Invalid file type for proof of payment. Only images (JPEG, PNG, GIF) are allowed."));
            exit;
        }

        $upload_dir = '../uploads/proof_of_payments/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true); // Create directory if it doesn't exist
        }

        $new_file_name = uniqid('proof_') . '_' . time() . '.' . pathinfo($file_name, PATHINFO_EXTENSION);
        $destination = $upload_dir . $new_file_name;

        if (move_uploaded_file($file_tmp_name, $destination)) {
            $proof_of_payment_image_path = 'uploads/proof_of_payments/' . $new_file_name;
        } else {
            header("Location: ../front-pages/checkout.php?error=" . urlencode("Failed to upload proof of payment image."));
            exit;
        }
    } else {
        // If GCash is selected but no file is uploaded and it's required
        header("Location: ../front-pages/checkout.php?error=" . urlencode("Proof of payment is required for GCash."));
        exit;
    }
}


// --- Re-calculate total amount server-side for security and fetch item details ---
$total_amount_calculated = 0;
$order_items_data = [];

// Prepare placeholders for SQL IN clause for cart items
$placeholders = implode(',', array_fill(0, count($cart_ids), '?'));
$types = str_repeat('i', count($cart_ids));

$sql_fetch_cart_items = "SELECT c.id AS cart_id, c.quantity, pv.id AS variation_id, pv.product_id, pv.price AS variation_price, pv.discount_percentage, pv.discount_expiry_date, pv.stock
                         FROM cart c
                         JOIN product_variations pv ON c.variation_id = pv.id
                         WHERE c.user_id = ? AND c.id IN ($placeholders)";
$stmt_fetch_cart = $conn->prepare($sql_fetch_cart_items);
$params_fetch_cart = array_merge([$user_id], $cart_ids);
$stmt_fetch_cart->bind_param('i' . $types, ...$params_fetch_cart);
$stmt_fetch_cart->execute();
$result_fetch_cart = $stmt_fetch_cart->get_result();

if ($result_fetch_cart->num_rows === 0) {
    header("Location: ../front-pages/my_account.php?tab=cart&error=" . urlencode("No valid items found in cart for checkout."));
    exit;
}

while ($item = $result_fetch_cart->fetch_assoc()) {
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
    $total_amount_calculated += $subtotal;

    // Check stock
    if ($item['stock'] < $item['quantity']) {
        // Handle insufficient stock (e.g., redirect back with an error)
        header("Location: ../front-pages/checkout.php?error=" . urlencode("Insufficient stock for one or more items. Please adjust quantities."));
        exit;
    }

    $order_items_data[] = [
        'cart_id' => $item['cart_id'],
        'product_id' => $item['product_id'],
        'variation_id' => $item['variation_id'],
        'quantity' => $item['quantity'],
        'price_at_order' => $display_price,
        'subtotal_at_order' => $subtotal,
    ];
}
$stmt_fetch_cart->close();


// --- Start Transaction ---
$conn->begin_transaction();

try {
    // Generate a unique reference number
    // Format: ORD +YYYYMMDD + 6 random hex characters
    $reference_number = 'ORD' . date('Ymd') . strtoupper(bin2hex(random_bytes(3)));

    // 1. Insert into orders table
    $sql_insert_order = "INSERT INTO orders (reference_number, user_id, total_amount, payment_method, proof_of_payment_image, full_name, email, contact_number, shipping_address, order_status)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_order = $conn->prepare($sql_insert_order);

    // Set order status to 'Pending' for all payment methods initially
    $order_status = 'Pending';

    $stmt_order->bind_param("sidsssssss", $reference_number, $user_id, $total_amount_calculated, $payment_method, $proof_of_payment_image_path, $full_name, $email, $contact_number, $shipping_address_json, $order_status);
    $stmt_order->execute();
    $order_id = $conn->insert_id;
    $stmt_order->close();

    // 2. Insert into order_items table, update product stock, and remove from cart
    $sql_insert_item = "INSERT INTO order_items (order_id, product_id, variation_id, quantity, price_at_order, subtotal_at_order) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt_item = $conn->prepare($sql_insert_item);

    $sql_update_stock = "UPDATE product_variations SET stock = stock - ? WHERE id = ?";
    $stmt_stock = $conn->prepare($sql_update_stock);

    $sql_delete_cart_item = "DELETE FROM cart WHERE id = ? AND user_id = ?";
    $stmt_delete_cart = $conn->prepare($sql_delete_cart_item);

    foreach ($order_items_data as $item) {
        // Insert order item
        $stmt_item->bind_param("iiiidd", $order_id, $item['product_id'], $item['variation_id'], $item['quantity'], $item['price_at_order'], $item['subtotal_at_order']);
        $stmt_item->execute();

        // Update product stock
        $stmt_stock->bind_param("ii", $item['quantity'], $item['variation_id']);
        $stmt_stock->execute();

        // Delete item from cart
        $stmt_delete_cart->bind_param("ii", $item['cart_id'], $user_id);
        $stmt_delete_cart->execute();
    }
    $stmt_item->close();
    $stmt_stock->close();
    $stmt_delete_cart->close();

    // 3. Log activity
    $activity_type = "Order Placed";
    $description = "New order placed (Ref: " . $reference_number . "). Total: ₱" . number_format($total_amount_calculated, 2) . ". Payment Method: " . $payment_method . ".";
    $stmt_log = $conn->prepare("INSERT INTO customer_activity_logs (user_id, activity_type, description) VALUES (?, ?, ?)");
    $stmt_log->bind_param("iss", $user_id, $activity_type, $description);
    $stmt_log->execute();
    $stmt_log->close();

    $conn->commit(); // Commit transaction

    // Set a session flag to indicate successful order confirmation
    $_SESSION['order_confirmed'] = true;

    header("Location: ../front-pages/order_confirmation.php?status=success&order_id=" . $order_id . "&ref=" . $reference_number);
    exit;

} catch (Exception $e) {
    $conn->rollback(); // Rollback on error
    error_log("Order Placement Error: " . $e->getMessage()); // Log the error
    header("Location: ../front-pages/checkout.php?status=error&message=" . urlencode("Failed to place order. Please try again. " . $e->getMessage()));
    exit;
} finally {
    $conn->close();
}
?>