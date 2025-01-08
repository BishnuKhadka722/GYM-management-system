<?php
session_start();

if(!isset($_SESSION['user_id'])){
header('location:../index.php');	
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<title>Gym System</title>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<link rel="stylesheet" href="../css/bootstrap.min.css" />
<link rel="stylesheet" href="../css/matrix-style.css" />
<link rel="stylesheet" href="../css/matrix-media.css" />
</head>
<body>


<div id="header">
  <h1><a href="index.php">Perfect Gym System</a></h1>
</div>
 
<?php include '../includes/topheader.php'?>


<?php $page="todo"; include '../includes/sidebar.php'?>

<!--main-container-part-->
<div id="content">

  <div id="content-header">
    <div id="breadcrumb"> <a href="index.php" title="You're right here" class="tip-bottom"><i class="icon-home"></i> Home</a></div>
  </div>


<!--Action boxes-->
  <div class="container-fluid">
    
<!--End-Action boxes-->    

    <div class="row-fluid">
	  
    <div class="span12">
        <div class="widget-box">
          <div class="widget-title"> <span class="icon"> <i class="icon-pencil"></i> </span>
            <h5>To-Do Lists</h5>
          </div>
          <div class="widget-content nopadding">
            <form id="form-wizard" class="form-horizontal" action="add-to-do.php" method="POST">
              <div id="form-wizard-1" class="step">

              <div class="control-group">
                <label class="control-label">Please Enter Your Task :</label>
                <div class="controls">
                    <input type="text" class="span11" name="task_desc" placeholder="I'll be doing 12 set up and . . ." />
                </div>
                </div>

                 <div class="control-group">
                    <label class="control-label">Please Select a Status:</label>
                    <div class="controls">
                        <select name="task_status" required="required" id="select">
                        <option value="In Progress">In Progress</option>
                        <option value="Pending">Pending</option>
                        </select>
                    </div>
                </div>

              <div class="form-actions">
              <input type="hidden" name="userid" value="<?php echo $userid; ?>">
                <input id="add" class="btn btn-primary" type="submit" value="Add To List" />
                <div id="status"></div>
              </div>
              <div id="submitted"></div>
            </form>
          </div>
        </div>
      </div>
	  
	  
	  
    </div>
  </div>
</div>
</div>


<div class="row-fluid">
  <div id="footer" class="span12"> <?php echo date("Y");?> &copy; Developed By Bishnu Khadka</a> </div>
</div>

<style>
#footer {
  color: white;
}
</style>


</body>
</html>
