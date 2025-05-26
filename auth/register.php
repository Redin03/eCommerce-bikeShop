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

<!-- Drawer Login Form (for AJAX/offcanvas use) -->
<div class="text-center mb-4">
  <img src="../assets/images/logos/logo.svg" alt="Bong Bicycle Shop Logo" style="width:48px; height:48px;">
  <h4 class="mt-2" style="color:var(--primary); font-weight:700;">Create Your Account</h4>
</div>
  <form action="../config/register_action.php" method="POST">
    <div class="mb-3">
      <label for="registerName" class="form-label">Name</label>
      <input type="text" class="form-control" id="registerName" name="registerName" placeholder="Enter your full name" required>
    </div>
    <div class="mb-3">
      <label for="registerEmail" class="form-label">Email address</label>
      <input type="email" class="form-control" id="registerEmail" name="registerEmail" placeholder="Enter your email" required>
    </div>
    <div class="mb-3">
      <label for="registerGender" class="form-label">Gender</label>
      <select class="form-select" id="registerGender" name="registerGender" required>
        <option value="">Select gender</option>
        <option value="Male">Male</option>
        <option value="Female">Female</option>
        <option value="Prefer not to say">Prefer not to say</option>
      </select>
    </div>
    <div class="mb-3">
      <label for="registerContact" class="form-label">Contact Number</label>
      <input type="text" class="form-control" id="registerContact" name="registerContact" placeholder="Enter your contact number" required>
    </div>
    <div class="mb-3">
      <label for="registerPassword" class="form-label">Password</label>
      <input type="password" class="form-control" id="registerPassword" name="registerPassword" placeholder="Create a password" required>
    </div>
    <div class="mb-3">
      <label for="registerConfirmPassword" class="form-label">Confirm Password</label>
      <input type="password" class="form-control" id="registerConfirmPassword" name="registerConfirmPassword" placeholder="Confirm your password" required>
    </div>
    <div class="mb-3 form-check">
      <input type="checkbox" class="form-check-input" id="agreeTerms" required>
      <label class="form-check-label" for="agreeTerms">
        I agree to the <a href="#" style="color:#00BFA6;" data-bs-toggle="modal" data-bs-target="#termsModal">Terms and Conditions</a>
      </label>
    </div>
    <button type="submit" class="btn btn-accent w-100">Register</button>
    <div class="mt-3 text-start">
      Already have an account?
      <a href="login.php" style="color:#00BFA6;" id="openLoginDrawer">Login here</a>
    </div>
  </form>



  <!-- Terms and Conditions Modal -->
<div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="termsModalLabel">Terms and Conditions</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" style="max-height:80vh; overflow-y:auto;">
        <strong>General Terms</strong>
        <p>By accessing or using our website, you agree to comply with these Terms and Conditions. If you do not agree to these terms, you must not use our services.</p>
        <strong>User Responsibilities</strong>
        <p>As a user, you are responsible for maintaining the confidentiality of your account information. You are also responsible for all activities under your account.</p>
        <strong>Privacy Policy</strong>
        <p>We value your privacy. Your personal information is only collected to process orders and enhance your experience on our platform. We do not sell or share your personal information with third parties without your consent.</p>
        <strong>Payment Terms</strong>
        <ul>
          <li><b>Online Payment (GCash):</b> Payments must be made through GCash, which is the only available online payment method on our platform.</li>
          <li><b>Cash on Delivery (COD):</b> A COD option is available for selected areas. Payment will be made upon delivery.</li>
          <li><b>Store Pickup:</b> If you choose the store pickup option, payment must be made at the time of pickup, and shipping or delivery fees will not apply.</li>
        </ul>
        <strong>Shipping and Delivery</strong>
        <p>Shipping and delivery fees apply only for online payment (GCash) and COD orders. Store pickup orders do not incur delivery charges.</p>
        <strong>Returns and Exchange Policy</strong>
        <p>If you wish to return or exchange an item, you must submit a return or exchange request within 30 days of receiving the item. Once your return or exchange request is approved by the admin, you must return the item within 30 days of the approval. Items must be unused, in their original condition, and in their original packaging to be eligible for return or exchange.</p>
        <strong>Limitation of Liability</strong>
        <p>We are not liable for any indirect, incidental, or consequential damages arising from the use or inability to use our platform.</p>
        <p><b>By continuing to use our services, you acknowledge that you have read, understood, and agreed to these Terms and Conditions.</b></p>
      </div>
    </div>
  </div>
</div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" 
          integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" 
          crossorigin="anonymous"></script>
</body>
</html>
