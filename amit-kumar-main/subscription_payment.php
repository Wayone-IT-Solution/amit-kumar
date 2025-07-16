<?php
session_start();
require_once 'inc/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Check if subscription order exists in session
if (!isset($_SESSION['subscription_order'])) {
    header("Location: subscription");
    exit;
}

$order = $_SESSION['subscription_order'];
$userId = $_SESSION['user_id'];

// Get user details
$stmt = $conn->prepare("SELECT fullname, email, phone FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle payment success
if (isset($_GET['payment_status']) && $_GET['payment_status'] === 'success') {
    try {
        // Update subscription status to active
        $stmt = $conn->prepare("UPDATE subscription_orders SET status = 'active' WHERE id = ?");
        $stmt->execute([$order['order_id']]);
        
        // Log the activation
        $logStmt = $conn->prepare("
            INSERT INTO subscription_logs 
            (subscription_order_id, user_id, action, details, created_at) 
            VALUES (?, ?, 'activated', ?, NOW())
        ");
        $logStmt->execute([$order['order_id'], $userId, "Subscription activated after successful payment"]);
        
        // Clear session
        unset($_SESSION['subscription_order']);
        
        $success_msg = "Payment successful! Your subscription is now active.";
        
    } catch (Exception $e) {
        $error_msg = "Error updating subscription: " . $e->getMessage();
    }
}

// Handle payment failure
if (isset($_GET['payment_status']) && $_GET['payment_status'] === 'failed') {
    $error_msg = "Payment failed. Please try again.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Payment - Amit Dairy & Sweets</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta name="description" content="Complete your subscription payment">
    <meta name="keywords" content="payment, subscription, dairy">

    <!-- Favicons -->
    <link href="assets/img/logo.webp" rel="icon">
    <link href="assets/img/logo.webp" rel="apple-touch-icon">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/vendor/aos/aos.css" rel="stylesheet">
    <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

    <!-- Template Main CSS File -->
    <link href="assets/css/main.css" rel="stylesheet">

    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    <style>
        .payment-hero {
            background: linear-gradient(135deg, #D6B669 0%, #f4d03f 100%);
            padding: 60px 0;
            position: relative;
            overflow: hidden;
        }
        
        .payment-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('assets/img/pattern.png') repeat;
            opacity: 0.1;
        }

        .payment-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border: none;
            overflow: hidden;
        }

        .order-summary {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e9ecef;
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .order-label {
            font-weight: 600;
            color: #6c757d;
        }

        .order-value {
            font-weight: 700;
            color: #2c3e50;
        }

        .total-amount {
            background: linear-gradient(45deg, #D6B669, #f4d03f);
            color: white;
            padding: 1rem;
            border-radius: 15px;
            text-align: center;
            font-size: 1.5rem;
            font-weight: 700;
            margin: 1rem 0;
        }

        .payment-methods {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin: 2rem 0;
        }

        .payment-method {
            border: 2px solid #e9ecef;
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .payment-method:hover {
            border-color: #D6B669;
            transform: translateY(-2px);
        }

        .payment-method.selected {
            border-color: #D6B669;
            background: #f8f9fa;
        }

        .payment-method i {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: #D6B669;
        }

        .pay-btn {
            background: linear-gradient(45deg, #28a745, #20c997);
            border: none;
            border-radius: 25px;
            padding: 1rem 2rem;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 1rem;
        }

        .pay-btn:hover {
            background: linear-gradient(45deg, #20c997, #28a745);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
            color: white;
        }

        .cancel-btn {
            background: linear-gradient(45deg, #dc3545, #fd7e14);
            border: none;
            border-radius: 25px;
            padding: 1rem 2rem;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 1rem;
        }

        .cancel-btn:hover {
            background: linear-gradient(45deg, #fd7e14, #dc3545);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.4);
            color: white;
        }

        .success-alert {
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
            border: none;
            border-radius: 15px;
        }

        .error-alert {
            background: linear-gradient(45deg, #dc3545, #fd7e14);
            color: white;
            border: none;
            border-radius: 15px;
        }
    </style>
</head>

<body class="index-page">
    <?php include('inc/header.php'); ?>

    <!-- Hero Section -->
    <main class="main">
        <section class="payment-hero">
            <div class="container text-center py-5">
                <div class="d-flex justify-content-center align-items-center mb-3">
                    <img src="assets/img/Vector.png" alt="" class="me-2">
                    <h2 class="m-0 text-white">Complete Payment</h2>
                    <img src="assets/img/Vector (1).png" alt="" class="ms-2">
                </div>
                <nav aria-label="breadcrumb" class="d-flex justify-content-center">
                    <ol class="breadcrumb bg-transparent">
                        <li class="breadcrumb-item"><a href="index" class="text-light fw-semibold text-decoration-none">Home</a></li>
                        <li class="breadcrumb-item"><a href="subscription" class="text-light fw-semibold text-decoration-none">Subscription</a></li>
                        <li class="breadcrumb-item active text-light fw-semibold" aria-current="page">Payment</li>
                    </ol>
                </nav>
            </div>
        </section>
    </main>

    <!-- Payment Section -->
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <?php if (isset($success_msg)): ?>
                        <div class="alert success-alert text-center mb-4">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            <?= $success_msg ?>
                        </div>
                        <div class="text-center">
                            <a href="user-dashboard.php" class="btn pay-btn">Go to Dashboard</a>
                        </div>
                    <?php elseif (isset($error_msg)): ?>
                        <div class="alert error-alert text-center mb-4">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <?= $error_msg ?>
                        </div>
                        <div class="text-center">
                            <a href="subscription.php" class="btn pay-btn">Try Again</a>
                        </div>
                    <?php else: ?>
                        <div class="payment-card">
                            <div class="card-body p-4">
                                <h3 class="text-center mb-4">
                                    <i class="bi bi-credit-card-fill text-warning me-2"></i>
                                    Complete Your Payment
                                </h3>

                                <!-- Order Summary -->
                                <div class="order-summary">
                                    <h5 class="mb-3">
                                        <i class="bi bi-receipt me-2"></i>
                                        Order Summary
                                    </h5>
                                    
                                    <div class="order-item">
                                        <span class="order-label">Order Code:</span>
                                        <span class="order-value"><?= htmlspecialchars($order['order_code']) ?></span>
                                    </div>
                                    
                                    <div class="order-item">
                                        <span class="order-label">Subscription Plan:</span>
                                        <span class="order-value"><?= htmlspecialchars($order['subscription_title']) ?></span>
                                    </div>
                                    
                                    <div class="order-item">
                                        <span class="order-label">Validity Period:</span>
                                        <span class="order-value"><?= $order['valid_days'] ?> Days</span>
                                    </div>
                                    
                                    <div class="order-item">
                                        <span class="order-label">Delivery Date:</span>
                                        <span class="order-value"><?= date('M d, Y', strtotime($order['delivery_date'])) ?></span>
                                    </div>
                                    
                                    <div class="order-item">
                                        <span class="order-label">Delivery Time:</span>
                                        <span class="order-value"><?= htmlspecialchars($order['delivery_time']) ?></span>
                                    </div>
                                    
                                    <div class="order-item">
                                        <span class="order-label">Receiver:</span>
                                        <span class="order-value"><?= htmlspecialchars($order['receiver_name']) ?></span>
                                    </div>
                                    
                                    <div class="total-amount">
                                        Total Amount: ₹<?= number_format($order['subscription_price'], 2) ?>
                                    </div>
                                </div>

                                <!-- Payment Methods -->
                                <div class="payment-methods">
                                    <div class="payment-method" onclick="selectPaymentMethod('razorpay')">
                                        <i class="bi bi-credit-card"></i>
                                        <h6>Credit/Debit Card</h6>
                                        <small class="text-muted">Visa, MasterCard, RuPay</small>
                                    </div>
                                    
                                    <div class="payment-method" onclick="selectPaymentMethod('upi')">
                                        <i class="bi bi-phone"></i>
                                        <h6>UPI Payment</h6>
                                        <small class="text-muted">Google Pay, PhonePe, Paytm</small>
                                    </div>
                                    
                                    <div class="payment-method" onclick="selectPaymentMethod('netbanking')">
                                        <i class="bi bi-bank"></i>
                                        <h6>Net Banking</h6>
                                        <small class="text-muted">All Major Banks</small>
                                    </div>
                                    
                                    <div class="payment-method" onclick="selectPaymentMethod('wallet')">
                                        <i class="bi bi-wallet2"></i>
                                        <h6>Digital Wallet</h6>
                                        <small class="text-muted">Paytm, PhonePe, Amazon Pay</small>
                                    </div>
                                </div>

                                <!-- Payment Buttons -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <button type="button" class="pay-btn" onclick="processPayment()">
                                            <i class="bi bi-lock-fill me-2"></i>
                                            Pay Securely ₹<?= number_format($order['subscription_price'], 2) ?>
                                        </button>
                                    </div>
                                    <div class="col-md-6">
                                        <button type="button" class="cancel-btn" onclick="cancelPayment()">
                                            <i class="bi bi-x-circle me-2"></i>
                                            Cancel Payment
                                        </button>
                                    </div>
                                </div>

                                <!-- Security Notice -->
                                <div class="text-center mt-4">
                                    <small class="text-muted">
                                        <i class="bi bi-shield-check me-1"></i>
                                        Your payment is secured with SSL encryption
                                    </small>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <?php include('inc/footer.php'); ?>

    <!-- Scripts -->
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/vendor/aos/aos.js"></script>
    <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });

        let selectedPaymentMethod = 'razorpay';

        function selectPaymentMethod(method) {
            selectedPaymentMethod = method;
            
            // Remove selected class from all methods
            document.querySelectorAll('.payment-method').forEach(el => {
                el.classList.remove('selected');
            });
            
            // Add selected class to clicked method
            event.currentTarget.classList.add('selected');
        }

        function processPayment() {
            Swal.fire({
                title: 'Processing Payment...',
                text: 'Please wait while we process your payment securely',
                icon: 'info',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Simulate payment processing (replace with actual payment gateway)
            setTimeout(() => {
                // For demo purposes, we'll simulate a successful payment
                // In real implementation, integrate with Razorpay, Stripe, or other payment gateway
                
                Swal.fire({
                    icon: 'success',
                    title: 'Payment Successful! 🎉',
                    text: 'Your subscription has been activated successfully!',
                    confirmButtonText: 'Continue',
                    confirmButtonColor: '#28a745'
                }).then(() => {
                    // Redirect to success page
                    window.location.href = 'subscription_payment.php?payment_status=success';
                });
            }, 3000);
        }

        function cancelPayment() {
            Swal.fire({
                title: 'Cancel Payment?',
                text: 'Are you sure you want to cancel this payment?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Cancel',
                cancelButtonText: 'No, Continue'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Clear session and redirect
                    window.location.href = 'subscription.php';
                }
            });
        }

        // Auto-select first payment method
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelector('.payment-method').classList.add('selected');
        });
    </script>
</body>
</html> 