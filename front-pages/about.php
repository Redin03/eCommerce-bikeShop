<?php
session_start();
require_once __DIR__ . '/../config/db.php';
?>
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
</head>
<body>
<!-- Navbar -->
<?php include '../components/navigation.php'; ?>

<!-- Hero Section: About Page -->
<section class="hero-section d-flex align-items-center justify-content-center" style="background: url('../assets/images/content-image/banner.jpg') center/cover no-repeat; min-height: 50vh; position: relative;">
  <div style="background: rgba(0, 106, 78, 0.14); position: absolute; inset: 0;"></div>
  <div class="container position-relative text-center" style="z-index:2;">
    <h1 class="display-4 fw-bold text-light">About Bong Bicycle Shop</h1>
    <p class="lead text-light">Discover our commitment to quality cycling products, and educational resources for every cyclist.</p>
  </div>
</section>


<!-- Our History Section -->
<section style="background: var(--bg-light); padding: 60px 0;">
  <div class="container">
    <h2 class="text-center mb-5" style="color: var(--primary); font-weight:700;">Our History</h2>
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="card border-0 shadow-sm" style="background: var(--bg-light);">
          <div class="card-body p-4">
            <h4 class="card-title mb-3" style="color:var(--primary); font-weight:600;">
              Bong Bicycle Shop: Serving Cyclists Since 2020
            </h4>
            <p class="card-text" style="font-size:1.1rem;">
              Founded in 2020, Bong Bicycle Shop began with a simple mission: to make quality bikes, parts, and cycling knowledge accessible to everyone in our community. 
              <br><br>
              From our humble beginnings, we have grown into a trusted destination for cyclists of all ages and skill levels. Our passion for cycling drives us to offer not only the best products, but also helpful guides and a welcoming environment for every rider.
              <br><br>
              Thank you for being part of our journey!
            </p>
            <div class="text-end mt-4">
              <span class="badge" style="background: var(--secondary); color: var(--text-dark); font-size:1rem;">Established 2020</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Our Mission & Vision Section -->
<section style="background: var(--accent); color: var(--text-light); padding: 60px 0;">
  <div class="container">
    <h2 class="text-center mb-5" style="font-weight:700;">Our Mission & Vision</h2>
    <div class="row justify-content-center g-4">
      <!-- Mission Card -->
      <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100" style="background: var(--bg-light); color: var(--text-dark);">
          <div class="card-body p-4">
            <h4 class="mb-3" style="color:var(--primary); font-weight:600;">Our Mission</h4>
            <p style="font-size:1.1rem;">
              To empower every rider—beginner or pro—with quality bikes, trusted service, and expert cycling knowledge, making cycling accessible and enjoyable for all in our community.
            </p>
          </div>
        </div>
      </div>
      <!-- Vision Card -->
      <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100" style="background: var(--bg-light); color: var(--text-dark);">
          <div class="card-body p-4">
            <h4 class="mb-3" style="color:var(--primary); font-weight:600;">Our Vision</h4>
            <p style="font-size:1.1rem;">
              To be the region’s leading bicycle shop, inspiring a healthy, active lifestyle and building a vibrant cycling community through innovation, education, and outstanding customer care.
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Our Values Section -->
<section style="background: var(--bg-light); padding: 60px 0;">
  <div class="container">
    <h2 class="text-center mb-5" style="color: var(--primary); font-weight:700;">Our Values</h2>
    <div class="row g-4 justify-content-center">
      <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100 text-center" style="background: var(--bg-light);">
          <div class="card-body">
            <i class="bi bi-people-fill mb-3" style="font-size:2.5rem; color:var(--secondary);"></i>
            <h5 class="card-title" style="color:var(--primary); font-weight:600;">Community</h5>
            <p class="card-text">We foster a welcoming environment and support local cycling initiatives to build a stronger, healthier community.</p>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100 text-center" style="background: var(--bg-light);">
          <div class="card-body">
            <i class="bi bi-award-fill mb-3" style="font-size:2.5rem; color:var(--secondary);"></i>
            <h5 class="card-title" style="color:var(--primary); font-weight:600;">Quality</h5>
            <p class="card-text">We are committed to offering only the best bikes, parts, and service for every rider’s needs.</p>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100 text-center" style="background: var(--bg-light);">
          <div class="card-body">
            <i class="bi bi-lightbulb-fill mb-3" style="font-size:2.5rem; color:var(--secondary);"></i>
            <h5 class="card-title" style="color:var(--primary); font-weight:600;">Education</h5>
            <p class="card-text">We believe in empowering cyclists with knowledge, safety tips, and maintenance guides for a better riding experience.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>


<!-- What Makes Us Different Section -->
<section style="background: var(--primary); color: var(--text-light); padding: 60px 0;">
  <div class="container">
    <h2 class="text-center mb-5" style="font-weight:700;">What Makes Us Different</h2>
    <div class="row g-4 justify-content-center">
      <!-- Cycling Education -->
      <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100 text-center" style="background: var(--bg-light); color: var(--text-dark);">
          <div class="card-body">
            <i class="bi bi-book-half mb-3" style="font-size:2.5rem; color:var(--secondary);"></i>
            <h5 class="card-title" style="color:var(--primary); font-weight:600;">Cycling Education</h5>
            <p class="card-text">Free guides, tips, and workshops to help you ride smarter and safer.</p>
          </div>
        </div>
      </div>
      <!-- Wide Selection -->
      <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100 text-center" style="background: var(--bg-light); color: var(--text-dark);">
          <div class="card-body">
            <i class="bi bi-bag-check mb-3" style="font-size:2.5rem; color:var(--secondary);"></i>
            <h5 class="card-title" style="color:var(--primary); font-weight:600;">Wide Selection</h5>
            <p class="card-text">From bikes to accessories and parts & components, we have everything for every cyclist’s needs.</p>
          </div>
        </div>
      </div>
      <!-- Friendly Navigation -->
      <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100 text-center" style="background: var(--bg-light); color: var(--text-dark);">
          <div class="card-body">
            <i class="bi bi-emoji-smile mb-3" style="font-size:2.5rem; color:var(--secondary);"></i>
            <h5 class="card-title" style="color:var(--primary); font-weight:600;">Friendly Navigation</h5>
            <p class="card-text">Enjoy a user-friendly website and in-store experience designed for all cyclists.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Footer -->
<?php include '../components/footer.php'; ?>
  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" 
          integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" 
          crossorigin="anonymous"></script>
</body>
</html>
