<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header('location:../index.php');	
    exit();
}

include '../../dbcon.php'; // Updated path to database connection file

// Check if this is a return from Khalti with pidx parameter
if (isset($_GET['pidx'])) {
    $pidx = $_GET['pidx'];
    
    // Set up curl request to verify payment status
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://a.khalti.com/api/v2/epayment/lookup/',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode(['pidx' => $pidx]),
        CURLOPT_HTTPHEADER => array(
            'Authorization: key live_secret_key_68791341fdd94846a146f0457ff7b455', // Replace with your actual Khalti key
            'Content-Type: application/json',
        ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
        // Handle curl error
        $_SESSION['transaction_msg'] = '<script>
            Swal.fire({
                icon: "error",
                title: "API Connection Error",
                text: "Error connecting to payment gateway: ' . $err . '",
                showConfirmButton: true
            });
            </script>';
        header("Location: checkout.php");
        exit();
    }

    if ($response) {
        $responseArray = json_decode($response, true);
        
        // Check if payment_processing session data exists
        if (!isset($_SESSION['payment_processing'])) {
            $_SESSION['transaction_msg'] = '<script>
                Swal.fire({
                    icon: "error",
                    title: "Session expired",
                    text: "Your payment session has expired. Please try again.",
                    showConfirmButton: true
                });
                </script>';
            header("Location: checkout.php");
            exit();
        }
        
        // Get payment data from session
        $user_id = $_SESSION['payment_processing']['user_id'];
        $plan = $_SESSION['payment_processing']['plan'];
        $status = $_SESSION['payment_processing']['status'];
        $amount = $_SESSION['payment_processing']['amount'];
        
        // Check payment status
        if (isset($responseArray['status'])) {
            switch ($responseArray['status']) {
                case 'Completed':
                    // Update database with payment information
                    $paid_date = date('Y-m-d'); // Current date
                    
                    // Calculate expiry date based on plan (in months)
                    if ($plan > 0) {
                        $expiry_date = date('Y-m-d', strtotime("+$plan months"));
                    } else {
                        $expiry_date = NULL; // No expiry for "None-Expired" option
                    }
                    
                    // Update members table
                    $updateQuery = "UPDATE members SET 
                                    amount = '$amount',
                                    paid_date = '$paid_date',
                                    expiry_date = " . ($expiry_date ? "'$expiry_date'" : "NULL") . ",
                                    status = '$status'
                                    WHERE user_id = '$user_id'";
                                    
                    $result = mysqli_query($conn, $updateQuery);
                    
                    if ($result) {
                        // Insert payment record in a payment_history table
                        $transaction_id = $responseArray['transaction_id'] ?? $pidx;
                        $payment_method = 'Khalti';
                        $customer_email = $_SESSION['payment_processing']['customer_email'];
                        $customer_phone = $_SESSION['payment_processing']['customer_phone'];
                        
                        // Create payment_history table if it doesn't exist
                        mysqli_query($conn, "CREATE TABLE IF NOT EXISTS payment_history (
                            id INT(11) AUTO_INCREMENT PRIMARY KEY,
                            user_id INT(11) NOT NULL,
                            amount DECIMAL(10,2) NOT NULL,
                            transaction_id VARCHAR(100) NOT NULL,
                            payment_method VARCHAR(50) NOT NULL,
                            customer_email VARCHAR(100),
                            customer_phone VARCHAR(20),
                            payment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
                            status VARCHAR(20) DEFAULT 'Completed'
                        )");
                        
                        // Insert payment record
                        $insertPayment = "INSERT INTO payment_history 
                                          (user_id, amount, transaction_id, payment_method, customer_email, customer_phone) 
                                          VALUES 
                                          ('$user_id', '$amount', '$transaction_id', '$payment_method', '$customer_email', '$customer_phone')";
                                          
                        mysqli_query($conn, $insertPayment);
                        
                        $_SESSION['transaction_msg'] = '<script>
                            Swal.fire({
                                icon: "success",
                                title: "Payment Successful",
                                text: "Your membership has been updated successfully!",
                                showConfirmButton: true
                            });
                            </script>';
                        
                        // Clean up session data
                        unset($_SESSION['payment_data']);
                        unset($_SESSION['payment_processing']);
                        
                        // Redirect to success page
                        header("Location: payment-success.php");
                        exit();
                    } else {
                        $_SESSION['transaction_msg'] = '<script>
                            Swal.fire({
                                icon: "error",
                                title: "Database Error",
                                text: "Payment was successful but there was an error updating your membership: ' . mysqli_error($conn) . '",
                                showConfirmButton: true
                            });
                            </script>';
                        header("Location: checkout.php");
                        exit();
                    }
                    break;
                    
                case 'Expired':
                case 'User canceled':
                    $_SESSION['transaction_msg'] = '<script>
                        Swal.fire({
                            icon: "warning",
                            title: "Payment ' . $responseArray['status'] . '",
                            text: "Your payment was not completed.",
                            showConfirmButton: true
                        });
                        </script>';
                    header("Location: checkout.php");
                    exit();
                    break;
                    
                default:
                    $_SESSION['transaction_msg'] = '<script>
                        Swal.fire({
                            icon: "error",
                            title: "Payment Failed",
                            text: "Status: ' . $responseArray['status'] . '. Please try again.",
                            showConfirmButton: true
                        });
                        </script>';
                    header("Location: checkout.php");
                    exit();
                    break;
            }
        } else {
            // Handle case where status is not present in response
            $_SESSION['transaction_msg'] = '<script>
                Swal.fire({
                    icon: "error",
                    title: "Invalid Response",
                    text: "Could not determine payment status. Please contact support.",
                    showConfirmButton: true
                });
                </script>';
            header("Location: checkout.php");
            exit();
        }
    } else {
        // Handle empty response
        $_SESSION['transaction_msg'] = '<script>
            Swal.fire({
                icon: "error",
                title: "Communication Error",
                text: "Could not verify payment status. Please contact support.",
                showConfirmButton: true
            });
            </script>';
        header("Location: checkout.php");
        exit();
    }
} else {
    // No pidx parameter, redirect to checkout
    $_SESSION['transaction_msg'] = '<script>
        Swal.fire({
            icon: "error",
            title: "Invalid Request",
            text: "Missing payment reference. Please try again.",
            showConfirmButton: true
        });
        </script>';
    header("Location: checkout.php");
    exit();
}