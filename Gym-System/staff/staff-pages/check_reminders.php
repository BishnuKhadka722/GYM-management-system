<?php
// This file should be set up as a cron job to run every hour or as frequently as needed
include '../../dbcon.php';

// Get current date and time
$current_datetime = date('Y-m-d H:i:s');

// Find all pending reminders that are due
$query = "SELECT m.user_id, m.name, m.email, m.phone 
          FROM members m 
          WHERE m.reminder = '1' 
          AND m.reminder_status = 'pending' 
          AND m.reminder_scheduled <= '$current_datetime'";

$result = mysqli_query($conn, $query);

while($row = mysqli_fetch_assoc($result)) {
    $user_id = $row['user_id'];
    $customer_name = $row['name'];
    $customer_email = $row['email'];
    $customer_phone = $row['phone'];
    
    // Send email notification
    $to = $customer_email;
    $subject = "Payment Reminder";
    $message = "Dear $customer_name,\n\nThis is a reminder about your pending payment. Please complete your payment as soon as possible.\n\nThank you.";
    $headers = "From: your-email@example.com";
    
    mail($to, $subject, $message, $headers);
    
    // For SMS, you would need to integrate with an SMS gateway service
    // sendSMS($customer_phone, $message);
    
    // Update the reminder status to sent
    $update_query = "UPDATE members SET 
                    reminder_status = 'sent',
                    reminder_sent_at = '$current_datetime'
                    WHERE user_id = $user_id";
    
    mysqli_query($conn, $update_query);
}

// Close the database connection
mysqli_close($conn);
?>