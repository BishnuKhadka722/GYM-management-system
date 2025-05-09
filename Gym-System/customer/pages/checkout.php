<?php
session_start();
//check if user is logged in
if(!isset($_SESSION['user_id'])){
    header('location:../index.php');	
}

// Check if form data is submitted from user-payment.php
if(isset($_POST['id'])) {
    // Process the payment details
    $user_id = $_POST['id'];
    $fullname = $_POST['fullname'];
    $services = $_POST['services'];
    $amount = $_POST['amount'];
    $plan = $_POST['plan'];
    $status = $_POST['status'];
    
    // Calculate total amount based on plan
    $total_amount = $amount * $plan;
    
    // Store payment information in session for processing
    $_SESSION['payment_data'] = [
        'user_id' => $user_id,
        'fullname' => $fullname,
        'services' => $services,
        'amount' => $amount,
        'plan' => $plan,
        'status' => $status,
        'total_amount' => $total_amount
    ];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gym Payment Checkout</title>

    <!-- bootstrap css -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Gym System CSS -->
    <link rel="stylesheet" href="../css/bootstrap.min.css" />
    <link rel="stylesheet" href="../css/matrix-style.css" />
    <link rel="stylesheet" href="../css/matrix-media.css" />
    <link href="../font-awesome/css/font-awesome.css" rel="stylesheet" />
</head>

<body class="m-4">
    <?php
    if (isset($_SESSION['transaction_msg'])) {
        echo $_SESSION['transaction_msg'];
        unset($_SESSION['transaction_msg']);
    }

    if (isset($_SESSION['validate_msg'])) {
        echo $_SESSION['validate_msg'];
        unset($_SESSION['validate_msg']);
    }
    ?>
    
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="widget-box">
                    <div class="widget-title">
                        <span class="icon"><i class="icon-money"></i></span>
                        <h5>Khalti Payment Integration</h5>
                    </div>
                    <div class="widget-content">
                        <div class="d-flex justify-content-center mt-3">
                            <form class="row g-3 w-75 mt-4" action="payment-request.php" method="POST">
                                <div class="col-md-12">
                                    <h4>Payment Details</h4>
                                </div>
                                
                                <!-- Product Details Section -->
                                <div class="col-md-12 mb-3">
                                    <label class="fw-bold">Product Details:</label>
                                </div>
                                <div class="col-md-4">
                                    <label for="inputAmount4" class="form-label">Amount (NPR)</label>
                                    <input type="number" class="form-control" id="inputAmount4" name="inputAmount4" 
                                        value="<?php echo isset($_SESSION['payment_data']) ? $_SESSION['payment_data']['total_amount'] : ''; ?>" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label for="inputPurchasedOrderId4" class="form-label">Member ID</label>
                                    <input type="text" class="form-control" id="inputPurchasedOrderId4" name="inputPurchasedOrderId4" 
                                        value="<?php echo isset($_SESSION['payment_data']) ? 'MEM-'.$_SESSION['payment_data']['user_id'] : ''; ?>" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label for="inputPurchasedOrderName" class="form-label">Service</label>
                                    <input type="text" class="form-control" id="inputPurchasedOrderName" name="inputPurchasedOrderName" 
                                        value="<?php echo isset($_SESSION['payment_data']) ? $_SESSION['payment_data']['services'].' Membership ('.$_SESSION['payment_data']['plan'].' month'.(($_SESSION['payment_data']['plan'] > 1) ? 's' : '').')' : ''; ?>" readonly>
                                </div>
                                
                                <!-- Customer Details Section -->
                                <div class="col-md-12 mt-4 mb-3">
                                    <label class="fw-bold">Customer Details:</label>
                                </div>
                                <div class="col-md-4">
                                    <label for="inputName" class="form-label">Name</label>
                                    <input type="text" class="form-control" id="inputName" name="inputName" 
                                        value="<?php echo isset($_SESSION['payment_data']) ? $_SESSION['payment_data']['fullname'] : ''; ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="inputEmail" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="inputEmail" name="inputEmail" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="inputPhone" class="form-label">Phone</label>
                                    <input type="text" class="form-control" id="inputPhone" name="inputPhone" required>
                                </div>
                                
                                <!-- Hidden fields to pass to payment-request.php -->
                                <input type="hidden" name="user_id" value="<?php echo isset($_SESSION['payment_data']) ? $_SESSION['payment_data']['user_id'] : ''; ?>">
                                <input type="hidden" name="plan" value="<?php echo isset($_SESSION['payment_data']) ? $_SESSION['payment_data']['plan'] : ''; ?>">
                                <input type="hidden" name="status" value="<?php echo isset($_SESSION['payment_data']) ? $_SESSION['payment_data']['status'] : ''; ?>">
                                
                                <div class="col-12 mt-4 text-center">
                                    <button type="submit" name="submit" class="btn btn-primary btn-large">
                                        <i class="icon-money"></i> Pay with Khalti
                                    </button>
                                    <a href="../pages/user-payment.php" class="btn btn-danger btn-large">
                                        <i class="icon-remove"></i> Cancel
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <div class="row-fluid">
        <div id="footer" class="span12"> <?php echo date("Y");?> &copy; Developed By Bishnu Khadka</div>
    </div>
    
    <style>
    #footer {
        color: #777;
        text-align: center;
        padding: 20px 0;
        margin-top: 30px;
    }
    .widget-box {
        border: 1px solid #ccc;
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .widget-title {
        background-color: #efefef;
        padding: 10px;
        margin: -20px -20px 20px -20px;
        border-bottom: 1px solid #ccc;
    }
    </style>
</body>
</html>