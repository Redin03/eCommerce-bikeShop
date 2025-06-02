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
</head>
<body>
<?php include '../components/navigation.php'; // Include the navigation bar ?>

<section class="hero-section d-flex align-items-center justify-content-center" style="background: url('../assets/images/content-image/hero-banner.jpg') center/cover no-repeat; min-height: 70vh; position: relative;">
  <div style="background: rgba(0, 106, 78, 0.14); position: absolute; inset: 0;"></div>
  <div class="container position-relative text-center" style="z-index:2;">
    <h1 class="display-4 fw-bold text-light">Welcome to Bong Bicycle Shop</h1>
    <p class="lead text-light">Your one-stop shop for bikes, parts, and cycling guides at the best prices.</p>
    <a href="collection.php" class="btn btn-accent btn-lg mt-3 me-2">Shop Now</a>
    <a href="guides.php" class="btn btn-outline-light btn-lg mt-3">Guides</a>
  </div>
</section>

<section style="background: var(--bg-light); padding: 60px 0;">
  <div class="container">
    <h2 class="text-center mb-5" style="color: var(--primary); font-weight:700;">Product Highlights</h2>
    <div class="row">
      <div class="col-md-3 mb-4">
        <div class="card h-100 shadow-sm border-0">
          <img src="../assets/images/products/electric-bike.jpg" class="card-img-top" alt="Electric Bike">
          <div class="card-body">
            <h5 class="card-title" style="color:var(--primary);">Electric Bikes</h5>
            <p class="card-text">Latest e-bikes for effortless rides.</p>
            <a href="shop.php?category=Electric Bikes" class="btn btn-accent btn-sm">Shop Electric</a>
          </div>
        </div>
      </div>
      <div class="col-md-3 mb-4">
        <div class="card h-100 shadow-sm border-0">
          <img src="../assets/images/products/mountain-bike.jpg" class="card-img-top" alt="Mountain Bike">
          <div class="card-body">
            <h5 class="card-title" style="color:var(--primary);">Mountain Bikes</h5>
            <p class="card-text">Conquer any trail with our MTB range.</p>
            <a href="shop.php?category=Mountain Bikes" class="btn btn-accent btn-sm">Shop Mountain</a>
          </div>
        </div>
      </div>
      <div class="col-md-3 mb-4">
        <div class="card h-100 shadow-sm border-0">
          <img src="../assets/images/products/road-bike.jpg" class="card-img-top" alt="Road Bike">
          <div class="card-body">
            <h5 class="card-title" style="color:var(--primary);">Road Bikes</h5>
            <p class="card-text">Speed and performance for every rider.</p>
            <a href="shop.php?category=Road Bikes" class="btn btn-accent btn-sm">Shop Road</a>
          </div>
        </div>
      </div>
      <div class="col-md-3 mb-4">
        <div class="card h-100 shadow-sm border-0">
          <img src="../assets/images/products/kids-bike.jpg" class="card-img-top" alt="Kids Bike">
          <div class="card-body">
            <h5 class="card-title" style="color:var(--primary);">Kids’ Bikes</h5>
            <p class="card-text">Fun, safe bikes for young riders.</p>
            <a href="shop.php?category=Kids' Bikes" class="btn btn-accent btn-sm">Shop Kids</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section style="background: var(--primary); padding: 50px 0;">
  <div class="container">
    <h2 class="text-center text-light mb-5" style="font-weight:700;">Shop by Category</h2>
    <div class="row g-4 justify-content-center">
      <div class="col-12 col-md-3">
        <div class="card h-100 shadow-sm border-0" style="background: var(--bg-light);">
          <img src="../assets/images/content-image/shop-by-bikes.jpg" class="card-img-top" alt="Bikes" style="height:180px; object-fit:cover;">
          <div class="card-body text-center">
            <h5 class="card-title" style="color:var(--primary);">Bikes</h5>
            <p class="card-text">Explore our full range of mountain, road, electric, and kids' bikes.</p>
            <a href="collection.php?category=Bikes" class="btn btn-accent">Shop Bikes</a>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-3">
        <div class="card h-100 shadow-sm border-0" style="background: var(--bg-light);">
          <img src="../assets/images/content-image/shop-by-accessories.jpg" class="card-img-top" alt="Accessories" style="height:180px; object-fit:cover;">
          <div class="card-body text-center">
            <h5 class="card-title" style="color:var(--primary);">Accessories</h5>
            <p class="card-text">Helmets, lights, pumps, locks, and more for every cyclist.</p>
            <a href="collection.php?category=Accessories" class="btn btn-accent">Shop Accessories</a>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-3">
        <div class="card h-100 shadow-sm border-0" style="background: var(--bg-light);">
          <img src="../assets/images/content-image/shop-by-apparel.jpg" class="card-img-top" alt="Apparel" style="height:180px; object-fit:cover;">
          <div class="card-body text-center">
            <h5 class="card-title" style="color:var(--primary);">Apparel</h5>
            <p class="card-text">Explore our full range of cycling apparel — from performance jerseys and padded shorts to gloves, jackets, and protective gear.</p>
            <a href="collection.php?category=Apparel" class="btn btn-accent">Shop Apparel</a>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-3">
        <div class="card h-100 shadow-sm border-0" style="background: var(--bg-light);">
          <img src="../assets/images/content-image/shop-by-parts-components.jpg" class="card-img-top" alt="Bike Parts" style="height:180px; object-fit:cover;">
          <div class="card-body text-center">
            <h5 class="card-title" style="color:var(--primary);">Bike Parts / Components</h5>
            <p class="card-text">Quality parts and components to keep your bike running smoothly.</p>
            <a href="collection.php?category=<?php echo urlencode('Parts & Components'); ?>" class="btn btn-accent">Shop Parts/ Components</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section style="background: var(--secondary); color: var(--text-dark); padding: 40px 0;">
  <div class="container text-center">
    <h2 class="fw-bold mb-3">Limited-Time Savings on Our Most Popular Items!</h2>
    <p class="mb-4">Explore incredible deals on a wide selection of products, available only for a short time.</p>
    <a href="collection.php?category=Discounted" class="btn btn-primary btn-lg" style="background: var(--primary); border:none;">Discover Discounts</a>
  </div>
