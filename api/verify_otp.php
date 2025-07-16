<?php
require '../inc/db.php';

header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

$data = json_decode(file_get_contents("php://input"), true);
$fullname = trim($data['fullname'] ?? '');
$phone = trim($data['phone'] ?? '');
$otp = trim($data['otp'] ?? '');

if (empty($fullname) || empty($phone) || empty($otp)) {
    echo json_encode(['status' => false, 'message' => 'All fields are required']);
    exit;
}

// Check OTP (valid for 5 minutes)
$stmt = $conn->prepare("SELECT * FROM otps WHERE phone = :phone AND otp = :otp AND created_at >= (NOW() - INTERVAL 5 MINUTE) ORDER BY id DESC LIMIT 1");
$stmt->execute(['phone' => $phone, 'otp' => $otp]);

if ($stmt->rowCount() === 0) {
    echo json_encode(['status' => false, 'message' => 'Invalid or expired OTP']);
    exit;
}

// Check if user already exists
$stmt = $conn->prepare("SELECT * FROM users WHERE phone = :phone");
$stmt->execute(['phone' => $phone]);

if ($stmt->rowCount() > 0) {
    echo json_encode(['status' => false, 'message' => 'User already registered']);
    exit;
}

// Register user
$stmt = $conn->prepare("INSERT INTO users (fullname, phone) VALUES (:fullname, :phone)");
$stmt->execute(['fullname' => $fullname, 'phone' => $phone]);

// Optionally delete OTP
$conn->prepare("DELETE FROM otps WHERE phone = :phone")->execute(['phone' => $phone]);

echo json_encode(['status' => true, 'message' => 'User registered successfully']);
