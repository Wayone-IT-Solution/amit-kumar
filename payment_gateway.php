<?php
session_start();
require_once 'inc/db.php';

$order_code = $_GET['order_code'] ?? '';
$amount = $_GET['amount'] ?? 0;

if (!$order_code || !$amount) {
    header('Location: index');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Gateway - Amit Dairy & Sweets</title>
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    
    <!-- SMS Notifications -->
    <link rel="stylesheet" href="assets/css/sms-notifications.css">
    <script src="assets/js/sms-notifications.js"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/html5-qrcode"></script>
    
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        .payment-container {
            max-width: 600px;
            margin: 50px auto;
        }
        .payment-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .payment-header {
            background: linear-gradient(135deg, #d1a94a 0%, #c19a3a 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .payment-body {
            padding: 30px;
        }
        .payment-method {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .payment-method:hover {
            border-color: #d1a94a;
            background-color: #fff8e1;
        }
        .payment-method.selected {
            border-color: #d1a94a;
            background-color: #fff8e1;
        }
        .payment-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            border-radius: 8px;
            margin-right: 15px;
        }
        .form-control {
            border-radius: 8px;
            border: 1px solid #e9ecef;
            padding: 12px 15px;
        }
        .form-control:focus {
            border-color: #d1a94a;
            box-shadow: 0 0 0 0.2rem rgba(209, 169, 74, 0.25);
        }
        .btn-pay {
            background: linear-gradient(135deg, #d1a94a 0%, #c19a3a 100%);
            border: none;
            border-radius: 8px;
            padding: 12px 30px;
            color: white;
            font-weight: 600;
            width: 100%;
        }
        .btn-pay:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(209, 169, 74, 0.3);
        }
        .upi-details {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .qr-scanner {
            border: 2px dashed #d1a94a;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
        }
        .bank-details {
            background: #e8f5e8;
            border: 1px solid #28a745;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .copy-btn {
            background: #d1a94a;
            border: none;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            cursor: pointer;
        }
        .copy-btn:hover {
            background: #c19a3a;
        }
        .upi-id {
            background: white;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            font-family: monospace;
            font-weight: bold;
            color: #d1a94a;
        }
        .payment-section {
            display: none;
        }
        .payment-section.active {
            display: block;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="payment-container">
            <div class="payment-card">
                <div class="payment-header">
                    <h4><i class="bi bi-shield-check me-2"></i>Secure Payment</h4>
                    <p class="mb-0">Order: <?= htmlspecialchars($order_code) ?></p>
                    <h3 class="mb-0">₹<?= number_format($amount, 2) ?></h3>
                </div>
                
                <div class="payment-body">
                    <form id="paymentForm">
                        <div class="mb-4">
                            <label class="form-label fw-bold">Choose Payment Method</label>
                            
                            <!-- Credit/Debit Card -->
                            <div class="payment-method selected" data-method="card">
                                <div class="d-flex align-items-center">
                                    <div class="payment-icon">
                                        <i class="bi bi-credit-card text-primary"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold">Credit/Debit Card</div>
                                        <small class="text-muted">Visa, MasterCard, RuPay</small>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- UPI Payment -->
                            <div class="payment-method" data-method="upi">
                                <div class="d-flex align-items-center">
                                    <div class="payment-icon">
                                        <i class="bi bi-phone text-warning"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold">UPI Payment</div>
                                        <small class="text-muted">Google Pay, PhonePe, Paytm, BHIM</small>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Net Banking -->
                            <div class="payment-method" data-method="netbanking">
                                <div class="d-flex align-items-center">
                                    <div class="payment-icon">
                                        <i class="bi bi-bank text-success"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold">Net Banking</div>
                                        <small class="text-muted">All major banks</small>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Bank Transfer -->
                            <div class="payment-method" data-method="banktransfer">
                                <div class="d-flex align-items-center">
                                    <div class="payment-icon">
                                        <i class="bi bi-building text-info"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold">Bank Transfer</div>
                                        <small class="text-muted">NEFT, RTGS, IMPS</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Credit/Debit Card Section -->
                        <div id="cardSection" class="payment-section active">
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label class="form-label">Card Number</label>
                                    <input type="text" class="form-control" id="cardNumber" placeholder="1234 5678 9012 3456" maxlength="19">
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="form-label">Expiry Date</label>
                                    <input type="text" class="form-control" id="expiryDate" placeholder="MM/YY" maxlength="5">
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="form-label">CVV</label>
                                    <input type="text" class="form-control" id="cvv" placeholder="123" maxlength="3">
                                </div>
                                <div class="col-12 mb-4">
                                    <label class="form-label">Card Holder Name</label>
                                    <input type="text" class="form-control" id="cardHolderName" placeholder="Enter card holder name">
                                </div>
                            </div>
                        </div>
                        
                        <!-- UPI Section -->
                        <div id="upiSection" class="payment-section">
                            <div class="upi-details">
                                <h6 class="fw-bold mb-3"><i class="bi bi-phone me-2"></i>UPI Payment Details</h6>
                                
                                <div class="mb-3">
                                    <label class="form-label">UPI ID</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="upi-id flex-grow-1">amitdairy@okicici</div>
                                        <button type="button" class="copy-btn" onclick="copyToClipboard('amitdairy@okicici')">
                                            <i class="bi bi-clipboard"></i> Copy
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Scan QR Code</label>
                                    <div class="qr-scanner">
                                        <div id="qr-code-display" style="text-align: center; margin-bottom: 15px;">
                                            <img id="upi-qr-code" src="" alt="UPI QR Code" style="max-width: 250px; border: 1px solid #ddd; border-radius: 8px;">
                                        </div>
                                        <div id="qr-reader" style="width: 100%; max-width: 300px; margin: 0 auto; display: none;"></div>
                                        <div class="d-flex gap-2 justify-content-center">
                                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="showQRCode()">
                                                <i class="bi bi-qr-code me-1"></i>Show QR Code
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="showQRScanner()">
                                                <i class="bi bi-camera me-1"></i>Scan QR
                                            </button>
                                        </div>
                                        <p class="text-muted mt-2">Scan QR code with any UPI app</p>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Or Enter UPI ID Manually</label>
                                    <input type="text" class="form-control" id="upiId" placeholder="Enter your UPI ID">
                                </div>
                                
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    <strong>Instructions:</strong>
                                    <ul class="mb-0 mt-2">
                                        <li>Copy our UPI ID: <strong>amitdairy@okicici</strong></li>
                                        <li>Open your UPI app (Google Pay, PhonePe, etc.)</li>
                                        <li>Send ₹<?= number_format($amount, 2) ?> to our UPI ID</li>
                                        <li>Add order number in payment note: <strong><?= htmlspecialchars($order_code) ?></strong></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Net Banking Section -->
                        <div id="netbankingSection" class="payment-section">
                            <div class="mb-3">
                                <label class="form-label">Select Your Bank</label>
                                <select class="form-control" id="bankSelect">
                                    <option value="">Choose your bank</option>
                                    <option value="sbi">State Bank of India</option>
                                    <option value="hdfc">HDFC Bank</option>
                                    <option value="icici">ICICI Bank</option>
                                    <option value="axis">Axis Bank</option>
                                    <option value="pnb">Punjab National Bank</option>
                                    <option value="canara">Canara Bank</option>
                                    <option value="union">Union Bank of India</option>
                                    <option value="bankofbaroda">Bank of Baroda</option>
                                    <option value="kotak">Kotak Mahindra Bank</option>
                                    <option value="yes">Yes Bank</option>
                                </select>
                            </div>
                            
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                You will be redirected to your bank's secure payment page.
                            </div>
                        </div>
                        
                        <!-- Bank Transfer Section -->
                        <div id="banktransferSection" class="payment-section">
                            <div class="bank-details">
                                <h6 class="fw-bold mb-3"><i class="bi bi-building me-2"></i>Bank Account Details</h6>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Account Holder Name</label>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="upi-id flex-grow-1">Amit Dairy & Sweets</div>
                                            <button type="button" class="copy-btn" onclick="copyToClipboard('Amit Dairy & Sweets')">
                                                <i class="bi bi-clipboard"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Account Number</label>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="upi-id flex-grow-1">1234567890</div>
                                            <button type="button" class="copy-btn" onclick="copyToClipboard('1234567890')">
                                                <i class="bi bi-clipboard"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">IFSC Code</label>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="upi-id flex-grow-1">ICIC0001234</div>
                                            <button type="button" class="copy-btn" onclick="copyToClipboard('ICIC0001234')">
                                                <i class="bi bi-clipboard"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Bank Name</label>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="upi-id flex-grow-1">ICICI Bank</div>
                                            <button type="button" class="copy-btn" onclick="copyToClipboard('ICICI Bank')">
                                                <i class="bi bi-clipboard"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Branch</label>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="upi-id flex-grow-1">Main Branch, Delhi</div>
                                            <button type="button" class="copy-btn" onclick="copyToClipboard('Main Branch, Delhi')">
                                                <i class="bi bi-clipboard"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Account Type</label>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="upi-id flex-grow-1">Current Account</div>
                                            <button type="button" class="copy-btn" onclick="copyToClipboard('Current Account')">
                                                <i class="bi bi-clipboard"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="alert alert-success">
                                    <i class="bi bi-check-circle me-2"></i>
                                    <strong>Important:</strong>
                                    <ul class="mb-0 mt-2">
                                        <li>Transfer amount: <strong>₹<?= number_format($amount, 2) ?></strong></li>
                                        <li>Add order number in transfer description: <strong><?= htmlspecialchars($order_code) ?></strong></li>
                                        <li>Send payment screenshot to WhatsApp: <strong>+91 98765 43210</strong></li>
                                        <li>Payment will be verified within 2-4 hours</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-pay">
                            <i class="bi bi-lock me-2"></i>Pay ₹<?= number_format($amount, 2) ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentMethod = 'card';
        let html5QrcodeScanner = null;

        document.addEventListener('DOMContentLoaded', function() {
            // Payment method selection
            document.querySelectorAll('.payment-method').forEach(method => {
                method.addEventListener('click', function() {
                    const methodType = this.dataset.method;
                    
                    // Remove selected class from all methods
                    document.querySelectorAll('.payment-method').forEach(m => m.classList.remove('selected'));
                    this.classList.add('selected');
                    
                    // Hide all sections
                    document.querySelectorAll('.payment-section').forEach(section => {
                        section.classList.remove('active');
                    });
                    
                    // Show selected section
                    document.getElementById(methodType + 'Section').classList.add('active');
                    currentMethod = methodType;
                    
                    // Initialize QR scanner for UPI
                    if (methodType === 'upi') {
                        showQRCode(); // Auto-generate QR code when UPI is selected
                    } else {
                        stopQRScanner();
                    }
                });
            });
            
            // Card number formatting
            document.getElementById('cardNumber').addEventListener('input', function(e) {
                let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
                let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
                e.target.value = formattedValue;
            });
            
            // Expiry date formatting
            document.getElementById('expiryDate').addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length >= 2) {
                    value = value.substring(0, 2) + '/' + value.substring(2, 4);
                }
                e.target.value = value;
            });
            
            // CVV validation
            document.getElementById('cvv').addEventListener('input', function(e) {
                e.target.value = e.target.value.replace(/\D/g, '');
            });
            
            // Form submission
            document.getElementById('paymentForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const submitBtn = this.querySelector('button[type="submit"]');
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Processing...';
                
                // Validate based on payment method
                if (!validatePaymentMethod()) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="bi bi-lock me-2"></i>Pay ₹<?= number_format($amount, 2) ?>';
                    return;
                }
                
                // Simulate payment processing
                setTimeout(() => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Payment Successful!',
                        text: 'Your payment has been processed successfully.',
                        confirmButtonText: 'Continue',
                        allowOutsideClick: false
                    }).then(() => {
                        // Update order status in database
                        fetch('inc/update_payment_status', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: 'order_code=<?= urlencode($order_code) ?>&status=paid&payment_method=' + currentMethod
                        }).then(() => {
                            window.location.href = 'cart?payment=success&order_code=<?= urlencode($order_code) ?>&amount=<?= $amount ?>';
                        });
                    });
                }, 2000);
            });
        });
        
        function validatePaymentMethod() {
            switch (currentMethod) {
                case 'card':
                    const cardNumber = document.getElementById('cardNumber').value.replace(/\s/g, '');
                    const expiryDate = document.getElementById('expiryDate').value;
                    const cvv = document.getElementById('cvv').value;
                    const cardHolderName = document.getElementById('cardHolderName').value;
                    
                    if (!cardNumber || cardNumber.length < 13) {
                        showError('Error', 'Please enter a valid card number');
                        return false;
                    }
                    if (!expiryDate || expiryDate.length < 5) {
                        showError('Error', 'Please enter expiry date (MM/YY)');
                        return false;
                    }
                    if (!cvv || cvv.length < 3) {
                        showError('Error', 'Please enter CVV');
                        return false;
                    }
                    if (!cardHolderName.trim()) {
                        showError('Error', 'Please enter card holder name');
                        return false;
                    }
                    break;
                    
                case 'upi':
                    const upiId = document.getElementById('upiId').value;
                    if (!upiId.trim()) {
                        showError('Error', 'Please enter UPI ID or scan QR code');
                        return false;
                    }
                    break;
                    
                case 'netbanking':
                    const bankSelect = document.getElementById('bankSelect').value;
                    if (!bankSelect) {
                        showError('Error', 'Please select your bank');
                        return false;
                    }
                    break;
                    
                case 'banktransfer':
                    // No validation needed for bank transfer
                    Swal.fire({
                        icon: 'info',
                        title: 'Bank Transfer Instructions',
                        html: `
                            <p>Please transfer ₹<?= number_format($amount, 2) ?> to our bank account.</p>
                            <p><strong>Order Number:</strong> <?= htmlspecialchars($order_code) ?></p>
                            <p>Send payment screenshot to WhatsApp: <strong>+91 98765 43210</strong></p>
                        `,
                        confirmButtonText: 'I Understand'
                    });
                    return true;
            }
            return true;
        }
        
        function initializeQRScanner() {
            if (html5QrcodeScanner) {
                html5QrcodeScanner.clear();
            }
            
            html5QrcodeScanner = new Html5QrcodeScanner(
                "qr-reader",
                { fps: 10, qrbox: { width: 250, height: 250 } },
                false
            );
            
            html5QrcodeScanner.render((decodedText) => {
                // Handle QR code scan
                if (decodedText.includes('upi://')) {
                    const upiId = decodedText.split('=')[1] || decodedText;
                    document.getElementById('upiId').value = upiId;
                    showSuccess('Success', 'UPI ID scanned successfully!');
                } else {
                    showError('Error', 'Invalid UPI QR code');
                }
            }, (error) => {
                // Handle scan error
                console.log(error);
            });
        }
        
        function showQRCode() {
            // Generate QR code for current order
            const amount = <?= $amount ?>;
            const orderCode = '<?= htmlspecialchars($order_code) ?>';
            
            fetch('inc/generate_upi_qr', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `amount=${amount}&order_code=${orderCode}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('upi-qr-code').src = data.qr_code_url;
                    document.getElementById('qr-code-display').style.display = 'block';
                    document.getElementById('qr-reader').style.display = 'none';
                    
                    // Update UPI ID field
                    document.getElementById('upiId').value = data.details.upi_id;
                } else {
                    showError('Error', 'Failed to generate QR code');
                }
            })
            .catch(err => {
                console.error('Error generating QR code:', err);
                showError('Error', 'Failed to generate QR code');
            });
        }
        
        function showQRScanner() {
            document.getElementById('qr-code-display').style.display = 'none';
            document.getElementById('qr-reader').style.display = 'block';
            initializeQRScanner();
        }
        
        function stopQRScanner() {
            if (html5QrcodeScanner) {
                html5QrcodeScanner.clear();
                html5QrcodeScanner = null;
            }
        }
        
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                showSuccess('Copied!', 'Text copied to clipboard');
            }).catch(() => {
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = text;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                
                showSuccess('Copied!', 'Text copied to clipboard');
            });
        }
    </script>
</body>
</html> 