</section>

<section style="background: var(--bg-light); padding: 60px 0;">
  <div class="container">
    <h2 class="text-center mb-5" style="color: var(--primary); font-weight:700;">What Our Customers Say</h2>
    <div class="row justify-content-center">
      <div class="col-md-4 mb-4">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <p class="card-text">"Excellent online shopping experience and top-quality bikes! Ordering was easy, shipping was fast, and the bike arrived in perfect condition. You can really tell this shop cares about quality and customer satisfaction. Highly recommended!"</p>
            <div class="d-flex align-items-center mt-3">
              <img src="../assets/images/avatar/avatar1.jpg" alt="User 1" class="rounded-circle me-3" style="width:48px; height:48px;">
              <div>
                <strong>Mike S.</strong><br>
                <span class="ms-2" style="color:var(--primary); font-weight:600;">5.0</span>
                <span class="text-warning">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4 mb-4">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <p class="card-text">""Great selection of bikes and gear! The website was easy to navigate, and everything arrived just as described. Highly recommend Bong Bicycle Shop for quality and convenience!"</p>
            <div class="d-flex align-items-center mt-3">
              <img src="../assets/images/avatar/avatar2.jpg" alt="User 2" class="rounded-circle me-3" style="width:48px; height:48px;">
              <div>
                <strong>Jane D.</strong><br>
                <span class="ms-2" style="color:var(--primary); font-weight:600;">5.0</span>
                <span class="text-warning">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-4 mb-4">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <p class="card-text">"Consistently great quality and smooth online shopping. Bong Bicycle Shop is my first choice every time!"</p>
            <div class="d-flex align-items-center mt-3">
              <img src="../assets/images/avatar/avatar3.jpg" alt="User 3" class="rounded-circle me-3" style="width:48px; height:48px;">
              <div>
                <strong>Sarah K.</strong><br>
                <span class="ms-2" style="color:var(--primary); font-weight:600;">5.0</span>
                <span class="text-warning">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section style="background: var(--bg-dark); padding: 40px 0;">
  <div class="container text-center">
    <h2 class="text-light mb-4" style="font-weight:700;">Cycling Educational Content</h2>
    <p class="text-light mb-5" style="max-width:600px; margin:auto;">
      Explore our essential cycling topics for every rider, from beginners to advanced!
    </p>
    <div class="row g-4 justify-content-center">
      <div class="col-6 col-md-2">
        <div class="d-flex flex-column align-items-center">
          <i class="bi bi-bicycle" style="font-size: 2.5rem; color: var(--secondary);"></i>
          <span class="text-light mt-2" style="font-weight:600;">Basic Cycling Skills</span>
        </div>
      </div>
      <div class="col-6 col-md-2">
        <div class="d-flex flex-column align-items-center">
          <i class="bi bi-shield-check" style="font-size: 2.5rem; color: var(--secondary);"></i>
          <span class="text-light mt-2" style="font-weight:600;">Bicycle Safety & Equipment</span>
        </div>
      </div>
      <div class="col-6 col-md-2">
        <div class="d-flex flex-column align-items-center">
          <i class="bi bi-signpost" style="font-size: 2.5rem; color: var(--secondary);"></i>
          <span class="text-light mt-2" style="font-weight:600;">Rules of the Road & Traffic Laws</span>
        </div>
      </div>
      <div class="col-6 col-md-2">
        <div class="d-flex flex-column align-items-center">
          <i class="bi bi-lightning-charge" style="font-size: 2.5rem; color: var(--secondary);"></i>
          <span class="text-light mt-2" style="font-weight:600;">Advanced Cycling Techniques</span>
        </div>
      </div>
      <div class="col-6 col-md-2">
        <div class="d-flex flex-column align-items-center">
          <i class="bi bi-building" style="font-size: 2.5rem; color: var(--secondary);"></i>
          <span class="text-light mt-2" style="font-weight:600;">Urban & Commuter Cycling</span>
        </div>
      </div>
      <div class="col-6 col-md-2">
        <div class="d-flex flex-column align-items-center">
          <i class="bi bi-heart-pulse" style="font-size: 2.5rem; color: var(--secondary);"></i>
          <span class="text-light mt-2" style="font-weight:600;">Cycling for Fitness & Health</span>
        </div>
      </div>
    </div>
  </div>
