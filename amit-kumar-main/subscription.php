<?php
session_start();
require_once 'inc/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login");
    exit;
}

// Get user details
$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT fullname, phone FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$receiver_name = $user['fullname'] ?? '';
$receiver_phone = $user['phone'] ?? '';

// Fetch all active subscriptions
$stmt = $conn->query("SELECT * FROM subscriptions WHERE status = 1 ORDER BY created_at DESC");
$subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch time slots for delivery
$stmt = $conn->query("SELECT DISTINCT start_time, end_time FROM blocked_slots WHERE start_time IS NOT NULL AND end_time IS NOT NULL ORDER BY start_time");
$timeSlots = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch blocked dates
$stmt = $conn->query("SELECT DISTINCT blocked_date FROM blocked_slots WHERE blocked_date IS NOT NULL AND blocked_date != ''");
$blockedDatesRaw = $stmt->fetchAll(PDO::FETCH_COLUMN);
$blockedDates = json_encode($blockedDatesRaw);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subscription_id'])) {
    try {
        $subscription_id = (int)$_POST['subscription_id'];
        $address_details = trim($_POST['address_details']);
        $house_block = trim($_POST['house_block']);
        $area_road = trim($_POST['area_road']);
        $save_as = trim($_POST['save_as']);
        $delivery_date = $_POST['delivery_date'];
        $delivery_time = $_POST['delivery_time'];
        $receiver_name = trim($_POST['receiver_name']);
        $receiver_phone = trim($_POST['receiver_phone']);
        
        // Validate required fields
        if (empty($address_details) || empty($house_block) || empty($area_road) || 
            empty($delivery_date) || empty($delivery_time) || empty($receiver_name) || empty($receiver_phone)) {
            throw new Exception("All fields are required.");
        }
        
        // Get subscription details
        $stmt = $conn->prepare("SELECT * FROM subscriptions WHERE id = ? AND status = 1");
        $stmt->execute([$subscription_id]);
        $subscription = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$subscription) {
            throw new Exception("Invalid subscription plan selected.");
        }
        
        // Validate delivery date (not in past)
        $delivery_date_obj = new DateTime($delivery_date);
        $today = new DateTime();
        if ($delivery_date_obj < $today) {
            throw new Exception("Delivery date cannot be in the past");
        }
        
        // Check if delivery date is blocked
        $stmt = $conn->prepare("SELECT COUNT(*) FROM blocked_slots WHERE blocked_date = ?");
        $stmt->execute([$delivery_date]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception("Selected delivery date is not available");
        }
        
        // Generate order code
        $order_code = 'SUB' . date('Ymd') . rand(1000, 9999);
        
        // Calculate expiry date
        $expiry_date = date('Y-m-d', strtotime("+{$subscription['valid_days']} days"));
        
        // Create subscription_orders table if it doesn't exist
        $create_table_sql = "
        CREATE TABLE IF NOT EXISTS `subscription_orders` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `order_code` varchar(50) NOT NULL,
            `user_id` int(11) NOT NULL,
            `subscription_id` int(11) NOT NULL,
            `subscription_title` varchar(255) NOT NULL,
            `subscription_price` decimal(10,2) NOT NULL,
            `valid_days` int(11) NOT NULL,
            `expiry_date` date NOT NULL,
            `address_details` text NOT NULL,
            `house_block` varchar(255) NOT NULL,
            `area_road` varchar(255) NOT NULL,
            `save_as` varchar(50) NOT NULL,
            `delivery_date` date NOT NULL,
            `delivery_time` varchar(50) NOT NULL,
            `receiver_name` varchar(255) NOT NULL,
            `receiver_phone` varchar(20) NOT NULL,
            `status` enum('pending','active','paused','cancelled','expired') NOT NULL DEFAULT 'pending',
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `order_code` (`order_code`),
            KEY `user_id` (`user_id`),
            KEY `subscription_id` (`subscription_id`),
            KEY `status` (`status`),
            KEY `delivery_date` (`delivery_date`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        
        $conn->exec($create_table_sql);
        
        // Create subscription_logs table if it doesn't exist
        $create_logs_table_sql = "
        CREATE TABLE IF NOT EXISTS `subscription_logs` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `subscription_order_id` int(11) NOT NULL,
            `user_id` int(11) NOT NULL,
            `admin_id` int(11) DEFAULT NULL,
            `action` varchar(50) NOT NULL COMMENT 'created, activated, expired, cancelled, manually_expired, setup_30day',
            `details` text DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `subscription_order_id` (`subscription_order_id`),
            KEY `user_id` (`user_id`),
            KEY `admin_id` (`admin_id`),
            KEY `action` (`action`),
            KEY `created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        
        $conn->exec($create_logs_table_sql);
        
        // Insert into subscription_orders table
        $stmt = $conn->prepare("INSERT INTO subscription_orders (
            order_code, user_id, subscription_id, subscription_title, subscription_price, 
            valid_days, expiry_date, address_details, house_block, area_road, save_as,
            delivery_date, delivery_time, receiver_name, receiver_phone, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
        
        $stmt->execute([
            $order_code, $userId, $subscription_id, $subscription['title'], $subscription['price'],
            $subscription['valid_days'], $expiry_date, $address_details, $house_block, $area_road, $save_as,
            $delivery_date, $delivery_time, $receiver_name, $receiver_phone
        ]);
        
        $orderId = $conn->lastInsertId();
        
        // Log the subscription creation
        $logStmt = $conn->prepare("
            INSERT INTO subscription_logs 
            (subscription_order_id, user_id, action, details, created_at) 
            VALUES (?, ?, 'created', ?, NOW())
        ");
        $logStmt->execute([$orderId, $userId, "Subscription order #$orderId created successfully"]);
        
        // Check if this is a 30-day subscription and set up auto-expiration
        if ($subscription['valid_days'] == 30) {
            // Log the 30-day subscription setup
            $logStmt = $conn->prepare("
                INSERT INTO subscription_logs 
                (subscription_order_id, user_id, action, details, created_at) 
                VALUES (?, ?, 'setup_30day', ?, NOW())
            ");
            $logStmt->execute([$orderId, $userId, "30-day subscription auto-expiration setup for order #$orderId"]);
        }
        
        // Store order details in session for payment
        $_SESSION['subscription_order'] = [
            'order_id' => $orderId,
            'order_code' => $order_code,
            'subscription_title' => $subscription['title'],
            'subscription_price' => $subscription['price'],
            'valid_days' => $subscription['valid_days'],
            'expiry_date' => $expiry_date,
            'delivery_date' => $delivery_date,
            'receiver_name' => $receiver_name,
            'receiver_phone' => $receiver_phone,
            'address_details' => $address_details,
            'house_block' => $house_block,
            'area_road' => $area_road,
            'save_as' => $save_as,
            'delivery_time' => $delivery_time
        ];
        
        // Redirect to payment page
        header("Location: test_payment");
        exit;
        
    } catch (Exception $e) {
        $error_msg = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
    <title>Subscription Plans - Amit Dairy & Sweets</title>
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta name="description" content="Choose from our premium subscription plans for fresh dairy products">
    <meta name="keywords" content="dairy subscription, milk delivery, fresh dairy">

  <!-- Favicons -->
  <link href="assets/img/logo.webp" rel="icon">
  <link href="assets/img/logo.webp" rel="apple-touch-icon">

  <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

  <!-- Template Main CSS File -->
  <link href="assets/css/main.css" rel="stylesheet">

  <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    
    <!-- Flatpickr -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

  <style>
        .subscription-hero {
            background: linear-gradient(135deg, #D6B669 0%, #f4d03f 100%);
            padding: 80px 0;
            position: relative;
            overflow: hidden;
        }
        
        .subscription-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('assets/img/pattern.png') repeat;
            opacity: 0.1;
        }

        .subscription-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border: none;
            overflow: hidden;
            position: relative;
            height: 100%;
        }

        .subscription-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }

        .subscription-card .card-img-top {
            height: 250px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .subscription-card:hover .card-img-top {
            transform: scale(1.05);
        }

        .subscription-card .card-body {
            padding: 2rem;
            position: relative;
        }

        .subscription-card .card-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 1rem;
        }

        .subscription-info {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 1.5rem;
            margin: 1rem 0;
        }

        .subscription-info .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid #e9ecef;
        }

        .subscription-info .info-item:last-child {
            border-bottom: none;
        }

        .subscription-info .info-label {
            font-weight: 600;
            color: #6c757d;
        }

        .subscription-info .info-value {
            font-weight: 700;
            color: #2c3e50;
        }

        .price-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 25px;
            font-weight: 700;
            font-size: 1.1rem;
            z-index: 10;
        }

        .subscribe-btn {
            background: linear-gradient(45deg, #D6B669, #f4d03f);
            border: none;
            border-radius: 25px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            color: #2c3e50;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 1rem;
        }

        .subscribe-btn:hover {
            background: linear-gradient(45deg, #f4d03f, #D6B669);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(214, 182, 105, 0.4);
            color: #2c3e50;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2c3e50;
            text-align: center;
            margin-bottom: 1rem;
        }

        .section-subtitle {
            font-size: 1.1rem;
            color: #6c757d;
            text-align: center;
            margin-bottom: 3rem;
        }

        .subscription-count {
            background: linear-gradient(45deg, #D6B669, #f4d03f);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 2rem;
        }

        .subscription-features {
            list-style: none;
            padding: 0;
            margin: 1rem 0;
        }

        .subscription-features li {
            padding: 0.5rem 0;
            color: #6c757d;
            position: relative;
            padding-left: 1.5rem;
        }

        .subscription-features li::before {
            content: '✓';
            position: absolute;
            left: 0;
            color: #28a745;
            font-weight: bold;
        }

        .modal-content {
            border-radius: 20px;
            border: none;
        }

        .form-control {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background-color: #fff;
            border-color: #D6B669;
            box-shadow: 0 0 0 0.2rem rgba(214, 182, 105, 0.25);
        }

        .btn-save {
            border: 1px solid #D6B669;
            background-color: transparent;
            color: #000;
            transition: all 0.2s ease-in-out;
            padding: 0.5rem 1rem;
            cursor: pointer;
      border-radius: 10px;
        }

        .btn-check:checked + .btn-save {
            background-color: #d4b160;
            color: #fff;
            border-color: #d4b160;
        }

        .btn-save:hover {
            background-color: #f5e6c9;
        }

        .blocked-date {
            font-weight: bold;
            text-decoration: line-through;
        }

        @media (max-width: 768px) {
            .subscription-card .card-body {
                padding: 1.5rem;
            }
            
            .subscription-card .card-title {
                font-size: 1.25rem;
            }
            
            .section-title {
                font-size: 2rem;
            }
        }
  </style>
</head>

<body class="index-page">
    <?php include('inc/header.php'); ?>

    <!-- Hero Section -->
<main class="main">
        <section class="subscription-hero">
    <div class="container text-center py-5">
      <div class="d-flex justify-content-center align-items-center mb-3">
        <img src="assets/img/Vector.png" alt="" class="me-2">
                    <h2 class="m-0 text-white">Subscription Plans</h2>
        <img src="assets/img/Vector (1).png" alt="" class="ms-2">
      </div>
      <nav aria-label="breadcrumb" class="d-flex justify-content-center">
        <ol class="breadcrumb bg-transparent">
          <li class="breadcrumb-item"><a href="index" class="text-light fw-semibold text-decoration-none">Home</a></li>
                        <li class="breadcrumb-item active text-light fw-semibold" aria-current="page">Subscription Plans</li>
        </ol>
      </nav>
    </div>
  </section>
</main>

    <!-- Subscription Plans Section -->
    <section class="py-5">
  <div class="container">
    <div class="text-center mb-5">
      <h1 class="section-title">Choose Your Perfect Plan</h1>
      <p class="section-subtitle">Discover our exclusive subscription plans designed to bring fresh dairy products to your doorstep regularly</p>
      <div class="subscription-count">
        <?= count(
          $subscriptions) ?> Available Plans
      </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="row mb-4 justify-content-right">
      <div class="col-lg-6 col-md-8 col-12">
        <div class="d-flex flex-wrap gap-3 align-items-center justify-content-center p-3 rounded-4 shadow-sm" style="background: linear-gradient(90deg, #fffbe6 0%, #fff3cd 100%); border: 2px solid #ffe066;">
          <div class="flex-grow-1">
            <input type="text" id="subscriptionSearch" class="form-control form-control-lg border-0 rounded-3" placeholder="🔍 Search please..." style="background: #fffde7; color: #b08a2a; font-weight: 500;">
          </div>
          <div>
            <select id="validityFilter" class="form-select form-select-lg border-0 rounded-3" style="background: #fffde7; color: #b08a2a; font-weight: 500; min-width: 180px;">
              <option value="">Filter</option>
              <?php $validities = array_unique(array_map(function($s){return $s['valid_days'];}, $subscriptions)); sort($validities); foreach($validities as $v): ?>
                <option value="<?= $v ?>"><?= $v ?> Days</option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>
    </div>
    <!-- End Search & Filter Bar -->

    <div class="row g-4" id="subscriptionContainer">
                <?php if (!empty($subscriptions)): ?>
        <?php foreach ($subscriptions as $sub): 
          $subImageFile = $sub['image'] ?? '';
          $imagePath = 'admin/' . $subImageFile;
          $subImage = (!empty($subImageFile) && file_exists(__DIR__ . '/' . $imagePath)) 
                      ? $imagePath 
                      : "assets/img/no-image.png";
                    ?>
                    <div class="col-lg-4 col-md-6 subscription-card-wrapper" data-aos="fade-up" data-aos-delay="<?= $loop * 100 ?>">
                        <div class="subscription-card h-100">
                            <!-- Price Badge -->
                            <div class="price-badge">
                                ₹<?= number_format($sub['price'], 2) ?>
                            </div>
                            
                            <!-- Card Image -->
                            <img src="<?= htmlspecialchars($subImage) ?>" class="card-img-top" alt="<?= htmlspecialchars($sub['title']) ?>">
                            
                            <!-- Card Body -->
            <div class="card-body text-center">
                                <h5 class="card-title"><?= htmlspecialchars($sub['title']) ?></h5>
                                
                                <!-- Subscription Info -->
                                <div class="subscription-info">
                                    <div class="info-item">
                                        <span class="info-label">Validity Period:</span>
                                        <span class="info-value"><?= $sub['valid_days'] ?> Days</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Price:</span>
                                        <span class="info-value">₹<?= number_format($sub['price'], 2) ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Daily Cost:</span>
                                        <span class="info-value">₹<?= number_format($sub['price'] / $sub['valid_days'], 2) ?></span>
                                    </div>
                                </div>

                                <!-- Description -->
                                <?php if (!empty($sub['description'])): ?>
                                    <p class="text-muted mb-3"><?= htmlspecialchars($sub['description']) ?></p>
                                <?php endif; ?>

                                <!-- Features List -->
                                <ul class="subscription-features">
                                    <li>Fresh dairy products</li>
                                    <li>Regular doorstep delivery</li>
                                    <li>Quality guaranteed</li>
                                    <li>Flexible scheduling</li>
                                </ul>

                                <!-- Subscribe Button -->
                                <button class="btn subscribe-btn" onclick="openSubscriptionModal(<?= $sub['id'] ?>, '<?= htmlspecialchars($sub['title']) ?>', <?= $sub['price'] ?>, <?= $sub['valid_days'] ?>)">
                                    <i class="bi bi-calendar-check me-2"></i>
                                    Subscribe Now
                                </button>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      <?php else: ?>
                    <div class="col-12">
                        <div class="text-center py-5">
                            <i class="bi bi-inbox display-1 text-muted"></i>
                            <h4 class="mt-3 text-muted">No Subscription Plans Available</h4>
                            <p class="text-muted">Please check back later for available plans.</p>
                        </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
    </section>

    <!-- Checkout Modal -->
    <div class="modal fade" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <form id="placeOrderForm" method="post" class="modal-content border-0 shadow rounded-4 p-4 bg-white">
                <input type="hidden" name="subscription_id" id="modal_subscription_id">

                <div class="modal-body p-0">
                    <div class="mb-4">
                        <div class="d-flex align-items-start">
                            <div class="me-2"><i class="bi bi-geo-alt-fill text-warning fs-4"></i></div>
                            <div>
                                <div class="fw-bold" id="modal-title">Subscribe to Plan</div>
                                <div class="text-muted small">Please add your delivery details below</div>
                            </div>
                        </div>
                    </div>

                    <!-- Subscription Details -->
                    <div class="alert alert-info mb-4">
                        <h6 class="mb-2">Selected Plan: <span id="modal_plan_title"></span></h6>
                        <p class="mb-1">Price: ₹<span id="modal_plan_price"></span></p>
                        <p class="mb-0">Validity: <span id="modal_plan_validity"></span> Days</p>
                    </div>

                    <div class="mb-3">
                        <input type="text" class="form-control" name="address_details" placeholder="Add Detailed Address to reach your Doorstep easily" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small">House / Flat / Block No.</label>
                        <input type="text" class="form-control" name="house_block" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small">Apartment / Road / Area</label>
                        <input type="text" class="form-control" name="area_road" required>
                    </div>

                    <!-- Save As Buttons -->
                    <label class="form-label small d-block mb-2">Save As</label>
                    <div class="mb-4 d-flex flex-wrap gap-2">
                        <?php foreach (['Home', 'Work', 'Friends & Family', 'Others'] as $option): ?>
                            <div>
                                <input type="radio" class="btn-check" name="save_as" id="<?= strtolower(str_replace(' ', '_', $option)) ?>" value="<?= $option ?>" <?= $option === 'Home' ? 'checked' : '' ?>>
                                <label class="btn-save px-3" for="<?= strtolower(str_replace(' ', '_', $option)) ?>"><?= $option ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                     
                    <!-- Calendar -->
                    <div class="mb-3">
                        <label class="form-label small">Delivery Date</label>
                        <input type="text" class="form-control" name="delivery_date" id="delivery_date" required placeholder="Select a date">
                    </div>

                    <div class="mb-4">
                        <label class="form-label small">Delivery Time</label>
                        <select class="form-control" name="delivery_time" id="delivery_time" required>
                            <option value="">Select Delivery Time</option>
                            <?php foreach ($timeSlots as $time): ?>
                                <?php
                                    $startFormatted = date("g:i A", strtotime($time['start_time']));
                                    $endFormatted = date("g:i A", strtotime($time['end_time']));
                                    $label = "$startFormatted - $endFormatted";
                                    $value = $time['start_time'] . '-' . $time['end_time'];
                                ?>
                                <option value="<?= htmlspecialchars($value) ?>"><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Receiver Info -->
                    <div class="mb-3">
                        <label class="form-label small">Receiver's Name</label>
                        <input type="text" class="form-control" name="receiver_name" value="<?= htmlspecialchars($receiver_name) ?>" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small">Receiver's Phone Number</label>
                        <input type="text" class="form-control" name="receiver_phone" value="<?= htmlspecialchars($receiver_phone) ?>" required>
                    </div>

                    <!-- Submit Button -->
                    <div class="d-grid">
                        <button type="submit" class="btn rounded-pill text-white fw-semibold" style="background-color: #d1ae5e;">
                            Subscribe to Plan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

<?php include('inc/footer.php'); ?>

<!-- Scripts -->
<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/vendor/aos/aos.js"></script>
<script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
<script src="assets/js/main.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Flatpickr -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });

        // Open subscription modal
        function openSubscriptionModal(id, title, price, validity) {
            document.getElementById('modal_subscription_id').value = id;
            document.getElementById('modal_plan_title').textContent = title;
            document.getElementById('modal_plan_price').textContent = price.toFixed(2);
            document.getElementById('modal_plan_validity').textContent = validity;
            
            const modal = new bootstrap.Modal(document.getElementById('checkoutModal'));
            modal.show();
        }

        // Initialize date picker
        document.addEventListener('DOMContentLoaded', function () {
            const blockedDates = <?= $blockedDates ?>;

            const today = new Date();
            const plusOne = new Date(today);
            plusOne.setDate(today.getDate() + 1);

            const plusTwo = new Date(today);
            plusTwo.setDate(today.getDate() + 2);

            const formatDate = d => d.toISOString().split('T')[0];

            blockedDates.push(formatDate(today));
            blockedDates.push(formatDate(plusOne));

            flatpickr("#delivery_date", {
                dateFormat: "Y-m-d",
                minDate: new Date(),
                disable: blockedDates,
                onDayCreate: function (_, __, ___, dayElem) {
                    const dateObj = dayElem.dateObj;
                    const dateStr = dateObj.getFullYear() + '-' +
                        String(dateObj.getMonth() + 1).padStart(2, '0') + '-' +
                        String(dateObj.getDate()).padStart(2, '0');

                    if (blockedDates.includes(dateStr)) {
                        dayElem.classList.add("blocked-date");
                        dayElem.style.backgroundColor = "#f8d7da";
                        dayElem.style.color = "#842029";
                        dayElem.style.border = "1px solid #dc3545";
                        dayElem.style.opacity = "0.6";
                        dayElem.style.cursor = "not-allowed";

                        dayElem.addEventListener('click', function (e) {
                            e.preventDefault();
                            Swal.fire({
                                icon: 'error',
                                title: 'Unavailable Date',
                                text: 'This date is unavailable for delivery. Please choose another.',
                                confirmButtonColor: '#d33'
                            });
                        });
                    }
                },
                onChange: function (selectedDates, dateStr) {
                    if (blockedDates.includes(dateStr)) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Blocked Date',
                            text: 'This date is blocked. Please choose another.',
                            confirmButtonColor: '#d33'
                        });
                        this.clear();
                    }
                }
            });
        });

        // Handle form submission
        document.getElementById('placeOrderForm').addEventListener('submit', function(e) {
            const form = this;
            const submitBtn = form.querySelector('button[type="submit"]');

            // Validate form
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('is-invalid');
                } else {
                    field.classList.remove('is-invalid');
                }
            });

            if (!isValid) {
                e.preventDefault();
                Swal.fire('Validation Error', 'Please fill in all required fields.', 'warning');
                return;
            }

            // Show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Processing...';

            // Form will submit normally to the same page
            // The PHP will handle the processing and redirect to payment page
        });

        <?php if (isset($success_msg)): ?>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '<?= $success_msg ?>',
                confirmButtonColor: '#28a745'
            });
        <?php endif; ?>

        <?php if (isset($error_msg)): ?>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '<?= $error_msg ?>',
                confirmButtonColor: '#dc3545'
            });
        <?php endif; ?>
    </script>
    <script>
    // Subscription Search & Filter
    (function() {
      const searchInput = document.getElementById('subscriptionSearch');
      const filterSelect = document.getElementById('validityFilter');
      const container = document.getElementById('subscriptionContainer');
      if (!searchInput || !filterSelect || !container) return;

      function normalize(str) {
        return (str || '').toLowerCase().replace(/\s+/g, ' ').trim();
      }

      function filterPlans() {
        const search = normalize(searchInput.value);
        const validity = filterSelect.value;
        const cards = container.querySelectorAll('.subscription-card-wrapper');
        let visibleCount = 0;
        cards.forEach(card => {
          const title = normalize(card.querySelector('.card-title')?.textContent);
          const desc = normalize(card.querySelector('p.text-muted')?.textContent);
          const valid = card.querySelector('.info-value')?.textContent.match(/\d+/)?.[0] || '';
          let show = true;
          if (search && !(title.includes(search) || desc.includes(search))) show = false;
          if (validity && valid !== validity) show = false;
          card.style.display = show ? '' : 'none';
          if (show) visibleCount++;
        });
        // Update count
        const countDiv = document.querySelector('.subscription-count');
        if (countDiv) countDiv.textContent = `${visibleCount} Available Plan${visibleCount === 1 ? '' : 's'}`;
      }
      searchInput.addEventListener('input', filterPlans);
      filterSelect.addEventListener('change', filterPlans);
    })();
    </script>
</body>
</html>
