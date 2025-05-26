<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/db.php';
require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$email = $_POST['forgotEmail'] ?? '';

if (!$email) {
    header("Location: ../front-pages/index.php?error=" . urlencode('Please enter your email.'));
    exit;
}

// Check if email exists
$stmt = $conn->prepare("SELECT id, name FROM users WHERE email=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    $stmt->close();
    $conn->close();
    header("Location: ../front-pages/index.php?error=" . urlencode('No account found with that email.'));
    exit;
}

$stmt->bind_result($user_id, $user_name);
$stmt->fetch();
$stmt->close();

// Generate reset code
$reset_code = bin2hex(random_bytes(32));

// Save reset code to database (you may want a separate column for this, e.g., password_reset_code)
$stmt = $conn->prepare("UPDATE users SET verification_code=? WHERE id=?");
$stmt->bind_param("si", $reset_code, $user_id);
$stmt->execute();
$stmt->close();

// Send reset email
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'bongbicycleshop@gmail.com'; // Your Gmail
    $mail->Password = 'ffud wagb hlcj goom'; // Your Gmail App Password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('bongbicycleshop@gmail.com', 'Bong Bicycle Shop');
    $mail->addAddress($email, $user_name);

    $mail->isHTML(true);
    $mail->Subject = 'Password Reset Request - Bong Bicycle Shop';
    $mail->Body = "
        <h2>Password Reset Request</h2>
        <p>Hello, $user_name!</p>
        <p>Click the link below to reset your password:</p>
        <a href='http://localhost/BongBicycleShop/auth/reset_password.php?code=$reset_code'>Reset Password</a>
        <p>If you did not request a password reset, please ignore this email.</p>
    ";

    $mail->send();
    $msg = "A password reset link has been sent to your email.";
    header("Location: ../front-pages/index.php?success=" . urlencode($msg));
    exit;
} catch (Exception $e) {
    $msg = "Could not send reset email. Mailer Error: {$mail->ErrorInfo}";
    header("Location: ../front-pages/index.php?error=" . urlencode($msg));
    exit;
}
$conn->close();
?>