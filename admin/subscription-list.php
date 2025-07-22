<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: index");
    exit;
}

require_once '../inc/db.php';

// Auto-expire subscriptions that are older than 7 days
$stmt = $conn->prepare("
    UPDATE subscription_orders 
    SET status = 'expired', updated_at = NOW() 
    WHERE status = 'active' 
    AND created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)
");
$stmt->execute();

// ✅ JSON API to check new subscription (AJAX)
if (isset($_GET['check_new'])) {
    header('Content-Type: application/json');
    try {
        $stmt = $conn->query("SELECT id, status FROM subscription_orders ORDER BY id DESC LIMIT 1");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode([
            'latest_id' => $row['id'] ?? 0,
            'latest_status' => $row['status'] ?? null
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            'error' => 'Database error',
            'message' => $e->getMessage()
        ]);
        http_response_code(500);
    }
    exit;
}

// Fetch all user subscriptions with user details
$stmt = $conn->query("
    SELECT 
        us.*,
        u.fullname as user_name,
        u.phone as user_phone,
        u.email as user_email
    FROM subscription_orders us
    LEFT JOIN users u ON us.user_id = u.id
    ORDER BY us.created_at DESC
");
$userSubscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get subscription statistics
$stmt = $conn->query("SELECT COUNT(*) as total_subscriptions FROM subscription_orders");
$totalSubscriptions = $stmt->fetchColumn();

$stmt = $conn->query("SELECT COUNT(*) as active_subscriptions FROM subscription_orders WHERE status = 'active'");
$activeSubscriptions = $stmt->fetchColumn();

$stmt = $conn->query("SELECT COUNT(*) as expired_subscriptions FROM subscription_orders WHERE status = 'expired'");
$expiredSubscriptions = $stmt->fetchColumn();

$stmt = $conn->query("SELECT SUM(total_amount) as total_revenue FROM subscription_orders");
$totalRevenue = $stmt->fetchColumn();

// Get status distribution
$stmt = $conn->query("
    SELECT 
        status,
        COUNT(*) as count
    FROM subscription_orders 
    GROUP BY status
");
$statusDistribution = $stmt->fetchAll(PDO::FETCH_ASSOC);

$statusLabels = [];
$statusData = [];
$statusColors = [
    'active' => '#28a745',
    'expired' => '#dc3545',
    'cancelled' => '#6c757d',
    'pending' => '#ffc107'
];

foreach ($statusDistribution as $status) {
    $statusLabels[] = ucfirst($status['status']);
    $statusData[] = $status['count'];
}
?>

<!doctype html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg"
    data-sidebar-image="none" data-preloader="disable">

<head>
    <meta charset="utf-8" />
    <title>User Subscriptions - Amit Dairy & Sweets</title>
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

    <!-- Custom Subscription List Styles -->
    <style>
        .subscription-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            overflow: hidden;
            position: relative;
            cursor: pointer;
        }
        
        .subscription-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }
        
        .user-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #b5ea66ff 0%, #a6a81cff 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 1.2rem;
        }
        
        .subscription-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(220, 224, 15, 0.9) 0%, rgba(239, 236, 51, 0.9) 100%);
            opacity: 0;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 20px;
        }
        
        .subscription-card:hover .subscription-overlay {
            opacity: 1;
        }
        
        .overlay-content {
            color: white;
            text-align: center;
        }
        
        .subscription-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 8px;
            line-height: 1.3;
        }
        
        .user-info {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }
        
        .subscription-price {
            font-size: 1.6rem;
            font-weight: 700;
            color: #28a745;
            margin-bottom: 10px;
        }
        
        .subscription-validity {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 10px;
        }
        
        .subscription-status {
            position: absolute;
            top: 15px;
            right: 15px;
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 600;
            z-index: 10;
        }
        
        .status-active {
            background: #d4edda;
            color: #155724;
            border: 2px solid #28a745;
        }
        
        .status-expired {
            background: #f8d7da;
            color: #721c24;
            border: 2px solid #dc3545;
        }
        
        .status-cancelled {
            background: #e2e3e5;
            color: #383d41;
            border: 2px solid #6c757d;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
            border: 2px solid #ffc107;
        }
        
        .delivery-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 10px;
            margin-top: 10px;
            font-size: 0.85rem;
        }
        
        .delivery-info i {
            color: #667eea;
            margin-right: 5px;
        }
        
        /* Modal Styles */
        .subscription-modal .modal-content {
            border-radius: 20px;
            border: none;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
        }
        
        .subscription-modal .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 20px 20px 0 0;
            border: none;
        }
        
        .subscription-modal .modal-body {
            padding: 30px;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .detail-row:last-child {
            border-bottom: none;
        }
        
        .detail-label {
            font-weight: 600;
            color: #333;
            min-width: 150px;
        }
        
        .detail-value {
            color: #666;
            text-align: right;
            flex: 1;
        }
        
        .detail-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .detail-section h6 {
            color: #667eea;
            font-weight: 700;
            margin-bottom: 15px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 8px;
        }
        
        .status-badge-modal {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        .stats-card {
            background: linear-gradient(135deg, #a5a31cff 0%, #b0cf03ff 100%);
            color: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
        }
        
        .stats-card h3 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .stats-card p {
            font-size: 1rem;
            opacity: 0.9;
            margin-bottom: 0;
        }
        
        .stats-icon {
            width: 60px;
            height: 60px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        .search-section {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.08);
        }
        
        .search-box {
            position: relative;
            max-width: 400px;
        }
        
        .search-box input {
            border-radius: 25px;
            padding: 12px 20px 12px 50px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }
        
        .search-box input:focus {
            border-color: #b2c70cff;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .search-box .search-icon {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }
        
        .filter-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .filter-btn {
            border-radius: 20px;
            padding: 8px 16px;
            font-size: 0.85rem;
            font-weight: 600;
            border: 2px solid #e9ecef;
            background: white;
            color: #6c757d;
            transition: all 0.3s ease;
        }
        
        .filter-btn:hover,
        .filter-btn.active {
            background: #d1ba0dff;
            color: white;
            border-color: #b1ac0bff;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        .empty-state h4 {
            font-size: 1.5rem;
            margin-bottom: 10px;
        }
        
        .empty-state p {
            font-size: 1rem;
            margin-bottom: 30px;
        }
        
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .filter-buttons {
                justify-content: center;
            }
            
            .detail-row {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .detail-value {
                text-align: left;
                margin-top: 5px;
            }
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
    </style>
</head>

<body>
    <!-- Begin page -->
    <div id="layout-wrapper">
        <?php include ("inc/header.php"); ?>
        
        <!-- Vertical Overlay-->
        <div class="vertical-overlay"></div>

        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    
                    <!-- Page Title -->
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                <h4 class="mb-sm-0">User Subscriptions Management</h4>
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="dashboard">Dashboard</a></li>
                                        <li class="breadcrumb-item active">User Subscriptions</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="stats-grid">
                        <div class="card subscription-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stats-icon me-3">
                                        <i class="bx bx-user-check text-primary"></i>
                                    </div>
                                    <div>
                                        <h3 class="mb-1"><?= number_format($totalSubscriptions) ?></h3>
                                        <p class="text-muted mb-0">Total Subscriptions</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card subscription-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stats-icon me-3">
                                        <i class="bx bx-check-circle text-success"></i>
                                    </div>
                                    <div>
                                        <h3 class="mb-1"><?= number_format($activeSubscriptions) ?></h3>
                                        <p class="text-muted mb-0">Active Subscriptions</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card subscription-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stats-icon me-3">
                                        <i class="bx bx-x-circle text-danger"></i>
                                    </div>
                                    <div>
                                        <h3 class="mb-1"><?= number_format($expiredSubscriptions) ?></h3>
                                        <p class="text-muted mb-0">Expired Subscriptions</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card subscription-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stats-icon me-3">
                                        <i class="bx bx-rupee text-warning"></i>
                                    </div>
                                    <div>
                                        <h3 class="mb-1">₹<?= number_format($totalRevenue, 2) ?></h3>
                                        <p class="text-muted mb-0">Total Revenue</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Search and Filter Section -->
                    <div class="search-section">
                        <div class="row align-items-center">
                            <div class="col-lg-6">
                                <div class="search-box">
                                    <input type="text" id="searchInput" class="form-control" placeholder="Search user subscriptions...">
                                    <i class="ri-search-line search-icon"></i>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="d-flex justify-content-lg-end align-items-center gap-3">
                                    <div class="filter-buttons">
                                        <button class="filter-btn active" data-filter="all">All</button>
                                        <button class="filter-btn" data-filter="active">Active</button>
                                        <button class="filter-btn" data-filter="expired">Expired</button>
                                        <button class="filter-btn" data-filter="cancelled">Cancelled</button>
                                        <button class="filter-btn" data-filter="pending">Pending</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- User Subscriptions Grid -->
                    <div class="row" id="subscriptionGrid">
                        <?php if (!empty($userSubscriptions)): ?>
                            <?php foreach ($userSubscriptions as $sub): ?>
                                <div class="col-lg-4 col-md-6 mb-4 subscription-item" 
                                     data-status="<?= $sub['status'] ?>"
                                     data-user="<?= strtolower(htmlspecialchars($sub['user_name'] ?? '')) ?>"
                                     data-subscription="<?= strtolower(htmlspecialchars($sub['subscription_title'] ?? '')) ?>">
                                    <div class="card subscription-card h-100" onclick="showSubscriptionDetails(<?= htmlspecialchars(json_encode($sub)) ?>)">
                                        <div class="subscription-status status-<?= $sub['status'] ?>">
                                            <i class="bi bi-<?= $sub['status'] === 'active' ? 'check-circle' : ($sub['status'] === 'expired' ? 'x-circle' : 'clock') ?> me-1"></i>
                                            <?= ucfirst($sub['status']) ?>
                                        </div>
                                        
                                        <div class="subscription-overlay">
                                            <div class="overlay-content">
                                                <h5>Click to View Details</h5>
                                                <p>Full subscription information</p>
                                            </div>
                                        </div>
                                        
                                        <div class="card-body d-flex flex-column">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="user-avatar me-3">
                                                    <?= strtoupper(substr($sub['user_name'] ?? 'U', 0, 1)) ?>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1"><?= htmlspecialchars($sub['user_name'] ?? 'Unknown User') ?></h6>
                                                    <small class="text-muted"><?= htmlspecialchars($sub['user_phone'] ?? 'No phone') ?></small>
                                                </div>
                                            </div>
                                            
                                            <h5 class="subscription-title"><?= htmlspecialchars($sub['subscription_title']) ?></h5>
                                            <p class="user-info">
                                                <strong>Order:</strong> <?= htmlspecialchars($sub['order_code']) ?><br>
                                                <strong>Valid for:</strong> <?= $sub['valid_days'] ?> days
                                            </p>
                                            
                                            <div class="subscription-validity">
                                                <i class="bi bi-calendar me-1"></i>
                                                Expires: <?= date('d M Y', strtotime($sub['expiry_date'])) ?>
                                            </div>
                                            
                                            <div class="subscription-price">
                                                ₹<?= number_format($sub['total_amount'], 2) ?>
                                            </div>
                                            
                                            <div class="delivery-info">
                                                <div><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($sub['address_details']) ?></div>
                                                <div><i class="bi bi-calendar-event"></i> <?= date('d M Y', strtotime($sub['delivery_date'])) ?></div>
                                                <div><i class="bi bi-clock"></i> <?= $sub['delivery_time'] ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12">
                                <div class="empty-state">
                                    <i class="bx bx-user-check"></i>
                                    <h4>No User Subscriptions Found</h4>
                                    <p>No users have subscribed to any plans yet.</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- No Results Message -->
                    <div class="row" id="noResults" style="display: none;">
                        <div class="col-12">
                            <div class="empty-state">
                                <i class="bx bx-search"></i>
                                <h4>No Subscriptions Found</h4>
                                <p>No user subscriptions match your search criteria. Try adjusting your search terms.</p>
                            </div>
                        </div>
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

    <!-- Subscription Details Modal -->
    <div class="modal fade subscription-modal" id="subscriptionDetailsModal" tabindex="-1" aria-labelledby="subscriptionDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="subscriptionDetailsModalLabel">
                        <i class="bx bx-user-check me-2"></i>Subscription Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="subscriptionDetailsContent">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>

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

    <!-- Custom Subscription List Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const filterButtons = document.querySelectorAll('.filter-btn');
            const subscriptionItems = document.querySelectorAll('.subscription-item');
            const subscriptionGrid = document.getElementById('subscriptionGrid');
            const noResults = document.getElementById('noResults');
            
            let currentFilter = 'all';
            let searchTerm = '';

            // Search functionality
            searchInput.addEventListener('input', function() {
                searchTerm = this.value.toLowerCase();
                filterAndSearch();
            });

            // Filter functionality
            filterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    currentFilter = this.dataset.filter;
                    filterAndSearch();
                });
            });

            function filterAndSearch() {
                let visibleCount = 0;
                
                subscriptionItems.forEach(item => {
                    const user = item.dataset.user;
                    const subscription = item.dataset.subscription;
                    const status = item.dataset.status;
                    
                    const matchesSearch = user.includes(searchTerm) || subscription.includes(searchTerm);
                    const matchesFilter = currentFilter === 'all' || status === currentFilter;
                    
                    if (matchesSearch && matchesFilter) {
                        item.style.display = 'block';
                        visibleCount++;
                    } else {
                        item.style.display = 'none';
                    }
                });

                // Show/hide no results message
                if (visibleCount === 0) {
                    subscriptionGrid.style.display = 'none';
                    noResults.style.display = 'block';
                } else {
                    subscriptionGrid.style.display = 'block';
                    noResults.style.display = 'none';
                }
            }

            // Add hover effects to cards
            const cards = document.querySelectorAll('.subscription-card');
            cards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-8px)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
        });

        // Show subscription details in modal
        function showSubscriptionDetails(subscription) {
            const modal = new bootstrap.Modal(document.getElementById('subscriptionDetailsModal'));
            const content = document.getElementById('subscriptionDetailsContent');
            
            const statusClass = `status-${subscription.status}`;
            const statusIcon = subscription.status === 'active' ? 'check-circle' : 
                              subscription.status === 'expired' ? 'x-circle' : 'clock';
            
            content.innerHTML = `
                <div class="detail-section">
                    <h6><i class="bi bi-person me-2"></i>User Information</h6>
                    <div class="detail-row">
                        <span class="detail-label">User Name:</span>
                        <span class="detail-value">${subscription.user_name || 'Unknown User'}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Phone:</span>
                        <span class="detail-value">${subscription.user_phone || 'Not provided'}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Email:</span>
                        <span class="detail-value">${subscription.user_email || 'Not provided'}</span>
                    </div>
                </div>

                <div class="detail-section">
                    <h6><i class="bi bi-box me-2"></i>Subscription Information</h6>
                    <div class="detail-row">
                        <span class="detail-label">Subscription Title:</span>
                        <span class="detail-value">${subscription.subscription_title}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Order Code:</span>
                        <span class="detail-value">${subscription.order_code}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Subscription Price:</span>
                        <span class="detail-value">₹${parseFloat(subscription.subscription_price).toLocaleString()}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Total Amount:</span>
                        <span class="detail-value">₹${parseFloat(subscription.total_amount).toLocaleString()}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Valid Days:</span>
                        <span class="detail-value">${subscription.valid_days} days</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Status:</span>
                        <span class="detail-value">
                            <span class="status-badge-modal ${statusClass}">
                                <i class="bi bi-${statusIcon} me-1"></i>
                                ${subscription.status.charAt(0).toUpperCase() + subscription.status.slice(1)}
                            </span>
                        </span>
                    </div>
                </div>

                <div class="detail-section">
                    <h6><i class="bi bi-calendar me-2"></i>Dates & Timing</h6>
                    <div class="detail-row">
                        <span class="detail-label">Created Date:</span>
                        <span class="detail-value">${new Date(subscription.created_at).toLocaleDateString('en-IN', { 
                            day: 'numeric', 
                            month: 'long', 
                            year: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        })}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Expiry Date:</span>
                        <span class="detail-value">${new Date(subscription.expiry_date).toLocaleDateString('en-IN', { 
                            day: 'numeric', 
                            month: 'long', 
                            year: 'numeric'
                        })}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Delivery Date:</span>
                        <span class="detail-value">${new Date(subscription.delivery_date).toLocaleDateString('en-IN', { 
                            day: 'numeric', 
                            month: 'long', 
                            year: 'numeric'
                        })}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Delivery Time:</span>
                        <span class="detail-value">${subscription.delivery_time}</span>
                    </div>
                </div>

                <div class="detail-section">
                    <h6><i class="bi bi-geo-alt me-2"></i>Delivery Information</h6>
                    <div class="detail-row">
                        <span class="detail-label">Receiver Name:</span>
                        <span class="detail-value">${subscription.receiver_name}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Receiver Phone:</span>
                        <span class="detail-value">${subscription.receiver_phone}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Address Details:</span>
                        <span class="detail-value">${subscription.address_details}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">House/Block:</span>
                        <span class="detail-value">${subscription.house_block}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Area/Road:</span>
                        <span class="detail-value">${subscription.area_road}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Address Type:</span>
                        <span class="detail-value">${subscription.save_as}</span>
                    </div>
                </div>
            `;
            
            modal.show();
        }

        // Animate counters on page load
        function animateCounters() {
            const counters = document.querySelectorAll('.stats-grid h3');
            counters.forEach(counter => {
                const target = parseInt(counter.textContent.replace(/[^\d]/g, ''));
                const duration = 2000;
                const step = target / (duration / 16);
                let current = 0;
                
                const timer = setInterval(() => {
                    current += step;
                    if (current >= target) {
                        current = target;
                        clearInterval(timer);
                    }
                    
                    if (counter.textContent.includes('₹')) {
                        counter.textContent = '₹' + Math.floor(current).toLocaleString();
                    } else {
                        counter.textContent = Math.floor(current).toLocaleString();
                    }
                }, 16);
            });
        }

        // Initialize animations
        setTimeout(animateCounters, 500);
    </script>
</body>
</html> 