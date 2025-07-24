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
require_once '../inc/db.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question = trim($_POST['question'] ?? '');
    $answer   = trim($_POST['answer'] ?? '');

    if ($question && $answer) {
        try {
            $stmt = $conn->prepare("INSERT INTO faq (question, answer) VALUES (:question, :answer)");
            $stmt->execute([
                ':question' => $question,
                ':answer'   => $answer
            ]);
            $success = "FAQ added successfully!";
        } catch (PDOException $e) {
            error_log("FAQ Insert Error: " . $e->getMessage());
            $error = "Database error. Please try again later.";
        }
    } else {
        $error = "Both Question and Answer are required.";
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
                        <h4 class="card-title mb-0 flex-grow-1">Add FAQs</h4>
                    </div>
                    <div class="card-body">
                        <form action="" method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="question" class="form-label">Question</label>
                                        <input type="text" name="question" class="form-control" placeholder="Enter question" id="question" required>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="answer" class="form-label">Answer</label>
                                        <textarea name="answer" id="answer" class="form-control" placeholder="Enter answer" required></textarea>
                                    </div>
                                </div>
                                
                                <div class="col-lg-12">
                                    <div class="text-end">
                                        <button type="submit" class="btn btn-primary">Add FAQs</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    


         <?php


$stmt = $conn->prepare("SELECT * FROM faq ORDER BY id DESC");
$stmt->execute();
$faqs = $stmt->fetchAll();
$sn = 1; 
?>

<div class="row mb-5">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">FAQs</h4>
            </div><!-- end card header -->

            <div class="card-body">
                <div class="listjs-table" id="faqList">
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
                        <table class="table align-middle table-nowrap" id="categoryTable">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th class="sort" data-sort="question">Question</th>
                                    <th class="sort" data-sort="answer">Answer</th>
                                    <th class="sort" data-sort="date">Created Date</th>
                                    <th class="sort" data-sort="action">Action</th>
                                </tr>
                            </thead>
                            <tbody class="list form-check-all">
                                <?php foreach ($faqs as $faq): ?>
                                    
                                    <tr>
                                        <td><?= $sn++; ?></td>
                                        <td class="question"><?= htmlspecialchars($faq['question']) ?></td>
                                        <td class="answer"><?= htmlspecialchars($faq['answer']) ?></td>
                                        <td class="date"><?= date("d M, Y", strtotime($faq['created_at'])) ?></td>
                                        
                                        <td class="action-icons">
                                        <i class="bx bx-edit icon-tooltip"
                                               title="Edit"
                                               style="color: #3B71CA; cursor: pointer;"
                                               data-bs-toggle="modal"
                                               data-bs-target="#editFaqModal"
                                               data-id="<?= $faq['id']; ?>"
                                               data-question="<?= htmlspecialchars($faq['question']); ?>"
                                               data-answer="<?= htmlspecialchars($faq['answer']); ?>">
                                            </i>

                                            <i class="bx bx-trash-alt icon-tooltip"
                                               title="Delete"
                                               style="color: #F44336; cursor: pointer;"
                                               onclick="deleteFaq(<?= $faq['id']; ?>)">
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
    </div><!-- end col -->
</div><!-- end row -->

</div>
</div>

<!-- Edit FAQ Modal -->
<div class="modal fade" id="editFaqModal" tabindex="-1" aria-labelledby="editFaqLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="inc/update_faq">
      <input type="hidden" name="id" id="edit-faq-id">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editFaqLabel">Edit FAQ</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="edit-faq-question" class="form-label">Question</label>
            <input type="text" name="question" id="edit-faq-question" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="edit-faq-answer" class="form-label">Answer</label>
            <textarea name="answer" id="edit-faq-answer" class="form-control" rows="4" required></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Update FAQ</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </div>
    </form>
  </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', () => {
  const editFaqModal = document.getElementById('editFaqModal');

  editFaqModal.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;

    const id = button.getAttribute('data-id');
    const question = button.getAttribute('data-question');
    const answer = button.getAttribute('data-answer');

    this.querySelector('#edit-faq-id').value = id;
    this.querySelector('#edit-faq-question').value = question;
    this.querySelector('#edit-faq-answer').value = answer;
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
function deleteFaq(id) {
    Swal.fire({
        title: '<div style="display:flex;align-items:center;"><div style="background:linear-gradient(135deg,#ffc107 0%,#ffecb3 100%);color:#856404;border-radius:50%;width:40px;height:40px;display:flex;align-items:center;justify-content:center;margin-right:12px;font-size:1.5rem;"><i class="bi bi-trash"></i></div><div><div style="font-weight:700;font-size:1.1rem;color:#856404;">Delete FAQ?</div><div style="font-size:0.95rem;opacity:0.85;color:#856404;">Are you sure you want to delete this FAQ? This action cannot be undone.</div></div></div>',
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
                title: '<div style="display:flex;align-items:center;"><div style="background:linear-gradient(135deg,#28a745 0%,#20c997 100%);color:white;border-radius:50%;width:40px;height:40px;display:flex;align-items:center;justify-content:center;margin-right:12px;font-size:1.5rem;"><i class="bi bi-check-circle"></i></div><div><div style="font-weight:700;font-size:1.1rem;">Deleted!</div><div style="font-size:0.95rem;opacity:0.85;">FAQ deleted successfully.</div></div></div>',
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
                window.location.href = 'inc/delete_faq.php?id=' + encodeURIComponent(id);
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
                    'question',
                    'answer',
                    'date'
                ],
                searchColumns: ['question', 'answer', 'date'],
                page: 10,
                pagination: true,
                listClass: 'list',
                searchClass: 'search'
            };

            var faqList = new List('faqList', options);

            faqList.on('updated', function (list) {
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

