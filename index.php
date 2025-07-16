<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Home - Amit Dairy & Sweets</title>
  <meta name="description" content="">
  <meta name="keywords" content="">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />

<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- Main CSS File -->
  <link href="assets/css/main.css" rel="stylesheet">

  
</head>

<body class="index-page">

 
<?php include ('inc/header.php');
include_once ('inc/contact_data.php');
?>
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




  <main class="main">


   <!-- Pincode Ticker -->
   <div class="pincode-ticker-container">
        <div class="ticker-label">
          Available Locations:
        </div>
        <div class="ticker-wrap">
        <div class="ticker" id="pincodeTicker">
    <?php
    try {
        // Fetch all active addresses from pincodes table
        $stmt = $conn->query("SELECT address FROM pincodes WHERE status = 'active'");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($rows)) {
            echo '<span class="pincode-item">No addresses available at the moment</span>';
        } else {
            // Loop through addresses twice for seamless ticker
            foreach ($rows as $row) {
                echo '<span class="pincode-item">' . htmlspecialchars($row['address']) . '</span>';
            }
            foreach ($rows as $row) {
                echo '<span class="pincode-item">' . htmlspecialchars($row['address']) . '</span>';
            }
        }
    } catch (PDOException $e) {
        error_log("Address fetch failed: " . $e->getMessage());
        echo '<span class="error-message">Error loading addresses. Please try again later.</span>';
    }
    ?>
</div>


        </div>
    </div>


<style>
/* Pincode Ticker Styles */
.pincode-ticker-container {
    position: fixed;
    bottom: 0;
    left: 0;
    width: 100%;
    background-color:rgba(214, 181, 105, 0.5);
    border-top: 1px solid #dee2e6;
    padding: 20px 0;
    z-index: 1000;
    display: flex;
    align-items: center;
    box-shadow: 0 -2px 5px rgba(0,0,0,0.1);
}

.ticker-label {
    padding: 0 15px;
    font-weight: bold;
    color: #000;
    white-space: nowrap;
    background-color: #e9ecef;
    height: 100%;
    display: flex;
    align-items: center;
    border-right: 1px solid #dee2e6;
}

.ticker-wrap {
    overflow: hidden;
    flex-grow: 1;
    height: 24px;
    position: relative;
}

.ticker {
    display: inline-block;
    white-space: nowrap;
    position: absolute;
    padding-right: 100%;
    will-change: transform;
}

.ticker:hover {
    animation-play-state: paused;
}

/* Individual pincode items */
.pincode-item {
    display: inline-block;
    margin: 0 15px;
    color:rgb(0, 0, 0);
    font-weight: 500;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .pincode-ticker-container {
        flex-direction: column;
        padding: 5px 0;
    }
    
    .ticker-label {
        padding: 5px 10px;
        border-right: none;
        border-bottom: 1px solid #dee2e6;
        width: 100%;
        justify-content: center;
    }
    
    .ticker-wrap {
        width: 100%;
    }
}
</style>


  <?php
$heroImage = '';

try {
    $stmt = $conn->prepare("SELECT image FROM banners WHERE page_name = ?");
    $stmt->execute(['home']);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && !empty($row['image']) && file_exists("admin/" . $row['image'])) {
        $heroImage = $row['image'];
    }
} catch (PDOException $e) {
    // echo "Error: " . $e->getMessage(); // optional for debugging
}
?>
<style>
  #hero {
  position: relative;
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  min-height: 80vh;
  display: flex;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 60px 20px;
}

#hero .overlay {
  position: absolute;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background: rgba(0, 0, 0, 0.4);
  z-index: 1;
}

.hero-content {
  position: relative;
  z-index: 2;
  color: #fff;
  max-width: 800px;
  margin: 0 auto;
}

@media (max-width: 768px) {
  #hero {
    min-height: 50vh;
    padding: 30px 15px;
  }

  .hero-content h1 {
    font-size: 1.8rem;
  }

  .hero-content p {
    font-size: 1rem;
  }
}


</style>

<!-- Hero Section -->
<section id="hero" class="hero section dark-background" 
  style="background-image: url('admin/<?= htmlspecialchars($heroImage ?: 'assets/img/hero.png') ?>');">
  <div class="overlay"></div>
  <div class="container hero-content" data-aos="fade-up">
   
  </div>
