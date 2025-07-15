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
     <!-- Faq Section -->
     <section id="faq" class="faq section">

<!-- Section Title -->
<div class="container section-title" data-aos="fade-up">
  <h2>Frequently Asked Questions</h2>
  <p>Necessitatibus eius consequatur ex aliquid fuga eum quidem sint consectetur velit</p>
</div><!-- End Section Title -->

<div class="container" data-aos="fade-up" data-aos-delay="100">

  <div class="row justify-content-center">
    <div class="col-lg-10">

      <?php
      $stmt = $conn->prepare("SELECT * FROM faq ORDER BY id ASC");
      $stmt->execute();
      $faqs = $stmt->fetchAll(PDO::FETCH_ASSOC);
      ?>
          <div class="faq-list">

          <div class="faq-section">
  <?php $i = 1; foreach ($faqs as $faq): ?>
    <div class="faq-item" data-aos="fade-up" data-aos-delay="<?= 100 * $i ?>">
      <h3>
        <span class="num"><?= $i ?></span>
        <span class="question"><?= htmlspecialchars($faq['question']) ?></span>
        <i class="bi bi-plus-lg faq-toggle"></i>
      </h3>
      <div class="faq-content">
        <p><?= nl2br(htmlspecialchars($faq['answer'])) ?></p>
      </div>
    </div><!-- End FAQ Item -->
  <?php $i++; endforeach; ?>
</div>

      </div>

      <div class="faq-cta text-center mt-5" data-aos="fade-up" data-aos-delay="300">
        <p>Still have questions? We're here to help!</p>
        <a href="contact-us" class="btn btn-primary">Contact Support</a>
      </div>

    </div>
  </div>

</div>

</section><!-- /Faq Section -->




   

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