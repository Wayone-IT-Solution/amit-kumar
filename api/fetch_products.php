<?php
require '../inc/db.php'; // Adjust path if needed

header('Content-Type: application/json');

try {
    // Only allow GET requests
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405); // Method Not Allowed
        echo json_encode([
            'status' => false,
            'message' => 'Invalid request method. Only GET is allowed.'
        ]);
        exit;
    }

    // Optional category filter
    $categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

    if ($categoryId > 0) {
        $stmt = $conn->prepare("SELECT * FROM products WHERE category_id = :category_id ORDER BY id DESC");
        $stmt->execute(['category_id' => $categoryId]);
    } else {
        $stmt = $conn->prepare("SELECT * FROM products ORDER BY id DESC");
        $stmt->execute();
    }

    $products = $stmt->fetchAll();

    if (!$products) {
        echo json_encode([
            'status' => true,
            'message' => 'No products found',
            'data' => []
        ]);
        exit;
    }

    echo json_encode([
        'status' => true,
        'message' => 'Products fetched successfully',
        'data' => $products
    ]);
} catch (PDOException $e) {
    error_log("DB Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => false,
        'message' => 'Error fetching products. Please try again later.'
    ]);
    exit;
} catch (Throwable $e) {
    error_log("General Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => false,
        'message' => 'Unexpected server error. Please try again.'
    ]);
    exit;
}
?>
