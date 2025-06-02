<?php
session_start();
require_once 'db.php';

// Always set JSON header for AJAX
$is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
if ($is_ajax) {
    header('Content-Type: application/json');
}

if (!isset($_SESSION['user_id'])) {
    if ($is_ajax) {
        echo json_encode(['success' => false, 'message' => 'Please login first.']);
        exit;
    } else {
        header("Location: ../front-pages/my_account.php?tab=cart&error=" . urlencode("Please login first."));
        exit;
    }
}

$user_id = $_SESSION['user_id'];
$cart_id = intval($_POST['cart_id'] ?? 0);

if ($cart_id <= 0) {
    if ($is_ajax) {
        echo json_encode(['success' => false, 'message' => 'Invalid cart item.']);
        exit;
    } else {
        header("Location: ../front-pages/my_account.php?tab=cart&error=" . urlencode("Invalid cart item."));
        exit;
    }
}

// Get product info for logging
$stmt = $conn->prepare("SELECT c.quantity, pv.id AS variation_id, p.name, pv.size, pv.color FROM cart c JOIN product_variations pv ON c.variation_id = pv.id JOIN products p ON pv.product_id = p.id WHERE c.id=? AND c.user_id=?");
$stmt->bind_param("ii", $cart_id, $user_id);
$stmt->execute();
$stmt->bind_result($quantity, $variation_id, $product_name, $size, $color);
if (!$stmt->fetch()) {
    $stmt->close();
    if ($is_ajax) {
        echo json_encode(['success' => false, 'message' => 'Cart item not found.']);
        exit;
    } else {
        header("Location: ../front-pages/my_account.php?tab=cart&error=" . urlencode("Cart item not found."));
        exit;
    }
}
$stmt->close();

// Delete the cart item
$stmt = $conn->prepare("DELETE FROM cart WHERE id=? AND user_id=?");
$stmt->bind_param("ii", $cart_id, $user_id);
if ($stmt->execute()) {
    // Log the removal
    $desc = "Removed {$quantity} x '{$product_name}'";
    if ($size && $size !== 'Not Available') $desc .= " (Size: {$size}";
    if ($color && $color !== 'Not Available') $desc .= (strpos($desc, '(') !== false ? ' / ' : ' (') . "Color: {$color}";
    if (strpos($desc, '(') !== false) $desc .= ')';
    $activity_type = "Cart Item Removed";
    $stmt_log = $conn->prepare("INSERT INTO customer_activity_logs (user_id, activity_type, description) VALUES (?, ?, ?)");
    $stmt_log->bind_param("iss", $user_id, $activity_type, $desc);
    $stmt_log->execute();
    $stmt_log->close();

    if ($is_ajax) {
        echo json_encode(['success' => true, 'message' => 'Item removed from cart.']);
        exit;
    } else {
        header("Location: ../front-pages/my_account.php?tab=cart&success=" . urlencode("Item removed from cart."));
        exit;
    }
} else {
    if ($is_ajax) {
        echo json_encode(['success' => false, 'message' => 'Failed to remove item from cart.']);
        exit;
    } else {
        header("Location: ../front-pages/my_account.php?tab=cart&error=" . urlencode("Failed to remove item from cart."));
        exit;
    }
}
?>