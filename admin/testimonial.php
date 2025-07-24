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
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />

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
require_once('../inc/db.php'); // Adjust path as needed

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate image
        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Image is required.');
        }

        $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
        $imageTmp = $_FILES['image']['tmp_name'];
        $imageName = basename($_FILES['image']['name']);
        $imageExt = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));

        if (!in_array($imageExt, $allowedExts)) {
            throw new RuntimeException('Invalid image format.');
        }

        $safeImageName = uniqid('testi_', true) . '.' . $imageExt;
        $uploadDir = 'uploads/testimonials/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fullImagePath = $uploadDir . $safeImageName;
        if (!move_uploaded_file($imageTmp, $fullImagePath)) {
            throw new RuntimeException('Failed to upload image.');
        }

        // Sanitize input
        $name = trim($_POST['name'] ?? '');
        $comment = trim($_POST['comment'] ?? '');
        $rating = floatval($_POST['rating'] ?? 0);

        if (empty($name) || empty($comment) || $rating <= 0 || $rating > 5) {
            throw new RuntimeException('All fields are required and rating must be between 1 and 5.');
        }

        // Insert into DB
        $stmt = $conn->prepare("INSERT INTO testimonial (name, rating, comment, image) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $rating, $comment, $fullImagePath]);

        $success = "Testimonial added successfully.";
    } catch (Throwable $e) {
        $error = 'Error: ' . htmlspecialchars($e->getMessage());
    }
}
?>

<div class="row">
    <?php if ($success): ?>
    <div class="alert alert-success">
        <?= $success ?>
    </div>
    <?php elseif ($error): ?>
    <div class="alert alert-danger">
        <?= $error ?>
    </div>
    <?php endif; ?>

    <div class="col-xxl-12">
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">Add Testimonial</h4>
            </div>

            <div class="card-body">
                <div class="live-preview">
                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="nameInput" class="form-label">Name</label>
                                    <input type="text" name="name" class="form-control" id="nameInput"
                                        placeholder="Enter name" required>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="commentInput" class="form-label">Comment</label>
                                    <textarea name="comment" class="form-control" id="commentInput"
                                        placeholder="Write testimonial comment..." rows="4" required></textarea>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="ratingInput" class="form-label">Rating (0.1 - 5.0)</label>
                                    <input type="number" name="rating" class="form-control" id="ratingInput"
                                        placeholder="Enter rating" step="0.1" min="0.1" max="5" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="imageInput" class="form-label">Testimonial Image</label>
                                    <input type="file" name="image" class="form-control" id="imageInput"
                                        accept="image/*" required>
                                </div>
                            </div>

                            <div class="col-lg-12">
                                <div class="text-end">
                                    <button type="submit" class="btn btn-primary">Add Testimonial</button>
                                </div>
                            </div>
                        </div> <!-- end row -->
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>



                   <?php
$stmt = $conn->prepare("SELECT id, name, comment, image, status, rating, created_at FROM testimonial ORDER BY id DESC");
$stmt->execute();
$testimonials = $stmt->fetchAll();
$sn = 1;
?>


<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Testimonials</h4>
            </div><!-- end card header -->

            <div class="card-body">
                <div class="listjs-table" id="testimonialList">
                    <div class="row g-4 mb-3">
                        <div class="col-sm">
                            <div class="d-flex justify-content-sm-end">
                                <div class="search-box ms-2">
                                    <input type="text" class="form-control search" placeholder="Search...">
                                    <i class="ri-search-line search-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive table-card mt-3 mb-1">
                        <table class="table align-middle table-nowrap" id="testimonialTable">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th class="sort" data-sort="image">Image</th>
                                    <th class="sort" data-sort="name">Name</th>
                                    <th class="sort" data-sort="comment">Comment</th>
                                    <th class="sort" data-sort="rating">Rating</th>
                                    <th class="sort" data-sort="status">Status</th>
                                    <th class="sort" data-sort="date">Date</th>
                                    <th class="sort" data-sort="action">Action</th>
                                </tr>
                            </thead>
                            <tbody class="list form-check-all">
                                <?php foreach ($testimonials as $row): ?>
                                <tr>
                                    <td><?= $sn++; ?></td>
                                    <td>
                                        <img src="<?= htmlspecialchars($row['image']) ?>" width="60" height="60"
                                            style="object-fit: cover;" alt="testimonial-img">
                                    </td>
                                    <td class="name"><?= htmlspecialchars($row['name']) ?></td>
                                    <td class="comment"><?= htmlspecialchars(mb_strimwidth($row['comment'], 0, 60, '...')) ?></td>
                                    <td class="rating"><span class="badge bg-warning text-dark"><?= htmlspecialchars($row['rating']) ?> ★</span></td>
                                    <td class="status">
                                        <?php if ($row['status']): ?>
                                            <a href="inc/toggle_testimonial_status?id=<?= $row['id']; ?>&status=0" 
                                               class="badge bg-success"
                                               onclick="return confirm('Unset as active?');">Active</a>
                                        <?php else: ?>
                                            <a href="inc/toggle_testimonial_status?id=<?= $row['id']; ?>&status=1" 
                                               class="badge bg-danger"
                                               onclick="return confirm('Set as active?');">Inactive</a>
                                        <?php endif; ?>
                                    </td>
                                    <td class="date"><?= date("d M, Y", strtotime($row['created_at'])) ?></td>
                                    <td class="action-icons">
                                        <i class="bx bx-edit icon-tooltip"
                                            title="Edit"
                                            style="color: #3B71CA; cursor: pointer;"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editTestimonialModal"
                                            data-id="<?= $row['id']; ?>"
                                            data-name="<?= htmlspecialchars($row['name']); ?>"
                                            data-comment="<?= htmlspecialchars($row['comment']); ?>"
                                            data-rating="<?= htmlspecialchars($row['rating']); ?>"
                                            data-image="<?= htmlspecialchars($row['image']); ?>">
                                        </i>

                                        <i class="bx bx-trash-alt icon-tooltip"
                                            title="Delete"
                                            style="color: #F44336; cursor: pointer;"
                                            onclick="deleteTestimonial(<?= $row['id']; ?>)">
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
                                <p class="text-muted mb-0">We couldn't find any testimonials matching your search.</p>
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
            </div><!-- end card-body -->
        </div>
    </div><!-- end col -->
