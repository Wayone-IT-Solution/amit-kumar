<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: index");
    exit;
}

?>
<?php

require_once '../inc/db.php'; // ✅ adjust as needed

// ✅ JSON API to check new order (AJAX)
if (isset($_GET['check_new'])) {
    header('Content-Type: application/json');
    try {
        $stmt = $conn->query("SELECT MAX(id) AS latest_id FROM orders");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode(['latest_id' => $row['latest_id'] ?? 0]);
    } catch (PDOException $e) {
        echo json_encode([
            'error' => 'Database error',
            'message' => $e->getMessage()
        ]);
        http_response_code(500);
    }
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

    <style>
        th{
            font-size: 12px;
        }
        td{
            font-size: 12px;
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
                                        <h4 class="mb-sm-0">Orders List</h4>

                                        <div class="page-title-right">
                                            <ol class="breadcrumb m-0">
                                                <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a>
                                                </li>
                                                <li class="breadcrumb-item active">Orders List</li>
                                            </ol>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <!-- end page title -->






                            <div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">All Orders List</h4>
            </div><!-- end card header -->

            <div class="card-body">
                <div class="listjs-table" id="orderList">
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
                        <table class="table align-middle table-nowrap" id="orderTable">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th class="sort" data-sort="order_code">Order Code</th>
                                    <th class="sort" data-sort="items">Items</th>
                               
                                    <th class="sort" data-sort="total">Total Price</th>
                                    <th class="sort" data-sort="total">Number Of box</th>

                                    <th class="sort" data-sort="address">Delivery Address</th>
                                    <th class="sort" data-sort="block">House Block</th>
                                    <th class="sort" data-sort="road">Area Road</th>
                                    <th class="sort" data-sort="type">Address Type</th>
                                    <th class="sort" data-sort="receiver">Receiver Name</th>
                                    <th class="sort" data-sort="phone">Receiver Phone</th>
                                    <th class="sort" data-sort="date">Order Date</th>
                                    <th class="sort" data-sort="payment">Payment Method</th>
                                    <th class="sort" data-sort="payment_status">Payment Status</th>
                                    <th class="sort" data-sort="status">Status</th>
                                    <th class="sort" data-sort="date">Delivery Date</th>
                                    <th class="sort" data-sort="time">Delivery Time</th>
                                    
                                   
                                </tr>
                            </thead>
                            <tbody class="list form-check-all">
                                <?php
                                require '../inc/db.php';
                                $stmt = $conn->prepare("SELECT * FROM orders ORDER BY id DESC");
                                $stmt->execute();
                                $orders = $stmt->fetchAll();
                                $sn = 1;
                                $customBoxMap = [];
                                $boxStmt = $conn->prepare("SELECT order_id, custom_text FROM custom_box_requests");
                                $boxStmt->execute();
                                while ($row = $boxStmt->fetch(PDO::FETCH_ASSOC)) {
                                    $customBoxMap[$row['order_id']] = $row['custom_text'];
                                }
                                
                                foreach ($orders as $order) {
                                    $cartItems = json_decode($order['cart_data'], true);
                                    $cartSummary = '';
                                
                                    if (is_array($cartItems)) {
                                        foreach ($cartItems as $item) {
                                            // ✅ Convert JSON string to array (Buy Now case)
                                            if (is_string($item)) {
                                                $item = json_decode($item, true);
                                            }
                                
                                            if (!is_array($item)) continue;
                                
                                            // Fallback-safe values
                                            $productName  = $item['product_name'] ?? $item['name'] ?? '-';
                                            $productPrice = $item['product_price'] ?? '0';
                                            $quantity     = $item['quantity'] ?? '-';
                                            $weight       = $item['product_weight'] ?? '-';
                                            $boxName      = $item['box_name'] ?? '-';
                                            $boxPrice     = $item['box_price'] ?? '0';
                                            $customText   = $item['custom_text'] ?? '';
                                            $productImage = $item['product_image'] ?? ''; // ✅ image path (relative or full)
                                
                                            // ✅ Display product image if available
                                            $imageTag = '';
                                            if (!empty($productImage)) {
                                                $imageTag = '<img src="' . htmlspecialchars($productImage) . '" alt="Product Image" style="max-width:100px; max-height:100px;" class="mb-2">';
                                            }
                                
                                            $cartSummary .= '
                                                <div class="mb-2 border-bottom pb-2">
                                                    ' . $imageTag . '<br>
                                                    <strong>Product:</strong> ' . htmlspecialchars($productName) . ' (₹' . htmlspecialchars($productPrice) . ')<br>
                                                    <strong>Quantity:</strong> ' . htmlspecialchars($quantity) . '<br>
                                                    <strong>Weight:</strong> ' . htmlspecialchars($weight) . '<br>
                                                    <strong>Box:</strong> ' . htmlspecialchars($boxName) . ' (₹' . htmlspecialchars($boxPrice) . ')<br>';
                                
                                            if (!empty($customText)) {
                                                $cartSummary .= '<strong>Custom Text:</strong> ' . nl2br(htmlspecialchars($customText)) . '<br>';
                                            }
                                
                                            $cartSummary .= '</div>';
                                        }
                                    } else {
                                        $cartSummary = "<span class='text-danger'>Invalid cart data</span>";
                                    }
                                
                                
                                    
                                    ?>
                                    <tr>
                                        <td><?= $sn++; ?></td>
                                        <td class="order_code"><?= htmlspecialchars($order['order_code']); ?></td>
                                        <td class="items"><?= $cartSummary; ?></td>
                                        <td class="total">₹<?= number_format($order['subtotal'], 2); ?></td>
                                        <td class="total"><?= number_format($order['number_of_boxes']); ?> Box<?= $order['number_of_boxes'] > 1 ? 'es' : '' ?></td>

                                        <td class="address"><?= nl2br(htmlspecialchars($order['address_details'])); ?></td>
                                        <td class="block"><?= htmlspecialchars($order['house_block']); ?></td>
                                        <td class="road"><?= htmlspecialchars($order['area_road']); ?></td>
                                        <td class="type"><?= htmlspecialchars($order['save_as']); ?></td>
                                        <td class="receiver"><?= htmlspecialchars($order['receiver_name']); ?></td>
                                        <td class="phone"><?= htmlspecialchars($order['receiver_phone']); ?></td>
                                        
                                        <?php
                                        $status = strtolower($order['order_status'] ?? '');

                                        switch ($status) {
                                            case 'delivered':
                                                $statusClass = 'bg-success text-white';
                                                break;
                                            case 'pending':
                                                $statusClass = 'bg-warning text-dark';
                                                break;
                                            case 'start_preparing':
                                                $statusClass = 'bg-info text-white';
                                                break;
                                            case 'cancelled':
                                                $statusClass = 'bg-danger text-white';
                                                break;
                                            default:
                                                $statusClass = 'bg-secondary text-white';
                                        }
                                                                                ?>
                                        <td class="date"><?= date("d M, Y", strtotime($order['created_at'])); ?></td>
                                        <td class="payment">
                                            <?php
                                            $paymentMethod = $order['payment_method'] ?? 'cod';
                                            $paymentIcons = [
                                                'cod' => '<i class="ri-money-dollar-circle-line text-success"></i>',
                                                'online' => '<i class="ri-bank-card-line text-primary"></i>',
                                                'upi' => '<i class="ri-smartphone-line text-warning"></i>',
                                                'wallet' => '<i class="ri-wallet-3-line text-info"></i>'
                                            ];
                                            $paymentLabels = [
                                                'cod' => 'Cash on Delivery',
                                                'online' => 'Online Payment',
                                                'upi' => 'UPI Payment',
                                                'wallet' => 'Wallet Payment'
                                            ];
                                            ?>
                                            <span class="badge bg-light text-dark">
                                                <?= $paymentIcons[$paymentMethod] ?? $paymentIcons['cod'] ?>
                                                <?= $paymentLabels[$paymentMethod] ?? 'Cash on Delivery' ?>
                                            </span>
                                        </td>
                                        <td class="payment_status">
                                            <?php
                                            $paymentStatus = $order['payment_status'] ?? 'pending';
                                            $statusColors = [
                                                'pending' => 'bg-warning',
                                                'paid' => 'bg-success',
                                                'failed' => 'bg-danger',
                                                'refunded' => 'bg-info'
                                            ];
                                            $statusLabels = [
                                                'pending' => 'Pending',
                                                'paid' => 'Paid',
                                                'failed' => 'Failed',
                                                'refunded' => 'Refunded'
                                            ];
                                            ?>
                                            <span class="badge <?= $statusColors[$paymentStatus] ?? 'bg-warning' ?>">
                                                <?= $statusLabels[$paymentStatus] ?? 'Pending' ?>
                                            </span>
                                        </td>
                                                                                <td class="status">
                                                                                    
                                        <div class="dropdown">
                                            <a href="#" role="button" id="statusDropdown<?= $order['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                            <span class="badge <?= $statusClass; ?> text-uppercase"><?= htmlspecialchars($order['order_status']); ?></span>
                                            </a>
                                            <ul class="dropdown-menu" aria-labelledby="statusDropdown<?= $order['id']; ?>">
                                            <form method="POST" action="inc/update_order_status">
                                                <input type="hidden" name="order_id" value="<?= $order['id']; ?>">
                                                <li>
                                                <button class="dropdown-item" type="submit" name="order_status" value="pending"
                                                    <?= ($order['order_status'] === 'pending') ? 'disabled' : ''; ?>>
                                                    Mark as Pending
                                                </button>
                                                </li>
                                                <li>
                                                <button class="dropdown-item" type="submit" name="order_status" value="delivered"
                                                    <?= ($order['order_status'] === 'delivered') ? 'disabled' : ''; ?>>
                                                    Mark as Delivered
                                                </button>
                                                </li>
                                                <li>
                                                <button class="dropdown-item" type="submit" name="order_status" value="start_preparing"
                                                    <?= ($order['order_status'] === 'start_preparing') ? 'disabled' : ''; ?>>
                                                    Start Preparing
                                                </button>
                                                </li>
                                                <li>
                                                <button class="dropdown-item" type="submit" name="order_status" value="cancelled"
                                                    <?= ($order['order_status'] === 'cancelled') ? 'disabled' : ''; ?>>
                                                    Mark as Cancelled
                                                </button>
                                                </li>
                                            </form>
                                            </ul>
                                        </div>
                                        
                                        </td>
                                      <!-- Delivery Date Column -->
                                        <td>
                                        <div class="dropdown mt-2">
                                            <a href="#" role="button" id="dateDropdown<?= $order['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                            <span class="badge bg-info text-uppercase">
                                                <?= $order['delivery_date'] ? date("d M Y", strtotime($order['delivery_date'])) : 'Set Date' ?>
                                            </span>
                                            </a>
                                            
                                        </div>
                                        </td>

                                        <!-- Delivery Time Column -->
                                        <td>
                                        <div class="dropdown mt-2">
                                            <a href="#" role="button" id="timeDropdown<?= $order['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                            <span class="badge bg-secondary text-uppercase">
                                                <?= $order['delivery_time'] ? date("g:i A", strtotime($order['delivery_time'])) : 'Set Time' ?>
                                            </span>
                                            </a>

                                            
                                        </div>
                                        </td>



                                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">


                                        <!-- <td class="action">
                                            <i class="bx bx-trash-alt icon-tooltip"
                                               title="Delete"
                                               style="color: #F44336; cursor: pointer;"
                                               onclick="deleteOrder(<?= $order['id']; ?>)">
                                            </i>
                                        </td> -->
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>

                        <div class="noresult" style="display: none">
                            <div class="text-center">
                                <lord-icon src="https://cdn.lordicon.com/msoeawqm.json"
                                           trigger="loop"
                                           colors="primary:#121331,secondary:#08a88a"
                                           style="width:75px;height:75px"></lord-icon>
                                <h5 class="mt-2">Sorry! No Result Found</h5>
                                <p class="text-muted mb-0">We didn't find any orders matching your search.</p>
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


<script>
let soundActivated = false;

document.addEventListener('click', () => {
    if (!soundActivated) {
        const sound = document.getElementById('orderAlertSound');
        if (sound) {
            sound.play().catch(() => {}); // attempt to unlock autoplay
            soundActivated = true;
            console.log("✅ Sound activated after user click");
        }
    }
});
</script>


<!-- 🔊 Sound -->
<audio id="orderAlertSound" src="/amit-kumar/admin/assets/alert.mp3" preload="auto"></audio>

<!-- 🛎️ Notification Toast -->
<div id="order-toast" style="display:none; position:fixed; top:20px; right:20px; background:#28a745; color:white; padding:10px 15px; border-radius:6px; z-index:9999;">
    🛒 New Order Received! <a href="">Refresh</a>
</div>






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

    <button id="hiddenPlayBtn" onclick="playSound(); showPopup();" style="display: none;"></button>




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
                    'order_code',
                    'items',
                    'total',
                    'address',
                    'block',
                    'road',
                    'type',
                    'receiver',
                    'phone',
                    'date',
                    'payment',
                    'payment_status',
                    'status'
                ],
                listClass: 'list',
                searchClass: 'search',
                page: 10,
                pagination: true
            };

            var orderList = new List('orderList', options);

            // Update 'noresult' element visibility
            orderList.on('updated', function (list) {
                var isEmpty = list.matchingItems.length === 0;
                var noresultEl = document.querySelector('.noresult');
                
                if (noresultEl) {
                    noresultEl.style.display = isEmpty ? 'block' : 'none';
                }
            });
        });
    </script>
