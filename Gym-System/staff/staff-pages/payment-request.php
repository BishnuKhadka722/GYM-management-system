<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header('location:../index.php');	
    exit();
}

if (isset($_POST['submit'])) {
    // Get form data
    $amount = $_POST['inputAmount4']*100; // convert to paisa for Khalti
    $purchase_order_id = $_POST['inputPurchasedOrderId4'];
    $purchase_order_name = $_POST['inputPurchasedOrderName'];
    $name = $_POST['inputName'];
    $email = $_POST['inputEmail'];
    $phone = $_POST['inputPhone'];
    
    // Store additional data for payment processing
    $_SESSION['payment_processing'] = [
        'user_id' => $_POST['user_id'],
        'plan' => $_POST['plan'],
        'status' => $_POST['status'],
        'amount' => $_POST['inputAmount4'],
        'customer_email' => $email,
        'customer_phone' => $phone
    ];

    // Validate all required fields
    if(empty($amount) || empty($purchase_order_id) || empty($purchase_order_name) || empty($name) || empty($email) || empty($phone)){
        $_SESSION["validate_msg"] = '<script>
        Swal.fire({
            icon: "error",
            title: "All fields are required",
            showConfirmButton: false,
            timer: 1500
        });
        </script>';
        header("Location: checkout.php");
        exit();
    }
    
    // Validate amount
    if(!is_numeric($amount)){
        $_SESSION["validate_msg"] = '<script>
        Swal.fire({
            icon: "error",
            title: "Amount must be a number",
            showConfirmButton: false,
            timer: 1500
        });
        </script>';
        header("Location: checkout.php");
        exit();
    }

    // Validate phone number
    if(!is_numeric($phone)){
        $_SESSION["validate_msg"] = '<script>
        Swal.fire({
            icon: "error",
            title: "Phone must be a number",
            showConfirmButton: false,
            timer: 1500
        });
        </script>';
        header("Location: checkout.php");
        exit();
    }

    // Validate email
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $_SESSION["validate_msg"] = '<script>
        Swal.fire({
            icon: "error",
            title: "Email is not valid",
            showConfirmButton: false,
            timer: 1500
        });
        </script>';
        header("Location: checkout.php");
        exit();
    }

    // Get server domain and protocol for return URL
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $base_path = dirname($_SERVER['PHP_SELF']);
    
    // Create the payment request for Khalti
    $postFields = array(
        "return_url" => "$protocol://$host$base_path/payment-response.php", 
        "website_url" => "$protocol://$host/gymdb/", 
        "amount" => $amount,
        "purchase_order_id" => $purchase_order_id,
        "purchase_order_name" => $purchase_order_name,
        "customer_info" => array(
            "name" => $name,
            "email" => $email,
            "phone" => $phone
        )
    );

    $jsonData = json_encode($postFields);

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://a.khalti.com/api/v2/epayment/initiate/',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $jsonData,
        CURLOPT_HTTPHEADER => array(
            'Authorization: key live_secret_key_68791341fdd94846a146f0457ff7b455', // Replace with your actual Khalti key
            'Content-Type: application/json',
        ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    if ($err) {
        $_SESSION["validate_msg"] = '<script>
        Swal.fire({
            icon: "error",
            title: "Payment Error",
            text: "' . $err . '",
            showConfirmButton: true
        });
        </script>';
        header("Location: checkout.php");
        exit();
    } else {
        $responseArray = json_decode($response, true);

        if (isset($responseArray['error'])) {
            $_SESSION["validate_msg"] = '<script>
            Swal.fire({
                icon: "error",
                title: "Payment Error",
                text: "' . $responseArray['error'] . '",
                showConfirmButton: true
            });
            </script>';
            header("Location: checkout.php");
            exit();
        } elseif (isset($responseArray['payment_url'])) {
            // Redirect the user to the Khalti payment page
            header('Location: ' . $responseArray['payment_url']);
            exit();
        } else {
            $_SESSION["validate_msg"] = '<script>
            Swal.fire({
                icon: "error",
                title: "Unexpected Response",
                text: "There was an issue processing your payment. Please try again.",
                showConfirmButton: true
            });
            </script>';
            header("Location: checkout.php");
            exit();
        }
    }

    curl_close($curl);
} else {
    // If no form submission, redirect to checkout
    header("Location: checkout.php");
    exit();
}