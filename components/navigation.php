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

<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
   <div class="container">
     <a class="navbar-brand d-flex align-items-center" href="index.php">
       <img src="../assets/images/logos/logo.svg" alt="Bong Bicycle Shop Logo" class="navbar-logo" />
       <span class="navbar-brand-text">Bong Bicycle Shop</span>
     </a>
     <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
             data-bs-target="#navbarNav" aria-controls="navbarNav"
             aria-expanded="false" aria-label="Toggle navigation">
       <span class="navbar-toggler-icon"></span>
     </button>

     <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
       <ul class="navbar-nav">
         <li class="nav-item">
           <a class="nav-link <?php if(basename($_SERVER['PHP_SELF']) == 'index.php') echo 'active'; ?>" href="index.php">Home</a>
         </li>
         <li class="nav-item">
           <a class="nav-link <?php if(basename($_SERVER['PHP_SELF']) == 'shop.php') echo 'active'; ?>" href="shop.php">Shop</a>
         </li>
         <li class="nav-item">
           <a class="nav-link <?php if(basename($_SERVER['PHP_SELF']) == 'about.php') echo 'active'; ?>" href="about.php">About Us</a>
         </li>
         <li class="nav-item">
           <a class="nav-link <?php if(basename($_SERVER['PHP_SELF']) == 'contact.php') echo 'active'; ?>" href="contact.php">Contact Us</a>
         </li>
         <li class="nav-item">
           <a class="nav-link <?php if(basename($_SERVER['PHP_SELF']) == 'resources.php') echo 'active'; ?>" href="guides.php">Guides</a>
         </li>
         <?php if (isset($_SESSION['user_id'])): ?>
         <li class="nav-item">
           <a class="nav-link <?php if(basename($_SERVER['PHP_SELF']) == 'my_account.php') echo 'active'; ?>" href="my_account.php">My Account</a>
         </li>
         <?php else: ?>
           <li class="nav-item">
             <a class="nav-link <?php if(basename($_SERVER['PHP_SELF']) == 'my_account.php') echo 'active'; ?>" href="#" data-bs-toggle="offcanvas" data-bs-target="#loginDrawer" aria-controls="loginDrawer">My Account</a>
           </li>
         <?php endif; ?>
       </ul>
     </div>
   </div>
 </nav>


 <div class="offcanvas offcanvas-end" tabindex="-1" id="loginDrawer" aria-labelledby="loginDrawerLabel">
 <div class="offcanvas-header">
   <h5 class="offcanvas-title" id="loginDrawerLabel">Login</h5>
   <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
 </div>
 <div class="offcanvas-body" id="loginDrawerBody">
   </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="registerDrawer" aria-labelledby="registerDrawerLabel">
 <div class="offcanvas-header">
   <h5 class="offcanvas-title" id="registerDrawerLabel">Register</h5>
   <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
 </div>
 <div class="offcanvas-body" id="registerDrawerBody">
   </div>
</div>


<div class="offcanvas offcanvas-end" tabindex="-1" id="forgotPasswordDrawer" aria-labelledby="forgotPasswordDrawerLabel">
 <div class="offcanvas-header">
   <h5 class="offcanvas-title" id="forgotPasswordDrawerLabel">Forgot Password</h5>
   <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
 </div>
 <div class="offcanvas-body" id="forgotPasswordDrawerBody">
   </div>
</div>


<script src="../assets/js/drawer.js"></script>