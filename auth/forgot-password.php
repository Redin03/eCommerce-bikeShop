<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Bong Bicycle Shop</title>

  <!-- Bootstrap Icons CDN -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <!-- Google Font: Montserrat -->
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">

  <!-- Favicon -->
  <link rel="icon" type="image/png" href="../assets/images/favicon/favicon.svg">

  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" 
        rel="stylesheet" 
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" 
        crossorigin="anonymous">

  <!-- Custom Styles -->
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
    }

    body {
      background-color: var(--bg-light);
      color: var(--text-dark);
      font-family: 'Montserrat', sans-serif;
    }

    .navbar {
      background-color: var(--primary);
    }

    .navbar-brand,
    .nav-link {
      color: var(--text-light) !important;
      position: relative;
      padding-bottom: 5px;
      font-weight: 500;
    }

    .nav-link:hover {
      color: var(--secondary) !important;
    }

    .nav-link.active::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 100%;
      height: 3px;
      background-color: var(--secondary);
    }

    .btn-accent {
      background-color: var(--accent);
      color: var(--text-light);
    }

    .btn-accent:hover {
      background-color: var(--secondary);
      color: var(--text-dark);
    }

    .hero-section {
      background-color: var(--bg-light);
      padding: 80px 20px;
      text-align: center;
    }

    .hero-section h1 {
      color: var(--primary);
    }

    .hero-section p {
      max-width: 600px;
      margin: 20px auto;
    }

    footer {
      background-color: var(--bg-dark);
      color: var(--text-light);
      padding: 40px 0;
    }

    .footer-link {
      color: var(--text-light);
      text-decoration: none;
    }

    .footer-link:hover {
      color: var(--secondary);
    }

    .border-top {
      border-top: 1px solid var(--border-gray);
    }

    .navbar-logo {
      width: 40px;
      height: 40px;
      margin-right: 10px;
      object-fit: contain;
    }

    .navbar-brand-text {
      font-weight: 600;
      font-size: 1.2rem;
    }
  </style>
</head>
<body>

<!-- Drawer Forgot Password Form (for AJAX/offcanvas use) -->
<div class="text-center mb-4">
  <img src="../assets/images/logos/logo.svg" alt="Bong Bicycle Shop Logo" style="width:48px; height:48px;">
  <h4 class="mt-2" style="color:var(--primary); font-weight:700;">Reset Your Password</h4>
</div>
<form action="../config/forgot_password_action.php" method="POST">
  <div class="mb-3">
    <label for="forgotEmail" class="form-label">Email address</label>
    <input type="email" class="form-control" id="forgotEmail" name="forgotEmail" placeholder="Enter your email" required>
  </div>
  <button type="submit" class="btn btn-accent w-100">Send Reset Link</button>
  <div class="mt-3 text-start">
    Remembered your password?
    <a href="#" id="openLoginDrawerFromForgot" style="color:var(--accent);">Login here</a>
  </div>
</form>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" 
          integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" 
          crossorigin="anonymous"></script>
</body>
</html>
