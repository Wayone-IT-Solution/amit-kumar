<?php
require '../inc/db.php'; // Adjust path if needed

header('Content-Type: application/json');

try {
    // Allow only GET requests
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405); // Method Not Allowed
        echo json_encode([
            'status' => false,
            'message' => 'Invalid request method. Only GET is allowed.'
        ]);
        exit;
    }

    // Fetch products where specialities = 1
    $stmt = $conn->prepare("SELECT * FROM products WHERE specialities = 1 ORDER BY id DESC");
    $stmt->execute();
    $products = $stmt->fetchAll();

    if (!$products) {
        echo json_encode([
            'status' => true,
            'message' => 'No special products found',
            'data' => []
        ]);
        exit;
    }

    echo json_encode([
        'status' => true,
        'message' => 'Special products fetched successfully',
        'data' => $products
    ]);
} catch (PDOException $e) {
    error_log("DB Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => false,
        'message' => 'Database error while fetching special products.'
    ]);
    exit;
} catch (Throwable $e) {
    error_log("Unexpected Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => false,
        'message' => 'Unexpected server error.'
    ]);
    exit;
}
