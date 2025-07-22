<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>About - Amit Dairy & Sweets</title>
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

  <?php include ('inc/header.php');
  include_once ('inc/contact_data.php');
  ?>

<?php
$aboutImage = '';

try {
    $stmt = $conn->prepare("SELECT image FROM banners WHERE page_name = ?");
    $stmt->execute(['about']);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && !empty($row['image']) && file_exists("admin/" . $row['image'])) {
        $aboutImage = $row['image'];
    }
} catch (PDOException $e) {
    // echo "Error: " . $e->getMessage(); // optional for debugging
}
?>
  <main class="main">

    <section class="breadcrumbs" style="background-color: #D6B66933; padding: 0;">
  
    <div class="row align-items-center">
      <!-- Image Column -->
      <div class="col-lg-7  text-center">
      
        <img src="admin/<?= htmlspecialchars($aboutImage ?: 'assets/img/bread.png') ?>" alt="Bread Image" class="img-fluid">
      </div>

      <!-- Content Column -->
      <div class="col-lg-5 text-center text-lg-start">
        <h2 class="fw-bold mb-3">Preserving Sweet Traditions: The Story of Amit & Dairy Sweets</h2>
        <p class="mb-4">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
        <!-- <a href="#" class="bread-button mt-5">Shop Now</a> -->
      </div>
    </div>

</section>

<section class="about2 section">
    <div class="container text-center">
        <h3 class="sub-heading">About Amit Dairy & Sweets</h3>
        <h1>Welcome to Amit  Dairy & Sweets</h1>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
        <div class="container d-flex justify-content-center align-items-center">
        <img src="assets/img/about/about1.png" alt="CTA Banner" class="img-fluid">
      </div>
    </div>
</section>

<section class="heritage section">
      <div class="container justify-content-center">
        <div class="row">
          <div class="col-lg-6">
            <div class="heritage-content">
            <h3 class="sub-heading">Our Heritage & Values</h3>
            <h1>Rooted in Tradition, Guided by Values</h1>
            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. </p>

           
          </div>
          </div>
          <div class="col-lg-6">
            <div class="container d-flex justify-content-center align-items-center">
        <img src="assets/img/about/about2.png" alt="CTA Banner" class="img-fluid">
      </div>
          </div>
        </div>
      </div>
    </section>

    <?php

$stmt = $conn->prepare("
    SELECT 
        c.id, 
        c.title, 
        c.category_image, 
        COUNT(p.id) AS total_products
    FROM 
        categories c
    LEFT JOIN 
        products p ON p.category_id = c.id AND p.status = 1
    WHERE 
        c.status = 1
    GROUP BY 
        c.id
");
$stmt->execute();
$categories = $stmt->fetchAll();

?>


    <section class="product section mt-3">
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
                        <?= htmlspecialchars($category['title']) ?>
                         <!-- (<?= $category['total_products'] ?> Products) -->
                    </h5>
                    <a href="subcategory.php?category_id=<?= urlencode($category['id']) ?>" class="btn btn-outline-light mt-1">View Products</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

         
        </div>
      </div>
    </section>

    <?php
$stmt = $conn->query("SELECT name, image, rating, comment FROM testimonial WHERE status = 1 ORDER BY id DESC");
$testimonials = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


    <!-- Testimonials Section -->
    <section id="testimonials" class="testimonials section">

      <!-- Section Title -->
      <div class="container section-title-2" data-aos="fade-up">
        <h2>Testimonials</h2>
        <p>Necessitatibus eius consequatur ex aliquid fuga eum quidem sint consectetur velit</p>
      </div><!-- End Section Title -->

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="swiper init-swiper">
          <script type="application/json" class="swiper-config">
            {
              "loop": true,
              "speed": 600,
              "autoplay": {
                "delay": 5000
              },
              "slidesPerView": "auto",
              "pagination": {
                "el": ".swiper-pagination",
                "type": "bullets",
                "clickable": true
              },
              "breakpoints": {
                "320": {
                  "slidesPerView": 1,
                  "spaceBetween": 40
                },
                "1200": {
                  "slidesPerView": 3,
                  "spaceBetween": 10
                }
              }
            }
          </script>
          <div class="swiper-wrapper">

            <?php foreach ($testimonials as $testimonial): ?>
<div class="swiper-slide">
  <div class="testimonial-item">
    <p>
      <span><?= htmlspecialchars($testimonial['comment']) ?></span>
    </p>
    <div class="d-flex align-items-center">
      <!-- Image -->
      <img src="admin/<?= htmlspecialchars($testimonial['image']) ?>" class="testimonial-img" alt="<?= htmlspecialchars($testimonial['name']) ?>">

      <!-- Name and rating -->
      <div>
        <h3><?= htmlspecialchars($testimonial['name']) ?></h3>
        <div class="stars d-flex align-items-center gap-1">
          <i class="bi bi-star-fill text-warning"></i>
          <h5 class="mb-0"><?= number_format($testimonial['rating'], 1) ?></h5>
        </div>
      </div>
    </div>
  </div>
</div>
<?php endforeach; ?>


            



            

          </div>
        </div>

      </div>

    </section><!-- /Testimonials Section -->
    

    <section class="contact section">
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <h2>Get in Touch with Amit Dairy & Sweets</h2>
                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. </p>

                    <ul>
                       <li> <i class="bi bi-house"></i> Shop Address : <?= htmlspecialchars($contact['address']) ?> </li>
                       <li> <i class="bi bi-telephone-fill"></i> Phone Number : <?= htmlspecialchars($contact['phone']) ?></li>
                       <li> <i class="bi bi-envelope-fill"></i> Email Address : <?= htmlspecialchars($contact['email']) ?></li>
                    </ul>

                </div>
                <div class="col-lg-6">
                    <div class="container d-flex justify-content-center align-items-center">
                      <img src="assets/img/contact.png" alt="CTA Banner" class="img-fluid">
                    </div>
                </div>
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

</body>

</html>