</div><!-- end row -->


<div class="modal fade" id="editTestimonialModal" tabindex="-1" aria-labelledby="editTestimonialLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="inc/update_testimonial" enctype="multipart/form-data">
      <input type="hidden" name="id" id="edit-testimonial-id">
      <input type="hidden" name="old_image" id="edit-testimonial-image-hidden">

      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Testimonial</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <div class="mb-3">
            <label>Name</label>
            <input type="text" name="name" id="edit-testimonial-name" class="form-control" required>
          </div>

          <div class="mb-3">
            <label>Comment</label>
            <textarea name="comment" id="edit-testimonial-comment" class="form-control" rows="3" required></textarea>
          </div>

          <div class="mb-3">
            <label>Rating (1 to 5)</label>
            <input type="number" name="rating" id="edit-testimonial-rating" class="form-control" min="1" max="5" required>
          </div>

          <div class="mb-3">
            <label>Image</label>
            <input type="file" name="image" class="form-control mb-2">
            <img id="preview-testimonial-image" src="" alt="Current Image" style="max-width: 100px; border-radius: 6px;">
            <small class="d-block">Leave blank to keep the current image.</small>
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Update Testimonial</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </div>
    </form>
  </div>
</div>


                </div> <!-- end col -->


            </div>

        </div>
        
    </div>
    <!-- End Page-content -->

    <script>
function deleteTestimonial(id) {
    Swal.fire({
        title: '<div style="display:flex;align-items:center;"><div style="background:linear-gradient(135deg,#ffc107 0%,#ffecb3 100%);color:#856404;border-radius:50%;width:40px;height:40px;display:flex;align-items:center;justify-content:center;margin-right:12px;font-size:1.5rem;"><i class="bi bi-trash"></i></div><div><div style="font-weight:700;font-size:1.1rem;color:#856404;">Delete Testimonial?</div><div style="font-size:0.95rem;opacity:0.85;color:#856404;">Are you sure you want to delete this testimonial? This action cannot be undone.</div></div></div>',
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
                title: '<div style="display:flex;align-items:center;"><div style="background:linear-gradient(135deg,#28a745 0%,#20c997 100%);color:white;border-radius:50%;width:40px;height:40px;display:flex;align-items:center;justify-content:center;margin-right:12px;font-size:1.5rem;"><i class="bi bi-check-circle"></i></div><div><div style="font-weight:700;font-size:1.1rem;">Deleted!</div><div style="font-size:0.95rem;opacity:0.85;">Testimonial deleted successfully.</div></div></div>',
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
                window.location.href = 'inc/delete_testimonial?id=' + encodeURIComponent(id);
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

<script>
document.addEventListener('DOMContentLoaded', function () {
  const modal = document.getElementById('editTestimonialModal');

  modal.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;

    const id = button.getAttribute('data-id');
    const name = button.getAttribute('data-name');
    const comment = button.getAttribute('data-comment');
    const rating = button.getAttribute('data-rating');
    const image = button.getAttribute('data-image');

    document.getElementById('edit-testimonial-id').value = id;
    document.getElementById('edit-testimonial-name').value = name;
    document.getElementById('edit-testimonial-comment').value = comment;
    document.getElementById('edit-testimonial-rating').value = rating;
    document.getElementById('edit-testimonial-image-hidden').value = image;

    document.getElementById('preview-testimonial-image').src = image;
  });
});
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
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>


<!-- Mirrored from themesbrand.com/velzon/html/default/dashboard.html by HTTrack Website Copier/3.x [XR&CO'2014], Wed, 21 May 2025 11:27:56 GMT -->

</html>