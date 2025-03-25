<?php
session_start();
// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('location:../index.php');
    exit();
}

// Get user ID from URL
$user_id = isset($_GET['id']) ? $_GET['id'] : null;
if (!$user_id) {
    echo "<script>alert('No user ID provided!'); window.location = 'attendance.php';</script>";
    exit();
}

include "../dbcon.php";
// Get user information
$user_query = "SELECT * FROM members WHERE user_id = '$user_id'";
$user_result = mysqli_query($conn, $user_query);
$user_data = mysqli_fetch_assoc($user_result);

if (!$user_data) {
    echo "<script>alert('User not found!'); window.location = 'attendance.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Gym System Admin</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="../css/bootstrap.min.css" />
    <link rel="stylesheet" href="../css/matrix-style.css" />
    <link rel="stylesheet" href="../css/matrix-media.css" />
    <link href="../font-awesome/css/font-awesome.css" rel="stylesheet" />
</head>
<body>

<div id="header">
    <h1><a href="dashboard.html">Perfect Gym Admin</a></h1>
</div>

<?php include 'includes/topheader.php'; ?>
<?php $page = "attendance"; include 'includes/sidebar.php'; ?>

<div id="content">
    <div id="content-header">
        <div id="breadcrumb"> 
            <a href="index.php" title="Go to Home" class="tip-bottom">
                <i class="icon-home"></i> Home
            </a> 
            <a href="attendance.php">Manage Attendance</a>
            <a href="#" class="current">Attendance History</a> 
        </div>
        <h1 class="text-center">Attendance History for <?php echo htmlspecialchars($user_data['fullname']); ?> <i class="icon icon-calendar"></i></h1>
    </div>
    <div class="container-fluid">
        <div class="row-fluid">
            <div class="span12">
                <div class="widget-box">
                    <div class="widget-title">
                        <span class="icon"><i class="icon-th"></i></span>
                        <h5>Attendance Records</h5>
                    </div>
                    <div class="widget-content">
                        <div class="row-fluid">
                            <div class="span12">
                                <p><strong>Member Name:</strong> <?php echo htmlspecialchars($user_data['fullname']); ?></p>
                                <p><strong>Contact:</strong> <?php echo htmlspecialchars($user_data['contact']); ?></p>
                                <p><strong>Service:</strong> <?php echo htmlspecialchars($user_data['services']); ?></p>
                                <a href="attendance.php" class="btn btn-primary"><i class="icon-arrow-left"></i> Back to Attendance</a>
                            </div>
                        </div>
                        <hr>
                        <div class="row-fluid">
                            <div class="span12">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Date</th>
                                            <th>Check-in Time</th>
                                            <th>Check-out Time</th>
                                            <th>Duration</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // Get all attendance records for this user
                                        $query = "SELECT * FROM attendance WHERE user_id = '$user_id' ORDER BY curr_date DESC, check_in_time DESC";
                                        $result = mysqli_query($conn, $query);
                                        $cnt = 1;
                                        
                                        while ($row = mysqli_fetch_array($result)) {
                                            // Calculate duration if checkout exists
                                            $duration = '-';
                                            if (!empty($row['check_out_time'])) {
                                                $check_in = new DateTime($row['check_in_time']);
                                                $check_out = new DateTime($row['check_out_time']);
                                                $interval = $check_in->diff($check_out);
                                                $duration = $interval->format('%H:%I:%S');
                                            }
                                            ?>
                                            <tr>
                                                <td><?php echo $cnt++; ?></td>
                                                <td><?php echo date('M d, Y', strtotime($row['curr_date'])); ?></td>
                                                <td><?php echo date('h:i A', strtotime($row['check_in_time'])); ?></td>
                                                <td>
                                                    <?php 
                                                    if (!empty($row['check_out_time'])) {
                                                        echo date('h:i A', strtotime($row['check_out_time'])); 
                                                    } else {
                                                        echo '-';
                                                    }
                                                    ?>
                                                </td>
                                                <td><?php echo $duration; ?></td>
                                                <td>
                                                    <?php if ($row['status'] == 1) { ?>
                                                        <span class="label label-warning">Active</span>
                                                    <?php } else { ?>
                                                        <span class="label label-success">Completed</span>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                        if (mysqli_num_rows($result) == 0) {
                                            echo '<tr><td colspan="6" class="text-center">No attendance records found.</td></tr>';
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row-fluid">
    <div id="footer" class="span12"> <?php echo date("Y"); ?> &copy; Developed By Bishnu Khadka</div>
</div>

<script src="../js/jquery.min.js"></script>
<script src="../js/bootstrap.min.js"></script>
<script src="../js/matrix.js"></script>

</body>
</html>