</section>





    <section class="product section">
      <div class="section-title">
  <span class="line"></span>
  <h2>Our Featured Categories</h2>
  <span class="line"></span>
</div>
      <div class="container">
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
      <div class="view-more-btn text-center mt-4">
         <a href="category" class="view-more"><span>View All Categories</span><i class="bi bi-arrow-right"></i></a>
        </div>
    </section>

<style>
  .view-more-btn .view-more{
    background-color: #D6B669;
    color: white;
    padding: 15px 30px;
    border-radius: 10px;
    margin-top: 15px;
  }
</style>

    <!-- Team Section -->
    <section id="team" class="team section">
      <!-- Section Title -->
      <div class="section-title">
  <span class="line"></span>
  <h2>Our Best Seller Sweets</h2>
  <span class="line"></span>
</div>

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row">


          <div class="col-lg-12 team-carousel-wrap">
            <div class="team-carousel swiper init-swiper">
              <script type="application/json" class="swiper-config">
                {
                  "loop": true,
                  "speed": 800,
                  "autoplay": {
                    "delay": 5000
                  },
                  "slidesPerView": 1,
                  "spaceBetween": 20,
                  "navigation": {
                    "nextEl": ".team-nav-next",
                    "prevEl": ".team-nav-prev"
                  },
                  "breakpoints": {
                    "576": {
                      "slidesPerView": 2,
                      "spaceBetween": 20
                    },
                    "992": {
                      "slidesPerView": 2.5,
                      "spaceBetween": 10
                    }
                  }
                }
              </script>
              <div class="swiper-wrapper">
              <?php
$stmt = $conn->prepare("SELECT * FROM products WHERE best_seller = 1 AND status = 1 ORDER BY id DESC");
$stmt->execute();
$bestSellers = $stmt->fetchAll();
?>


                <?php foreach ($bestSellers as $product): ?>
        <div class="swiper-slide">
            <div class="sweet-card text-center p-4">
                <!-- Badge -->
                <div class="text-dark mt-3">
                    <span class="title">✨ Best Seller</span>
                    
                </div>

                <!-- Image -->
                <img src="admin/<?= htmlspecialchars($product['product_image']) ?>"  class="sweet-img mb-3" alt="<?= htmlspecialchars($product['name']) ?>">

                <!-- Product Title & Price -->
                <h5 class="mb-2"><?= htmlspecialchars($product['name']) ?></h5>
                <p class="price-text">₹ <?= htmlspecialchars($product['discount_price']) ?> / <?php echo htmlspecialchars($product['weight']); ?></p>

                <!-- Feature Badges  -->
                <div class="d-flex justify-content-center gap-3 mb-3">
                    <div class="feature-box">✨ <?= htmlspecialchars($product['tags']) ?></div>
                </div>

                <!-- Buttons -->
                <div class="d-flex justify-content-center gap-3">
                    <button class="btn btn-outline-dark px-5 rounded-3 add-to-cart-btn" data-product-id="<?php echo $product['id']; ?>" 
                    data-category-id="<?php echo $product['category_id']; ?>">Add to cart</button>
                    <button class="btn btn-light px-5 rounded-3 add-to-cart-btn" data-product-id="<?php echo $product['id']; ?>" 
                    data-category-id="<?php echo $product['category_id']; ?>">Buy Now</button>
                </div>
                
            </div>
        </div>
    <?php endforeach; ?>



              </div>
            </div>
          </div>
        </div>

      </div>
    </section><!-- /Team Section -->


    <section class="cta section py-5">
      <div class="container d-flex justify-content-center align-items-center">
        <img src="assets/img/cta-banner.png" alt="CTA Banner" class="img-fluid">
      </div>
    </section>


    





    <section class="feature section">
      <div class="section-title">
  <span class="line"></span>
  <h2>Our Sweet Gift Boxes Collection</h2>
  <span class="line"></span>
