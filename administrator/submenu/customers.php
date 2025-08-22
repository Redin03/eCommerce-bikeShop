<?php
session_start();
// Check if the admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    $_SESSION['login_message'] = [
        'text' => 'Please log in to access this page.',
        'type' => 'info'
    ];
    header('Location: ../login.php');
    exit;
}

require_once '../../config/db.php'; // Adjust path if necessary
require_once '../includes/logger.php'; // Adjust path if necessary

// Fetch all users from the database
$users = [];
$sql = "SELECT id, name, email, created_at, is_verified FROM users ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $users[] = [
                'id' => htmlspecialchars($row['id']),
                'name' => htmlspecialchars($row['name']),
                'email' => htmlspecialchars($row['email']),
                'created_at' => htmlspecialchars($row['created_at']),
                'is_verified' => (bool)$row['is_verified'] // Convert to boolean
            ];
        }
    }
    $result->free();
    $stmt->close();
} else {
    // Log database error
    error_log("Error preparing users fetch query: " . $conn->error);
}

$conn->close();

?>

<h2 class="mb-4">Customer List</h2>
<div class="alert alert-warning" role="alert">
  This is the content for the **Customers** page. Manage customer accounts and details.
</div>
<div class="card shadow-sm">
    <div class="card-header">
        Registered Customers
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover" style="font-size: 0.9em;">
                <thead>
                    <tr>
                        <th>Customer ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Joined Date</th>
                        <th>Total Orders</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($users)): ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= $user['id'] ?></td>
                                <td><?= $user['name'] ?></td>
                                <td><?= $user['email'] ?></td>
                                <td><?= date('Y-m-d', strtotime($user['created_at'])) ?></td>
                                <td>0</td>
                                <td>
                                    <?php if ($user['is_verified']): ?>
                                        <span class="badge bg-success">Verified</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Not Verified</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No registered customers found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>