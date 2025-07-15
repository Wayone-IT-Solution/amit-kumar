<?php
require '../inc/db.php';
header('Content-Type: application/json');

try {
    // Read input
    $data = json_decode(file_get_contents("php://input"), true);
    $phone = trim($data['phone'] ?? '');

    // Validate input
    if (empty($phone)) {
        echo json_encode(['status' => false, 'message' => 'Phone number is required']);
        exit;
    }

    if (!preg_match('/^\d{10}$/', $phone)) {
        echo json_encode(['status' => false, 'message' => 'Invalid phone number format']);
        exit;
    }

    // ✅ Check if user exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE phone = :phone LIMIT 1");
    $stmt->execute(['phone' => $phone]);

    if ($stmt->rowCount() === 0) {
        echo json_encode(['status' => false, 'message' => 'Phone number not registered']);
        exit;
    }

    // ✅ Generate OTP
    $otp = rand(1000, 9999);

    // ✅ Store OTP
    $stmt = $conn->prepare("INSERT INTO otps (phone, otp) VALUES (:phone, :otp)");
    $stmt->execute(['phone' => $phone, 'otp' => $otp]);

    // ✅ Log OTP for testing (Remove this in production)
    file_put_contents("otp_log.txt", "Phone: $phone | OTP: $otp | Time: " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);

    // ✅ Return success response
    echo json_encode([
        'status' => true,
        'message' => 'OTP sent successfully',
        'otp' => $otp  // ⚠️ REMOVE IN PRODUCTION
    ]);

} catch (PDOException $e) {
    error_log("DB Error: " . $e->getMessage());
    echo json_encode(['status' => false, 'message' => 'Database error.']);
} catch (Exception $e) {
    error_log("General Error: " . $e->getMessage());
    echo json_encode(['status' => false, 'message' => 'Unexpected server error.']);
}
