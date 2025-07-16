<?php
require '../inc/db.php'; // Adjust if necessary

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

    // Fetch categories
    $stmt = $conn->prepare("SELECT * FROM categories ORDER BY id DESC");
    $stmt->execute();
    $categories = $stmt->fetchAll();

    if (!$categories) {
        echo json_encode([
            'status' => true,
            'message' => 'No categories found',
            'data' => []
        ]);
        exit;
    }

    echo json_encode([
        'status' => true,
        'message' => 'Categories fetched successfully',
        'data' => $categories
    ]);
} catch (PDOException $e) {
    // Log the error and respond safely
    error_log("DB Error: " . $e->getMessage());
    http_response_code(500); // Internal Server Error
    echo json_encode([
        'status' => false,
        'message' => 'Something went wrong while fetching categories. Please try again later.'
    ]);
    exit;
} catch (Throwable $e) {
    // Catch unexpected errors
    error_log("General Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => false,
        'message' => 'An unexpected error occurred. Please try again later.'
    ]);
    exit;
}
?>
