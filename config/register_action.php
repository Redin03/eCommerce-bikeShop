<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config/db.php';
require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Get form data
$name = $_POST['registerName'] ?? '';
$gender = $_POST['registerGender'] ?? '';
$contact = $_POST['registerContact'] ?? '';
$email = $_POST['registerEmail'] ?? '';
$password = $_POST['registerPassword'] ?? '';
$confirm = $_POST['registerConfirmPassword'] ?? '';

// Basic validation
if (!$name || !$gender || !$contact || !$email || !$password || !$confirm) {
    header("Location: ../front-pages/index.php?error=" . urlencode('All fields are required.'));
    exit;
}
if ($password !== $confirm) {
    header("Location: ../front-pages/index.php?error=" . urlencode('Passwords do not match.'));
    exit;
}

// Check if email exists
$stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    header("Location: ../front-pages/index.php?error=" . urlencode('Email already registered.'));
    exit;
}
$stmt->close();

$hashed_password = password_hash($password, PASSWORD_DEFAULT);
$verification_code = bin2hex(random_bytes(32));

// Insert user
$stmt = $conn->prepare("INSERT INTO users (name, gender, contact_number, email, password, verification_code) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssss", $name, $gender, $contact, $email, $hashed_password, $verification_code);
if ($stmt->execute()) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'bongbicycleshop@gmail.com';
        $mail->Password = 'ffud wagb hlcj goom'; // Your Gmail App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('bongbicycleshop@gmail.com', 'Bong Bicycle Shop');
        $mail->addAddress($email, $name);

        $mail->isHTML(true);
        $mail->Subject = 'Verify Your Email - Bong Bicycle Shop';
        $mail->Body = "
            <h2>Thank you for registering!</h2>
            <p>Click the link below to verify your email:</p>
            <a href='http://localhost/BongBicycleShop/auth/verify.php?code=$verification_code'>Verify Email</a>
        ";

        $mail->send();
        $msg = "Registration successful! Please check your email to verify your account.";
        header("Location: ../front-pages/index.php?success=" . urlencode($msg));
        exit;
    } catch (Exception $e) {
        $msg = "Registration successful, but email could not be sent. Mailer Error: {$mail->ErrorInfo}";
        header("Location: ../front-pages/index.php?error=" . urlencode($msg));
        exit;
    }
} else {
    $msg = "Registration failed. Error: " . $stmt->error;
    header("Location: ../front-pages/index.php?error=" . urlencode($msg));
    exit;
}
$stmt->close();
$conn->close();
?>