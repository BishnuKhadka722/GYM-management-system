<?php
session_start();
//the isset function to check username is already logged in and stored on the session
if(!isset($_SESSION['user_id'])){
    header('location:../index.php');	
}

$error_message = "";
$khalti_public_key = "test_public_key_78040772c70c47309222c28578dc01772";

// Default test credentials
$default_mobile = "9800000000";
$default_mpin = "1111";

// Handle the form submission from user-payment.php
if(isset($_POST['id']) && isset($_POST['amount']) && isset($_POST['plan'])) {
    // Store the payment data in session to use later
    $_SESSION['payment_data'] = $_POST;
    
    // Calculate the total amount based on plan
    $price = floatval($_POST['amount']) * intval($_POST['plan']);
    $amount = $price;
    
    // Set other required variables
    $uniqueProductId = "GYM_" . $_POST['id'];
    $uniqueProductName = "Gym Membership - " . $_POST['services'];
    $uniqueUrl = "http://localhost/Gym-System/staff/staff-pages/";
    $successRedirect = "http://localhost/Gym-System/staff/staff-pages/userpay.php";
} else {
    // Default values if direct access
    $amount = 10;
    $uniqueProductId = "GYM form";
    $uniqueProductName = "Gym Membership";
    $uniqueUrl = "http://localhost/Gym-System/staff/staff-pages/";
    $successRedirect = "http://localhost/Gym-System/staff/staff-pages/userpay.php";
}

function checkValid($data)
{
    if(isset($_SESSION['payment_data'])) {
        $expectedAmount = floatval($_SESSION['payment_data']['amount']) * intval($_SESSION['payment_data']['plan']) * 100;
        if ((float) $data["amount"] == $expectedAmount) {
            // Set a flag that payment was successful
            $_SESSION['payment_successful'] = true;
            return 1;
        }
    } else {
        $verifyAmount = 1000; // get amount from database and multiply by 100
        // $data contains khalti response. you can print it to view more details.
        // eg. $data["token] will give token & $data["amount] will give amount and more. see khalti docs for response format
        if ((float) $data["amount"] == $verifyAmount) {
            // Set a flag that payment was successful
            $_SESSION['payment_successful'] = true;
            return 1;
        }
    }
    return 0;
}

// declaring some global variables
$token = "";
$price = $amount;
$mpin = isset($_POST["mpin"]) ? $_POST["mpin"] : $default_mpin;
$mobile = isset($_POST["mobile"]) ? $_POST["mobile"] : $default_mobile;

