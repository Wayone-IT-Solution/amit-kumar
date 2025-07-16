<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: index");
    exit;
}

require_once '../inc/db.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $title = trim($_POST['title']);
                $description = trim($_POST['description']);
                $valid_days = (int)$_POST['valid_days'];
                $price = (float)$_POST['price'];
                $status = (int)$_POST['status'];
                
                // Handle image upload
                $image = '';
                if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                    $filename = $_FILES['image']['name'];
                    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                    
                    if (in_array($ext, $allowed)) {
                        $newname = 'subscription_' . time() . '.' . $ext;
                        $upload_path = 'uploads/' . $newname;
                        
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                            $image = $upload_path;
                        }
                    }
                }
                
                $stmt = $conn->prepare("INSERT INTO subscriptions (title, description, valid_days, price, image, status, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                if ($stmt->execute([$title, $description, $valid_days, $price, $image, $status])) {
                    $success_msg = "Subscription plan added successfully!";
                } else {
                    $error_msg = "Error adding subscription plan.";
                }
                break;
                
            case 'edit':
                $id = (int)$_POST['id'];
                $title = trim($_POST['title']);
                $description = trim($_POST['description']);
                $valid_days = (int)$_POST['valid_days'];
                $price = (float)$_POST['price'];
                $status = (int)$_POST['status'];
                
                // Handle image upload
                $image = $_POST['current_image'];
                if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                    $filename = $_FILES['image']['name'];
                    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                    
                    if (in_array($ext, $allowed)) {
                        $newname = 'subscription_' . time() . '.' . $ext;
                        $upload_path = 'uploads/' . $newname;
                        
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                            // Delete old image
                            if (!empty($_POST['current_image']) && file_exists($_POST['current_image'])) {
                                unlink($_POST['current_image']);
                            }
                            $image = $upload_path;
                        }
                    }
                }
                
                $stmt = $conn->prepare("UPDATE subscriptions SET title = ?, description = ?, valid_days = ?, price = ?, image = ?, status = ? WHERE id = ?");
                if ($stmt->execute([$title, $description, $valid_days, $price, $image, $status, $id])) {
                    $success_msg = "Subscription plan updated successfully!";
                } else {
                    $error_msg = "Error updating subscription plan.";
                }
                break;
                
            case 'delete':
                $id = (int)$_POST['id'];
                
                // Get image path before deletion
                $stmt = $conn->prepare("SELECT image FROM subscriptions WHERE id = ?");
                $stmt->execute([$id]);
                $image_path = $stmt->fetchColumn();
                
                $stmt = $conn->prepare("DELETE FROM subscriptions WHERE id = ?");
                if ($stmt->execute([$id])) {
                    // Delete image file
                    if (!empty($image_path) && file_exists($image_path)) {
                        unlink($image_path);
                    }
                    $success_msg = "Subscription plan deleted successfully!";
                } else {
                    $error_msg = "Error deleting subscription plan.";
                }
                break;
        }
    }
}

