<?php
session_start();
// Check if user is logged in
if(!isset($_SESSION['user_id'])){
  header('location:../index.php');
  exit();
}

include "../dbcon.php";

// Check if an ID is provided
if(!isset($_GET['id']) || empty($_GET['id'])) {
  $_SESSION['error_msg'] = "No workout plan specified.";
  header('location:workout-plan.php');
  exit();
}

$plan_id = intval($_GET['id']);

// Handle form submission for updating
if(isset($_POST['update_plan'])) {
  $plan_name = mysqli_real_escape_string($conn, $_POST['plan_name']);
  $description = mysqli_real_escape_string($conn, $_POST['description']);
  $difficulty_level = mysqli_real_escape_string($conn, $_POST['difficulty_level']);
  $duration_weeks = intval($_POST['duration_weeks']);
  
  // Update workout plan
  $update_query = "UPDATE workout_plans SET 
                   plan_name = '$plan_name', 
                   description = '$description', 
                   difficulty_level = '$difficulty_level', 
                   duration_weeks = $duration_weeks,
                   last_modified = NOW()
                   WHERE id = $plan_id";
  
  if(mysqli_query($conn, $update_query)) {
    $_SESSION['success_msg'] = "Workout plan updated successfully!";
    
    // Process exercises if submitted
    if(isset($_POST['exercise_name']) && is_array($_POST['exercise_name'])) {
      // First delete all existing exercises for this plan
      mysqli_query($conn, "DELETE FROM workout_exercises WHERE plan_id = $plan_id");
      
      // Add the new exercises
      foreach($_POST['exercise_name'] as $key => $name) {
        if(!empty($name)) {
          $exercise_name = mysqli_real_escape_string($conn, $name);
          $sets = isset($_POST['sets'][$key]) ? intval($_POST['sets'][$key]) : 0;
          $reps = isset($_POST['reps'][$key]) ? intval($_POST['reps'][$key]) : 0;
          $rest_time = isset($_POST['rest_time'][$key]) ? intval($_POST['rest_time'][$key]) : 0;
          $notes = isset($_POST['exercise_notes'][$key]) ? mysqli_real_escape_string($conn, $_POST['exercise_notes'][$key]) : '';
          
          $insert_exercise = "INSERT INTO workout_exercises (plan_id, exercise_name, sets, reps, rest_time, notes) 
                             VALUES ($plan_id, '$exercise_name', $sets, $reps, $rest_time, '$notes')";
          mysqli_query($conn, $insert_exercise);
        }
      }
    }
    header('location:workout-plan.php');
    exit();
  } else {
    $_SESSION['error_msg'] = "Error updating workout plan: " . mysqli_error($conn);
  }
}

// Get workout plan details
$query = "SELECT * FROM workout_plans WHERE id = $plan_id";
$result = mysqli_query($conn, $query);

if(mysqli_num_rows($result) == 0) {
  $_SESSION['error_msg'] = "Workout plan not found.";
  header('location:workout-plan.php');
  exit();
}

$plan = mysqli_fetch_assoc($result);

