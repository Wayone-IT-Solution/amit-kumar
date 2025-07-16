<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    exit('Unauthorized');
}

require_once '../inc/db.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    $stmt = $conn->prepare("SELECT id, title, description, valid_days, price, image, status FROM subscriptions WHERE id = ?");
    $stmt->execute([$id]);
    $subscription = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($subscription) {
        header('Content-Type: application/json');
        echo json_encode($subscription);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Subscription not found']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'ID parameter required']);
}
?> 