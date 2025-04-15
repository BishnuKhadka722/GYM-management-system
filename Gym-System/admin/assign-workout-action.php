<?php
session_start();
// Check if user is logged in
if(!isset($_SESSION['user_id'])){
    header('location:../../index.php');	
}

include "../../dbcon.php";

// Check if form data exists
if(isset($_POST['workout_plan_id']) && isset($_POST['member_ids']) && isset($_POST['start_date'])) {
    
    // Get form data
    $workout_plan_id = mysqli_real_escape_string($conn, $_POST['workout_plan_id']);
    $start_date = mysqli_real_escape_string($conn, $_POST['start_date']);
    $notes = isset($_POST['notes']) ? mysqli_real_escape_string($conn, $_POST['notes']) : '';
    $assigned_date = date('Y-m-d');
    
    // Get workout plan duration
    $plan_query = "SELECT duration FROM workout_plans WHERE id = '$workout_plan_id'";
    $plan_result = mysqli_query($conn, $plan_query);
    $plan = mysqli_fetch_array($plan_result);
    $duration_weeks = $plan['duration'];
    
    // Calculate end date (start date + duration weeks)
    $end_date = date('Y-m-d', strtotime($start_date . ' + ' . $duration_weeks . ' weeks'));
    
    $success_count = 0;
    $error_count = 0;
    
    // Loop through selected members
    foreach($_POST['member_ids'] as $member_id) {
        $member_id = mysqli_real_escape_string($conn, $member_id);
        
        // Insert into member_workouts table
        $query = "INSERT INTO member_workouts (member_id, workout_plan_id, start_date, end_date, notes, assigned_date) 
                  VALUES ('$member_id', '$workout_plan_id', '$start_date', '$end_date', '$notes', '$assigned_date')";
        
        $result = mysqli_query($conn, $query);
        
        if($result) {
            $success_count++;
        } else {
            $error_count++;
        }
    }
    
    // Redirect with appropriate message
    if($success_count > 0) {
        $_SESSION['success_msg'] = "Workout plan assigned to $success_count member(s) successfully!";
        if($error_count > 0) {
            $_SESSION['success_msg'] .= " However, $error_count assignment(s) failed.";
        }
    } else {
        $_SESSION['error_msg'] = "Error assigning workout plan. Please try again.";
    }
    
    header('location:../workout-plan.php');
} else {
    // Redirect with error message
    $_SESSION['error_msg'] = "All fields are required!";
    header('location:../workout-plan.php');
}
?>