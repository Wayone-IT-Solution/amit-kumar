<?php
require '../inc/db.php';
header('Content-Type: application/json');

try {
    // Decode incoming JSON
    $data = json_decode(file_get_contents("php://input"), true);
    $fullname = trim($data['fullname'] ?? '');
    $phone = trim($data['phone'] ?? '');

    // Validation
    if (empty($fullname) || empty($phone)) {
        echo json_encode(['status' => false, 'message' => 'Full name and phone number are required']);
        exit;
    }

    if (!preg_match('/^\d{10}$/', $phone)) {
        echo json_encode(['status' => false, 'message' => 'Invalid phone number format']);
        exit;
    }

    // Generate 4-digit OTP
    $otp = rand(1000, 9999);

    // Optional: remove old OTPs for the same phone (to keep latest only)
    $conn->prepare("DELETE FROM otps WHERE phone = :phone")->execute(['phone' => $phone]);

    // Save OTP with fullname and phone
    $stmt = $conn->prepare("INSERT INTO otps (fullname, phone, otp) VALUES (:fullname, :phone, :otp)");
    $stmt->execute([
        'fullname' => $fullname,
        'phone' => $phone,
        'otp' => $otp
    ]);

    // ✅ Success Response (remove 'otp' in production)
    echo json_encode([
        'status' => true,
        'message' => 'OTP sent successfully',
        'data' => [
            'fullname' => $fullname,
            'phone' => $phone,
            'otp' => $otp // ⚠️ Remove or hide in production
        ]
    ]);

} catch (PDOException $e) {
    error_log("DB Error: " . $e->getMessage());
    echo json_encode(['status' => false, 'message' => 'Server error. Please try again later.']);
} catch (Exception $e) {
    error_log("General Error: " . $e->getMessage());
    echo json_encode(['status' => false, 'message' => 'Unexpected error occurred.']);
}
