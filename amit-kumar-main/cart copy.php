<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Amit Dairy & Sweets</title>
  <meta name="description" content="">
  <meta name="keywords" content="">

  <!-- Favicons -->
  <link href="assets/img/logo.webp" rel="icon">
  <link href="assets/img/logo.webp" rel="apple-touch-icon">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Montserrat:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

  <!-- Main CSS File -->
  <link href="assets/css/main.css" rel="stylesheet">


</head>

<body class="index-page">

  <?php include ('inc/header.php'); ?>
  
  <main class="main">
    
<style>

.cart-container {
  max-width: 1000px;
  margin: 15px auto;

}

.cart-header {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 1fr;
  font-weight: 600;
  margin-bottom: 1.5rem;
}

.cart-item {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 1fr;
  align-items: center;
  padding: 1rem 0;
}

.product-info {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.product-info img {
  width: 110px;
  height: 110px;
  object-fit: cover;
  border-radius: 6px;
}

.details {
  display: flex;
  flex-direction: column;
}

.details .name {
  font-weight: 600;
  font-size: 1rem;
}

.details .weight {
  font-size: 0.9rem;
  margin-top: 4px;
}

.remove-link {
  font-size: 0.8rem;
  margin-top: 6px;
  color: #000;
  text-decoration: underline;
  cursor: pointer;
}

.price,
.total {
  font-weight: 500;
}

.quantity {
  display: flex;
  align-items: start;
  justify-content: start;
  gap: 0.9rem;
}

.qty-btn {
  width: 30px;
  height: 30px;
  border: 1px solid #d1a94a;
  background: transparent;
  color: #000;
  font-size: 16px;
  cursor: pointer;
  border-radius: 6px;
}

.checkout-section {
  margin-top: 2rem;
  display: block;
  justify-content: end;
  align-items: end;
}

.subtotal {
  font-weight: 600;
  font-size: 1.1rem;
}

.checkout-btn {
  background: #d1a94a;
  color: #000;
  font-weight: 600;
  padding: 0.8rem 2rem;
  border: none;
  border-radius: 10px;
  cursor: pointer;
}

   
  </style>


<div class="container form-container">
  <div class="form-title">Shopping Cart</div>
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="#">Home</a></li>
      <li class="breadcrumb-item active" aria-current="page">Cart</li>
    </ol>
  </nav>

</div>


<?php


$productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
$boxId = isset($_POST['box_id']) ? (int)$_POST['box_id'] : 0;

if ($productId > 0 && $quantity > 0 && $boxId > 0) {
    // Fetch product
    $stmt = $conn->prepare("SELECT id, name, price, product_image, weight FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch box
    $stmt = $conn->prepare("SELECT id, box_name, box_price, box_image FROM boxes WHERE id = ?");
    $stmt->execute([$boxId]);
    $box = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product && $box) {
        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

        // Create a unique key for product + box combination
        $cartKey = $productId . '_' . $boxId;

        if (isset($_SESSION['cart'][$cartKey])) {
            $_SESSION['cart'][$cartKey]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$cartKey] = [
                'product_id' => $product['id'],
                'product_name' => $product['name'],
                'product_price' => $product['price'],
                'product_image' => $product['product_image'],
                'product_weight' => $product['weight'],
                'quantity' => $quantity,
                'box_id' => $box['id'],
                'box_name' => $box['box_name'],
                'box_price' => $box['box_price'],
                'box_image' => $box['box_image']
            ];
        }

        echo "success";
    } else {
        echo "Product or box not found.";
    }
} 
?>



<div class="cart-container">
  

 <?php

$cartItems = $_SESSION['cart'] ?? [];
$subtotal = 0;
?>

<div class="cart-container">
  <div class="cart-header">
    <div>Product</div>
    <div>Box</div>
    <div>Price</div>
    <div>Quantity</div>
    <div>Total</div>
  </div>

  <?php if (!empty($cartItems)): ?>
    <?php foreach ($cartItems as $key => $item): 
      $itemTotal = ($item['product_price'] + $item['box_price']) * $item['quantity'];
      $subtotal += $itemTotal;
    ?>
    <div class="cart-item">
      <!-- Product Info -->
      <div class="product-info">
        <img src="admin/<?= htmlspecialchars($item['product_image']) ?>" alt="<?= htmlspecialchars($item['product_name']) ?>" width="60">
        <div class="details">
          <div class="name"><?= htmlspecialchars($item['product_name']) ?></div>
         <?php
$weight = floatval($item['product_weight']); // extract numeric part
$qty = (int)$item['quantity'];
$totalWeight = $weight * $qty;
?>
<div class="weight">
  <?= htmlspecialchars($weight) ?>kg × <?= $qty ?> = <?= $totalWeight ?>kg
</div>
<div class="total">
        ₹ <?= number_format($itemTotal, 2) ?>
        <br />
        <a href="inc/remove_from_cart.php?key=<?= urlencode($key) ?>" class="remove-link">Remove</a>
      </div>


        </div>
      </div>

      <!-- Box Info -->
      <div class="box-info">
        <img src="admin/<?= htmlspecialchars($item['box_image']) ?>" alt="<?= htmlspecialchars($item['box_name']) ?>" width="60">
        <div class="details">
  <div class="name"><?= htmlspecialchars($item['box_name']) ?></div>
  <div class="price">₹ <?= number_format($item['box_price'] * $item['quantity'], 2) ?> (<?= $item['quantity'] ?> × ₹<?= number_format($item['box_price'], 2) ?>)</div>
</div>

      </div>

      <!-- Price (Product + Box) -->
      <div class="price">
  ₹ <?= number_format(($item['product_price'] + $item['box_price']) * $item['quantity'], 2) ?>
  <div class="small text-muted">
    (<?= $item['quantity'] ?> × ₹<?= number_format($item['product_price'] + $item['box_price'], 2) ?>)
  </div>
</div>

      <!-- Quantity Controls -->
      <div class="quantity">
        <form action="inc/update_cart" method="post" class="d-flex align-items-center">
          <input type="hidden" name="cart_key" value="<?= htmlspecialchars($key) ?>">
          <button type="submit" name="action" value="decrease" class="qty-btn">−</button>
          <span class="mx-2"><?= $item['quantity'] ?></span>
          <button type="submit" name="action" value="increase" class="qty-btn">+</button>
        </form>
      </div>

      <!-- Total -->
      
    </div>
    <hr />
    <?php endforeach; ?>
  <?php else: ?>
    <p class="text-center my-4">Your cart is empty.</p>
  <?php endif; ?>

  <!-- Checkout Section -->
  <div class="checkout-section">
    <div class="subtotal">
      <span>Subtotal</span>
      <span>₹ <?= number_format($subtotal, 2) ?></span>
    </div>
    <button class="checkout-btn" <?= empty($cartItems) ? 'disabled' : '' ?>>Checkout</button>
  </div>
</div>


    


   

  </main>

  <?php include ('inc/footer.php'); ?>

  <!-- Vendor JS Files -->
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
  <script src="assets/vendor/purecounter/purecounter_vanilla.js"></script>
  <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>

  <!-- Main JS File -->
  <script src="assets/js/main.js"></script>

</body>

</html>