<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: index");
    exit;
}

require_once('../inc/db.php');

try {
    // ---------------- DELETE SLOT ----------------
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
        $stmt = $conn->prepare("DELETE FROM blocked_slots WHERE id = :id");
        $stmt->execute([':id' => intval($_POST['delete_id'])]);
        echo "<script>window.location.href = window.location.pathname;</script>";
        exit;
    }

    // ---------------- ADD SLOT ----------------
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['time_select'])) {
        $blocked_date = trim($_POST['blocked_date']) ?: null;
        $time_select  = $_POST['time_select'];

        $start_time = $end_time = null;

        if ($time_select === 'custom') {
            $sh = $_POST['custom_start_hour'] ?? '';
            $sm = $_POST['custom_start_minute'] ?? '';
            $sampm = $_POST['custom_start_ampm'] ?? '';

            $eh = $_POST['custom_end_hour'] ?? '';
            $em = $_POST['custom_end_minute'] ?? '';
            $eampm = $_POST['custom_end_ampm'] ?? '';

            if ($sh && $sm !== '' && $sampm && $eh && $em !== '' && $eampm) {
                $sh = intval($sh);
                $sm = intval($sm);
                $eh = intval($eh);
                $em = intval($em);

                $sh24 = ($sampm === 'PM' && $sh != 12) ? $sh + 12 : (($sampm === 'AM' && $sh == 12) ? 0 : $sh);
                $eh24 = ($eampm === 'PM' && $eh != 12) ? $eh + 12 : (($eampm === 'AM' && $eh == 12) ? 0 : $eh);

                $start_time = sprintf('%02d:%02d:00', $sh24, $sm);
                $end_time   = sprintf('%02d:%02d:00', $eh24, $em);
            }
        } elseif (strpos($time_select, '-') !== false) {
            // Predefined slot like "10:00-18:00"
            list($start_time, $end_time) = explode('-', $time_select);
            $start_time = trim($start_time) . ':00';
            $end_time   = trim($end_time) . ':00';
        }

        // ✅ At least one of date or time must be present
        if (!$blocked_date && !$start_time && !$end_time) {
            echo "<script>alert('Please enter at least date or time.'); window.history.back();</script>";
            exit;
        }

        // ✅ Duplicate check only if both date and time are provided
        if ($blocked_date && $start_time && $end_time) {
            $check = $conn->prepare("SELECT COUNT(*) FROM blocked_slots WHERE blocked_date = :date AND start_time = :start AND end_time = :end");
            $check->execute([
                ':date' => $blocked_date,
                ':start' => $start_time,
                ':end' => $end_time
            ]);

            if ($check->fetchColumn() > 0) {
                echo "<script>alert('This slot is already blocked.'); window.history.back();</script>";
                exit;
            }
        }

        // ✅ Insert with null allowed
        $stmt = $conn->prepare("INSERT INTO blocked_slots (blocked_date, start_time, end_time) VALUES (:date, :start, :end)");
        $stmt->execute([
            ':date' => $blocked_date,
            ':start' => $start_time,
            ':end' => $end_time
        ]);

        echo "<script>window.location.href = window.location.href;</script>";
        exit;
    }

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo "<script>alert('Database error occurred.');</script>";
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
<div class="vertical-overlay"></div>

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">

            <!-- Page Heading -->
            <div class="row">
                <div class="col-xxl-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <h4 class="card-title mb-0 flex-grow-1">Manage Time and Date</h4>
                        </div>
                    </div>
                </div>
            </div>

            <form method="POST" action="">
    <div class="mb-3">
        <label class="form-label">Date (optional)</label>
        <input type="date" name="blocked_date" class="form-control">
    </div>

 <!-- 📌 TIME SLOT DROPDOWN -->
<div class="mb-3">
    <label class="form-label">Time Slot</label>
    <select class="form-control" name="time_select" id="timeSelect">
        <option value="">-- Select Time Slot --</option>
        <option value="10:00-18:00">10 AM - 6 PM</option>
        <option value="11:00-21:00">11 AM - 9 PM</option>
        <option value="custom">Other (Enter Custom Time)</option>
    </select>
</div>

