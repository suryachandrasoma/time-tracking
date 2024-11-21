<?php
session_start();
include 'db.php';

$user_id = $_SESSION['user_id']; // Assuming user_id is stored in session

// Check if there's an active session
$sql_check = "SELECT id FROM time_logs WHERE user_id = '$user_id' AND is_session_active = TRUE";
$result_check = mysqli_query($conn, $sql_check);

if (mysqli_num_rows($result_check) > 0) {
    // Active session detected, show an alert and redirect to the dashboard
    echo "<script>alert('You have an active session. Please end it before logging out.');</script>";
    echo "<script>window.location.href = 'employee.php';</script>";
    exit();
}

// Destroy session and log out
session_destroy();
header("Location: index.php");
exit();
?>
