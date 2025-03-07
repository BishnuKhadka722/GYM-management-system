<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header('location:../index.php');	
}

// Handle form submission for scheduling a reminder
if(isset($_POST['schedule_reminder'])) {
    $user_id = $_POST['user_id'];
    $reminder_date = $_POST['reminder_date'];
    $reminder_time = $_POST['reminder_time'];
    $reminder_message = $_POST['reminder_message'];
    
    // Combine date and time into a timestamp
    $reminder_datetime = date('Y-m-d H:i:s', strtotime("$reminder_date $reminder_time"));
    
    include '../../dbcon.php';
    
    // Update the members table with reminder information
    $qry = "UPDATE members SET 
            reminder = '1', 
            reminder_datetime = '$reminder_datetime', 
            reminder_message = '" . mysqli_real_escape_string($conn, $reminder_message) . "' 
            WHERE user_id = $user_id";
            
    $result = mysqli_query($conn, $qry);
    
    if($result){
        echo '<script>alert("Reminder scheduled successfully for ' . $reminder_datetime . '!")</script>';
        echo '<script>window.location.href = "payment.php";</script>';
    } else {
        echo '<script>alert("Error scheduling reminder: ' . mysqli_error($conn) . '")</script>';
        echo '<script>window.location.href = "payment.php";</script>';
    }
} 
// Handle immediate reminder sending (original functionality)
else if(isset($_GET['id'])) {
    $id = $_GET['id'];
    
    include '../../dbcon.php';
    
    // Show the reminder scheduling form
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <title>Schedule Reminder</title>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <link rel="stylesheet" href="../css/bootstrap.min.css" />
        <link rel="stylesheet" href="../css/bootstrap-responsive.min.css" />
        <link rel="stylesheet" href="../css/matrix-style.css" />
    </head>
    <body>
        <div class="container-fluid">
            <div class="row-fluid">
                <div class="span12">
                    <div class="widget-box">
                        <div class="widget-title">
                            <h5>Schedule Reminder</h5>
                        </div>
                        <div class="widget-content">
                            <form action="sendReminder.php" method="post" class="form-horizontal">
                                <input type="hidden" name="user_id" value="<?php echo $id; ?>">
                                
                                <div class="control-group">
                                    <label class="control-label">Reminder Date:</label>
                                    <div class="controls">
                                        <input type="date" name="reminder_date" required>
                                    </div>
                                </div>
                                
                                <div class="control-group">
                                    <label class="control-label">Reminder Time:</label>
                                    <div class="controls">
                                        <input type="time" name="reminder_time" required>
                                    </div>
                                </div>
                                
                                <div class="control-group">
                                    <label class="control-label">Reminder Message:</label>
                                    <div class="controls">
                                        <textarea name="reminder_message" rows="4" cols="50">This is to notify you that your current membership program is going to expire soon. Please clear up your payments before your due dates. IT IS IMPORTANT THAT YOU CLEAR YOUR DUES ON TIME IN ORDER TO AVOID SERVICE DISRUPTIONS.</textarea>
                                    </div>
                                </div>
                                
                                <div class="form-actions">
                                    <button type="submit" name="schedule_reminder" class="btn btn-primary">Schedule Reminder</button>
                                    <a href="payment.php" class="btn">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <script src="../js/jquery.min.js"></script>
        <script src="../js/bootstrap.min.js"></script>
    </body>
    </html>
    <?php
    exit;
}
?>
