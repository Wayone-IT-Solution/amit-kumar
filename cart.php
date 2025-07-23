<?php
session_start();
require_once 'inc/db.php';

// Handle payment success redirect
if (isset($_GET['payment']) && $_GET['payment'] === 'success') {
    $order_code = $_GET['order_code'] ?? '';
    $amount = $_GET['amount'] ?? 0;
    
    // Show success message
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'Order Placed!',
                html: `<p>Your order has been placed successfully!</p><strong>Order Code: $order_code</strong><br>Delivery: Within 2 days`,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3500,
                timerProgressBar: true
            });
            setTimeout(() => { window.location.href = 'index'; }, 1800);
        });
    </script>";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Cart - Amit Dairy & Sweets</title>
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
  
  <!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- Flatpickr for date picker -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    
<style>
.cart-container {
      max-width: 1200px;
      margin: 20px auto;
      padding: 0 15px;
}

.cart-header {
  display: grid;
      grid-template-columns: 2fr 1fr 1fr 1fr 1fr;
      gap: 15px;
  font-weight: 600;
      padding: 15px;
      background-color: #f8f9fa;
      border-radius: 8px;
      margin-bottom: 20px;
}

.cart-item {
  display: grid;
      grid-template-columns: 2fr 1fr 1fr 1fr 1fr;
      gap: 15px;
  align-items: center;
      padding: 20px;
      border: 1px solid #e9ecef;
      border-radius: 8px;
      margin-bottom: 15px;
      background: white;
      transition: all 0.3s ease;
    }

    .cart-item:hover {
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
} 

.product-info {
  display: flex;
  align-items: center;
      gap: 15px;
}

.product-info img {
      width: 80px;
      height: 80px;
  object-fit: cover;
      border-radius: 8px;
      border: 1px solid #e9ecef;
    }

    .product-details h6 {
      margin: 0 0 5px 0;
  font-weight: 600;
      color: #333;
}

    .product-details .weight {
  font-size: 0.9rem;
      color: #666;
      margin-bottom: 5px;
    }

    .product-details .price {
      font-weight: 600;
      color: #d1a94a;
}

.remove-link {
      color: #dc3545;
      text-decoration: none;
      font-size: 0.85rem;
  cursor: pointer;
      transition: color 0.3s ease;
    }

    .remove-link:hover {
      color: #c82333;
      text-decoration: underline;
    }

    .box-info {
      text-align: center;
    }

    .box-info img {
      width: 50px;
      height: 50px;
      object-fit: cover;
      border-radius: 6px;
      margin-bottom: 5px;
    }

    .box-info .box-name {
  font-weight: 500;
      margin-bottom: 3px;
    }

    .box-info .box-price {
      font-size: 0.9rem;
      color: #666;
    }

    .custom-text-box {
      background-color: #fff8e1;
      border-left: 3px solid #ffc107;
      padding: 10px;
      border-radius: 6px;
      font-size: 0.9rem;
      line-height: 1.4;
    }

    .quantity-controls {
  display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
}

.qty-btn {
      width: 35px;
      height: 35px;
  border: 1px solid #d1a94a;
      background: white;
      color: #d1a94a;
      font-size: 18px;
  cursor: pointer;
  border-radius: 6px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.3s ease;
    }

    .qty-btn:hover:not(:disabled) {
      background: #d1a94a;
      color: white;
    }

    .qty-btn:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }

    .qty-input {
      width: 60px;
      text-align: center;
      border: 1px solid #d1a94a;
      border-radius: 6px;
      padding: 8px;
      font-weight: 500;
    }

    .item-subtotal {
      text-align: center;
      background: #f8f9fa;
      padding: 15px;
      border-radius: 10px;
      border: 1px solid #e9ecef;
    }

    .item-subtotal .total-price {
      font-weight: 700;
      font-size: 1.3rem;
      color: #d1a94a;
      margin-bottom: 8px;
    }

    .item-subtotal .breakdown {
      font-size: 0.85rem;
      color: #666;
      line-height: 1.4;
    }

    .item-subtotal .breakdown .product-line {
      color: #28a745;
      font-weight: 500;
    }

    .item-subtotal .breakdown .box-line {
      color: #ffc107;
      font-weight: 500;
}

.checkout-section {
      background: linear-gradient(135deg, #fff8e1 0%, #fff3cd 100%);
      padding: 30px;
      border-radius: 15px;
      border: 2px solid #d1a94a;
      margin-top: 30px;
      box-shadow: 0 5px 15px rgba(209, 169, 74, 0.1);
    }

    .subtotal-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px 0;
      border-bottom: 2px solid #d1a94a;
      font-size: 1.2rem;
  font-weight: 600;
      color: #333;
    }

    .calculation-breakdown {
      background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
      padding: 25px;
      border-radius: 15px;
      margin: 20px 0;
      border: 2px solid #d1a94a;
      box-shadow: 0 5px 15px rgba(209, 169, 74, 0.1);
    }

    .calculation-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 8px 0;
      border-bottom: 1px solid #f8f9fa;
    }

    .calculation-row:last-child {
      border-bottom: none;
      font-weight: 700;
  font-size: 1.1rem;
      color: #d1a94a;
      border-top: 2px solid #d1a94a;
      padding-top: 15px;
      margin-top: 10px;
    }

    .calculation-label {
      color: #666;
      font-size: 0.95rem;
    }

    .calculation-value {
      font-weight: 600;
      color: #333;
}

