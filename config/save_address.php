<?php
session_start();
require_once 'db.php'; // Update path if needed

if (!isset($_SESSION['user_id'])) {
    header('Location: ../front-pages/index.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get POST data and sanitize
$region = $_POST['region'] ?? '';
$province = $_POST['province'] ?? '';
$city = $_POST['city'] ?? '';
$barangay = $_POST['barangay'] ?? '';
$street = $_POST['street'] ?? '';

$shipping_address = json_encode([
    'region' => $region,
    'province' => $province,
    'city' => $city,
    'barangay' => $barangay,
    'street' => $street
]);

// Fetch old shipping address to compare for description
$stmt_old_address = $conn->prepare("SELECT shipping_address FROM users WHERE id=?");
$stmt_old_address->bind_param("i", $user_id);
$stmt_old_address->execute();
$stmt_old_address->bind_result($old_shipping_address_json);
$stmt_old_address->fetch();
$stmt_old_address->close();
$old_address_data = json_decode($old_shipping_address_json, true);

// Update user's shipping address
$stmt = $conn->prepare("UPDATE users SET shipping_address = ? WHERE id = ?");
$stmt->bind_param("si", $shipping_address, $user_id);

if ($stmt->execute()) {
    // Log the activity to customer_activity_logs table
    $description = "Shipping address updated: ";
    $changes = [];

    $current_address_data = json_decode($shipping_address, true);

    foreach ($current_address_data as $key => $value) {
        if (!isset($old_address_data[$key]) || $old_address_data[$key] !== $value) {
            $old_value = isset($old_address_data[$key]) ? $old_address_data[$key] : 'not set';
            $changes[] = ucfirst($key) . " from '{$old_value}' to '{$value}'";
        }
    }

    if (empty($changes) && $old_shipping_address_json === $shipping_address) {
        $description = "Shipping address update attempted with no changes.";
    } else {
        $description .= implode(", ", $changes) . ".";
    }

    $activity_type = "Address Update";
    // Use customer_activity_logs table
    $stmt_log = $conn->prepare("INSERT INTO customer_activity_logs (user_id, activity_type, description) VALUES (?, ?, ?)");
    $stmt_log->bind_param("iss", $user_id, $activity_type, $description);
    $stmt_log->execute();
    $stmt_log->close();

    header('Location: ../front-pages/my_account.php?success=' . urlencode('Shipping address saved successfully!'));
    exit();
} else {
    header('Location: ../front-pages/my_account.php?error=' . urlencode('Failed to save shipping address. Please try again.'));
    exit();
}
?>