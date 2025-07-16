<?php
require 'db.php'; // PDO connection

header('Content-Type: application/json');

$pincode = trim($_POST['pincode'] ?? '');

if (!preg_match('/^\d{6}$/', $pincode)) {
    echo json_encode(['status' => false]);
    exit;
}

try {
    $stmt = $conn->query("SELECT pincode FROM pincodes");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($row) {
        $validPincodes = preg_split('/[\s,]+/', $row['pincode']);
        echo json_encode(['status' => in_array($pincode, $validPincodes)]);
        exit;
    }

    echo json_encode(['status' => false]);
} catch (PDOException $e) {
    error_log("Pincode check failed: " . $e->getMessage());
    echo json_encode(['status' => false]);
}
