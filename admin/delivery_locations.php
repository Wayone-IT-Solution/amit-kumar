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

        <?php include('inc/header.php'); ?>
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
                    $pincodeData = [
                        'address' => '',
                        'pincode' => '',
                        'status' => 'active'
                    ];

                    try {
                        // Handle update request
                        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update') {
                            $id = intval($_POST['id'] ?? 0);
                            $address = trim($_POST['address'] ?? '');
                            $pincode = trim($_POST['pincode'] ?? '');
                            $status = $_POST['status'] ?? 'inactive';

                            if ($id <= 0) {
                                throw new RuntimeException('Invalid ID.');
                            }
                            if (empty($address)) {
                                throw new RuntimeException('Address is required.');
                            }
                            if (!preg_match('/^\d{4,10}$/', $pincode)) {
                                throw new RuntimeException('Valid pincode is required.');
                            }
                            if (!in_array($status, ['active', 'inactive'])) {
                                throw new RuntimeException('Invalid status selected.');
                            }

                            $stmt = $conn->prepare("UPDATE pincodes SET address = :address, pincode = :pincode, status = :status WHERE id = :id");
                            $stmt->execute([
                                ':address' => $address,
                                ':pincode' => $pincode,
                                ':status' => $status,
                                ':id' => $id
                            ]);

                            $success = "Pincode updated successfully.";
                        }
                        // Handle add request
                        else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                            // Sanitize & validate inputs
                            $address = trim($_POST['address'] ?? '');
                            $pincode = trim($_POST['pincode'] ?? '');
                            $status = $_POST['status'] ?? 'inactive';

                            if (empty($address)) {
                                throw new RuntimeException('Address is required.');
                            }

                            if (!preg_match('/^\d{4,10}$/', $pincode)) {
                                throw new RuntimeException('Valid pincode is required.');
                            }

                            if (!in_array($status, ['active', 'inactive'])) {
                                throw new RuntimeException('Invalid status selected.');
                            }

                            // Insert into DB
                            $stmt = $conn->prepare("INSERT INTO pincodes (address, pincode, status) VALUES (:address, :pincode, :status)");
                            $stmt->execute([
                                ':address' => $address,
                                ':pincode' => $pincode,
                                ':status' => $status
                            ]);

                            $success = "Pincode added successfully.";
                            $pincodeData = ['address' => '', 'pincode' => '', 'status' => 'active']; // clear form after submit
                        }
                    } catch (Throwable $e) {
                        error_log('Pincode submission failed: ' . $e->getMessage());
                        $error = 'Error: ' . htmlspecialchars($e->getMessage());
                    }

                    // Load latest pincode data
                    $pincodes = []; // Always initialize to avoid "undefined variable" warning
                    
                    try {
                        $stmt = $conn->query("SELECT * FROM pincodes ORDER BY id DESC");
                        $pincodes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    } catch (Throwable $e) {
                        echo "<div class='alert alert-danger'>Error fetching pincodes: " . $e->getMessage() . "</div>";
                    }


                    ?>

                    <!-- HTML Part -->
                    <div class="col-xxl-12">
                        <div class="card">
                            <div class="card-header align-items-center d-flex">
                                <h4 class="card-title mb-0 flex-grow-1">Add / Update Pincodes</h4>
                            </div>

                            <div class="card-body">
                                <?php if (!empty($success)): ?>
                                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                                <?php elseif (!empty($error)): ?>
                                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                                <?php endif; ?>

                                <div class="live-preview">

                                    <form method="POST" action="">
                                        <div class="row">
                                            <!-- Address Textarea -->
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label for="address" class="form-label">Enter Your Address</label>
                                                    <textarea class="form-control" name="address" id="address" rows="3"
                                                        required><?= htmlspecialchars($pincodeData['address'] ?? '') ?></textarea>
                                                </div>
                                            </div>

                                            <!-- Pincode Input -->
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label for="pincode" class="form-label">Enter Your Pincode</label>
                                                    <input class="form-control" type="number" name="pincode"
                                                        id="pincode" required
                                                        value="<?= htmlspecialchars($pincodeData['pincode'] ?? '') ?>"
                                                        autocomplete="off">
                                                </div>
                                            </div>

                                            <!-- Status Dropdown -->
                                            <div class="col-md-2">
                                                <div class="mb-3">
                                                    <label for="status" class="form-label">Status</label>
                                                    <select id="status" name="status" class="form-control"  style="width: 100%;" required>
                                                        <option value="active" <?= (isset($pincodeData['status']) && $pincodeData['status'] === 'active') ? 'selected' : '' ?>>
                                                            Active</option>
                                                        <option value="inactive" <?= (isset($pincodeData['status']) && $pincodeData['status'] === 'inactive') ? 'selected' : '' ?>>
                                                            Inactive</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- Submit Button -->
                                            <div class="col-12 text-end">
                                                <button type="submit" class="btn btn-primary">Save Pincodes</button>
                                            </div>
                                        </div>
                                    </form>



                                </div> <!-- end live-preview -->
                            </div>
                        </div>
                    </div>


                    <div class="row mb-5">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title mb-0">Delivery Location</h4>
                                </div>

                                <div class="card-body">
                                    <div class="listjs-table" id="categoryList">
                                        <!-- <div class="row g-4 mb-3">
                        <div class="col-sm d-flex justify-content-sm-end">
                            <div class="search-box ms-2">
                                <input type="text" class="form-control search" placeholder="Search...">
                                <i class="ri-search-line search-icon"></i>
                            </div>
                        </div>
                    </div> -->

                                        <div class="table-responsive table-card mt-3 mb-1">
                                            <table class="table align-middle table-nowrap" id="categoryTable">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>#</th>
                                                        <th class="sort" data-sort="category_name">Address</th>
                                                        <th class="sort" data-sort="category_image">Pincode</th>
                                                        <th class="sort" data-sort="status">Status</th>
                                                        <th class="sort" data-sort="date">Created Date</th>
                                                        <th class="sort" data-sort="action">Action</th>
                                                    </tr>
                                                </thead>

                                                <tbody class="list form-check-all">
                                                    <?php
                                                    $sn = 1;
                                                    foreach ($pincodes as $pincode): ?>
                                                        <tr>
                                                            <!-- Serial Number -->
                                                            <td><?= $sn++; ?></td>

                                                            <!-- Address -->
                                                            <td class="address"><?= htmlspecialchars($pincode['address']) ?>
                                                            </td>

                                                            <!-- Pincode -->
                                                            <td class="pincode"><?= htmlspecialchars($pincode['pincode']) ?>
                                                            </td>

                                                            <!-- Status Toggle -->
                                                            <td class="status">
    <form method="GET" action="/amit-kumar/admin/inc/toggle_pincode_status.php" id="status-form-<?= $pincode['id']; ?>" style="position: relative;">
        <input type="hidden" name="id" value="<?= $pincode['id']; ?>">
        <input type="hidden" name="status" id="status-input-<?= $pincode['id']; ?>">

        <!-- Status Button -->
        <button type="button"
                class="btn btn-sm <?= $pincode['status'] === 'active' ? 'btn-success' : 'btn-danger' ?>"
                onclick="toggleDropdown(<?= $pincode['id']; ?>)">
            <?= ucfirst($pincode['status']) ?>
        </button>

        <!-- Custom Dropdown (Initially Hidden) -->
        <div id="dropdown-<?= $pincode['id']; ?>" class="dropdown-menu show" style="display: none; position: absolute; top: 100%; left: 0;">
            <a href="#" class="dropdown-item" onclick="changeStatus(<?= $pincode['id']; ?>, 'active')">Active</a>
            <a href="#" class="dropdown-item" onclick="changeStatus(<?= $pincode['id']; ?>, 'inactive')">Inactive</a>
        </div>
    </form>
