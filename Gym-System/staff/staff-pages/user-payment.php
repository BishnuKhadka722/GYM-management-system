<?php
session_start();
//the isset function to check username is already loged in and stored on the session
if (!isset($_SESSION['user_id'])) {
  header('location:../index.php');
  exit;
}

// Successful payment handling
$payment_success = false;
$success_message = "";
if(isset($_GET['payment']) && $_GET['payment'] == 'success') {
  $payment_success = true;
  $success_message = "Payment completed successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <title>Gym System</title>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="../css/bootstrap.min.css" />
  <link rel="stylesheet" href="../css/fullcalendar.css" />
  <link rel="stylesheet" href="../css/matrix-style.css" />
  <link rel="stylesheet" href="../css/matrix-media.css" />
  <link href="../font-awesome/css/font-awesome.css" rel="stylesheet" />
  <link rel="stylesheet" href="../css/jquery.gritter.css" />
  <style>
    #footer {
      color: white;
    }
    
    .khalticontainer {
      width: 300px;
      border: 2px solid #5C2D91;
      margin: 0 auto;
      padding: 8px;
      border-radius: 8px;
    }
    
    .khalti-input {
      display: block;
      width: 98%;
      padding: 8px;
      margin: 4px 2px;
      border: 1px solid #ccc;
      border-radius: 4px;
    }
    
    .khalti-button {
      display: block;
      background-color: #5C2D91;
      border: none;
      color: white;
      cursor: pointer;
      width: 98%;
      padding: 10px 8px;
      margin: 8px 2px;
      border-radius: 4px;
      font-weight: bold;
      transition: background-color 0.3s;
    }
    
    .khalti-button:hover {
      background-color: #4A2275;
    }
    
    .success-message {
      background-color: #dff0d8;
      border: 1px solid #d6e9c6;
      color: #3c763d;
      padding: 15px;
      margin-bottom: 20px;
      border-radius: 4px;
    }
    
    .payment-receipt {
      border: 1px solid #ddd;
      padding: 20px;
      margin-top: 20px;
      background-color: #f9f9f9;
      border-radius: 4px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    
    .payment-receipt h3 {
      color: #5C2D91;
      border-bottom: 1px solid #eee;
      padding-bottom: 10px;
      margin-bottom: 15px;
    }

    .loader {
      border: 5px solid #f3f3f3;
      border-radius: 50%;
      border-top: 5px solid #5C2D91;
      width: 30px;
      height: 30px;
      animation: spin 1s linear infinite;
      margin: 10px auto;
      display: none;
    }

    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    #processing-payment {
      display: none;
      text-align: center;
      padding: 15px;
      background-color: #f8f9fa;
      border-radius: 4px;
      margin-top: 15px;
    }

    .error-message {
      color: #d9534f;
      background-color: #f2dede;
      border: 1px solid #ebccd1;
      padding: 10px;
      border-radius: 4px;
      margin: 5px 0;
      font-size: 14px;
    }
    
    #payment-details {
      display: none;
      padding: 15px;
      background-color: #f8f9fa;
      border-radius: 4px;
      margin-top: 15px;
      border: 1px solid #ddd;
    }
    
    #confirm-payment-btn {
      margin-top: 10px;
    }
  </style>
</head>

