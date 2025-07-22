<?php
require_once 'db.php';
// If your send_sms/send_email are in another file, require them here
// require_once 'place_order.php';

// Admin contact info
$adminPhone = '9889090837';
$adminEmail = 'jatin@wayone.co.in';

$today = date('Y-m-d');
$tomorrow = date('Y-m-d', strtotime('+1 day'));
$dayAfterTomorrow = date('Y-m-d', strtotime('+2 days'));

// 1 day remaining
$stmt = $conn->prepare("SELECT * FROM orders WHERE delivery_date = ? AND (order_status = 'pending' OR order_status = 'confirmed')");
$stmt->execute([$tomorrow]);
$orders1 = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2 days remaining
$stmt = $conn->prepare("SELECT * FROM orders WHERE delivery_date = ? AND (order_status = 'pending' OR order_status = 'confirmed')");
$stmt->execute([$dayAfterTomorrow]);
$orders2 = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($orders1 as $order) {
    // Send to user
    $userMsg = "Dear {$order['receiver_name']}, your order ({$order['order_code']}) will be delivered tomorrow ({$order['delivery_date']}).";
    send_email($order['receiver_email'], 'Order Delivery Reminder', $userMsg);

    // Send to admin
    $adminMsg = "Order {$order['order_code']} is scheduled for delivery tomorrow ({$order['delivery_date']}).";
    send_email($adminEmail, 'Order Delivery Reminder', $adminMsg);
}

foreach ($orders2 as $order) {
    // Send to user
    $userMsg = "Dear {$order['receiver_name']}, your order ({$order['order_code']}) will be delivered in 2 days ({$order['delivery_date']}).";
    send_email($order['receiver_email'], 'Order Delivery Reminder', $userMsg);

    // Send to admin
    $adminMsg = "Order {$order['order_code']} is scheduled for delivery in 2 days ({$order['delivery_date']}).";
    send_email($adminEmail, 'Order Delivery Reminder', $adminMsg);
}

echo "Reminders sent for orders with delivery dates: $tomorrow and $dayAfterTomorrow\n"; 