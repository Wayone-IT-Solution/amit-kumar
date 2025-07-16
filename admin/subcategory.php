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
    <title>Admin - Subcategories</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
    <meta content="Themesbrand" name="author" />
    <link rel="shortcut icon" href="assets/images/logo.webp">
    <link href="assets/libs/jsvectormap/jsvectormap.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/libs/swiper/swiper-bundle.min.css" rel="stylesheet" type="text/css" />
    <script src="assets/js/layout.js"></script>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/custom.min.css" rel="stylesheet" type="text/css" />
</head>
<body>
    <div id="layout-wrapper">
        <?php include('inc/header.php'); ?>
        <div class="vertical-overlay"></div>
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
<?php
require_once('../inc/db.php');
$success = '';
$error = '';
$categories = [];
try {
    $stmt = $conn->query("SELECT id, title FROM categories WHERE status = 1 ORDER BY title ASC");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $error = "Failed to fetch categories.";
}
// Handle Add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add') {
    try {
        if (!isset($_FILES['subcategory_image']) || $_FILES['subcategory_image']['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Subcategory image is required.');
        }
        $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
        $imageTmp = $_FILES['subcategory_image']['tmp_name'];
        $imageName = basename($_FILES['subcategory_image']['name']);
        $imageExt = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));
        if (!in_array($imageExt, $allowedExts)) {
            throw new RuntimeException('Invalid image format. Only JPG, JPEG, PNG, WEBP allowed.');
        }
        $safeImageName = uniqid('subcat_', true) . '.' . $imageExt;
        $uploadDir = 'uploads/subcategories/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $fullImagePath = $uploadDir . $safeImageName;
        if (!move_uploaded_file($imageTmp, $fullImagePath)) {
            throw new RuntimeException('Failed to save uploaded image.');
        }
        $category_id = (int)($_POST['category_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $status = $_POST['status'] ?? 'active';
        if ($category_id <= 0) {
            throw new RuntimeException('Category is required.');
        }
        if (empty($title)) {
            throw new RuntimeException('Subcategory title is required.');
        }
        if (!in_array($status, ['active', 'inactive'])) {
            throw new RuntimeException('Invalid status.');
        }
        $stmt = $conn->prepare("INSERT INTO subcategories (category_id, title, subcategory_image, status) VALUES (:category_id, :title, :image, :status)");
        $stmt->execute([
            ':category_id' => $category_id,
            ':title' => $title,
            ':image' => $fullImagePath,
            ':status' => $status
        ]);
        $success = "Subcategory added successfully.";
    } catch (Throwable $e) {
        error_log('Subcategory submission failed: ' . $e->getMessage());
        $error = 'Error: ' . htmlspecialchars($e->getMessage());
    }
}
// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update') {
    try {
        $id = (int)($_POST['id'] ?? 0);
        $category_id = (int)($_POST['category_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $status = $_POST['status'] ?? 'active';
        if ($id <= 0) throw new RuntimeException('Invalid subcategory ID.');
        if ($category_id <= 0) throw new RuntimeException('Category is required.');
        if (empty($title)) throw new RuntimeException('Subcategory title is required.');
        if (!in_array($status, ['active', 'inactive'])) throw new RuntimeException('Invalid status.');
        // Get existing image
        $stmt = $conn->prepare("SELECT subcategory_image FROM subcategories WHERE id = ?");
        $stmt->execute([$id]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$existing) throw new RuntimeException('Subcategory not found.');
        $subcategory_image = $existing['subcategory_image'];
        // Handle new image
        if (!empty($_FILES['subcategory_image']['name'])) {
            $uploadDir = 'uploads/subcategories/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $imageName = basename($_FILES['subcategory_image']['name']);
            $imageExt = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));
            $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
            if (!in_array($imageExt, $allowedExts)) {
                throw new RuntimeException('Invalid image format.');
            }
            $safeImageName = uniqid('subcat_', true) . '.' . $imageExt;
            $fullImagePath = $uploadDir . $safeImageName;
            if (!move_uploaded_file($_FILES['subcategory_image']['tmp_name'], $fullImagePath)) {
                throw new RuntimeException('Failed to upload image.');
            }
            // Delete old image
            if (!empty($subcategory_image) && file_exists($subcategory_image) && is_file($subcategory_image)) {
                unlink($subcategory_image);
            }
            $subcategory_image = $fullImagePath;
        }
        $stmt = $conn->prepare("UPDATE subcategories SET category_id = :category_id, title = :title, subcategory_image = :image, status = :status WHERE id = :id");
        $stmt->execute([
            ':category_id' => $category_id,
            ':title' => $title,
            ':image' => $subcategory_image,
            ':status' => $status,
            ':id' => $id
        ]);
        $success = "Subcategory updated successfully.";
    } catch (Throwable $e) {
        error_log('Subcategory update failed: ' . $e->getMessage());
        $error = 'Error: ' . htmlspecialchars($e->getMessage());
    }
}
// Handle Delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    try {
        $id = (int)$_GET['delete'];
        $stmt = $conn->prepare("SELECT subcategory_image FROM subcategories WHERE id = ?");
        $stmt->execute([$id]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($existing && !empty($existing['subcategory_image']) && file_exists($existing['subcategory_image']) && is_file($existing['subcategory_image'])) {
            unlink($existing['subcategory_image']);
        }
        $stmt = $conn->prepare("DELETE FROM subcategories WHERE id = ?");
        $stmt->execute([$id]);
        $success = "Subcategory deleted successfully.";
    } catch (Throwable $e) {
        error_log('Subcategory delete failed: ' . $e->getMessage());
        $error = 'Error: ' . htmlspecialchars($e->getMessage());
    }
}
// Fetch all subcategories
$subcategories = [];
try {
    $stmt = $conn->query("SELECT s.*, c.title AS category_title FROM subcategories s JOIN categories c ON s.category_id = c.id ORDER BY s.id DESC");
    $subcategories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $error = "Failed to fetch subcategories.";
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
                <h4 class="card-title mb-0 flex-grow-1">Add Subcategory</h4>
            </div>
            <div class="card-body">
                <form action="" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="categorySelect" class="form-label">Select Category</label>
                                <select name="category_id" id="categorySelect" class="form-select" required>
                                    <option value="">-- Select Category --</option>
                                    <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['title']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="titleInput" class="form-label">Subcategory Title</label>
                                <input type="text" name="title" class="form-control" placeholder="Enter subcategory title" id="titleInput" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="subcategoryImageInput" class="form-label">Subcategory Image</label>
                                <input type="file" name="subcategory_image" class="form-control" id="subcategoryImageInput" accept="image/*" required>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select id="status" name="status" class="form-control" required style="width: 100%;">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">Add Subcategory</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="row mb-5">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Subcategories</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive table-card mt-3 mb-1">
                    <table class="table align-middle table-nowrap" id="subcategoryTable">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Category</th>
                                <th>Subcategory</th>
                                <th>Image</th>
                                <th>Status</th>
                                <th>Created Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $sn = 1; foreach ($subcategories as $subcat): ?>
                            <tr>
                                <td><?= $sn++; ?></td>
                                <td><?= htmlspecialchars($subcat['category_title']) ?></td>
                                <td><?= htmlspecialchars($subcat['title']) ?></td>
                                <td><img src="<?= htmlspecialchars($subcat['subcategory_image']) ?>" width="60" height="60" alt=""></td>
                                <td>
                                    <?php if ($subcat['status'] === 'active'): ?>
                                        <a href="?toggle_status=<?= $subcat['id'] ?>&status=inactive" class="badge bg-success" onclick="return confirm('Mark as Inactive?');">Active</a>
                                    <?php else: ?>
                                        <a href="?toggle_status=<?= $subcat['id'] ?>&status=active" class="badge bg-danger" onclick="return confirm('Mark as Active?');">Inactive</a>
                                    <?php endif; ?>
                                </td>
                                <td><?= date("d M, Y", strtotime($subcat['created_at'])) ?></td>
                                <td>
                                    <i class="bx bx-edit icon-tooltip" title="Edit" style="color: #3B71CA; cursor: pointer;" data-bs-toggle="modal" data-bs-target="#editSubcategoryModal" data-id="<?= $subcat['id']; ?>" data-category_id="<?= $subcat['category_id']; ?>" data-title="<?= htmlspecialchars($subcat['title'], ENT_QUOTES); ?>" data-status="<?= htmlspecialchars($subcat['status'], ENT_QUOTES); ?>" data-image="<?= htmlspecialchars($subcat['subcategory_image'], ENT_QUOTES); ?>"></i>
                                    <i class="bx bx-trash-alt icon-tooltip" title="Delete" style="color: #F44336; cursor: pointer;" onclick="if(confirm('Delete this subcategory?')) window.location='?delete=<?= $subcat['id']; ?>';"></i>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="editSubcategoryModal" tabindex="-1" aria-labelledby="editSubcategoryLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="" enctype="multipart/form-data">
      <input type="hidden" name="id" id="edit-subcat-id">
      <input type="hidden" name="action" value="update">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editSubcategoryLabel">Edit Subcategory</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="edit-categorySelect" class="form-label">Category</label>
            <select name="category_id" id="edit-categorySelect" class="form-select" required>
              <option value="">-- Select Category --</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id']; ?>"><?= htmlspecialchars($cat['title']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label for="edit-titleInput" class="form-label">Subcategory Title</label>
            <input type="text" name="title" id="edit-titleInput" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="edit-subcategoryImageInput" class="form-label">Subcategory Image</label>
            <input type="file" name="subcategory_image" id="edit-subcategoryImageInput" class="form-control mb-2" accept="image/*">
            <img id="preview-subcategory-image" src="" alt="Current Image" style="max-width: 100px; border-radius: 6px;">
            <small class="d-block">Leave blank to keep the current image.</small>
          </div>
          <div class="mb-3">
            <label for="edit-status" class="form-label">Status</label>
            <select name="status" id="edit-status" class="form-select" required>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Update Subcategory</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </div>
    </form>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const editModal = document.getElementById('editSubcategoryModal');
    editModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const id = button.getAttribute('data-id') || '';
        const category_id = button.getAttribute('data-category_id') || '';
        const title = button.getAttribute('data-title') || '';
        const status = button.getAttribute('data-status') || '';
        const image = button.getAttribute('data-image') || '';
        this.querySelector('#edit-subcat-id').value = id;
        this.querySelector('#edit-categorySelect').value = category_id;
        this.querySelector('#edit-titleInput').value = title;
        this.querySelector('#edit-status').value = status;
        this.querySelector('#preview-subcategory-image').src = image;
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
<script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/libs/simplebar/simplebar.min.js"></script>
<script src="assets/libs/node-waves/waves.min.js"></script>
<script src="assets/libs/feather-icons/feather.min.js"></script>
<script src="assets/js/pages/plugins/lord-icon-2.1.0.js"></script>
<script src="assets/js/plugins.js"></script>
<script src="assets/libs/apexcharts/apexcharts.min.js"></script>
<script src="assets/libs/jsvectormap/jsvectormap.min.js"></script>
<script src="assets/libs/jsvectormap/maps/world-merc.js"></script>
<script src="assets/libs/swiper/swiper-bundle.min.js"></script>
<script src="assets/js/pages/dashboard-ecommerce.init.js"></script>
<script src="assets/js/app.js"></script>
</body>
</html> 