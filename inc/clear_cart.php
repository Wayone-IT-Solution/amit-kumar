<?php
session_start();
require_once 'db.php';

// Clear cart after successful payment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Clear session cart
        if (isset($_SESSION['cart'])) {
            unset($_SESSION['cart']);
        }
        
        // Clear guest cart if exists
        if (isset($_SESSION['guest_cart'])) {
            unset($_SESSION['guest_cart']);
        }
        
        // Clear database cart for logged in users
        if (isset($_SESSION['user_id'])) {
            $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
        }
        
        echo 'success';
    } catch (Exception $e) {
        http_response_code(500);
        echo 'error: ' . $e->getMessage();
    }
} else {
    http_response_code(405);
    echo 'Method not allowed';
}
?> 