// send otp
if (isset($_POST["submit_payment"])) {
    try {
        $mobile = !empty($_POST["mobile"]) ? $_POST["mobile"] : $default_mobile;
        $mpin = !empty($_POST["mpin"]) ? $_POST["mpin"] : $default_mpin;
        $price = (float) $amount;
        $amount = (float) $amount * 100;

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
                CURLOPT_POSTFIELDS => '{
                    "public_key": "' . $khalti_public_key . '",
                    "mobile": ' . $mobile . ',
                    "transaction_pin": ' . $mpin . ',
                    "amount": ' . ($amount) . ',
                    "product_identity": "' . $uniqueProductId . '",
                    "product_name": "' . $uniqueProductName . '",
                    "product_url": "' . $uniqueUrl . '"
                }',
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
        } else {
            $error_message = "Error: " . (isset($parsed["detail"]) ? $parsed["detail"] : "Incorrect mobile or mpin");
        }
    } catch (Exception $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

// otp verification
if (isset($_POST["verify_otp"])) {
    try {
        $otp = $_POST["otp"];
        $token = $_POST["token"];
        $mpin = !empty($_POST["mpin"]) ? $_POST["mpin"] : $default_mpin;

        $curl = curl_init();

        curl_setopt_array(
            $curl,
            array(
                CURLOPT_URL => 'https://khalti.com/api/v2/payment/confirm/',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => '{
                    "public_key": "' . $khalti_public_key . '",
                    "transaction_pin": ' . $mpin . ',
                    "confirmation_code": ' . $otp . ',
                    "token": "' . $token . '"
                }',
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json'
                ),
            )
        );

        $response = curl_exec($curl);
        curl_close($curl);
        $parsed = json_decode($response, true);

        if (isset($parsed["token"])) {
            $isvalid = checkValid($parsed);
            if ($isvalid) {
                // Save transaction details if needed
                $_SESSION['payment_successful'] = true;
                // Redirect to success page
                header("Location: " . $successRedirect);
                exit;
            }
        } else {
            $error_message = "Could not process the transaction at the moment.";
            if (isset($parsed["detail"])) {
                $error_message = $parsed["detail"];
            }
        }
    } catch (Exception $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

// Pass all the payment data to the form's hidden fields
$hiddenFields = '';
if (isset($_SESSION['payment_data'])) {
    foreach ($_SESSION['payment_data'] as $key => $value) {
        $hiddenFields .= '<input type="hidden" name="' . $key . '" value="' . htmlspecialchars($value) . '">';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Khalti Payment</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css" />
    <style>
        .khalticontainer {
            width: 350px;
            border: 2px solid #5C2D91;
            border-radius: 8px;
            margin: 40px auto;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            background-color: #fff;
        }
        .logo-container {
            text-align: center;
            margin-bottom: 20px;
        }
        input {
            display: block;
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        button {
            display: block;
            background-color: #5C2D91;
            border: none;
            color: white;
            cursor: pointer;
            width: 100%;
            padding: 12px;
            margin: 15px 0 5px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #4a2275;
        }
        small {
            color: #666;
            font-size: 12px;
        }
        .error-msg {
            color: red;
            font-size: 14px;
            margin: 8px 0;
        }
        .back-btn {
            text-align: center;
            margin-top: 15px;
        }
        .back-btn a {
            color: #5C2D91;
            text-decoration: none;
        }
        .back-btn a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="khalticontainer">
        <div class="logo-container">
            <img src="../img/khalti.png" alt="khalti" width="180">
        </div>
        
        <?php if ($token == ""): ?>
            <form action="checkout.php" method="post">
                <?php echo $hiddenFields; ?>
                
                <small>Mobile Number:</small>
                <input type="number" class="number" name="mobile" placeholder="980605684" value="<?php echo $default_mobile; ?>" required>
                
                <small>Khalti MPIN:</small>
                <input type="password" class="mpin" name="mpin" placeholder="1234" value="<?php echo $default_mpin; ?>" required>
                
                <small>Amount:</small>
                <input type="text" class="price" value="Rs. <?php echo $price; ?>" disabled>
                <input type="hidden" name="amount" value="<?php echo $price; ?>">
                
                <?php if(!empty($error_message)): ?>
                    <div class="error-msg"><?php echo $error_message; ?></div>
                <?php endif; ?>
                
                <button type="submit" name="submit_payment">Pay Rs. <?php echo $price; ?></button>
                
                <small>Default Test credentials: 980605684 / 1234</small>
                <small>We don't store your credentials for security reasons.</small>
                
                <div class="back-btn">
                    <a href="payment.php">&larr; Back to Payments</a>
                </div>
            </form>
        <?php else: ?>
            <form action="checkout.php" method="post">
                <?php echo $hiddenFields; ?>
                <input type="hidden" name="token" value="<?php echo $token; ?>">
                <input type="hidden" name="mpin" value="<?php echo $mpin; ?>">
                <input type="hidden" name="amount" value="<?php echo $price; ?>">
                
                <small>Enter OTP sent to <?php echo substr($mobile, 0, 3) . 'xxxxxxx'; ?>:</small>
                <input type="number" name="otp" placeholder="Enter OTP" required>
                
                <?php if(!empty($error_message)): ?>
                    <div class="error-msg"><?php echo $error_message; ?></div>
                <?php endif; ?>
                
                <button type="submit" name="verify_otp">Verify & Pay Rs. <?php echo $price; ?></button>
                
                <div class="back-btn">
                    <a href="checkout.php">Cancel Payment</a>
                </div>
            </form>
        <?php endif; ?>
    </div>
    
    <script src="../js/jquery.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
</body>
</html>