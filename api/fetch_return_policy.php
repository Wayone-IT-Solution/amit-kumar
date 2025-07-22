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

    // Fetch the latest return_policy with HTML content from CKEditor
    $stmt = $conn->prepare("SELECT id, content FROM return_policy ORDER BY id DESC LIMIT 1");
    $stmt->execute();
    $return_policy = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$return_policy) {
        echo json_encode([
            'status' => false,
            'message' => 'No return_policy content found.'
        ]);
        exit;
    }

    echo json_encode([
        'status' => true,
        'data' => $return_policy
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>
