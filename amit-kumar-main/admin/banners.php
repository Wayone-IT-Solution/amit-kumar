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
require_once('../inc/db.php'); // Adjust as needed

$success = '';
$error = '';
$selectedPage = $_POST['page_name'] ?? '';
$currentImage = '';

// Check if banner already exists
if (!empty($selectedPage)) {
    $stmt = $conn->prepare("SELECT image FROM banners WHERE page_name = ?");
    $stmt->execute([$selectedPage]);
    $existingBanner = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($existingBanner) {
        $currentImage = $existingBanner['image'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_banner'])) {
    try {
        $page_name = trim($_POST['page_name'] ?? '');
        if (empty($page_name)) throw new RuntimeException('Page name is required.');

        $imageUploaded = isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK;
        $finalImagePath = $currentImage;

        if ($imageUploaded) {
            $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
            $imageTmp = $_FILES['image']['tmp_name'];
            $imageName = basename($_FILES['image']['name']);
            $imageExt = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));

            if (!in_array($imageExt, $allowedExts)) {
                throw new RuntimeException('Invalid image format.');
            }

            $safeImageName = uniqid('banner_', true) . '.' . $imageExt;
            $uploadDir = 'uploads/banners/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fullImagePath = $uploadDir . $safeImageName;
            if (!move_uploaded_file($imageTmp, $fullImagePath)) {
                throw new RuntimeException('Failed to upload image.');
            }

            $finalImagePath = $fullImagePath;
        }

        if (!empty($currentImage)) {
            // Update existing banner
            $stmt = $conn->prepare("UPDATE banners SET image = ? WHERE page_name = ?");
            $stmt->execute([$finalImagePath, $page_name]);
            $success = "Banner updated successfully.";
        } else {
            // Insert new banner
            if (!$imageUploaded) throw new RuntimeException("Upload Banner Image Size (1440 X 650).");
            $stmt = $conn->prepare("INSERT INTO banners (page_name, image) VALUES (?, ?)");
            $stmt->execute([$page_name, $finalImagePath]);
            $success = "Banner added successfully.";
        }

        $currentImage = $finalImagePath;
    } catch (Throwable $e) {
        $error = 'Error: ' . htmlspecialchars($e->getMessage());
    }
}
?>

                <div class="row">
    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <div class="col-xxl-12">
        <div class="card">
            <div class="card-header"><h4 class="card-title mb-0">Manage Banner</h4></div>

            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="submit_banner" value="1">

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="pageSelect" class="form-label">Select Page</label>
                            <select name="page_name" id="pageSelect" class="form-select" onchange="this.form.submit()" required>
                                <option value="">-- Select Page --</option>
                                <option value="home" <?= ($selectedPage === 'home') ? 'selected' : '' ?>>Home</option>
                                <option value="about" <?= ($selectedPage === 'about') ? 'selected' : '' ?>>About</option>
                                <option value="product" <?= ($selectedPage === 'product') ? 'selected' : '' ?>>Products</option>
                                <option value="contact" <?= ($selectedPage === 'contact') ? 'selected' : '' ?>>Contact</option>
                                <!-- Add more options if needed -->
                            </select>
                        </div>

                        <?php if (!empty($selectedPage)): ?>
                            <?php if (!empty($currentImage)): ?>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Current Banner</label><br>
                                    <img src="<?= htmlspecialchars($currentImage) ?>" alt="Current Banner" style="max-width: 100%; height: auto; border:1px solid #ccc;">
                                </div>
                            <?php endif; ?>

                            <div class="col-md-6 mb-3">
                                <label for="imageInput" class="form-label">
                                    <?= $currentImage ? 'Insert Banner Image Size (1440 X 650)' : 'Upload Banner Image' ?>
                                </label>
                                <input type="file" name="image" class="form-control" id="imageInput" accept="image/*" <?= $currentImage ? '' : 'required' ?>>
                            </div>

                            <div class="col-lg-12 text-end">
                                <button type="submit" class="btn btn-primary"><?= $currentImage ? 'Update' : 'Add' ?> Banner</button>
                            </div>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>




                  




                </div> <!-- end col -->


            </div>

        </div>
        
    </div>
    <!-- End Page-content -->

  






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