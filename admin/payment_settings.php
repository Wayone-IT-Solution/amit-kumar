<?php
session_start();
require_once '../inc/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Update payment settings
        $settings = [
            'upi_id' => $_POST['upi_id'] ?? '',
            'merchant_name' => $_POST['merchant_name'] ?? '',
            'bank_account_name' => $_POST['bank_account_name'] ?? '',
            'bank_account_number' => $_POST['bank_account_number'] ?? '',
            'bank_ifsc_code' => $_POST['bank_ifsc_code'] ?? '',
            'bank_name' => $_POST['bank_name'] ?? '',
            'bank_branch' => $_POST['bank_branch'] ?? '',
            'bank_account_type' => $_POST['bank_account_type'] ?? '',
            'whatsapp_number' => $_POST['whatsapp_number'] ?? '',
            'razorpay_key_id' => $_POST['razorpay_key_id'] ?? '',
            'razorpay_key_secret' => $_POST['razorpay_key_secret'] ?? '',
            'stripe_publishable_key' => $_POST['stripe_publishable_key'] ?? '',
            'stripe_secret_key' => $_POST['stripe_secret_key'] ?? ''
        ];
        
        foreach ($settings as $key => $value) {
            $stmt = $conn->prepare("INSERT INTO payment_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
            $stmt->execute([$key, $value, $value]);
        }
        
        $success_message = "Payment settings updated successfully!";
        
    } catch (Exception $e) {
        $error_message = "Error updating settings: " . $e->getMessage();
    }
}

