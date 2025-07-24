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
require_once('../inc/db.php');

$success = '';
$error = '';
$boxes = [];

try {
    // Fetch all boxes for multi-select
    $stmt = $conn->query("SELECT id, box_name FROM boxes ORDER BY box_name ASC");
    $boxes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $error = "Failed to fetch boxes.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate image
        if (!isset($_FILES['category_image']) || $_FILES['category_image']['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Category image is required.');
        }

        $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
        $imageTmp = $_FILES['category_image']['tmp_name'];
        $imageName = basename($_FILES['category_image']['name']);
        $imageExt = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));

        if (!in_array($imageExt, $allowedExts)) {
            throw new RuntimeException('Invalid image format. Only JPG, JPEG, PNG, WEBP allowed.');
        }

        $safeImageName = uniqid('cat_', true) . '.' . $imageExt;
        $uploadDir = 'uploads/categories/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fullImagePath = $uploadDir . $safeImageName;
        if (!move_uploaded_file($imageTmp, $fullImagePath)) {
            throw new RuntimeException('Failed to save uploaded image.');
        }

        // Sanitize input
        $title = trim($_POST['title'] ?? '');
        if (empty($title)) {
            throw new RuntimeException('Category title is required.');
        }

        if (str_word_count($title) > 10) {
            throw new RuntimeException('Title must not exceed 10 words.');
        }

        // Get selected box IDs and store them as JSON
$selectedBoxIds = array_filter($_POST['box_ids'] ?? [], 'is_numeric');
$boxIdsJson = json_encode(array_map('intval', $selectedBoxIds));



       $stmt = $conn->prepare("INSERT INTO categories (title, category_image, box_ids_json) VALUES (:title, :image, :boxes)");
