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
require_once('../inc/db.php'); // Adjust path to db.php

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate image
        if (!isset($_FILES['box_image']) || $_FILES['box_image']['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Box image is required.');
        }

        $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
        $imageTmp = $_FILES['box_image']['tmp_name'];
        $imageName = basename($_FILES['box_image']['name']);
        $imageExt = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));

        if (!in_array($imageExt, $allowedExts)) {
            throw new RuntimeException('Invalid image format. Only JPG, JPEG, PNG, WEBP allowed.');
        }

        $safeImageName = uniqid('cat_', true) . '.' . $imageExt;
        $uploadDir = 'uploads/boxes/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fullImagePath = $uploadDir . $safeImageName;
        if (!move_uploaded_file($imageTmp, $fullImagePath)) {
            throw new RuntimeException('Failed to save uploaded image.');
        }

        // Sanitize inputs
        $box_name = trim($_POST['box_name'] ?? '');
        $box_price = trim($_POST['box_price'] ?? '');

        if (empty($box_name)) {
            throw new RuntimeException('Box name is required.');
        }

        if (str_word_count($box_name) > 10) {
            throw new RuntimeException('Box name must not exceed 10 words.');
        }

        if (!is_numeric($box_price) || floatval($box_price) < 0) {
            throw new RuntimeException('Box price must be a non-negative number.');
        }

        // Insert into DB
        $stmt = $conn->prepare("INSERT INTO boxes (box_name, box_price, box_image) VALUES (:box_name, :box_price, :box_image)");
        $stmt->execute([
            ':box_name' => $box_name,
            ':box_price' => $box_price,
            ':box_image' => $fullImagePath
        ]);

        $success = "Box added successfully.";
    } catch (Throwable $e) {
        error_log('Box submission failed: ' . $e->getMessage());
        $error = 'Error: ' . htmlspecialchars($e->getMessage());
    }
}
?>

<div class="row">
    <?php if ($success): ?>
    <div class="alert alert-success">
        <?php echo $success; ?>
    </div>
    <?php elseif ($error): ?>
    <div class="alert alert-danger">
        <?php echo $error; ?>
    </div>
    <?php endif; ?>

    <div class="col-xxl-12">
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">Add Box</h4>
            </div>

            <div class="card-body">
                <div class="live-preview">
                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="boxNameInput" class="form-label">Box Name</label>
                                    <input type="text" name="box_name" class="form-control"
                                        placeholder="Enter Box Name" id="boxNameInput" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="boxPriceInput" class="form-label">Box Price</label>
                                    <input type="number" name="box_price" class="form-control"
                                        placeholder="Enter Box Price" id="boxPriceInput" step="0.01" min="0" required>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="boxImageInput" class="form-label">Box Image</label>
                                    <input type="file" name="box_image" class="form-control"
                                        id="boxImageInput" accept="image/*" required>
                                </div>
                            </div>

                            <div class="col-lg-12">
                                <div class="text-end">
                                    <button type="submit" class="btn btn-primary">Add Box</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div><!-- end live-preview -->
            </div>
        </div>
    </div>
</div>


                   <?php
require_once('../inc/db.php');

