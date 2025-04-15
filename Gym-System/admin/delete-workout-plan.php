<?php
session_start();
// Check if user is logged in
if(!isset($_SESSION['user_id'])){
  header('location:../../index.php');	
  exit();
}

include "../../dbcon.php";

// Check if ID parameter exists
if(!isset($_GET['id']) || empty($_GET['id'])){
  $_SESSION['error_msg'] = "No workout plan specified for deletion.";
  header('location:../workout-plan.php');
  exit();
}

$plan_id = intval($_GET['id']);

// Begin transaction to ensure data consistency
mysqli_begin_transaction($conn);

try {
  // First delete all exercises associated with this plan
  $delete_exercises = "DELETE FROM workout_exercises WHERE plan_id = $plan_id";
  mysqli_query($conn, $delete_exercises);
  
  // Delete all member assignments for this plan
  $delete_assignments = "DELETE FROM member_workout_plans WHERE plan_id = $plan_id";
  mysqli_query($conn, $delete_assignments);
  
  // Finally delete the workout plan itself
  $delete_plan = "DELETE FROM workout_plans WHERE id = $plan_id";
  mysqli_query($conn, $delete_plan);
  
  // If everything went well, commit the transaction
  mysqli_commit($conn);
  
  $_SESSION['success_msg'] = "Workout plan deleted successfully!";
} catch (Exception $e) {
  // An error occurred, rollback changes
  mysqli_rollback($conn);
  $_SESSION['error_msg'] = "Error deleting workout plan: " . $e->getMessage();
}

// Redirect back to workout plans page
header('location:../workout-plan.php');
exit();
?>