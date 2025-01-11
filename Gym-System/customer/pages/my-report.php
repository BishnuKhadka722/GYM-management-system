<?php
include "../../dbcon.php"; 
include "session.php"; 
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
    <h1><a href="index.php">Perfect Gym System</a></h1>
</div>



<?php include '../includes/topheader.php' ?>

<?php $page = "report"; include '../includes/sidebar.php' ?>


<?php
$qry = "SELECT * FROM members WHERE user_id='" . $_SESSION['user_id'] . "'";
$result = mysqli_query($con, $qry);
while ($row = mysqli_fetch_array($result)) {
?> 

<!--main-container-part-->
<div id="content">
    <div id="content-header">
        <div id="breadcrumb">
            <a href="index.php" title="Go to Home" class="tip-bottom">
                <i class="icon-home"></i> Home
            </a>
            <a href="my-report.php" class="current">My Report</a>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row-fluid">
            <div class="span12">
                <div class="widget-box">
                    <div class="widget-content">
                        <div class="row-fluid">
                            <div class="span4">
                                <table>
                                    <tbody>
                                        <tr>
                                            <td><h4>Perfect GYM Club</h4></td>
                                        </tr>
                                        <tr>
                                            <td>Chabahill, Kathmandu</td>
                                        </tr>
                                        <tr>
                                            <td>Tel: 023-582-010</td>
                                        </tr>
                                        <tr>
                                            <td>Email: support@perfectgym.com</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="span8">
                                <table class="table table-bordered table-invoice-full">
                                    <thead>
                                        <tr>
                                            <th class="head0">Membership ID</th>
                                            <th class="head1">Services Taken</th>
                                            <th class="head0 right">My Plans (Upto)</th>
                                            <th class="head1 right">Address</th>
                                            <th class="head0 right">Charge</th>
                                            <th class="head0 right">Attendance Count</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><div class="text-center">PGC-SS-<?php echo $row['user_id']; ?></div></td>
                                            <td><div class="text-center"><?php echo $row['services']; ?></div></td>
                                            <td><div class="text-center"><?php echo $row['plan']; ?> Month/s</div></td>
                                            <td><div class="text-center"><?php echo $row['address']; ?></div></td>
                                            <td><div class="text-center"><?php echo '$' . $row['amount']; ?></div></td>
                                            <td><div class="text-center"><?php echo $row['attendance_count']; ?> Day/s</div></td>
                                        </tr>
                                    </tbody>
                                </table>
                                <table class="table table-bordered table-invoice-full">
                                    <tbody>
                                        <tr>
                                            <td class="msg-invoice" width="55%"> 
                                                <div class="text-center">
                                                    <h4>Last Payment Done:  $<?php echo $row['amount']; ?>/-</h4>
                                                    <em>
                                                        <a href="#" class="tip-bottom" title="Registration Date" style="font-size:15px;">
                                                            Member Since: <?php echo $row['dor']; ?> 
                                                        </a>
                                                    </em> 
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div> <!-- end of span 12 -->
                        </div>

                        <div class="row-fluid">
                            <div class="pull-left">
                                <h4>Dear Member <?php echo $row['fullname']; ?>,<br/><br/>
                                    Your Membership is currently <?php echo $row['status']; ?>!<br/>
                                </h4>
                                <p>Thank you for choosing our services.<br/> </p>
                            </div>
                            <div class="pull-right">
                                <h4><span>Approved By:</span></h4>
                                <img src="../img/report/report.png" width="124px;" alt="">
                                <p class="text-center">Note: AutoGenerated</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        }
        ?>
    </div>
</div>
<!--end-main-container-part-->

<!--Footer-part-->
<div class="row-fluid">
    <div id="footer" class="span12">
        <?php echo date("Y"); ?> &copy; Developed By Bishnu Khadka
    </div>
</div>

<style>
#footer {
    color: white;
}
</style>


</body>
</html>
