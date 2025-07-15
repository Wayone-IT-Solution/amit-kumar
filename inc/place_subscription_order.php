<?php
session_start();
require_once 'db.php';
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