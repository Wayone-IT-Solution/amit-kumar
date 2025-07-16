<?php
require '../inc/db.php'; // Make sure $conn (PDO) is initialized here
header('Content-Type: application/json');

try {
    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['status' => false, 'message' => 'Only GET method is allowed.']);
        exit;
    }

    // Get query params
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

    // Base SQL
    $sql = "SELECT p.*, c.title 
            FROM products p 
            JOIN categories c ON p.category_id = c.id 
            WHERE 1";

    $params = [];

    // Add category filter
    if ($category_id > 0) {
        $sql .= " AND p.category_id = :category_id";
        $params[':category_id'] = $category_id;
    }

    // Add search filter
    if (!empty($search)) {
        $sql .= " AND p.name LIKE :search";
        $params[':search'] = '%' . $search . '%';
    }

    // Execute query
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => true,
        'data' => $products,
        'count' => count($products)
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>
