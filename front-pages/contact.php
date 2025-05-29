<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Include PHPMailer classes
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Initialize variables for messages
$success_message = '';
$error_message = '';

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize form data
    $name = htmlspecialchars(trim($_POST['name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $message = htmlspecialchars(trim($_POST['message']));

    // Basic validation
    if (empty($name) || empty($email) || empty($message)) {
        $error_message = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    } else {
        // Create a new PHPMailer instance
        $mail = new PHPMailer(true); // Passing true enables exceptions

        try {
            // Server settings (from your registration.php example)
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'bongbicycleshop@gmail.com';
            $mail->Password = 'ffud wagb hlcj goom'; // Your Gmail App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('bongbicycleshop@gmail.com', 'Bong Bicycle Shop Contact'); // Sender email and name
            $mail->addAddress('bongbicycleshop@gmail.com', 'Bong Bicycle Shop'); // Recipient email and name (you can change this to another internal email if preferred)
            $mail->addReplyTo($email, $name); // Set reply-to to the sender's email

            // Content
            $mail->isHTML(false); // Set email format to plain text for a contact form
            $mail->Subject = 'New Contact Message from ' . $name;
            $mail->Body    = "You have received a new message from your website contact form.\n\n"
                           . "Here are the details:\n\n"
                           . "Name: " . $name . "\n"
                           . "Email: " . $email . "\n"
                           . "Message:\n" . $message;

            $mail->send();
            $success_message = "Your message has been sent successfully!";
            // Clear form fields after successful submission (optional)
            $name = '';
            $email = '';
            $message = '';

        } catch (Exception $e) {
            $error_message = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }

    // Set messages in session for display after redirect
    $_SESSION['success'] = $success_message;
    $_SESSION['error'] = $error_message;

    // Redirect to prevent form resubmission and display toast messages
    header("Location: contact.php");
    exit();
}

// Retrieve and clear messages from session for display
if (isset($_SESSION['success'])) {
    $success_message = $_SESSION['success'];
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    $error_message = $_SESSION['error'];
    unset($_SESSION['error']);
}
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
<?php include '../components/navigation.php'; ?>

<section class="hero-section d-flex align-items-center justify-content-center" style="background: url('../assets/images/content-image/banner.jpg') center/cover no-repeat; min-height: 50vh; position: relative;">
  <div style="background: rgba(0, 106, 78, 0.14); position: absolute; inset: 0;"></div>
  <div class="container position-relative text-center" style="z-index:2;">
    <h1 class="display-4 fw-bold text-light">Get in Touch</h1>
    <p class="lead text-light">We'd love to hear from you. Reach out with questions, service requests, or just to say hello.</p>
  </div>
</section>

<section style="background: var(--bg-light); padding: 60px 0;">
  <div class="container">
    <div class="row g-5 justify-content-center">
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
      <div class="col-md-7">
        <div class="card border-0 shadow-sm h-100" style="background: var(--bg-light);">
          <div class="card-body">
            <h4 class="mb-4" style="color:var(--primary); font-weight:600;">Send a Message</h4>
            <p class="mb-4">Fill out the form below and we'll get back to you soon.</p>
            <form method="POST" action=""> <div class="mb-3">
                <label for="name" class="form-label">Your Name</label>
                <input type="text" class="form-control" id="name" name="name" placeholder="Enter your name" required value="<?php echo htmlspecialchars($name ?? ''); ?>">
              </div>
              <div class="mb-3">
                <label for="email" class="form-label">Your Email</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required value="<?php echo htmlspecialchars($email ?? ''); ?>">
              </div>
              <div class="mb-3">
                <label for="message" class="form-label">Message</label>
                <textarea class="form-control" id="message" name="message" rows="4" placeholder="Type your message here..." required><?php echo htmlspecialchars($message ?? ''); ?></textarea>
              </div>
              <button type="submit" class="btn btn-accent">Send Message</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section style="background: var(--bg-light); padding: 60px 0;">
  <div class="container">
    <h2 class="text-center mb-4" style="color: var(--primary); font-weight:700;">Our Location</h2>
    <p class="text-center mb-4" style="font-size:1.1rem;">Find us in General Mariano Alvarez, Cavite</p>
    <div class="d-flex justify-content-center">
      <iframe
        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3869.664448259441!2d120.9702280750953!3d14.205777486127181!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397d9e4a3c2b87f%3A0x67e1a2f6b8c9d2f!2s688%20Congressional%20Rd%2C%20General%20Mariano%20Alvarez%2C%20Cavite!5e0!3m2!1sen!2sph!4v1700000000000!5m2!1sen!2sph"
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

<div aria-live="polite" aria-atomic="true" class="position-relative">
  <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
    <?php if (!empty($success_message)): ?>
      <div class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true"
           data-bs-autohide="true" data-bs-delay="5000" id="successToast">
        <div class="d-flex">
          <div class="toast-body">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?php echo htmlspecialchars($success_message); ?>
          </div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
      </div>
    <?php endif; ?>
    <?php if (!empty($error_message)): ?>
      <div class="toast align-items-center text-bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true"
           data-bs-autohide="true" data-bs-delay="5000" id="errorToast">
        <div class="d-flex">
          <div class="toast-body">
            <i class="bi bi-x-circle-fill me-2"></i>
            <?php echo htmlspecialchars($error_message); ?>
          </div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php include '../components/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
        crossorigin="anonymous"></script>

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

</body>
</html>