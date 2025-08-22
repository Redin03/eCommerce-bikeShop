<?php
session_start(); // Start the session at the very top

// Check if the admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // If not logged in, redirect to the login page
    $_SESSION['login_message'] = [
        'text' => 'Please log in to access the administrator dashboard.',
        'type' => 'info' // Or 'warning'
    ];
    header('Location: login.php');
    exit;
}

// Optionally, include the logger here if you want to log general admin page access,
// but usually, logging specific actions is more relevant.
// require_once 'includes/logger.php';

// Fetch the logged-in admin's username to display in the header
$loggedInAdminUsername = $_SESSION['admin_username'] ?? 'Admin User';
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
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<header id="header">
  <a class="navbar-brand" href="#">
    <img src="../assets/images/logos/logo.svg" alt="Your Website Name Logo" style="height: 30px; margin-right: 10px;"> Bong Bicycle Shop
  </a>
  <div class="dropdown">
    <button class="btn btn-accent dropdown-toggle" type="button" id="adminDropdown" data-bs-toggle="dropdown" aria-expanded="false">
      <i class="bi bi-person-circle me-2"></i><?php echo htmlspecialchars($loggedInAdminUsername); ?>
    </button>
    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="adminDropdown">
      <li><hr class="dropdown-divider bg-light"></li>
      <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
    </ul>
  </div>
</header>

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
          <span>Sales Reports</span>
        </a>
        <a href="submenu/log_history.php" class="list-group-item list-group-item-action" data-content-id="log_history">
            <i class="bi bi-clock-history"></i>
            <span>Log History</span>
        </a>
        <a href="submenu/settings_users.php" class="list-group-item list-group-item-action" data-content-id="settings_users">
              <i class="bi bi-person-circle"></i>
            <span>Admin Users</span>
        </a>
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
  <script src="js/product_logic.js"></script> 
</body>
</html>