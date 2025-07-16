<?php
/**
 * Payment Gateway Integration
 * This file handles payment processing for subscription orders
 * 
 * Supported Payment Gateways:
 * - Razorpay (India)
 * - Stripe (International)
 * - PayPal (International)
 * - Custom Gateway
 */

class PaymentGateway {
    private $config;
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
        $this->loadConfig();
    }
    
    /**
     * Load payment gateway configuration
     */
    private function loadConfig() {
        // You can store these in database or config file
        $this->config = [
            'razorpay' => [
                'key_id' => 'rzp_test_YOUR_KEY_ID', // Replace with your Razorpay key
                'key_secret' => 'YOUR_KEY_SECRET',   // Replace with your Razorpay secret
                'currency' => 'INR',
                'enabled' => true
            ],
            'stripe' => [
                'publishable_key' => 'pk_test_YOUR_STRIPE_KEY',
                'secret_key' => 'sk_test_YOUR_STRIPE_SECRET',
                'currency' => 'usd',
                'enabled' => false
            ],
            'paypal' => [
                'client_id' => 'YOUR_PAYPAL_CLIENT_ID',
                'client_secret' => 'YOUR_PAYPAL_SECRET',
                'currency' => 'USD',
                'enabled' => false
            ]
        ];
    }
    
    /**
     * Create payment order
     */
    public function createPaymentOrder($orderData) {
        $gateway = $orderData['gateway'] ?? 'razorpay';
        
        if (!$this->config[$gateway]['enabled']) {
            throw new Exception("Payment gateway not enabled");
        }
        
        switch ($gateway) {
            case 'razorpay':
                return $this->createRazorpayOrder($orderData);
            case 'stripe':
                return $this->createStripeOrder($orderData);
            case 'paypal':
                return $this->createPayPalOrder($orderData);
            default:
                throw new Exception("Unsupported payment gateway");
        }
    }
    
    /**
     * Create Razorpay payment order
     */
    private function createRazorpayOrder($orderData) {
        // Include Razorpay SDK
        require_once 'vendor/autoload.php'; // If using Composer
        
        try {
            $api = new Razorpay\Api\Api($this->config['razorpay']['key_id'], $this->config['razorpay']['key_secret']);
            
            $paymentData = [
                'amount' => $orderData['amount'] * 100, // Convert to paise
                'currency' => $this->config['razorpay']['currency'],
                'receipt' => $orderData['order_code'],
                'notes' => [
                    'subscription_id' => $orderData['subscription_id'],
                    'user_id' => $orderData['user_id']
                ]
            ];
            
            $razorpayOrder = $api->order->create($paymentData);
            
            return [
                'success' => true,
                'order_id' => $razorpayOrder['id'],
                'amount' => $orderData['amount'],
                'currency' => $this->config['razorpay']['currency'],
                'gateway' => 'razorpay'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Create Stripe payment order
     */
    private function createStripeOrder($orderData) {
        // Include Stripe SDK
        require_once 'vendor/autoload.php';
        
        try {
            \Stripe\Stripe::setApiKey($this->config['stripe']['secret_key']);
            
            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $orderData['amount'] * 100, // Convert to cents
                'currency' => $this->config['stripe']['currency'],
                'metadata' => [
                    'order_code' => $orderData['order_code'],
                    'subscription_id' => $orderData['subscription_id'],
                    'user_id' => $orderData['user_id']
                ]
            ]);
            
            return [
                'success' => true,
                'payment_intent_id' => $paymentIntent->id,
                'client_secret' => $paymentIntent->client_secret,
                'amount' => $orderData['amount'],
                'currency' => $this->config['stripe']['currency'],
                'gateway' => 'stripe'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Create PayPal payment order
     */
    private function createPayPalOrder($orderData) {
        // PayPal integration code here
        // This is a placeholder - implement based on PayPal SDK
        return [
            'success' => false,
            'error' => 'PayPal integration not implemented yet'
        ];
    }
    
    /**
     * Verify payment
     */
    public function verifyPayment($paymentData) {
        $gateway = $paymentData['gateway'] ?? 'razorpay';
        
        switch ($gateway) {
            case 'razorpay':
                return $this->verifyRazorpayPayment($paymentData);
            case 'stripe':
                return $this->verifyStripePayment($paymentData);
            case 'paypal':
                return $this->verifyPayPalPayment($paymentData);
            default:
                return ['success' => false, 'error' => 'Unsupported gateway'];
        }
    }
    
    /**
     * Verify Razorpay payment
     */
    private function verifyRazorpayPayment($paymentData) {
        try {
            $api = new Razorpay\Api\Api($this->config['razorpay']['key_id'], $this->config['razorpay']['key_secret']);
            
            $attributes = [
                'razorpay_order_id' => $paymentData['razorpay_order_id'],
                'razorpay_payment_id' => $paymentData['razorpay_payment_id'],
                'razorpay_signature' => $paymentData['razorpay_signature']
            ];
            
            $api->utility->verifyPaymentSignature($attributes);
            
            return ['success' => true, 'payment_id' => $paymentData['razorpay_payment_id']];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Verify Stripe payment
     */
    private function verifyStripePayment($paymentData) {
        try {
            \Stripe\Stripe::setApiKey($this->config['stripe']['secret_key']);
            
            $paymentIntent = \Stripe\PaymentIntent::retrieve($paymentData['payment_intent_id']);
            
            if ($paymentIntent->status === 'succeeded') {
                return ['success' => true, 'payment_id' => $paymentIntent->id];
            } else {
                return ['success' => false, 'error' => 'Payment not completed'];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Verify PayPal payment
     */
    private function verifyPayPalPayment($paymentData) {
        // PayPal verification code here
        return ['success' => false, 'error' => 'PayPal verification not implemented yet'];
    }
    
    /**
     * Process payment success
     */
    public function processPaymentSuccess($orderId, $paymentId, $gateway) {
        try {
            // Update subscription order status
            $stmt = $this->conn->prepare("UPDATE subscription_orders SET status = 'active' WHERE id = ?");
            $stmt->execute([$orderId]);
            
            // Log payment success
            $logStmt = $this->conn->prepare("
                INSERT INTO subscription_logs 
                (subscription_order_id, user_id, action, details, created_at) 
                VALUES (?, ?, 'payment_success', ?, NOW())
            ");
            $logStmt->execute([$orderId, $_SESSION['user_id'], "Payment successful via $gateway. Payment ID: $paymentId"]);
            
            return ['success' => true];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Process payment failure
     */
    public function processPaymentFailure($orderId, $error, $gateway) {
        try {
            // Log payment failure
            $logStmt = $this->conn->prepare("
                INSERT INTO subscription_logs 
                (subscription_order_id, user_id, action, details, created_at) 
                VALUES (?, ?, 'payment_failed', ?, NOW())
            ");
            $logStmt->execute([$orderId, $_SESSION['user_id'], "Payment failed via $gateway. Error: $error"]);
            
            return ['success' => true];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Get payment gateway configuration
     */
    public function getConfig($gateway = null) {
        if ($gateway) {
            return $this->config[$gateway] ?? null;
        }
        return $this->config;
    }
    
    /**
     * Get available payment methods
     */
    public function getAvailableMethods() {
        $methods = [];
        foreach ($this->config as $gateway => $config) {
            if ($config['enabled']) {
                $methods[] = $gateway;
            }
        }
        return $methods;
    }
}

// Example usage:
/*
$paymentGateway = new PaymentGateway($conn);

// Create payment order
$orderData = [
    'amount' => 1500.00,
    'currency' => 'INR',
    'order_code' => 'SUB20241201001',
    'subscription_id' => 1,
    'user_id' => 1,
    'gateway' => 'razorpay'
];

$result = $paymentGateway->createPaymentOrder($orderData);

if ($result['success']) {
    // Redirect to payment gateway
    echo "Payment order created: " . $result['order_id'];
} else {
    echo "Error: " . $result['error'];
}
*/
?> 