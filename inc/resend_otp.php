<?php
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['phone'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Session expired. Please register again.'
    ]);
    exit;
}

// Generate new OTP
$otp = rand(100000, 999999);
$_SESSION['otp'] = $otp;

// You would normally send SMS here

echo json_encode([
    'status' => 'success',
    'phone' => $_SESSION['phone'],
    'otp' => $otp // Only for demo; remove in production
]);
?>
