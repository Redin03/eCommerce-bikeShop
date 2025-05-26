   <style>
    :root {
      --primary: #006A4E;
      --secondary: #FFB703;
      --accent: #00BFA6;
      --bg-light: #F4F4F4;
      --bg-dark: #003D33;
      --text-dark: #1E1E1E;
      --text-light: #FFFFFF;
      --border-gray: #D9D9D9;
      --header-height: 66px; /* Define header height as a variable */
      --sidebar-width: 250px;
      --sidebar-collapsed-width: 70px; /* Width when collapsed */
      --toggle-button-size: 40px;
    }

    body {
      background-color: var(--bg-light);
      color: var(--text-dark);
      font-family: 'Montserrat', sans-serif;
      margin: 0; /* Reset default body margin */
      padding-top: var(--header-height); /* Height of the fixed header */
      display: flex;
      flex-direction: column;
      min-height: 100vh;
      overflow-x: hidden; /* Prevent horizontal scroll when sidebar is open on smaller screens */
    }

    #header {
      background-color: var(--primary);
      color: var(--text-light);
      padding: 1rem 1.5rem;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      display: flex;
      justify-content: space-between;
      align-items: center;
      position: fixed; /* Fixed header */
      width: 100%;
      top: 0;
      left: 0;
      z-index: 1030; /* Ensure header is above other content */
      height: var(--header-height); /* Set header height */
    }

    #header .navbar-brand {
      color: var(--text-light);
      font-weight: 700;
      font-size: 1.5rem;
    }

    /* Sidebar styles */
    #wrapper {
      display: flex;
      flex-grow: 1; /* Allow wrapper to take remaining height */
      position: relative; /* Needed for toggle button positioning relative to wrapper */
    }

    #sidebar-wrapper {
      width: var(--sidebar-width);
      min-width: var(--sidebar-width);
      background-color: var(--bg-dark);
      color: var(--text-light);
      padding-top: 20px;
      transition: all 0.3s ease; /* Smooth transition for sidebar */
      box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
      position: fixed; /* Fixed sidebar */
      top: var(--header-height); /* Position below fixed header */
      bottom: 0; /* Extend to the bottom */
      left: 0;
      z-index: 1020;
      overflow-y: auto; /* Enable scrolling for sidebar content if needed */
    }

    /* This class is for the main content to slide over when sidebar collapses */
    #wrapper.toggled #sidebar-wrapper {
        width: var(--sidebar-collapsed-width);
        min-width: var(--sidebar-collapsed-width);
    }

    #sidebar-wrapper .sidebar-heading {
      padding: 0.875rem 1.25rem;
      font-size: 1.2rem;
      font-weight: bold;
      color: var(--text-light);
      text-align: center;
    }

    #sidebar-wrapper .list-group {
      width: 100%;
    }

    #sidebar-wrapper .list-group-item {
      background-color: transparent;
      border: none;
      color: var(--text-light);
      padding: 10px 20px;
      display: flex;
      align-items: center;
      gap: 10px;
      transition: background-color 0.2s ease, color 0.2s ease;
      cursor: pointer;
    }

    #sidebar-wrapper .list-group-item:hover,
    #sidebar-wrapper .list-group-item.active {
      background-color: var(--primary);
      color: var(--secondary);
    }

    #sidebar-wrapper .list-group-item i {
      font-size: 1.2rem;
    }

    #sidebar-wrapper .list-group-item span {
      white-space: nowrap; /* Prevent text wrapping */
      opacity: 1;
      transition: opacity 0.3s ease;
    }

    /* Submenu styles */
    #sidebar-wrapper .submenu {
      list-style: none;
      padding-left: 30px; /* Indent submenu items */
      display: none; /* Hidden by default */
    }

    #sidebar-wrapper .submenu.show {
      display: block;
    }

    #sidebar-wrapper .submenu .list-group-item {
      padding-left: 45px; /* Further indent submenu items */
      font-size: 0.9rem;
    }

    /* Styles for collapsed sidebar items */
    #wrapper.toggled #sidebar-wrapper .list-group-item {
      justify-content: center; /* Center icons when collapsed */
      padding: 10px; /* Adjust padding for icons */
    }

    #wrapper.toggled #sidebar-wrapper .list-group-item span {
      width: 0;
      overflow: hidden;
      opacity: 0;
    }

    #wrapper.toggled #sidebar-wrapper .sidebar-heading {
        display: none; /* Hide heading when collapsed */
    }

    #wrapper.toggled #sidebar-wrapper .submenu {
        display: none !important; /* Force hide submenu when collapsed */
    }

    #page-content-wrapper {
      flex-grow: 1;
      padding: 1.5rem;
      transition: margin-left 0.3s ease; /* Only transition margin-left */
      margin-left: var(--sidebar-width); /* Initial margin for content */
      overflow-y: auto; /* Allow main content to scroll */
      position: relative; /* For z-index context if needed */
    }

    #wrapper.toggled #page-content-wrapper {
      margin-left: var(--sidebar-collapsed-width); /* Adjust margin when sidebar is collapsed */
    }

    /* Toggle button styles - Now fixed */
    #sidebarToggle {
      background-color: var(--accent);
      color: var(--text-light); /* Make the button text/icon light */
      border: none;
      width: var(--toggle-button-size);
      height: var(--toggle-button-size);
      border-radius: 50%;
      display: flex;
      justify-content: center;
      align-items: center;
      cursor: pointer;
      position: fixed; /* Fixed position */
      top: calc(var(--header-height) + 20px); /* Position below header, slightly down */
      /* Calculate left position so half the button is visible outside the sidebar */
      left: calc(var(--sidebar-width) - (var(--toggle-button-size) / 2));
      z-index: 1040; /* Ensure toggle button is above sidebar and content */
      transition: left 0.3s ease, transform 0.3s ease; /* Transition for its left position and icon rotation */
      box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }

    #sidebarToggle i {
      color: currentColor; /* Make icon inherit the button's color */
      transition: transform 0.3s ease;
    }

    #wrapper.toggled #sidebarToggle {
      left: calc(var(--sidebar-collapsed-width) - (var(--toggle-button-size) / 2)); /* Adjust left when sidebar collapsed */
    }

    #wrapper.toggled #sidebarToggle i {
      transform: rotate(180deg); /* Rotate arrow when toggled */
    }

    .btn-accent {
      background-color: var(--accent);
      color: var(--text-light);
      border: none;
    }

    .btn-accent:hover {
      background-color: var(--secondary);
      color: var(--text-dark);
    }

    .dropdown-menu {
      background-color: var(--bg-dark);
      border: none;
    }

    .dropdown-item {
      color: var(--text-light);
    }

    .dropdown-item:hover {
      background-color: var(--primary);
      color: var(--secondary);
    }

    /* Media queries for smaller screens - Sidebar as true drawer */
    @media (max-width: 768px) {
        #sidebar-wrapper {
            margin-left: calc(-1 * var(--sidebar-width)); /* Hide by default on small screens */
            position: fixed; /* Keep it fixed for drawer effect */
            top: var(--header-height);
            bottom: 0;
            z-index: 1040; /* Bring sidebar above main content when open */
        }

        #wrapper.toggled #sidebar-wrapper {
            margin-left: 0; /* Show sidebar when toggled */
        }

        #page-content-wrapper {
            margin-left: 0; /* No margin on small screens */
            width: 100%; /* Take full width */
        }

        #wrapper.toggled #page-content-wrapper {
            /* When sidebar is open, content stays without margin for true overlay */
        }

        #sidebarToggle {
            left: 15px; /* Position button fixed on the screen, slightly from left edge */
            top: calc(var(--header-height) + 20px); /* Adjust vertical position below header */
            transform: none; /* Reset transform for mobile */
            z-index: 1050; /* Ensure it's above the sidebar too */
            background-color: var(--primary); /* Adjust color for visibility on mobile */
        }

        #wrapper.toggled #sidebarToggle {
            left: calc(var(--sidebar-width) + 15px); /* Move button with the open sidebar */
            transform: rotate(180deg); /* Rotate arrow when sidebar is open */
        }
    }
  </style>
<?php
// This is typically part of your index.php or main admin layout file
// session_start(); // Make sure this is at the very top of your main admin page

$adminUsername = $_SESSION['admin_username'] ?? 'Admin User'; // Default if not set
?>

<header id="header">
    <a class="navbar-brand" href="#">
      <i class="bi bi-gear-fill me-2"></i>Admin Dashboard
    </a>
    <div class="dropdown">
      <button class="btn btn-accent dropdown-toggle" type="button" id="adminDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-person-circle me-2"></i><?php echo htmlspecialchars($adminUsername); ?>
      </button>
      <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="adminDropdown">
        <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Profile</a></li>
        <li><a class="dropdown-item" href="#"><i class="bi bi-key me-2"></i>Change Password</a></li>
        <li><hr class="dropdown-divider bg-light"></li>
        <li><a class="dropdown-item" href="../administrator/api/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
      </ul>
    </div>
</header>