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

        <?php include ("inc/header.php"); ?>
        <!-- Left Sidebar End -->
        <!-- Vertical Overlay-->
        <div class="vertical-overlay"></div>

        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">

            <div class="page-content">
                <div class="container-fluid">

                    <div class="row">
                        <div class="col">

                            <!-- start page title -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                        <h4 class="mb-sm-0">Customers Enquiries</h4>

                                        <div class="page-title-right">
                                            <ol class="breadcrumb m-0">
                                                <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a>
                                                </li>
                                                <li class="breadcrumb-item active">Customers Enquiries</li>
                                            </ol>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <!-- end page title -->






                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h4 class="card-title mb-0">Users Data</h4>
                                        </div><!-- end card header -->

                                        <div class="card-body">
                                            <div class="listjs-table" id="customerList">
                                                <div class="row g-4 mb-3">
                                                   
                                                    <div class="col-sm">
                                                        <div class="d-flex justify-content-sm-end">
                                                            <div class="search-box ms-2">
                                                                <input type="text" class="form-control search"
                                                                    placeholder="Search...">
                                                                <i class="ri-search-line search-icon"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="table-responsive table-card mt-3 mb-1">
                                                    <table class="table align-middle table-nowrap" id="customerTable">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>#</th>
                                                                <th class="sort" data-sort="customer_name">Customer</th>
                                                                <th class="sort" data-sort="email">email</th>
                                                                <th class="sort" data-sort="Phone">Phone</th>
                                                                <th class="sort" data-sort="message">Message</th>
                                                                 <th class="sort" data-sort="date">Enquire At</th>
                                                                <th class="sort" data-sort="action">Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="list form-check-all">
                                                                                                                        <?php
                                                            require '../inc/db.php'; // Include your PDO connection

                                                            $stmt = $conn->prepare("SELECT id, name, phone, email, description, created_at FROM contact ORDER BY id DESC");
                                                            $stmt->execute();
                                                            $contacts = $stmt->fetchAll();
                                                            $sn = 1;

                                                            foreach ($contacts as $contact) {
                                                            ?>
                                                            <tr>
                                                            <td><?= $sn++; ?></td>
                                                                <td class="customer_name">
                                                                    <?= htmlspecialchars($contact['name']); ?>
                                                                </td>
                                                                <td class="email">
                                                                    <a href="mailto: <?= htmlspecialchars($contact['email']); ?>"><?= htmlspecialchars($contact['email']); ?></a>
                                                                    
                                                                </td>
                                                                <td class="phone">
                                                                    <a href="tel:<?= htmlspecialchars($contact['phone']); ?>"><?= htmlspecialchars($contact['phone']); ?></a>
                                                                    
                                                                </td>
                                                                <td class="message">
                                                                    <?= htmlspecialchars($contact['description']); ?>
                                                                </td>
                                                                <td class="date">
                                                                    <?= date("d M, Y", strtotime($contact['created_at'])); ?>
                                                                </td>
                                                                
                                                                <td>
                                                                    <i class="bx bx-trash-alt icon-tooltip"
                                                                        title="Delete"
                                                                        style="color: #F44336; cursor: pointer;"
                                                                        onclick="deleteCustomer(<?= $contact['id']; ?>)">
                                                                    </i>
                                                                </td>
                                                            </tr>
                                                            <?php } ?>


                                                        </tbody>
                                                    </table>
                                                    <div class="noresult" style="display: none">
                                                        <div class="text-center">
                                                            <lord-icon src="https://cdn.lordicon.com/msoeawqm.json"
                                                                trigger="loop"
                                                                colors="primary:#121331,secondary:#08a88a"
                                                                style="width:75px;height:75px"></lord-icon>
                                                            <h5 class="mt-2">Sorry! No Result Found</h5>
                                                            <p class="text-muted mb-0">We've searched more than 150+
                                                                Orders We did not find any orders for you search.</p>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="d-flex justify-content-end">
                                                    <div class="pagination-wrap hstack gap-2">
                                                        <a class="page-item pagination-prev disabled"
                                                            href="javascript:void(0);">
                                                            Previous
                                                        </a>
                                                        <ul class="pagination listjs-pagination mb-0"></ul>
                                                        <a class="page-item pagination-next" href="javascript:void(0);">
                                                            Next
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div><!-- end card -->
                                    </div>
                                    <!-- end col -->
                                </div>
                                <!-- end col -->
                            </div>
                            <!-- end row -->







 <script>
function deleteCustomer(id) {
    if (confirm("Are you sure you want to delete this Customer?")) {
        window.location.href = 'inc/delete_customer?id=' + id;
    }
}
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

    <script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/libs/simplebar/simplebar.min.js"></script>
    <script src="assets/libs/node-waves/waves.min.js"></script>
    <script src="assets/libs/feather-icons/feather.min.js"></script>
    <script src="assets/js/pages/plugins/lord-icon-2.1.0.js"></script>
    <script src="assets/js/plugins.js"></script>
    <!-- prismjs plugin -->
    <script src="assets/libs/prismjs/prism.js"></script>
    <script src="assets/libs/list.js/list.min.js"></script>
    <script src="assets/libs/list.pagination.js/list.pagination.min.js"></script>

    <!-- listjs init -->
    <script src="assets/js/pages/listjs.init.js"></script>

    <!-- Sweet Alerts js -->
    <script src="assets/libs/sweetalert2/sweetalert2.min.js"></script>

    <!-- App js -->
    <script src="assets/js/app.js"></script>
</body>



</html>