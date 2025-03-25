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

$user_id = $_GET['id'];
date_default_timezone_set('Asia/Kathmandu');
$curr_date = date('Y-m-d');
$check_in_time = date('Y-m-d H:i:s');

// Check if there's an active check-in that hasn't been checked out yet
$check_query = "SELECT * FROM attendance WHERE user_id = '$user_id' AND curr_date = '$curr_date' AND status = 1";
$check_result = mysqli_query($conn, $check_query);

if (mysqli_num_rows($check_result) > 0) {
    // There's an active check-in without checkout, so we'll check them out first
    $update_query = "UPDATE attendance 
                    SET check_out_time = '$check_in_time', status = 0 
                    WHERE user_id = '$user_id' AND curr_date = '$curr_date' AND status = 1";
    mysqli_query($conn, $update_query);
    
    echo "<script>alert('Previous session automatically checked out. New check-in created.');</script>";
}

// Create new check-in record
$query = "INSERT INTO attendance (user_id, curr_date, check_in_time, status) 
          VALUES ('$user_id', '$curr_date', '$check_in_time', 1)";

if (mysqli_query($conn, $query)) {
    echo "<script>alert('Check-in successful!');</script>";
} else {
    echo "<script>alert('Failed to check in: " . mysqli_error($conn) . "');</script>";
}

echo "<script>window.location = '../attendance.php';</script>";
?>