// Get exercises for this plan
$exercises_query = "SELECT * FROM workout_exercises WHERE plan_id = $plan_id ORDER BY id ASC";
$exercises_result = mysqli_query($conn, $exercises_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<title>Gym System Admin - Edit Workout Plan</title>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<link rel="stylesheet" href="../css/bootstrap.min.css" />
<link rel="stylesheet" href="../css/bootstrap-responsive.min.css" />
<link rel="stylesheet" href="../css/matrix-style.css" />
<link rel="stylesheet" href="../css/matrix-media.css" />
<link href="../font-awesome/css/fontawesome.css" rel="stylesheet" />
<link href="../font-awesome/css/all.css" rel="stylesheet" />
<link rel="stylesheet" href="../css/jquery.gritter.css" />
<link href='http://fonts.googleapis.com/css?family=Open+Sans:400,700,800' rel='stylesheet' type='text/css'>
<style>
  .exercise-row {
    border: 1px solid #eee;
    padding: 10px;
    margin-bottom: 10px;
    border-radius: 5px;
    background-color: #f9f9f9;
  }
  .remove-exercise {
    color: #F66;
    cursor: pointer;
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
      <a href="workout-plan.php">Workout Plans</a> 
      <a href="#" class="current">Edit Workout Plan</a> 
    </div>
    <h1>Edit Workout Plan</h1>
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
        
        <div class="widget-box">
          <div class="widget-title"> 
            <span class="icon"> <i class="fas fa-edit"></i> </span>
            <h5>Edit Workout Plan</h5>
          </div>
          <div class="widget-content nopadding">
            <form action="" method="post" class="form-horizontal">
              
              <div class="control-group">
                <label class="control-label">Plan Name :</label>
                <div class="controls">
                  <input type="text" class="span11" name="plan_name" value="<?php echo htmlspecialchars($plan['plan_name']); ?>" required />
                </div>
              </div>
              
              <div class="control-group">
                <label class="control-label">Description :</label>
                <div class="controls">
                  <textarea class="span11" name="description" rows="5"><?php echo htmlspecialchars($plan['description']); ?></textarea>
                </div>
              </div>
              
              <div class="control-group">
                <label class="control-label">Difficulty Level :</label>
                <div class="controls">
                  <select name="difficulty_level" class="span11">
                    <option value="Beginner" <?php if($plan['difficulty_level'] == 'Beginner') echo 'selected'; ?>>Beginner</option>
                    <option value="Intermediate" <?php if($plan['difficulty_level'] == 'Intermediate') echo 'selected'; ?>>Intermediate</option>
                    <option value="Advanced" <?php if($plan['difficulty_level'] == 'Advanced') echo 'selected'; ?>>Advanced</option>
                  </select>
                </div>
              </div>
              
              <div class="control-group">
                <label class="control-label">Duration (weeks) :</label>
                <div class="controls">
                  <input type="number" class="span11" name="duration_weeks" value="<?php echo intval($plan['duration_weeks']); ?>" min="1" max="52" required />
                </div>
              </div>
              
              <hr>
              <h4 class="text-center">Exercises</h4>
              
              <div id="exercises-container">
                <?php 
                if(mysqli_num_rows($exercises_result) > 0) {
                  $i = 0;
                  while($exercise = mysqli_fetch_assoc($exercises_result)) {
                    $i++;
                ?>
                <div class="exercise-row">
                  <div class="row-fluid">
                    <div class="span11">
                      <div class="control-group">
                        <label class="control-label">Exercise Name :</label>
                        <div class="controls">
                          <input type="text" class="span10" name="exercise_name[]" value="<?php echo htmlspecialchars($exercise['exercise_name']); ?>" required />
                        </div>
                      </div>
                      
                      <div class="row-fluid">
                        <div class="span4">
                          <div class="control-group">
                            <label class="control-label">Sets :</label>
                            <div class="controls">
                              <input type="number" class="span12" name="sets[]" value="<?php echo intval($exercise['sets']); ?>" min="1" />
                            </div>
                          </div>
                        </div>
                        
                        <div class="span4">
                          <div class="control-group">
                            <label class="control-label">Reps :</label>
                            <div class="controls">
                              <input type="number" class="span12" name="reps[]" value="<?php echo intval($exercise['reps']); ?>" min="1" />
                            </div>
                          </div>
                        </div>
                        
                        <div class="span4">
                          <div class="control-group">
                            <label class="control-label">Rest Time (sec) :</label>
                            <div class="controls">
                              <input type="number" class="span12" name="rest_time[]" value="<?php echo intval($exercise['rest_time']); ?>" min="0" />
                            </div>
                          </div>
                        </div>
                      </div>
                      
                      <div class="control-group">
                        <label class="control-label">Notes :</label>
                        <div class="controls">
                          <textarea class="span12" name="exercise_notes[]" rows="2"><?php echo htmlspecialchars($exercise['notes']); ?></textarea>
                        </div>
                      </div>
                    </div>
                    <div class="span1">
                      <a href="javascript:void(0);" class="remove-exercise" title="Remove Exercise"><i class="fas fa-trash-alt"></i></a>
                    </div>
                  </div>
                </div>
                <?php 
                  }
                } else {
                  // No exercises yet, show one blank form
                ?>
                <div class="exercise-row">
                  <div class="row-fluid">
                    <div class="span11">
                      <div class="control-group">
                        <label class="control-label">Exercise Name :</label>
                        <div class="controls">
                          <input type="text" class="span10" name="exercise_name[]" required />
                        </div>
                      </div>
                      
                      <div class="row-fluid">
                        <div class="span4">
                          <div class="control-group">
                            <label class="control-label">Sets :</label>
                            <div class="controls">
                              <input type="number" class="span12" name="sets[]" value="3" min="1" />
                            </div>
                          </div>
                        </div>
                        
                        <div class="span4">
                          <div class="control-group">
                            <label class="control-label">Reps :</label>
                            <div class="controls">
                              <input type="number" class="span12" name="reps[]" value="10" min="1" />
                            </div>
                          </div>
                        </div>
                        
                        <div class="span4">
                          <div class="control-group">
                            <label class="control-label">Rest Time (sec) :</label>
                            <div class="controls">
                              <input type="number" class="span12" name="rest_time[]" value="60" min="0" />
                            </div>
                          </div>
                        </div>
                      </div>
                      
                      <div class="control-group">
                        <label class="control-label">Notes :</label>
                        <div class="controls">
                          <textarea class="span12" name="exercise_notes[]" rows="2"></textarea>
                        </div>
                      </div>
                    </div>
                    <div class="span1">
                      <a href="javascript:void(0);" class="remove-exercise" title="Remove Exercise"><i class="fas fa-trash-alt"></i></a>
                    </div>
                  </div>
                </div>
                <?php } ?>
              </div>
              
              <div class="text-center" style="margin-bottom:20px;">
                <button type="button" id="add-exercise" class="btn btn-info"><i class="fas fa-plus"></i> Add Exercise</button>
              </div>
              
              <div class="form-actions">
                <button type="submit" name="update_plan" class="btn btn-success">Update Workout Plan</button>
                <a href="workout-plan.php" class="btn">Cancel</a>
              </div>
            </form>
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
$(document).ready(function() {
  // Add new exercise form
  $("#add-exercise").on("click", function() {
    var exerciseHtml = `
      <div class="exercise-row">
        <div class="row-fluid">
          <div class="span11">
            <div class="control-group">
              <label class="control-label">Exercise Name :</label>
              <div class="controls">
                <input type="text" class="span10" name="exercise_name[]" required />
              </div>
            </div>
            
            <div class="row-fluid">
              <div class="span4">
                <div class="control-group">
                  <label class="control-label">Sets :</label>
                  <div class="controls">
                    <input type="number" class="span12" name="sets[]" value="3" min="1" />
                  </div>
                </div>
              </div>
              
              <div class="span4">
                <div class="control-group">
                  <label class="control-label">Reps :</label>
                  <div class="controls">
                    <input type="number" class="span12" name="reps[]" value="10" min="1" />
                  </div>
                </div>
              </div>
              
              <div class="span4">
                <div class="control-group">
                  <label class="control-label">Rest Time (sec) :</label>
                  <div class="controls">
                    <input type="number" class="span12" name="rest_time[]" value="60" min="0" />
                  </div>
                </div>
              </div>
            </div>
            
            <div class="control-group">
              <label class="control-label">Notes :</label>
              <div class="controls">
                <textarea class="span12" name="exercise_notes[]" rows="2"></textarea>
              </div>
            </div>
          </div>
          <div class="span1">
            <a href="javascript:void(0);" class="remove-exercise" title="Remove Exercise"><i class="fas fa-trash-alt"></i></a>
          </div>
        </div>
      </div>
    `;
    $("#exercises-container").append(exerciseHtml);
  });
  
  // Remove exercise form
  $(document).on("click", ".remove-exercise", function() {
    // Check if there's at least one exercise row
    if($("#exercises-container .exercise-row").length > 1) {
      $(this).closest(".exercise-row").remove();
    } else {
      alert("You must have at least one exercise in the workout plan.");
    }
  });
});
</script>
</body>
</html>