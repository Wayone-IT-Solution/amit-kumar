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

    // Fetch the latest refund_policy with HTML content from CKEditor
    $stmt = $conn->prepare("SELECT id, content FROM refund_policy ORDER BY id DESC LIMIT 1");
    $stmt->execute();
    $refund_policy = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$refund_policy) {
        echo json_encode([
            'status' => false,
            'message' => 'No refund_policy content found.'
        ]);
        exit;
    }

    echo json_encode([
        'status' => true,
        'data' => $refund_policy
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>
