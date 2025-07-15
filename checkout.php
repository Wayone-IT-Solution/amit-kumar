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
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


  <!-- Main CSS File -->
  <link href="assets/css/main.css" rel="stylesheet">

<style>
    .payment-section, .summary-section {
      background-color: #fdf9f0;
      border-radius: 10px;
      padding: 20px;
    }
    .payment-option, .summary-item {
      border-bottom: 1px dashed #AC7900;
      padding: 40px 0;
    }
    .payment-option:last-child {
      border-bottom: none;
    }
    .payment-option input[type="radio"] {
      float: right;
      margin-top: 8px;
    }
    .payment-option img {
      width: 25px;
      height: 25px;
      margin-right: 10px;
    }
    .pay-button {
      background-color: #d5b263;
      color: #000;
      border: none;
      border-radius: 8px;
      padding: 10px 20px;
      font-weight: 500;
    }
    .pay-via-btn {
      background-color: #07a44f;
      color: #fff;
      border-radius: 6px;
      padding: 6px 12px;
      font-size: 14px;
      float: right;
      border: none;
    }
    .upi-box, .other-option {
      background-color: #fcf9f2;
      border-radius: 10px;
      padding: 15px;
      margin-bottom: 15px;
    }

    .card-box{
        padding: 35px;
        background-color: white;
    }
    .summary-item {
      display: flex;
      justify-content: space-between;
      font-weight: 500;
    }
    .summary-item small {
      display: block;
      font-weight: 400;
      color: #777;
    }
    .add-card, .add-upi {
      border: 1px dashed #ddd;
      padding: 10px;
      border-radius: 8px;
      background-color: #fff;
      cursor: pointer;
    }
    .add-card i, .add-upi i {
      margin-right: 8px;
    }
    .more-options {
      font-size: 15px;
      font-weight: 500;
    }
</style>
</head>

<body class="index-page">

  <?php include ('inc/header.php'); ?>
  
  <main class="main">
   
  <div class="container">
  <div class="row">
    <!-- Payment Section -->
    <div class="col-md-6">
      <div class="payment-section">
        <!-- UPI -->
        <div class="upi-box mb-3">
          <div class="payment-option d-flex align-items-center justify-content-between">
            <div>
              <img src="assets/img/phone.png" width="20">
              PhonePe UPI
            </div>
            <button class="pay-via-btn">Pay Via PhonePe</button>
          </div>
          <div class="payment-option d-flex align-items-center justify-content-between">
            <div>
              <img src="assets/img/g-pay.png" width="20">
              Google Pay
            </div>
            <button class="pay-via-btn">Pay Via GooglePay</button>
          </div>
          <div class="add-upi mt-2">
            <i class="bi bi-plus-circle"></i>
            Add New UPI ID
          </div>
        </div>

        <!-- Cards -->
        <h6 class="mb-2">Credit & Debit Cards</h6>
        <div class="card-box add-card">
          <i class="bi bi-plus-circle"></i>
          Add New Card
        </div>

        <!-- More Options -->
        <h6 class="mt-4 mb-2">More Payment Options</h6>
        <div class="other-option d-flex justify-content-between align-items-center">
          <div>
            <i class="bi bi-bank"></i>
            Net banking<br>
            <small>Select from a list of banks</small>
          </div>
          <i class="bi bi-chevron-right"></i>
        </div>
        <div class="other-option d-flex justify-content-between align-items-center">
          <div>
            <i class="bi bi-box"></i>
            Pay on Delivery<br>
            <small>Pay in cash or pay online</small>
          </div>
          <i class="bi bi-chevron-right"></i>
        </div>
      </div>
    </div>

    <!-- Order Summary -->
    <div class="col-md-6">
      <div class="summary-section">
        <h5>Order Summary</h5>
        <div class="summary-item">
          <div>
            Besan Laddu
            <small>200g</small>
          </div>
          <div>₹ 233/</div>
        </div>
        <hr>
        <div class="summary-item">
          <div>
            Milk Barfi
            <small>200g</small>
          </div>
          <div>₹ 233/</div>
        </div>
        <hr>
        <div class="summary-item">
          <div>Subtotal</div>
          <div>₹ 433/</div>
        </div>
        <div class="text-center mt-4">
          <button class="pay-button w-100">Pay Now</button>
        </div>
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

</body>

</html>