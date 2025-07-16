<?php
require '../../inc/db.php';
require_once '../../inc/send_sms.php';

$isAjax = (
    (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
    (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = intval($_POST['order_id']);
    $order_status = $_POST['order_status'];
    $allowed = ['pending', 'delivered', 'cancelled', 'start_preparing'];
    if (in_array($order_status, $allowed)) {
        $stmt = $conn->prepare("UPDATE orders SET order_status = ? WHERE id = ?");
        $success = $stmt->execute([$order_status, $order_id]);
        // Send SMS to admin when order is confirmed as delivered or start_preparing
        if ($success && in_array($order_status, ['delivered', 'start_preparing'])) {
            // Fetch order details for SMS
            $orderStmt = $conn->prepare("SELECT order_code, receiver_name, receiver_phone FROM orders WHERE id = ?");
            $orderStmt->execute([$order_id]);
            $order = $orderStmt->fetch(PDO::FETCH_ASSOC);
            if ($order) {
                $msg = "Order Confirmed: #{$order['order_code']} for {$order['receiver_name']} ({$order['receiver_phone']})";
                send_sms($msg);
            }
        }
        if ($isAjax) {
            header('Content-Type: application/json');
            if ($success) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Database update failed.']);
            }
            exit;
        } else {
            header("Location: ../orders-list");
            exit;
        }
    } else {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid status value.']);
            exit;
        } else {
            header("Location: ../orders-list");
            exit;
        }
    }
}

if ($isAjax) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
} else {
    header("Location: ../orders-list");
    exit;
}