</div>
      <div class="container justify-content-center">
        <div class="row">
          <div class="col-lg-4">
            <img src="assets/img/feature/1.png" alt="">
            <div class="fea-content text-center">
              <h2>Dry Fruit Gift Box</h2>
              <p>Lorem ipsum dolor sit amet consectetur adipisicing elit.</p>
              <button class="btn btn-outline-dark px-5 rounded-3">View Collection</button>
            </div>
          </div>
          <div class="col-lg-4">
            <img src="assets/img/feature/2.png" alt="">
            <div class="fea-content text-center">
              <h2>Wedding Sweets Gift Boxes</h2>
              <p>Lorem ipsum dolor sit amet consectetur adipisicing elit.</p>
              <button class="btn btn-outline-dark px-5 rounded-3">View Collection</button>
            </div>
          </div>
          <div class="col-lg-4">
            <img src="assets/img/feature/3.png" alt="">
            <div class="fea-content text-center">
              <h2>Corporate Sweets Gift Boxes</h2>
              <p>Lorem ipsum dolor sit amet consectetur adipisicing elit.</p>
              <button class="btn btn-outline-dark px-5 rounded-3">View Collection</button>
            </div>
          </div>
        </div>
      </div>

    </section>


    <section class="stats section">
      <div class="container">
        <div class="row text-center">
          <div class="col-lg-3 d-flex flex-column align-items-center justify-content-center">
            <img src="assets/img/icon/1.png" alt="">
            <div class="stat-content">
              <h2>Trusted By Indians</h2>
              <p>Loved by 5 Lac + Customers</p>
            </div>
          </div>
          <div class="col-lg-3 d-flex flex-column align-items-center justify-content-center">
            <img src="assets/img/icon/2.png" alt="">
            <div class="stat-content">
              <h2>Trusted By Indians</h2>
              <p>Every Piece is made with love</p>
            </div>
          </div>
          <div class="col-lg-3 d-flex flex-column align-items-center justify-content-center">
            <img src="assets/img/icon/3.png" alt="">
            <div class="stat-content">
              <h2>Ships In 1-2 Days</h2>
              <p>Every Piece is made with love</p>
            </div>
          </div>
          <div class="col-lg-3 d-flex flex-column align-items-center justify-content-center">
            <img src="assets/img/icon/4.png" alt="">
            <div class="stat-content">
              <h2>No Preservations</h2>
              <p>Pure test, Natural fresh</p>
            </div>
          </div>
        </div>
      </div>
    </section>


    <!-- About Section -->
    <section id="about" class="about section">

      <div class="container">

        <div class="row gy-4">



          <div class="col-lg-6 about-images" data-aos="fade-up" data-aos-delay="200">
            <div class="row gy-4">
              <div class="col-lg-6">
                <div class="row gy-4">
                  <div class="col-lg-12">
                    <img src="assets/img/about/2.png" class="img-fluid" alt="">
                  </div>
                  <div class="col-lg-12">
                    <img src="assets/img/about/3.png" class="img-fluid" alt="">
                  </div>
                </div>
              </div>
              <div class="col-lg-6">
                <div class="row gy-4">
                  <div class="col-lg-12">
                    <img src="assets/img/about/3.png" class="img-fluid" alt="">
                  </div>
                  <div class="col-lg-12">
                    <img src="assets/img/about/2.png" class="img-fluid" alt="">
                  </div>
                </div>
              </div>
            </div>

          </div>

          <div class="col-lg-6 content" data-aos="fade-up" data-aos-delay="100">
            <p class="who-we-are">About Our Traditional Sweets</p>
            <h3>Our Story</h3>
            <p class="para">
              Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et
              dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex
              ea commodo consequat. Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor
              incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco
              laboris nisi ut aliquip ex ea commodo consequat.
            </p>

            <a href="about" class="read-more"><span>Read More</span><i class="bi bi-arrow-right"></i></a>
          </div>

        </div>

      </div>
    </section><!-- /About Section -->

    <section class="specialties-section">
      <div class="container">

        <!-- Background Wrapper with Text -->
        <div class="specialties-wrapper">
          <div class="specialties-text">
            <div class="vertical-line"></div>
            <div class="text-content">
              <h2><span class="leaf-icon">🍁</span> Our Specialties</h2>
              <p>
                Lorem ipsum dolor sit amet, consectetur adipiscing elit, Lorem ipsum dolor sit amet, consectetur
                adipiscing elit, Lorem ipsum dolor sit amet, consectetur adipiscing elit,
              </p>
            </div>
          </div>
        </div>

        <!-- Cards Overflowing the Wrapper -->
        <?php
// Assuming $conn is your PDO connection

