<?php
session_start();
// Check if user is logged in
if(!isset($_SESSION['user_id'])){
  header('location:../index.php');	
}

include "../dbcon.php";

// Handle assignment form submission
if(isset($_POST['assign_submit'])){
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

// Check if we're showing the assign form (triggered by GET request or form error)
$show_assign_form = isset($_GET['assign']) || (isset($_POST['assign_submit']) && isset($_SESSION['error_msg']));

// Get the plan ID if specified
$selected_plan = isset($_GET['plan_id']) ? intval($_GET['plan_id']) : 0;

// Check if a specific member is pre-selected
$selected_member = isset($_GET['member_id']) ? intval($_GET['member_id']) : 0;

// Get plans list for dropdown (only if showing assign form)
if($show_assign_form) {
  $plans_query = "SELECT id, plan_name FROM workout_plans ORDER BY plan_name ASC";
  $plans_result = mysqli_query($conn, $plans_query);

  // Get members list for dropdown
  $members_query = "SELECT user_id, fullname FROM members ORDER BY fullname ASC";
  $members_result = mysqli_query($conn, $members_query);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<title>Gym System Admin - Workout Plans</title>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<link rel="stylesheet" href="../css/bootstrap.min.css" />
<link rel="stylesheet" href="../css/bootstrap-responsive.min.css" />
<link rel="stylesheet" href="../css/fullcalendar.css" />
<link rel="stylesheet" href="../css/matrix-style.css" />
<link rel="stylesheet" href="../css/matrix-media.css" />
<link href="../font-awesome/css/fontawesome.css" rel="stylesheet" />
<link href="../font-awesome/css/all.css" rel="stylesheet" />
<link rel="stylesheet" href="../css/jquery.gritter.css" />
<link href='http://fonts.googleapis.com/css?family=Open+Sans:400,700,800' rel='stylesheet' type='text/css'>
<style>
  .assign-form {
    display: none;
    margin-bottom: 20px;
  }
  .assign-form.active {
    display: block;
  }
  .close-form {
    float: right;
    cursor: pointer;
    font-size: 16px;
    color: #999;
  }
  .close-form:hover {
    color: #333;
  }
</style>
</head>
<body>

<!--Header-part-->
<div id="header">
  <h1><a href="dashboard.html">Perfect Gym Admin</a></h1>
</div>
<!--close-Header-part--> 

<!--top-Header-menu-->
<?php include 'includes/topheader.php'?>
<!--close-top-Header-menu-->

<!--sidebar-menu-->
<?php $page='workout-plan'; include 'includes/sidebar.php'?>
<!--sidebar-menu-->

<div id="content">
  <div id="content-header">
    <div id="breadcrumb"> 
      <a href="index.php" title="Go to Home" class="tip-bottom"><i class="fas fa-home"></i> Home</a> 
      <a href="#" class="current">Workout Plans</a> 
    </div>
    <h1 class="text-center">Workout Plans Management <i class="fas fa-dumbbell"></i></h1>
  </div>
  <div class="container-fluid">
    <hr>
    <div class="row-fluid">
      <div class="span12">
      
        <?php
        // Display success or error messages if any
        if(isset($_SESSION['success_msg'])) {
          echo '<div class="alert alert-success">'.$_SESSION['success_msg'].'</div>';
          unset($_SESSION['success_msg']);
        }
        if(isset($_SESSION['error_msg'])) {
          echo '<div class="alert alert-danger">'.$_SESSION['error_msg'].'</div>';
          unset($_SESSION['error_msg']);
        }
        ?>

        <!-- Assign Workout Plan Form (Initially Hidden) -->
        <div class="widget-box assign-form <?php echo $show_assign_form ? 'active' : ''; ?>" id="assignForm">
          <div class="widget-title"> 
            <span class="icon"><i class="fas fa-user-plus"></i></span>
            <h5>Assign Workout Plan to Member</h5>
            <span class="close-form" onclick="toggleAssignForm(false)"><i class="fas fa-times"></i></span>
          </div>
          <div class="widget-content nopadding">
            <form action="" method="post" class="form-horizontal">
              <div class="control-group">
                <label class="control-label">Select Member:</label>
                <div class="controls">
                  <select name="member_id" required>
                    <option value="">Select a Member</option>
                    <?php if($show_assign_form) { 
                      while($member = mysqli_fetch_array($members_result)): ?>
                      <option value="<?php echo $member['user_id']; ?>" <?php if($selected_member == $member['user_id']) echo "selected"; ?>>
                        <?php echo $member['fullname']; ?>
                      </option>
                    <?php endwhile; } ?>
                  </select>
                </div>
              </div>
              
              <div class="control-group">
                <label class="control-label">Select Workout Plan:</label>
                <div class="controls">
                  <select name="plan_id" required>
                    <option value="">Select a Plan</option>
                    <?php if($show_assign_form) { 
                      while($plan = mysqli_fetch_array($plans_result)): ?>
                      <option value="<?php echo $plan['id']; ?>" <?php if($selected_plan == $plan['id']) echo "selected"; ?>>
                        <?php echo $plan['plan_name']; ?>
                      </option>
                    <?php endwhile; } ?>
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
                <button type="submit" name="assign_submit" class="btn btn-primary">Assign Plan</button>
                <button type="button" class="btn" onclick="toggleAssignForm(false)">Cancel</button>
              </div>
            </form>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="text-right" style="margin-bottom: 10px;">
          <a href="add-workout-plan.php" class="btn btn-primary"><i class="fas fa-plus"></i> Create New Workout Plan</a>
          <a href="javascript:void(0);" onclick="toggleAssignForm(true)" class="btn btn-success"><i class="fas fa-user-plus"></i> Assign Plan to Member</a>
        </div>

        <div class='widget-box'>
          <div class='widget-title'> 
            <span class='icon'><i class='fas fa-dumbbell'></i></span>
            <h5>Workout Plans</h5>
          </div>
          <div class='widget-content nopadding'>
          
          <?php
          $qry = "SELECT wp.*, COUNT(we.id) as exercise_count 
                  FROM workout_plans wp
                  LEFT JOIN workout_exercises we ON wp.id = we.plan_id
                  GROUP BY wp.id
                  ORDER BY wp.created_date DESC";
          $result = mysqli_query($conn, $qry);
          
          if(mysqli_num_rows($result) > 0) {
            echo "
            <table class='table table-bordered table-hover'>
              <thead>
                <tr>
                  <th>#</th>
                  <th>Plan Name</th>
                  <th>Description</th>
                  <th>Difficulty</th>
                  <th>Duration</th>
                  <th>Exercises</th>
                  <th>Created Date</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>";
              
            $cnt = 1;
            while($row = mysqli_fetch_array($result)){
              echo "
                <tr>
                  <td><div class='text-center'>".$cnt."</div></td>
                  <td><div class='text-center'>".$row['plan_name']."</div></td>
                  <td><div class='text-center'>".substr($row['description'], 0, 50).(strlen($row['description']) > 50 ? '...' : '')."</div></td>
                  <td><div class='text-center'>".$row['difficulty_level']."</div></td>
                  <td><div class='text-center'>".$row['duration_weeks']." weeks</div></td>
                  <td><div class='text-center'>".$row['exercise_count']."</div></td>
                  <td><div class='text-center'>".date('Y-m-d', strtotime($row['created_date']))."</div></td>
                  <td><div class='text-center'>
                      
                      <a href='edit-workout-plan.php?id=".$row['id']."'><i class='fas fa-edit'></i> Edit</a> | 
                      <a href='actions/delete-workout-plan.php?id=".$row['id']."' style='color:#F66;' onclick=\"return confirm('Are you sure you want to delete this workout plan?');\"><i class='fas fa-trash'></i> Delete</a> |
                    
                    </div>
                  </td>
                </tr>";
              $cnt++;
            }
            echo "</tbody></table>";
          } else {
            echo "<div class='alert alert-info'>No workout plans found. <a href='add-workout-plan.php'>Create your first workout plan</a>.</div>";
          }
          ?>
          </div>
        </div>
        
        <!-- Member Assignments Section -->
        <div class='widget-box'>
          <div class='widget-title'> 
            <span class='icon'><i class='fas fa-users'></i></span>
            <h5>Recent Workout Plan Assignments</h5>
          </div>
          <div class='widget-content nopadding'>
          
          <?php
          $qry2 = "SELECT mwp.*, m.fullname as member_name, wp.plan_name 
                   FROM member_workout_plans mwp
                   JOIN members m ON mwp.member_id = m.user_id
                   JOIN workout_plans wp ON mwp.plan_id = wp.id
                   ORDER BY mwp.assigned_date DESC
                   LIMIT 10";
          $result2 = mysqli_query($conn, $qry2);
          
          if(mysqli_num_rows($result2) > 0) {
            echo "
            <table class='table table-bordered table-hover'>
              <thead>
                <tr>
                  <th>#</th>
                  <th>Member</th>
                  <th>Workout Plan</th>
                  <th>Start Date</th>
                  <th>End Date</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>";
              
            $cnt = 1;
            while($row = mysqli_fetch_array($result2)){
              echo "
                <tr>
                  <td><div class='text-center'>".$cnt."</div></td>
                  <td><div class='text-center'>".$row['member_name']."</div></td>
                  <td><div class='text-center'>".$row['plan_name']."</div></td>
                  <td><div class='text-center'>".$row['start_date']."</div></td>
                  <td><div class='text-center'>".$row['end_date']."</div></td>
                  <td><div class='text-center'>
                    <span class='badge ".($row['status'] == 'active' ? 'badge-success' : 'badge-warning')."'>".$row['status']."</span>
                  </div></td>
                  <td><div class='text-center'>
                      
                      <a href='edit-member-plan.php?id=".$row['id']."'><i class='fas fa-edit'></i> Edit</a> | 
                      <a href='actions/delete-member-plan.php?id=".$row['id']."' style='color:#F66;' onclick=\"return confirm('Are you sure you want to delete this assignment?');\"><i class='fas fa-trash'></i> Delete</a>
                    </div>
                  </td>
                </tr>";
              $cnt++;
            }
            echo "</tbody></table>";
          } else {
            echo "<div class='alert alert-info'>No workout plan assignments found. Click 'Assign Plan to Member' to assign a workout plan.</div>";
          }
          ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!--end-main-container-part-->

<!--Footer-part-->
<div class="row-fluid">
  <div id="footer" class="span12"> <?php echo date("Y");?> &copy; Developed By Bishnu Khadka</a> </div>
</div>

<style>
#footer {
  color: white;
}
</style>

<!--end-Footer-part-->

<script src="../js/jquery.min.js"></script> 
<script src="../js/jquery.ui.custom.js"></script> 
<script src="../js/bootstrap.min.js"></script> 
<script src="../js/matrix.js"></script>
<script>
// Function to toggle the assign form visibility
function toggleAssignForm(show) {
  var form = document.getElementById('assignForm');
  
  if(show) {
    form.classList.add('active');
    // If we're showing the form, refresh the page to load the member and plan lists
    if(!form.classList.contains('loaded')) {
      window.location.href = 'workout-plan.php?assign=1';
    }
  } else {
    form.classList.remove('active');
  }
}

// Function to assign a specific plan
function assignPlan(planId) {
  window.location.href = 'workout-plan.php?assign=1&plan_id=' + planId;
}

// Add a class to the form when it's loaded with data
document.addEventListener('DOMContentLoaded', function() {
  var form = document.getElementById('assignForm');
  if(form.classList.contains('active')) {
    form.classList.add('loaded');
  }
});
</script>
</body>
</html>