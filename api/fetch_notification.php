<?php
require '../inc/db.php'; // Ensure $conn (PDO) is available
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['status' => false, 'message' => 'Only GET method is allowed.']);
        exit;
    }

    // Get user_id from query parameter
    $user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

    if ($user_id <= 0) {
        echo json_encode(['status' => false, 'message' => 'Invalid user ID.']);
        exit;
    }

    // Fetch all notifications for the user
    $stmt = $conn->prepare("SELECT id, title, message, is_read, created_at FROM notifications WHERE user_id = :user_id ORDER BY created_at DESC");
    $stmt->execute([':user_id' => $user_id]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => true,
        'count' => count($notifications),
        'notifications' => $notifications
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>
