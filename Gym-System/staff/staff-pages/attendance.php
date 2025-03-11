<?php
session_start();
// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('location:../index.php');
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

<?php include '../includes/header.php'; ?>
<?php $page = "attendance"; include '../includes/sidebar.php'; ?>

<div id="content">
    <div id="content-header">
        <div id="breadcrumb"> 
            <a href="index.php" title="Go to Home" class="tip-bottom">
                <i class="icon-home"></i> Home
            </a> 
            <a href="attendance.php" class="current">Manage Attendance</a> 
        </div>
        <h1 class="text-center">Attendance List <i class="icon icon-calendar"></i></h1>
    </div>
    <div class="container-fluid">
        <div class="row-fluid">
            <div class="span12">
                <div class='widget-box'>
                    <div class='widget-title'>
                        <span class='icon'> <i class='icon-th'></i> </span>
                        <h5>Today's Attendance</h5>
                    </div>
                    <div class='widget-content nopadding'>
                        <table class='table table-bordered'>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Fullname</th>
                                    <th>Contact Number</th>
                                    <th>Service</th>
                                    <th>Last Check-in</th>
                                    <th>Last Check-out</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                include "../../dbcon.php";
                                date_default_timezone_set('Asia/Kathmandu');
                                $todays_date = date('Y-m-d');
                                $qry = "SELECT * FROM members WHERE status = 'Active'";
                                $result = mysqli_query($conn, $qry);
                                $cnt = 1;
                                while ($row = mysqli_fetch_array($result)) {
                                    $user_id = isset($row['user_id']) ? $row['user_id'] : null;
                                    
                                    // Get the latest attendance record for today
                                    $attendance_data = [
                                        'has_record' => false,
                                        'check_in_time' => '',
                                        'check_out_time' => '',
                                        'status' => 0,
                                        'attendance_id' => 0,
                                        'checked_in_count' => 0
                                    ];
                                    
                                    if ($user_id) {
                                        // Get count of check-ins for today
                                        $count_query = "SELECT COUNT(*) as checkin_count FROM attendance WHERE user_id = '$user_id' AND curr_date = '$todays_date'";
                                        $count_result = mysqli_query($conn, $count_query);
                                        $count_row = mysqli_fetch_array($count_result);
                                        $attendance_data['checked_in_count'] = $count_row['checkin_count'];
                                        
                                        // Get latest attendance record
                                        $qry_attendance = "SELECT * FROM attendance 
                                                          WHERE user_id = '$user_id' AND curr_date = '$todays_date' 
                                                          ORDER BY check_in_time DESC LIMIT 1";
                                        $res = mysqli_query($conn, $qry_attendance);
                                        
                                        if ($res && mysqli_num_rows($res) > 0) {
                                            $attendance_row = mysqli_fetch_array($res);
                                            $attendance_data = [
                                                'has_record' => true,
                                                'check_in_time' => $attendance_row['check_in_time'],
                                                'check_out_time' => $attendance_row['check_out_time'],
                                                'status' => $attendance_row['status'],
                                                'attendance_id' => $attendance_row['id'],
                                                'checked_in_count' => $attendance_data['checked_in_count']
                                            ];
                                        }
                                    }
                                    ?>
                                    <tr>
                                        <td class='text-center'><?php echo $cnt++; ?></td>
                                        <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                                        <td class='text-center'><?php echo htmlspecialchars($row['contact']); ?></td>
                                        <td><?php echo htmlspecialchars($row['services']); ?></td>
                                        <td class='text-center'>
                                            <?php if ($attendance_data['has_record']) {
                                                echo date('h:i A', strtotime($attendance_data['check_in_time']));
                                            } else {
                                                echo '-';
                                            } ?>
                                        </td>
                                        <td class='text-center'>
                                            <?php if ($attendance_data['has_record'] && !empty($attendance_data['check_out_time'])) {
                                                echo date('h:i A', strtotime($attendance_data['check_out_time']));
                                            } else {
                                                echo '-';
                                            } ?>
                                        </td>
                                        <td class='text-center'>
                                            <?php if ($attendance_data['has_record']) {
                                                if ($attendance_data['status'] == 1) { ?>
                                                    <span class="label label-warning">Checked In</span>
                                                <?php } else { ?>
                                                    <span class="label label-success">Checked Out</span>
                                                <?php }
                                                
                                                if ($attendance_data['checked_in_count'] > 1) { ?>
                                                    <br><span class="label label-info">
                                                        <?php echo $attendance_data['checked_in_count']; ?> entries today
                                                    </span>
                                                <?php }
                                            } else { ?>
                                                <span class="label">Not Visited</span>
                                            <?php } ?>
                                        </td>
                                        <td class='text-center'>
                                            <?php if ($attendance_data['has_record'] && $attendance_data['status'] == 1) { ?>
                                                <a href='actions/delete-attendance.php?id=<?php echo $user_id; ?>&attendance_id=<?php echo $attendance_data['attendance_id']; ?>'>
                                                    <button class='btn btn-danger'>Check Out <i class='icon icon-time'></i></button>
                                                </a>
                                            <?php } ?>
                                            
                                            <a href='actions/check-attendance.php?id=<?php echo $user_id; ?>'>
                                                <button class='btn btn-info'>
                                                    <?php echo ($attendance_data['has_record'] && $attendance_data['status'] == 1) ? 'New Check In' : 'Check In'; ?> 
                                                    <i class='icon icon-map-marker'></i>
                                                </button>
                                            </a>
                                            
                                            <a href='attendance-history.php?id=<?php echo $user_id; ?>'>
                                                <button class='btn btn-primary'>History <i class='icon icon-list'></i></button>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php
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

<div class="row-fluid">
    <div id="footer" class="span12"> <?php echo date("Y"); ?> &copy; Developed By Bishnu Khadka</div>
</div>

<script src="../js/jquery.min.js"></script>
<script src="../js/bootstrap.min.js"></script>
<script src="../js/matrix.js"></script>

</body>
</html>
