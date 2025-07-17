<?php
session_start();
require 'db.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$productId   = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$quantity    = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
$boxId       = $_POST['box_id'] ?? 'none';
$customText  = trim($_POST['custom_text'] ?? '');
$boxQty      = isset($_POST['box_qty']) ? (int)$_POST['box_qty'] : 1;
$isGuest     = isset($_POST['as_guest']) && $_POST['as_guest'] == 1;

// Not logged in check
if (!isset($_SESSION['user_id']) && !$isGuest) {
    error_log('Add to cart: not_logged_in');
    echo 'not_logged_in';
    exit;
}

if (!isset($_SESSION['user_id']) && $isGuest) {
    $_SESSION['is_guest'] = true;
}

if ($productId > 0 && $quantity > 0) {

    $stmt = $conn->prepare("SELECT id, name, discount_price, product_image, weight FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        error_log('Add to cart: Product not found for product_id=' . $productId);
        echo "Product not found.";
        exit;
    }

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Handle Custom Box
    if ($boxId === 'custom') {
        $cartKey = $productId . '_custom';
        if (isset($_SESSION['cart'][$cartKey])) {
            $_SESSION['cart'][$cartKey]['quantity'] += $quantity;
            $_SESSION['cart'][$cartKey]['box_qty'] += $boxQty;
        } else {
            $_SESSION['cart'][$cartKey] = [
                'product_id'     => $product['id'],
                'product_name'   => $product['name'],
                'product_price'  => $product['discount_price'],
                'product_image'  => $product['product_image'],
                'product_weight' => $product['weight'],
                'quantity'       => $quantity,
                'box_id'         => 'custom',
                'box_name'       => 'Custom Box',
                'box_price'      => null,
                'box_image'      => null,
                'custom_text'    => $customText,
                'box_qty'        => $boxQty
            ];
        }

    } elseif ($boxId !== 'none') {
        // Normal Box
        $stmt = $conn->prepare("SELECT id, box_name, box_price, box_image FROM boxes WHERE id = ?");
        $stmt->execute([$boxId]);
        $box = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$box) {
            error_log('Add to cart: Box not found for box_id=' . $boxId);
            echo "Box not found.";
            exit;
        }

        $cartKey = $productId . '_' . $boxId;

        if (isset($_SESSION['cart'][$cartKey])) {
            $_SESSION['cart'][$cartKey]['quantity'] += $quantity;
            $_SESSION['cart'][$cartKey]['box_qty'] += $boxQty;
        } else {
            $_SESSION['cart'][$cartKey] = [
                'product_id'     => $product['id'],
                'product_name'   => $product['name'],
                'product_price'  => $product['discount_price'],
                'product_image'  => $product['product_image'],
                'product_weight' => $product['weight'],
                'quantity'       => $quantity,
                'box_id'         => $box['id'],
                'box_name'       => $box['box_name'],
                'box_price'      => $box['box_price'],
                'box_image'      => $box['box_image'],
                'custom_text'    => $customText,
                'box_qty'        => $boxQty
            ];
        }

    } else {
        // No Box selected
        $cartKey = $productId . '_none';

        if (isset($_SESSION['cart'][$cartKey])) {
            $_SESSION['cart'][$cartKey]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$cartKey] = [
                'product_id'     => $product['id'],
                'product_name'   => $product['name'],
                'product_price'  => $product['discount_price'],
                'product_image'  => $product['product_image'],
                'product_weight' => $product['weight'],
                'quantity'       => $quantity,
                'box_id'         => null,
                'box_name'       => null,
                'box_price'      => null,
                'box_image'      => null,
                'custom_text'    => $customText,
                'box_qty'        => 0
            ];
        }
    }

    echo "success";
} else {
    error_log('Add to cart: Invalid request. product_id=' . $productId . ', quantity=' . $quantity);
    echo "Invalid request.";
}
