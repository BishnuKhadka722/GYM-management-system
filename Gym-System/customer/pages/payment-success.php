<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header('location:../index.php');	
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful - Gym System</title>

    <!-- bootstrap css -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Gym System CSS -->
    <link rel="stylesheet" href="../css/bootstrap.min.css" />
    <link rel="stylesheet" href="../css/matrix-style.css" />
    <link rel="stylesheet" href="../css/matrix-media.css" />
    <link href="../font-awesome/css/font-awesome.css" rel="stylesheet" />
</head>

<body>
    <?php
    if (isset($_SESSION['transaction_msg'])) {
        echo $_SESSION['transaction_msg'];
        unset($_SESSION['transaction_msg']);
    } else {
        // Set default success message if none exists
        echo '<script>
            Swal.fire({
                icon: "success",
                title: "Payment Successful",
                text: "Your payment has been successfully processed!",
                showConfirmButton: true
            });
            </script>';
    }
    ?>

    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-8">
                <div class="card border-success">
                    <div class="card-header bg-success text-white">
                        <h3 class="text-center">Payment Successful!</h3>
                    </div>
                    <div class="card-body text-center">
                        <i class="icon-check-sign" style="font-size: 72px; color: green;"></i>
                        <h4 class="my-4">Thank you for your payment</h4>
                        <p class="card-text">
                            Your gym membership has been successfully updated. You can now enjoy all the facilities according to your selected plan.
                        </p>
                        <hr>
                        <p>If you have any questions, please contact our staff.</p>
                        
                        <div class="mt-4">
                            <a href="userpay.php" class="btn btn-primary">
                                <i class="icon-credit-card"></i> View Payments
                            </a>
                            <a href="index.php" class="btn btn-secondary">
                                <i class="icon-home"></i> Go to Dashboard
                            </a>
                        </div>
                    </div>
                    <div class="card-footer text-center">
                        <small class="text-muted">
                            Transaction Date: <?php echo date("Y-m-d H:i:s"); ?>
                        </small>
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
    body {
        background-color: #f5f5f5;
    }
    #footer {
        color: #777;
        text-align: center;
        padding: 20px 0;
        margin-top: 30px;
    }
    .card {
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .icon-check-sign {
        display: inline-block;
        margin: 20px 0;
    }
    </style>
</body>
</html>