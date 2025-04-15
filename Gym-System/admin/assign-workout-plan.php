<?php
session_start();
// Check if user is logged in
if(!isset($_SESSION['user_id'])){
  header('location:../index.php');	
}

include "../dbcon.php";

// Handle form submission
if(isset($_POST['submit'])){
  $member_id = intval($_POST['member_id']);
  $plan_id = intval($_POST['plan_id']);
  $start_date = mysqli_real_escape_string($conn, $_POST['start_date']);
  $end_date = mysqli_real_escape_string($conn, $_POST['end_date']);
  $notes = mysqli_real_escape_string($conn, $_POST['notes']);
  
  // Check if this member already has this plan assigned
  $check_query = "SELECT id FROM member_workout_plans WHERE member_id = $member_id AND plan_id = $plan_id AND status = 'active'";
  $check_result = mysqli_query($conn, $check_query);
  
  if(mysqli_num_rows($check_result) > 0){
    $_SESSION['error_msg'] = "This workout plan is already assigned to this member.";
  } else {
    // Insert into member_workout_plans table
    $insert_assignment = "INSERT INTO member_workout_plans (member_id, plan_id, start_date, end_date, notes) 
                         VALUES ($member_id, $plan_id, '$start_date', '$end_date', '$notes')";
    
    if(mysqli_query($conn, $insert_assignment)){
      $_SESSION['success_msg'] = "Workout plan assigned successfully!";
      header('location:workout-plan.php');
      exit();
    } else {
      $_SESSION['error_msg'] = "Error assigning workout plan: " . mysqli_error($conn);
    }
  }
}

// Get plans list for dropdown
$plans_query = "SELECT id, plan_name FROM workout_plans ORDER BY plan_name ASC";
$plans_result = mysqli_query($conn, $plans_query);

// Get members list for dropdown
$members_query = "SELECT user_id, fullname FROM members ORDER BY fullname ASC";
$members_result = mysqli_query($conn, $members_query);

// Check if a specific plan is pre-selected
$selected_plan = isset($_GET['plan_id']) ? intval($_GET['plan_id']) : 0;

// Check if a specific member is pre-selected
$selected_member = isset($_GET['member_id']) ? intval($_GET['member_id']) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<title>Gym System Admin - Assign Workout Plan</title>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<link rel="stylesheet" href="../css/bootstrap.min.css" />
<link rel="stylesheet" href="../css/bootstrap-responsive.min.css" />
<link rel="stylesheet" href="../css/fullcalendar.css" />
<link rel="stylesheet" href="../css/matrix-style.css" />
<link rel="stylesheet" href="../css/matrix-media.css" />
<link href="../font-awesome/css/fontawesome.css" rel="stylesheet" />
<link href="../font-awesome/css/all.css" rel="stylesheet" />
<link href="https://fonts.googleapis.com/css?family=Open+Sans:400,700,800" rel="stylesheet" type="text/css">
</head>
<body>

<!--Header-part-->
<div id="header">
  <h1><a href="dashboard.html">Gym Admin</a></h1>
</div>
<!--close-Header-part--> 

<!--top-Header-menu-->
<?php include 'includes/topheader.php'?>
<!--close-top-Header-menu-->

<!--sidebar-menu-->
<?php include 'includes/sidebar.php'?>
<!--sidebar-menu-->

<div id="content">
  <div id="content-header">
    <div id="breadcrumb"> <a href="index.php" title="Go to Home" class="tip-bottom"><i class="fas fa-home"></i> Home</a> <a href="workout-plan.php">Workout Plans</a> <a href="#" class="current">Assign Workout Plan</a> </div>
    <h1>Assign Workout Plan</h1>
  </div>
  
  <div class="container-fluid">
    <div class="row-fluid">
      <div class="span12">
        <div class="widget-box">
          <div class="widget-title"> <span class="icon"> <i class="fas fa-dumbbell"></i> </span>
            <h5>Assign Workout Plan to Member</h5>
          </div>
          <div class="widget-content nopadding">
            <?php
              // Display error message if any
              if(isset($_SESSION['error_msg'])) {
                echo '<div class="alert alert-danger"><button class="close" data-dismiss="alert">×</button><strong>Error!</strong> '.$_SESSION['error_msg'].'</div>';
                unset($_SESSION['error_msg']);
              }
            ?>
            
            <form action="" method="post" class="form-horizontal">
              <div class="control-group">
                <label class="control-label">Select Member :</label>
                <div class="controls">
                  <select name="member_id" required>
                    <option value="">Select a Member</option>
                    <?php while($member = mysqli_fetch_array($members_result)): ?>
                      <option value="<?php echo $member['user_id']; ?>" <?php if($selected_member == $member['user_id']) echo "selected"; ?>>
                        <?php echo $member['fullname']; ?>
                      </option>
                    <?php endwhile; ?>
                  </select>
                </div>
              </div>
              
              <div class="control-group">
                <label class="control-label">Select Workout Plan :</label>
                <div class="controls">
                  <select name="plan_id" required>
                    <option value="">Select a Plan</option>
                    <?php while($plan = mysqli_fetch_array($plans_result)): ?>
                      <option value="<?php echo $plan['id']; ?>" <?php if($selected_plan == $plan['id']) echo "selected"; ?>>
                        <?php echo $plan['plan_name']; ?>
                      </option>
                    <?php endwhile; ?>
                  </select>
                </div>
              </div>
              
              <div class="control-group">
                <label class="control-label">Start Date:</label>
                <div class="controls">
                  <input type="date" name="start_date" required />
                </div>
              </div>
              
              <div class="control-group">
                <label class="control-label">End Date:</label>
                <div class="controls">
                  <input type="date" name="end_date" required />
                </div>
              </div>
              
              <div class="control-group">
                <label class="control-label">Notes:</label>
                <div class="controls">
                  <textarea name="notes" cols="30" rows="5"></textarea>
                </div>
              </div>
              
              <div class="form-actions">
                <button type="submit" name="submit" class="btn btn-primary">Assign Plan</button>
                <a href="workout-plan.php" class="btn">Cancel</a>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!--Footer-part-->
<div class="row-fluid">
  <div id="footer" class="span12"> <?php echo date('Y') ?> &copy; Gym Management System by <a href="#">Your Company</a> </div>
</div>
<!--end-Footer-part-->


</body>
</html>