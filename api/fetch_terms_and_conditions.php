<?php
require '../inc/db.php'; // Ensure $conn (PDO) is available
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode([
            'status' => false,
            'message' => 'Only GET method is allowed.'
        ]);
        exit;
    }

    // Fetch the latest terms_and_conditions with HTML content from CKEditor
    $stmt = $conn->prepare("SELECT id, content FROM terms_and_conditions ORDER BY id DESC LIMIT 1");
    $stmt->execute();
    $terms_and_conditions = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$terms_and_conditions) {
        echo json_encode([
            'status' => false,
            'message' => 'No terms_and_conditions content found.'
        ]);
        exit;
    }

    echo json_encode([
        'status' => true,
        'data' => $terms_and_conditions
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>
