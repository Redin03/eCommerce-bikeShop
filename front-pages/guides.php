<?php
// Start the session
session_start();
// Require the database configuration file
require_once __DIR__ . '/../config/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Bong Bicycle Shop</title>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">

  <link rel="icon" type="image/png" href="../assets/images/favicon/favicon.svg">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC"
        crossorigin="anonymous">

  <style>
    /* Custom CSS for card styling - using root variables */
    .guide-card {
        background-color: var(--text-light); /* White background for the card */
        border-radius: 10px;
        padding: 20px;
        text-align: center;
        color: var(--text-dark); /* Dark text for contrast on white background */
        margin-bottom: 20px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Lighter shadow for white cards */
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
    }
    .guide-card .card-icon {
        font-size: 3.5rem;
        color: var(--secondary); /* Using secondary color for icons */
        margin-bottom: 15px;
    }
    .guide-card .card-title {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 10px;
    }
    .guide-card .card-description {
        font-size: 0.95rem;
        color: var(--text-dark); /* Dark text for description */
        flex-grow: 1;
        margin-bottom: 20px;
    }
    .guide-card .btn-read-more {
        background: none;
        border: none;
        color: var(--primary); /* Primary color for the button text */
        text-decoration: underline;
        padding: 0;
        font-size: 1rem;
        font-weight: bold;
    }
    .guide-card .btn-read-more:hover {
        color: var(--accent); /* Accent color on hover */
        text-decoration: none;
    }
  </style>
</head>
<body>
<?php include '../components/navigation.php'; // Include the navigation bar ?>


<div class="bg-image p-5 text-center shadow-sm" style="background-image: url('../assets/images/content-image/guides-banner.png'); background-size: cover; background-position: center;">
 <div class="mask" style="background-color: rgba(0, 0, 0, 0.6);">
  <div class="d-flex justify-content-center align-items-center h-100">
   <div class="text-white">
    <h2 class="mb-4" style="font-weight:700;">Cycling Educational Content</h2>
    <p class="mb-5" style="max-width:600px; margin:auto;">
     Explore our essential cycling topics for every rider, from beginners to advanced!
    </p>
   </div>
  </div>
 </div>
</div>

<div class="container my-5 text-center">
    <div class="row">
        <div class="col-md-4 col-sm-6 mb-4">
            <div class="guide-card">
                <div class="card-icon">
                    <i class="bi bi-bicycle"></i>
                </div>
                <div class="card-title">Basic Cycling Skills</div>
                <div class="card-description">
                    Learn the fundamentals of riding a bicycle, from balancing and pedaling to steering and braking safely.
                </div>
                <a href="../cycling-guides-pages/basic-cycling-skills.php" class="btn btn-read-more">Read More</a>
            </div>
        </div>

        <div class="col-md-4 col-sm-6 mb-4">
            <div class="guide-card">
                <div class="card-icon">
                    <i class="bi bi-shield-check"></i>
                </div>
                <div class="card-title">Bicycle Safety & Equipment</div>
                <div class="card-description">
                    Understand essential safety gear, pre-ride checks, and how to maintain your bicycle for optimal performance and safety.
                </div>
                <a href="../cycling-guides-pages/bicycle-safety-equipment.php" class="btn btn-read-more">Read More</a>
            </div>
        </div>

        <div class="col-md-4 col-sm-6 mb-4">
            <div class="guide-card">
                <div class="card-icon">
                    <i class="bi bi-signpost"></i>
                </div>
                <div class="card-title">Rules of the Road & Traffic Laws</div>
                <div class="card-description">
                    Familiarize yourself with traffic laws, road signs, and common courtesies to ensure a smooth and legal ride.
                </div>
                <a href="../cycling-guides-pages/rules-of-the-road.php" class="btn btn-read-more">Read More</a>
            </div>
        </div>

        <div class="col-md-4 col-sm-6 mb-4">
            <div class="guide-card">
                <div class="card-icon">
                    <i class="bi bi-lightning-charge"></i>
                </div>
                <div class="card-title">Advanced Cycling Techniques</div>
                <div class="card-description">
                    Master advanced maneuvers like cornering, climbing, and descending, and improve your overall riding efficiency.
                </div>
                <a href="../cycling-guides-pages/advanced-cycling-techniques.php" class="btn btn-read-more">Read More</a>
            </div>
        </div>

        <div class="col-md-4 col-sm-6 mb-4">
            <div class="guide-card">
                <div class="card-icon">
                    <i class="bi bi-building"></i>
                </div>
                <div class="card-title">Urban & Commuter Cycling</div>
                <div class="card-description">
                    Navigate city streets confidently, understand commuting best practices, and discover tips for urban cycling.
                </div>
                <a href="../cycling-guides-pages/urban-commuter-cycling.php" class="btn btn-read-more">Read More</a>
            </div>
        </div>

        <div class="col-md-4 col-sm-6 mb-4">
            <div class="guide-card">
                <div class="card-icon">
                    <i class="bi bi-heart-pulse"></i>
                </div>
                <div class="card-title">Cycling for Fitness & Health</div>
                <div class="card-description">
                    Explore how cycling can boost your fitness, improve your health, and contribute to a healthier lifestyle.
                </div>
                <a href="../cycling-guides-pages/cycling-for-fitness-health.php" class="btn btn-read-more">Read More</a>
            </div>
        </div>
    </div>
</div>

<?php include '../components/footer.php'; // Include the footer ?>

<script>
  // JavaScript to initialize and show Bootstrap toasts
  document.addEventListener('DOMContentLoaded', function () {
    var toastElList = [].slice.call(document.querySelectorAll('.toast'));
    toastElList.forEach(function (toastEl) {
      var toast = new bootstrap.Toast(toastEl);
      toast.show();
    });
  });
</script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
          integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
          crossorigin="anonymous"></script>
</body>
</html>