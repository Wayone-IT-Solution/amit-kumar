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

    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />

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
require_once('../inc/db.php');

// ✅ Get all categories
$categories = $conn->query("SELECT id, title FROM categories ORDER BY title")->fetchAll(PDO::FETCH_ASSOC);

// ✅ Get selected category ID from GET param
$selectedCategoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

// ✅ Get subcategories for selected category
$subcategories = [];
if ($selectedCategoryId > 0) {
    $stmt = $conn->prepare("SELECT id, title FROM subcategories WHERE category_id = ?");
    $stmt->execute([$selectedCategoryId]);
    $subcategories = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$success = '';
$error = '';

// ✅ Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!isset($_FILES['product_image']) || $_FILES['product_image']['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Product image is required.');
        }

        $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
        $imageTmp = $_FILES['product_image']['tmp_name'];
        $imageName = basename($_FILES['product_image']['name']);
        $imageExt = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));

        if (!in_array($imageExt, $allowedExts)) {
            throw new RuntimeException('Invalid image format.');
        }

        $safeImageName = uniqid('prod_', true) . '.' . $imageExt;
        $uploadDir = 'uploads/products/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fullImagePath = $uploadDir . $safeImageName;
        if (!move_uploaded_file($imageTmp, $fullImagePath)) {
            throw new RuntimeException('Failed to upload image.');
        }

        // Form values
        $category_id    = (int)$_POST['category_id'];
        $subcategory_id = (int)$_POST['subcategory_id'];
        $name           = trim($_POST['name']);
        $description    = trim($_POST['description']);
        $weight_type    = $_POST['weight_type'];
        $weight         = trim($_POST['weight']);
        $price          = trim($_POST['price']);
        $discount_price = trim($_POST['discount_price']);
        $min_order      = (int)$_POST['min_order'];
        $max_order      = (int)$_POST['max_order'];
        $tags           = trim($_POST['tags']);
        // Fix for implode error: handle both array and string for 'type'
        if (isset($_POST['type'])) {
            if (is_array($_POST['type'])) {
                $types = implode(',', $_POST['type']);
            } else {
                $types = $_POST['type'];
            }
        } else {
            $types = '';
        }

        if ($category_id <= 0 || $subcategory_id <= 0 || empty($name) || empty($price)) {
            throw new RuntimeException('Please fill all required fields.');
        }

        // Generate product code
        $last = $conn->query("SELECT product_code FROM products ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        $nextNumber = ($last && preg_match('/^#P(\d{4})$/', $last['product_code'], $matches)) ? ((int)$matches[1] + 1) : 1;
        $productCode = '#P' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        $stmt = $conn->prepare("INSERT INTO products 
            (product_code, category_id, subcategory_id, name, description, weight, weight_type, price, tags, discount_price, min_order, max_order, product_image, types)
            VALUES (:pid, :cat, :subcat, :name, :desc, :weight, :wtype, :price, :tags, :d_price, :min, :max, :img, :types)");
        $stmt->execute([
            ':pid'      => $productCode,
            ':cat'      => $category_id,
            ':subcat'   => $subcategory_id,
            ':name'     => $name,
            ':desc'     => $description,
            ':weight'   => $weight,
            ':wtype'    => $weight_type,
            ':price'    => $price,
            ':tags'     => $tags,
            ':d_price'  => $discount_price ?: null,
            ':min'      => $min_order ?: null,
            ':max'      => $max_order ?: null,
            ':img'      => $fullImagePath,
            ':types'    => $types
        ]);

        $success = "✅ Product added successfully with ID $productCode.";
    } catch (Throwable $e) {
        $error = "❌ Error: " . $e->getMessage();
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
                <h4 class="card-title mb-0 flex-grow-1">Add Product</h4>
            </div>
            <div class="card-body">
                <div class="live-preview">
                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <!-- Category -->
                            <div class="col-md-6 mb-3">
                                <label>Select Category</label>
                                <select name="category_id" id="categorySelect" class="form-select" required onchange="location.href='?category_id=' + this.value;">
                                    <option value="">-- Select Category --</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>" <?= ($cat['id'] == $selectedCategoryId) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat['title']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Subcategory -->
                            <div class="col-md-6 mb-3">
                                <label>Select Subcategory</label>
                                <select name="subcategory_id" class="form-select" required>
                                    <option value="">-- Select Subcategory --</option>
                                    <?php foreach ($subcategories as $sub): ?>
                                        <option value="<?= $sub['id'] ?>"><?= htmlspecialchars($sub['title']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="nameInput" class="form-label">Product Name</label>
                                <input type="text" name="name" class="form-control" id="nameInput" placeholder="Enter product name" required>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="descInput" class="form-label">Description</label>
                                <textarea name="description" id="descInput" rows="4" class="form-control" placeholder="Enter product description" required></textarea>
                            </div>

                            <!-- Weight & Type -->
                            <div class="col-md-3 mb-3">
                                <label for="weightInput" class="form-label">Quantity</label>
                                <input type="text" name="weight" class="form-control" id="weightInput" placeholder="e.g. 250, 1" required>
                            </div>

                            <!-- Restore original Unit Dropdown -->
                            <div class="col-md-3 mb-3">
                                <label for="weightType" class="form-label">Unit</label>
                                <select name="weight_type" class="form-select" id="weightType" required>
                                    <option value="">-- Select Unit --</option>
                                    <option value="g">Gram (g)</option>
                                    <option value="kg">Kilogram (Kg)</option>
                                    <option value="unit">Unit</option>
                                </select>
                            </div>

                            <!-- Type Checkbox Dropdown (improved design) -->
                            
                        <div class="col-md-3 mb-3">
                                <label for="typeDropdownBtn" class="form-label">Type</label>
                                
                    <div class="dropdown">
                        
                        <button class="btn btn-light dropdown-toggle w-100" type="button" id="typeDropdownBtn" data-bs-toggle="dropdown" aria-expanded="false">
                        Select Types
                        </button>
                  

                        <ul class="dropdown-menu w-100 p-2" aria-labelledby="typeDropdownBtn" id="typeDropdownMenu">
                        <li><label class="form-check"><input class="form-check-input type-checkbox" type="checkbox" value="250"> 250 gram</label></li>
                        <li><label class="form-check"><input class="form-check-input type-checkbox" type="checkbox" value="500"> 500 gram</label></li>
                        <li><label class="form-check"><input class="form-check-input type-checkbox" type="checkbox" value="750"> 750 gram</label></li>
                        <li><label class="form-check"><input class="form-check-input type-checkbox" type="checkbox" value="1"> 1000 gram</label></li>

                        </ul>
                                                     <small class="text-muted">Select  All  types.</small>

                        <input type="hidden" name="type" id="typeHiddenInput">
                    </div>
                    </div>

                            <div class="col-md-6 mb-3">
                                <label for="priceInput" class="form-label">Price (₹)</label>
                                <input type="number" name="price" class="form-control" id="priceInput" placeholder="Enter product price" step="0.01" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="discountPriceInput" class="form-label">Discounted Price (₹)</label>
                                <input type="number" name="discount_price" class="form-control" id="discountPriceInput" placeholder="Enter discounted price (if any)" step="0.01" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="minOrderInput" class="form-label">Minimum Order Quantity</label>
                                <input type="number" name="min_order" class="form-control" id="minOrderInput" placeholder="Enter min order quantity" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="maxOrderInput" class="form-label">Maximum Order Quantity</label>
                                <input type="number" name="max_order" class="form-control" id="maxOrderInput" placeholder="Enter max order quantity" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="tags" class="form-label">Tags</label>
                                <select name="tags" id="tags" class="form-select" required>
                                    <option value="">-- Select Tags --</option>
                                    <option value="hot">Hot</option>
                                    <option value="new_arrival">New Arrival</option>
                                    <option value="featured">Featured</option>
                                </select>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="productImageInput" class="form-label">Product Image</label>
                                <input type="file" name="product_image" class="form-control" id="productImageInput" accept="image/*" required>
                            </div>

                            <div class="col-lg-12">
                                <div class="text-end">
                                    <button type="submit" class="btn btn-primary">Add Product</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div> <!-- live-preview -->
            </div> <!-- card-body -->
        </div>
    </div>
</div>
<?php

$stmt = $conn->prepare("
      SELECT 
        p.id, 
        p.product_code,
        p.name AS product_name, 
        p.product_image, 
        p.weight_type,
        p.weight, 
        p.price, 
        p.discount_price,
        p.min_order,
        p.max_order,
        p.created_at,
        p.best_seller, 
        p.specialities,
        p.status, 
        p.tags,   
        p.description,
        c.title AS category_title,
        s.title AS subcategory_title
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN subcategories s ON p.subcategory_id = s.id
    ORDER BY p.id DESC
");
$stmt->execute();
$products = $stmt->fetchAll();
$sn = 1;

?>


                   <div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Products</h4>
            </div>

            <div class="card-body">
                <div class="listjs-table" id="productList">
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
                        <table class="table align-middle table-nowrap" id="productTable">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th class="sort" data-sort="product_code">Product Code</th>
                                    <th class="sort" data-sort="image">Image</th>
                                    <th class="sort" data-sort="product_name">Product Name</th>
                                    <th class="sort" data-sort="weight_type">Weight type</th>
                                    <th class="sort" data-sort="product_name">Subcategory Name</th>
                                    <th class="sort" data-sort="category">Category</th>
                                    <th class="sort" data-sort="weight">Weight</th>
                                    <th class="sort" data-sort="price">Price</th>
                                    <th class="sort" data-sort="discount_price">Discount Price</th>
                                    <th class="sort" data-sort="min_order">Min Order</th>
                                    <th class="sort" data-sort="max_order">Max Order</th>
                                    <th class="sort" data-sort="tags">Tags</th>
                                    <th class="sort" data-sort="created">Created At</th>
                                    <th class="sort" data-sort="best_seller">Best Seller</th>
                                    <th class="sort" data-sort="specialities">Specialities</th>
                                    <th class="sort" data-sort="status">Status</th>
                                    <th class="sort" data-sort="action">Action</th>
                                </tr>
                            </thead>
                            <tbody class="list form-check-all">
                                <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?= $sn++; ?></td>
                                    <td class="product_code"><?= htmlspecialchars($product['product_code']) ?></td>
                                    <td><img src="<?= htmlspecialchars($product['product_image']) ?>" width="60" height="60" alt="Product Image"></td>
                                    <td class="product_name"><?= htmlspecialchars($product['product_name']) ?></td>
                                    <td class="weight_type"><?= htmlspecialchars($product['weight_type'] ?? '') ?></td>
                                    <td class="subcategory"><?= htmlspecialchars($product['subcategory_title']) ?></td>

                                    <td class="category"><?= htmlspecialchars($product['category_title']) ?></td>
                                    <td class="weight"><?= htmlspecialchars($product['weight']) ?></td>
                                    <td class="price">₹<?= htmlspecialchars($product['price']) ?></td>
                                    <td class="discount_price">₹<?= htmlspecialchars($product['discount_price'] ?? '-') ?></td>
                                    <td class="min_order"><?= htmlspecialchars($product['min_order'] ?? '-') ?></td>
                                    <td class="max_order"><?= htmlspecialchars($product['max_order'] ?? '-') ?></td>
                                    <td class="tags"><?= htmlspecialchars($product['tags'] ?? '-') ?></td>
                                    <td class="created"><?= date("d M, Y", strtotime($product['created_at'])) ?></td>
                                    <td class="best_seller">
    <?php if ($product['best_seller']): ?>
        <a href="inc/toggle_best_seller?id=<?= $product['id']; ?>&status=0" 
           class="badge bg-success"
           onclick="return confirm('Unset as best seller?');">Yes</a>
    <?php else: ?>
        <a href="inc/toggle_best_seller?id=<?= $product['id']; ?>&status=1" 
           class="badge bg-secondary"
           onclick="return confirm('Set as best seller?');">No</a>
    <?php endif; ?>
</td>
<td class="specialities">
    <?php if ($product['specialities']): ?>
        <a href="inc/toggle_specialities?id=<?= $product['id']; ?>&status=0" 
           class="badge bg-success"
           onclick="return confirm('Unset as speciality?');">Yes</a>
    <?php else: ?>
        <a href="inc/toggle_specialities?id=<?= $product['id']; ?>&status=1" 
           class="badge bg-secondary"
           onclick="return confirm('Set as speciality?');">No</a>
    <?php endif; ?>
</td>
<td class="status">
    <?php if ($product['status']): ?>
        <a href="inc/toggle_product_status?id=<?= $product['id']; ?>&status=0" 
           class="badge bg-success"
           onclick="return confirm('Unset as active?');">Active</a>
    <?php else: ?>
        <a href="inc/toggle_product_status?id=<?= $product['id']; ?>&status=1" 
           class="badge bg-danger"
           onclick="return confirm('Set as active?');">Inactive</a>
    <?php endif; ?>
</td>

                                    <td class="action-icons">
                  <i class="bx bx-edit icon-tooltip"
    title="Edit"
    style="color: #3B71CA; cursor: pointer;"
    data-bs-toggle="modal"
    data-bs-target="#editProductModal"
    data-id="<?= htmlspecialchars($product['id'] ?? '', ENT_QUOTES); ?>"
    data-name="<?= htmlspecialchars($product['product_name'] ?? '', ENT_QUOTES); ?>"
    data-category="<?= htmlspecialchars($product['category_title'] ?? '', ENT_QUOTES); ?>"
    data-image="<?= htmlspecialchars($product['product_image'] ?? '', ENT_QUOTES); ?>"
    data-weight="<?= htmlspecialchars($product['weight'] ?? '', ENT_QUOTES); ?>"
    data-price="<?= htmlspecialchars($product['price'] ?? '', ENT_QUOTES); ?>"
    data-tags="<?= htmlspecialchars($product['tags'] ?? '', ENT_QUOTES); ?>"
    data-discount_price="<?= htmlspecialchars($product['discount_price'] ?? '', ENT_QUOTES); ?>"
    data-min_order="<?= htmlspecialchars($product['min_order'] ?? '', ENT_QUOTES); ?>"
    data-max_order="<?= htmlspecialchars($product['max_order'] ?? '', ENT_QUOTES); ?>"
    data-description="<?= htmlspecialchars($product['description'] ?? '', ENT_QUOTES); ?>"
    data-types="<?= htmlspecialchars($product['types'] ?? '', ENT_QUOTES); ?>">
</i>


                                        <i class="bx bx-trash-alt icon-tooltip"
                                            title="Delete"
                                            style="color: #F44336; cursor: pointer;"
                                            onclick="deleteProduct(<?= $product['id']; ?>)">
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
                                <p class="text-muted mb-0">We couldn't find any products matching your search.</p>
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

<div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="inc/update_product" enctype="multipart/form-data">
      <input type="hidden" name="id" id="edit-product-id">
      <input type="hidden" name="old_image" id="edit-product_image">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Product</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">

          <div class="mb-3">
            <label>Product Name</label>
            <input type="text" name="name" id="edit-product-name" class="form-control" required>
          </div>

          <div class="mb-3">
            <label>Category</label>
            <select name="category_id" id="edit-product-category" class="form-control" required>
              <option value="">Select Category</option>
              <?php
              $stmt = $conn->prepare("SELECT id, title FROM categories ORDER BY title");
              $stmt->execute();
              $categories = $stmt->fetchAll();
              foreach ($categories as $cat):
              ?>
              <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['title']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label>Weight</label>
            <input type="text" name="weight" id="edit-product-weight" class="form-control" required>
          </div>

          <div class="mb-3">
            <label>Price</label>
            <input type="text" name="price" id="edit-product-price" class="form-control" required>
          </div>

          <div class="mb-3">
            <label>Discount Price</label>
            <input type="text" name="discount_price" id="edit-product-discount_price" class="form-control">
          </div>

          <div class="mb-3">
            <label>Min Order</label>
            <input type="number" name="min_order" id="edit-product-min_order" class="form-control">
          </div>

          <div class="mb-3">
            <label>Max Order</label>
            <input type="number" name="max_order" id="edit-product-max_order" class="form-control">
          </div>

          <div class="mb-3">
            <label>Tags</label>
            <select name="tags" id="edit-product-tags" class="form-select" required>
              <option value="">-- Select Tags --</option>
              <option value="hot">Hot</option>
              <option value="new_arrival">New Arrival</option>
              <option value="featured">Featured</option>
            </select>
          </div>
         
          <div class="mb-3">
            <label>Type</label>
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle w-100" type="button" id="editTypeDropdownBtn" data-bs-toggle="dropdown" aria-expanded="false">
                    Select Types
                </button>
                <ul class="dropdown-menu w-100 p-2" aria-labelledby="editTypeDropdownBtn" id="editTypeDropdownMenu">
                    <li><label class="form-check"><input class="form-check-input edit-type-checkbox" type="checkbox" value="500"> 500</label></li>
                    <li><label class="form-check"><input class="form-check-input edit-type-checkbox" type="checkbox" value="700"> 700</label></li>
                    <li><label class="form-check"><input class="form-check-input edit-type-checkbox" type="checkbox" value="250"> 250</label></li>
                </ul>
                <input type="hidden" name="type" id="editTypeHiddenInput">
            </div>
            <small class="text-muted">Select one or more types.</small>
          </div>

          <div class="mb-3">
            <label>Description</label>
            <textarea name="description" id="edit-product-description" class="form-control" required></textarea>
          </div>

          <div class="mb-3">
            <label>Product Image</label>
            <input type="file" name="product_image" class="form-control mb-2">
            <img id="preview-product-image" src="" alt="Current Image" style="max-width: 100px; border-radius: 6px;">
            <small class="d-block">Leave blank to keep the current image.</small>
          </div>

        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Update Product</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const modal = document.getElementById('editProductModal');
  modal.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;

    // Read data attributes
    const id = button.getAttribute('data-id');
    const name = button.getAttribute('data-name');
    const categoryTitle = button.getAttribute('data-category');
    const image = button.getAttribute('data-image');
    const weight = button.getAttribute('data-weight');
    const price = button.getAttribute('data-price');
    const discount_price = button.getAttribute('data-discount_price');
    const min_order = button.getAttribute('data-min_order');
    const max_order = button.getAttribute('data-max_order');
    const tags = button.getAttribute('data-tags');
    const description = button.getAttribute('data-description');
    const types = button.getAttribute('data-types') || '';
    const typeArr = types.split(',').map(t => t.trim()).filter(Boolean);

    // Populate modal fields
    document.getElementById('edit-product-id').value = id;
    document.getElementById('edit-product-name').value = name;
    document.getElementById('edit-product-weight').value = weight;
    document.getElementById('edit-product-price').value = price;
    document.getElementById('edit-product-discount_price').value = discount_price;
    document.getElementById('edit-product-min_order').value = min_order;
    document.getElementById('edit-product-max_order').value = max_order;
    document.getElementById('edit-product-tags').value = tags;
    document.getElementById('edit-product-description').value = description;
    document.getElementById('edit-product_image').value = image;
    document.getElementById('preview-product-image').src = image;

    // Match category dropdown by title
    const categorySelect = document.getElementById('edit-product-category');
    for (let option of categorySelect.options) {
      if (option.textContent.trim() === categoryTitle.trim()) {
        option.selected = true;
        break;
      }
    }

    // Uncheck all first
    editTypeCheckboxes.forEach(cb => { cb.checked = false; });
    // Check those present in typeArr
    editTypeCheckboxes.forEach(cb => {
      if (typeArr.includes(cb.value)) cb.checked = true;
    });
    updateEditTypeHiddenInput();
  });
});
</script>

