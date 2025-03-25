<?php
session_start();
include "../../dbcon.php";

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit();
}

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "No user ID provided!";
    exit();
}

// Check if specific attendance ID is provided
if (!isset($_GET['attendance_id']) || empty($_GET['attendance_id'])) {
    // Get the most recent active check-in for this user
    $user_id = $_GET['id'];
    date_default_timezone_set('Asia/Kathmandu');
    $curr_date = date('Y-m-d');
    $check_out_time = date('Y-m-d H:i:s');

    // Update the most recent attendance record with check-out time
    $query = "UPDATE attendance 
              SET check_out_time = '$check_out_time', status = 0 
              WHERE user_id = '$user_id' AND curr_date = '$curr_date' AND status = 1 
              ORDER BY check_in_time DESC LIMIT 1";
} else {
    // Update specific attendance record
    $attendance_id = $_GET['attendance_id'];
    $check_out_time = date('Y-m-d H:i:s');
    
    $query = "UPDATE attendance 
              SET check_out_time = '$check_out_time', status = 0 
              WHERE id = '$attendance_id' AND status = 1";
}

$result = mysqli_query($conn, $query);

if ($result && mysqli_affected_rows($conn) > 0) {
    echo "<script>alert('Check-out successful!');</script>";
} else {
    echo "<script>alert('Failed to check out or no active check-in found!');</script>";
}

echo "<script>window.location = '../attendance.php';</script>";
?>