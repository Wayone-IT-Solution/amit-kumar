<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: index");
    exit;
}
require_once '../inc/db.php';

$today = date('Y-m-d');
$tomorrow = date('Y-m-d', strtotime('+1 day'));
$stmt = $conn->prepare("SELECT * FROM orders WHERE delivery_date >= ? AND delivery_date <= ? ORDER BY delivery_date ASC, delivery_time ASC");
$stmt->execute([$today, $tomorrow]);
$orders = $stmt->fetchAll();
$sn = 1;
$customBoxMap = [];
$boxStmt = $conn->prepare("SELECT order_id, custom_text FROM custom_box_requests");
$boxStmt->execute();
while ($row = $boxStmt->fetch(PDO::FETCH_ASSOC)) {
    $customBoxMap[$row['order_id']] = $row['custom_text'];
}
?>
<!doctype html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-preloader="disable">
<head>
    <meta charset="utf-8" />
    <title>Upcoming Orders (Next 2 Days) - Amit Dairy & Sweets</title>
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
    <style>
        th{ font-size: 12px; }
        td{ font-size: 12px; }
    </style>
</head>
<body>
<div id="layout-wrapper">
    <?php include ("inc/header.php"); ?>
    <div class="vertical-overlay"></div>
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col">
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                    <h4 class="mb-sm-0">Upcoming Orders (Next 2 Days)</h4>
                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="dashboard">Dashboard</a></li>
                                            <li class="breadcrumb-item active">Upcoming Orders</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title mb-0">Orders for <?= date('d M, Y') ?> & <?= date('d M, Y', strtotime('+1 day')) ?></h4>
                                    </div>
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
                                                        <?php if (count($orders) === 0): ?>
                                                            <tr><td colspan="17" class="text-center text-warning fs-5">No orders for the next 2 days.</td></tr>
                                                        <?php else: foreach ($orders as $order):
                                                            $cartItems = json_decode($order['cart_data'], true);
                                                            $cartSummary = '';
                                                            if (is_array($cartItems)) {
                                                                foreach ($cartItems as $item) {
                                                                    if (is_string($item)) $item = json_decode($item, true);
                                                                    if (!is_array($item)) continue;
                                                                    $productName  = $item['product_name'] ?? $item['name'] ?? '-';
                                                                    $productPrice = $item['product_price'] ?? '0';
                                                                    $quantity     = $item['quantity'] ?? '-';
                                                                    $weight       = $item['product_weight'] ?? '-';
                                                                    $boxName      = $item['box_name'] ?? '-';
                                                                    $boxPrice     = $item['box_price'] ?? '0';
                                                                    $customText   = $item['custom_text'] ?? '';
                                                                    $productImage = $item['product_image'] ?? '';
                                                                    $imageTag = '';
                                                                    if (!empty($productImage)) {
                                                                        $imageTag = '<img src="' . htmlspecialchars($productImage) . '" alt="Product Image" style="max-width:100px; max-height:100px;" class="mb-2">';
                                                                    }
                                                                    $cartSummary .= '<div class="mb-2 border-bottom pb-2">' . $imageTag . '<br><strong>Product:</strong> ' . htmlspecialchars($productName) . ' (₹' . htmlspecialchars($productPrice) . ')<br><strong>Quantity:</strong> ' . htmlspecialchars($quantity) . '<br><strong>Weight:</strong> ' . htmlspecialchars($weight) . '<br><strong>Box:</strong> ' . htmlspecialchars($boxName) . ' (₹' . htmlspecialchars($boxPrice) . ')<br>';
                                                                    if (!empty($customText)) {
                                                                        $cartSummary .= '<strong>Custom Text:</strong> ' . nl2br(htmlspecialchars($customText)) . '<br>';
                                                                    }
                                                                    $cartSummary .= '</div>';
                                                                }
                                                            } else {
                                                                $cartSummary = "<span class='text-danger'>Invalid cart data</span>";
                                                            }
                                                            $status = strtolower($order['order_status'] ?? '');
                                                            switch ($status) {
                                                                case 'delivered': $statusClass = 'bg-success text-white'; break;
                                                                case 'pending': $statusClass = 'bg-warning text-dark'; break;
                                                                case 'start_preparing': $statusClass = 'bg-info text-white'; break;
                                                                case 'cancelled': $statusClass = 'bg-danger text-white'; break;
                                                                default: $statusClass = 'bg-secondary text-white';
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
                                                                                <button class="dropdown-item" type="submit" name="order_status" value="pending" <?= ($order['order_status'] === 'pending') ? 'disabled' : ''; ?>>Mark as Pending</button>
                                                                            </li>
                                                                            <li>
                                                                                <button class="dropdown-item" type="submit" name="order_status" value="delivered" <?= ($order['order_status'] === 'delivered') ? 'disabled' : ''; ?>>Mark as Delivered</button>
                                                                            </li>
                                                                            <li>
                                                                                <button class="dropdown-item" type="submit" name="order_status" value="start_preparing" <?= ($order['order_status'] === 'start_preparing') ? 'disabled' : ''; ?>>Start Preparing</button>
                                                                            </li>
                                                                            <li>
                                                                                <button class="dropdown-item" type="submit" name="order_status" value="cancelled" <?= ($order['order_status'] === 'cancelled') ? 'disabled' : ''; ?>>Mark as Cancelled</button>
                                                                            </li>
                                                                        </form>
                                                                    </ul>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div class="dropdown mt-2">
                                                                    <a href="#" role="button" id="dateDropdown<?= $order['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                                                        <span class="badge bg-info text-uppercase">
                                                                            <?= $order['delivery_date'] ? date("d M Y", strtotime($order['delivery_date'])) : 'Set Date' ?>
                                                                        </span>
                                                                    </a>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div class="dropdown mt-2">
                                                                    <a href="#" role="button" id="timeDropdown<?= $order['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                                                        <span class="badge bg-secondary text-uppercase">
                                                                            <?= $order['delivery_time'] ? date("g:i A", strtotime($order['delivery_time'])) : 'Set Time' ?>
                                                                        </span>
                                                                    </a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <?php endforeach; endif; ?>
                                                    </tbody>
                                                </table>
                                                <div class="noresult" style="display: none">
                                                    <div class="text-center">
                                                        <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop" colors="primary:#121331,secondary:#08a88a" style="width:75px;height:75px"></lord-icon>
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
    </div>
</div>
<script src="assets/libs/apexcharts/apexcharts.min.js"></script>
<script src="assets/libs/jsvectormap/jsvectormap.min.js"></script>
<script src="assets/libs/jsvectormap/maps/world-merc.js"></script>
<script src="assets/libs/swiper/swiper-bundle.min.js"></script>
<script src="assets/js/pages/dashboard-ecommerce.init.js"></script>
<script src="assets/js/app.js"></script>
<script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/libs/simplebar/simplebar.min.js"></script>
<script src="assets/libs/node-waves/waves.min.js"></script>
<script src="assets/libs/feather-icons/feather.min.js"></script>
<script src="assets/js/pages/plugins/lord-icon-2.1.0.js"></script>
<script src="assets/js/plugins.js"></script>
<script src="assets/libs/prismjs/prism.js"></script>
<script src="assets/libs/list.js/list.min.js"></script>
<script src="assets/libs/list.pagination.js/list.pagination.min.js"></script>
<script src="assets/js/pages/listjs.init.js"></script>
<script src="assets/libs/sweetalert2/sweetalert2.min.js"></script>
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
</html> 