<?php
session_start();
header('Content-Type: application/json');

$entered = $_POST['entered_otp'] ?? '';

if ($entered == ($_SESSION['reset_otp'] ?? '')) {
    echo json_encode(['status' => true]);
} else {
    echo json_encode(['status' => false, 'message' => 'OTP does not match.']);
}
