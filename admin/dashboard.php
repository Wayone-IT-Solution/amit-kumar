<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: index");
    exit;
}
?>

<!doctype html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-preloader="disable">

<head>
    <meta charset="utf-8" />
    <title>Admin Dashboard - Amit Dairy & Sweets</title>
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

    <!-- Custom Dashboard Styles -->
    <style>
        .dashboard-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }
        
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            overflow: hidden;
            position: relative;
        }
        
        .stat-card.earnings {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        .stat-card.orders {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        
        .stat-card.customers {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }
        
        .stat-card.products {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }
        
        .stat-card.subscriptions {
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
        }
        
        .stat-card.subscription-revenue {
            background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255,255,255,0.1);
            clip-path: polygon(0 0, 100% 0, 100% 30%, 0 70%);
        }
        
        .stat-icon {
            position: relative;
            z-index: 2;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
        }
        
        .recent-activity {
            max-height: 400px;
            overflow-y: auto;
        }
        
        .activity-item {
            padding: 15px;
            border-left: 3px solid #e9ecef;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }
        
        .activity-item:hover {
            border-left-color: #667eea;
            background: rgba(102, 126, 234, 0.05);
        }
        
        .activity-item.new-order {
            border-left-color: #28a745;
        }
        
        .activity-item.delivered {
            border-left-color: #17a2b8;
        }
        
        .activity-item.cancelled {
            border-left-color: #dc3545;
        }
        
        .quick-actions .btn {
            border-radius: 10px;
            padding: 12px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .quick-actions .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .chart-container {
            position: relative;
            height: 300px;
        }
        
        .progress-ring {
            width: 120px;
            height: 120px;
        }
        
        .progress-ring-circle {
            stroke: #e9ecef;
            stroke-width: 8;
            fill: transparent;
        }
        
        .progress-ring-progress {
            stroke: #667eea;
            stroke-width: 8;
            fill: transparent;
            stroke-linecap: round;
            transition: stroke-dasharray 0.5s ease;
        }
        
        .welcome-banner {
            background: linear-gradient(135deg, #c7d347ff 0%, #9d92a8ff 100%);
            color: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .welcome-banner h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .welcome-banner p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .welcome-banner h2 {
                font-size: 2rem;
            }
        }
    </style>
</head>

<body>
    <!-- Begin page -->
    <div id="layout-wrapper">
       <?php include ('inc/header.php'); ?>

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
                    
                    // Get current date and time
                    $currentDate = date('Y-m-d');
                    $currentMonth = date('Y-m');
                    $currentYear = date('Y');
                    
                    // Today's statistics
                    $stmt = $conn->prepare("SELECT COUNT(*) as count, SUM(subtotal) as total FROM orders WHERE DATE(created_at) = ?");
                    $stmt->execute([$currentDate]);
                    $todayStats = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    // This month's statistics
                    $stmt = $conn->prepare("SELECT COUNT(*) as count, SUM(subtotal) as total FROM orders WHERE DATE_FORMAT(created_at, '%Y-%m') = ?");
                    $stmt->execute([$currentMonth]);
                    $monthStats = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    // Total statistics
                    $stmt = $conn->prepare("SELECT COUNT(*) as total_orders, SUM(subtotal) as total_earnings FROM orders");
                    $stmt->execute();
                    $totalStats = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    // Total customers
                    $stmt = $conn->prepare("SELECT COUNT(*) as total_customers FROM users");
                    $stmt->execute();
                    $customerStats = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    // Total products
                    $stmt = $conn->prepare("SELECT COUNT(*) as total_products FROM products");
                    $stmt->execute();
                    $productStats = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    // Recent orders
                    $stmt = $conn->prepare("SELECT o.*, u.fullname, u.phone FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 10");
                    $stmt->execute();
                    $recentOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    // Monthly sales data for chart
$stmt = $conn->prepare("
    SELECT 
        DATE_FORMAT(created_at, '%b %Y') AS month,
        SUM(subtotal) AS total,
        MIN(created_at) as min_created
    FROM orders
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%b %Y')
    ORDER BY min_created;
");

$stmt->execute();
$monthlySales = $stmt->fetchAll(PDO::FETCH_ASSOC);

$months = array_column($monthlySales, 'month');
$totals = array_map('floatval', array_column($monthlySales, 'total'));

                    // Order status distribution
                    $stmt = $conn->prepare("
                        SELECT 
                            order_status as status,
                            COUNT(*) as count
                        FROM orders 
                        GROUP BY order_status
                    ");
                    $stmt->execute();
                    $orderStatus = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    $statusLabels = [];
                    $statusData = [];
                    $statusColors = [
                        'pending' => '#ffc107',
                        'confirmed' => '#17a2b8',
                        'processing' => '#007bff',
                        'shipped' => '#6f42c1',
                        'delivered' => '#28a745',
                        'cancelled' => '#dc3545'
                    ];
                    
                    foreach ($orderStatus as $status) {
                        $statusLabels[] = ucfirst($status['status']);
                        $statusData[] = $status['count'];
                    }

                    // Subscription Statistics
                    $stmt = $conn->query("SELECT COUNT(*) as total_subscriptions FROM subscription_orders");
                    $totalSubscriptions = $stmt->fetchColumn();

                    $stmt = $conn->query("SELECT COUNT(*) as active_subscriptions FROM subscription_orders WHERE status = 'active'");
                    $activeSubscriptions = $stmt->fetchColumn();

                    $stmt = $conn->query("SELECT SUM(total_amount) as subscription_revenue FROM subscription_orders");
                    $subscriptionRevenue = $stmt->fetchColumn();

                    // Monthly subscription data
                    $stmt = $conn->query("
                        SELECT 
  DATE_FORMAT(created_at, '%b %Y') AS month,
  COUNT(*) AS count,
  SUM(total_amount) AS revenue
FROM subscription_orders
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
GROUP BY DATE_FORMAT(created_at, '%b %Y')
ORDER BY MIN(created_at);
                    ");
                    $monthlySubscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    $subscriptionMonths = array_column($monthlySubscriptions, 'month');
                    $subscriptionCounts = array_column($monthlySubscriptions, 'count');
                    $subscriptionRevenues = array_column($monthlySubscriptions, 'revenue');

                    // 6-month subscription data
                    $stmt = $conn->query("
                           SELECT 
        DATE_FORMAT(created_at, '%b %Y') AS month, 
        COUNT(*) AS count, 
        SUM(total_amount) AS revenue,
        MIN(created_at) as sort_date
    FROM subscription_orders
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY month
    ORDER BY sort_date 
                    ");
                    $sixMonthSubscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    $sixMonthLabels = array_column($sixMonthSubscriptions, 'month');
                    $sixMonthCounts = array_column($sixMonthSubscriptions, 'count');
                    $sixMonthRevenues = array_column($sixMonthSubscriptions, 'revenue');

                    // Yearly subscription data
                    $stmt = $conn->query("
                        SELECT YEAR(created_at) AS year, COUNT(*) AS count, SUM(total_amount) AS revenue
                        FROM subscription_orders 
                        GROUP BY year 
                        ORDER BY year
                    ");
                    $yearlySubscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    $yearlyLabels = array_column($yearlySubscriptions, 'year');
                    $yearlyCounts = array_column($yearlySubscriptions, 'count');
                    $yearlyRevenues = array_column($yearlySubscriptions, 'revenue');

                    // PHP: Calculate 2 days remaining orders
                    $twoDayStmt = $conn->prepare("SELECT COUNT(*) as two_day_orders FROM orders WHERE delivery_date >= ? AND delivery_date <= ?");
                    $twoDayStmt->execute([$currentDate, date('Y-m-d', strtotime('+1 day'))]);
                    $twoDayOrders = $twoDayStmt->fetchColumn();
                    ?>
                    
                    <!-- Welcome Banner -->
                    <div class="welcome-banner">
                        <div class="row align-items-center">
                            <div class="col-lg-8">
                                <h2>Welcome back, Admin! 👋</h2>
                                <p>Here's what's happening with Amit Dairy & Sweets today</p>
                                <div class="d-flex gap-3 mt-3">
                                    <div class="text-center">
                                        <h4 class="mb-0"><?= $todayStats['count'] ?? 0 ?></h4>
                                        <small>Today's Orders</small>
                                    </div>
                                    <div class="text-center">
                                        <h4 class="mb-0">₹<?= number_format($todayStats['total'] ?? 0) ?></h4>
                                        <small>Today's Revenue</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 text-center">
                                <div class="progress-ring">
                                    <svg class="progress-ring" width="120" height="120">
                                        <circle class="progress-ring-circle" cx="60" cy="60" r="50"></circle>
                                        <circle class="progress-ring-progress" cx="60" cy="60" r="50" 
                                                stroke-dasharray="<?= ($todayStats['count'] ?? 0) * 10 ?>" 
                                                stroke-dashoffset="314"></circle>
                                    </svg>
                                    <div class="position-absolute top-50 start-50 translate-middle text-center">
                                        <h5 class="mb-0"><?= $todayStats['count'] ?? 0 ?></h5>
                                        <small>Orders</small>
                                    </div>
                                </div>
                    </div>
                </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="stats-grid">
                        <div id="twoDayOrdersCard" class="card dashboard-card stat-card" style="background: linear-gradient(135deg, #ff5858 0%, #f857a6 100%); color: white;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-white-50 mb-1">2 Days Remaining Orders</h6>
                                        <h3 class="text-white mb-2"><?= number_format($twoDayOrders ?? 0) ?></h3>
                                        <small class="text-white-75">Delivery today or tomorrow</small>
                                    </div>
                                    <div class="stat-icon" style="background: rgba(255,0,0,0.2);">
                                        <i class="bx bx-alarm-exclamation fs-2" style="color:#fff"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card dashboard-card stat-card earnings">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-white-50 mb-1">Total Earnings</h6>
                                        <h3 class="text-white mb-2">₹<?= number_format($totalStats['total_earnings'] ?? 0) ?></h3>
                                        <small class="text-white-75">This month: ₹<?= number_format($monthStats['total'] ?? 0) ?></small>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="bx bx-dollar-circle fs-2"></i>
                                    </div>
                                </div>
                    </div>
                </div>

                        <div class="card dashboard-card stat-card orders">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-white-50 mb-1">Total Orders</h6>
                                        <h3 class="text-white mb-2"><?= number_format($totalStats['total_orders'] ?? 0) ?></h3>
                                        <small class="text-white-75">This month: <?= number_format($monthStats['count'] ?? 0) ?></small>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="bx bx-shopping-bag fs-2"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card dashboard-card stat-card customers">
            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-white-50 mb-1">Total Customers</h6>
                                        <h3 class="text-white mb-2"><?= number_format($customerStats['total_customers'] ?? 0) ?></h3>
                                        <small class="text-white-75">Registered users</small>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="bx bx-user-circle fs-2"></i>
                                    </div>
                                </div>
                    </div>
                </div>

                        <div class="card dashboard-card stat-card products">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                    <div>
                                        <h6 class="text-white-50 mb-1">Total Products</h6>
                                        <h3 class="text-white mb-2"><?= number_format($productStats['total_products'] ?? 0) ?></h3>
                                        <small class="text-white-75">Active products</small>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="bx bx-package fs-2"></i>
                                    </div>
                    </div>
                    </div>
                </div>

                        <div class="card dashboard-card stat-card subscriptions">
            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-white-50 mb-1">Total Subscriptions</h6>
                                        <h3 class="text-white mb-2"><?= number_format($totalSubscriptions ?? 0) ?></h3>
                                        <small class="text-white-75">Active: <?= number_format($activeSubscriptions ?? 0) ?></small>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="bx bx-user-check fs-2"></i>
                                    </div>
                                </div>
                    </div>
                </div>

                        <div class="card dashboard-card stat-card subscription-revenue">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                    <div>
                                        <h6 class="text-white-50 mb-1">Subscription Revenue</h6>
                                        <h3 class="text-white mb-2">₹<?= number_format($subscriptionRevenue ?? 0) ?></h3>
                                        <small class="text-white-75">Total earnings</small>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="bx bx-rupee fs-2"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts and Analytics Row -->
                    <div class="row">
                        <!-- Monthly Sales Chart -->
                        <div class="col-lg-6">
                            <div class="card dashboard-card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <h5 class="card-title mb-0">Sales Analytics</h5>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                Last 12 Months
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#">Last 6 Months</a></li>
                                                <li><a class="dropdown-item" href="#">Last 12 Months</a></li>
                                                <li><a class="dropdown-item" href="#">This Year</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="chart-container">
                                        <canvas id="monthlySalesChart"></canvas>
                                    </div>
                                </div>
                    </div>
                </div>

                        <!-- Subscription Analytics Chart -->
                        <div class="col-lg-6">
                            <div class="card dashboard-card">
            <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <h5 class="card-title mb-0">Subscription Analytics</h5>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" id="subscriptionPeriodDropdown">
                                                Monthly
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#" data-period="monthly">Monthly</a></li>
                                                <li><a class="dropdown-item" href="#" data-period="6month">6 Months</a></li>
                                                <li><a class="dropdown-item" href="#" data-period="yearly">Yearly</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="chart-container">
                                        <canvas id="subscriptionChart"></canvas>
                                    </div>
                                </div>
                    </div>
                </div>
                    </div>

                    <!-- Second Row of Charts -->
                    <div class="row mt-4">
                        <!-- Order Status Distribution -->
                        <div class="col-lg-6">
                            <div class="card dashboard-card">
                                <div class="card-body">
                                    <h5 class="card-title mb-4">Order Status</h5>
                                    <div class="chart-container">
                                        <canvas id="orderStatusChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Subscription Revenue Chart -->
                        <div class="col-lg-6">
                            <div class="card dashboard-card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <h5 class="card-title mb-0">Subscription Revenue</h5>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" id="revenuePeriodDropdown">
                                                Monthly
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#" data-period="monthly">Monthly</a></li>
                                                <li><a class="dropdown-item" href="#" data-period="6month">6 Months</a></li>
                                                <li><a class="dropdown-item" href="#" data-period="yearly">Yearly</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="chart-container">
                                        <canvas id="subscriptionRevenueChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions and Recent Activity -->
                    <div class="row mt-4">
                        <!-- Quick Actions -->
                        <div class="col-lg-4">
                            <div class="card dashboard-card">
                                <div class="card-body">
                                    <h5 class="card-title mb-4">Quick Actions</h5>
                                    <div class="quick-actions d-grid gap-3">
                                        <a href="new-orders" class="btn btn-primary">
                                            <i class="bx bx-plus-circle me-2"></i>View New Orders
                                        </a>
                                        <a href="product" class="btn btn-success">
                                            <i class="bx bx-plus me-2"></i>Add New Product
                                        </a>
                                        <a href="customers" class="btn btn-info">
                                            <i class="bx bx-user-plus me-2"></i>Manage Customers
                                        </a>
                                        <a href="orders-list" class="btn btn-warning">
                                            <i class="bx bx-list-ul me-2"></i>All Orders
                                        </a>
                                        <a href="subscription" class="btn btn-secondary">
                                            <i class="bx bx-calendar me-2"></i>Subscriptions
                                        </a>
                                        <a href="contact-details" class="btn btn-dark">
                                            <i class="bx bx-cog me-2"></i>Settings
                                        </a>
                                    </div>
                                </div>
                    </div>
                </div>

                        <!-- Recent Orders -->
                        <div class="col-lg-8">
                            <div class="card dashboard-card">
  <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <h5 class="card-title mb-0">Recent Orders</h5>
                                        <a href="orders-list" class="btn btn-sm btn-outline-primary">View All</a>
                                    </div>
                                    <div class="recent-activity">
                                        <?php if (empty($recentOrders)): ?>
                                            <div class="text-center text-muted py-4">
                                                <i class="bx bx-package fs-1"></i>
                                                <p class="mt-2">No orders yet</p>
                                            </div>
                                        <?php else: ?>
                                            <?php foreach ($recentOrders as $order): ?>
                                                <div class="activity-item <?= $order['order_status'] ?>">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div class="flex-grow-1">
                                                            <h6 class="mb-1">Order #<?= $order['id'] ?></h6>
                                                            <p class="mb-1 text-muted">
                                                                <i class="bx bx-user me-1"></i>
                                                                <?= htmlspecialchars($order['fullname'] ?? 'Guest User') ?>
                                                            </p>
                                                            <p class="mb-0 text-muted">
                                                                <i class="bx bx-rupee me-1"></i>
                                                                ₹<?= number_format($order['subtotal']) ?>
                                                            </p>
                                                        </div>
                                                                                <div class="text-end">
                            <span class="badge bg-<?= $order['order_status'] === 'delivered' ? 'success' : ($order['order_status'] === 'cancelled' ? 'danger' : 'warning') ?>">
                                <?= ucfirst($order['order_status']) ?>
                            </span>
                                                            <br>
                                                            <small class="text-muted">
                                                                <?= date('M d, Y H:i', strtotime($order['created_at'])) ?>
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
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

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Dashboard init -->
    <script src="assets/js/pages/dashboard-ecommerce.init.js"></script>

    <!-- App js -->
    <script src="assets/js/app.js"></script>

    <!-- Custom Dashboard Scripts -->
    <script>
        // Monthly Sales Chart
        const salesCtx = document.getElementById('monthlySalesChart').getContext('2d');
        const salesChart = new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode($months) ?>,
                datasets: [{
                    label: 'Monthly Sales (₹)',
                    data: <?= json_encode($totals) ?>,
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 6,
                    pointBackgroundColor: '#667eea',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        labels: {
                            color: '#333',
                            font: {
                                size: 12
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₹' + value.toLocaleString();
                            },
                            color: '#666'
                        },
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
                        }
                    },
                    x: {
                        ticks: {
                            color: '#666'
                        },
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
                        }
                    }
                }
            }
        });

        // Order Status Chart
        const statusCtx = document.getElementById('orderStatusChart').getContext('2d');
        const statusChart = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($statusLabels) ?>,
                datasets: [{
                    data: <?= json_encode($statusData) ?>,
                    backgroundColor: [
                        '#ffc107',
                        '#17a2b8',
                        '#007bff',
                        '#6f42c1',
                        '#28a745',
                        '#dc3545'
                    ],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#333',
                            font: {
                                size: 11
                            },
                            usePointStyle: true,
                            padding: 15
                        }
                    }
                }
            }
        });

        // Subscription Chart Data
        const subscriptionData = {
            monthly: {
                labels: <?= json_encode($subscriptionMonths) ?>,
                counts: <?= json_encode($subscriptionCounts) ?>,
                revenues: <?= json_encode($subscriptionRevenues) ?>
            },
            '6month': {
                labels: <?= json_encode($sixMonthLabels) ?>,
                counts: <?= json_encode($sixMonthCounts) ?>,
                revenues: <?= json_encode($sixMonthRevenues) ?>
            },
            yearly: {
                labels: <?= json_encode($yearlyLabels) ?>,
                counts: <?= json_encode($yearlyCounts) ?>,
                revenues: <?= json_encode($yearlyRevenues) ?>
            }
        };

        // Subscription Analytics Chart
        const subscriptionCtx = document.getElementById('subscriptionChart').getContext('2d');
        let subscriptionChart = new Chart(subscriptionCtx, {
            type: 'bar',
            data: {
                labels: subscriptionData.monthly.labels,
                datasets: [{
                    label: 'Subscriptions',
                    data: subscriptionData.monthly.counts,
                    backgroundColor: 'rgba(102, 126, 234, 0.8)',
                    borderColor: '#667eea',
                    borderWidth: 2,
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        labels: {
                            color: '#333',
                            font: {
                                size: 12
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: '#666'
                        },
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
                        }
                    },
                    x: {
                        ticks: {
                            color: '#666'
                        },
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
                        }
                    }
                }
            }
        });

        // Subscription Revenue Chart
        const revenueCtx = document.getElementById('subscriptionRevenueChart').getContext('2d');
        let revenueChart = new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: subscriptionData.monthly.labels,
                datasets: [{
                    label: 'Revenue (₹)',
                    data: subscriptionData.monthly.revenues,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 6,
                    pointBackgroundColor: '#28a745',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        labels: {
                            color: '#333',
                            font: {
                                size: 12
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₹' + value.toLocaleString();
                            },
                            color: '#666'
                        },
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
                        }
                    },
                    x: {
                        ticks: {
                            color: '#666'
                        },
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
                        }
                    }
                }
            }
        });

        // Chart Period Switchers
        document.querySelectorAll('#subscriptionPeriodDropdown + .dropdown-menu .dropdown-item').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                const period = this.dataset.period;
                const dropdown = document.getElementById('subscriptionPeriodDropdown');
                dropdown.textContent = this.textContent;
                
                updateSubscriptionChart(period);
            });
        });

        document.querySelectorAll('#revenuePeriodDropdown + .dropdown-menu .dropdown-item').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                const period = this.dataset.period;
                const dropdown = document.getElementById('revenuePeriodDropdown');
                dropdown.textContent = this.textContent;
                
                updateRevenueChart(period);
            });
        });

        function updateSubscriptionChart(period) {
            const data = subscriptionData[period];
            subscriptionChart.data.labels = data.labels;
            subscriptionChart.data.datasets[0].data = data.counts;
            subscriptionChart.update();
        }

        function updateRevenueChart(period) {
            const data = subscriptionData[period];
            revenueChart.data.labels = data.labels;
            revenueChart.data.datasets[0].data = data.revenues;
            revenueChart.update();
        }

        // Animate counters
        function animateCounters() {
            const counters = document.querySelectorAll('.counter-value');
            counters.forEach(counter => {
                const target = parseInt(counter.getAttribute('data-target'));
                const duration = 2000;
                const step = target / (duration / 16);
                let current = 0;
                
                const timer = setInterval(() => {
                    current += step;
                    if (current >= target) {
                        current = target;
                        clearInterval(timer);
                    }
                    counter.textContent = Math.floor(current).toLocaleString();
                }, 16);
            });
        }

        // Initialize animations when page loads
        document.addEventListener('DOMContentLoaded', function() {
            animateCounters();
            
            // Add hover effects to cards
            const cards = document.querySelectorAll('.dashboard-card');
            cards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
        });

        // Auto-refresh dashboard data every 30 seconds
        setInterval(() => {
            // You can add AJAX calls here to refresh dashboard data
            console.log('Dashboard data refreshed');
        }, 30000);
    </script>

    <!-- 2 Days Remaining Orders Modal -->
    <div class="modal fade" id="twoDayOrdersModal" tabindex="-1" aria-labelledby="twoDayOrdersModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-danger text-white">
            <h5 class="modal-title" id="twoDayOrdersModalLabel">2 Days Remaining Orders (Today & Tomorrow)</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="table-responsive">
              <table class="table table-striped align-middle">
                <thead class="table-light">
                  <tr>
                    <th>#</th>
                    <th>Order Code</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Boxes</th>
                    <th>Address</th>
                    <th>Receiver</th>
                    <th>Phone</th>
                    <th>Order Date</th>
                    <th>Delivery Date</th>
                    <th>Delivery Time</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $modalSn = 1;
                  $modalStmt = $conn->prepare("SELECT * FROM orders WHERE delivery_date >= ? AND delivery_date <= ? ORDER BY delivery_date ASC, delivery_time ASC");
                  $modalStmt->execute([$currentDate, date('Y-m-d', strtotime('+1 day'))]);
                  $modalOrders = $modalStmt->fetchAll();
                  foreach ($modalOrders as $order):
                    $cartItems = json_decode($order['cart_data'], true);
                    $cartSummary = '';
                    if (is_array($cartItems)) {
                      foreach ($cartItems as $item) {
                        if (is_string($item)) $item = json_decode($item, true);
                        if (!is_array($item)) continue;
                        $productName  = $item['product_name'] ?? $item['name'] ?? '-';
                        $quantity     = $item['quantity'] ?? '-';
                        $boxName      = $item['box_name'] ?? '-';
                        $cartSummary .= '<div><b>' . htmlspecialchars($productName) . '</b> x ' . htmlspecialchars($quantity) . ' <span class="badge bg-warning">' . htmlspecialchars($boxName) . '</span></div>';
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
                    <td><?= $modalSn++; ?></td>
                    <td><?= htmlspecialchars($order['order_code']); ?></td>
                    <td><?= $cartSummary; ?></td>
                    <td>₹<?= number_format($order['subtotal'], 2); ?></td>
                    <td><?= number_format($order['number_of_boxes']); ?> Box<?= $order['number_of_boxes'] > 1 ? 'es' : '' ?></td>
                    <td><?= nl2br(htmlspecialchars($order['address_details'])); ?></td>
                    <td><?= htmlspecialchars($order['receiver_name']); ?></td>
                    <td><?= htmlspecialchars($order['receiver_phone']); ?></td>
                    <td><?= date("d M, Y", strtotime($order['created_at'])); ?></td>
                    <td><span class="badge bg-danger-subtle text-dark fs-6"><?= $order['delivery_date'] ? date("d M Y", strtotime($order['delivery_date'])) : '-' ?></span></td>
                    <td><span class="badge bg-secondary fs-6"><?= $order['delivery_time'] ? date("g:i A", strtotime($order['delivery_time'])) : '-' ?></span></td>
                    <td>
                      <form method="POST" action="inc/update_order_status" class="d-inline status-update-form" data-order-id="<?= $order['id']; ?>">
                        <input type="hidden" name="order_id" value="<?= $order['id']; ?>">
                        <select name="order_status" class="form-select form-select-sm d-inline w-auto status-dropdown" data-current="<?= htmlspecialchars($order['order_status']) ?>">
                          <option value="pending" data-badge="bg-warning text-dark" <?= ($order['order_status'] === 'pending') ? 'selected' : '' ?>>Pending</option>
                          <option value="delivered" data-badge="bg-success text-white" <?= ($order['order_status'] === 'delivered') ? 'selected' : '' ?>>Delivered</option>
                          <option value="start_preparing" data-badge="bg-info text-white" <?= ($order['order_status'] === 'start_preparing') ? 'selected' : '' ?>>Start Preparing</option>
                          <option value="cancelled" data-badge="bg-danger text-white" <?= ($order['order_status'] === 'cancelled') ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                      </form>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
      var twoDayCard = document.getElementById('twoDayOrdersCard');
      if (twoDayCard) {
        twoDayCard.addEventListener('click', function() {
          var modal = new bootstrap.Modal(document.getElementById('twoDayOrdersModal'));
          modal.show();
        });
        twoDayCard.style.cursor = 'pointer';
      }
    });
    </script>

    <!-- Admin Notifications -->
    <link rel="stylesheet" href="assets/css/admin-notifications.css">
    <script src="assets/js/admin-notifications.js"></script>
    
    <!-- Add SweetAlert2 CDN in <head> if not present -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
