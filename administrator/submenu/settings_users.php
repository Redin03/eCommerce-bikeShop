<?php
require_once '../../config/db.php'; // Include your database connection
?>

<h2 class="mb-4">Settings: User Management</h2>
<div class="card shadow-sm">
    <div class="card-header">
        Admin Users
    </div>
    <div class="card-body">
        <table class="table table-striped" style="font-size: 0.85em;">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Last Login</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT id, username, role, last_login FROM admin_users ORDER BY id ASC";
                $result = $conn->query($sql);

                if ($result) {
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo '<tr>';
                            echo '<td>' . htmlspecialchars($row['id']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['username']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['role']) . '</td>';
                            $last_login = $row['last_login'] ? date('Y-m-d h:i A', strtotime($row['last_login'])) : 'Never';
                            echo '<td>' . htmlspecialchars($last_login) . '</td>';
                            echo '<td>';
                            // Reset Password Button - Opens Reset Password Modal
                            echo '<button class="btn btn-sm btn-warning text-dark me-2"
                                data-bs-toggle="modal" data-bs-target="#resetPasswordModal"
                                data-id="' . htmlspecialchars($row['id']) . '"
                                data-username="' . htmlspecialchars($row['username']) . '">
                                <i class="bi bi-arrow-repeat me-1"></i>RESET</button>';
                            // Delete Button - Opens Delete Confirmation Modal
                            echo '<button class="btn btn-sm btn-danger"
                                data-bs-toggle="modal" data-bs-target="#deleteAdminModal"
                                data-id="' . htmlspecialchars($row['id']) . '"
                                data-username="' . htmlspecialchars($row['username']) . '">
                                <i class="bi bi-trash"></i>DELETE</button>';
                            echo '</td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="5" class="text-center">No admin users found.</td></tr>';
                    }
                    $result->free();
                } else {
                    echo '<tr><td colspan="5" class="text-center text-danger">Error fetching users: ' . $conn->error . '</td></tr>';
                }
                ?>
            </tbody>
        </table>
        <button class="btn btn-accent mt-3" data-bs-toggle="modal" data-bs-target="#addAdminModal">
            <i class="bi bi-person-plus-fill me-2"></i>Add New Admin
        </button>
    </div>
</div>

<div class="modal fade" id="addAdminModal" tabindex="-1" aria-labelledby="addAdminModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addAdminModalLabel">Add New Admin User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form data-api-endpoint="api/add_new_admin.php">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="adminUsername" class="form-label">Username</label>
                        <input type="text" class="form-control" id="adminUsername" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="adminPassword" class="form-label">Password</label>
                        <input type="password" class="form-control" id="adminPassword" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="adminConfirmPassword" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="adminConfirmPassword" name="confirm_password" required>
                    </div>
                    <div data-form-message class="mt-3"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-accent">Add Admin</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-labelledby="resetPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="resetPasswordModalLabel">Reset Password for <span id="resetAdminUsername"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form data-api-endpoint="api/reset_admin_password.php">
                <div class="modal-body">
                    <input type="hidden" id="resetAdminId" name="admin_id">
                    <p class="mb-3">Enter the new password for <strong id="resetAdminUsernameConfirm"></strong>:</p>
                    <div class="mb-3">
                        <label for="newPassword" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="newPassword" name="new_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirmNewPassword" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirmNewPassword" name="confirm_new_password" required>
                    </div>
                    <div data-form-message class="mt-3"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-accent text-dark">Reset Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteAdminModal" tabindex="-1" aria-labelledby="deleteAdminModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteAdminModalLabel">Confirm Delete Admin User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form data-api-endpoint="api/delete_admin.php">
                <div class="modal-body">
                    <input type="hidden" id="deleteAdminId" name="admin_id">
                    <p class="lead">Are you sure you want to delete admin user <strong id="deleteAdminUsername"></strong>?</p>
                    <p class="text-danger">This action cannot be undone.</p>
                    <div data-form-message class="mt-3"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete Admin</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$conn->close();
?>