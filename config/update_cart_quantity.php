<?php
session_start();
header('Content-Type: application/json');
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$cart_id = intval($_POST['cart_id'] ?? 0);
$new_quantity = intval($_POST['quantity'] ?? 0);

if ($cart_id <= 0 || $new_quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid cart item or quantity.']);
    exit;
}

// Get old quantity and product info
$stmt = $conn->prepare("SELECT quantity, variation_id FROM cart WHERE id=? AND user_id=?");
$stmt->bind_param("ii", $cart_id, $user_id);
$stmt->execute();
$stmt->bind_result($old_quantity, $variation_id);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Cart item not found.']);
    exit;
}
$stmt->close();

if ($old_quantity == $new_quantity) {
    echo json_encode(['success' => false, 'message' => 'No changes made to quantity.']);
    exit;
}

// Update quantity
$stmt = $conn->prepare("UPDATE cart SET quantity=? WHERE id=? AND user_id=?");
$stmt->bind_param("iii", $new_quantity, $cart_id, $user_id);
if ($stmt->execute()) {
    // Get product name for log
    $stmt2 = $conn->prepare("SELECT p.name FROM product_variations pv JOIN products p ON pv.product_id = p.id WHERE pv.id=?");
    $stmt2->bind_param("i", $variation_id);
    $stmt2->execute();
    $stmt2->bind_result($product_name);
    $stmt2->fetch();
    $stmt2->close();

    // Log activity
    $desc = "Cart item updated: '$product_name' (Cart ID: $cart_id) quantity from '$old_quantity' to '$new_quantity'.";
    $activity_type = "Cart Item Edited";
    $stmt3 = $conn->prepare("INSERT INTO customer_activity_logs (user_id, activity_type, description) VALUES (?, ?, ?)");
    $stmt3->bind_param("iss", $user_id, $activity_type, $desc);
    $stmt3->execute();
    $stmt3->close();

    echo json_encode(['success' => true, 'message' => 'Quantity updated successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update quantity.']);
}
?>