<?php
require_once __DIR__ . '/../config/db.php';

$code = $_GET['code'] ?? '';
$show_form = false;
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $_POST['code'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (!$new_password || !$confirm_password) {
        $msg = "Please fill in all fields.";
        $show_form = true;
    } elseif ($new_password !== $confirm_password) {
        $msg = "Passwords do not match.";
        $show_form = true;
    } elseif (strlen($new_password) < 6) {
        $msg = "Password must be at least 6 characters.";
        $show_form = true;
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE verification_code=?");
        $stmt->bind_param("s", $code);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows === 1) {
            $stmt->bind_result($user_id);
            $stmt->fetch();
            $stmt->close();

            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password=?, verification_code=NULL WHERE id=?");
            $stmt->bind_param("si", $hashed, $user_id);
            $stmt->execute();
            $stmt->close();
            $conn->close();

            $msg = "Your password has been reset. You can now login.";
            header("Location: ../front-pages/index.php?success=" . urlencode($msg));
            exit;
        } else {
            $stmt->close();
            $conn->close();
            $msg = "Invalid or expired reset link.";
            header("Location: ../front-pages/index.php?error=" . urlencode($msg));
            exit;
        }
    }
} else {
    if ($code) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE verification_code=?");
        $stmt->bind_param("s", $code);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows === 1) {
            $show_form = true;
        } else {
            $msg = "Invalid or expired reset link.";
        }
        $stmt->close();
    } else {
        $msg = "Invalid reset code.";
    }
    $conn->close();
}
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
<?php include '../components/navigation.php'; ?>

<div class="container mt-5" style="max-width:400px;">
    <div class="text-center mb-4">
        <img src="../assets/images/logos/logo.svg" alt="Bong Bicycle Shop Logo" style="width:48px; height:48px;">
        <h4 class="mt-2" style="color:var(--primary); font-weight:700;">Reset Your Password</h4>
    </div>
    <?php if ($msg && !$show_form): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($msg); ?></div>
    <?php endif; ?>
    <?php if ($show_form): ?>
    <form method="POST" class="bg-white p-4 rounded shadow-sm">
        <input type="hidden" name="code" value="<?php echo htmlspecialchars($code); ?>">
        <div class="mb-3">
            <label for="new_password" class="form-label">New Password</label>
            <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6" placeholder="Enter new password">
        </div>
        <div class="mb-3">
            <label for="confirm_password" class="form-label">Confirm New Password</label>
            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="6" placeholder="Confirm new password">
        </div>
        <?php if ($msg): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($msg); ?></div>
        <?php endif; ?>
        <button type="submit" class="btn btn-accent w-100">Reset Password</button>
    </form>
    <?php endif; ?>
</div>
<br><br><br>

<?php include '../components/footer.php'; ?>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    var toastElList = [].slice.call(document.querySelectorAll('.toast'));
    toastElList.forEach(function (toastEl) {
      var toast = new bootstrap.Toast(toastEl);
      toast.show();
    });
  });
</script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" 
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" 
        crossorigin="anonymous"></script>
</body>
</html>