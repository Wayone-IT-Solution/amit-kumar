<?php
session_start();
require_once 'db.php';
require_once 'place_order.php'; // For send_sms function
require_once __DIR__ . '/../vendor/autoload.php'; // For Pusher
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please login to place subscription order']);
    exit;
}

try {
    // Validate required fields
    $required_fields = ['subscription_id', 'address_details', 'house_block', 'area_road', 'save_as', 'delivery_date', 'delivery_time', 'receiver_name', 'receiver_phone'];
    
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            throw new Exception("All fields are required");
        }
    }
    
    $userId = $_SESSION['user_id'];
    $subscription_id = (int)$_POST['subscription_id'];
    $address_details = trim($_POST['address_details']);
    $house_block = trim($_POST['house_block']);
    $area_road = trim($_POST['area_road']);
    $save_as = trim($_POST['save_as']);
    $delivery_date = $_POST['delivery_date'];
    $delivery_time = $_POST['delivery_time'];
    $receiver_name = trim($_POST['receiver_name']);
    $receiver_phone = trim($_POST['receiver_phone']);
    
    // Validate subscription exists and is active
    $stmt = $conn->prepare("SELECT * FROM subscriptions WHERE id = ? AND status = 1");
    $stmt->execute([$subscription_id]);
    $subscription = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$subscription) {
        throw new Exception("Invalid subscription plan selected");
    }
    
    // Validate delivery date (not in past)
    $delivery_date_obj = new DateTime($delivery_date);
    $today = new DateTime();
    if ($delivery_date_obj < $today) {
        throw new Exception("Delivery date cannot be in the past");
    }
    
    // Check if delivery date is blocked
    $stmt = $conn->prepare("SELECT COUNT(*) FROM blocked_slots WHERE blocked_date = ?");
    $stmt->execute([$delivery_date]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception("Selected delivery date is not available");
    }
    
    // Generate unique order code
    $order_code = 'SUB' . date('Ymd') . rand(1000, 9999);
    
    // Calculate expiry date
    $expiry_date = date('Y-m-d', strtotime("+{$subscription['valid_days']} days"));
    
    // Create subscription_orders table if it doesn't exist
    $create_table_sql = "
    CREATE TABLE IF NOT EXISTS `subscription_orders` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `order_code` varchar(50) NOT NULL,
        `user_id` int(11) NOT NULL,
        `subscription_id` int(11) NOT NULL,
        `subscription_title` varchar(255) NOT NULL,
        `subscription_price` decimal(10,2) NOT NULL,
        `valid_days` int(11) NOT NULL,
        `expiry_date` date NOT NULL,
        `address_details` text NOT NULL,
        `house_block` varchar(255) NOT NULL,
        `area_road` varchar(255) NOT NULL,
        `save_as` varchar(50) NOT NULL,
        `delivery_date` date NOT NULL,
        `delivery_time` varchar(50) NOT NULL,
        `receiver_name` varchar(255) NOT NULL,
        `receiver_phone` varchar(20) NOT NULL,
        `status` enum('active','paused','cancelled','expired') NOT NULL DEFAULT 'active',
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `order_code` (`order_code`),
        KEY `user_id` (`user_id`),
        KEY `subscription_id` (`subscription_id`),
        KEY `status` (`status`),
        KEY `delivery_date` (`delivery_date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $conn->exec($create_table_sql);
    
    // Insert subscription order
    $stmt = $conn->prepare("INSERT INTO subscription_orders (
        order_code, user_id, subscription_id, subscription_title, subscription_price, 
        valid_days, expiry_date, address_details, house_block, area_road, save_as,
        delivery_date, delivery_time, receiver_name, receiver_phone, status
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')");
    
    $stmt->execute([
        $order_code, $userId, $subscription_id, $subscription['title'], $subscription['price'],
        $subscription['valid_days'], $expiry_date, $address_details, $house_block, $area_road, $save_as,
        $delivery_date, $delivery_time, $receiver_name, $receiver_phone
    ]);
    
    $orderId = $conn->lastInsertId();
    
    // Create subscription_logs table if it doesn't exist
    $create_logs_table_sql = "
    CREATE TABLE IF NOT EXISTS `subscription_logs` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `subscription_order_id` int(11) NOT NULL,
        `user_id` int(11) NOT NULL,
        `admin_id` int(11) DEFAULT NULL,
        `action` varchar(50) NOT NULL COMMENT 'created, activated, expired, cancelled, manually_expired',
        `details` text DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `subscription_order_id` (`subscription_order_id`),
        KEY `user_id` (`user_id`),
        KEY `admin_id` (`admin_id`),
        KEY `action` (`action`),
        KEY `created_at` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $conn->exec($create_logs_table_sql);
    
    // Log the subscription creation
    $logStmt = $conn->prepare("
        INSERT INTO subscription_logs 
        (subscription_order_id, user_id, action, details, created_at) 
        VALUES (?, ?, 'created', ?, NOW())
    ");
    $logStmt->execute([$orderId, $userId, "Subscription order #$orderId created successfully"]);

    // Send SMS to admin
    // $adminPhone = '9889090837';
    // $adminMsg_en = "New subscription order placed: $order_code by user ID $userId. Delivery Date: $delivery_date.";
    // $adminMsg_hi = "नई सब्सक्रिप्शन ऑर्डर: $order_code, उपयोगकर्ता आईडी $userId द्वारा। डिलीवरी तिथि: $delivery_date.";
    // $adminMsg_bi_sms = $adminMsg_en . "\n\n" . $adminMsg_hi;
    // send_sms($adminPhone, $adminMsg_bi_sms);

    // Send email to user
    $user_email = '';
    $stmt = $conn->prepare('SELECT email FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $user_email = $stmt->fetchColumn();
    error_log('Trying to send mail to user: ' . $user_email . ' from sender: j83367806@gmail.com');
    if ($user_email) {
        $logo_url = 'https://amitdairyandsweets.com/amit-kumar/assets/img/logo.webp';
        $subject = "Subscription Order Placed - Amit Dairy & Sweets";
        $body = '
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Subscription Confirmation - Amit Dairy & Sweets</title>
</head>
<body style="background:#f4f8fb;margin:0;padding:0;font-family:Arial,sans-serif;">
  <div style="max-width:600px;margin:40px auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.07);">
    <div style="padding:32px 24px 0 24px;text-align:center;">
      <img src="'.$logo_url.'" alt="Amit Dairy & Sweets" style="width:90px;margin-bottom:18px;">
      <h1 style="font-size:2.2rem;margin:0 0 8px 0;color:#333;">Subscription Activated!</h1>
      <p style="font-size:1.1rem;color:#444;">Dear <b>'.htmlspecialchars($receiver_name).'</b>,<br>Your subscription order has been placed successfully.</p>
      <div style="margin:18px 0 0 0;font-size:1.1rem;">
        <b>Order ID:</b> <span style="color:#6c63ff;">'.htmlspecialchars($order_code).'</span><br>
        <b>Plan:</b> '.htmlspecialchars($subscription['title']).'<br>
        <b>Delivery Date:</b> '.htmlspecialchars($delivery_date).'<br>
        <b>Expiry Date:</b> '.htmlspecialchars($expiry_date).'
      </div>
    </div>
    <div style="padding:24px;background:#f7f7f7;">
      <h3 style="margin:0 0 8px 0;color:#333;">Shipping Information</h3>
      <p style="margin:0 0 4px 0;color:#444;"><b>Address:</b> '.htmlspecialchars($address_details . ', ' . $house_block . ', ' . $area_road).'</p>
      <p style="margin:0 0 4px 0;color:#444;"><b>Delivery Time:</b> '.htmlspecialchars($delivery_time).'</p>
    </div>
    <div style="padding:24px;text-align:center;">
      <p style="margin:0 0 8px 0;color:#333;">Thank you for subscribing!<br><b>Amit Dairy & Sweets</b></p>
      <small style="color:#888;">If you have any questions, reply to this email.</small>
    </div>
  </div>
</body>
</html>
';
        $result = send_email($user_email, $subject, $body);
        error_log('Mail send result: ' . var_export($result, true));
    } else {
        error_log('User email is blank or invalid!');
    }

    // Send email to admin
    $admin_email = 'admin@amitdairyandsweets.com';
    $admin_subject = "New Subscription Order: $order_code";
    $admin_body = "A new subscription order has been placed.<br>Order Code: <b>$order_code</b><br>User: <b>$receiver_name</b> ($user_email)<br>Plan: <b>{$subscription['title']}</b><br>Delivery Date: <b>$delivery_date</b>";
    send_email($admin_email, $admin_subject, $admin_body);
    
    // Check if this is a 30-day subscription and set up auto-expiration
    if ($subscription['valid_days'] == 30) {
        // Log the 30-day subscription setup
        $logStmt = $conn->prepare("
            INSERT INTO subscription_logs 
            (subscription_order_id, user_id, action, details, created_at) 
            VALUES (?, ?, 'setup_30day', ?, NOW())
        ");
        $logStmt->execute([$orderId, $userId, "30-day subscription auto-expiration setup for order #$orderId"]);
    }
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Subscription order placed successfully!',
        'order_code' => $order_code,
        'plan_title' => $subscription['title'],
        'expiry_date' => $expiry_date,
        'delivery_date' => $delivery_date,
        'is_30day' => $subscription['valid_days'] == 30,
        'order_id' => $orderId
    ]);
    
} catch (PDOException $e) {
    error_log("Database error in place_subscription_order: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Database error occurred. Please try again.'
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}
?> 