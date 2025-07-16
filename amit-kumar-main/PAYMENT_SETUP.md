# Payment Gateway Setup Guide

## Overview
This guide explains how to integrate payment gateways with the subscription system.

## Supported Payment Gateways

### 1. Razorpay (Recommended for India)
- **Website**: https://razorpay.com
- **Documentation**: https://razorpay.com/docs/
- **Supported Currencies**: INR

### 2. Stripe (International)
- **Website**: https://stripe.com
- **Documentation**: https://stripe.com/docs
- **Supported Currencies**: USD, EUR, GBP, and many more

### 3. PayPal (International)
- **Website**: https://paypal.com
- **Documentation**: https://developer.paypal.com/
- **Supported Currencies**: USD, EUR, GBP, and many more

## Installation Steps

### Step 1: Install Composer (if not already installed)
```bash
# Download Composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
```

### Step 2: Install Payment Gateway SDKs

#### For Razorpay:
```bash
composer require razorpay/razorpay
```

#### For Stripe:
```bash
composer require stripe/stripe-php
```

#### For PayPal:
```bash
composer require paypal/rest-api-sdk-php
```

### Step 3: Configure Payment Gateway

#### Razorpay Configuration:
1. Sign up at https://razorpay.com
2. Get your API keys from the dashboard
3. Update `inc/payment_gateway.php`:

```php
'razorpay' => [
    'key_id' => 'rzp_test_YOUR_ACTUAL_KEY_ID',
    'key_secret' => 'YOUR_ACTUAL_KEY_SECRET',
    'currency' => 'INR',
    'enabled' => true
],
```

#### Stripe Configuration:
1. Sign up at https://stripe.com
2. Get your API keys from the dashboard
3. Update `inc/payment_gateway.php`:

```php
'stripe' => [
    'publishable_key' => 'pk_test_YOUR_ACTUAL_KEY',
    'secret_key' => 'sk_test_YOUR_ACTUAL_SECRET',
    'currency' => 'usd',
    'enabled' => true
],
```

### Step 4: Update Payment Page

Edit `subscription_payment.php` to integrate with actual payment gateway:

```php
// Add this at the top of the file
require_once 'inc/payment_gateway.php';

// Initialize payment gateway
$paymentGateway = new PaymentGateway($conn);

// Create payment order
$orderData = [
    'amount' => $order['subscription_price'],
    'currency' => 'INR',
    'order_code' => $order['order_code'],
    'subscription_id' => $order['subscription_id'],
    'user_id' => $userId,
    'gateway' => 'razorpay' // or 'stripe', 'paypal'
];

$paymentResult = $paymentGateway->createPaymentOrder($orderData);
```

## Integration Examples

### Razorpay Integration

#### Frontend (JavaScript):
```javascript
// Add this to subscription_payment.php
function processRazorpayPayment() {
    const options = {
        key: 'rzp_test_YOUR_KEY_ID',
        amount: <?= $order['subscription_price'] * 100 ?>,
        currency: 'INR',
        name: 'Amit Dairy & Sweets',
        description: 'Subscription Payment',
        order_id: '<?= $paymentResult['order_id'] ?>',
        handler: function (response) {
            // Handle success
            window.location.href = 'payment_success.php?payment_id=' + response.razorpay_payment_id;
        },
        prefill: {
            name: '<?= $user['fullname'] ?>',
            email: '<?= $user['email'] ?>',
            contact: '<?= $user['phone'] ?>'
        },
        theme: {
            color: '#D6B669'
        }
    };
    
    const rzp = new Razorpay(options);
    rzp.open();
}
```

#### Backend (PHP):
```php
// payment_success.php
require_once 'inc/payment_gateway.php';

$paymentGateway = new PaymentGateway($conn);

$paymentData = [
    'razorpay_order_id' => $_POST['razorpay_order_id'],
    'razorpay_payment_id' => $_POST['razorpay_payment_id'],
    'razorpay_signature' => $_POST['razorpay_signature'],
    'gateway' => 'razorpay'
];

$verification = $paymentGateway->verifyPayment($paymentData);

if ($verification['success']) {
    $paymentGateway->processPaymentSuccess($orderId, $verification['payment_id'], 'razorpay');
    echo "Payment successful!";
} else {
    echo "Payment verification failed: " . $verification['error'];
}
```

### Stripe Integration

#### Frontend (JavaScript):
```javascript
// Add Stripe.js
<script src="https://js.stripe.com/v3/"></script>

const stripe = Stripe('pk_test_YOUR_STRIPE_KEY');

function processStripePayment() {
    stripe.confirmCardPayment('<?= $paymentResult['client_secret'] ?>', {
        payment_method: {
            card: elements.getElement('card'),
            billing_details: {
                name: '<?= $user['fullname'] ?>',
                email: '<?= $user['email'] ?>'
            }
        }
    }).then(function(result) {
        if (result.error) {
            console.error(result.error.message);
        } else {
            if (result.paymentIntent.status === 'succeeded') {
                window.location.href = 'payment_success.php?payment_id=' + result.paymentIntent.id;
            }
        }
    });
}
```

## Testing

### Test Cards

#### Razorpay Test Cards:
- **Success**: 4111 1111 1111 1111
- **Failure**: 4000 0000 0000 0002
- **CVV**: Any 3 digits
- **Expiry**: Any future date

#### Stripe Test Cards:
- **Success**: 4242 4242 4242 4242
- **Failure**: 4000 0000 0000 0002
- **CVV**: Any 3 digits
- **Expiry**: Any future date

## Security Considerations

1. **Never expose secret keys** in frontend code
2. **Always verify payments** on the backend
3. **Use HTTPS** in production
4. **Validate all inputs** before processing
5. **Log all payment activities** for audit

## Production Checklist

- [ ] Switch to live API keys
- [ ] Enable HTTPS
- [ ] Set up webhook endpoints
- [ ] Configure error handling
- [ ] Test payment flows thoroughly
- [ ] Set up monitoring and alerts
- [ ] Review security measures

## Troubleshooting

### Common Issues:

1. **Payment not processing**
   - Check API keys are correct
   - Verify currency matches gateway
   - Check network connectivity

2. **Verification failing**
   - Ensure signature verification is correct
   - Check webhook endpoints
   - Verify payment data integrity

3. **Orders not updating**
   - Check database connections
   - Verify SQL queries
   - Check error logs

### Support:

- **Razorpay**: https://razorpay.com/support/
- **Stripe**: https://support.stripe.com/
- **PayPal**: https://developer.paypal.com/support/

## Quick Start (Demo Mode)

For testing without real payment gateways:

1. Update `subscription_payment.php` to use demo mode
2. Set `enabled` to `false` for all gateways in `payment_gateway.php`
3. Use the existing demo payment flow
4. Test the complete subscription process

This allows you to test the subscription flow without setting up payment gateways immediately. 