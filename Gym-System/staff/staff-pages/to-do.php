<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('location:../index.php');
}

// Include database connection
include '../../dbcon.php';

// Function to fetch members
function getMembers($conn) {
    $query = "SELECT user_id, fullname FROM members WHERE status = 'Active'";
    return $conn->query($query);
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['task_desc']) && isset($_POST['member_id'])) {
        $task_desc = $_POST['task_desc'];
        $task_status = $_POST['task_status'];
        $member_id = $_POST['member_id']; // This will be stored in user_id column
        
        // Update query to explicitly SET the id column to NULL to allow auto-increment
        $query = "INSERT INTO todo (id, task_desc, task_status, user_id) 
                 VALUES (NULL, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        
        // Check if prepare was successful
        if ($stmt === false) {
            $error_message = "Prepare failed: " . $conn->error;
        } else {
            // Only bind the parameters that exist in the table
            $stmt->bind_param("ssi", $task_desc, $task_status, $member_id);
            
            if ($stmt->execute()) {
                $_SESSION['success_msg'] = "Task added successfully!";
                header('location: member-tasks.php');
                exit();
            } else {
                $error_message = "Error adding task: " . $stmt->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Gym System - Member Tasks</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="../css/bootstrap.min.css" />
    <link rel="stylesheet" href="../css/matrix-style.css" />
    <link rel="stylesheet" href="../css/matrix-media.css" />
    <link href="../font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link rel="stylesheet" href="../css/jquery.gritter.css" />
    <style>
        .task-form {
            padding: 20px;
            background: #fff;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .member-select {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
        }
        .task-description {
            width: 100%;
            min-height: 100px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<!--Header-part-->
<div id="header">
    <h1><a href="dashboard.php">Perfect Gym System</a></h1>
</div>

<?php include '../includes/header.php'?>
<?php $page="todo"; include '../includes/sidebar.php'?>

<div id="content">
    <div id="content-header">
        <div id="breadcrumb">
            <a href="index.php" class="tip-bottom"><i class="icon-home"></i> Home</a>
            <a href="#" class="current">Member Tasks</a>
        </div>
    </div>

    <div class="container-fluid">
        <?php if (isset($_SESSION['success_msg'])): ?>
            <div class="alert alert-success">
                <?php 
                    echo $_SESSION['success_msg'];
                    unset($_SESSION['success_msg']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="row-fluid">
            <div class="span12">
                <div class="widget-box">
                    <div class="widget-title">
                        <span class="icon"><i class="icon-tasks"></i></span>
                        <h5>Add New Task</h5>
                    </div>
                    
                    <div class="widget-content">
                        <form method="POST" class="task-form">
                            <div class="control-group">
                                <label class="control-label">Select Member:</label>
                                <div class="controls">
                                    <select name="member_id" class="member-select" required>
                                        <option value="">Select a Member</option>
                                        <?php
                                        $members = getMembers($conn);
                                        while ($member = $members->fetch_assoc()):
                                        ?>
                                            <option value="<?php echo $member['user_id']; ?>">
                                                <?php echo $member['fullname']; ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="control-group">
                                <label class="control-label">Task Description:</label>
                                <div class="controls">
                                    <textarea name="task_desc" class="task-description" required 
                                              placeholder="Enter task description..."></textarea>
                                </div>
                            </div>

                            <div class="control-group">
                                <label class="control-label">Status:</label>
                                <div class="controls">
                                    <select name="task_status" required>
                                        <option value="Pending">Pending</option>
                                        <option value="In Progress">In Progress</option>
                                        <option value="Completed">Completed</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">Add Task</button>
                                <button type="reset" class="btn">Clear</button>
                            </div>
                        </form>
                    </div>
                </div>

               
                            

<div class="row-fluid">
    <div id="footer" class="span12"> <?php echo date("Y"); ?> &copy; Developed By Bishnu Khadka</div>
</div>

<script src="../js/jquery.min.js"></script>
<script src="../js/jquery.ui.custom.js"></script>
<script src="../js/bootstrap.min.js"></script>
<script src="../js/matrix.js"></script>
</body>
</html>