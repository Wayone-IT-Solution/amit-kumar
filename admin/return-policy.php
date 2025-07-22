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
    <script src="https://cdn.ckeditor.com/4.21.0/standard/ckeditor.js"></script>

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
require_once '../inc/db.php';

$success = '';
$error = '';
$content = '';

// For update: fetch existing disclaimer (assuming single row with id=1)
$stmt = $conn->prepare("SELECT content FROM return_policy WHERE id = 1");
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ($row) {
    $content = $row['content'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $content = $_POST['content'] ?? '';
        if (empty(trim(strip_tags($content)))) {
            throw new Exception("Policy content cannot be empty.");
        }

        // Check if disclaimer exists - update else insert
        $stmt = $conn->prepare("SELECT COUNT(*) FROM return_policy WHERE id = 1");
        $stmt->execute();
        $exists = $stmt->fetchColumn() > 0;

        if ($exists) {
            $update = $conn->prepare("UPDATE return_policy SET content = :content WHERE id = 1");
            $update->execute([':content' => $content]);
            $success = "Policy updated successfully.";
        } else {
            $insert = $conn->prepare("INSERT INTO return_policy (id, content) VALUES (1, :content)");
            $insert->execute([':content' => $content]);
            $success = "Policy added successfully.";
        }

    } catch (Exception $e) {
        $error = "Error: " . htmlspecialchars($e->getMessage());
    }
}
?>


                    <div class="row">
    <?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
    <?php elseif ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="col-xxl-12">
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">Return Policy</h4>
            </div>
            <div class="card-body">
                <div class="live-preview">
                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="row">
                            

                            

                            <div class="col-md-12 mb-3">
                                <label for="descInput" class="form-label">Return Policy</label>
                                <textarea name="content" id="content" rows="10" cols="80" class="form-control"
                                    placeholder="Enter Website Return Policy"><?= htmlspecialchars($content) ?></textarea>
                            </div>

                            


                            <div class="col-lg-12">
                                <div class="text-end">
                                    <button type="submit" class="btn btn-primary">Update Policy</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div> <!-- live-preview -->
            </div> <!-- card-body -->
        </div>
    </div>
</div>


        <script>
    CKEDITOR.replace('content', {
        height: 300
    });
    CKEDITOR.disableAutoInline = true;
      CKEDITOR.config.versionCheck = false;
</script>



                </div> <!-- end col -->


            </div>

        </div>
        <!-- container-fluid -->
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