</section>

<section style="background: var(--accent); color: var(--text-light); padding: 50px 0;">
  <div class="container text-center">
    <h2 class="fw-bold mb-3">Boost Your Cycling Skills</h2>
    <p class="mb-4">Explore beginner to advanced tips on safety, handling, fitness, and urban riding.</p>
    <a href="guides.php" class="btn btn-secondary btn-lg" style="background: var(--secondary); color: var(--text-dark); border: none;">
      View Cycling Guides
    </a>
  </div>
</section>

<section style="background: var(--bg-light); padding: 50px 0;">
  <div class="container">
    <h2 class="text-center mb-5" style="color: var(--primary); font-weight:700;">Frequently Asked Questions</h2>
    <div class="accordion" id="faqAccordion">
      <div class="accordion-item">
        <h2 class="accordion-header" id="faq1">
          <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse1" aria-expanded="true" aria-controls="collapse1" style="color:var(--primary); font-weight:600;">
            What types of bikes do you sell?
          </button>
        </h2>
        <div id="collapse1" class="accordion-collapse collapse show" aria-labelledby="faq1" data-bs-parent="#faqAccordion">
          <div class="accordion-body">
            We offer a wide range of bikes, including mountain bikes, road bikes, electric bikes, and kids' bikes.
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="faq2">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2" aria-expanded="false" aria-controls="collapse2" style="color:var(--primary); font-weight:600;">
            Are your educational cycling guides free to access?
          </button>
        </h2>
        <div id="collapse2" class="accordion-collapse collapse" aria-labelledby="faq2" data-bs-parent="#faqAccordion">
          <div class="accordion-body">
            Yes, our cycling guides are free for all riders! Simply visit our <a href="guides.php" style="color:var(--accent);">Guides</a> page.
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="faq3">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse3" aria-expanded="false" aria-controls="collapse3" style="color:var(--primary); font-weight:600;">
            Can I order online and pick up in-store?
          </button>
        </h2>
        <div id="collapse3" class="accordion-collapse collapse" aria-labelledby="faq3" data-bs-parent="#faqAccordion">
          <div class="accordion-body">
            Absolutely! You can shop online and pay at the store to receive the item you checked out.
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="faq4">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse4" aria-expanded="false" aria-controls="collapse4" style="color:var(--primary); font-weight:600;">
            What payment methods do you accept?
          </button>
        </h2>
        <div id="collapse4" class="accordion-collapse collapse" aria-labelledby="faq4" data-bs-parent="#faqAccordion">
          <div class="accordion-body">
            We accept Cash on Delivery (COD), GCash, and in-store payments for pickup orders.
          </div>
        </div>
      </div>
      <div class="accordion-item">
        <h2 class="accordion-header" id="faq5">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse5" aria-expanded="false" aria-controls="collapse5" style="color:var(--primary); font-weight:600;">
            How can I contact customer support?
          </button>
        </h2>
        <div id="collapse5" class="accordion-collapse collapse" aria-labelledby="faq5" data-bs-parent="#faqAccordion">
          <div class="accordion-body">
            You can reach us via our <a href="contact.php" style="color:var(--accent);">Contact Us</a> page, by phone, or by email.
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php
// Get success and error messages from URL parameters
$success = isset($_GET['success']) ? urldecode($_GET['success']) : '';
$error = isset($_GET['error']) ? urldecode($_GET['error']) : '';
?>
<div aria-live="polite" aria-atomic="true" class="position-relative">
  <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
    <?php if ($success): // Display success toast if message exists ?>
      <div class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true"
           data-bs-autohide="true" data-bs-delay="5000" id="successToast">
        <div class="d-flex">
          <div class="toast-body">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?php echo htmlspecialchars($success); ?>
          </div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
      </div>
    <?php endif; ?>
    <?php if ($error): // Display error toast if message exists ?>
      <div class="toast align-items-center text-bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true"
           data-bs-autohide="true" data-bs-delay="5000" id="errorToast">
        <div class="d-flex">
          <div class="toast-body">
            <i class="bi bi-x-circle-fill me-2"></i>
            <?php echo htmlspecialchars($error); ?>
          </div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
      </div>
    <?php endif; ?>
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