<script>
// For Add Product
const typeCheckboxes = document.querySelectorAll('.type-checkbox');
const typeHiddenInput = document.getElementById('typeHiddenInput');
function updateTypeHiddenInput() {
    const checked = Array.from(typeCheckboxes).filter(cb => cb.checked).map(cb => cb.value);
    typeHiddenInput.value = checked.join(',');
}
typeCheckboxes.forEach(cb => cb.addEventListener('change', updateTypeHiddenInput));
// For Edit Product Modal
const editTypeCheckboxes = document.querySelectorAll('.edit-type-checkbox');
const editTypeHiddenInput = document.getElementById('editTypeHiddenInput');
function updateEditTypeHiddenInput() {
    const checked = Array.from(editTypeCheckboxes).filter(cb => cb.checked).map(cb => cb.value);
    editTypeHiddenInput.value = checked.join(',');
}
editTypeCheckboxes.forEach(cb => cb.addEventListener('change', updateEditTypeHiddenInput));
</script>

<script>
    function deleteProduct(id) {
        Swal.fire({
            title: '<div style="display:flex;align-items:center;"><div style="background:linear-gradient(135deg,#ffc107 0%,#ffecb3 100%);color:#856404;border-radius:50%;width:40px;height:40px;display:flex;align-items:center;justify-content:center;margin-right:12px;font-size:1.5rem;"><i class="bi bi-trash"></i></div><div><div style="font-weight:700;font-size:1.1rem;color:#856404;">Delete Product?</div><div style="font-size:0.95rem;opacity:0.85;color:#856404;">Are you sure you want to delete this product? This action cannot be undone.</div></div></div>',
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
                    title: '<div style="display:flex;align-items:center;"><div style="background:linear-gradient(135deg,#28a745 0%,#20c997 100%);color:white;border-radius:50%;width:40px;height:40px;display:flex;align-items:center;justify-content:center;margin-right:12px;font-size:1.5rem;"><i class="bi bi-check-circle"></i></div><div><div style="font-weight:700;font-size:1.1rem;">Deleted!</div><div style="font-size:0.95rem;opacity:0.85;">Product deleted successfully.</div></div></div>',
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
                    window.location.href = 'inc/delete_product.php?id=' + encodeURIComponent(id);
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

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Dashboard init -->
    <script src="assets/js/pages/dashboard-ecommerce.init.js"></script>

    <!-- App js -->
    <script src="assets/js/app.js"></script>

    <!-- List.js min js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/list.js/2.3.1/list.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var options = {
                valueNames: [
                    'product_name',
                    'product_code',
                    'category',
                    'weight',
                    'price',
                    'created',
                    'best_seller',
                    'specialities'
                ],
                searchColumns: ['product_name', 'product_code', 'category', 'weight', 'price', 'created', 'best_seller', 'specialities'],
                page: 10,
                pagination: true
            };

            var productList = new List('productList', options);

            // Update 'noresult' element visibility
            productList.on('updated', function (list) {
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