document.addEventListener('DOMContentLoaded', function() {
  // Modal trigger
  var twoDayCard = document.getElementById('twoDayOrdersCard');
  if (twoDayCard) {
    twoDayCard.addEventListener('click', function() {
      var modal = new bootstrap.Modal(document.getElementById('twoDayOrdersModal'));
      modal.show();
    });
    twoDayCard.style.cursor = 'pointer';
  }

  // Status dropdown confirmation and AJAX update
  document.querySelectorAll('.status-dropdown').forEach(function(dropdown) {
    dropdown.addEventListener('change', function(e) {
      e.preventDefault();
      var form = this.closest('form');
      var newStatus = this.value;
      var currentStatus = this.getAttribute('data-current');
      var statusText = this.options[this.selectedIndex].text;
      var badgeClass = this.options[this.selectedIndex].getAttribute('data-badge');
      var orderId = form.getAttribute('data-order-id');
      var selectEl = this;
      Swal.fire({
        title: 'Change Status?',
        html: '<span class="badge ' + badgeClass + ' px-3 py-2">' + statusText + '</span>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, change it',
        cancelButtonText: 'Cancel',
        reverseButtons: true
      }).then((result) => {
        if (result.isConfirmed) {
          // AJAX submit with explicit headers
          var formData = new FormData(form);
          fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'Accept': 'application/json'
            }
          })
          .then(async response => {
            let data;
            try {
              data = await response.json();
            } catch (err) {
              // Try to show the raw response for debugging
              const text = await response.text();
              Swal.fire({
                icon: 'error',
                title: 'Update Failed',
                html: 'Could not update status.<br><pre style="text-align:left;max-width:400px;overflow:auto;">' + text + '</pre>'
              });
              selectEl.value = currentStatus;
              var badgeClass = selectEl.options[selectEl.selectedIndex].getAttribute('data-badge');
              selectEl.className = 'form-select form-select-sm d-inline w-auto status-dropdown ' + badgeClass;
              return;
            }
            if (data.success) {
              selectEl.setAttribute('data-current', newStatus);
              var badgeClass = selectEl.options[selectEl.selectedIndex].getAttribute('data-badge');
              selectEl.className = 'form-select form-select-sm d-inline w-auto status-dropdown ' + badgeClass;
              showAdminSuccess('Status Updated', 'Order status updated successfully!');
            } else {
              showAdminError('Update Failed', data.message || 'Could not update status.');
              selectEl.value = currentStatus;
              var badgeClass = selectEl.options[selectEl.selectedIndex].getAttribute('data-badge');
              selectEl.className = 'form-select form-select-sm d-inline w-auto status-dropdown ' + badgeClass;
            }
          })
          .catch((err) => {
            showAdminError('Update Failed', 'Could not update status.');
            selectEl.value = currentStatus;
            var badgeClass = selectEl.options[selectEl.selectedIndex].getAttribute('data-badge');
            selectEl.className = 'form-select form-select-sm d-inline w-auto status-dropdown ' + badgeClass;
          });
        } else {
          // Revert dropdown to previous value
          selectEl.value = currentStatus;
          var badgeClass = selectEl.options[selectEl.selectedIndex].getAttribute('data-badge');
          selectEl.className = 'form-select form-select-sm d-inline w-auto status-dropdown ' + badgeClass;
        }
      });
    });
  });

  // Style the status dropdown with badge color
  document.querySelectorAll('.status-dropdown').forEach(function(dropdown) {
    function updateBadge() {
      var badgeClass = dropdown.options[dropdown.selectedIndex].getAttribute('data-badge');
      dropdown.className = 'form-select form-select-sm d-inline w-auto status-dropdown ' + badgeClass;
    }
    updateBadge();
    dropdown.addEventListener('change', updateBadge);
  });

  // Ensure close icon closes modal (Bootstrap handles this by default)

  // Refresh page when 2 Days Remaining Orders modal is closed
  var twoDayOrdersModalEl = document.getElementById('twoDayOrdersModal');
  if (twoDayOrdersModalEl) {
    twoDayOrdersModalEl.addEventListener('hidden.bs.modal', function () {
      location.reload();
    });
  }
});
</script>

</body>
</html>
