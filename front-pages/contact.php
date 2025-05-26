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

<!-- Hero Section: Contact Us Page -->
<section class="hero-section d-flex align-items-center justify-content-center" style="background: url('../assets/images/content-image/banner.jpg') center/cover no-repeat; min-height: 50vh; position: relative;">
  <div style="background: rgba(0, 106, 78, 0.14); position: absolute; inset: 0;"></div>
  <div class="container position-relative text-center" style="z-index:2;">
    <h1 class="display-4 fw-bold text-light">Get in Touch</h1>
    <p class="lead text-light">We'd love to hear from you. Reach out with questions, service requests, or just to say hello.</p>
  </div>
</section>

<!-- Get in Touch Section -->
<section style="background: var(--bg-light); padding: 60px 0;">
  <div class="container">
    <div class="row g-5 justify-content-center">
      <!-- Shop Information -->
      <div class="col-md-5">
        <div class="card border-0 shadow-sm h-100" style="background: var(--bg-light);">
          <div class="card-body">
            <h4 class="mb-4" style="color:var(--primary); font-weight:600;">Shop Information</h4>
            <div class="mb-3">
              <i class="bi bi-envelope me-2" style="color:var(--secondary);"></i>
              <span>bongbicycleshop@gmail.com</span>
            </div>
            <div class="mb-3">
              <i class="bi bi-geo-alt me-2" style="color:var(--secondary);"></i>
              <span>688 Congressional Rd, General Mariano Alvarez, Cavite</span>
            </div>
            <div class="mb-3">
              <i class="bi bi-clock me-2" style="color:var(--secondary);"></i>
              <span><strong>Service Hours</strong><br>Mon - Sat: 8:00 AM - 5:00 PM</span>
            </div>
          </div>
        </div>
      </div>
      <!-- Send a Message Form -->
      <div class="col-md-7">
        <div class="card border-0 shadow-sm h-100" style="background: var(--bg-light);">
          <div class="card-body">
            <h4 class="mb-4" style="color:var(--primary); font-weight:600;">Send a Message</h4>
            <p class="mb-4">Fill out the form below and we'll get back to you soon.</p>
            <form>
              <div class="mb-3">
                <label for="name" class="form-label">Your Name</label>
                <input type="text" class="form-control" id="name" placeholder="Enter your name" required>
              </div>
              <div class="mb-3">
                <label for="email" class="form-label">Your Email</label>
                <input type="email" class="form-control" id="email" placeholder="Enter your email" required>
              </div>
              <div class="mb-3">
                <label for="message" class="form-label">Message</label>
                <textarea class="form-control" id="message" rows="4" placeholder="Type your message here..." required></textarea>
              </div>
              <button type="submit" class="btn btn-accent">Send Message</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>



<!-- Our Location Section -->
<section style="background: var(--bg-light); padding: 60px 0;">
  <div class="container">
    <h2 class="text-center mb-4" style="color: var(--primary); font-weight:700;">Our Location</h2>
    <p class="text-center mb-4" style="font-size:1.1rem;">Find us in General Mariano Alvarez, Cavite</p>
    <div class="d-flex justify-content-center">
      <iframe 
        src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d247429.20963446822!2d121.009717!3d14.306703!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397d18a64871e83%3A0x6575f026a8e789a4!2sBong%20Bicycle%20Shop!5e0!3m2!1sen!2sph!4v1747840943089!5m2!1sen!2sph" 
        width="100%" 
        height="450" 
        style="border:0; max-width:900px;" 
        allowfullscreen="" 
        loading="lazy" 
        referrerpolicy="no-referrer-when-downgrade">
      </iframe>
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
