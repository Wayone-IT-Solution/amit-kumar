<?php
require '../inc/db.php'; // Ensure $conn (PDO) is available
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['status' => false, 'message' => 'Only POST method is allowed.']);
        exit;
    }

    // Decode JSON body
    $data = json_decode(file_get_contents("php://input"), true);

    $user_id = isset($data['user_id']) ? (int)$data['user_id'] : 0;
    $title = trim($data['title'] ?? '');
    $message = trim($data['message'] ?? '');

    // Validate inputs
    if ($user_id <= 0 || empty($title) || empty($message)) {
        echo json_encode(['status' => false, 'message' => 'Missing required fields.']);
        exit;
    }

    // Insert notification
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, title, message) VALUES (:user_id, :title, :message)");
    $stmt->execute([
        ':user_id' => $user_id,
        ':title' => $title,
        ':message' => $message
    ]);

    echo json_encode(['status' => true, 'message' => 'Notification sent successfully.']);

} catch (Exception $e) {
    echo json_encode(['status' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>
