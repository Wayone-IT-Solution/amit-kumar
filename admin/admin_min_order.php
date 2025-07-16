<?php
session_start();
require_once '../inc/db.php';


$success = '';
$error = '';
$editKey = $editValue = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'save';

    if ($action === 'delete') {
        $deleteKey = trim($_POST['delete_key']);
        if ($deleteKey !== '') {
            $stmt = $conn->prepare("DELETE FROM settings WHERE key_name = ?");
            $stmt->execute([$deleteKey]);
            $success = "🗑️ Setting '$deleteKey' deleted successfully.";
        }
    }

    if ($action === 'save') {
        $key = trim($_POST['key_name'] ?? '');
        $value = trim($_POST['value'] ?? '');
        // If key is empty, use 'min_order' as default
        if ($key === '') {
            $key = 'min_order';
        }
        if ($value === '') {
            $error = "Value is required.";
        } else {
            $stmt = $conn->prepare("SELECT COUNT(*) FROM settings WHERE key_name = ?");
            $stmt->execute([$key]);
            $exists = $stmt->fetchColumn();

            if ($exists) {
                $stmt = $conn->prepare("UPDATE settings SET value = ? WHERE key_name = ?");
                $stmt->execute([$value, $key]);
                $success = "✅ Updated setting '$key' successfully.";
            } else {
                $stmt = $conn->prepare("INSERT INTO settings (key_name, value) VALUES (?, ?)");
                $stmt->execute([$key, $value]);
                $success = "✅ Added new setting '$key'.";
            }
        }
    }
}

// Fetch all settings
$stmt = $conn->query("SELECT * FROM settings ORDER BY key_name ASC");
$settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Settings | Admin Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="assets/css/bootstrap.min.css" rel="stylesheet" />
    <link href="assets/css/icons.min.css" rel="stylesheet" />
    <link href="assets/css/app.min.css" rel="stylesheet" />
    <link href="assets/css/custom.min.css" rel="stylesheet" />
    <style>
        .fade-out {
            animation: fadeOut 1s ease-in-out 3s forwards;
        }
        @keyframes fadeOut {
            to { opacity: 0; visibility: hidden; }
        }
    </style>
</head>
<body>

<div id="layout-wrapper">
    <?php include 'inc/header.php'; ?>
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">

                <h4 class="mb-3">⚙️ Manage Rs Settings</h4>

                <?php if ($success): ?>
                    <div class="alert alert-success fade-out"><?= $success ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger fade-out"><?= $error ?></div>
                <?php endif; ?>

                <!-- Add / Edit Form -->
                <div class="card mb-4" style="margin-top: -10px;">
                    <div class="card-header"><strong id="form-title">➕ Add / Edit Setting</strong></div>
                    <div class="card-body">
                        <form method="POST" id="settingForm">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label">comment</label>
                                                                        <small class="text-muted">If left blank, will use <b>min_order</b> as key.</small>

                                    <input type="text" name="key_name" id="keyInput" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Value</label>
                                    <input type="text" name="value" id="valueInput" class="form-control" required>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100">Save</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Settings Table -->
                <div class="card">
                    <div class="card-header"><strong>📋 Rs Settings</strong></div>
                    <div class="card-body table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Key</th>
                                    <th>Value</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($settings as $i => $row): ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td><?= htmlspecialchars($row['key_name']) ?></td>
                                        <td><?= htmlspecialchars($row['value']) ?></td>
                                         <td class="d-flex gap-1">
                                    <!-- ✏️ Edit Button -->
                                    <button class="btn btn-sm btn-info edit-btn"
                                        data-key="<?= htmlspecialchars($row['key_name']) ?>"
                                        data-value="<?= htmlspecialchars($row['value']) ?>">
                                        ✏️ Edit
                                    </button>

                                    <!-- 🗑️ Delete Button Form -->
                               <form method="POST" class="delete-setting-form">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="delete_key" value="<?= htmlspecialchars($row['key_name']) ?>">
                                <button type="submit" class="btn btn-sm btn-danger">🗑️</button>
                            </form>

                                </td>

                                    </tr>
                                <?php endforeach; ?>
                                <?php if (count($settings) === 0): ?>
                                    <tr><td colspan="4" class="text-center">No settings found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div> <!-- container -->
        </div> <!-- page-content -->

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
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    document.querySelectorAll('.delete-setting-form').forEach(function(form) {
      form.addEventListener('submit', function(e) {
        e.preventDefault();
        Swal.fire({
          title: 'Are you sure?',
          text: 'This will permanently delete the setting.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#d33',
          cancelButtonColor: '#3085d6',
          confirmButtonText: 'Yes, delete it!',
          cancelButtonText: 'Cancel'
        }).then((result) => {
          if (result.isConfirmed) {
            form.submit();
          }
        });
      });
    });
    </script>

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
    // ✅ Handle edit button to populate form
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const key = this.dataset.key;
            const value = this.dataset.value;
            document.getElementById('keyInput').value = key;
            document.getElementById('valueInput').value = value;
            document.getElementById('form-title').innerText = '✏️ Edit Setting';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    });
</script>
</body>
</html>
