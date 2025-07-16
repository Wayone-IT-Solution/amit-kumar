<?php
session_start();
require 'db.php';

// Get user ID from session
$userId = $_SESSION['user_id'] ?? null;

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
    $stmt = $conn->prepare("SELECT value FROM settings WHERE setting_name = 'admin_value' LIMIT 1");
    $stmt->execute();
    $setting = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $adminValue = $setting['value'] ?? null;
    
    // Check if user value matches admin value
    $showZeroPrice = ($userValue == $adminValue);
    
    echo json_encode([
        'success' => true,
        'user_value' => $userValue,
        'admin_value' => $adminValue,
        'show_zero_price' => $showZeroPrice
    ]);
    
} catch (PDOException $e) {
    error_log("Settings fetch error: " . $e->getMessage());
    echo json_encode(['error' => 'Database error occurred']);
}
?> 