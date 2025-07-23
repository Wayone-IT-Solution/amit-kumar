<?php
session_start();
require_once 'db.php';
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);

try {
    // Debug: Log the request
    error_log("Update cart request: " . json_encode($_POST));
    error_log("Session cart: " . json_encode($_SESSION['cart'] ?? []));

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    echo json_encode(['success' => false, 'message' => 'Cart not found']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $cartKey = $_POST['cart_key'] ?? '';
        $productQty = isset($_POST['product_qty']) ? (int)$_POST['product_qty'] : 1;

        // Debug: Log the cart key
        error_log("Cart key: " . $cartKey);
        error_log("Available cart keys: " . implode(', ', array_keys($_SESSION['cart'])));

        // Check if cart key exists
        if (!isset($_SESSION['cart'][$cartKey])) {
            echo json_encode(['success' => false, 'message' => 'Item not found in cart. Key: ' . $cartKey]);
        exit;
    }

        $item = $_SESSION['cart'][$cartKey];
        
        // Debug: Log the item
        error_log("Cart item: " . json_encode($item));

        // Get product limits
    $stmt = $conn->prepare("SELECT min_order, max_order, weight_type FROM products WHERE id = ?");
    $stmt->execute([$item['product_id']]);
    $productLimits = $stmt->fetch(PDO::FETCH_ASSOC);

    $minOrder = (int)($productLimits['min_order'] ?? 1);
    $maxOrder = (int)($productLimits['max_order'] ?? 999);
        $productWeightType = $productLimits['weight_type'] ?? 'g';

        // Validate and set quantity
        $productQty = max($minOrder, min($maxOrder, $productQty));
        $_SESSION['cart'][$cartKey]['quantity'] = $productQty;

        // Calculate prices
    $productPrice = floatval($item['product_price'] ?? 0);
    $boxPrice = floatval($item['box_price'] ?? 0);
    $boxQty = (int)($item['box_qty'] ?? 1);
    $weightType = $productWeightType;
    $weightValue = isset($item['selected_type']) ? (float)$item['selected_type'] : 1000;
    if ($weightType === 'unit') {
        $weightValue = 1;
        $productTotal = $productQty * $productPrice;
    } else {
        $productTotal = ($weightValue * $productQty / 1000) * $productPrice;
    }
    $boxTotal = $boxPrice * $boxQty;
    $itemTotal = $productTotal + $boxTotal;

        // Calculate cart subtotal
    $subtotal = 0;
    foreach ($_SESSION['cart'] as $cartItem) {
            $pQty = (int)($cartItem['quantity'] ?? 1);
            $bQty = (int)($cartItem['box_qty'] ?? 1);
        $pPrice = floatval($cartItem['product_price'] ?? 0);
            $bPrice = floatval($cartItem['box_price'] ?? 0);
            $subtotal += ($pPrice * $pQty) + ($bPrice * $bQty);
    }

        // Calculate weight
    $weightPerUnit = $weightValue;
    $weightUnit = $weightType;
    $totalWeight = $weightPerUnit * $productQty;
    if ($weightType === 'unit') {
        $weightUnit = 'unit';
    }

        $response = [
        'success' => true,
            'product_qty' => $productQty,
            'box_qty' => $boxQty,
            'product_price' => $productPrice,
            'box_price' => $boxPrice,
        'product_total' => $productTotal,
        'box_total' => $boxTotal,
        'item_total' => $itemTotal,
        'subtotal' => $subtotal,
        'unit_weight' => $weightPerUnit,
        'total_weight' => $totalWeight,
            'weight_type' => $weightUnit
        ];

        // Debug: Log the response
        error_log("Update cart response: " . json_encode($response));

        echo json_encode($response);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request method']);
exit;

} catch (Exception $e) {
    error_log("Update cart error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error occurred: ' . $e->getMessage()]);
    exit;
}
?>
