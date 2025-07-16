<?php
session_start();
require 'db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $order_id = intval($_POST['order_id']);
    $user_id = $_SESSION['user_id'];

    // Check if user owns the order and it is cancelable
    $stmt = $conn->prepare("SELECT order_status FROM orders WHERE id = :id AND user_id = :user_id");
    $stmt->execute(['id' => $order_id, 'user_id' => $user_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit;
    }

    if ($order['order_status'] === 'start_preparing') {
        echo json_encode(['success' => false, 'message' => 'Cannot cancel. Order is already being prepared.']);
        exit;
    }

    $update = $conn->prepare("UPDATE orders SET order_status = 'cancelled' WHERE id = :id AND user_id = :user_id");
    $update->execute(['id' => $order_id, 'user_id' => $user_id]);

    echo json_encode(['success' => true]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request']);
?>