// Fetch all subscriptions
$stmt = $conn->query("SELECT id, title, description, valid_days, price, image, status, created_at FROM subscriptions ORDER BY created_at DESC");
$subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate statistics
$total_subscriptions = count($subscriptions);
$active_subscriptions = count(array_filter($subscriptions, function($s) { return $s['status'] == 1; }));
$total_value = array_sum(array_column($subscriptions, 'price'));
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
        th{
            font-size: 12px;
        }
        td{
            font-size: 12px;
        }
        
        .subscription-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #e9ecef;
        }
        
        .plan-details {
            max-width: 200px;
        }
        
        .plan-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 2px;
        }
        
        .plan-subtitle {
            font-size: 11px;
            color: #666;
        }
        
        .description-cell {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .description-cell:hover {
            white-space: normal;
            overflow: visible;
            position: relative;
            z-index: 10;
        }
        
        .validity-badge {
            background: #e3f2fd;
            color: #1976d2;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }
        
        .price-display {
            background: #e8f5e8;
            color: #2e7d32;
            padding: 4px 8px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 12px;
        }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }
        
        .status-active {
            background: #d4edda;
            color: #155724;
        }
        
        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }
        
        .date-display {
            background: #f8f9fa;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            color: #495057;
        }
        
        .action-btn {
            padding: 4px 8px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 11px;
            margin: 1px;
        }
        
        .btn-edit {
            background: #007bff;
            color: white;
        }
        
        .btn-edit:hover {
            background: #0056b3;
            color: white;
        }
        
        .btn-delete {
            background: #dc3545;
            color: white;
        }
        
        .btn-delete:hover {
            background: #c82333;
            color: white;
        }
        
        .btn-add {
            background: #28a745;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 600;
        }
        
        .btn-add:hover {
            background: #218838;
            color: white;
        }
    </style>
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
                                        <h4 class="mb-sm-0">Subscription Plans</h4>

                                        <div class="page-title-right">
                                            <ol class="breadcrumb m-0">
                                                <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a>
                                                </li>
                                                <li class="breadcrumb-item active">Subscription Plans</li>
                                            </ol>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <!-- end page title -->

                            <!-- Alert messages -->
                            <?php if (isset($success_msg)): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="bi bi-check-circle me-2"></i><?= $success_msg ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (isset($error_msg)): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="bi bi-exclamation-triangle me-2"></i><?= $error_msg ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="card">
                                       <div class="card-header d-flex justify-content-between align-items-center">
                                        <h4 class="card-title mb-0">All Subscription Plans</h4>
                                        <button class="btn btn-add" data-bs-toggle="modal" data-bs-target="#addSubscriptionModal">
                                            <i class="bi bi-plus-circle me-2"></i>Add New Plan
                                        </button>
                                        </div>
                                            <!-- end card header -->

                                        <div class="card-body">
                                            <div class="listjs-table" id="subscriptionList">
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
                                                    <table class="table align-middle table-nowrap" id="subscriptionTable">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>#</th>
                                                                <th class="sort" data-sort="image">Image</th>
                                                                <th class="sort" data-sort="title">Plan Title</th>
                                                                <th class="sort" data-sort="description">Description</th>
                                                                <th class="sort" data-sort="validity">Validity</th>
                                                                <th class="sort" data-sort="price">Price</th>
                                                                <th class="sort" data-sort="status">Status</th>
                                                                <th class="sort" data-sort="created">Created Date</th>
                                                                <th>Actions</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="list form-check-all">
                                                            <?php if (!empty($subscriptions)): ?>
                                                                <?php foreach ($subscriptions as $index => $sub): ?>
                                                                    <tr>
                                                                        <td><?= $index + 1 ?></td>
                                                                        <td class="image">
                                                                            <img src="<?= !empty($sub['image']) ? $sub['image'] : 'assets/images/no-image.png' ?>" 
                                                                                 alt="<?= htmlspecialchars($sub['title']) ?>" 
                                                                                 class="subscription-image">
                                                                        </td>
                                                                        <td class="title">
                                                                            <div class="plan-details">
                                                                                <div class="plan-title"><?= htmlspecialchars($sub['title']) ?></div>
                                                                                <div class="plan-subtitle">ID: #<?= $sub['id'] ?></div>
                                                                            </div>
                                                                        </td>
                                                                        <td class="description">
                                                                            <div class="description-cell" title="<?= htmlspecialchars($sub['description']) ?>">
                                                                                <?= !empty($sub['description']) ? htmlspecialchars($sub['description']) : 'No description' ?>
                                                                            </div>
                                                                        </td>
                                                                        <td class="validity">
                                                                            <span class="validity-badge">
                                                                                <i class="bi bi-calendar me-1"></i>
                                                                                <?= $sub['valid_days'] ?> Days
                                                                            </span>
                                                                        </td>
                                                                        <td class="price">
                                                                            <span class="price-display">
                                                                                <i class="bi bi-currency-rupee me-1"></i>
                                                                                <?= number_format($sub['price'], 2) ?>
                                                                            </span>
                                                                        </td>
                                                                        <td class="status">
                                                                            <span class="status-badge <?= $sub['status'] ? 'status-active' : 'status-inactive' ?>">
                                                                                <i class="bi bi-<?= $sub['status'] ? 'check-circle' : 'x-circle' ?> me-1"></i>
                                                                                <?= $sub['status'] ? 'Active' : 'Inactive' ?>
                                                                            </span>
                                                                        </td>
                                                                        <td class="created">
                                                                            <span class="date-display">
                                                                                <i class="bi bi-calendar3 me-1"></i>
                                                                                <?= date('d M Y', strtotime($sub['created_at'])) ?>
                                                                            </span>
                                                                        </td>
                                                                        <td>
                                                                            <button class="action-btn btn-edit" onclick="editSubscription(<?= $sub['id'] ?>)">
                                                                                <i class="bi bi-pencil me-1"></i>Edit
                                                                            </button>
                                                                            <button class="action-btn btn-delete" onclick="deleteSubscription(<?= $sub['id'] ?>, '<?= htmlspecialchars($sub['title']) ?>')">
                                                                                <i class="bi bi-trash me-1"></i>Delete
                                                                            </button>
                                                                        </td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            <?php else: ?>
                                                                <tr>
                                                                    <td colspan="9">
                                                                        <div class="text-center py-4">
                                                                            <i class="bi bi-inbox" style="font-size: 3rem; color: #6c757d;"></i>
                                                                            <h5 class="mt-3">No Subscription Plans Found</h5>
                                                                            <p class="text-muted">Start by adding your first subscription plan.</p>
                                                                            <button class="btn btn-add" data-bs-toggle="modal" data-bs-target="#addSubscriptionModal">
                                                                                <i class="bi bi-plus-circle me-2"></i>Add First Plan
                                                                            </button>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            <?php endif; ?>
                                                        </tbody>
                                                    </table>

                                                    <div class="noresult" style="display: none">
                                                        <div class="text-center">
                                                            <lord-icon src="https://cdn.lordicon.com/msoeawqm.json"
                                                                       trigger="loop"
                                                                       colors="primary:#121331,secondary:#08a88a"
                                                                       style="width:75px;height:75px"></lord-icon>
                                                            <h5 class="mt-2">Sorry! No Result Found</h5>
                                                            <p class="text-muted mb-0">We didn't find any subscription plans matching your search.</p>
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
                                    </div><!-- end card -->
                                </div><!-- end col -->
                            </div><!-- end row -->

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

    <!-- Add Subscription Modal -->
    <div class="modal fade" id="addSubscriptionModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Subscription Plan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">Plan Title *</label>
                                    <input type="text" class="form-control" name="title" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" name="description" rows="3" placeholder="Enter plan description..."></textarea>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Validity (Days) *</label>
                                            <input type="number" class="form-control" name="valid_days" min="1" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Price (₹) *</label>
                                            <input type="number" class="form-control" name="price" min="0" step="0.01" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select class="form-control" name="status">
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Plan Image</label>
                                    <input type="file" class="form-control" name="image" accept="image/*">
                                    <small class="text-muted">Recommended: 400x300px, JPG/PNG/WEBP</small>
                                </div>
                                
                                <div class="text-center">
                                    <div class="preview-image mt-3" style="display: none;">
                                        <img id="imagePreview" src="" alt="Preview" style="max-width: 100%; height: 150px; object-fit: cover; border-radius: 8px;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                           <button type="submit" class="btn btn-primary">Add Plan</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                     
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Edit Subscription Modal -->
    <div class="modal fade" id="editSubscriptionModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Subscription Plan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    <input type="hidden" name="current_image" id="edit_current_image">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">Plan Title *</label>
                                    <input type="text" class="form-control" name="title" id="edit_title" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" name="description" id="edit_description" rows="3"></textarea>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Validity (Days) *</label>
                                            <input type="number" class="form-control" name="valid_days" id="edit_valid_days" min="1" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Price (₹) *</label>
                                            <input type="number" class="form-control" name="price" id="edit_price" min="0" step="0.01" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select class="form-control" name="status" id="edit_status">
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Plan Image</label>
                                    <input type="file" class="form-control" name="image" accept="image/*">
                                    <small class="text-muted">Leave empty to keep current image</small>
                                </div>
                                
                                <div class="text-center">
                                    <div class="current-image mt-3">
                                        <img id="editImagePreview" src="" alt="Current Image" style="max-width: 100%; height: 150px; object-fit: cover; border-radius: 8px;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Update Plan</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the subscription plan "<span id="deletePlanName"></span>"?</p>
                    <p class="text-danger"><small>This action cannot be undone.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="delete_id">
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/list.js/2.3.1/list.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var options = {
                valueNames: [
                    'title',
                    'description',
                    'validity',
                    'price',
                    'status',
                    'created'
                ],
                listClass: 'list',
                searchClass: 'search',
                page: 10,
                pagination: true
            };

            var subscriptionList = new List('subscriptionList', options);

            // Update 'noresult' element visibility
            subscriptionList.on('updated', function (list) {
                var isEmpty = list.matchingItems.length === 0;
                var noresultEl = document.querySelector('.noresult');
                
                if (noresultEl) {
                    noresultEl.style.display = isEmpty ? 'block' : 'none';
                }
            });
        });

        // Image preview for add modal
        document.querySelector('input[name="image"]').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('imagePreview').src = e.target.result;
                    document.querySelector('.preview-image').style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });
        
        // Edit subscription function
        function editSubscription(id) {
            // Fetch subscription data via AJAX
            fetch(`get_subscription.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('edit_id').value = data.id;
                    document.getElementById('edit_title').value = data.title;
                    document.getElementById('edit_description').value = data.description;
                    document.getElementById('edit_valid_days').value = data.valid_days;
                    document.getElementById('edit_price').value = data.price;
                    document.getElementById('edit_status').value = data.status;
                    document.getElementById('edit_current_image').value = data.image;
                    document.getElementById('editImagePreview').src = data.image || 'assets/images/no-image.png';
                    
                    new bootstrap.Modal(document.getElementById('editSubscriptionModal')).show();
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error', 'Failed to load subscription data', 'error');
                });
        }
        
        // Delete subscription function
        function deleteSubscription(id, name) {
            document.getElementById('delete_id').value = id;
            document.getElementById('deletePlanName').textContent = name;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
        
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>