// Fetch current settings
$current_settings = [];
try {
    $stmt = $conn->query("SELECT setting_key, setting_value FROM payment_settings");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $current_settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (Exception $e) {
    // Create table if it doesn't exist
    $conn->exec("
        CREATE TABLE IF NOT EXISTS payment_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(100) UNIQUE NOT NULL,
            setting_value TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Settings - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .main-content {
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
        }
        .form-control, .form-select {
            border-radius: 10px;
            border: 1px solid #e9ecef;
        }
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar p-0">
                <div class="p-3">
                    <h4 class="text-white mb-4">Admin Panel</h4>
                    <nav class="nav flex-column">
                        <a class="nav-link text-white-50" href="dashboard.php">
                            <i class="bi bi-speedometer2 me-2"></i>Dashboard
                        </a>
                        <a class="nav-link text-white-50" href="orders-list.php">
                            <i class="bi bi-list-ul me-2"></i>Orders
                        </a>
                        <a class="nav-link text-white-50" href="subscription-list.php">
                            <i class="bi bi-calendar-check me-2"></i>Subscriptions
                        </a>
                        <a class="nav-link text-white" href="payment_settings.php">
                            <i class="bi bi-credit-card me-2"></i>Payment Settings
                        </a>
                        <a class="nav-link text-white-50" href="logout.php">
                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                        </a>
                    </nav>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content p-4">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="bi bi-credit-card me-2"></i>
                                    Payment Gateway Settings
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (isset($success_message)): ?>
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        <i class="bi bi-check-circle me-2"></i><?= $success_message ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (isset($error_message)): ?>
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <i class="bi bi-exclamation-triangle me-2"></i><?= $error_message ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                <?php endif; ?>
                                
                                <form method="POST">
                                    <!-- UPI Settings -->
                                    <div class="row mb-4">
                                        <div class="col-12">
                                            <h6 class="fw-bold text-primary mb-3">
                                                <i class="bi bi-phone me-2"></i>UPI Payment Settings
                                            </h6>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">UPI ID</label>
                                            <input type="text" class="form-control" name="upi_id" 
                                                   value="<?= htmlspecialchars($current_settings['upi_id'] ?? 'amitdairy@okicici') ?>" 
                                                   placeholder="your-upi-id@bank">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Merchant Name</label>
                                            <input type="text" class="form-control" name="merchant_name" 
                                                   value="<?= htmlspecialchars($current_settings['merchant_name'] ?? 'Amit Dairy & Sweets') ?>" 
                                                   placeholder="Business Name">
                                        </div>
                                    </div>
                                    
                                    <!-- Bank Transfer Settings -->
                                    <div class="row mb-4">
                                        <div class="col-12">
                                            <h6 class="fw-bold text-success mb-3">
                                                <i class="bi bi-building me-2"></i>Bank Transfer Settings
                                            </h6>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Account Holder Name</label>
                                            <input type="text" class="form-control" name="bank_account_name" 
                                                   value="<?= htmlspecialchars($current_settings['bank_account_name'] ?? 'Amit Dairy & Sweets') ?>" 
                                                   placeholder="Account Holder Name">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Account Number</label>
                                            <input type="text" class="form-control" name="bank_account_number" 
                                                   value="<?= htmlspecialchars($current_settings['bank_account_number'] ?? '1234567890') ?>" 
                                                   placeholder="Account Number">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">IFSC Code</label>
                                            <input type="text" class="form-control" name="bank_ifsc_code" 
                                                   value="<?= htmlspecialchars($current_settings['bank_ifsc_code'] ?? 'ICIC0001234') ?>" 
                                                   placeholder="IFSC Code">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Bank Name</label>
                                            <input type="text" class="form-control" name="bank_name" 
                                                   value="<?= htmlspecialchars($current_settings['bank_name'] ?? 'ICICI Bank') ?>" 
                                                   placeholder="Bank Name">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Branch</label>
                                            <input type="text" class="form-control" name="bank_branch" 
                                                   value="<?= htmlspecialchars($current_settings['bank_branch'] ?? 'Main Branch, Delhi') ?>" 
                                                   placeholder="Branch Name">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Account Type</label>
                                            <select class="form-select" name="bank_account_type">
                                                <option value="Current Account" <?= ($current_settings['bank_account_type'] ?? '') === 'Current Account' ? 'selected' : '' ?>>Current Account</option>
                                                <option value="Savings Account" <?= ($current_settings['bank_account_type'] ?? '') === 'Savings Account' ? 'selected' : '' ?>>Savings Account</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <!-- Contact Settings -->
                                    <div class="row mb-4">
                                        <div class="col-12">
                                            <h6 class="fw-bold text-info mb-3">
                                                <i class="bi bi-telephone me-2"></i>Contact Settings
                                            </h6>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">WhatsApp Number</label>
                                            <input type="text" class="form-control" name="whatsapp_number" 
                                                   value="<?= htmlspecialchars($current_settings['whatsapp_number'] ?? '+91 98765 43210') ?>" 
                                                   placeholder="WhatsApp Number">
                                        </div>
                                    </div>
                                    
                                    <!-- Payment Gateway API Keys -->
                                    <div class="row mb-4">
                                        <div class="col-12">
                                            <h6 class="fw-bold text-warning mb-3">
                                                <i class="bi bi-key me-2"></i>Payment Gateway API Keys
                                            </h6>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Razorpay Key ID</label>
                                            <input type="text" class="form-control" name="razorpay_key_id" 
                                                   value="<?= htmlspecialchars($current_settings['razorpay_key_id'] ?? '') ?>" 
                                                   placeholder="rzp_test_...">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Razorpay Key Secret</label>
                                            <input type="password" class="form-control" name="razorpay_key_secret" 
                                                   value="<?= htmlspecialchars($current_settings['razorpay_key_secret'] ?? '') ?>" 
                                                   placeholder="Secret Key">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Stripe Publishable Key</label>
                                            <input type="text" class="form-control" name="stripe_publishable_key" 
                                                   value="<?= htmlspecialchars($current_settings['stripe_publishable_key'] ?? '') ?>" 
                                                   placeholder="pk_test_...">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Stripe Secret Key</label>
                                            <input type="password" class="form-control" name="stripe_secret_key" 
                                                   value="<?= htmlspecialchars($current_settings['stripe_secret_key'] ?? '') ?>" 
                                                   placeholder="sk_test_...">
                                        </div>
                                    </div>
                                    
                                    <div class="text-end">
                                        <button type="submit" class="btn btn-primary px-4 py-2">
                                            <i class="bi bi-save me-2"></i>Save Settings
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 