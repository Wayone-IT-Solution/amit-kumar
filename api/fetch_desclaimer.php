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

    // Fetch the latest desclaimer with HTML content from CKEditor
    $stmt = $conn->prepare("SELECT id, content FROM desclaimer ORDER BY id DESC LIMIT 1");
    $stmt->execute();
    $desclaimer = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$desclaimer) {
        echo json_encode([
            'status' => false,
            'message' => 'No desclaimer content found.'
        ]);
        exit;
    }

    echo json_encode([
        'status' => true,
        'data' => $desclaimer
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>