<body>

  <!--Header-part-->
  <div id="header">
    <h1><a href="dashboard.html">Perfect Gym</a></h1>
  </div>
  <!--close-Header-part-->

  <!--top-Header-menu-->
  <?php include '../includes/header.php' ?>
  <!--close-top-Header-menu-->
  
  <!--sidebar-menu-->
  <?php $page = 'payment';
  include '../includes/sidebar.php' ?>
  <!--sidebar-menu-->

  <?php
  include '../../dbcon.php';
  $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
  if ($id <= 0) {
    echo "<script>alert('Invalid member ID'); window.location='payment.php';</script>";
    exit;
  }

  $qry = "SELECT * FROM members WHERE user_id=?";
  $stmt = mysqli_prepare($conn, $qry);
  mysqli_stmt_bind_param($stmt, "i", $id);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  
  if (mysqli_num_rows($result) == 0) {
    echo "<script>alert('Member not found'); window.location='payment.php';</script>";
    exit;
  }

  while ($row = mysqli_fetch_array($result)) {
    // Calculate the amount based on service
    $amount = 0;
    if ($row['services'] == 'Fitness') {
      $amount = 55;
    } elseif ($row['services'] == 'Sauna') {
      $amount = 35;
    } else {
      $amount = 40;
    }
    
    // Define Khalti variables
    $error_message = "";
    $khalti_public_key = "test_public_key_78040772c70c47309222c28578dc01772";
    $uniqueProductId = "GYM-" . $id;
    $uniqueUrl = "http://localhost/Gym-System/staff/staff-pages/";
    $uniqueProductName = $row['services'] . " Membership";
    $successRedirect = "http://localhost/Gym-System/staff/staff-pages/user-payment.php?id=" . $id . "&payment=success";
    $token = "";
    
    // Handle Khalti payment initiation
    if (isset($_POST["submit-payment"])) {
      // Check if mobile and mpin fields are set and not empty
      if (empty($_POST["mobile"]) || empty($_POST["mpin"])) {
        $error_message = "Please enter both mobile number and MPIN";
      } else {
        try {
          $mobile = $_POST["mobile"];
          $mpin = $_POST["mpin"];
          $price = (float) $amount;
          $khalti_amount = (float) $amount * 100;
          $plan = $_POST["plan"];
          $status = $_POST["status"];

          $curl = curl_init();
          curl_setopt_array(
            $curl,
            array(
              CURLOPT_URL => 'https://khalti.com/api/v2/payment/initiate/',
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => '',
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => 'POST',
              CURLOPT_POSTFIELDS => json_encode([
                "public_key" => $khalti_public_key,
                "mobile" => $mobile,
                "transaction_pin" => $mpin,
                "amount" => $khalti_amount,
                "product_identity" => $uniqueProductId,
                "product_name" => $uniqueProductName,
                "product_url" => $uniqueUrl
              ]),
              CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
              ),
            )
          );

          $response = curl_exec($curl);
          curl_close($curl);
          $parsed = json_decode($response, true);

          if (isset($parsed["token"])) {
            $token = $parsed["token"];
            
            // Display payment confirmation screen instead of auto-redirecting
            echo '
            <div id="payment-details">
              <h3>Confirm Your Payment</h3>
              <div class="row-fluid">
                <div class="span6">
                  <p><strong>Member Name:</strong> ' . htmlspecialchars($row['fullname']) . '</p>
                  <p><strong>Service:</strong> ' . htmlspecialchars($row['services']) . '</p>
                  <p><strong>Amount:</strong> $' . $amount . '</p>
                </div>
                <div class="span6">
                  <p><strong>Date:</strong> ' . date('Y-m-d') . '</p>
                  <p><strong>Payment Method:</strong> Khalti</p>
                  <p><strong>Plan:</strong> ' . ($plan == 1 ? "One Month" : ($plan == 3 ? "Three Month" : ($plan == 6 ? "Six Month" : ($plan == 12 ? "One Year" : "None-Expired")))) . '</p>
                </div>
              </div>
              <hr>
              <div class="text-center">
                <form id="confirm-payment-form" action="userpay.php" method="post">
                  <input type="hidden" name="token" value="' . $token . '">
                  <input type="hidden" name="mpin" value="' . $mpin . '">
                  <input type="hidden" name="user_id" value="' . $id . '">
                  <input type="hidden" name="amount" value="' . $amount . '">
                  <input type="hidden" name="plan" value="' . $plan . '">
                  <input type="hidden" name="status" value="' . $status . '">
                  <input type="hidden" name="fullname" value="' . htmlspecialchars($row['fullname']) . '">
                  <input type="hidden" name="services" value="' . htmlspecialchars($row['services']) . '">
                  <button type="submit" id="confirm-payment-btn" class="btn btn-success btn-large">Confirm and Complete Payment</button>
                  <button type="button" id="cancel-payment-btn" class="btn btn-danger">Cancel Payment</button>
                </form>
                <div id="processing-payment" style="display:none;">
                  <div class="loader"></div>
                  <p>Processing your payment. Please wait...</p>
                </div>
              </div>
            </div>
            <script>
              document.getElementById("payment-details").style.display = "block";
              
              //Handle confirm button click
              document.getElementById("confirm-payment-btn").addEventListener("click", function(e) {
                e.preventDefault();
                document.getElementById("confirm-payment-btn").style.display = "none";
                document.getElementById("cancel-payment-btn").style.display = "none";
                document.getElementById("processing-payment").style.display = "block";
                
                // Submit the form after showing processing message
                setTimeout(function() {
                  document.getElementById("confirm-payment-form").submit();
                }, 1000);
              });
              
              // Handle cancel button click
              document.getElementById("cancel-payment-btn").addEventListener("click", function() {
                document.getElementById("payment-details").style.display = "none";
              });
            </script
            ';
          } else {
            $error_message = isset($parsed["detail"]) ? $parsed["detail"] : "Incorrect mobile or mpin";
          }
        } catch (Exception $e) {
          $error_message = "An error occurred: " . $e->getMessage();
        }
      }
    }
  ?>

  <div id="content">
    <div id="content-header">
      <div id="breadcrumb"> <a href="index.php" title="Go to Home" class="tip-bottom"><i class="icon-home"></i> Home</a>
        <a href="payment.php">Payments</a> <a href="#" class="current">Invoice</a>
      </div>
      <h1>Payment Form</h1>
    </div>

    <div class="container-fluid" style="margin-top:-38px;">
      <?php if($payment_success) { ?>
      <div class="row-fluid">
        <div class="span12">
          <div class="success-message">
            <h4><i class="icon-check"></i> <?php echo $success_message; ?></h4>
          </div>
          
          <div class="widget-box">
            <div class="widget-title"> <span class="icon"> <i class="icon-money"></i> </span>
              <h5>Payment Receipt</h5>
            </div>
            <div class="widget-content">
              <div class="payment-receipt">
                <h3>Payment Confirmation</h3>
                <div class="row-fluid">
                  <div class="span6">
                    <p><strong>Member Name:</strong> <?php echo htmlspecialchars($row['fullname']); ?></p>
                    <p><strong>Service:</strong> <?php echo htmlspecialchars($row['services']); ?></p>
                    <p><strong>Amount Paid:</strong> $<?php echo $amount; ?></p>
                  </div>
                  <div class="span6">
                    <p><strong>Date:</strong> <?php echo date('Y-m-d'); ?></p>
                    <p><strong>Payment Method:</strong> Khalti</p>
                    <p><strong>Status:</strong> Completed</p>
                  </div>
                </div>
                <hr>
                <div class="text-center">
                  <a href="payment.php" class="btn btn-primary">Go to Payments</a>
                  <a href="index.php" class="btn btn-success">Go to Dashboard</a>
                  <button onclick="window.print();" class="btn btn-info">Print Receipt</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php } else { ?>
      <div class="row-fluid">
        <div class="span12">
          <div class="widget-box">
            <div class="widget-title"> <span class="icon"> <i class="icon-money"></i> </span>
              <h5>Payments</h5>
            </div>
            <div class="widget-content">
              <div class="row-fluid">
                <div class="span5">
                  <table class="">
                    <tbody>
                      <tr>
                        <td><img src="../img/gym-logo.png" alt="Gym Logo" width="175"></td>
                      </tr>
                      <tr>
                        <td>
                          <h4>Perfect GYM Club</h4>
                        </td>
                      </tr>
                      <tr>
                        <td>Chabahill,Kathmandu</td>
                      </tr>
                      <tr>
                        <td>Tel: 023-582-010</td>
                      </tr>
                      <tr>
                        <td>Email: support@Bishnugym.com</td>
                      </tr>
                    </tbody>
                  </table>
                </div>

                <div class="span7">
                  <table class="table table-bordered table-invoice">
                    <tbody>
                      <tr>
                        <td class="width30">Member's Fullname:</td>
                        <td class="width70"><strong><?php echo htmlspecialchars($row['fullname']); ?></strong></td>
                      </tr>
                      <tr>
                        <td>Service:</td>
                        <td><strong><?php echo htmlspecialchars($row['services']); ?></strong></td>
                      </tr>
                      <tr>
                        <td>Amount Per Month:</td>
                        <td><strong>$<?php echo $amount; ?></strong></td>
                      </tr>
                      <tr>
                        <td class="width30">Plan:</td>
                        <td class="width70">
                          <div class="controls">
                            <select name="plan" required="required" id="select-plan">
                              <option value="1" selected="selected">One Month</option>
                              <option value="3">Three Month</option>
                              <option value="6">Six Month</option>
                              <option value="12">One Year</option>
                              <option value="0">None-Expired</option>
                            </select>
                          </div>
                        </td>
                      </tr>
                      <tr>
                        <td class="width30">Member's Status:</td>
                        <td class="width70">
                          <div class="controls">
                            <select name="status" required="required" id="select-status">
                              <option value="Active" selected="selected">Active</option>
                              <option value="Expired">Expired</option>
                            </select>
                          </div>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div> <!-- row-fluid ends here -->

              <div class="row-fluid">
                <div class="span12">
                  <hr>
                  <div class="text-center">
                    <input type="hidden" id="user-id" value="<?php echo $row['user_id']; ?>">
                    <input type="hidden" id="payment-amount" value="<?php echo $amount; ?>">
                    <input type="hidden" id="fullname" value="<?php echo htmlspecialchars($row['fullname']); ?>">
                    <input type="hidden" id="services" value="<?php echo htmlspecialchars($row['services']); ?>">
                    
                    <button id="khalti-payment-btn" class="btn btn-primary btn-large">Make Payment with Khalti</button>
                    
                    <!-- Khalti Payment Form -->
                    <div id="khaltiModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="khaltiModalLabel" aria-hidden="true">
                      <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h3 id="khaltiModalLabel">Pay with Khalti</h3>
                      </div>
                      <div class="modal-body">
                        <div class="khalticontainer">
                          <center>
                            <div><img src="../img/khalti.png" alt="khalti" width="200" ></div>
                          </center>
                          <form id="khalti-form" method="post" action="">
                            <?php if($error_message): ?>
                              <div class="error-message">
                                <i class="icon-warning-sign"></i> <?php echo $error_message; ?>
                              </div>
                            <?php endif; ?>
                            <small>Mobile Number:</small> <br>
                            <input type="number" class="khalti-input" name="mobile" placeholder="98xxxxxxxx" pattern="[0-9]{10}" title="Please enter a valid 10-digit mobile number" required>
                            <small>Khalti Mpin:</small> <br>
                            <input type="password" class="khalti-input" name="mpin" pattern="[0-9]{4,6}" placeholder="xxxx" title="MPIN must be 4 to 6 digits" required>
                            <small>Price:</small> <br>
                            <input type="text" class="khalti-input" value="Rs. <?php echo $amount; ?>" disabled>
                            <input type="hidden" name="amount" value="<?php echo $amount; ?>">
                            <input type="hidden" name="plan" id="khalti-plan" value="1">
                            <input type="hidden" name="status" id="khalti-status" value="Active">
                            <br>
                            <button type="submit" name="submit-payment" id="submit-payment" class="khalti-button">Pay Rs. <?php echo $amount; ?></button>
                            <div id="payment-loader" class="loader"></div>
                            <br>
                            <small style="display:block;text-align:center;color:#666;">We don't store your credentials for security reasons.</small>
                          </form>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div> 
      <?php } ?>
    </div> 
  </div> 

  <?php
  }
  ?>

  <!--end-main-container-part-->

  <!--Footer-part-->
  <div class="row-fluid">
    <div id="footer" class="span12"> <?php echo date("Y"); ?> &copy; Developed By Bishnu Khadka</a> </div>
  </div>

  <!-- JavaScript -->
  <script src="../js/jquery.min.js"></script>
  <script src="../js/jquery.ui.custom.js"></script>
  <script src="../js/bootstrap.min.js"></script>
  
  <script>
  $(document).ready(function() {
    // Handle Khalti payment button click
    $("#khalti-payment-btn").click(function(e) {
      e.preventDefault();
      $("#khaltiModal").modal('show');
      
      // Update hidden fields with current selections
      $("#khalti-plan").val($("#select-plan").val());
      $("#khalti-status").val($("#select-status").val());
    });
    
    // Update hidden fields when selections change
    $("#select-plan, #select-status").change(function() {
      $("#khalti-plan").val($("#select-plan").val());
      $("#khalti-status").val($("#select-status").val());
    });
    
    // Show loading indicator when form is submitted
    $("#khalti-form").submit(function() {
      var mobile = $("input[name='mobile']").val();
      var mpin = $("input[name='mpin']").val();
      
      if(mobile == "" || mpin == "") {
        // Don't show loader if fields are empty
        return false;
      }
      
      $("#submit-payment").prop('disabled', true).text('Processing...');
      $("#payment-loader").show();
    });
  });
  </script>
</body>
</html>