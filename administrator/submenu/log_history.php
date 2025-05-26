<?php
// submenu/log_history.php
session_start();

// Define the path to the log file
$logFile = __DIR__ . '/../../logs/user_actions.log';

$logEntries = [];
$displayLogEntries = [];

// Get filter dates from GET request (when form is submitted)
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : '';

$filterStartTimestamp = !empty($startDate) ? strtotime($startDate . ' 00:00:00') : null;
$filterEndTimestamp = !empty($endDate) ? strtotime($endDate . ' 23:59:59') : null;

if (file_exists($logFile)) {
    $fileContent = file_get_contents($logFile);
    if ($fileContent !== false) {
        $rawLogEntries = explode(PHP_EOL, $fileContent);
        $rawLogEntries = array_filter($rawLogEntries);

        $processedLogEntries = [];
        foreach ($rawLogEntries as $entry) {
            if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\](.*)$/', $entry, $matches)) {
                $entryTimestamp = strtotime($matches[1]);
                if ($entryTimestamp !== false) {
                    $processedLogEntries[] = [
                        'timestamp' => $entryTimestamp,
                        'formatted_timestamp' => $matches[1],
                        'details' => trim($matches[2])
                    ];
                } else {
                    $processedLogEntries[] = [
                        'timestamp' => time(),
                        'formatted_timestamp' => 'N/A - ' . date('Y-m-d H:i:s'),
                        'details' => htmlspecialchars($entry)
                    ];
                }
            } else {
                 $processedLogEntries[] = [
                    'timestamp' => time(),
                    'formatted_timestamp' => 'N/A - ' . date('Y-m-d H:i:s'),
                    'details' => htmlspecialchars($entry)
                 ];
            }
        }

        usort($processedLogEntries, function($a, $b) {
            return $b['timestamp'] <=> $a['timestamp'];
        });

        $sixtyDaysAgo = strtotime('-60 days');
        foreach ($processedLogEntries as $entry) {
            if ($entry['timestamp'] >= $sixtyDaysAgo) {
                $passFilter = true;
                if ($filterStartTimestamp !== null && $entry['timestamp'] < $filterStartTimestamp) {
                    $passFilter = false;
                }
                if ($filterEndTimestamp !== null && $entry['timestamp'] > $filterEndTimestamp) {
                    $passFilter = false;
                }

                if ($passFilter) {
                    $displayLogEntries[] = $entry;
                }
            }
        }
    }
}

$message = '';
$message_type = 'info';
if (isset($_SESSION['log_clear_message'])) {
    $message = $_SESSION['log_clear_message'];
    $message_type = $_SESSION['log_clear_type'];
    unset($_SESSION['log_clear_message'], $_SESSION['log_clear_type']);
}
?>

<h2 class="mb-4">User Action Log History</h2>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Recent Actions</h4>
    <form action="http://localhost/ecommerce-bikeshop/administrator/index.php#log_history" method="GET" class="d-flex align-items-center me-3">
        <label for="start_date" class="form-label me-2 mb-0">From:</label>
        <input type="date" class="form-control form-control-sm me-2" id="start_date" name="start_date" value="<?php echo htmlspecialchars($startDate); ?>">

        <label for="end_date" class="form-label me-2 mb-0">To:</label>
        <input type="date" class="form-control form-control-sm me-2" id="end_date" name="end_date" value="<?php echo htmlspecialchars($endDate); ?>">

        <button type="submit" class="btn btn-accent btn-sm me-2">Filter</button>
        <button type="submit" class="btn btn-secondary btn-sm" name="reset_filter" value="1">Reset</button>
    </form>

    <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#clearLogConfirmModal">
        <i class="bi bi-trash me-2"></i>Clear All History
    </button>
</div>

<?php if (empty($displayLogEntries)): ?>
    <div class="alert alert-info" role="alert">
        <?php echo (!empty($startDate) || !empty($endDate)) ? 'No log entries found for the selected date range.' : 'No user actions logged yet or all old entries have been cleared.'; ?>
    </div>
<?php else: ?>
    <div class="card shadow-sm p-4">
        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>Action Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($displayLogEntries as $entry): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($entry['formatted_timestamp']); ?></td>
                            <td><?php echo htmlspecialchars($entry['details']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<div class="modal fade" id="clearLogConfirmModal" tabindex="-1" aria-labelledby="clearLogConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-light">
                <h5 class="modal-title" id="clearLogConfirmModalLabel">Confirm Clear History</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to **clear ALL log history**?</p>
                <p class="text-danger"><small>This action cannot be undone.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="clearLogForm" action="api/clear_logs_action.php" method="POST">
                    <input type="hidden" name="redirect_url" value="http://localhost/ecommerce-bikeshop/administrator/index.php#products">
                    <button type="submit" name="action" value="clear" class="btn btn-danger">
                        <i class="bi bi-trash me-2"></i>Clear History
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>