</body>
<script>
let lastOrderId = <?= isset($orders[0]['id']) ? $orders[0]['id'] : 0 ?>;

function playSound() {
    const sound = document.getElementById('orderAlertSound');
    if (sound) {
        sound.pause();
        sound.currentTime = 0;
        sound.play().catch(err => {
            console.warn("Autoplay might be blocked:", err);
        });
    }
}

function showPopup() {
    const popup = document.getElementById('order-toast');
    popup.style.display = 'block';
    setTimeout(() => {
        popup.style.display = 'none';
    }, 4000);
}

function checkNewOrders() {
    fetch('/amit-kumar/admin/orders-list.php?check_new=1')
        .then(res => res.json())
        .then(data => {
            const newId = parseInt(data.latest_id);
            if (newId > lastOrderId) {
                playSound();    // ✅ Play sound immediately
                showPopup();    // ✅ Show popup
                // ✅ Voice notification
                if ('speechSynthesis' in window) {
                    const msg = new SpeechSynthesisUtterance('New order received on Amit Dairy and Sweets.');
                    msg.lang = 'en-IN';
                    window.speechSynthesis.speak(msg);
                }
                lastOrderId = newId;
            }
        })
        .catch(err => console.error("Order check failed:", err));
}

// 🔁 Keep checking every 5 seconds
setInterval(checkNewOrders, 5000);
</script>




</html>