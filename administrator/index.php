<?php
session_start(); // ALWAYS start the session at the very top of any page that uses sessions

// Check if the user is NOT logged in as admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // If not logged in, set a toast message and redirect to the login page
    $_SESSION['toast_message'] = 'You must be logged in to access the admin area.';
    $_SESSION['toast_type'] = 'danger';
    header('Location: login.php'); // Redirect to your admin login page
    exit(); // Stop further script execution
}

// If the user IS logged in, the script continues to render the page below
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Bong Bicycle Shop Admin</title>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">

  <link rel="icon" type="image/png" href="../assets/images/favicon/favicon.svg">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC"
        crossorigin="anonymous">
</head>
<body>

    <?php include '../components/admin-header.php'; ?>

  <div id="wrapper">
    <div id="sidebar-wrapper">
      <div class="list-group list-group-flush">
        <a href="submenu/dashboard.php" class="list-group-item list-group-item-action active" data-content-id="dashboard">
          <i class="bi bi-house-door-fill"></i>
          <span>Dashboard</span>
        </a>
        <a href="submenu/products.php" class="list-group-item list-group-item-action" data-content-id="products">
          <i class="bi bi-bicycle"></i>
          <span>Products</span>
        </a>
        <a href="submenu/orders.php" class="list-group-item list-group-item-action" data-content-id="orders">
          <i class="bi bi-cart4"></i>
          <span>Orders</span>
        </a>
        <a href="submenu/customers.php" class="list-group-item list-group-item-action" data-content-id="customers">
          <i class="bi bi-people-fill"></i>
          <span>Customers</span>
        </a>
        <a href="submenu/reports_analytics.php" class="list-group-item list-group-item-action" data-content-id="reports_analytics">
          <i class="bi bi-file-earmark-bar-graph-fill"></i>
          <span>Reports</span>
        </a>
        <a href="submenu/payments_transactions.php" class="list-group-item list-group-item-action" data-content-id="payments_transactions">
          <i class="bi bi-wallet-fill"></i>
          <span>Payments</span>
        </a>
        <a href="submenu/reviews_ratings.php" class="list-group-item list-group-item-action" data-content-id="reviews_ratings">
          <i class="bi bi-star-fill"></i>
          <span>Reviews</span>
        </a>
          <a href="submenu/log_history.php" class="list-group-item list-group-item-action" data-content-id="log_history">
          <i class="bi bi-clock-history"></i>
          <span>Log History</span>
        </a>

        <a href="#settingsSubmenu" data-bs-toggle="collapse" class="list-group-item list-group-item-action dropdown-toggle">
          <i class="bi bi-gear-fill"></i>
          <span>Settings</span>
        </a>
        <ul class="submenu collapse" id="settingsSubmenu" data-bs-parent="#sidebar-wrapper .list-group">
          <li>
            <a href="submenu/settings_promotions.php" class="list-group-item list-group-item-action" data-content-id="settings_promotions">
              <i class="bi bi-tag-fill"></i>
              <span>Promotions</span>
            </a>
          </li>
          <li>
            <a href="submenu/settings_content_management.php" class="list-group-item list-group-item-action" data-content-id="settings_content_management">
              <i class="bi bi-journal-richtext"></i>
              <span>Contents</span>
            </a>
          </li>
          <li>
            <a href="submenu/settings_users.php" class="list-group-item list-group-item-action" data-content-id="settings_users">
              <i class="bi bi-person-circle"></i>
              <span>Users</span>
            </a>
          </li>
        </ul>
      </div>
      <div class="mt-auto p-3 text-center">
        <small class="text-muted">Bong Bicycle Shop &copy; 2025</small>
      </div>
    </div>
    <button class="btn" id="sidebarToggle">
      <i class="bi bi-arrow-left-circle-fill"></i>
    </button>

    <div id="page-content-wrapper">
        <div id="content-area" class="container-fluid">
            </div>
    </div>
    </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
            crossorigin="anonymous"></script>

  <script src="js/script.js"></script>
</body>
</html>