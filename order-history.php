
<?php
session_start();

// Assuming you've stored the user ID in session during login like:
// $_SESSION['user_id'] = $user['id'];

if (!isset($_SESSION['user_id'])) {
    // Redirect to login if not logged in
    header("Location: login");
    exit();
}

$user_id = $_SESSION['user_id'];
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
  <link
    href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Montserrat:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
    rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

  <!-- Main CSS File -->
  <link href="assets/css/main.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>



</head>

<body class="index-page">

  <?php include ('inc/header.php'); ?>
 


  <main class="main">



  <?php

require_once 'inc/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login");
    exit();
}

$userId = $_SESSION['user_id'];

// Fetch user's name
$userStmt = $conn->prepare("SELECT fullname FROM users WHERE id = ?");
$userStmt->execute([$userId]);
$user = $userStmt->fetch(PDO::FETCH_ASSOC);
$fullname = $user ? $user['fullname'] : 'Guest';
?>

<div class="container p-4">
    <div class="row">
        <!-- Left Panel -->
        <div class="col-md-6">
            <div class="text-center mb-4">
                <div class="icon-circle mx-auto">
                    <i class="fa fa-user text-brown fs-3"></i>
                </div>
                <p class="mt-2 mb-0 fw-semibold text-dark">Hi, <?= htmlspecialchars($fullname) ?></p>
            </div>
            <div class="side-menu">
                <a href="user-dashboard" class="menu-item">Address<i class="fa fa-location-dot me-2"></i></a>
                <a href="order-history" class="menu-item active">Order History<i class="fa fa-receipt me-2"></i></a>
                <a href="#" class="menu-item">Track your orders<i class="fa fa-truck me-2"></i></a>
                <a href="contact-us" class="menu-item">Contact Us<i class="fa fa-headset me-2"></i></a>
                <a href="terms-and-conditions" class="menu-item">Terms & Conditions<i class="fa fa-file-alt me-2"></i></a>
                <a href="privacy-policy" class="menu-item">Privacy Policy<i class="fa fa-lock me-2"></i></a>
                <a href="refund-policy" class="menu-item">Refund Policy<i class="fa fa-undo me-2"></i></a>
            </div>
            <a href="logout" class="btn logout-btn w-100 mt-4">Logout</a>
        </div>

        <!-- Right Panel -->
        <div class="col-md-6 address">
            <h6 class="fw-bold mb-4">Order History</h6>
            <div class="row g-4">
                <?php
                $stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC");
                $stmt->execute(['user_id' => $userId]);
                $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (empty($orders)) {
                    echo '<h4>No orders are available.</h4>';
                } else {
                    foreach ($orders as $order):
                        $cartItems = json_decode($order['cart_data'], true);
                        if (!is_array($cartItems)) {
                            $cartItems = [];
                        }
                ?>
                    <div class="order-card">
                        <h1>Order Code: <?= htmlspecialchars($order['order_code']) ?></h1>
                        <h2>Status: <?= htmlspecialchars($order['order_status']) ?></h2>
                        <p>Ordered On: <?= date('d M, Y', strtotime($order['created_at'])) ?></p>
                        <p>delivery Date On: <?= date('d M, Y', strtotime($order['delivery_date'])) ?></p>
                        <p>Delivery Time: <?= date('h:i A', strtotime($order['delivery_time'])) ?></p>


                        <div class="order-products">
                            <?php
                            $orderTotal = 0;
                            foreach ($cartItems as $item):
                                if (!is_array($item)) continue;

                                $productPrice = floatval($item['product_price'] ?? 0);
                                $boxPrice = floatval($item['box_price'] ?? 0);
                                $quantity = intval($item['quantity'] ?? 1);
                                $lineTotal = ($productPrice + $boxPrice) * $quantity;
                                $orderTotal += $lineTotal;
                            ?>
                                <div class="product-item">
                                    <img src="admin/<?= htmlspecialchars($item['product_image'] ?? '') ?>" alt="<?= htmlspecialchars($item['product_name'] ?? '') ?>" class="product-img">
                                    <h4><?= htmlspecialchars($item['product_name'] ?? '') ?> (<?= htmlspecialchars($item['product_weight'] ?? '') ?>)</h4>
                                    <p>Box: <?= htmlspecialchars($item['box_name'] ?? 'None') ?></p>
                                    <p>Quantity: <?= $quantity ?></p>
                                    <p>Product Price: ₹<?= number_format($productPrice, 2) ?> x <?= $quantity ?></p>
                                    <p>Box Price: ₹<?= number_format($boxPrice, 2) ?> x <?= $quantity ?></p>
                                    <p><strong>Total: ₹<?= number_format($lineTotal, 2) ?></strong></p>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <p><strong>Order Total: ₹<?= number_format($orderTotal, 2) ?></strong></p>

                        <form method="POST" action="inc/reorder" class="order-actions" style="margin-top: 10px;">
                            <input type="hidden" name="cart_data" value="<?= htmlspecialchars($order['cart_data']) ?>">
                            <button type="submit" class="reorder-btn">Reorder Entire Order</button>
                            <button type="button" class="cancel-btn" data-id="<?= $order['id'] ?>"
                                <?= ($order['order_status'] === 'start_preparing') ? 'disabled title="Order is being prepared"' : 'title="Cancel Order"' ?>>
                                🚫
                            </button>
                        </form>
                    </div>
                <?php
                    endforeach;
                }
                ?>
            </div>
        </div>
    </div>
