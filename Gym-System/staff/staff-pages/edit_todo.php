<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('location:../index.php');
    exit();
}

// Include database connection
include '../../dbcon.php';

// Check if task ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_msg'] = "Invalid task ID!";
    header("Location: member-tasks.php");
    exit();
}

$task_id = $_GET['id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task_desc = $_POST['task_desc'];
    $task_status = $_POST['task_status'];
    
    $query = "UPDATE todo SET task_desc = ?, task_status = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }
    
    $stmt->bind_param("ssi", $task_desc, $task_status, $task_id);
    
    if ($stmt->execute()) {
        $_SESSION['success_msg'] = "Task updated successfully!";
        header("Location: member-tasks.php");
        exit();
    } else {
        $_SESSION['error_msg'] = "Error updating task: " . $stmt->error;
    }
    
    $stmt->close();
}

// Fetch task details
$query = "SELECT * FROM todo WHERE id = ?";
$stmt = $conn->prepare($query);

if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}

$stmt->bind_param("i", $task_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error_msg'] = "Task not found!";
    header("Location: member-tasks.php");
    exit();
}

$task = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Gym System - Edit Task</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="../css/bootstrap.min.css" />
    <link rel="stylesheet" href="../css/matrix-style.css" />
    <link rel="stylesheet" href="../css/matrix-media.css" />
    <link href="../font-awesome/css/font-awesome.css" rel="stylesheet" />
</head>
<body>

<div id="header">
    <h1><a href="dashboard.php">Perfect Gym System</a></h1>
</div>

<?php include '../includes/header.php'; ?>
<?php $page="todo"; include '../includes/sidebar.php'; ?>

<div id="content">
    <div id="content-header">
        <div id="breadcrumb">
            <a href="index.php" class="tip-bottom"><i class="icon-home"></i> Home</a>
            <a href="member-tasks.php">Member Tasks</a>
            <a href="#" class="current">Edit Task</a>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row-fluid">
            <div class="span12">
                <div class="widget-box">
                    <div class="widget-title">
                        <span class="icon"><i class="icon-pencil"></i></span>
                        <h5>Edit Task</h5>
                    </div>
                    <div class="widget-content nopadding">
                        <form method="post" action="" class="form-horizontal">
                            <div class="control-group">
                                <label class="control-label">Task Description :</label>
                                <div class="controls">
                                    <textarea name="task_desc" class="span11" required><?php echo htmlspecialchars($task['task_desc']); ?></textarea>
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label">Status :</label>
                                <div class="controls">
                                    <select name="task_status" class="span11">
                                        <option value="Pending" <?php if ($task['task_status'] === 'Pending') echo 'selected'; ?>>Pending</option>
                                        <option value="In Progress" <?php if ($task['task_status'] === 'In Progress') echo 'selected'; ?>>In Progress</option>
                                        <option value="Completed" <?php if ($task['task_status'] === 'Completed') echo 'selected'; ?>>Completed</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-success">Update Task</button>
                                <a href="member-tasks.php" class="btn">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row-fluid">
    <div id="footer" class="span12"> <?php echo date("Y");?> &copy; Developed By Bishnu Khadka</div>
</div>

<script src="../js/jquery.min.js"></script>
<script src="../js/bootstrap.min.js"></script>
<script src="../js/matrix.js"></script>
</body>
</html>