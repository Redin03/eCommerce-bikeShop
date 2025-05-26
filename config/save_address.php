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

// Update user's shipping address
$stmt = $conn->prepare("UPDATE users SET shipping_address = ? WHERE id = ?");
$stmt->bind_param("si", $shipping_address, $user_id);

if ($stmt->execute()) {
    header('Location: ../front-pages/my_account.php?success=' . urlencode('Shipping address saved successfully!'));
    exit();
} else {
    header('Location: ../front-pages/my_account.php?error=' . urlencode('Failed to save shipping address. Please try again.'));
    exit();
}
?>