<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('location:../index.php');
    exit;
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
    <h1><a href="index.php">Perfect Gym System</a></h1>
</div>

<!--top-Header-menu-->
<?php include '../includes/topheader.php'; ?>

<!--sidebar-menu-->
<?php $page = "todo"; include '../includes/sidebar.php'; ?>

<!--main-container-part-->
<div id="content">
    <!--breadcrumbs-->
    <div id="content-header">
        <div id="breadcrumb">
            <a href="index.php" title="You're right here" class="tip-bottom">
                <i class="icon-home"></i> Home
            </a>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row-fluid">
            <form role="form" action="index.php" method="POST">
                <?php
                // Include the database connection file
                $dbconPath = __DIR__ . '/../../dbcon.php';
                if (file_exists($dbconPath)) {
                    include $dbconPath;
                } else {
                    echo "<div class='alert alert-danger'>Database connection file not found. Please contact the administrator.</div>";
                    exit;
                }

                // Ensure the database connection is established
                if (!isset($con) || !$con) {
                    echo "<div class='alert alert-danger'>Database connection failed. Please check your connection settings.</div>";
                    exit;
                }

                // Include session file
                include 'session.php';

                if (isset($_POST['task_desc'])) {
                    $task_status = mysqli_real_escape_string($con, $_POST["task_status"]);
                    $task_desc = mysqli_real_escape_string($con, $_POST["task_desc"]);
                    $user_id = $_SESSION['user_id'];

                    // Query to insert the task
                    $qry = "INSERT INTO todo (task_status, task_desc, user_id) VALUES ('$task_status', '$task_desc', '$user_id')";
                    $result = mysqli_query($con, $qry);

                    if (!$result) {
                        echo "<div class='alert alert-danger'>Error occurred while entering your details. Please try again.</div>";
                    } else {
                        echo "<div class='alert alert-success'>Task successfully added!</div>";
                    }
                } else {
                    echo "<h3>You are not authorized to redirect to this page. Go back to <a href='index.php'>Dashboard</a>.</h3>";
                }
                ?>
            </form>
        </div><!-- End of row-fluid -->
    </div><!-- End of container-fluid -->
</div><!-- End of content-ID -->
</div>

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

<!--end-Footer-part-->


</body>
</html>
