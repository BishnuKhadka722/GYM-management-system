<?php
session_start();
//the isset function to check username is already loged in and stored on the session
if(!isset($_SESSION['user_id'])){
header('location:../index.php');	
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<title>Gym System Admin</title>
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
    <div id="breadcrumb"> <a href="index.php" title="Go to Home" class="tip-bottom"><i class="fas fa-home"></i> Home</a> <a href="workout-plan.php">Workout Plans</a> <a href="#" class="current">Assign Workout Plan</a> </div>
    <h1 class="text-center">Assign Workout Plan <i class="fas fa-user-plus"></i></h1>
  </div>
  <div class="container-fluid">
    <hr>
    <div class="row-fluid">
      <div class="span12">
        <?php
        include "../dbcon.php";
        
        // Check if plan_id is set in the URL
        if(isset($_GET['plan_id'])) {
            $plan_id = $_GET['plan_id'];
            
            // Get plan details
            $plan_qry = "SELECT * FROM workout_plans WHERE id = $plan_id";
            $plan_result = mysqli_query($conn, $plan_qry);
            $plan = mysqli_fetch_array($plan_result);
            
            if($plan) {
        ?>
        <div class="widget-box">
          <div class="widget-title"> <span class="icon"> <i class="fas fa-info"></i> </span>
            <h5>Workout Plan Details</h5>
          </div>
          <div class="widget-content">
            <div class="row-fluid">
              <div class="span6">
                <table class="table table-bordered table-striped">
                  <tr>
                    <td><strong>Plan Name</strong></td>
                    <td><?php echo $plan['plan_name']; ?></td>
                  </tr>
                  <tr>
                    <td><strong>Description</strong></td>
                    <td><?php echo $plan['description']; ?></td>
                  </tr>
                  <tr>
                    <td><strong>Duration</strong></td>
                    <td><?php echo $plan['duration']; ?> weeks</td>
                  </tr>
                  <tr>
                    <td><strong>Level</strong></td>
                    <td><?php echo $plan['level']; ?></td>
                  </tr>
                </table>
              </div>
              <div class="span6">
                <h5>Equipment Used</h5>
                <ul>
                  <?php
                    // Get equipment for this plan
                    $equipment_qry = "SELECT e.name FROM equipment e 
                                    JOIN workout_equipment we ON e.id = we.equipment_id 
                                    WHERE we.workout_plan_id = $plan_id";
                    $equipment_result = mysqli_query($conn, $equipment_qry);
                    
                    while($equip = mysqli_fetch_array($equipment_result)) {
                      echo "<li>" . $equip['name'] . "</li>";
                    }
                    
                    if(mysqli_num_rows($equipment_result) == 0) {
                      echo "<li>No equipment specified</li>";
                    }
                  ?>
                </ul>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Assign Workout Plan Form -->
        <div class="widget-box">
          <div class="widget-title"> <span class="icon"> <i class="fas fa-user-plus"></i> </span>
            <h5>Assign Workout Plan to Members</h5>
          </div>
          <div class="widget-content nopadding">
            <form method="post" action="actions/assign-workout-action.php" class="form-horizontal">
              <input type="hidden" name="workout_plan_id" value="<?php echo $plan_id; ?>">
              
              <div class="control-group">
                <label class="control-label">Select Members :</label>
                <div class="controls">
                  <select name="member_ids[]" multiple class="span11" required>
                    <?php
                      // Get all members
                      $members_qry = "SELECT * FROM members ORDER BY fullname";
                      $members_result = mysqli_query($conn, $members_qry);
                      
                      while($member = mysqli_fetch_array($members_result)) {
                        echo "<option value='" . $member['user_id'] . "'>" . $member['fullname'] . " (ID: " . $member['user_id'] . ")</option>";
                      }
                    ?>
                  </select>
                  <span class="help-block">Hold Ctrl to select multiple members</span>
                </div>
              </div>
              
              <div class="control-group">
                <label class="control-label">Start Date :</label>
                <div class="controls">
                  <input type="date" name="start_date" class="span11" required>
                </div>
              </div>
              
              <div class="control-group">
                <label class="control-label">Notes :</label>
                <div class="controls">
                  <textarea name="notes" class="span11" placeholder="Additional notes for the members"></textarea>
                </div>
              </div>
              
              <div class="form-actions">
                <button type="submit" class="btn btn-success">Assign Workout Plan</button>
                <a href="workout-plan.php" class="btn btn-danger">Cancel</a>
              </div>
            </form>
          </div>
        </div>
        
        <?php
            } else {
                echo "<div class='alert alert-danger'>Workout plan not found!</div>";
            }
        } else {
            echo "<div class='alert alert-danger'>No workout plan selected!</div>";
        }
        ?>
      </div>
    </div>
  </div>
</div>

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
</body>
</html>