$sql = "SELECT * FROM products WHERE specialities = 1 AND status = 1 LIMIT 2";
$stmt = $conn->prepare($sql);
$stmt->execute();
$specialProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="specialties-cards">
  <?php foreach ($specialProducts as $product): ?>
    <div class="card">
      <img src="admin/<?php echo htmlspecialchars($product['product_image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
      <div class="card-overlay">
        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
        <p>₹ <?php echo number_format($product['discount_price'], 2); ?> / <?php echo htmlspecialchars($product['weight']); ?></p>
        <a href="javascript:void(0);" 
   class="btn btn-outline-light mt-1 add-to-cart-btn" 
   data-product-id="<?php echo $product['id']; ?>" 
   data-category-id="<?php echo $product['category_id']; ?>">
   Add to Cart
</a>

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
      <!-- Modal -->
<div class="modal fade" id="boxSelectModal" tabindex="-1" aria-labelledby="boxSelectModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content p-4 rounded-4">
      <div class="modal-header">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="box-options">
        <!-- Box options will be injected here -->
      </div>
    </div>
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
  <script>
document.querySelectorAll('.add-to-cart-btn').forEach(function(addToCartBtn) {
  addToCartBtn.addEventListener('click', () => {
    const productId = addToCartBtn.dataset.productId;
    const categoryId = addToCartBtn.dataset.categoryId;
    const qty = 1;

    fetch('inc/fetch_boxes', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `category_id=${categoryId}`
    })
    .then(res => res.json())
    .then(boxes => {
      const boxOptions = document.getElementById('box-options');
      boxOptions.innerHTML = `
        <h5 class="text-center fw-semibold mb-4">Select a Sweet Box</h5>
        <div class="d-flex flex-wrap justify-content-center gap-3">
          ${boxes.map(box => `
            <div class="box-card text-center rounded" style="width: 140px; cursor: pointer;" data-box-id="${box.id}">
              <img src="admin/${box.box_image}" alt="${box.box_name}" class="img-fluid rounded" style="height: 100px; width: 100px; object-fit: cover;">
              <div class="mt-2 box_title" title="${box.box_name}">${box.box_name}</div>
              <div class="mt-1 box_price" style="background-color: #fef3c7; border-radius: 6px; padding: 2px 8px; display: inline-block;">₹ ${box.box_price}</div>
            </div>
          `).join('')}
        </div>
        <div class="text-center mt-4">
          <button id="confirmBoxBtn" class="btn btn-warning px-5 py-2 fw-semibold me-3" disabled>Continue Shipping</button>
          <button id="skipBoxBtn" class="btn btn-outline-secondary px-4 py-2">Skip</button>
        </div>
      `;

      const modal = new bootstrap.Modal(document.getElementById('boxSelectModal'));
      modal.show();

      let selectedBoxId = null;
      setTimeout(() => {
        document.querySelectorAll('.box-card').forEach(option => {
          option.addEventListener('click', () => {
            document.querySelectorAll('.box-card').forEach(o => o.classList.remove('border-primary', 'border', 'shadow'));
            option.classList.add('border', 'border-primary', 'shadow');
            selectedBoxId = option.dataset.boxId;
            document.getElementById('confirmBoxBtn').disabled = false;
          });
        });

        document.getElementById('confirmBoxBtn').onclick = () => {
          submitToCart(productId, qty, selectedBoxId, modal);
        };

        document.getElementById('skipBoxBtn').onclick = () => {
          submitToCart(productId, qty, 'none', modal);
        };
      }, 100);
    });
  });
});

function submitToCart(productId, qty, boxId, modal) {
  fetch('inc/add_to_cart', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `product_id=${productId}&quantity=${qty}&box_id=${boxId}`
  })
  .then(res => res.text())
  .then(data => {
    const trimmed = data.trim();

    if (trimmed === 'success') {
      Swal.fire({
        icon: 'success',
        title: 'Added to cart!',
        showConfirmButton: false,
        timer: 2000,
        timerProgressBar: true,
        toast: true,
        position: 'top-end',
        didClose: () => {
          location.reload();
        }
      });
      modal.hide();

    } else if (trimmed === 'not_logged_in') {
      Swal.fire({
        icon: 'warning',
        title: 'Login Required',
        text: 'You need to be logged in to add items to your cart.',
        showCancelButton: true,
        confirmButtonText: 'Login Now',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#aaa'
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = 'login';
        }
      });

    } else {
      Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: data,
        confirmButtonColor: '#d33',
      });
    }
  });
}
</script>
<!-- ✅ Toast Notification -->
<div class="toast-container position-fixed top-0 end-0 p-3">
  <div id="pincodeToast" class="toast align-items-center text-bg-danger border-0" role="alert">
    <div class="d-flex">
      <div class="toast-body">
        ❌ We do not deliver to this pincode.
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
</div>

