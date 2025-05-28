<?php
session_start();
// Check if the admin is logged in (Crucial for all admin pages)
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    $_SESSION['login_message'] = [
        'text' => 'Please log in to access this page.',
        'type' => 'info'
    ];
    header('Location: ../login.php');
    exit;
}

require_once '../../config/db.php'; // Include your database connection
require_once '../includes/logger.php'; // Include the logger function

// --- Automatic Log Deletion Logic (Older than 60 days) ---
$days_to_keep = 60;
$cutoff_date = date('Y-m-d H:i:s', strtotime("-{$days_to_keep} days"));

$delete_stmt = $conn->prepare("DELETE FROM activity_logs WHERE log_time < ?");
if ($delete_stmt) {
    $delete_stmt->bind_param("s", $cutoff_date);
    $delete_stmt->execute();
    $rows_deleted = $delete_stmt->affected_rows;
    $delete_stmt->close();

    // Optionally log this automatic cleanup action (consider if it's too frequent)
    // For now, let's skip logging the cleanup itself to avoid an infinite loop of logs.
} else {
    // Log error if deletion fails
    error_log("Error preparing automatic log deletion: " . $conn->error);
}
// --- END Automatic Log Deletion Logic ---

// --- Date Filtering Logic ---
$filter_start_date = $_GET['start_date'] ?? '';
$filter_end_date = $_GET['end_date'] ?? '';

// Build the SQL query with optional date filters
$sql = "SELECT au.username, al.description, al.log_time
        FROM activity_logs al
        JOIN admin_users au ON al.admin_id = au.id";

$conditions = [];
$params = [];
$types = '';

if (!empty($filter_start_date)) {
    $conditions[] = "al.log_time >= ?";
    $params[] = $filter_start_date . " 00:00:00"; // Start of the day
    $types .= 's';
}

if (!empty($filter_end_date)) {
    $conditions[] = "al.log_time <= ?";
    $params[] = $filter_end_date . " 23:59:59"; // End of the day
    $types .= 's';
}

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$sql .= " ORDER BY al.log_time DESC"; // Order by most recent first

?>

<h2 class="mb-4">Activity Log History</h2>
<div class="card shadow-sm mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Filter Activity Log</span>
    </div>
    <div class="card-body">
        <form id="logFilterForm" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="startDate" class="form-label">From Date:</label>
                <input type="date" class="form-control" id="startDate" name="start_date" value="<?php echo htmlspecialchars($filter_start_date); ?>">
            </div>
            <div class="col-md-4">
                <label for="endDate" class="form-label">To Date:</label>
                <input type="date" class="form-control" id="endDate" name="end_date" value="<?php echo htmlspecialchars($filter_end_date); ?>">
            </div>
            <div class="col-md-auto">
                <button type="submit" class="btn btn-accent"><i class="bi bi-funnel me-2"></i>Apply Filter</button>
                <button type="button" class="btn btn-secondary ms-2" id="resetFilterBtn"><i class="bi bi-arrow-counterclockwise me-2"></i>Reset Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Admin Activity Log</span>
        <button class="btn btn-sm btn-danger"
                data-bs-toggle="modal" data-bs-target="#clearAllLogsModal">
            <i class="bi bi-trash-fill me-2"></i>Clear All History
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Admin Username</th>
                        <th>Description</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Use prepared statement for fetching logs to prevent SQL injection
                    $stmt = $conn->prepare($sql);
                    if ($stmt) {
                        if (!empty($params)) {
                            $stmt->bind_param($types, ...$params);
                        }
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo '<tr>';
                                echo '<td>' . htmlspecialchars($row['username']) . '</td>';
                                echo '<td>' . htmlspecialchars($row['description']) . '</td>';
                                echo '<td>' . htmlspecialchars(date('Y-m-d H:i:s', strtotime($row['log_time']))) . '</td>';
                                echo '</tr>';
                            }
                        } else {
                            echo '<tr><td colspan="3" class="text-center">No activity logs found for the selected date range.</td></tr>';
                        }
                        $result->free();
                        $stmt->close();
                    } else {
                        echo '<tr><td colspan="3" class="text-center text-danger">Error preparing log fetch query: ' . $conn->error . '</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="clearAllLogsModal" tabindex="-1" aria-labelledby="clearAllLogsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="clearAllLogsModalLabel">Confirm Clear All Activity History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form data-api-endpoint="api/clear_all_logs.php">
                <div class="modal-body">
                    <p class="lead">Are you absolutely sure you want to delete <strong class="text-danger">ALL</strong> activity log history?</p>
                    <p class="text-danger">This action cannot be undone.</p>
                    <div data-form-message class="mt-3"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Clear All Logs</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$conn->close();
?>