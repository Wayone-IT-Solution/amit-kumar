<?php
// Force JSON response
header('Content-Type: application/json');

// Show errors in development (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require('db.php');

try {
    // Fetch all boxes, ignore category_id
    $sql = "SELECT id, box_name, box_price, box_image FROM boxes ORDER BY id ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $boxes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Return response
    echo json_encode($boxes);
} catch (Exception $e) {
    error_log('Fetch boxes error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch boxes.']);
}
