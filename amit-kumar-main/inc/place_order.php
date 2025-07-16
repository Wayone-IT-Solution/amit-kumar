<?php
session_start();
require_once 'db.php';
header('Content-Type: application/json');

// ✅ Check login
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    http_response_code(401);
    echo json_encode(['error' => 'Login required']);
    exit;
}

// ✅ Minimum order amount (from settings)
$settingStmt = $conn->query("SELECT value FROM settings LIMIT 1");
$setting = $settingStmt->fetch(PDO::FETCH_ASSOC);
$minAmount = isset($setting['value']) ? (float)$setting['value'] : 1500.0;

// ✅ Add to Cart Logic
if (isset($_POST['product_id']) && isset($_POST['quantity']) && isset($_POST['box_id']) && !isset($_POST['address_details'])) {
    $product_id = (int)$_POST['product_id'];
    $quantity   = (int)$_POST['quantity'];
    $box_id     = $_POST['box_id'];
    $custom_text = trim($_POST['custom_text'] ?? '');
    $box_qty    = (int)($_POST['box_qty'] ?? 1);

    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        http_response_code(404);
        echo json_encode(['error' => 'Product not found']);
        exit;
    }

    $box_name = '-';
    $box_price = 0;

    if ($box_id === 'custom') {
        $box_name = 'Custom Box';
    } elseif ($box_id !== 'none') {
        $bstmt = $conn->prepare("SELECT box_name, box_price FROM sweet_boxes WHERE id = ?");
        $bstmt->execute([$box_id]);
        $box = $bstmt->fetch(PDO::FETCH_ASSOC);
        if ($box) {
            $box_name = $box['box_name'];
            $box_price = floatval($box['box_price']);
        }
    }

    $product_price = floatval($product['discount_price']);
    $subtotal = ($product_price + $box_price) * $quantity;

    if ($subtotal < $minAmount) {
        http_response_code(400);
        echo json_encode(['error' => "Minimum order amount is ₹{$minAmount}."]);
        exit;
    }

    $cartItem = [
        'product_id'     => $product['id'],
        'product_name'   => $product['name'],
        'product_price'  => $product_price,
        'product_image'  => $product['product_image'],
        'product_weight' => $product['weight'] ?? '-',
        'quantity'       => $quantity,
        'box_name'       => $box_name,
        'box_price'      => $box_price,
        'custom_text'    => $custom_text,
        'box_qty'        => $box_qty,
    ];

    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
    $_SESSION['cart'][] = $cartItem;

    echo json_encode(['success' => true, 'message' => 'Added to cart']);
    exit;
}

// ✅ Order placement
$address_details = trim($_POST['address_details'] ?? '');
$house_block     = trim($_POST['house_block'] ?? '');
$area_road       = trim($_POST['area_road'] ?? '');
$save_as         = trim($_POST['save_as'] ?? '');
$receiver_name   = trim($_POST['receiver_name'] ?? '');
$receiver_phone  = trim($_POST['receiver_phone'] ?? '');
$receiver_email  = trim($_POST['receiver_email'] ?? '');
$delivery_date   = trim($_POST['delivery_date'] ?? '');
$delivery_time   = trim($_POST['delivery_time'] ?? '');
$payment_method  = trim($_POST['payment_method'] ?? 'cod');

// Debug logging
error_log("Payment method received: " . $payment_method);
error_log("POST data: " . print_r($_POST, true));

$allowed_save_as = ['Home', 'Work', 'Friends & Family', 'Others'];
$allowed_payment_methods = ['cod', 'online', 'upi', 'wallet', 'card', 'netbanking', 'banktransfer'];
$errors = [];

if (!$address_details) $errors[] = "Address details required.";
if (!$delivery_date)   $errors[] = "Delivery date is required.";
if (!$delivery_time)   $errors[] = "Delivery time is required.";
if (!$house_block)     $errors[] = "House / Flat / Block No. required.";
if (!$area_road)       $errors[] = "Apartment / Road / Area required.";
if (!in_array($save_as, $allowed_save_as)) $errors[] = "Invalid 'Save As' value.";
if (!$receiver_name)   $errors[] = "Receiver's name required.";
if (!$receiver_phone || !preg_match('/^\+?[0-9]{7,15}$/', $receiver_phone)) {
    $errors[] = "Valid receiver phone number required.";
}
if (!in_array($payment_method, $allowed_payment_methods)) {
    $errors[] = "Invalid payment method selected.";
}

