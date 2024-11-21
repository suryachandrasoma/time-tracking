<?php
$host = 'localhost';
$db = 'time_tracking';
$user = 'root';
$pass = '';  // XAMPP default

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>


 