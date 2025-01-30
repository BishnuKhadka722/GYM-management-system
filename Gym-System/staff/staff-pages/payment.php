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

<!--Header-part-->
<div id="header">
  <h1><a href="dashboard.html">Perfect Gym</a></h1>
</div>
<!--close-Header-part--> 


<!--top-Header-menu-->
<?php include '../includes/header.php'?>

<?php $page="payment"; include '../includes/sidebar.php'?>
<!--sidebar-menu-->

<div id="content">
  <div id="content-header">
    <div id="breadcrumb"> <a href="index.php" title="Go to Home" class="tip-bottom"><i class="icon-home"></i> Home</a> <a href="payment.php" class="current">Payments</a> </div>
    <h1 class="text-center">Registered Member's Payment <i class="icon icon-group"></i></h1>
  </div>
  <div class="container-fluid">
    <hr>
    <div class="row-fluid">
      <div class="span12">

      <div class='widget-box'>
          <div class='widget-title'> <span class='icon'> <i class='icon-th'></i> </span>
            <h5>Member's Payment table</h5>
            <form id="custom-search-form" role="search" method="POST" action="search-result.php" class="form-search form-horizontal pull-right">
                <div class="input-append span12">
                    <input type="text" class="search-query" placeholder="Search" name="search" required>
                    <button type="submit" class="btn"><i class="icon-search"></i></button>
                </div>
            </form>
          </div>
          <div class='widget-content nopadding'>
	  
          <?php

include "../../dbcon.php";
$qry="SELECT * FROM members";
$cnt = 1;
  $result=mysqli_query($con,$qry);

  
    echo"<table class='table table-bordered data-table table-hover'>
        <thead>
          <tr>
            <th>#</th>
            <th>Fullname</th>
            <th>Last Payment Date</th>
            <th>Amount</th>
            <th>Choosen Service</th>
            <th>Plan</th>
            <th>Action</th>
            <th>Remind</th>
          </tr>
        </thead>";
        
      while($row=mysqli_fetch_array($result)){ ?>
      
      <tbody> 
         
          <td><div class='text-center'><?php echo $cnt;?></div></td>
          <td><div class='text-center'><?php echo $row['fullname']?></div></td>
          <td><div class='text-center'><?php echo($row['paid_date'] == 0 ? "New Member" : $row['paid_date'])?></div></td>
          
          <td><div class='text-center'><?php echo '$'.$row['amount']?></div></td>
          <td><div class='text-center'><?php echo $row['services']?></div></td>
          <td><div class='text-center'><?php echo $row['plan']." Month/s"?></div></td>
          <td><div class='text-center'><a href='user-payment.php?id=<?php echo $row['user_id']?>'><button class='btn btn-success btn'><i class='icon icon-money'></i> Make Payment</button></a></div></td>
          <td><div class='text-center'><a href='sendReminder.php?id=<?php echo $row['user_id']?>'><button class='btn btn-danger btn' <?php echo($row['reminder'] == 1 ? "disabled" : "")?>>Alert</button></a></div></td>
        </tbody>
    <?php $cnt++; }

      ?>

            </table>
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

<!--end-Footer-part-->

<style>
    #custom-search-form {
        margin:0;
        margin-top: 5px;
        padding: 0;
    }
 
    #custom-search-form .search-query {
        padding-right: 3px;
        padding-right: 4px \9;
        padding-left: 3px;
        padding-left: 4px \9;
        /* IE7-8 doesn't have border-radius, so don't indent the padding */
 
        margin-bottom: 0;
        -webkit-border-radius: 3px;
        -moz-border-radius: 3px;
        border-radius: 3px;
    }
 
    #custom-search-form button {
        border: 0;
        background: none;
        /** belows styles are working good */
        padding: 2px 5px;
        margin-top: 2px;
        position: relative;
        left: -28px;
        /* IE7-8 doesn't have border-radius, so don't indent the padding */
        margin-bottom: 0;
        -webkit-border-radius: 3px;
        -moz-border-radius: 3px;
        border-radius: 3px;
    }
 
    .search-query:focus + button {
        z-index: 3;   
    }
</style>


</body>
</html>
