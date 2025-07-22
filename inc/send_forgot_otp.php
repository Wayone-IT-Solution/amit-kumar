<?php
session_start();
header('Content-Type: application/json');
require_once 'db.php';

$input = trim($_POST['forgot_input'] ?? '');

if (empty($input)) {
    echo json_encode(['status' => false, 'message' => 'Email or Phone is required']);
    exit;
}

$stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR phone = ?");
$stmt->execute([$input, $input]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    $otp = rand(100000, 999999);
    $_SESSION['reset_user_id'] = $user['id'];
    $_SESSION['reset_otp'] = $otp;

    echo json_encode([
        'status' => true,
        'otp' => $otp,
        'message' => 'OTP sent successfully'
    ]);
} else {
    echo json_encode(['status' => false, 'message' => 'User not found']);
}
