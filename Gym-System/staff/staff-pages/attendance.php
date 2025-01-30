<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('location:../index.php');
    exit();
}

include "../../dbcon.php";
date_default_timezone_set('Asia/Kathmandu');
$current_date = date('Y-m-d h:i A');
$todays_date = explode(' ', $current_date)[0];
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
</head>
<body>

<!-- Header -->
<div id="header">
    <h1><a href="dashboard.html">Perfect Gym Admin</a></h1>
</div>

<!-- Include Header & Sidebar -->
<?php include '../includes/header.php'; ?>
<?php $page = "attendance"; include '../includes/sidebar.php'; ?>

<div id="content">
    <div id="content-header">
        <div id="breadcrumb">
            <a href="index.php" title="Go to Home" class="tip-bottom"><i class="icon-home"></i> Home</a>
            <a href="attendance.php" class="current">Manage Attendance</a>
        </div>
        <h1 class="text-center">Attendance List <i class="icon icon-calendar"></i></h1>
    </div>
    <div class="container-fluid">
        <div class="row-fluid">
            <div class="span12">
                <div class='widget-box'>
                    <div class='widget-title'>
                        <span class='icon'><i class='icon-th'></i></span>
                        <h5>Attendance Table</h5>
                    </div>
                    <div class='widget-content nopadding'>
                        <table class='table table-bordered'>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Fullname</th>
                                    <th>Contact Number</th>
                                    <th>Chosen Service</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            $qry = "SELECT * FROM members WHERE status = 'Active'";
                            $result = mysqli_query($con, $qry);
                            $cnt = 1;

                            if (mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    $user_id = isset($row['id']) ? $row['id'] : ''; // Ensure user_id exists

                                    // Check if user already checked in today
                                    $attendance_qry = "SELECT * FROM attendance WHERE curr_date = '$todays_date' AND user_id = '$user_id'";
                                    $attendance_res = mysqli_query($con, $attendance_qry);
                                    $is_checked_in = mysqli_num_rows($attendance_res) > 0;
                                    $attendance_row = $is_checked_in ? mysqli_fetch_assoc($attendance_res) : null;
                                    ?>
                                    <tr>
                                        <td class='text-center'><?php echo $cnt++; ?></td>
                                        <td class='text-center'><?php echo htmlspecialchars($row['fullname']); ?></td>
                                        <td class='text-center'><?php echo htmlspecialchars($row['contact']); ?></td>
                                        <td class='text-center'><?php echo htmlspecialchars($row['services']); ?></td>
                                        <td class='text-center'>
                                            <?php if ($is_checked_in) { ?>
                                                <span class="label label-inverse">
                                                    <?php echo htmlspecialchars($attendance_row['curr_date']) . ' ' . htmlspecialchars($attendance_row['curr_time']); ?>
                                                </span>
                                                <br>
                                                <a href='actions/delete-attendance.php?id=<?php echo $user_id; ?>'>
                                                    <button class='btn btn-danger'>Check Out <i class='icon icon-time'></i></button>
                                                </a>
                                            <?php } else { ?>
                                                <a href='actions/check-attendance.php?id=<?php echo $user_id; ?>'>
                                                    <button class='btn btn-info'>Check In <i class='icon icon-map-marker'></i></button>
                                                </a>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                echo "<tr><td colspan='5' class='text-center'>No active members found.</td></tr>";
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

<!-- Footer -->
<div class="row-fluid">
    <div id="footer" class="span12"><?php echo date("Y"); ?> &copy; Developed By Bishnu Khadka</div>
</div>

<style>
    #footer { color: white; }
</style>

</body>
</html>