</td>
<script>
    function toggleDropdown(id) {
        // Close all dropdowns first
        document.querySelectorAll('[id^="dropdown-"]').forEach(el => el.style.display = 'none');

        // Toggle the clicked one
        const dropdown = document.getElementById('dropdown-' + id);
        if (dropdown.style.display === 'block') {
            dropdown.style.display = 'none';
        } else {
            dropdown.style.display = 'block';
        }
    }

    function changeStatus(id, status) {
        document.getElementById('status-input-' + id).value = status;
        document.getElementById('status-form-' + id).submit();
    }

    // Optional: Click anywhere outside to close dropdown
    document.addEventListener('click', function (e) {
        if (!e.target.closest('form')) {
            document.querySelectorAll('[id^="dropdown-"]').forEach(el => el.style.display = 'none');
        }
    });
</script>





                                                            <!-- Created Date -->
                                                            <td class="date">
                                                                <?= date("d M, Y", strtotime($pincode['created_at'])) ?>
                                                            </td>

                                                            <!-- Action Icons -->
                                                            <td class="action-icons">
                                                                <!-- Edit Icon -->
                                                                <i class="bx bx-edit icon-tooltip" title="Edit"
                                                                    style="color: #3B71CA; cursor: pointer;"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#editPincodeModal"
                                                                    data-id="<?= $pincode['id']; ?>"
                                                                    data-address="<?= htmlspecialchars($pincode['address'], ENT_QUOTES); ?>"
                                                                    data-pincode="<?= htmlspecialchars($pincode['pincode'], ENT_QUOTES); ?>"
                                                                    data-status="<?= htmlspecialchars($pincode['status'], ENT_QUOTES); ?>">
                                                                </i>

                                                                <!-- Delete Icon -->
                                                                <i class="bx bx-trash-alt icon-tooltip" title="Delete"
                                                                    style="color: #F44336; cursor: pointer;"
                                                                    onclick="deletePincode(<?= $pincode['id']; ?>)">
                                                                </i>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>


                                            </table>

                                            <div class="noresult" style="display: none">
                                                <div class="text-center">
                                                    <lord-icon src="https://cdn.lordicon.com/msoeawqm.json"
                                                        trigger="loop" colors="primary:#121331,secondary:#08a88a"
                                                        style="width:75px;height:75px">
                                                    </lord-icon>
                                                    <h5 class="mt-2">Sorry! No Result Found</h5>
                                                    <p class="text-muted mb-0">We couldn't find any categories matching
                                                        your search.</p>
                                                </div>
                                            </div>
                                        </div>


                                    </div>
                                </div><!-- end card-body -->
                            </div>
                        </div>
                    </div>


                    <div class="modal fade" id="editPincodeModal" tabindex="-1" aria-labelledby="editCategoryLabel"
                        aria-hidden="true">
                        <div class="modal-dialog">
                            <!-- Edit Pincode Modal -->
                            <form method="POST" action="">
                                <input type="hidden" name="id" id="edit-pincode-id">
                                <input type="hidden" name="action" value="update">

                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Pincode</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <div class="modal-body">
                                        <!-- Address Field -->
                                        <div class="mb-3">
                                            <label for="edit-address" class="form-label">Address</label>
                                            <textarea name="address" id="edit-address" class="form-control" rows="3"
                                                required></textarea>
                                        </div>

                                        <!-- Pincode Input -->
                                        <div class="mb-3">
                                            <label for="edit-pincode" class="form-label">Pincode</label>
                                            <input type="number" name="pincode" id="edit-pincode" class="form-control"
                                                required>
                                        </div>

                                        <!-- Status Dropdown -->
                                        <div class="mb-3">
                                            <label for="edit-status" class="form-label">Status</label>
                                            <select name="status" id="edit-status" class="form-select" required>
                                                <option value="active">Active</option>
                                                <option value="inactive">Inactive</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-primary">Update Pincode</button>
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Cancel</button>
                                    </div>
                                </div>
                            </form>


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


            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const modal = document.getElementById('editPincodeModal');
                    modal.addEventListener('show.bs.modal', function (event) {
                        const button = event.relatedTarget;

                        document.getElementById('edit-pincode-id').value = button.getAttribute('data-id');
                        document.getElementById('edit-address').value = button.getAttribute('data-address');
                        document.getElementById('edit-pincode').value = button.getAttribute('data-pincode');
                        document.getElementById('edit-status').value = button.getAttribute('data-status');
                    });
                });

                function deletePincode(id) {
                    if (confirm('Are you sure you want to delete this pincode?')) {
                        window.location.href = 'inc/delete_pincode.php?id=' + id;
                    }
                }
            </script>




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