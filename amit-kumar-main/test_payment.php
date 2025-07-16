<?php
session_start();
require_once 'inc/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "User not logged in. Please login first.";
    exit;
}

// Check if subscription order exists in session
if (!isset($_SESSION['subscription_order'])) {
    echo "No subscription order found. Please go back to subscription page.";
    exit;
}

$order = $_SESSION['subscription_order'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Payment Test - Amit Dairy & Sweets</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    
    <!-- Bootstrap CSS -->
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0">
                            <i class="bi bi-check-circle me-2"></i>
                            Payment Page Test - Working!
                        </h4>
                    </div>
                    <div class="card-body">
                        <h5>Subscription Order Details:</h5>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <strong>Order Code:</strong> <?= htmlspecialchars($order['order_code']) ?>
                            </li>
                            <li class="list-group-item">
                                <strong>Plan:</strong> <?= htmlspecialchars($order['subscription_title']) ?>
                            </li>
                            <li class="list-group-item">
                                <strong>Price:</strong> ₹<?= number_format($order['subscription_price'], 2) ?>
                            </li>
                            <li class="list-group-item">
                                <strong>Validity:</strong> <?= $order['valid_days'] ?> Days
                            </li>
                            <li class="list-group-item">
                                <strong>Delivery Date:</strong> <?= date('M d, Y', strtotime($order['delivery_date'])) ?>
                            </li>
                        </ul>
                        
                        <div class="mt-4">
                            <h6>Payment Status: <span class="badge bg-warning">Pending</span></h6>
                            <p class="text-muted">This is a test page to verify the payment system is working.</p>
                        </div>
                        
                        <div class="mt-4">
                            <a href="subscription" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-2"></i>
                                Back to Subscriptions
                            </a>
                            <a href="user-dashboard" class="btn btn-primary">
                                <i class="bi bi-house me-2"></i>
                                Go to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html> 