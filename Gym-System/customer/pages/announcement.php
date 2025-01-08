<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('location:../index.php');
    exit();
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

<?php include '../includes/topheader.php'; ?>

<?php $page = "announcement"; include '../includes/sidebar.php'; ?>



<div id="content">
  <div id="content-header">
    <div id="breadcrumb"> <a href="index.php" title="You're right here" class="tip-bottom"><i class="icon-home"></i> Home</a></div>
  </div>
 

  <!--Action boxes-->
  <div class="container-fluid">
    <div class="row-fluid">
      <div class="span12">
        <div class="widget-box">
          <div class="widget-title bg_ly" data-toggle="collapse" href="#collapseG2">
            <span class="icon"><i class="icon-chevron-down"></i></span>
            <h5>Gym Announcement</h5>
          </div>
          <div class="widget-content nopadding collapse in" id="collapseG2">
            <ul class="recent-posts">
              <li>
              <?php
        
              $dbcon_path = "../../dbcon.php"; 
              if (file_exists($dbcon_path)) {
                  include $dbcon_path;

                  // Check database connection
                  if (!$con) {
                      echo "<p style='color: red;'>Database connection failed: " . mysqli_connect_error() . "</p>";
                      exit;
                  }

                 
                  $qry = "SELECT * FROM announcements";
                  $result = mysqli_query($con, $qry);

                  if ($result && mysqli_num_rows($result) > 0) {
                      while ($row = mysqli_fetch_assoc($result)) {
                          echo "<div class='user-thumb'> <img width='50' height='50' alt='User' src='../img/demo/av2.jpg'> </div>";
                          echo "<div class='article-post'>";
                          echo "<span class='user-info'> By: System Administrator / Date: " . $row['date'] . " </span>";
                          echo "<p><a href='#'>" . htmlspecialchars($row['message']) . "</a> </p>";
                          echo "</div>";
                      }
                  } else {
                      echo "<p>No announcements available.</p>";
                  }
              } else {
                  echo "<p style='color: red;'>Error: Unable to include dbcon.php. Please check the file path.</p>";
              }
              ?>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>


<div class="row-fluid">
  <div id="footer" class="span12"> <?php echo date("Y"); ?> &copy; Developed By Bishnu Khadka </div>
</div>

<style>
#footer {
  color: white;
}
</style>

</body>
</html>
