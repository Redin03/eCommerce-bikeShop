<?php
session_start();
require_once __DIR__ . '/../config/db.php'; // Adjust path if necessary for your database connection

header('Content-Type: application/json'); // Respond with JSON

$response = ['success' => false, 'message' => ''];

if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Please log in to add items to your cart.';
    echo json_encode($response);
    exit;
}

$user_id = $_SESSION['user_id'];
$variation_id = $_POST['variation_id'] ?? null;
$quantity = $_POST['quantity'] ?? 1; // Default quantity to 1

if (!is_numeric($variation_id) || $variation_id <= 0) {
    $response['message'] = 'Invalid product variation selected.';
    echo json_encode($response);
    exit;
}

if (!is_numeric($quantity) || $quantity <= 0) {
    $response['message'] = 'Quantity must be a positive number.';
    echo json_encode($response);
    exit;
}

$conn->begin_transaction(); // Start transaction for atomicity

try {
    // 1. Fetch product and variation details, including stock and prices
    $stmt = $conn->prepare("SELECT
                                pv.product_id,
                                pv.stock,
                                pv.price,
                                pv.discount_percentage,
                                pv.discount_expiry_date,
                                p.name AS product_name,
                                pv.size,
                                pv.color
                            FROM
                                product_variations pv
                            JOIN
                                products p ON pv.product_id = p.id
                            WHERE
                                pv.id = ? FOR UPDATE"); // FOR UPDATE to lock row during transaction
    if (!$stmt) {
        throw new Exception("Failed to prepare statement to fetch variation details: " . $conn->error);
    }
    $stmt->bind_param("i", $variation_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $variation_data = $result->fetch_assoc();
    $stmt->close();

    if (!$variation_data) {
        throw new Exception('Product variation not found.');
    }

    $product_id = $variation_data['product_id'];
    $current_stock = $variation_data['stock'];
    $original_price = (float)$variation_data['price'];
    $discount_percentage = $variation_data['discount_percentage'];
    $discount_expiry_date = $variation_data['discount_expiry_date'];
    $product_name = $variation_data['product_name'];
    $size = $variation_data['size'];
    $color = $variation_data['color'];

    // Calculate effective price at the time of addition
    $price_at_addition = $original_price;
    if ($discount_percentage !== null && $discount_expiry_date !== null) {
        $discount_expiry_timestamp = strtotime($discount_expiry_date);
        if (time() <= $discount_expiry_timestamp) {
            $discount_amount = $original_price * ($discount_percentage / 100);
            $price_at_addition = $original_price - $discount_amount;
        }
    }

    // IMPORTANT: Stock check remains, but deduction is removed from here.
    // Stock will only be deducted upon order completion.
    if ($current_stock < $quantity) {
        throw new Exception('Not enough stock available for this variation. Available: ' . $current_stock);
    }

    // 2. Check if item already exists in cart for this user and variation
    $stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND variation_id = ? FOR UPDATE");
    if (!$stmt) {
        throw new Exception("Failed to prepare statement to check cart: " . $conn->error);
    }
    $stmt->bind_param("ii", $user_id, $variation_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $cart_item = $result->fetch_assoc();
    $stmt->close();

    if ($cart_item) {
        // Item exists, update quantity
        $new_quantity_in_cart = $cart_item['quantity'] + $quantity;
        // Re-check stock against the *new total quantity in cart*
        if ($current_stock < $new_quantity_in_cart) {
            throw new Exception('Adding this quantity would exceed available stock. Current in cart: ' . $cart_item['quantity'] . ', Available to add: ' . ($current_stock - $cart_item['quantity']));
        }

        $stmt = $conn->prepare("UPDATE cart SET quantity = ?, price_at_addition = ? WHERE id = ?");
        if (!$stmt) {
            throw new Exception("Failed to prepare statement to update cart: " . $conn->error);
        }
        $stmt->bind_param("idi", $new_quantity_in_cart, $price_at_addition, $cart_item['id']);
        $stmt->execute();
        $stmt->close();
        $cart_action = "updated";
    } else {
        // Item does not exist, insert new
        $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, variation_id, quantity, price_at_addition) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Failed to prepare statement to insert into cart: " . $conn->error);
        }
        $stmt->bind_param("iiiid", $user_id, $product_id, $variation_id, $quantity, $price_at_addition);
        $stmt->execute();
        $stmt->close();
        $cart_action = "added";
    }

    // **Stock deduction logic is REMOVED from here.**
    // It should be handled during the order completion process.

    // 3. Log customer activity
    $activity_type = "Cart Interaction";
    $description = "Product '{$product_name}' (Variation: Size: {$size}, Color: {$color}) {$cart_action} to cart. Quantity: {$quantity}.";

    $stmt_log = $conn->prepare("INSERT INTO customer_activity_logs (user_id, activity_type, description) VALUES (?, ?, ?)");
    if (!$stmt_log) {
        throw new Exception("Failed to prepare statement to log activity: " . $conn->error);
    }
    $stmt_log->bind_param("iss", $user_id, $activity_type, $description);
    $stmt_log->execute();
    $stmt_log->close();

    $conn->commit(); // Commit the transaction
    $response['success'] = true;
    $response['message'] = 'Item successfully ' . $cart_action . ' to cart!';

} catch (Exception $e) {
    $conn->rollback(); // Rollback on error
    $response['message'] = 'Error: ' . $e->getMessage();
} finally {
    $conn->close();
    echo json_encode($response);
}
?>