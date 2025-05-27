<?php
session_start(); // Start the session at the very top of the page

// Initialize message variables
$message = '';
$message_type = '';

// Check if there's a message from a previous redirect (e.g., failed login)
if (isset($_SESSION['login_message'])) {
    $message = $_SESSION['login_message']['text'];
    $message_type = $_SESSION['login_message']['type'];
    unset($_SESSION['login_message']); // Clear the message after displaying it
}

// If admin is already logged in, redirect them to the dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Login - Bong Bicycle Shop</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="../assets/images/favicon/favicon.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css"
          rel="stylesheet"
          integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC"
          crossorigin="anonymous">

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
            --header-height: 66px;
            --sidebar-width: 250px;
            --sidebar-collapsed-width: 70px;
            --toggle-button-size: 40px;
        }

        body {
            background-color: var(--primary); /* Use primary color for the background */
            font-family: 'Montserrat', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px; /* Add some padding for smaller screens */
            box-sizing: border-box; /* Include padding in element's total width and height */
        }

        .login-container {
            background-color: var(--bg-light); /* Light background for the card */
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 450px; /* Max width for the login card */
            text-align: center;
        }

        .login-container h2 {
            color: var(--primary); /* Primary color for the heading */
            margin-bottom: 30px;
            font-weight: 700;
        }

        .form-label {
            color: var(--text-dark);
            font-weight: 600;
        }

        .form-control:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 0.25rem rgba(0, 191, 166, 0.25); /* Accent color with transparency */
        }

        .btn-login {
            background-color: var(--accent);
            color: var(--text-light);
            border: none;
            padding: 10px 20px;
            font-size: 1.1rem;
            font-weight: 600;
            transition: background-color 0.2s ease, color 0.2s ease;
            width: 100%;
        }

        .btn-login:hover {
            background-color: var(--secondary);
            color: var(--text-dark);
        }

        .alert {
            margin-top: 20px;
        }

        .alert-success {
            background-color: var(--accent);
            color: var(--text-light);
            border-color: var(--accent);
        }

        .alert-danger {
            background-color: #dc3545; /* Bootstrap danger red */
            color: var(--text-light);
            border-color: #dc3545;
        }
    </style>
</head>
<body>

    <div class="login-container">
        <h2>Admin Login</h2>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form action="api/login_action.php" method="POST">
            <div class="mb-3 text-start">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" required autocomplete="username">
            </div>
            <div class="mb-4 text-start">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required autocomplete="current-password">
            </div>
            <button type="submit" class="btn btn-login">Login</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
            crossorigin="anonymous"></script>
    </body>
</html>