$stmt = $conn->prepare("SELECT id, box_name, box_image, box_price, created_at FROM boxes ORDER BY id DESC");
$stmt->execute();
$sn = 1;
$boxes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Boxes</h4>
            </div>

            <div class="card-body">
                <div class="listjs-table" id="boxesList">
                    <div class="row g-4 mb-3">
                        <div class="col-sm d-flex justify-content-sm-end">
                            <div class="search-box ms-2">
                                <input type="text" class="form-control search" placeholder="Search...">
                                <i class="ri-search-line search-icon"></i>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive table-card mt-3 mb-1">
                        <table class="table align-middle table-nowrap" id="boxesTable">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th class="sort" data-sort="box_name">Box Name</th>
                                    <th class="sort" data-sort="box_image">Box Image</th>
                                    <th class="sort" data-sort="box_price">Box Price</th>
                                    <th class="sort" data-sort="created_at">Created Date</th>
                                    <th class="sort" data-sort="action">Action</th>
                                </tr>
                            </thead>
                            <tbody class="list form-check-all">
                                <?php foreach ($boxes as $box): ?>
                                    <tr>
                                        <td><?= $sn++; ?></td>
                                        
                                        <td class="box_name"><?= htmlspecialchars($box['box_name']) ?></td>
                                        <td class="box_image">
                                            <img src="<?= htmlspecialchars($box['box_image']) ?>" width="60" height="60" alt="Box Image">
                                        </td>
                                        <td class="box_price"><?= htmlspecialchars($box['box_price']) ?></td>
                                        <td class="created_at"><?= date("d M, Y", strtotime($box['created_at'])) ?></td>
                                        <td class="action">
                                            <i class="bx bx-edit icon-tooltip"
                                               title="Edit"
                                               style="color: #3B71CA; cursor: pointer;"
                                               data-bs-toggle="modal"
                                               data-bs-target="#editBoxModal"
                                               data-id="<?= $box['id']; ?>"
                                               data-name="<?= htmlspecialchars($box['box_name']); ?>"
                                               data-box_image="<?= htmlspecialchars($box['box_image']); ?>"
                                               data-box_price="<?= htmlspecialchars($box['box_price']); ?>"
                                               data-created_at="<?= htmlspecialchars($box['created_at']); ?>">
                                            </i>

                                            <i class="bx bx-trash-alt icon-tooltip"
                                               title="Delete"
                                               style="color: #F44336; cursor: pointer;"
                                               onclick="deleteBox(<?= $box['id']; ?>)">
                                            </i>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <div class="noresult" style="display: none">
                            <div class="text-center">
                                <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop"
                                           colors="primary:#121331,secondary:#08a88a" style="width:75px;height:75px">
                                </lord-icon>
                                <h5 class="mt-2">Sorry! No Result Found</h5>
                                <p class="text-muted mb-0">We couldn't find any boxes matching your search.</p>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <div class="pagination-wrap hstack gap-2">
                            <a class="page-item pagination-prev disabled" href="javascript:void(0);">Previous</a>
                            <ul class="pagination listjs-pagination mb-0"></ul>
                            <a class="page-item pagination-next" href="javascript:void(0);">Next</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<!-- Edit Box Modal -->
<div class="modal fade" id="editBoxModal" tabindex="-1" aria-labelledby="editBoxLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="inc/update_box" enctype="multipart/form-data">
      <input type="hidden" name="id" id="edit-box-id">
      <input type="hidden" name="old_image" id="edit-box-old-image">

      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editBoxLabel">Edit Box</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <div class="mb-3">
            <label>Box Name</label>
            <input type="text" name="box_name" id="edit-box-name" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Box Price</label>
            <input type="number" name="box_price" id="edit-box-price" class="form-control" step="0.01" min="0" required>
          </div>

          <div class="mb-3">
            <label>Box Image</label>
            <input type="file" name="box_image" class="form-control mb-2">
            <img id="preview-box-image" src="" alt="Current Image" style="max-width: 100px; border-radius: 6px;">
            <small class="d-block">Leave blank to keep the current image.</small>
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Update Box</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const modal = document.getElementById('editBoxModal');
  modal.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;

    const id = button.getAttribute('data-id');
    const name = button.getAttribute('data-name');
    const image = button.getAttribute('data-box_image');
    const price = button.getAttribute('data-box_price');

    document.getElementById('edit-box-id').value = id;
    document.getElementById('edit-box-name').value = name;
    document.getElementById('edit-box-price').value = price;
    document.getElementById('edit-box-old-image').value = image;
    document.getElementById('preview-box-image').src = image;
  });
});
</script>


                </div> <!-- end col -->


            </div>

        </div>
        <!-- container-fluid -->
    </div>
    <!-- End Page-content -->

    <script>
function deleteBox(id) {
    if (confirm("Are you sure you want to delete this boxes?")) {
        window.location.href = 'inc/delete_box?id=' + id;
    }
}
</script>





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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/list.js/2.3.1/list.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var options = {
                valueNames: [
                    'box_name',
                    'box_image',
                    'box_price',
                    'created_at',
                    'action'
                ],
                searchColumns: ['box_name', 'box_image', 'box_price', 'created_at'],
                page: 10,
                pagination: true
            };  

            var boxesList = new List('boxesList', options);

            // Update 'noresult' element visibility
            boxesList.on('updated', function (list) {
                var isEmpty = list.matchingItems.length == 0;   
                var noresultEl = document.querySelector('.noresult');
                
                if (noresultEl) {
                    noresultEl.style.display = isEmpty ? 'block' : 'none';
                }
            });
        });
    </script>
</body>


<!-- Mirrored from themesbrand.com/velzon/html/default/dashboard.html by HTTrack Website Copier/3.x [XR&CO'2014], Wed, 21 May 2025 11:27:56 GMT -->

</html>