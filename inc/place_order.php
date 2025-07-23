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

$allowed_save_as = ['Home', 'Work', 'Friends & Family', 'Others'];
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
        if ($quantity < 1) $quantity = 1;
        if ($box_qty < 0) $box_qty = 0;
        $custom_box_text = trim($_POST['custom_box_text'] ?? '');
        $box_id = isset($_POST['box_id']) ? $_POST['box_id'] : null;

        // Fetch product details
        $stmt = $conn->prepare("SELECT id, name, discount_price, product_image, weight FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$product) {
            http_response_code(404);
            echo json_encode(['error' => 'Product not found']);
            exit;
        }

        // Default box values
        $box_name = '-';
        $box_price = 0;
        $box_image = '';
        $box_db_id = null;

        if ($box_id === 'custom') {
            $box_name = 'Custom Box';
            $box_price = null;
            $box_image = null;
            $box_db_id = 'custom';
        } elseif ($box_id !== 'none' && $box_id !== null) {
            $bstmt = $conn->prepare("SELECT id, box_name, box_price, box_image FROM boxes WHERE id = ?");
            $bstmt->execute([$box_id]);
            $box = $bstmt->fetch(PDO::FETCH_ASSOC);
            if ($box) {
                $box_name = $box['box_name'];
                $box_price = floatval($box['box_price']);
                $box_image = $box['box_image'];
                $box_db_id = $box['id'];
            }
        }

        $product_json = [
            'product_id'     => $product['id'],
            'product_name'   => $product['name'],
            'product_price'  => $product['discount_price'],
            'product_image'  => $product['product_image'],
            'product_weight' => $product['weight'],
            'quantity'       => $quantity,
            'box_id'         => $box_db_id,
            'box_name'       => $box_name,
            'box_price'      => $box_price,
            'box_image'      => $box_image,
            'custom_text'    => $custom_box_text,
            'box_qty'        => $box_qty
        ];

        // Calculate subtotal: (product_price * product_quantity) + (box_price * box_quantity)
        $subtotal = ($product_json['product_price'] * $quantity) + ($product_json['box_price'] * $box_qty);



        $stmt = $conn->prepare("INSERT INTO orders 
            (order_code, user_id, cart_data, subtotal, number_of_boxes, address_details, house_block, area_road, save_as, receiver_name, receiver_phone, delivery_date, delivery_time, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

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
            $delivery_time
        ]);

        // Handle payment based on method for Buy Now
        $paymentResponse = handlePayment('cod', $subtotal, $order_code, $user_id); // Assuming COD for Buy Now
        
        if ($paymentResponse['success']) {
            // Fetch user email for order confirmation
            $receiver_email = '';
            if (!empty($_SESSION['user_email'])) {
                $receiver_email = $_SESSION['user_email'];
            } elseif (!empty($_POST['receiver_email'])) {
                $receiver_email = $_POST['receiver_email'];
            } elseif (!empty($_SESSION['user_id'])) {
                $stmt = $conn->prepare('SELECT email FROM users WHERE id = ?');
                $stmt->execute([$_SESSION['user_id']]);
                $receiver_email = $stmt->fetchColumn();
            }

            // Prepare product info for email (HTML block)
            $productLines = '';
            $subtotal = 0;
            $shipping = 0; // Set as needed
            $taxes = 0;    // Set as needed
            $total = 0;
            if (isset($product_json) && !empty($product_json['product_name'])) { // Buy Now
                $img = !empty($product_json['product_image']) ? 'admin/' . $product_json['product_image'] : '';
                $productLines .= '<div style="display:flex;align-items:center;margin-bottom:12px;">';
                if ($img) $productLines .= '<img src="'.$img.'" style="width:48px;height:48px;border-radius:8px;margin-right:12px;">';
                $productLines .= '<div>';
                $productLines .= '<div><b>'.$product_json['product_name'].'</b> ('.$product_json['product_weight'].')</div>';
                $productLines .= '<div>Qty: '.$product_json['quantity'].' | ₹'.number_format($product_json['product_price'],2).'</div>';
                $productLines .= '<div>Box: '.($product_json['box_name'] ?? '-').' (₹'.number_format($product_json['box_price'],2).')</div>';
                $productLines .= '</div></div>';
                $subtotal = ($product_json['product_price'] * $product_json['quantity']) + ($product_json['box_price'] * $product_json['box_qty']);
                $total = $subtotal + $shipping + $taxes;
            } elseif (!empty($_SESSION['cart'])) { // Cart order
                foreach ($_SESSION['cart'] as $item) {
                    $img = !empty($item['product_image']) ? 'admin/' . $item['product_image'] : '';
                    $productLines .= '<div style="display:flex;align-items:center;margin-bottom:12px;">';
                    if ($img) $productLines .= '<img src="'.$img.'" style="width:48px;height:48px;border-radius:8px;margin-right:12px;">';
                    $productLines .= '<div>';
                    $productLines .= '<div><b>'.($item['product_name'] ?? '-').'</b> ('.($item['product_weight'] ?? '-').')</div>';
                    $productLines .= '<div>Qty: '.($item['quantity'] ?? '-').' | ₹'.number_format($item['product_price'],2).'</div>';
                    $productLines .= '<div>Box: '.($item['box_name'] ?? '-').' (₹'.number_format($item['box_price'],2).')</div>';
                    $productLines .= '</div></div>';
                    $subtotal += ($item['product_price'] * $item['quantity']) + ($item['box_price'] * $item['box_qty']);
                }
                $total = $subtotal + $shipping + $taxes;
            }
            $shipping_address = $address_details . ', ' . $house_block . ', ' . $area_road;
            $payment_method = 'Cash on Delivery'; // Change if needed
            $userMsg_en = '
<div style="background:#f4f8fb;padding:0;margin:0;font-family:Arial,sans-serif;">
  <div style="max-width:600px;margin:0 auto;background:#fff;border-radius:16px;overflow:hidden;">
    <div style="padding:32px 24px 0 24px;text-align:center;">
      <h1 style="font-size:2.2rem;margin:0 0 8px 0;">Thank you for your order!</h1>
      <img src="https://'.$_SERVER['HTTP_HOST'].'/amit-kumar/assets/img/clients/order.jpg" alt="Shopping" style="width:100%;max-width:320px;border-radius:12px;margin:16px 0;">
      <p style="font-size:1.1rem;">Dear <b>'.htmlspecialchars($receiver_name).'</b>,<br>
      Your order has been placed successfully!<br>
      <b>Delivery Date:</b> '.htmlspecialchars($delivery_date).'<br>
      <b>Order ID:</b> '.htmlspecialchars($order_code).'
      </p>
    </div>
    <div style="padding:24px;">
      <h2 style="font-size:1.2rem;margin-bottom:12px;">Order summary</h2>
      '.$productLines.'
    </div>
    <div style="padding:24px;background:#f7f7f7;">
      <h3 style="margin:0 0 8px 0;">Customer information</h3>
      <p style="margin:0 0 4px 0;"><b>Shipping address:</b> '.htmlspecialchars($address_details . ', ' . $house_block . ', ' . $area_road).'</p>
      <p style="margin:0 0 4px 0;"><b>Payment method:</b> Cash on Delivery</p>
    </div>
    <div style="padding:24px;text-align:center;">
      <p style="margin:0 0 8px 0;">Thank you for shopping with us!<br><b>Amit Dairy & Sweets</b></p>
      <small style="color:#888;">If you have any questions, reply to this email.</small>
    </div>
  </div>
</div>
';
            send_email($receiver_email, 'Order Placed - Amit Dairy & Sweets', $userMsg_en);
            $response = [
                'success' => true, 
                'message' => $paymentResponse['message'],
                'payment_redirect' => $paymentResponse['redirect_url'] ?? null,
                'order_code' => $order_code
            ];
            echo json_encode($response);
        } else {
            $response = [
                'success' => false, 
                'error' => $paymentResponse['error']
            ];
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
        $qty     = (int)($item['quantity'] ?? 1); // default to 1
        $boxQty  = (int)($item['box_qty'] ?? 1);  // default to 1
        $price   = floatval($item['product_price'] ?? 0);
        $box     = floatval($item['box_price'] ?? 0);
        // Calculate subtotal: (product_price * product_quantity) + (box_price * box_quantity)
        $subtotal += ($price * $qty) + ($box * $boxQty);
        $totalBoxCount += $boxQty;
        
    }

    if ($subtotal < $minAmount) {
        http_response_code(400);
        echo json_encode(['error' => "Minimum order amount is ₹{$minAmount}."]);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO orders 
        (order_code, user_id, cart_data, subtotal, number_of_boxes, address_details, house_block, area_road, save_as, receiver_name, receiver_phone, delivery_date, delivery_time, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

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
        $delivery_time
    ]);

    // Handle payment based on method
    $paymentResponse = handlePayment('cod', $subtotal, $order_code, $user_id); // Assuming COD for Cart
    
    if ($paymentResponse['success']) {
        // Fetch user email for order confirmation
        $receiver_email = '';
        if (!empty($_SESSION['user_email'])) {
            $receiver_email = $_SESSION['user_email'];
        } elseif (!empty($_POST['receiver_email'])) {
            $receiver_email = $_POST['receiver_email'];
        } elseif (!empty($_SESSION['user_id'])) {
            $stmt = $conn->prepare('SELECT email FROM users WHERE id = ?');
            $stmt->execute([$_SESSION['user_id']]);
            $receiver_email = $stmt->fetchColumn();
        }

        // Prepare product info for email (HTML block)
        $productLines = '';
        $subtotal = 0;
        $shipping = 0; // Set as needed
        $taxes = 0;    // Set as needed
        $total = 0;
        if (isset($product_json) && !empty($product_json['product_name'])) { // Buy Now
            $img = !empty($product_json['product_image']) ? 'admin/' . $product_json['product_image'] : '';
            $productLines .= '<div style="display:flex;align-items:center;margin-bottom:12px;">';
            if ($img) $productLines .= '<img src="'.$img.'" style="width:48px;height:48px;border-radius:8px;margin-right:12px;">';
            $productLines .= '<div>';
            $productLines .= '<div><b>'.$product_json['product_name'].'</b> ('.$product_json['product_weight'].')</div>';
            $productLines .= '<div>Qty: '.$product_json['quantity'].' | ₹'.number_format($product_json['product_price'],2).'</div>';
            $productLines .= '<div>Box: '.($product_json['box_name'] ?? '-').' (₹'.number_format($product_json['box_price'],2).')</div>';
            $productLines .= '</div></div>';
            $subtotal = ($product_json['product_price'] * $product_json['quantity']) + ($product_json['box_price'] * $product_json['box_qty']);
            $total = $subtotal + $shipping + $taxes;
        } elseif (!empty($_SESSION['cart'])) { // Cart order
            foreach ($_SESSION['cart'] as $item) {
                $img = !empty($item['product_image']) ? 'admin/' . $item['product_image'] : '';
                $productLines .= '<div style="display:flex;align-items:center;margin-bottom:12px;">';
                if ($img) $productLines .= '<img src="'.$img.'" style="width:48px;height:48px;border-radius:8px;margin-right:12px;">';
                $productLines .= '<div>';
                $productLines .= '<div><b>'.($item['product_name'] ?? '-').'</b> ('.($item['product_weight'] ?? '-').')</div>';
                $productLines .= '<div>Qty: '.($item['quantity'] ?? '-').' | ₹'.number_format($item['product_price'],2).'</div>';
                $productLines .= '<div>Box: '.($item['box_name'] ?? '-').' (₹'.number_format($item['box_price'],2).')</div>';
                $productLines .= '</div></div>';
                $subtotal += ($item['product_price'] * $item['quantity']) + ($item['box_price'] * $item['box_qty']);
            }
            $total = $subtotal + $shipping + $taxes;
        }
        $shipping_address = $address_details . ', ' . $house_block . ', ' . $area_road;
        $payment_method = 'Cash on Delivery'; // Change if needed
        // Use production image URL for AWS server
        $image_url = 'https://amitdairyandsweets.com/amit-kumar/assets/img/clients/order.jpg';
        $userMsg_en = '
<div style="background:#f4f8fb;padding:0;margin:0;font-family:Arial,sans-serif;">
  <div style="max-width:600px;margin:0 auto;background:#fff;border-radius:16px;overflow:hidden;">
    <div style="padding:32px 24px 0 24px;text-align:center;">
      <h1 style="font-size:2.2rem;margin:0 0 8px 0;">Thank you for your order!</h1>
      <img src="'.$image_url.'" alt="Shopping" style="width:100%;max-width:320px;border-radius:12px;margin:16px 0;">
      <p style="font-size:1.1rem;">Dear <b>'.htmlspecialchars($receiver_name).'</b>,<br>
      Your order has been placed successfully!<br>
      <b>Delivery Date:</b> '.htmlspecialchars($delivery_date).'<br>
      <b>Order ID:</b> '.htmlspecialchars($order_code).'
      </p>
    </div>
    <div style="padding:24px;">
      <h2 style="font-size:1.2rem;margin-bottom:12px;">Order summary</h2>
      <hr style="border:0;border-top:1px solid #eee;margin-bottom:18px;">
      '.$productLines.'
    </div>
    <div style="padding:24px;background:#f7f7f7;">
      <h3 style="margin:0 0 8px 0;">Customer information</h3>
      <p style="margin:0 0 4px 0;"><b>Shipping address:</b> '.htmlspecialchars($address_details . ', ' . $house_block . ', ' . $area_road).'</p>
      <p style="margin:0 0 4px 0;"><b>Payment method:</b> Cash on Delivery</p>
    </div>
    <div style="padding:24px;text-align:center;">
      <p style="margin:0 0 8px 0;">Thank you for shopping with us!<br><b>Amit Dairy & Sweets</b></p>
      <small style="color:#888;">If you have any questions, reply to this email.</small>
    </div>
  </div>
</div>
';
        send_email($receiver_email, 'Order Placed - Amit Dairy & Sweets', $userMsg_en);
        unset($_SESSION['cart']);
        $response = [
            'success' => true, 
            'message' => $paymentResponse['message'],
            'payment_redirect' => $paymentResponse['redirect_url'] ?? null,
            'order_code' => $order_code
        ];
        echo json_encode($response);
    } else {
        $response = [
            'success' => false, 
            'error' => $paymentResponse['error']
        ];
        echo json_encode($response);
    }
    exit;

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}

// Payment handling function
function handlePayment($paymentMethod, $amount, $orderCode, $userId) {
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
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'j83367806@gmail.com';
    $mail->Password = 'zidqgkggsnubhztg'; // Gmail app password, no spaces
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;
    $mail->setFrom('j83367806@gmail.com', 'Amit Dairy & Sweets');
    $mail->addAddress($to);
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body = $body;
    try {
        $result = $mail->send();
        error_log('Mail sent to ' . $to . ' result: ' . var_export($result, true));
        return $result;
    } catch (\Exception $e) {
        error_log('Mail error: ' . $mail->ErrorInfo);
        return false;
    }
}
