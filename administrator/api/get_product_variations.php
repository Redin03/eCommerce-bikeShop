<?php
session_start();
header('Content-Type: application/json');

// Disable error display to prevent HTML output breaking JSON response
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once '../../config/db.php'; // Adjust path if necessary

$response = ['success' => false, 'message' => '', 'variations' => []];

// Check if the admin is logged in (Crucial for all admin actions)
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    $response['message'] = 'Unauthorized access.';
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $product_id = filter_var($_GET['product_id'] ?? '', FILTER_VALIDATE_INT);

    if (!$product_id) {
        $response['message'] = 'Invalid product ID.';
        echo json_encode($response);
        exit;
    }

    $stmt = $conn->prepare("SELECT id, size, color, discount_percentage, discount_expiry_date FROM product_variations WHERE product_id = ? ORDER BY size, color");
    if ($stmt) {
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $variations = [];
        while ($row = $result->fetch_assoc()) {
            $variations[] = [
                'id' => $row['id'],
                'text' => htmlspecialchars("Size: {$row['size']}, Color: {$row['color']} (Current Discount: " . ($row['discount_percentage'] ?? 'None') . "%)")
            ];
        }

        $response['success'] = true;
        $response['variations'] = $variations;
        $stmt->close();
    } else {
        $response['message'] = 'Database error: ' . $conn->error;
        error_log("Error preparing variation fetch query: " . $conn->error);
    }

    $conn->close();
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>