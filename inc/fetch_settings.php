<?php
session_start();
require 'db.php';

// Get user ID from session
$userId = $_SESSION['user_id'] ?? null;

// For minimum order amount, we don't need login
$requestType = $_GET['type'] ?? '';

if ($requestType === 'min_order' || !$userId) {
    // Return only minimum order amount without requiring login
    try {
        // Fetch minimum order amount from settings
        $stmt = $conn->prepare("SELECT value FROM settings WHERE key_name = 'min_order' LIMIT 1");
        $stmt->execute();
        $minOrderSetting = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $minOrderAmount = $minOrderSetting['value'] ?? 1500;
        
        echo json_encode([
            'success' => true,
            'min_order_amount' => $minOrderAmount
        ]);
        exit;
    } catch (PDOException $e) {
        error_log("Settings fetch error: " . $e->getMessage());
        echo json_encode(['error' => 'Database error occurred']);
        exit;
    }
}

// For other settings, require login
if (!$userId) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

try {
    // Fetch user's value from users table
    $stmt = $conn->prepare("SELECT user_value FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo json_encode(['error' => 'User not found']);
        exit;
    }
    
    $userValue = $user['user_value'];
    
    // Fetch admin value from settings table
    $stmt = $conn->prepare("SELECT value FROM settings WHERE key_name = 'admin_value' LIMIT 1");
    $stmt->execute();
    $setting = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $adminValue = $setting['value'] ?? null;
    
    // Fetch minimum order amount from settings
    $stmt = $conn->prepare("SELECT value FROM settings WHERE setting_name = 'min_order_amount' LIMIT 1");
    $stmt->execute();
    $minOrderSetting = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $minOrderAmount = $minOrderSetting['value'] ?? 1500;
    
    // Check if user value matches admin value
    $showZeroPrice = ($userValue == $adminValue);
    
    echo json_encode([
        'success' => true,
        'user_value' => $userValue,
        'admin_value' => $adminValue,
        'show_zero_price' => $showZeroPrice,
        'min_order_amount' => $minOrderAmount
    ]);
    
} catch (PDOException $e) {
    error_log("Settings fetch error: " . $e->getMessage());
    echo json_encode(['error' => 'Database error occurred']);
}
?> 