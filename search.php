<?php
session_start();

// Count cart items
$cartCount = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;

// Check login status
$isLoggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
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
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />

<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <title>Amit Dairy & Sweets</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
  * {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    font-family: 'Inter', sans-serif;
  }

  body {
    background-color: #fff;
    color: #000;
  }

  .top-banner {
    background-color: #d8b66c;
    text-align: center;
    padding: 10px;
    font-size: 16px;
    font-weight: 600;
  }

  .header {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    padding: 20px 60px;
    border-bottom: 1px solid #eee;
  }

  .logo-container img {
    width: 140px;
    height: auto;
  }

  .search-container {
    flex: 1 1 300px;
    margin: 10px 20px;
  }

  .search-box {
    width: 100%;
    display: flex;
    align-items: center;
    background: #f0f0f0;
    padding: 12px 16px;
    border-radius: 12px;
  }

  .search-box input {
    border: none;
    outline: none;
    background: transparent;
    font-size: 16px;
    width: 100%;
    margin-left: 10px;
  }

  .icons {
    display: flex;
    gap: 20px;
    font-size: 22px;
    color: #111;
    margin-top: 10px;
  }

  .products {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 24px;
    padding: 40px 20px;
  }

  .category-card {
    flex: 1 1 200px;
    max-width: 240px;
    text-align: center;
  }

  .category-card img {
    width: 100%;
    border-radius: 20px;
    object-fit: cover;
    height: 140px;
  }

  .product-name {
    margin-top: 16px;
    font-size: 18px;
    font-weight: 600;
  }

  /* Responsive */
  @media screen and (max-width: 768px) {
    .header {
      flex-direction: column;
      align-items: flex-start;
      padding: 20px;
    }

    .search-container {
      margin: 10px 0;
      width: 100%;
    }

    .icons {
      width: 100%;
      justify-content: flex-end;
      margin-top: -370px;
    }

    .category-card {
      flex: 1 1 45%;
      max-width: 45%;
      margin-top: 40px;
    }
  }

  @media screen and (max-width: 480px) {
    .search-box input {
      font-size: 14px;
    }

    .category-card {
      flex: 1 1 100%;
      max-width: 100%;
    }

    .product-name {
      font-size: 16px;
    }

    .logo-container img {
      width: 120px;
    }
  }
</style>

</head>
<body>

<?php
require 'inc/db.php';

$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';

if ($searchTerm !== '') {
    $stmt = $conn->prepare("SELECT id, name, product_image FROM products WHERE name LIKE ? ORDER BY id ASC");
    $stmt->execute(["%$searchTerm%"]);
} else {
    $stmt = $conn->prepare("SELECT id, name, product_image FROM products ORDER BY id ASC");
    $stmt->execute();
}

$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>



  <header class="header">
    <div class="logo-container">
    <a href="index" class="logo d-flex align-items-center me-auto">
        <!-- Uncomment the line below if you also wish to use an image logo -->
        <img src="assets/img/logo.png" alt="">
      </a>
    </div>

    <div class="search-container">
    <form method="GET" action="" class="search-container">
  <div class="search-box">
    <span style="font-size: 22px;">🔍</span>
    <input type="text" name="search" placeholder="Search Sweets by name"
      value="<?= htmlspecialchars($searchTerm) ?>">
  </div>
</form>


    </div>

    <div class="icons d-flex header-icons align-items-end order-3 order-xl-2 ms-3">


  <div class="position-relative me-3">
    <a href="cart" class="text-dark">
      <i class="bi bi-cart-fill fs-5"></i>
      <?php if ($cartCount > 0): ?>
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
          <?= $cartCount ?>
        </span>
      <?php endif; ?>
    </a>
  </div>

  <?php if ($isLoggedIn): ?>
    <a href="user-dashboard"><i class="bi bi-person-fill fs-5" style="color: #000"></i></a>
  <?php else: ?>
    <a href="register"><i class="bi bi-box-arrow-in-right fs-5" style="color: #000"></i></a>
  <?php endif; ?>
</div>
  </header>



  <section class="products">
  <?php if (!empty($products)): ?>
    <?php foreach ($products as $product): ?>
      <div class="category-card">
        <a style="text-decoration: none; color: #000;" href="product-details.php?product_id=<?= $product['id'] ?>">
          <img src="admin/<?= htmlspecialchars($product['product_image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
          <div class="product-name"><?= htmlspecialchars($product['name']) ?></div>
        </a>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <div class="text-center" style="width: 100%;">
      <h5 class="text-danger mt-5">No product found for "<?= htmlspecialchars($searchTerm) ?>"</h5>
    </div>
  <?php endif; ?>
</section>



   

</body>
</html>
