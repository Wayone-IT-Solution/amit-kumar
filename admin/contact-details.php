<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: index");
    exit;
}

?>


<!doctype html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg"
    data-sidebar-image="none" data-preloader="disable">

<head>

    <meta charset="utf-8" />
    <title>Admin - Amit Dairy & Sweets</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
    <meta content="Themesbrand" name="author" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/images/logo.webp">

    <!-- jsvectormap css -->
    <link href="assets/libs/jsvectormap/jsvectormap.min.css" rel="stylesheet" type="text/css" />

    <!--Swiper slider css-->
    <link href="assets/libs/swiper/swiper-bundle.min.css" rel="stylesheet" type="text/css" />

    <!-- Layout config Js -->
    <script src="assets/js/layout.js"></script>
    <!-- Bootstrap Css -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <!-- Icons Css -->
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <!-- App Css-->
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" />
    <!-- custom Css-->
    <link href="assets/css/custom.min.css" rel="stylesheet" type="text/css" />

</head>

<body>

    <!-- Begin page -->
    <div id="layout-wrapper">

        <?php include ('inc/header.php'); ?>
        <!-- Left Sidebar End -->
        <!-- Vertical Overlay-->
        <div class="vertical-overlay"></div>

        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">

            <div class="page-content">
                <div class="container-fluid">

                   <?php
require '../inc/db.php';

$success = $error = '';
$contact = [];