</div>


  
<script>
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.cancel-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const orderId = this.dataset.id;
      if (!orderId) return;

      Swal.fire({
        title: 'Cancel Order?',
        text: 'Are you sure you want to cancel this order?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, cancel it!'
      }).then((result) => {
        if (result.isConfirmed) {
          fetch('inc/cancel_order', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'order_id=' + encodeURIComponent(orderId)
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              this.closest('.order-card').querySelector('p strong').textContent = 'cancelled';
              this.disabled = true;

              Swal.fire(
                'Cancelled!',
                'Your order has been cancelled.',
                'success'
              );
            } else {
              Swal.fire(
                'Error',
                data.message || 'Failed to cancel order.',
                'error'
              );
            }
          })
          .catch(error => {
            console.error('Error:', error);
            Swal.fire(
              'Oops...',
              'Something went wrong!',
              'error'
            );
          });
        }
      });
    });
  });
});
</script>



   <style>
    


.icon-circle {
  background-color: #f4ede4;
  width: 56px;
  height: 56px;
  border-radius: 50%;
  display: flex;
  justify-content: center;
  align-items: center;
}

.text-brown {
  color: #a1782a;
}

.side-menu .menu-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background-color: #fdf6ee;
  padding: 20px 15px;
  margin-bottom: 8px;
  color: #333;
  font-weight: 500;
  border-radius: 6px;
  text-decoration: none;
  transition: background 0.3s;
}

.side-menu .menu-item:hover,
.side-menu .menu-item.active {
  border-left: 3px solid #d4b158;
  background-color: #fdf6ee;
  color: #000;
}

.logout-btn {
  border: 1px solid #d4b158;
  color: #000;
  background: #fff;
  border-radius: 6px;
  padding: 10px 0;
  font-weight: 500;
}

.logout-btn:hover {
  background: #fdf6ee;
}



.address{
    margin-top: 100px;
}

.order-card {
  background-color: #fdf5e6;
  border-radius: 20px;
  padding: 20px;
  width: 600px;
  box-shadow: 0 4px 8px rgba(0,0,0,0.05);
}

.order-products {
  margin-top: 15px;
}

.product-item {
  display: flex;
  align-items: flex-start;
  margin-bottom: 15px;
  border-bottom: 1px solid #eee;
  padding-bottom: 12px;
}

.product-img {
  width: 80px;
  height: 80px;
  border-radius: 12px;
  object-fit: cover;
  flex-shrink: 0;
  margin-right: 20px; /* This is the actual spacing you want */
}





.product-item h4 {
  margin: 0 0 6px 0;
  font-size: 14px;
  font-weight: 600;
  color: #2e2e2e;
}

.product-item p {
  margin: 2px 0;
  font-size: 10px;
  color:rgb(61, 61, 61);
  padding: 10px;
}

.order-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 20px;
  gap: 12px;
}

.reorder-btn {
  flex-grow: 1;
  background-color: #fff;
  border: none;
  padding: 12px;
  font-size: 16px;
  border-radius: 12px;
  cursor: pointer;
  box-shadow: 0 2px 5px rgba(0,0,0,0.1);
  transition: background 0.3s ease;
}

.reorder-btn:hover {
  background-color: #f0f0f0;
}

.cancel-btn {
  width: 45px;
  height: 45px;
  background-color: #fff;
  border: none;
  font-size: 20px;
  border-radius: 12px;
  cursor: pointer;
  box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

@media (max-width: 480px) {
  .product-item {
    flex-direction: column;
    align-items: flex-start;
  }

  .product-img {
    margin-bottom: 10px;
  }

  .order-actions {
    flex-direction: column;
    gap: 10px;
  }

  .reorder-btn,
  .cancel-btn {
    width: 100%;
  }
}

</style>





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