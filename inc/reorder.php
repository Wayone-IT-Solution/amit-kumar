<?php
session_start();

// Check if cart_data was sent
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cart_data'])) {
    $cartData = json_decode($_POST['cart_data'], true);

    if (is_array($cartData)) {
        // Clear existing cart or merge — here we append
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        foreach ($cartData as $item) {
            // Generate a unique key (product ID + box name for example)
            $key = $item['product_id'] . '_' . $item['box_name'];

            if (isset($_SESSION['cart'][$key])) {
                // If item already exists, increase quantity
                $_SESSION['cart'][$key]['quantity'] += (int)$item['quantity'];
            } else {
                // Add new item to cart
                $_SESSION['cart'][$key] = [
                    'product_id'     => $item['product_id'],
                    'product_name'   => $item['product_name'],
                    'product_image'  => $item['product_image'],
                    'product_weight' => $item['product_weight'],
                    'product_price'  => (float)$item['product_price'],
                    'box_image'      => $item['box_image'],
                    'box_name'       => $item['box_name'],
                    'box_price'      => (float)$item['box_price'],
                    'quantity'       => (int)$item['quantity']
                ];
            }
        }

        // Redirect to cart page
        header("Location: ../cart");
        exit;
    } else {
        echo "Invalid cart data.";
    }
} else {
    echo "No data received.";
}