try {
    // Ensure contact table exists (optional but safe in production)
    $conn->query("SELECT 1 FROM contact_details LIMIT 1");
} catch (PDOException $e) {
    error_log("Table check failed: " . $e->getMessage());
    exit('Critical error: Contact table not found. Please check database setup.');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address = trim($_POST['address'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $facebook = trim($_POST['facebook'] ?? '');
    $instagram = trim($_POST['instagram'] ?? '');
    $twitter = trim($_POST['twitter'] ?? '');

    // Basic validation
    if (empty($address) || empty($email) || empty($phone)) {
        $error = "Address, Email, and Phone are required.";
    } else {
        try {
            // Check if a contact row exists
            $stmt = $conn->query("SELECT id FROM contact_details LIMIT 1");
            $existingContact = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existingContact) {
                // UPDATE
                $sql = "UPDATE contact_details SET 
                    address = :address,
                    email = :email,
                    phone = :phone,
                    facebook = :facebook,
                    instagram = :instagram,
                    twitter = :twitter
                    WHERE id = :id";
                $params = [
                    ':address' => $address,
                    ':email' => $email,
                    ':phone' => $phone,
                    ':facebook' => $facebook,
                    ':instagram' => $instagram,
                    ':twitter' => $twitter,
                    ':id' => $existingContact['id']
                ];
            } else {
                // INSERT
                $sql = "INSERT INTO contact_details 
                    (address, email, phone, facebook, instagram, twitter)
                    VALUES (:address, :email, :phone, :facebook, :instagram, :twitter)";
                $params = [
                    ':address' => $address,
                    ':email' => $email,
                    ':phone' => $phone,
                    ':facebook' => $facebook,
                    ':instagram' => $instagram,
                    ':twitter' => $twitter
                ];
            }

            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $success = $existingContact ? "Contact details updated successfully." : "Contact details added successfully.";

        } catch (PDOException $e) {
            error_log("DB Operation Error: " . $e->getMessage());
            $error = "A database error occurred while saving the contact.";
        }
    }
}

// Load latest contact details
try {
    $stmt = $conn->query("SELECT * FROM contact_details ORDER BY id DESC LIMIT 1");
    $contact = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
} catch (PDOException $e) {
    error_log("DB Read Error: " . $e->getMessage());
    $error = "Failed to load contact details.";
}
?>

<div class="col-xxl-12">
  <div class="card">
    <div class="card-header align-items-center d-flex">
      <h4 class="card-title mb-0 flex-grow-1">Add / Update Contact Details</h4>
    </div>

    <div class="card-body">
      <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
      <?php elseif (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <div class="live-preview">
        <form method="POST">
          <div class="row">
            <!-- Address -->
            <div class="col-md-6">
              <div class="mb-3">
                <label for="address" class="form-label">Address</label>
                <textarea class="form-control" name="address" id="address" rows="3" required><?= htmlspecialchars($contact['address'] ?? '') ?></textarea>
              </div>
            </div>

            <!-- Email -->
            <div class="col-md-6">
              <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" name="email" id="email" value="<?= htmlspecialchars($contact['email'] ?? '') ?>" required>
              </div>
            </div>

            <!-- Phone -->
            <div class="col-md-6">
              <div class="mb-3">
                <label for="phone" class="form-label">Phone</label>
                <input type="text" class="form-control" name="phone" id="phone" value="<?= htmlspecialchars($contact['phone'] ?? '') ?>" required>
              </div>
            </div>

            

            <!-- Facebook -->
            <div class="col-md-6">
              <div class="mb-3">
                <label for="facebook" class="form-label">Facebook</label>
                <input type="url" class="form-control" name="facebook" id="facebook" placeholder="https://facebook.com/..." value="<?= htmlspecialchars($contact['facebook'] ?? '') ?>">
              </div>
            </div>

            <!-- Instagram -->
            <div class="col-md-6">
              <div class="mb-3">
                <label for="instagram" class="form-label">Instagram</label>
                <input type="url" class="form-control" name="instagram" id="instagram" placeholder="https://instagram.com/..." value="<?= htmlspecialchars($contact['instagram'] ?? '') ?>">
              </div>
            </div>

            <!-- Twitter -->
            <div class="col-md-6">
              <div class="mb-3">
                <label for="twitter" class="form-label">X</label>
                <input type="url" class="form-control" name="twitter" id="twitter" placeholder="https://twitter.com/..." value="<?= htmlspecialchars($contact['twitter'] ?? '') ?>">
              </div>
            </div>

            <!-- Submit -->
            <div class="col-12 text-end">
              <button type="submit" class="btn btn-primary">Save Details</button>
            </div>
          </div>
        </form>
      </div> <!-- end live-preview -->
    </div>
  </div>
</div>






    <footer class="footer">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <script>document.write(new Date().getFullYear())</script> © Amit Dairy & Sweets.
                </div>
                <div class="col-sm-6">
                    <div class="text-sm-end d-none d-sm-block">
                        Design & Develop by Way One
                    </div>
                </div>
            </div>
        </div>
    </footer>
    </div>
    <!-- end main content-->

    </div>
    <!-- END layout-wrapper -->



    <!--start back-to-top-->
    <button onclick="topFunction()" class="btn btn-danger btn-icon" id="back-to-top">
        <i class="ri-arrow-up-line"></i>
    </button>
    <!--end back-to-top-->

    <!--preloader-->
    <div id="preloader">
        <div id="status">
            <div class="spinner-border text-primary avatar-sm" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>





    <!-- JAVASCRIPT -->
    <script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/libs/simplebar/simplebar.min.js"></script>
    <script src="assets/libs/node-waves/waves.min.js"></script>
    <script src="assets/libs/feather-icons/feather.min.js"></script>
    <script src="assets/js/pages/plugins/lord-icon-2.1.0.js"></script>
    <script src="assets/js/plugins.js"></script>

    <!-- apexcharts -->
    <script src="assets/libs/apexcharts/apexcharts.min.js"></script>

    <!-- Vector map-->
    <script src="assets/libs/jsvectormap/jsvectormap.min.js"></script>
    <script src="assets/libs/jsvectormap/maps/world-merc.js"></script>

    <!--Swiper slider js-->
    <script src="assets/libs/swiper/swiper-bundle.min.js"></script>

    <!-- Dashboard init -->
    <script src="assets/js/pages/dashboard-ecommerce.init.js"></script>

    <!-- App js -->
    <script src="assets/js/app.js"></script>
</body>


<!-- Mirrored from themesbrand.com/velzon/html/default/dashboard.html by HTTrack Website Copier/3.x [XR&CO'2014], Wed, 21 May 2025 11:27:56 GMT -->

</html>