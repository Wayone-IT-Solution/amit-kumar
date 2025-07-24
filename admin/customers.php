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
    <style>
        td i{
            font-size: 15px;
        }
    </style>
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />
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
                                        <h4 class="mb-sm-0">Customers</h4>

                                        <div class="page-title-right">
                                            <ol class="breadcrumb m-0">
                                                <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a>
                                                </li>
                                                <li class="breadcrumb-item active">Customers</li>
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
                                            <h4 class="card-title mb-0">Users List</h4>
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
                                                                <th class="sort" data-sort="phone">Phone</th>
                                                                <th class="sort" data-sort="date">Joining Date</th>
                                                                <th class="sort" data-sort="status">Status</th>
                                                                <th class="sort" data-sort="action">Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="list form-check-all">
                                                            <?php
require '../inc/db.php'; // PDO connection

$stmt = $conn->prepare("SELECT id, fullname, phone, created_at, status FROM users ORDER BY id DESC");
$stmt->execute();
$users = $stmt->fetchAll();
$sn = 1;

foreach ($users as $user) {
    $statusClass = ($user['status'] === 'Active') ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger';
    ?>
                                                            <tr>
                                                                <td><?= $sn++; ?></td>
                                                                <td class="customer_name">
                                                                    <?php echo htmlspecialchars($user['fullname']); ?>
                                                                </td>
                                                                <td class="phone">
                                                                    <?php echo htmlspecialchars($user['phone']); ?>
                                                                </td>
                                                                <td class="date">
                                                                    <?php echo date("d M, Y", strtotime($user['created_at'])); ?>
                                                                </td>
                                                                <td class="status">
                                                                    <?php
$status = $user['status'];
$statusClass = ($status === 'active') ? 'bg-success' : 'bg-danger';
$toggleStatus = ($status === 'active') ? 'inactive' : 'active';
?>

<a href="inc/toggle_user_status?id=<?= $user['id']; ?>&status=<?= $toggleStatus; ?>" 
   class="badge <?= $statusClass; ?> text-uppercase"
   onclick="return confirm('Are you sure you want to change the status?');">
    <?= htmlspecialchars($status); ?>
</a>

                                                                </td>
                                                                <td>
                                                                   <i class="bx bx-trash-alt icon-tooltip"
                                            title="Delete"
                                            style="color: #F44336; cursor: pointer;"
                                            onclick="deleteCustomer(<?= $user['id']; ?>)">
                                        </i>
                                                                </td>
                                                            </tr>
                                                            <?php
}
?>

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
    Swal.fire({
        title: '<div style="display:flex;align-items:center;"><div style="background:linear-gradient(135deg,#ffc107 0%,#ffecb3 100%);color:#856404;border-radius:50%;width:40px;height:40px;display:flex;align-items:center;justify-content:center;margin-right:12px;font-size:1.5rem;"><i class="bi bi-trash"></i></div><div><div style="font-weight:700;font-size:1.1rem;color:#856404;">Delete Customer?</div><div style="font-size:0.95rem;opacity:0.85;color:#856404;">Are you sure you want to delete this customer? This action cannot be undone.</div></div></div>',
        iconHtml: '<i class="bi bi-chat-dots-fill"></i>',
        showCancelButton: true,
        confirmButtonText: 'Yes, Delete',
        cancelButtonText: 'Cancel',
        customClass: {
            popup: 'sms-popup',
            confirmButton: 'btn btn-warning rounded-pill px-4 text-dark',
            cancelButton: 'btn btn-danger rounded-pill px-4',
            title: 'w-100',
        },
        background: 'linear-gradient(135deg,#fffbe6 0%,#fff3cd 100%)',
        buttonsStyling: false,
        focusCancel: true
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'success',
                title: '<div style="display:flex;align-items:center;"><div style="background:linear-gradient(135deg,#28a745 0%,#20c997 100%);color:white;border-radius:50%;width:40px;height:40px;display:flex;align-items:center;justify-content:center;margin-right:12px;font-size:1.5rem;"><i class="bi bi-check-circle"></i></div><div><div style="font-weight:700;font-size:1.1rem;">Deleted!</div><div style="font-size:0.95rem;opacity:0.85;">Customer deleted successfully.</div></div></div>',
                showConfirmButton: false,
                timer: 1200,
                background: 'linear-gradient(135deg,#e9fbe7 0%,#e0f7fa 100%)',
                customClass: {
                    popup: 'sms-popup',
                    title: 'w-100',
                },
                iconHtml: '<i class="bi bi-chat-dots-fill"></i>',
            });
            setTimeout(function() {
                window.location.href = 'inc/delete_customer?id=' + encodeURIComponent(id);
            }, 1200);
        }
    });
}
</script>
<style>
.sms-popup {
    border-radius: 15px !important;
    box-shadow: 0 10px 30px rgba(255, 193, 7, 0.15) !important;
    border-left: 5px solid #ffc107 !important;
    max-width: 400px;
    padding: 20px 25px !important;
    font-family: 'Montserrat', 'Roboto', Arial, sans-serif;
}
</style>














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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- App js -->
    <script src="assets/js/app.js"></script>
</body>



</html>