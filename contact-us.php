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


</head>

<body class="index-page">

  <?php include ('inc/header.php');
  include_once ('inc/contact_data.php');
  ?>
<?php
$contactImage = '';

try {
    $stmt = $conn->prepare("SELECT image FROM banners WHERE page_name = ?");
    $stmt->execute(['contact']);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && !empty($row['image']) && file_exists("admin/" . $row['image'])) {
        $contactImage = $row['image'];
    }
} catch (PDOException $e) {
    // echo "Error: " . $e->getMessage(); // optional for debugging
}
?>

  <main class="main">
    <section class="contact-banner" style="background-image: url('admin/<?= htmlspecialchars($contactImage ?: 'assets/img/contact-banner.png') ?>'); ">
      <div class="container text-center">
        <h3>Contact Us</h3>
        <h1>We, d Love to hear from you</h1>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea </p>
      </div>
      
    </section>




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
                    <div class="container mt-5 justify-content-center align-items-center">
                       <?php


$success = $error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $description = trim($_POST['descritption'] ?? '');

    if (empty($name) || empty($description)) {
        $error = "Name and Message are required fields.";
    } else {
        try {
            $stmt = $conn->prepare("INSERT INTO contact (name, email, phone, description) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $phone, $description]);
            $success = "Thank you! Your message has been submitted successfully.";
        } catch (PDOException $e) {
            $error = "Error submitting form: " . $e->getMessage();
        }
    }
}
?>

<!-- Show alerts -->
<?php if (!empty($success)): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
  <?= htmlspecialchars($success) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php elseif (!empty($error)): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
  <?= htmlspecialchars($error) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Contact Form -->
<form action="" method="POST">
  <div class="mb-3">
    <input type="text" name="name" class="form-control" placeholder="Name" required>
  </div>
  <div class="mb-3">
    <input type="email" name="email" class="form-control" placeholder="Email">
  </div>
  <div class="mb-3">
    <input type="tel" name="phone" class="form-control" placeholder="Phone Number">
  </div>
  <div class="mb-3">
    <textarea name="descritption" class="form-control" placeholder="Message" rows="5" required></textarea>
  </div>
  <button type="submit" class="btn btn-submit">Submit</button>
</form>

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