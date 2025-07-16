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
  <link
    href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Montserrat:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
    rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

  <!-- Main CSS File -->
  <link href="assets/css/main.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>



</head>

<body class="index-page">

  <?php include ('inc/header.php'); ?>
  
<main>

<?php include_once ('inc/contact_data.php');
?>
<?php

$stmt = $conn->prepare("
    SELECT 
        c.id, c.title, c.category_image, COUNT(p.id) AS total_products
    FROM 
        categories c
    LEFT JOIN 
        products p ON p.category_id = c.id
    WHERE 
        c.status = 1
    GROUP BY 
        c.id
");
$stmt->execute();
$categories = $stmt->fetchAll();
?>



  <main class="main">

   <section class="contact-banner" style="background-image: url('assets/img/contact-banner.png'); ">
      <div class="container text-center">
        <h3>Our Categories</h3>
        <h1>We, d Love to show you our category</h1>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea </p>
      </div>
      
    </section>

    <section class="product section">
      <div class="section-title-3 text-center">
  <h3>Our Products</h3>
  <h1>What We Offers</h1>
  <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea </p>

</div>
      <div class="container mt-5">
        <div class="row gx-0 gy-0">
          <?php foreach ($categories as $category): ?>
<div class="col-lg-4">
    <div class="container">
        <div class="card product-card text-center text-white overflow-hidden">
            <div class="position-relative h-100">
                <!-- Category Image -->
                <img src="admin/<?= htmlspecialchars($category['category_image']) ?>" class="card-img" alt="<?= htmlspecialchars($category['title']) ?>">

                <!-- Gradient Overlay -->
                <div class="gradient-overlay position-absolute top-0 start-0 w-100 h-100"></div>

                <!-- Content -->
                <div class="card-content position-absolute bottom-0 start-0 w-100 p-4">
                    <h5 class="card-title">
                        <?= htmlspecialchars($category['title']) ?> (<?= $category['total_products'] ?> Products)
                    </h5>
                    <a href="product?category_id=<?= urlencode($category['id']) ?>" class="btn btn-outline-light mt-1">View Products</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

         
        </div>
        
      </div>
     
    </section>



   

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
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script>
document.addEventListener('DOMContentLoaded', function () {
    const params = new URLSearchParams(window.location.search);
    if (params.get('otp') === 'retry') {
        const otpModal = new bootstrap.Modal(document.getElementById('otpModal'));
        otpModal.show();
    }
});
</script>

</body>

</html>