if ($errors) {
    http_response_code(422);
    echo json_encode(['errors' => $errors]);
    exit;
}

// ✅ Generate Order Code
$last = $conn->query("SELECT order_code FROM orders ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$nextNumber = (isset($last['order_code']) && preg_match('/^#O(\d{4})$/', $last['order_code'], $matches)) ? ((int)$matches[1] + 1) : 1;
$order_code = '#O' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

// ✅ Order Process
try {
    //
    // ✅ BUY NOW
    //
    if (isset($_POST['product_id']) && isset($_POST['box_name'])) {
        $product_id = (int)$_POST['product_id'];
        $quantity   = (int)($_POST['quantity'] ?? 1);
        $box_qty    = isset($_POST['box_qty']) ? (int)$_POST['box_qty'] : 1;
        $box_name   = $_POST['box_name'] ?? '-';
        $box_price  = floatval($_POST['box_price'] ?? 0);
        $custom_box_text = trim($_POST['custom_box_text'] ?? '');

        $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            http_response_code(404);
            echo json_encode(['error' => 'Product not found']);
            exit;
        }

        $product_json = [
            'product_id'     => $product['id'],
            'product_name'   => $product['name'],
            'product_price'  => floatval($product['discount_price']),
            'product_image'  => $product['product_image'],
            'product_weight' => $product['weight'] ?? '-',
            'quantity'       => $quantity,
            'box_name'       => $box_name,
            'box_price'      => $box_price,
            'custom_text'    => $custom_box_text,
            'box_qty'        => $box_qty,
        ];

        $subtotal = ($product_json['product_price'] + $product_json['box_price']) * $quantity;

        if ($subtotal < $minAmount) {
            http_response_code(400);
            echo json_encode(['error' => "Minimum order amount is ₹{$minAmount}."]);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO orders 
            (order_code, user_id, cart_data, subtotal, number_of_boxes, address_details, house_block, area_road, save_as, receiver_name, receiver_phone, delivery_date, delivery_time, payment_method, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

        $stmt->execute([
            $order_code,
            $user_id,
            json_encode([$product_json], JSON_UNESCAPED_UNICODE),
            $subtotal,
            $box_qty,
            $address_details,
            $house_block,
            $area_road,
            $save_as,
            $receiver_name,
            $receiver_phone,
            $delivery_date,
            $delivery_time,
            $payment_method
        ]);

        // Handle payment based on method for Buy Now
        $paymentResponse = handlePayment($payment_method, $subtotal, $order_code, $user_id);
        
        if ($paymentResponse['success']) {
            // --- User SMS/Email ---
            $userMsg_en = "Dear $receiver_name, your order ($order_code) has been placed successfully! Delivery Date: $delivery_date.";
            $userMsg_hi = "प्रिय $receiver_name, आपका ऑर्डर ($order_code) सफलतापूर्वक बुक हो गया है! डिलीवरी तिथि: $delivery_date.";
            $userMsg_bi = $userMsg_en . "<br><br>" . $userMsg_hi; // For email
            $userMsg_bi_sms = $userMsg_en . "\n\n" . $userMsg_hi;  // For SMS
            send_sms($receiver_phone, $userMsg_bi_sms);
            send_email($receiver_email, 'Order Placed - Amit Dairy & Sweets', $userMsg_bi);

            // --- Admin SMS/Email if delivery is within 2 days ---
            $adminPhone = '9889090837';
            $adminEmail = 'jatin@wayone.co.in';
            $daysToDelivery = 99;
            if ($delivery_date) {
                $dt = new DateTime($delivery_date);
                $now = new DateTime();
                $daysToDelivery = (int)$now->diff($dt)->format('%r%a');
            }
            if ($daysToDelivery <= 2 && $daysToDelivery >= 0) {
                $adminMsg_en = "Order $order_code scheduled for delivery on $delivery_date.";
                $adminMsg_hi = "ऑर्डर $order_code की डिलीवरी $delivery_date को निर्धारित है।";
                $adminMsg_bi = $adminMsg_en . "<br><br>" . $adminMsg_hi;
                $adminMsg_bi_sms = $adminMsg_en . "\n\n" . $adminMsg_hi;
                send_sms($adminPhone, $adminMsg_bi_sms);
                send_email($adminEmail, 'Upcoming Delivery - Amit Dairy & Sweets', $adminMsg_bi);
            }
            $response = [
                'success' => true, 
                'message' => $paymentResponse['message'],
                'payment_redirect' => $paymentResponse['redirect_url'] ?? null,
                'order_code' => $order_code
            ];
            error_log("Payment response: " . print_r($response, true));
            echo json_encode($response);
        } else {
            $response = [
                'success' => false, 
                'error' => $paymentResponse['error']
            ];
            error_log("Payment error response: " . print_r($response, true));
            echo json_encode($response);
        }
        exit;
    }

    //
    // ✅ CART ORDER
    //
    if (empty($_SESSION['cart'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Cart is empty']);
        exit;
    }

    $subtotal = 0;
    $totalBoxCount = 0;

    foreach ($_SESSION['cart'] as $item) {
        $qty     = (int)($item['quantity'] ?? 0);
        $boxQty  = (int)($item['box_qty'] ?? 0);
        $price   = floatval($item['product_price'] ?? 0);
        $box     = floatval($item['box_price'] ?? 0);
        $subtotal += ($price + $box) * $qty;
        $totalBoxCount += $boxQty;
    }

    if ($subtotal < $minAmount) {
        http_response_code(400);
        echo json_encode(['error' => "Minimum order amount is ₹{$minAmount}."]);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO orders 
        (order_code, user_id, cart_data, subtotal, number_of_boxes, address_details, house_block, area_road, save_as, receiver_name, receiver_phone, delivery_date, delivery_time, payment_method, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

    $stmt->execute([
        $order_code,
        $user_id,
        json_encode($_SESSION['cart'], JSON_UNESCAPED_UNICODE),
        $subtotal,
        $totalBoxCount,
        $address_details,
        $house_block,
        $area_road,
        $save_as,
        $receiver_name,
        $receiver_phone,
        $delivery_date,
        $delivery_time,
        $payment_method
    ]);

    unset($_SESSION['cart']);

    // Handle payment based on method
    $paymentResponse = handlePayment($payment_method, $subtotal, $order_code, $user_id);
    
    if ($paymentResponse['success']) {
        // --- User SMS/Email ---
        $userMsg_en = "Dear $receiver_name, your order ($order_code) has been placed successfully! Delivery Date: $delivery_date.";
        $userMsg_hi = "प्रिय $receiver_name, आपका ऑर्डर ($order_code) सफलतापूर्वक बुक हो गया है! डिलीवरी तिथि: $delivery_date.";
        $userMsg_bi = $userMsg_en . "<br><br>" . $userMsg_hi; // For email
        $userMsg_bi_sms = $userMsg_en . "\n\n" . $userMsg_hi;  // For SMS
        send_sms($receiver_phone, $userMsg_bi_sms);
        send_email($receiver_email, 'Order Placed - Amit Dairy & Sweets', $userMsg_bi);

        // --- Admin SMS/Email if delivery is within 2 days ---
        $adminPhone = '9889090837';
        $adminEmail = 'jatin@wayone.co.in';
        $daysToDelivery = 99;
        if ($delivery_date) {
            $dt = new DateTime($delivery_date);
            $now = new DateTime();
            $daysToDelivery = (int)$now->diff($dt)->format('%r%a');
        }
        if ($daysToDelivery <= 2 && $daysToDelivery >= 0) {
            $adminMsg_en = "Order $order_code scheduled for delivery on $delivery_date.";
            $adminMsg_hi = "ऑर्डर $order_code की डिलीवरी $delivery_date को निर्धारित है।";
            $adminMsg_bi = $adminMsg_en . "<br><br>" . $adminMsg_hi;
            $adminMsg_bi_sms = $adminMsg_en . "\n\n" . $adminMsg_hi;
            send_sms($adminPhone, $adminMsg_bi_sms);
            send_email($adminEmail, 'Upcoming Delivery - Amit Dairy & Sweets', $adminMsg_bi);
        }
        $response = [
            'success' => true, 
            'message' => $paymentResponse['message'],
            'payment_redirect' => $paymentResponse['redirect_url'] ?? null,
            'order_code' => $order_code
        ];
        error_log("Cart payment response: " . print_r($response, true));
        echo json_encode($response);
    } else {
        $response = [
            'success' => false, 
            'error' => $paymentResponse['error']
        ];
        error_log("Cart payment error response: " . print_r($response, true));
        echo json_encode($response);
    }
    exit;

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}

// Payment handling function
function handlePayment($paymentMethod, $amount, $orderCode, $userId) {
    error_log("handlePayment called with method: " . $paymentMethod . ", amount: " . $amount . ", orderCode: " . $orderCode);
    switch ($paymentMethod) {
        case 'cod':
            return [
                'success' => true,
                'message' => 'Order placed successfully! Pay ₹' . number_format($amount, 2) . ' on delivery.',
                'redirect_url' => 'cart?payment=success&order_code=' . $orderCode . '&amount=' . number_format($amount, 2)
            ];
            
        case 'online':
        case 'card':
            // Redirect to payment gateway
            return [
                'success' => true,
                'message' => 'Redirecting to payment gateway...',
                'redirect_url' => 'payment_gateway?order_code=' . $orderCode . '&amount=' . $amount
            ];
            
        case 'upi':
            // Generate UPI payment link
            return [
                'success' => true,
                'message' => 'UPI payment initiated. Check your phone for payment link.',
                'redirect_url' => 'payment_gateway?order_code=' . $orderCode . '&amount=' . $amount
            ];
            
        case 'netbanking':
            // Redirect to payment gateway for net banking
            return [
                'success' => true,
                'message' => 'Redirecting to net banking...',
                'redirect_url' => 'payment_gateway.php?order_code=' . $orderCode . '&amount=' . $amount
            ];
            
        case 'banktransfer':
            // Bank transfer instructions
            return [
                'success' => true,
                'message' => 'Bank transfer details provided. Please transfer ₹' . number_format($amount, 2) . ' to our account.',
                'redirect_url' => 'payment_gateway.php?order_code=' . $orderCode . '&amount=' . $amount
            ];
            
        case 'wallet':
            // Check wallet balance and deduct
            return [
                'success' => true,
                'message' => 'Payment completed from wallet balance.',
                'redirect_url' => 'cart?payment=success&order_code=' . $orderCode . '&amount=' . number_format($amount, 2)
            ];
            
        default:
            return [
                'success' => false,
                'error' => 'Invalid payment method'
            ];
    }
}

// --- Notification helpers ---
function send_sms($to, $msg) {
    if (!$to) {
        error_log("SMS not sent: missing phone number. Message: $msg");
        return false;
    }
    // Replace with your SMS API integration
    // Example: file_get_contents('https://sms-api.example.com/send?to=' . urlencode($to) . '&msg=' . urlencode($msg));
    error_log("SMS to $to: $msg");
    return true;
}

function send_email($to, $subject, $body) {
    if (!$to) {
        error_log("Email not sent: missing email address. Subject: $subject");
        return false;
    }
    require_once __DIR__ . '/../vendor/autoload.php';
    $mail = new \PHPMailer\PHPMailer\PHPMailer();
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com'; // Change as needed
    $mail->SMTPAuth = true;
    $mail->Username = 'your@email.com'; // Change as needed
    $mail->Password = 'yourpassword'; // Change as needed
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;
    $mail->setFrom('no-reply@amitdairy.com', 'Amit Dairy & Sweets');
    $mail->addAddress($to);
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body = $body;
    try {
        $result = $mail->send();
        error_log('Mail sent to ' . $to . ' result: ' . var_export($result, true));
        return $result;
    } catch (\Exception $e) {
        error_log('Mail error: ' . $e->getMessage());
        return false;
    }
}