$stmt->execute([
    ':title' => $title,
    ':image' => $fullImagePath,
    ':boxes' => $boxIdsJson
]);


        $success = "Category added successfully with selected boxes.";
    } catch (Throwable $e) {
        error_log('Category submission failed: ' . $e->getMessage());
        $error = 'Error: ' . htmlspecialchars($e->getMessage());
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
                    <div class="card-header d-flex align-items-center">
                        <h4 class="card-title mb-0 flex-grow-1">Add Category</h4>
                    </div>
                    <div class="card-body">
                        <form action="" method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="titleInput" class="form-label">Category Title</label>
                                        <input type="text" name="title" class="form-control" placeholder="Enter category title" id="titleInput" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="categoryImageInput" class="form-label">Category Image</label>
                                        <input type="file" name="category_image" class="form-control" id="categoryImageInput" accept="image/*" required>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="boxSelect" class="form-label">Select Boxes</label>
                                        <select name="box_ids[]" id="boxSelect" class="form-select" multiple >
                                            <?php foreach ($boxes as $box): ?>
                                                <option value="<?php echo $box['id']; ?>"><?php echo htmlspecialchars($box['box_name']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small class="text-muted">Hold Ctrl/Cmd to select multiple boxes.</small>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="text-end">
                                        <button type="submit" class="btn btn-primary">Add Category</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    


        <?php
$stmt = $conn->prepare("SELECT id, title, category_image, created_at, status, box_ids_json FROM categories ORDER BY id DESC");
$stmt->execute();
$categories = $stmt->fetchAll();
$sn = 1;
?>


<div class="row mb-5">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Categories</h4>
            </div>

            <div class="card-body">
                <div class="listjs-table" id="categoryList">
                    <div class="row g-4 mb-3">
                        <div class="col-sm d-flex justify-content-sm-end">
                            <div class="search-box ms-2">
                                <input type="text" class="form-control search" placeholder="Search...">
                                <i class="ri-search-line search-icon"></i>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive table-card mt-3 mb-1">
                        <table class="table align-middle table-nowrap" id="categoryTable">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th class="sort" data-sort="category_name">Category</th>
                                    <th class="sort" data-sort="category_image">Image</th>
                                    <th class="sort" data-sort="status">Status</th>
                                    <th class="sort" data-sort="date">Created Date</th>
                                    <th class="sort" data-sort="action">Action</th>
                                </tr>
                            </thead>
                            <tbody class="list form-check-all">
                                <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td><?= $sn++; ?></td>
                                    <td class="category_name"><?= htmlspecialchars($category['title']) ?></td>
                                    <td><img class="category_image" src="<?= htmlspecialchars($category['category_image']) ?>" width="60px" height="60px" alt=""></td>

                                    

                                    <!-- Status Toggle -->
                                    <td class="status">
                                        <?php if ($category['status']): ?>
                                            <a href="inc/toggle_category_status?id=<?= $category['id']; ?>&status=0"
                                               class="badge bg-success"
                                               onclick="return confirm('Mark as Inactive?');">Active</a>
                                        <?php else: ?>
                                            <a href="inc/toggle_category_status?id=<?= $category['id']; ?>&status=1"
                                               class="badge bg-danger"
                                               onclick="return confirm('Mark as Active?');">Inactive</a>
                                        <?php endif; ?>
                                    </td>

                                    <td class="date"><?= date("d M, Y", strtotime($category['created_at'])) ?></td>

                                    <td class="action-icons">
                                        <i class="bx bx-edit icon-tooltip"
                                           title="Edit"
                                           style="color: #3B71CA; cursor: pointer;"
                                           data-bs-toggle="modal"
                                           data-bs-target="#editCategoryModal"
                                           data-id="<?= $category['id']; ?>"
                                           data-title="<?= htmlspecialchars($category['title']); ?>"
                                           data-category_image="<?= htmlspecialchars($category['category_image']); ?>"
                                           data-created_at="<?= htmlspecialchars($category['created_at']); ?>"
                                           data-box_ids='<?= htmlspecialchars($category['box_ids_json']); ?>'></i>

                                        <i class="bx bx-trash-alt icon-tooltip"
                                           title="Delete"
                                           style="color: #F44336; cursor: pointer;"
                                           onclick="deleteCategory(<?= $category['id']; ?>)">
                                        </i>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <div class="noresult" style="display: none">
                            <div class="text-center">
                                <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop"
                                           colors="primary:#121331,secondary:#08a88a"
                                           style="width:75px;height:75px">
                                </lord-icon>
                                <h5 class="mt-2">Sorry! No Result Found</h5>
                                <p class="text-muted mb-0">We couldn't find any categories matching your search.</p>
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
    </div>
</div>


</div>
</div>

<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="inc/update_category" enctype="multipart/form-data">
      <input type="hidden" name="id" id="edit-cat-id">
      <input type="hidden" name="old_image" id="edit-category_image">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editCategoryLabel">Edit Category</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="edit-cat-title" class="form-label">Title</label>
            <input type="text" name="title" id="edit-cat-title" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="editBoxSelect" class="form-label">Boxes</label>
            <select name="box_ids[]" id="editBoxSelect" class="form-select" multiple >
              <?php foreach ($boxes as $box): ?>
                <option value="<?= $box['id']; ?>"><?= htmlspecialchars($box['box_name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label for="categoryImageInput" class="form-label">Category Image</label>
            <input type="file" name="category_image" id="categoryImageInput" class="form-control mb-2" accept="image/*">
            <!-- Preview current image -->
            <img id="preview-category-image" src="" alt="Current Image" style="max-width: 100px; border-radius: 6px;">
            <small class="d-block">Leave blank to keep the current image.</small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Update Category</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const editModal = document.getElementById('editCategoryModal');

    editModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;

        // Extract data attributes from the clicked edit button
        const id = button.getAttribute('data-id') || '';
        const title = button.getAttribute('data-title') || '';
        const image = button.getAttribute('data-category_image') || '';
        const boxIdsJson = button.getAttribute('data-box_ids') || '[]';

        // Set form inputs in modal
        this.querySelector('#edit-cat-id').value = id;
        this.querySelector('#edit-cat-title').value = title;
        this.querySelector('#preview-category-image').src = image;
        this.querySelector('#edit-category_image').value = image;

        // Parse box IDs safely and select options
        let selectedIds = [];
        try {
            selectedIds = JSON.parse(boxIdsJson);
            if (!Array.isArray(selectedIds)) selectedIds = [];
        } catch {
            selectedIds = [];
        }

        // Select the relevant options in the multi-select
        const boxSelect = this.querySelector('#editBoxSelect');
        Array.from(boxSelect.options).forEach(option => {
            option.selected = selectedIds.includes(Number(option.value));
        });
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
function deleteCategory(id) {
    Swal.fire({
        title: '<div style="display:flex;align-items:center;"><div style="background:linear-gradient(135deg,#ffc107 0%,#ffecb3 100%);color:#856404;border-radius:50%;width:40px;height:40px;display:flex;align-items:center;justify-content:center;margin-right:12px;font-size:1.5rem;"><i class="bi bi-trash"></i></div><div><div style="font-weight:700;font-size:1.1rem;color:#856404;">Delete Category?</div><div style="font-size:0.95rem;opacity:0.85;color:#856404;">Are you sure you want to delete this category? This action cannot be undone.</div></div></div>',
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
                title: '<div style="display:flex;align-items:center;"><div style="background:linear-gradient(135deg,#28a745 0%,#20c997 100%);color:white;border-radius:50%;width:40px;height:40px;display:flex;align-items:center;justify-content:center;margin-right:12px;font-size:1.5rem;"><i class="bi bi-check-circle"></i></div><div><div style="font-weight:700;font-size:1.1rem;">Deleted!</div><div style="font-size:0.95rem;opacity:0.85;">Category deleted successfully.</div></div></div>',
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
                window.location.href = 'inc/delete_category?id=' + encodeURIComponent(id);
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
                    'category_name',
                    'date'
                ],
                searchColumns: ['category_name'],
                page: 10,
                pagination: true,
                listClass: 'list',
                searchClass: 'search'
            };

            var categoryList = new List('categoryList', options);

            categoryList.on('updated', function (list) {
                var isEmpty = list.matchingItems.length === 0;
                var noresultEl = document.querySelector('.noresult');
                
                if (noresultEl) {
                    noresultEl.style.display = isEmpty ? 'block' : 'none';
                }
            });
        });
    </script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>




</html>

