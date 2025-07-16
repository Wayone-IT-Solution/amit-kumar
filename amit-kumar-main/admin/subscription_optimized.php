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

// Fetch all subscriptions with optimized query
$stmt = $conn->query("SELECT id, title, description, valid_days, price, image, status, created_at FROM subscriptions ORDER BY created_at DESC");
$subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate statistics
$total_subscriptions = count($subscriptions);
$active_subscriptions = count(array_filter($subscriptions, function($s) { return $s['status'] == 1; }));
$total_value = array_sum(array_column($subscriptions, 'price'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Manage Subscriptions - Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Admin Dashboard" name="description">
    
    <!-- App favicon -->
    <link rel="shortcut icon" href="assets/images/logo.webp">
    
    <!-- Bootstrap Css -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <!-- Icons Css -->
    <link href="assets/css/icons.min.css" rel="stylesheet" type="text/css">
    <!-- App Css-->
    <link href="assets/css/app.min.css" rel="stylesheet" type="text/css">
    
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    
    <style>
        /* Optimized CSS - Minimal and Fast */
        .page-header {
            background: #D6B669;
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .stats-card {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .stats-item {
            display: inline-block;
            background: #D6B669;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            margin: 5px;
            font-size: 14px;
            font-weight: 600;
        }
        
        .table-container {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .table-header {
            background: #D6B669;
            color: white;
            padding: 15px 20px;
            font-weight: 600;
        }
        
        .subscription-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .subscription-table th {
            background: #f8f9fa;
            padding: 12px;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            text-align: left;
        }
        
        .subscription-table td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
            vertical-align: middle;
        }
        
        .subscription-table tr:hover {
            background: #f8f9fa;
        }
        
        .subscription-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 6px;
        }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
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
        
        .price-tag {
            background: #D6B669;
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-weight: 600;
        }
        
        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            margin: 2px;
        }
        
        .btn-edit {
            background: #007bff;
            color: white;
        }
        
        .btn-delete {
            background: #dc3545;
            color: white;
        }
        
        .btn-add {
            background: #28a745;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
        }
        
        .modal-content {
            border-radius: 8px;
        }
        
        .form-control {
            border-radius: 6px;
        }
        
        /* Remove heavy animations and effects */
        * {
            transition: none !important;
            animation: none !important;
        }
    </style>
</head>

<body>
    <div class="page-wrapper-img">
        <div class="page-wrapper-img-inner">
            <div class="sidebar-content">
                <?php include 'inc/sidebar.php'; ?>
            </div>
            <div class="page-content">
                <?php include 'inc/header.php'; ?>
                
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="dashboard">Dashboard</a></li>
                                        <li class="breadcrumb-item active">Manage Subscriptions</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">Manage Subscriptions</h4>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics -->
                    <div class="stats-card">
                        <div class="stats-item">
                            <i class="mdi mdi-package-variant"></i> Total: <?= $total_subscriptions ?>
                        </div>
                        <div class="stats-item">
                            <i class="mdi mdi-check-circle"></i> Active: <?= $active_subscriptions ?>
                        </div>
                        <div class="stats-item">
                            <i class="mdi mdi-currency-inr"></i> Value: ₹<?= number_format($total_value, 2) ?>
                        </div>
                    </div>

                    <!-- Add New Subscription Button -->
                    <div class="mb-3">
                        <button type="button" class="btn-add" data-bs-toggle="modal" data-bs-target="#addSubscriptionModal">
                            <i class="mdi mdi-plus"></i> Add New Subscription
                        </button>
                    </div>

                    <!-- Subscriptions Table -->
                    <div class="table-container">
                        <div class="table-header">
                            <h5 class="mb-0">Subscription Plans</h5>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="subscription-table">
                                <thead>
                                    <tr>
                                        <th>Image</th>
                                        <th>Title</th>
                                        <th>Description</th>
                                        <th>Valid Days</th>
                                        <th>Price</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($subscriptions)): ?>
                                        <?php foreach ($subscriptions as $subscription): ?>
                                            <tr>
                                                <td>
                                                    <?php if (!empty($subscription['image'])): ?>
                                                        <img src="<?= htmlspecialchars($subscription['image']) ?>" alt="Subscription" class="subscription-image">
                                                    <?php else: ?>
                                                        <div class="subscription-image bg-light d-flex align-items-center justify-content-center">
                                                            <i class="mdi mdi-package-variant text-muted"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <strong><?= htmlspecialchars($subscription['title']) ?></strong>
                                                </td>
                                                <td>
                                                    <div class="text-muted" style="max-width: 200px;">
                                                        <?= htmlspecialchars(substr($subscription['description'], 0, 100)) ?>
                                                        <?= strlen($subscription['description']) > 100 ? '...' : '' ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary"><?= $subscription['valid_days'] ?> Days</span>
                                                </td>
                                                <td>
                                                    <span class="price-tag">₹<?= number_format($subscription['price'], 2) ?></span>
                                                </td>
                                                <td>
                                                    <span class="status-badge <?= $subscription['status'] == 1 ? 'status-active' : 'status-inactive' ?>">
                                                        <?= $subscription['status'] == 1 ? 'Active' : 'Inactive' ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <small><?= date('M d, Y', strtotime($subscription['created_at'])) ?></small>
                                                </td>
                                                <td>
                                                    <button type="button" class="action-btn btn-edit" onclick="editSubscription(<?= $subscription['id'] ?>)">
                                                        <i class="mdi mdi-pencil"></i> Edit
                                                    </button>
                                                    <button type="button" class="action-btn btn-delete" onclick="deleteSubscription(<?= $subscription['id'] ?>)">
                                                        <i class="mdi mdi-delete"></i> Delete
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center py-4">
                                                <i class="mdi mdi-package-variant-remove text-muted" style="font-size: 3rem;"></i>
                                                <p class="mt-2 text-muted">No subscription plans found</p>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Subscription Modal -->
    <div class="modal fade" id="addSubscriptionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Subscription</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="post" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Valid Days</label>
                            <input type="number" class="form-control" name="valid_days" value="30" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Price</label>
                            <input type="number" class="form-control" name="price" step="0.01" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Image</label>
                            <input type="file" class="form-control" name="image" accept="image/*">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-control" name="status">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Subscription</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Subscription Modal -->
    <div class="modal fade" id="editSubscriptionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Subscription</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="post" enctype="multipart/form-data" id="editForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" id="edit_id">
                        <input type="hidden" name="current_image" id="edit_current_image">
                        
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" name="title" id="edit_title" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" id="edit_description" rows="3"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Valid Days</label>
                            <input type="number" class="form-control" name="valid_days" id="edit_valid_days" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Price</label>
                            <input type="number" class="form-control" name="price" id="edit_price" step="0.01" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Image</label>
                            <input type="file" class="form-control" name="image" accept="image/*">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-control" name="status" id="edit_status">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Subscription</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Vendor js -->
    <script src="assets/js/vendor.min.js"></script>
    <!-- App js -->
    <script src="assets/js/app.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        // Optimized JavaScript - Minimal and Fast
        function editSubscription(id) {
            // Fetch subscription data and populate modal
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
                    
                    new bootstrap.Modal(document.getElementById('editSubscriptionModal')).show();
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error', 'Failed to load subscription data', 'error');
                });
        }

        function deleteSubscription(id) {
            Swal.fire({
                title: 'Delete Subscription?',
                text: 'This action cannot be undone!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.innerHTML = `
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="${id}">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        // Show success/error messages
        <?php if (isset($success_msg)): ?>
            Swal.fire('Success', '<?= $success_msg ?>', 'success');
        <?php endif; ?>

        <?php if (isset($error_msg)): ?>
            Swal.fire('Error', '<?= $error_msg ?>', 'error');
        <?php endif; ?>
    </script>
</body>
</html> 