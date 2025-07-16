<?php
/**
 * UPI QR Code Generator
 * Generates QR codes for UPI payments
 */

require_once 'db.php';

class UPIQRGenerator {
    private $upiId;
    private $merchantName;
    private $amount;
    private $orderCode;
    
    public function __construct($upiId = 'amitdairy@okicici', $merchantName = 'Amit Dairy & Sweets') {
        $this->upiId = $upiId;
        $this->merchantName = $merchantName;
    }
    
    /**
     * Generate UPI payment URL
     */
    public function generateUPIURL($amount, $orderCode = '', $note = '') {
        $params = [
            'pa' => $this->upiId, // Payee UPI ID
            'pn' => $this->merchantName, // Payee name
            'am' => $amount, // Amount
            'tn' => $note ?: 'Payment for order', // Transaction note
            'cu' => 'INR' // Currency
        ];
        
        if ($orderCode) {
            $params['tn'] = "Order: $orderCode";
        }
        
        $queryString = http_build_query($params);
        return "upi://pay?$queryString";
    }
    
    /**
     * Generate QR code image using Google Charts API
     */
    public function generateQRCode($amount, $orderCode = '', $note = '') {
        $upiURL = $this->generateUPIURL($amount, $orderCode, $note);
        $encodedURL = urlencode($upiURL);
        
        // Using Google Charts API for QR code generation
        $qrURL = "https://chart.googleapis.com/chart?cht=qr&chs=300x300&chl=$encodedURL&choe=UTF-8";
        
        return $qrURL;
    }
    
    /**
     * Get UPI payment details
     */
    public function getUPIDetails() {
        return [
            'upi_id' => $this->upiId,
            'merchant_name' => $this->merchantName,
            'supported_apps' => [
                'Google Pay',
                'PhonePe', 
                'Paytm',
                'BHIM',
                'Amazon Pay',
                'Mobikwik',
                'Freecharge'
            ]
        ];
    }
}

// API endpoint for generating QR codes
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $amount = $_POST['amount'] ?? 0;
    $orderCode = $_POST['order_code'] ?? '';
    $note = $_POST['note'] ?? '';
    
    if (!$amount) {
        echo json_encode(['error' => 'Amount is required']);
        exit;
    }
    
    $qrGenerator = new UPIQRGenerator();
    $qrCodeURL = $qrGenerator->generateQRCode($amount, $orderCode, $note);
    $upiURL = $qrGenerator->generateUPIURL($amount, $orderCode, $note);
    $details = $qrGenerator->getUPIDetails();
    
    echo json_encode([
        'success' => true,
        'qr_code_url' => $qrCodeURL,
        'upi_url' => $upiURL,
        'details' => $details
    ]);
}
?> 