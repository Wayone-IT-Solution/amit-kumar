<?php
require 'db.php'; // PDO connection

header('Content-Type: application/json');

$pincode = trim($_POST['pincode'] ?? '');

if (!preg_match('/^\d{6}$/', $pincode)) {
    echo json_encode(['status' => false]);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM pincodes WHERE pincode = ? AND status = 'active'");
    $stmt->execute([$pincode]);
    $exists = $stmt->fetchColumn();

    echo json_encode(['status' => $exists > 0]);
} catch (PDOException $e) {
    error_log("Pincode check failed: " . $e->getMessage());
    echo json_encode(['status' => false]);
}
