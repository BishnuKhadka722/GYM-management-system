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
  $_SESSION['error_msg'] = "No assignment specified for deletion.";
  header('location:../workout-plan.php');
  exit();
}

$assignment_id = intval($_GET['id']);

// Delete the plan assignment
$delete_query = "DELETE FROM member_workout_plans WHERE id = $assignment_id";

if(mysqli_query($conn, $delete_query)){
  $_SESSION['success_msg'] = "Workout plan assignment deleted successfully!";
} else {
  $_SESSION['error_msg'] = "Error deleting workout plan assignment: " . mysqli_error($conn);
}

// Redirect back to workout plans page
header('location:../workout-plan.php');
exit();
?>