.checkout-btn {
      background: linear-gradient(135deg, #d1a94a 0%, #c19a3a 100%);
      color: white;
  font-weight: 600;
      padding: 15px 40px;
  border: none;
      border-radius: 25px;
  cursor: pointer;
      transition: all 0.3s ease;
      width: 100%;
      margin-top: 20px;
      font-size: 1.1rem;
      box-shadow: 0 4px 15px rgba(209, 169, 74, 0.3);
    }

    .checkout-btn:hover:not(:disabled) {
      background: linear-gradient(135deg, #c19a3a 0%, #b08a2a 100%);
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(209, 169, 74, 0.4);
    }

    .checkout-btn:disabled {
      opacity: 0.6;
      cursor: not-allowed;
      transform: none;
    }

    /* SMS Style Success Popup */
    .sms-popup {
      position: fixed;
      top: 20px;
      right: 20px;
      background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
      color: white;
      padding: 20px 25px;
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(40, 167, 69, 0.3);
      z-index: 9999;
      max-width: 350px;
      transform: translateX(400px);
      transition: transform 0.3s ease;
      border-left: 5px solid #fff;
    }

    .sms-popup.show {
      transform: translateX(0);
    }

    .sms-popup .sms-header {
      display: flex;
      align-items: center;
      margin-bottom: 10px;
    }

    .sms-popup .sms-icon {
      width: 40px;
      height: 40px;
      background: rgba(255, 255, 255, 0.2);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-right: 15px;
      font-size: 1.2rem;
    }

    .sms-popup .sms-title {
      font-weight: 700;
      font-size: 1.1rem;
      margin: 0;
    }

    .sms-popup .sms-message {
      font-size: 0.95rem;
      line-height: 1.4;
      margin: 0;
      opacity: 0.95;
    }

    .sms-popup .sms-time {
      font-size: 0.8rem;
      opacity: 0.8;
      margin-top: 8px;
    }

    .empty-cart {
      text-align: center;
      padding: 60px 20px;
      color: #666;
    }

    .empty-cart i {
      font-size: 4rem;
      color: #ddd;
      margin-bottom: 20px;
    }

    /* Modal Styles */
    .modal-content {
      border-radius: 15px;
      border: none;
    }

    .modal-header {
      border-bottom: none;
      padding: 25px 25px 0 25px;
    }

    .modal-body {
      padding: 25px;
    }

    .modal-footer {
      border-top: none;
      padding: 0 25px 25px 25px;
    }

    .form-control {
      background-color: #f8f9fa;
      border: 1px solid #e9ecef;
      border-radius: 8px;
      padding: 12px 15px;
      transition: all 0.3s ease;
    }

    .form-control:focus {
      background-color: white;
      border-color: #d1a94a;
      box-shadow: 0 0 0 0.2rem rgba(209, 169, 74, 0.25);
    }

    .btn-save {
      border: 1px solid #d1a94a;
      background-color: transparent;
      color: #d1a94a;
      transition: all 0.3s ease;
      padding: 8px 16px;
      cursor: pointer;
      border-radius: 20px;
      font-size: 0.9rem;
    }

    .btn-check:checked + .btn-save {
      background-color: #d1a94a;
      color: white;
      border-color: #d1a94a;
    }

    .btn-save:hover {
      background-color: #f8f9fa;
    }

    /* Payment Methods Styles */
    .payment-methods {
      display: flex;
      flex-direction: column;
  gap: 10px;
    }

    .payment-option {
      position: relative;
    }

    .payment-method-card {
      display: flex;
  align-items: center;
      gap: 15px;
      padding: 15px;
      border: 2px solid #e9ecef;
      border-radius: 10px;
      cursor: pointer;
      transition: all 0.3s ease;
      background: white;
    }

    .payment-method-card:hover {
      border-color: #d1a94a;
      background-color: #fff8e1;
    }

    .btn-check:checked + .payment-method-card {
      border-color: #d1a94a;
      background-color: #fff8e1;
      box-shadow: 0 0 0 0.2rem rgba(209, 169, 74, 0.25);
    }

    .payment-icon {
      width: 50px;
      height: 50px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: #f8f9fa;
      border-radius: 10px;
      font-size: 1.5rem;
    }

    .payment-details {
      flex: 1;
    }

    .payment-name {
      font-weight: 600;
      color: #333;
      margin-bottom: 2px;
    }

    .payment-desc {
      font-size: 0.85rem;
      color: #666;
    }

    .payment-fee {
      font-weight: 600;
      color: #d1a94a;
      font-size: 1.1rem;
    }

    /* Responsive */
    @media (max-width: 768px) {
.cart-header, .cart-item {
        grid-template-columns: 1fr;
  gap: 10px;
}

.cart-header {
        display: none;
      }
      
      .product-info {
        flex-direction: column;
        text-align: center;
      }
      
      .quantity-controls {
        justify-content: center;
      }
}
</style>
</head>

<body class="index-page">
  <?php include ('inc/header.php'); ?>
  
  <main class="main">
    <div class="container form-container">
      <div class="form-title">Shopping Cart</div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="index">Home</a></li>
          <li class="breadcrumb-item active" aria-current="page">Cart</li>
        </ol>
      </nav>
    </div>
    
<?php
$cartItems = $_SESSION['cart'] ?? [];
$subtotal = 0;
    $totalItems = 0;

    // Calculate totals
    foreach ($cartItems as $cartKey => $item) {
        $qty = (int)($item['quantity'] ?? 1);
        $boxPrice = floatval($item['box_price'] ?? 0);
        $discountPrice = floatval($item['product_price'] ?? 0);
        $stmt = $conn->prepare("SELECT min_order, max_order, weight_type FROM products WHERE id = ?");
        $stmt->execute([$item['product_id']]);
        $productLimits = $stmt->fetch(PDO::FETCH_ASSOC);
        $minOrder = (int)($productLimits['min_order'] ?? 1);
        $maxOrder = (int)($productLimits['max_order'] ?? 999);
        $weightType = strtolower($productLimits['weight_type'] ?? 'g');
        $weightValue = isset($item['selected_type']) ? (float)$item['selected_type'] : 1000;
        if ($weightType === 'unit') {
            $weightValue = 1;
            $weightUnit = 'unit';
            $totalWeight = $qty;
            $productTotal = $qty * $discountPrice;
        } else {
            $weightUnit = $weightType;
            $totalWeight = $weightValue * $qty;
            $productTotal = ($weightValue * $qty / 1000) * $discountPrice;
        }
        $boxTotal = $boxPrice * (int)($item['box_qty'] ?? 1);
        $itemTotal = $productTotal + $boxTotal;
        $subtotal += $itemTotal;
        $totalItems += $qty;
    }

    // Get minimum order amount
    $stmt = $conn->query("SELECT value FROM settings ORDER BY id DESC LIMIT 1");
    $minAmount = (float)($stmt->fetchColumn() ?? 1500);

    // Get user info if logged in
    $receiver_name = '';
    $receiver_phone = '';
    if (isset($_SESSION['user_id'])) {
        $stmt = $conn->prepare("SELECT fullname, phone FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $receiver_name = $user['fullname'];
            $receiver_phone = $user['phone'];
        }
    }

    // Get blocked dates and time slots
   $stmt = $conn->query("SELECT DISTINCT blocked_date FROM blocked_slots WHERE blocked_date IS NOT NULL");
$blockedDates = json_encode($stmt->fetchAll(PDO::FETCH_COLUMN));
    

    $stmt = $conn->query("SELECT DISTINCT start_time, end_time FROM blocked_slots WHERE start_time IS NOT NULL AND end_time IS NOT NULL ORDER BY start_time");
    $timeSlots = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <div class="cart-container">
      <?php if (!empty($cartItems)): ?>
  <div class="cart-header">
    <div>Product</div>
    <div>Box</div>
    <div>Custom Text</div>
    <div>Quantity</div>
    <div>Subtotal</div>
  </div>

        <?php foreach ($cartItems as $cartKey => $item): 
  $qty = (int)($item['quantity'] ?? 1);
  $boxPrice = floatval($item['box_price'] ?? 0);
  $discountPrice = floatval($item['product_price'] ?? 0);
  $stmt = $conn->prepare("SELECT min_order, max_order, weight_type FROM products WHERE id = ?");
  $stmt->execute([$item['product_id']]);
  $productLimits = $stmt->fetch(PDO::FETCH_ASSOC);
  $minOrder = (int)($productLimits['min_order'] ?? 1);
  $maxOrder = (int)($productLimits['max_order'] ?? 999);
  $weightType = strtolower($productLimits['weight_type'] ?? 'g');
  $productWeight = $item['product_weight'] ?? '1g';
  preg_match('/^([\d.]+)\s*([a-zA-Z]+)$/', $productWeight, $matches);
  $weightValue = isset($matches[1]) ? (float)$matches[1] : 1;
  $weightUnit = isset($matches[2]) ? $matches[2] : $weightType;
  $totalWeight = $weightValue * $qty;
  if ($weightType === 'unit') {
    $productTotal = $qty * $discountPrice;
  } else {
    $productTotal = ($weightValue * $qty / 1000) * $discountPrice;
  }
  $boxTotal = $boxPrice * (int)($item['box_qty'] ?? 1);
  $itemTotal = $productTotal + $boxTotal;
  $subtotal += $itemTotal;
  $totalItems += $qty;

            // Get product limits
      $stmt = $conn->prepare("SELECT min_order, max_order, weight_type FROM products WHERE id = ?");
      $stmt->execute([$item['product_id']]);
      $productLimits = $stmt->fetch(PDO::FETCH_ASSOC);
      $minOrder = (int)($productLimits['min_order'] ?? 1);
      $maxOrder = (int)($productLimits['max_order'] ?? 999);
            $weightType = $productLimits['weight_type'] ?? 'g';

            // Calculate weight
            $productWeight = $item['product_weight'] ?? '1g';
            preg_match('/^([\d.]+)\s*([a-zA-Z]+)$/', $productWeight, $matches);
            $weightValue = isset($matches[1]) ? (float)$matches[1] : 1;
            $weightUnit = isset($matches[2]) ? $matches[2] : $weightType;
            $totalWeight = $weightValue * $qty;
        ?>
        <div class="cart-item" data-cart-key="<?= htmlspecialchars($cartKey) ?>">
          <!-- Product Info -->
          <div class="product-info">
            <img src="admin/<?= htmlspecialchars($item['product_image']) ?>" alt="<?= htmlspecialchars($item['product_name']) ?>">
            <div class="product-details">
              <h6><?= htmlspecialchars($item['product_name']) ?></h6>
              <?php
                // Use session cart's product_weight/type for display
                $isUnit = false;
                if (
                    (isset($item['selected_type']) && $item['selected_type'] == 1 && isset($productLimits['weight_type']) && strtolower($productLimits['weight_type']) === 'unit') ||
                    (isset($item['product_weight']) && (stripos($item['product_weight'], 'unit') !== false || $item['product_weight'] == '1unit'))
                ) {
                    $isUnit = true;
                }
                if ($isUnit) {
                    $weightValue = 1;
                    $weightUnit = 'unit';
                    $totalWeight = $qty;
                } else {
                    $weightValue = isset($item['selected_type']) ? (float)$item['selected_type'] : 1000;
                    $weightUnit = $weightType;
                    $totalWeight = $weightValue * $qty;
                }
              ?>
              <div class="weight"><?= $weightValue ?> <?= $weightUnit ?> × <?= $qty ?> = <?= $totalWeight ?> <?= $weightUnit ?></div>
              <div class="price"> </div>
              <a href="inc/remove_from_cart?key=<?= urlencode($cartKey) ?>" class="remove-link" onclick="return false;" data-cart-key="<?= htmlspecialchars($cartKey) ?>">Remove</a>
            </div>
      </div>

          <!-- Box Info -->
         <div class="box-info">
  <?php if (!empty($item['box_image'])): ?>
    <img src="admin/<?= htmlspecialchars($item['box_image']) ?>" alt="<?= htmlspecialchars($item['box_name']) ?>">
  <?php endif; ?>

  <div class="box-name"><?= htmlspecialchars($item['box_name'] ?: 'No Box') ?></div>
  <div class="box-price">₹<?= number_format($boxPrice, 2) ?></div>
  
  <?php if (!empty($item['box_qty'])): ?>
    <div class="box-qty">Number of Boxes: <?= (int)$item['box_qty'] ?></div>
  <?php endif; ?>
</div>

          <!-- Custom Text -->
      <div>
        <?php if (!empty($item['custom_text'])): ?>
          <div class="custom-text-box"><?= nl2br(htmlspecialchars($item['custom_text'])) ?></div>
        <?php else: ?>
          <div class="text-muted">—</div>
        <?php endif; ?>
      </div>

          <!-- Quantity Controls -->
          <div class="quantity-controls">
            <button type="button" class="qty-btn decrease-qty" data-cart-key="<?= htmlspecialchars($cartKey) ?>" <?= $qty <= $minOrder ? 'disabled' : '' ?>>−</button>
            <input type="number" class="qty-input" data-cart-key="<?= htmlspecialchars($cartKey) ?>" data-min="<?= $minOrder ?>" data-max="<?= $maxOrder ?>" value="<?= $qty ?>" min="<?= $minOrder ?>" max="<?= $maxOrder ?>">
            <button type="button" class="qty-btn increase-qty" data-cart-key="<?= htmlspecialchars($cartKey) ?>" <?= $qty >= $maxOrder ? 'disabled' : '' ?>>+</button>
      </div>

          <!-- Subtotal -->
          <div class="item-subtotal">
            <div class="total-price">₹<?= number_format($itemTotal, 2) ?></div>
            <div class="breakdown">
              <?php if ($isUnit): ?>
                <div class="product-line">Product: ₹<?= number_format($discountPrice, 2) ?> × <?= $qty ?> unit<?= $qty > 1 ? 's' : '' ?> = ₹<?= number_format($productTotal, 2) ?></div>
              <?php else: ?>
                <div class="product-line">Product: <?= $weightValue ?> <?= $weightUnit ?> × <?= $qty ?> = <?= $totalWeight ?> <?= $weightUnit ?>, ₹<?= number_format($discountPrice, 2) ?> per kg = ₹<?= number_format($productTotal, 2) ?></div>
              <?php endif; ?>
              <?php if ($boxTotal > 0): ?>
                <div class="box-line">Box: ₹<?= number_format($boxPrice, 2) ?> × <?= (int)($item['box_qty'] ?? 1) ?> = ₹<?= number_format($boxTotal, 2) ?></div>
              <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>

        <!-- Checkout Section -->
        <div class="checkout-section">
          <div class="subtotal-row d-flex justify-content-between align-items-center mb-3">
            <span>Total Items: <?= $totalItems ?></span>
            <span>Subtotal: ₹<?= number_format($subtotal, 2) ?></span>
</div>

          <!-- Detailed Calculation Breakdown -->
          <div class="calculation-breakdown">
            <h6 class="mb-3 fw-bold text-center">Order Calculation Breakdown</h6>
<?php
            $totalProductCost = 0;
            $totalBoxCost = 0;
            $totalQuantity = 0;
            $totalBoxes = 0;
            
            foreach ($cartItems as $item) {
                $qty = (int)($item['quantity'] ?? 1);
                $productPrice = floatval($item['product_price'] ?? 0);
                $boxPrice = floatval($item['box_price'] ?? 0);
                $boxQty = (int)($item['box_qty'] ?? 1);
                
                $totalProductCost += $productPrice * $qty;
                $totalBoxCost += $boxPrice * $boxQty;
                $totalQuantity += $qty;
                $totalBoxes += $boxQty;
            }
            ?>
            
            <!-- <div class="calculation-row">
              <span class="calculation-label">Products Cost:</span>
              <span class="calculation-value">₹<?= number_format($totalProductCost, 2) ?></span>
        </div> -->

            <?php if ($totalBoxCost > 0): ?>
            <div class="calculation-row">
              <span class="calculation-label">Sweet Boxes Cost:</span>
              <span class="calculation-value">₹<?= number_format($totalBoxCost, 2) ?></span>
        </div>
            <?php endif; ?>
            
            <div class="calculation-row">
              <span class="calculation-label">Delivery Charges:</span>
              <span class="calculation-value">₹0.00 (Free)</span>
        </div>

            <div class="calculation-row">
              <span class="calculation-label">Total Amount:</span>
              <span class="calculation-value total-amount-value">₹<?= number_format($subtotal, 2) ?></span>
        </div>

            <?php if ($subtotal < $minAmount): ?>
            <div class="alert alert-warning mt-3 mb-0">
              <i class="bi bi-exclamation-triangle me-2"></i>
              Minimum order amount is ₹<?= number_format($minAmount, 2) ?>. Add more items to proceed.
  </div>
            <?php endif; ?>
  </div>

          <div class="d-flex justify-content-end">
            <button 
              type="button" 
              class="checkout-btn w-auto" 
              data-subtotal="<?= $subtotal ?>" 
              data-minimum="<?= $minAmount ?>" 
              <?= empty($cartItems) ? 'disabled' : '' ?>>
              <i class="bi bi-cart-check me-2"></i>Proceed to Checkout
            </button>
  </div>
  </div>


      <?php else: ?>
        <div class="empty-cart">
          <i class="bi bi-cart-x"></i>
          <h4>Your cart is empty</h4>
          <p>Add some delicious products to your cart to get started!</p>
          <a href="index" class="btn btn-warning">Continue Shopping</a>
        </div>
      <?php endif; ?>
</div>

    <!-- Include Flatpickr and SweetAlert2 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
        // Blocked dates from PHP
        const blockedDates = <?= $blockedDates ?>;

        // Add today + 1 day to blocked list
    const today = new Date();
    const plusOne = new Date(today);
    plusOne.setDate(today.getDate() + 1);

    const formatDate = d => d.toISOString().split('T')[0];

    blockedDates.push(formatDate(today));
    blockedDates.push(formatDate(plusOne));

    flatpickr("#delivery_date", {
    dateFormat: "Y-m-d",
    minDate: new Date(),
            disable: blockedDates,
    onDayCreate: function (_, __, ___, dayElem) {
        const dateObj = dayElem.dateObj;
        const dateStr = dateObj.getFullYear() + '-' +
            String(dateObj.getMonth() + 1).padStart(2, '0') + '-' +
            String(dateObj.getDate()).padStart(2, '0');

        if (blockedDates.includes(dateStr)) {
            dayElem.classList.add("blocked-date");
            dayElem.style.backgroundColor = "#f8d7da";
            dayElem.style.color = "#842029";
            dayElem.style.border = "1px solid #dc3545";
            dayElem.style.opacity = "0.6";
            dayElem.style.cursor = "not-allowed";

            dayElem.addEventListener('click', function (e) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Unavailable Date',
                    text: 'This date is unavailable for delivery. Please choose another.',
                    confirmButtonColor: '#d33'
                });
            });
        }
    },
    onChange: function (selectedDates, dateStr) {
        if (blockedDates.includes(dateStr)) {
            Swal.fire({
                icon: 'warning',
                title: 'Blocked Date',
                text: 'This date is blocked. Please choose another.',
                confirmButtonColor: '#d33'
            });
            this.clear();
        }
    }
});
});
</script>