<!-- 📌 CUSTOM TIME FIELDS (Dropdowns for HH:MM AM/PM) -->
<div class="row d-none" id="customTimeDiv">
    <div class="col-md-4 mb-3">
        <label class="form-label">Start Time</label>
        <div class="d-flex gap-1">
            <select class="form-control" name="custom_start_hour">
                <?php for ($i = 1; $i <= 12; $i++): ?>
                    <option value="<?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>"><?= $i ?></option>
                <?php endfor; ?>
            </select>
            <select class="form-control" name="custom_start_minute">
                <option value="00">00</option>
                <option value="15">15</option>
                <option value="30">30</option>
                <option value="45">45</option>
            </select>
            <select class="form-control" name="custom_start_ampm">
                <option value="AM">AM</option>
                <option value="PM">PM</option>
            </select>
        </div>
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">End Time</label>
        <div class="d-flex gap-1">
            <select class="form-control" name="custom_end_hour">
                <?php for ($i = 1; $i <= 12; $i++): ?>
                    <option value="<?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>"><?= $i ?></option>
                <?php endfor; ?>
            </select>
            <select class="form-control" name="custom_end_minute">
                <option value="00">00</option>
                <option value="15">15</option>
                <option value="30">30</option>
                <option value="45">45</option>
            </select>
            <select class="form-control" name="custom_end_ampm">
                <option value="AM">AM</option>
                <option value="PM">PM</option>
            </select>
        </div>
    </div>
</div>
<script>
document.getElementById('timeSelect').addEventListener('change', function () {
    const selected = this.value;
    const customDiv = document.getElementById('customTimeDiv');
    
    if (selected === 'custom') {
        customDiv.classList.remove('d-none');
    } else {
        customDiv.classList.add('d-none');
    }
});
</script>



    <button type="submit" class="btn btn-danger" style="margin-bottom: 20px;">Save Change</button>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const timeSelect = document.getElementById('timeSelect');
    const customTimeDiv = document.getElementById('customTimeDiv');
    const customTimeInput = document.getElementById('customTimeInput');

    timeSelect.addEventListener('change', function () {
        if (this.value === 'custom') {
            customTimeDiv.classList.remove('d-none');
        } else {
            customTimeDiv.classList.add('d-none');
        }
    });
});
</script>

            <!-- Table Display -->
            <div class="row mb-5">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Time And Date</h4>
                        </div>

                        <div class="card-body">
                            <div class="listjs-table" id="categoryList">
                                <div class="row g-4 mb-3">
                                    <div class="col-sm d-flex justify-content-sm-end">
                                        <div class="search-box ms-2">
                                            <input type="text" id="slotSearchInput" class="form-control" placeholder="Search date or time...">
                                            <i class="ri-search-line search-icon"></i>
                                        </div>
                                    </div>
                                </div>

                                <div class="table-responsive table-card mt-3 mb-1">
                                <table class="table align-middle table-nowrap" id="categoryTable">
    <thead class="table-light">
        <tr>
            <th>#</th>
            <th>Date</th>
            <th>Time</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        require_once('../inc/db.php');

        // ✅ Update query to match new columns
        $stmt = $conn->query("SELECT * FROM blocked_slots ORDER BY blocked_date DESC, start_time ASC");
        $blockedSlots = $stmt->fetchAll();

        foreach ($blockedSlots as $index => $slot): ?>
            <tr>
                <td><?= $index + 1 ?></td>

                <td><?= $slot['blocked_date'] ?: '<i>N/A</i>' ?></td>

                <td>
                    <?php if (!empty($slot['start_time']) && !empty($slot['end_time'])): ?>
                        <?= date('h:i A', strtotime($slot['start_time'])) ?> - <?= date('h:i A', strtotime($slot['end_time'])) ?>
                    <?php else: ?>
                        <i>N/A</i>
                    <?php endif; ?>
                </td>

                <td>
                    <!-- Delete Form -->
                    <form method="POST" action="" style="display:inline;" class="delete-slot-form">
                        <input type="hidden" name="delete_id" value="<?= $slot['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

                                    <div class="noresult" style="display: none">
                                        <div class="text-center">
                                            <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop"
                                                colors="primary:#121331,secondary:#08a88a"
                                                style="width:75px;height:75px"></lord-icon>
                                            <h5 class="mt-2">Sorry! No Result Found</h5>
                                            <p class="text-muted mb-0">We couldn't find any date and time.</p>
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
    <script>
    // Simple search for slots table
    document.addEventListener('DOMContentLoaded', function () {
      const searchInput = document.getElementById('slotSearchInput');
      const table = document.getElementById('categoryTable');
      const rows = table.querySelectorAll('tbody tr');
      const noResult = document.querySelector('.noresult');

      searchInput.addEventListener('input', function () {
        const val = this.value.trim().toLowerCase();
        let anyVisible = false;
        rows.forEach(row => {
          const date = row.children[1]?.textContent.toLowerCase() || '';
          const time = row.children[2]?.textContent.toLowerCase() || '';
          if (date.includes(val) || time.includes(val)) {
            row.style.display = '';
            anyVisible = true;
          } else {
            row.style.display = 'none';
          }
        });
        if (noResult) noResult.style.display = anyVisible ? 'none' : 'block';
      });
    });
    </script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    document.querySelectorAll('.delete-slot-form').forEach(function(form) {
      form.addEventListener('submit', function(e) {
        e.preventDefault();
        Swal.fire({
          title: 'Are you sure?',
          text: 'This will permanently delete the slot.',
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
</body>




</html>

