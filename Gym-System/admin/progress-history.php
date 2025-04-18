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
<?php $page='manage-customer-progress'; include 'includes/sidebar.php'?>
<!--sidebar-menu-->

<?php
include '../dbcon.php';
$id=$_GET['id'];

// Get member details
$member_qry = "SELECT * FROM members WHERE user_id='$id'";
$member_result = mysqli_query($conn, $member_qry);
$member = mysqli_fetch_array($member_result);

// Get progress history
$history_qry = "SELECT * FROM progress_history WHERE user_id='$id' ORDER BY progress_date DESC, history_id DESC";
$history_result = mysqli_query($conn, $history_qry);
?> 

<div id="content">
  <div id="content-header">
    <div id="breadcrumb"> <a href="index.php" title="Go to Home" class="tip-bottom"><i class="fas fa-home"></i> Home</a> <a href="customer-progress.php">Customer Progress</a> <a href="#" class="current">Progress History</a> </div>
    <h1 class="text-center">Member's Progress History <i class="fas fa-history"></i></h1>
  </div>
  
  <div class="container-fluid">
    <div class="row-fluid">
      <div class="span12">
        <div class="widget-box">
          <div class="widget-title"> 
            <span class="icon"> <i class="fas fa-user"></i> </span>
            <h5>Member Information</h5>
            <span class="label label-info">
              <a href="update-progress.php?id=<?php echo $id; ?>" style="color: white;">
                <i class="fas fa-edit"></i> Update Progress
              </a>
            </span>
          </div>
          <div class="widget-content">
            <div class="row-fluid">
              <div class="span6">
                <table class="table table-bordered table-invoice">
                  <tbody>
                    <tr>
                      <td class="width30">Member's Fullname:</td>
                      <td class="width70"><strong><?php echo $member['fullname']; ?></strong></td>
                    </tr>
                    <tr>
                      <td>Service Taken:</td>
                      <td><strong><?php echo $member['services']; ?></strong></td>
                    </tr>
                    <tr>
                      <td>Plan:</td>
                      <td><strong><?php echo $member['plan']; ?> Month/s</strong></td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div class="span6">
                <table class="table table-bordered table-invoice">
                  <tbody>
                    <tr>
                      <td class="width30">Current Weight:</td>
                      <td class="width70"><strong><?php echo $member['curr_weight']; ?> KG</strong></td>
                    </tr>
                    <tr>
                      <td>Current Body Type:</td>
                      <td><strong><?php echo $member['curr_bodytype']; ?></strong></td>
                    </tr>
                    <tr>
                      <td>Last Update:</td>
                      <td><strong><?php echo $member['progress_date']; ?></strong></td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
        
        <div class="widget-box">
          <div class="widget-title"> 
            <span class="icon"><i class="fas fa-signal"></i></span>
            <h5>Progress History</h5>
          </div>
          <div class="widget-content nopadding">
            <table class="table table-bordered table-striped">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Date</th>
                  <th>Initial Weight</th>
                  <th>Current Weight</th>
                  <th>Weight Change</th>
                  <th>Initial Body Type</th>
                  <th>Current Body Type</th>
                  <th>Notes</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $count = 1;
                $prev_weight = 0;
                
                if (mysqli_num_rows($history_result) > 0) {
                  while ($history = mysqli_fetch_array($history_result)) {
                    $weight_change = '';
                    if ($count > 1 && $prev_weight > 0) {
                      $diff = $history['curr_weight'] - $prev_weight;
                      if ($diff > 0) {
                        $weight_change = "<span class='label label-important'>+{$diff} KG</span>";
                      } elseif ($diff < 0) {
                        $weight_change = "<span class='label label-success'>{$diff} KG</span>";
                      } else {
                        $weight_change = "<span class='label'>No Change</span>";
                      }
                    }
                    $prev_weight = $history['curr_weight'];
                    
                    echo "<tr>
                            <td>{$count}</td>
                            <td>{$history['progress_date']}</td>
                            <td>{$history['ini_weight']} KG</td>
                            <td>{$history['curr_weight']} KG</td>
                            <td>{$weight_change}</td>
                            <td>{$history['ini_bodytype']}</td>
                            <td>{$history['curr_bodytype']}</td>
                            <td>" . ($history['notes'] ? $history['notes'] : '<em>No notes</em>') . "</td>
                          </tr>";
                    $count++;
                  }
                } else {
                  echo "<tr><td colspan='8' class='text-center'>No progress history available</td></tr>";
                }
                ?>
              </tbody>
            </table>
          </div>
        </div>
        
        <div class="text-center">
          <a href="customer-progress.php" class="btn btn-inverse">Back to Member List</a>
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

<script src="../js/excanvas.min.js"></script> 
<script src="../js/jquery.min.js"></script> 
<script src="../js/jquery.ui.custom.js"></script> 
<script src="../js/bootstrap.min.js"></script> 
<script src="../js/jquery.flot.min.js"></script> 
<script src="../js/jquery.flot.resize.min.js"></script> 
<script src="../js/jquery.peity.min.js"></script> 
<script src="../js/fullcalendar.min.js"></script> 
<script src="../js/matrix.js"></script> 
<script src="../js/matrix.dashboard.js"></script> 
<script src="../js/jquery.gritter.min.js"></script> 
<script src="../js/matrix.interface.js"></script> 
<script src="../js/matrix.chat.js"></script> 
<script src="../js/jquery.validate.js"></script> 
<script src="../js/matrix.form_validation.js"></script> 
<script src="../js/jquery.wizard.js"></script> 
<script src="../js/jquery.uniform.js"></script> 
<script src="../js/select2.min.js"></script> 
<script src="../js/matrix.popover.js"></script> 
<script src="../js/jquery.dataTables.min.js"></script> 
<script src="../js/matrix.tables.js"></script> 

</body>
</html>