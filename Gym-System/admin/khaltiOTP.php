<?php
session_start();
// Check if user is logged in
if(!isset($_SESSION['user_id'])){
    header('location:../index.php');
    exit;
}

include '../dbcon.php';

// Check if token and amount are provided in URL
if(!isset($_GET['token']) || !isset($_GET['amount'])) {
    header('location:payment.php');
    exit;
}

$token = $_GET['token'];
$amount = $_GET['amount'];

// Get payment details from session
if(!isset($_SESSION['payment_details'])) {
    header('location:payment.php');
    exit;
}

$payment_details = $_SESSION['payment_details'];
$id = $payment_details['id'];
$fullname = $payment_details['fullname'];
$services = $payment_details['services'];
$amount_per_month = $payment_details['amount'];
$plan = $payment_details['plan'];
$status = $payment_details['status'];
$paid_date = $payment_details['paid_date'];

// Verify the transaction with Khalti server
$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://khalti.com/api/v2/payment/verify/',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => json_encode(array(
        'token' => $token,
        'amount' => $amount
    )),
    CURLOPT_HTTPHEADER => array(
        'Authorization: Key test_secret_key_70182d69cb764ffe9b4ebadc58d76cce',
        'Content-Type: application/json'
    ),
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
    $payment_status = "failed";
    $payment_message = "Payment verification failed: " . $err;
} else {
    $response_data = json_decode($response);
    
    if(isset($response_data->idx)) {
        $payment_status = "success";
        $payment_message = "Payment successful!";
        
        // Calculate expiry date based on plan
        if($plan > 0) {
            $expiry_date = date('Y-m-d', strtotime("+$plan months"));
        } else {
            $expiry_date = "0000-00-00"; // For expired plans
        }
        
        // Update membership details in database
        $total_amount = $amount_per_month * $plan;
        $transaction_id = $response_data->idx;
        
        $update_query = "UPDATE members SET 
                        plan='$plan', 
                        status='$status', 
                        amount='$amount_per_month',
                        paid_date='$paid_date',
                        expiry_date='$expiry_date'
                        WHERE user_id='$id'";
        
        $result = mysqli_query($conn, $update_query);
        
        // Insert payment record
        $payment_insert = "INSERT INTO payments (user_id, fullname, services, amount, plan, status, payment_date, payment_method, transaction_id) 
                          VALUES ('$id', '$fullname', '$services', '$total_amount', '$plan', 'Paid', '$paid_date', 'Khalti', '$transaction_id')";
        
        mysqli_query($conn, $payment_insert);
        
        // Clear payment details from session
        unset($_SESSION['payment_details']);
    } else {
        $payment_status = "failed";
        $payment_message = "Payment verification failed: Invalid response from Khalti";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Gym System - Payment Status</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="../css/bootstrap.min.css" />
    <link rel="stylesheet" href="../css/bootstrap-responsive.min.css" />
    <link rel="stylesheet" href="../css/matrix-style.css" />
    <link rel="stylesheet" href="../css/matrix-media.css" />
    <link href="../font-awesome/css/fontawesome.css" rel="stylesheet" />
    <link href="../font-awesome/css/all.css" rel="stylesheet" />
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,700,800' rel='stylesheet' type='text/css'>
</head>
<body>

<!--Header-part-->
<div id="header">
  <h1><a href="">Perfect Gym Admin</a></h1>
</div>

<!--top-Header-menu-->
<?php include 'includes/topheader.php'?>

<!--sidebar-menu-->
<?php $page='payment'; include 'includes/sidebar.php'?>

<div id="content">
  <div id="content-header">
    <div id="breadcrumb"> 
        <a href="index.php" title="Go to Home" class="tip-bottom"><i class="fas fa-home"></i> Home</a> 
        <a href="payment.php">Payments</a> 
        <a href="#" class="current">Payment Status</a> 
    </div>
    <h1>Payment Status</h1>
  </div>
  
  <div class="container-fluid">
    <div class="row-fluid">
      <div class="span12">
        <div class="widget-box">
          <div class="widget-title"> 
            <span class="icon"> <i class="fas fa-check-circle"></i> </span>
            <h5>Payment Result</h5>
          </div>
          <div class="widget-content">
            <div class="row-fluid">
              <div class="span12 text-center" style="padding: 30px;">
                <?php if($payment_status == "success"): ?>
                    <div class="alert alert-success">
                        <h2><i class="fas fa-check-circle"></i> Payment Successful!</h2>
                        <p>Your payment has been successfully processed and membership has been updated.</p>
                    </div>
                    
                    <h4>Transaction Details</h4>
                    <table class="table table-bordered table-striped" style="max-width: 600px; margin: 0 auto;">
                        <tr>
                            <td><strong>Member:</strong></td>
                            <td><?php echo $fullname; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Service:</strong></td>
                            <td><?php echo $services; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Plan:</strong></td>
                            <td><?php echo $plan; ?> Month(s)</td>
                        </tr>
                        <tr>
                            <td><strong>Total Amount:</strong></td>
                            <td>NPR <?php echo $amount / 100; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Transaction ID:</strong></td>
                            <td><?php echo $response_data->idx; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Payment Date:</strong></td>
                            <td><?php echo date('Y-m-d H:i:s'); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Status:</strong></td>
                            <td><span class="label label-success">PAID</span></td>
                        </tr>
                    </table>
                    
                    <div style="margin-top: 30px;">
                        <a href="payment.php" class="btn btn-primary btn-large">Back to Payments</a>
                        <a href="index.php" class="btn btn-info btn-large">Go to Dashboard</a>
                    </div>
                <?php else: ?>
                    <div class="alert alert-error">
                        <h2><i class="fas fa-times-circle"></i> Payment Failed</h2>
                        <p><?php echo $payment_message; ?></p>
                    </div>
                    
                    <div style="margin-top: 30px;">
                        <a href="user-payment.php?id=<?php echo $id; ?>" class="btn btn-danger btn-large">Try Again</a>
                        <a href="payment.php" class="btn btn-primary btn-large">Back to Payments</a>
                    </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!--Footer-part-->
<div class="row-fluid">
  <div id="footer" class="span12"><?php echo date("Y");?> &copy; Developed By Bishnu Khadka</div>
</div>

<script src="../js/jquery.min.js"></script> 
<script src="../js/bootstrap.min.js"></script>
</body>
</html>