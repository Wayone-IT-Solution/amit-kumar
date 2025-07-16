<?php
session_start();

if (isset($_SESSION['login_phone'], $_SESSION['login_user_id'])) {
    // Generate new OTP
    $newOtp = rand(100000, 999999);

    // Store it in session
    $_SESSION['login_otp'] = $newOtp;

    // For real apps: Send OTP via SMS API here

    // For demo purposes: return OTP in response
    echo "New OTP: $newOtp";
} else {
    http_response_code(400);
    echo "Session expired. Please login again.";
}
?>
