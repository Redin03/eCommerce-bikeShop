<?php
// submenu/settings_users.php
session_start();

// Initialize message variables
$message = '';
$message_type = 'info';

// Check for toast messages from previous redirects (e.g., after adding a user)
if (isset($_SESSION['toast_message'])) {
    $message = $_SESSION['toast_message'];
    $message_type = ($_SESSION['toast_type'] === 'success') ? 'success' : 'danger';
    // Clear the session variables after displaying them
    unset($_SESSION['toast_message'], $_SESSION['toast_type']);
}

// Include database connection (adjust path as necessary)
require_once __DIR__ . '/../../config/db.php';

$adminUsers = [];
try {
    // Check if $conn is valid before using it
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception("Database connection not established or failed: " . ($conn->connect_error ?? 'Unknown error'));
    }

    // Fetch admin users from the database
    // Ensure 'created_at' column exists in your 'admin_users' table
    $stmt = $conn->prepare("SELECT id, username, last_login, created_at FROM admin_users ORDER BY created_at DESC");
    if (!$stmt) {
        throw new Exception("Prepare statement failed: " . $conn->error);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $adminUsers[] = $row;
    }
    $stmt->close();
} catch (Exception $e) {
    error_log("Error fetching admin users: " . $e->getMessage());
    $message = "Error loading admin users: " . $e->getMessage();
    $message_type = "danger";
} finally {
    // Close the connection if it was opened by this script and is not shared globally
    if (isset($conn) && $conn instanceof mysqli && !$conn->connect_error) {
        $conn->close();
    }
}
?>

<h2 class="mb-4">Settings: User Management</h2>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="alert alert-info" role="alert">
    Manage administrative users and their permissions. This is a sub-menu item under Settings.
</div>

<div class="card shadow-sm">
    <div class="card-header">
        Admin Users
    </div>
    <div class="card-body">
        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Last Login</th>
                        <th>Account Create</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($adminUsers)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted">No admin users found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($adminUsers as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['id']); ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo $user['last_login'] ? htmlspecialchars($user['last_login']) : 'Never'; ?></td>
                                <td>
                                    <?php
                                    // Check if created_at is set and not empty
                                    if (!empty($user['created_at'])) {
                                        // Attempt to create a DateTime object
                                        $createdAt = new DateTime($user['created_at']);
                                        // Format as "Month Day, Year Time" (e.g., March 7, 2025 14:30:00)
                                        echo $createdAt->format('F j, Y H:i:s');
                                    } else {
                                        echo 'N/A'; // Or leave empty, or provide a default value
                                    }
                                    ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-danger delete-user-btn"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteUserModal"
                                            data-user-id="<?php echo htmlspecialchars($user['id']); ?>"
                                            data-username="<?php echo htmlspecialchars($user['username']); ?>">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <button class="btn btn-accent mt-3" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="bi bi-person-plus-fill me-2"></i>Add New User
        </button>
    </div>
</div>

<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-light">
                <h5 class="modal-title" id="addUserModalLabel">Add New Admin Account</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addUserForm" action="api/add_admin_user.php" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                </div>
                <input type="hidden" name="redirect_url" value="http://localhost/bongbicycleshop/administrator/index.php#settings_users">
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-accent">Create Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-light">
                <h5 class="modal-title" id="deleteUserModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="deleteUserForm" action="api/delete_admin_user.php" method="POST">
                <div class="modal-body">
                    <p>Are you sure you want to delete the admin account:</p>
                    <p><strong>Username: </strong><span id="modalUsername"></span></p>
                    <p><strong>ID: </strong><span id="modalUserId"></span></p>
                    <p class="text-danger"><small>This action cannot be undone.</small></p>
                    <input type="hidden" name="user_id" id="deleteUserId">
                </div>
                <input type="hidden" name="redirect_url" value="http://localhost/bongbicycleshop/administrator/index.php#settings_users">
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger"><i class="bi bi-trash"></i> Delete Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    /*
    NOTE: As previously discussed, these CSS rules would ideally reside in your main stylesheet
    (e.g., style.css) that is loaded once in index.php. Placing them here means they
    will be loaded and parsed every time settings_users.php is dynamically fetched.
    */

    :root {
        --primary: #006A4E;
        --secondary: #FFB703;
        --accent: #00BFA6;
        --bg-light: #F4F4F4;
        --bg-dark: #003D33;
        --text-dark: #1E1E1E;
        --text-light: #FFFFFF;
        --border-gray: #D9D9D9;
        --header-height: 66px;
        --sidebar-width: 250px;
        --sidebar-collapsed-width: 70px;
        --toggle-button-size: 40px;
    }

    /* Styles for the "Add New User" button (using btn-accent) */
    .btn-accent {
        background-color: var(--accent);
        color: var(--text-light);
        border: none;
        transition: background-color 0.2s ease, color 0.2s ease;
    }

    .btn-accent:hover {
        background-color: var(--secondary);
        color: var(--text-dark);
    }

    /* Styles for the modal header */
    .modal-header.bg-primary {
        background-color: var(--primary) !important; /* Override Bootstrap's bg-primary */
        color: var(--text-light);
    }

    .modal-header.bg-danger { /* For the delete modal header */
        background-color: #dc3545 !important; /* Bootstrap's default danger color */
        color: var(--text-light);
    }

    .modal-header .btn-close {
        filter: invert(1); /* Invert the color of the close button icon to make it white */
    }

    /* Styles for modal footer buttons */
    /* Bootstrap 5 uses .btn-primary and .btn-secondary, applying the variable colors directly */
    .modal-footer .btn-primary { /* You might not use this class if you use btn-accent */
        background-color: var(--primary);
        border-color: var(--primary);
        color: var(--text-light);
    }

    .modal-footer .btn-primary:hover {
        background-color: var(--secondary);
        border-color: var(--secondary);
        color: var(--text-dark);
    }

    .modal-footer .btn-secondary {
        background-color: var(--border-gray);
        border-color: var(--border-gray);
        color: var(--text-dark);
    }

    .modal-footer .btn-secondary:hover {
        background-color: #c0c0c0;
        border-color: #c0c0c0;
    }

    /* Additional styles for the page content */
    .card {
        border: none;
        border-radius: 0.5rem;
    }
    .table-responsive {
        margin-top: 15px;
    }
</style>