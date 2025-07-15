<?php
require_once 'db.php'; // Include your DB connection (as shown above)

try {
    $stmt = $conn->prepare("SELECT pincode FROM pincodes ORDER BY id DESC LIMIT 100");
    $stmt->execute();
    $pincodes = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode($pincodes);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch pincodes']);
}
?>