<style>
.blocked-date {
    font-weight: bold;
    text-decoration: line-through;
}
</style>

    <!-- SMS Style Success Popup -->
    <div id="smsPopup" class="sms-popup">
      <div class="sms-header">
        <div class="sms-icon">
          <i class="bi bi-check-circle"></i>
        </div>
        <h6 class="sms-title">Order Successful!</h6>
      </div>
      <p class="sms-message" id="smsMessage">Your order has been placed successfully.</p>
      <div class="sms-time" id="smsTime"></div>
    </div>

    <!-- Checkout Modal -->
<div class="modal fade" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <form id="placeOrderForm" method="post" class="modal-content border-0 shadow rounded-4 p-4 bg-white">
          <div class="modal-body p-0">
            <div class="mb-4">
              <div class="d-flex align-items-start">
                <div class="me-2"><i class="bi bi-geo-alt-fill text-warning fs-4"></i></div>
                <div>
                  <div class="fw-bold" id="modal-title">Add Address</div>
                  <div class="text-muted small">Please add your delivery details below</div>
                </div>
              </div>
            </div>

            <!-- Address Fields -->
            <div class="mb-3">
              <input type="text" class="form-control border-0 rounded-3" name="address_details" placeholder="Add Detailed Address" required>
            </div>

            <div class="mb-3">
              <label class="form-label small">House / Flat / Block No.</label>
              <input type="text" class="form-control border-0 rounded-3" name="house_block" required>
            </div>

            <div class="mb-3">
              <label class="form-label small">Apartment / Road / Area</label>
              <input type="text" class="form-control border-0 rounded-3" name="area_road" required>
            </div>

            <!-- Save As Buttons -->
            <label class="form-label small d-block mb-2">Save As</label>
            <div class="mb-4 d-flex flex-wrap gap-2">
              <?php foreach (['Home', 'Work', 'Friends & Family', 'Others'] as $option): ?>
                <div>
                  <input type="radio" class="btn-check" name="save_as" id="<?= strtolower(str_replace(' ', '_', $option)) ?>" value="<?= $option ?>" <?= $option === 'Home' ? 'checked' : '' ?>>
                  <label class="btn-save px-3" for="<?= strtolower(str_replace(' ', '_', $option)) ?>"><?= $option ?></label>
                </div>
              <?php endforeach; ?>
            </div>

            <!-- Delivery Date & Time -->
            <div class="mb-3">
              <label class="form-label small">Delivery Date</label>
              <input type="text" class="form-control border-0 rounded-3" name="delivery_date" id="delivery_date" required placeholder="Select a date">
            </div>

