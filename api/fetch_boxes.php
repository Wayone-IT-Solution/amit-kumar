<?php
require '../inc/db.php';
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode([
            'status' => false,
            'message' => 'Invalid request method. Only GET is allowed.'
        ]);
        exit;
    }

    // Step 1: Get category ID from URL params
    $categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

    if ($categoryId <= 0) {
        echo json_encode([
            'status' => false,
            'message' => 'Invalid or missing category_id.'
        ]);
        exit;
    }

    // Step 2: Fetch the box_ids_json for the given category
    $stmt = $conn->prepare("SELECT box_ids_json FROM categories WHERE id = ?");
    $stmt->execute([$categoryId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row || empty($row['box_ids_json'])) {
        echo json_encode([
            'status' => false,
            'message' => 'No box IDs found for the given category.',
            'category_id' => $categoryId
        ]);
        exit;
    }

    // Step 3: Decode box_ids_json
    $boxIds = json_decode($row['box_ids_json'], true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($boxIds)) {
        echo json_encode([
            'status' => false,
            'message' => 'Invalid JSON format in box_ids_json.',
            'json' => $row['box_ids_json'],
            'error' => json_last_error_msg()
        ]);
        exit;
    }

    $boxIds = array_filter($boxIds, 'is_numeric');
    if (empty($boxIds)) {
        echo json_encode([
            'status' => false,
            'message' => 'No valid numeric box IDs found.',
            'box_ids_json' => $row['box_ids_json']
        ]);
        exit;
    }

    // Step 4: Fetch boxes by box IDs
    $placeholders = implode(',', array_fill(0, count($boxIds), '?'));
    $stmt = $conn->prepare("SELECT * FROM boxes WHERE id IN ($placeholders)");
    $stmt->execute($boxIds);
    $boxes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => true,
        'message' => 'Boxes fetched successfully',
        'category_id' => $categoryId,
        'box_ids' => $boxIds,
        'data' => $boxes
    ]);
} catch (PDOException $e) {
    error_log("DB Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => false,
        'message' => 'Database error while fetching boxes.',
        'error' => $e->getMessage()
    ]);
} catch (Throwable $e) {
    error_log("General Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => false,
        'message' => 'Unexpected server error.',
        'error' => $e->getMessage()
    ]);
}
