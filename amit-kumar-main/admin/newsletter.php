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
    include '../inc/db.php';
$stmt = $conn->prepare("SELECT id, email, status, subscribed_at FROM newsletter_subscribers ORDER BY id DESC");
$stmt->execute();
$subscribers = $stmt->fetchAll();
$sn = 1;
?>

<div class="row mb-5">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header">
        <h4 class="card-title mb-0">Newsletter Subscribers</h4>
      </div>

      <div class="card-body">
        <div class="listjs-table" id="newsletterList">
          <div class="row g-4 mb-3">
            <div class="col-sm d-flex justify-content-sm-end">
              <div class="search-box ms-2">
                <input type="text" class="form-control search" placeholder="Search...">
                <i class="ri-search-line search-icon"></i>
              </div>
            </div>
          </div>

          <div class="table-responsive table-card mt-3 mb-1">
            <table class="table align-middle table-nowrap" id="newsletterTable">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th class="sort" data-sort="email">Email</th>
                  
                  <th class="sort" data-sort="date">Subscribed On</th>
                  <th class="sort" data-sort="status">Status</th>
                  <th class="sort" data-sort="action">Action</th>
                </tr>
              </thead>
              <tbody class="list form-check-all">
                <?php foreach ($subscribers as $sub): ?>
                  <tr>
                    <td><?= $sn++; ?></td>
                    <td class="email"><?= htmlspecialchars($sub['email']); ?></td>

                    
                    <td class="date"><?= date("d M, Y", strtotime($sub['subscribed_at'])); ?></td>
                    <!-- Status Toggle -->
                    <td class="status">
                      <?php if ($sub['status']): ?>
                        <a href="inc/toggle_newsletter_status?id=<?= $sub['id']; ?>&status=0"
                           class="badge bg-success"
                           onclick="return confirm('Mark as Inactive?');">Active</a>
                      <?php else: ?>
                        <a href="inc/toggle_newsletter_status?id=<?= $sub['id']; ?>&status=1"
                           class="badge bg-danger"
                           onclick="return confirm('Mark as Active?');">Inactive</a>
                      <?php endif; ?>
                    </td>


                    <td class="action-icons">
                      <i class="bx bx-trash-alt icon-tooltip"
                         title="Delete"
                         style="color: #F44336; cursor: pointer;"
                         onclick="deleteNewsletter(<?= $sub['id']; ?>)">
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
                <h5 class="mt-2">No Result Found</h5>
                <p class="text-muted mb-0">No subscribers matched your search.</p>
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





                </div> <!-- end col -->


            </div>

        </div>
        <!-- container-fluid -->
    </div>
    <!-- End Page-content -->

    <script>
function deleteNewsletter(id) {
    if (confirm("Are you sure you want to delete this newsletter?")) {
        window.location.href = 'inc/delete_newsletter?id=' + id;
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
                    'email',
                    'date'
                ],
                searchColumns: ['email','date'],
                page: 10,
                pagination: true,
                listClass: 'list',
                searchClass: 'search'
            };

            var newsletterList = new List('newsletterList', options);

            newsletterList.on('updated', function (list) {
                var isEmpty = list.matchingItems.length === 0;
                var noresultEl = document.querySelector('.noresult');
                
                if (noresultEl) {
                    noresultEl.style.display = isEmpty ? 'block' : 'none';
                }
            });
        });
    </script>
</body>




</html>