<div class="mb-4">
  <label class="form-label small">Delivery Time</label>
  <select class="form-control border-0 rounded-3" name="delivery_time" id="delivery_time" required>
    <option value="">Select Delivery Time</option>
    <?php foreach ($timeSlots as $time): ?>
      <?php
        $startFormatted = date("g:i A", strtotime($time['start_time']));
        $endFormatted = date("g:i A", strtotime($time['end_time']));
        $label = "$startFormatted - $endFormatted";
                    $value = $time['start_time'] . '-' . $time['end_time'];
      ?>
      <option value="<?= htmlspecialchars($value) ?>"><?= $label ?></option>
    <?php endforeach; ?>
  </select>
</div>

            <!-- Receiver Info -->
            <div class="mb-3">
              <label class="form-label small">Receiver's Name</label>
              <input type="text" class="form-control border-0 rounded-3" name="receiver_name" value="<?= htmlspecialchars($receiver_name) ?>" required>
            </div>

            <div class="mb-4">
              <label class="form-label small">Receiver's Phone Number</label>
              <input type="text" class="form-control border-0 rounded-3" name="receiver_phone" value="<?= htmlspecialchars($receiver_phone) ?>" required>
            </div>

            <!-- Submit Button -->
        <div class="d-grid">
          <button type="submit" class="btn rounded-pill text-white fw-semibold" style="background-color: #d1ae5e;">
            Place Order
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize date picker
        const blockedDates = <?= $blockedDates ?>;
        const today = new Date();
        const tomorrow = new Date(today);
        tomorrow.setDate(today.getDate() + 1);
        
        // Add today and tomorrow to blocked dates
        const formatDate = d => d.toISOString().split('T')[0];
    blockedDates.push(formatDate(today));
        blockedDates.push(formatDate(tomorrow));

    flatpickr("#delivery_date", {
    dateFormat: "Y-m-d",
    minDate: new Date(),
            disable: blockedDates,
            onDayCreate: function(_, __, ___, dayElem) {
        const dateObj = dayElem.dateObj;
        const dateStr = dateObj.getFullYear() + '-' +
            String(dateObj.getMonth() + 1).padStart(2, '0') + '-' +
            String(dateObj.getDate()).padStart(2, '0');

        if (blockedDates.includes(dateStr)) {
            dayElem.classList.add("blocked-date");
            dayElem.style.backgroundColor = "#f8d7da";
            dayElem.style.color = "#842029";
            dayElem.style.border = "1px solid #dc3545";
            dayElem.style.opacity = "0.6";
            dayElem.style.cursor = "not-allowed";
        }
    }
});

        // Quantity controls
        function updateQuantity(cartKey, action) {
            const cartItem = document.querySelector(`[data-cart-key="${cartKey}"]`);
            const qtyInput = cartItem.querySelector('.qty-input');
            const decreaseBtn = cartItem.querySelector('.decrease-qty');
            const increaseBtn = cartItem.querySelector('.increase-qty');
            
            let currentQty = parseInt(qtyInput.value);
            const minQty = parseInt(qtyInput.dataset.min);
            const maxQty = parseInt(qtyInput.dataset.max);
            
            if (action === 'increase' && currentQty < maxQty) {
                currentQty++;
            } else if (action === 'decrease' && currentQty > minQty) {
                currentQty--;
            } else {
                return;
            }
            
            // Update input and buttons
            qtyInput.value = currentQty;
            decreaseBtn.disabled = currentQty <= minQty;
            increaseBtn.disabled = currentQty >= maxQty;
            
            // Send update request
            updateCartItem(cartKey, currentQty);
        }

        function updateCartItem(cartKey, quantity) {
            console.log('Updating cart item:', cartKey, 'quantity:', quantity);
            
            const formData = new URLSearchParams();
            formData.append('cart_key', cartKey);
            formData.append('product_qty', quantity);

            console.log('Sending request to update_cart');
            
            fetch('inc/update_cart', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData.toString()
            })
            .then(res => {
                console.log('Response status:', res.status);
                return res.text().then(text => {
                    console.log('Response text:', text);
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('JSON parse error:', e);
                        throw new Error('Invalid JSON response: ' + text);
                    }
                });
            })
            .then(data => {
                if (data.success) {
                    // Update cart item display
                    const cartItem = document.querySelector(`[data-cart-key="${cartKey}"]`);
                    const weightDisplay = cartItem.querySelector('.weight');
                    const subtotalDisplay = cartItem.querySelector('.total-price');
                    const breakdownDisplay = cartItem.querySelector('.breakdown');
                    
                    // Update weight display
                    weightDisplay.textContent = `${data.unit_weight} ${data.weight_type} × ${data.product_qty} = ${data.total_weight} ${data.weight_type}`;
                    
                    // Update subtotal
                    subtotalDisplay.textContent = `₹${data.item_total.toFixed(2)}`;
                    
                    // Update breakdown
                    let breakdown = `Product: ₹${data.product_total.toFixed(2)}`;
                    if (data.box_total > 0) {
                        breakdown += `<br>Box: ₹${data.box_total.toFixed(2)}`;
                    }
                    breakdownDisplay.innerHTML = breakdown;
                    
                    // Update cart total
                    document.querySelector('.subtotal-row span:last-child').textContent = `Subtotal: ₹${data.subtotal.toFixed(2)}`;
                    document.querySelector('.total-amount-value').textContent = `₹${data.subtotal.toFixed(2)}`;
                    
                    // Update checkout button data
                    const checkoutBtn = document.querySelector('.checkout-btn');
                    checkoutBtn.dataset.subtotal = data.subtotal;
                    
                    // Show success message
      Swal.fire({
        icon: 'success',
                        title: 'Updated!',
                        text: 'Cart updated successfully',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2000
                    });
      } else {
                    throw new Error(data.message || 'Update failed');
                }
            })
            .catch(error => {
                console.error('Error:', error);
    Swal.fire({
      icon: 'error',
                    title: 'Error',
                    text: error.message || 'Failed to update cart',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
    });
  });
        }

        // Event listeners for quantity buttons
        document.querySelectorAll('.decrease-qty').forEach(btn => {
            btn.addEventListener('click', function() {
                const cartKey = this.dataset.cartKey;
                updateQuantity(cartKey, 'decrease');
            });
        });

        document.querySelectorAll('.increase-qty').forEach(btn => {
            btn.addEventListener('click', function() {
                const cartKey = this.dataset.cartKey;
                updateQuantity(cartKey, 'increase');
            });
        });

        // Event listener for quantity input
        document.querySelectorAll('.qty-input').forEach(input => {
            input.addEventListener('change', function() {
                const cartKey = this.dataset.cartKey;
                let qty = parseInt(this.value);
                const minQty = parseInt(this.dataset.min);
                const maxQty = parseInt(this.dataset.max);
                
                if (isNaN(qty) || qty < minQty) qty = minQty;
                if (qty > maxQty) qty = maxQty;
                
                this.value = qty;
                updateCartItem(cartKey, qty);
            });
        });

        // Checkout button
        document.querySelector('.checkout-btn').addEventListener('click', function() {
            const subtotal = parseFloat(this.dataset.subtotal);
            const minimum = parseFloat(this.dataset.minimum);
            
            if (subtotal < minimum) {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'info',
                    title: 'Minimum Order Amount Required',
                    html: `<p>Your total: ₹${subtotal.toFixed(2)}</p><p>Minimum required: ₹${minimum.toFixed(2)}</p><p>Please add more items to your cart.</p>`,
                    showConfirmButton: false,
                    timer: 4000,
                    timerProgressBar: true,
                    customClass: { popup: 'sms-toast' }
                });
                return;
            }
            
            // Open checkout modal
            const modal = new bootstrap.Modal(document.getElementById('checkoutModal'));
            modal.show();
        });

        // Form submission
        document.getElementById('placeOrderForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const subtotal = parseFloat(document.querySelector('.checkout-btn').dataset.subtotal);
            const minimum = parseFloat(document.querySelector('.checkout-btn').dataset.minimum);
            
            // Validate minimum order
            if (subtotal < minimum) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Minimum Order Required',
                    text: `Order total must be at least ₹${minimum.toFixed(2)}`,
                    confirmButtonText: 'OK'
                });
                return;
            }
            
            // Show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Processing...';
            
            fetch('inc/place_order', {
      method: 'POST',
                body: formData
    })
      .then(res => res.json())
      .then(data => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Place Order';
                
        if (data.success) {
                    // COD or wallet payment completed
                    Swal.fire({
                        icon: 'success',
                        title: '🎉 Order Placed Successfully!',
                        text: data.message,
                        confirmButtonText: 'Continue Shopping',
                        allowOutsideClick: false
                    }).then(() => {
                        // Show SMS popup and redirect
                        showSMSSuccess(data.message, data.order_code);
                        setTimeout(() => {
                            window.location.href = 'cart?payment=success&order_code=' + data.order_code + '&amount=' + data.subtotal;
                        }, 1000);
                    });
                } else if (data.errors) {
                    const errorMessages = Array.isArray(data.errors) ? data.errors.join('\n') : Object.values(data.errors).join('\n');
                    showError('Please fix the following errors:', errorMessages);
                } else {
                    showError('Order Failed', data.error || 'Something went wrong. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Place Order';
                
                showError('Network Error', 'Please check your internet connection and try again.');
    });
  });

  // SMS Style Success Popup Function
  function showSMSSuccess(message, orderCode = '') {
    const smsPopup = document.getElementById('smsPopup');
    const smsMessage = document.getElementById('smsMessage');
    const smsTime = document.getElementById('smsTime');
    
    // Set message
    smsMessage.textContent = message;
    
    // Set current time
    const now = new Date();
    const timeString = now.toLocaleTimeString('en-IN', { 
      hour: '2-digit', 
      minute: '2-digit',
      hour12: true 
    });
    smsTime.textContent = timeString;
    
    // Show popup
    smsPopup.classList.add('show');
    
    // Auto hide after 5 seconds
    setTimeout(() => {
      smsPopup.classList.remove('show');
    }, 5000);
  }

  // Show SMS popup for successful orders
  if (window.location.search.includes('payment=success')) {
    const urlParams = new URLSearchParams(window.location.search);
    const orderCode = urlParams.get('order_code') || '';
    const amount = urlParams.get('amount') || '';
    
    const message = orderCode ? 
      `Order #${orderCode} placed successfully! Amount: ₹${amount}` : 
      'Order placed successfully!';
    
    showSMSSuccess(message, orderCode);
  }

  // Calendar functionality
  document.addEventListener('DOMContentLoaded', function () {
      // Blocked dates from PHP
      const blockedDates = <?= $blockedDates ?>;

      // Add today + 1 day to blocked list
      const today = new Date();
      const plusOne = new Date(today);
      plusOne.setDate(today.getDate() + 1);

      const formatDate = d => d.toISOString().split('T')[0];

      blockedDates.push(formatDate(today));
      blockedDates.push(formatDate(plusOne));

      flatpickr("#delivery_date", {
          dateFormat: "Y-m-d",
          minDate: new Date(),
          disable: blockedDates,
          onDayCreate: function (_, __, ___, dayElem) {
              const dateObj = dayElem.dateObj;
              const dateStr = dateObj.getFullYear() + '-' +
                  String(dateObj.getMonth() + 1).padStart(2, '0') + '-' +
                  String(dateObj.getDate()).padStart(2, '0');

              if (blockedDates.includes(dateStr)) {
                  dayElem.classList.add("blocked-date");
                  dayElem.style.backgroundColor = "#f8d7da";
                  dayElem.style.color = "#842029";
                  dayElem.style.border = "1px solid #dc3545";
                  dayElem.style.opacity = "0.6";
                  dayElem.style.cursor = "not-allowed";

                  dayElem.addEventListener('click', function (e) {
        e.preventDefault();
                      Swal.fire({
                          icon: 'error',
                          title: 'Unavailable Date',
                          text: 'This date is unavailable for delivery. Please choose another.',
                          confirmButtonColor: '#d33'
                      });
                  });
              }
          },
          onChange: function (selectedDates, dateStr) {
              if (blockedDates.includes(dateStr)) {
                  Swal.fire({
                      icon: 'warning',
                      title: 'Blocked Date',
                      text: 'This date is blocked. Please choose another.',
                      confirmButtonColor: '#d33'
                  });
                  this.clear();
              }
          }
    });
  });
});
</script>

<style>
.blocked-date {
    font-weight: bold;
    text-decoration: line-through;
}
</style>

<script>
document.querySelectorAll('.remove-link').forEach(function(link) {
  link.addEventListener('click', function(e) {
    e.preventDefault();
    const url = this.getAttribute('href');
    Swal.fire({
      title: 'Remove this item?',
      icon: 'warning',
      iconColor: '#ffc107',
      showCancelButton: true,
      confirmButtonText: 'Yes, remove',
      cancelButtonText: 'Cancel',
      confirmButtonColor: '#ffc107',
      cancelButtonColor: '#aaa',
      focusCancel: true
    }).then((result) => {
      if (result.isConfirmed) {
        window.location.href = url;
      }
    });
  });
});
</script>

  </main>

  <?php include ('inc/footer.php'); ?>

  <!-- Vendor JS Files -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

  <!-- SMS Notifications -->
  <link rel="stylesheet" href="assets/css/sms-notifications.css">
  <script src="assets/js/sms-notifications.js"></script>

  <!-- Include Flatpickr and SweetAlert2 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>