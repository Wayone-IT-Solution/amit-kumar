<?php
require '../inc/db.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$phone = trim($data['phone'] ?? '');
$otp = trim($data['otp'] ?? '');

if (empty($phone) || empty($otp)) {
    echo json_encode(['status' => false, 'message' => 'Phone and OTP are required']);
    exit;
}

// Check if OTP is valid (within 5 minutes)
$stmt = $conn->prepare("SELECT * FROM otps WHERE phone = :phone AND otp = :otp AND created_at >= (NOW() - INTERVAL 5 MINUTE) ORDER BY id DESC LIMIT 1");
$stmt->execute(['phone' => $phone, 'otp' => $otp]);

if ($stmt->rowCount() === 0) {
    echo json_encode(['status' => false, 'message' => 'Invalid or expired OTP']);
    exit;
}

// Check if user exists
$stmt = $conn->prepare("SELECT * FROM users WHERE phone = :phone");
$stmt->execute(['phone' => $phone]);

if ($stmt->rowCount() === 0) {
    echo json_encode(['status' => false, 'message' => 'User not found']);
    exit;
}

$user = $stmt->fetch();

// Delete OTP after verification
$conn->prepare("DELETE FROM otps WHERE phone = :phone")->execute(['phone' => $phone]);

// You can return a session token or basic user info
echo json_encode([
    'status' => true,
    'message' => 'Login successful',
    'user' => [
        'id' => $user['id'],
        'fullname' => $user['fullname'],
        'phone' => $user['phone']
    ]
]);
