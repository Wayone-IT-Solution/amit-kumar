<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

try {
    // Clear subscription payment info from session
    if (isset($_SESSION['subscription_payment_info'])) {
        unset($_SESSION['subscription_payment_info']);
    }
    
    // Also clear subscription order from session if exists
    if (isset($_SESSION['subscription_order'])) {
        unset($_SESSION['subscription_order']);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Payment information cleared successfully'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?> 