<!-- ✅ Pincode Modal -->
<div class="modal fade" id="pincodeModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="pincodeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content shadow-lg rounded-4 border-0">
      <form id="pincodeForm" class="needs-validation" novalidate>
        <div class="modal-header text-white rounded-top-4" style="background-color: #D6B669;">
          <h5 class="modal-title mx-auto" id="pincodeModalLabel">🚚 Delivery Check</h5>
        </div>
        <div class="modal-body p-4">
          <p class="text-center mb-3 text-muted">Please enter your 6-digit pincode to check delivery availability in your area.</p>
          <div class="mb-3">
            <input type="text" class="form-control form-control-lg text-center fw-semibold border-primary shadow-sm" 
                   id="pincodeInput" placeholder="e.g. 110001" required maxlength="6" pattern="\d{6}">
            <div class="invalid-feedback text-center">
              Please enter a valid 6-digit pincode.
            </div>
          </div>
        </div>
        <div class="modal-footer px-4 pb-4 pt-0 border-0">
          <button type="submit" class="btn  w-100 btn-lg rounded-pill shadow-sm" style="background-color: #D6B669;">Check Availability</button>
        </div>
      </form>
    </div>
  </div>
</div>





<script>
document.addEventListener('DOMContentLoaded', () => {
  const modalEl = document.getElementById('pincodeModal');
  const pincodeInput = document.getElementById('pincodeInput');
  const pincodeForm = document.getElementById('pincodeForm');
  const modal = new bootstrap.Modal(modalEl);

  // ✅ Check if pincode was already entered in this session
  if (!sessionStorage.getItem('pincode_verified')) {
    modal.show(); // Only show modal if not submitted before in this session
  }

  // ✅ Form submission
  pincodeForm.addEventListener('submit', function (e) {
    e.preventDefault();
    const pin = pincodeInput.value.trim();

    if (!/^\d{6}$/.test(pin)) {
      pincodeInput.classList.add('is-invalid');
      return;
    }

    pincodeInput.classList.remove('is-invalid');

    // ✅ Send to server for verification
    fetch('inc/verify_pincode', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'pincode=' + encodeURIComponent(pin)
    })
    .then(res => res.json())
    .then(data => {
      if (data.status) {
        // ✅ Save flag so modal won't appear again during this session
        sessionStorage.setItem('pincode_verified', pin); // or just '1'
        modal.hide();
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Oops!',
          text: '🚫 We do not deliver to this pincode.',
          confirmButtonColor: '#d33'
        });

        // Optional: auto-close modal
        setTimeout(() => modal.hide(), 2000);
      }
    })
    .catch(err => {
      console.error('Error:', err);
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Something went wrong while checking the pincode.',
        confirmButtonColor: '#d33'
      });
    });
  });

  // ✅ Remove error on typing
  pincodeInput.addEventListener('input', () => {
    pincodeInput.classList.remove('is-invalid');
  });
});
</script>



<script>
document.addEventListener('DOMContentLoaded', function() {
    const ticker = document.getElementById('pincodeTicker');
    if (!ticker) return;

    // Duplicate the ticker content for seamless looping
    ticker.innerHTML += ticker.innerHTML;

    let tickerWrap = ticker.parentElement;
    let speed = 1; // pixels per frame
    let pos = 0;

    function animateTicker() {
        pos -= speed;
        // Reset position for seamless loop
        if (Math.abs(pos) >= ticker.scrollWidth / 2) {
            pos = 0;
        }
        ticker.style.transform = `translateX(${pos}px)`;
        requestAnimationFrame(animateTicker);
    }

    // Set ticker to inline-block and relative for proper measurement
    ticker.style.display = 'inline-block';
    ticker.style.position = 'relative';
    ticker.style.whiteSpace = 'nowrap';
    ticker.style.willChange = 'transform';

    // Pause on hover
    let paused = false;
    tickerWrap.addEventListener('mouseenter', () => paused = true);
    tickerWrap.addEventListener('mouseleave', () => paused = false);

    function loop() {
        if (!paused) animateTicker();
        else requestAnimationFrame(loop);
    }
    loop();
});
</script>

</body>

</html>