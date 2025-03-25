<?php
session_start();
// Check if user is logged in
if(!isset($_SESSION['user_id'])){
    header('location:../index.php');
    exit;
}

include '../dbcon.php';

// Get payment details from POST
$id = $_POST['id'];
$fullname = $_POST['fullname'];
$services = $_POST['services'];
$amount = $_POST['amount'];
$plan = $_POST['plan'];
$status = $_POST['status'];
$paid_date = date('Y-m-d');

// Store payment details in session for later use
$_SESSION['payment_details'] = [
    'id' => $id,
    'fullname' => $fullname,
    'services' => $services,
    'amount' => $amount,
    'plan' => $plan,
    'status' => $status,
    'paid_date' => $paid_date
];

// Calculate total amount based on plan (months)
$total_amount = $amount * $plan;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Gym System - Payment Checkout</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="../css/bootstrap.min.css" />
    <link rel="stylesheet" href="../css/bootstrap-responsive.min.css" />
    <link rel="stylesheet" href="../css/matrix-style.css" />
    <link rel="stylesheet" href="../css/matrix-media.css" />
    <link href="../font-awesome/css/fontawesome.css" rel="stylesheet" />
    <link href="../font-awesome/css/all.css" rel="stylesheet" />
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,700,800' rel='stylesheet' type='text/css'>
    <script src="https://khalti.s3.ap-south-1.amazonaws.com/KPG/dist/2020.12.22.0.0.0/khalti-checkout.iffe.js"></script>
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
        <a href="#" class="current">Checkout</a> 
    </div>
    <h1>Payment Checkout</h1>
  </div>
  
  <div class="container-fluid">
    <div class="row-fluid">
      <div class="span12">
        <div class="widget-box">
          <div class="widget-title"> <span class="icon"> <i class="fas fa-money"></i> </span>
            <h5>Khalti Payment</h5>
          </div>
          <div class="widget-content">
            <div class="row-fluid">
              <div class="span6">
                <table class="table table-bordered">
                  <tbody>
                    <tr>
                      <td><strong>Member Name:</strong></td>
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
                      <td><strong>Amount per Month:</strong></td>
                      <td>NPR <?php echo $amount; ?></td>
                    </tr>
                    <tr>
                      <td><strong>Total Amount:</strong></td>
                      <td>NPR <?php echo $total_amount; ?></td>
                    </tr>
                  </tbody>
                </table>
              </div>
              
              <div class="span6">
                <div class="text-center" style="padding: 20px;">
                  <h3>Pay with Khalti</h3>
                  <p>Click the button below to proceed with the payment</p>
                  <button id="payment-button" class="btn btn-primary btn-large">Pay with Khalti</button>
                </div>
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
<script>
    var config = {
        // replace this key with your public key
        "publicKey": "test_public_key_78fa5944680a4ff8a9dc59cde40da2de",
        "productIdentity": "membership<?php echo $id; ?>",
        "productName": "<?php echo $services; ?> Membership",
        "productUrl": "http://localhost/gym/admin/checkout.php",
        "paymentPreference": [
            "KHALTI",
            "EBANKING",
            "MOBILE_BANKING",
            "CONNECT_IPS",
            "SCT",
        ],
        "eventHandler": {
            onSuccess (payload) {
                // hit merchant api for initiating verification
                console.log(payload);
                window.location.href = "khaltiOTP.php?token=" + payload.token + "&amount=" + payload.amount;
            },
            onError (error) {
                console.log(error);
                alert("Payment failed. Please try again.");
            },
            onClose () {
                console.log('widget is closing');
            }
        }
    };

    var checkout = new KhaltiCheckout(config);
    var btn = document.getElementById("payment-button");
    btn.onclick = function () {
        // minimum amount in paisa, which is NPR 100
        checkout.show({amount: <?php echo $total_amount * 100; ?>});
    }
</script>
</body>
</html>