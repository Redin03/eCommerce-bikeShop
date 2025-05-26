<?php
// config/db.php - Your original mysqli connection

$host = 'localhost';
$user = 'root';
$pass = ''; // Your database password, if any
$dbname = 'bong_bike_shop';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    // It's crucial this error is visible, especially for debugging connection issues
    die('Database connection failed: ' . $conn->connect_error);
}
// Optionally, set charset for the connection (good practice)
$conn->set_charset('utf8mb4');

?>