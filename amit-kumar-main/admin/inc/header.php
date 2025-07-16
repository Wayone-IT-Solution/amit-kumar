<?php
// ✅ Correct path to db.php
require_once '../inc/db.php';
// ✅ Fetch latest order ID
$stmt = $conn->query("SELECT MAX(id) AS latest_id FROM orders");
$lastOrderId = $stmt->fetchColumn();
?>

<!-- ✅ Audio element -->
<audio id="orderAlertSound" src="/amit-kumar/admin/assets/alert.mp3" preload="auto"></audio>

<!-- ✅ Order Notification Toast -->
<div id="order-toast" style="display:none; position:fixed; top:20px; right:20px; background:#198754; color:#fff; padding:12px 20px; border-radius:6px; z-index:9999;">
    🛒 <strong>New order received!</strong>
    <a href="/amit-kumar/admin/orders-list" style="color:#fff; text-decoration:underline;">View</a>
</div>

<!-- ✅ JS Script to Check New Orders -->
<script>
let lastOrderId = <?= $lastOrderId ?: 0 ?>;

function playSound() {
    const sound = document.getElementById('orderAlertSound');
    if (sound) {
        sound.pause();
        sound.currentTime = 0;
        sound.play().catch(err => console.warn("Autoplay blocked:", err));
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
            if (!data || typeof data.latest_id === 'undefined' || isNaN(parseInt(data.latest_id))) return;
            const newId = parseInt(data.latest_id);
            if (newId > lastOrderId) {
                playSound();
                showPopup();
                // Voice notification
                if ('speechSynthesis' in window) {
                    const msg = new SpeechSynthesisUtterance('New order received on Amit Dairy and Sweets.');
                    msg.lang = 'en-IN';
                    window.speechSynthesis.speak(msg);
                }
                lastOrderId = newId;
            }
        })
        .catch(err => {/* silent fail, do not break page */});
}

// 🔁 Check every 5 seconds
setInterval(checkNewOrders, 2000);
</script>

 
 <header id="page-topbar">
    <div class="layout-width">
        <div class="navbar-header">
            <div class="d-flex">
                <!-- LOGO -->
                <div class="navbar-brand-box horizontal-logo">
                    <a href="dashboard" class="logo logo-dark">
                        <span class="logo-sm">
                            <img src="assets/images/logo.webp" alt="" height="22">
                        </span>
                        <span class="logo-lg">
                            <img src="assets/images/logo.webp" alt="" height="17">
                        </span>
                    </a>

                    <a href="dashboard" class="logo logo-light">
                        <span class="logo-sm">
                            <img src="assets/images/logo.webp" alt="" height="22">
                        </span>
                        <span class="logo-lg">
                            <img src="assets/images/logo.webp" alt="" height="17">
                        </span>
                    </a>
                </div>

                <button type="button" class="btn btn-sm px-3 fs-16 header-item vertical-menu-btn topnav-hamburger" id="topnav-hamburger-icon">
                    <span class="hamburger-icon">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                </button>

                <!-- App Search-->
                
            </div>

            <div class="d-flex align-items-center">

                
               

              



                <div class="dropdown ms-sm-3 header-item topbar-user">
                    <button type="button" class="btn" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="d-flex align-items-center">
                            <img class="rounded-circle header-profile-user" src="assets/images/users/avatar-1.jpg" alt="Header Avatar">
                            <span class="text-start ms-xl-2">
                                <span class="d-none d-xl-inline-block ms-1 fw-medium user-name-text">Amit Dairy</span>
                                <span class="d-none d-xl-block ms-1 fs-12 user-name-sub-text">Admin</span>
                            </span>
                        </span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <!-- item-->
                        <h6 class="dropdown-header">Welcome Admin!</h6>
                        
                        <a class="dropdown-item" href="change-credentials"><i class="mdi mdi-key-outline text-muted fs-16 align-middle me-1"></i> <span class="align-middle" data-key="t-change-password">Change Credentials</span></a>
                        <a class="dropdown-item" href="logout"><i class="mdi mdi-logout text-muted fs-16 align-middle me-1"></i> <span class="align-middle" data-key="t-logout">Logout</span></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>


        <!-- ========== App Menu ========== -->
        <div class="app-menu navbar-menu">
            <!-- LOGO -->
            <div class="navbar-brand-box">
                <!-- Dark Logo-->
                <a href="dashboard" class="logo logo-dark">
                    <span class="logo-sm">
                        <img src="assets/images/logo.webp" alt="" height="100px" width="100px">
                    </span>
                    <span class="logo-lg">
                        <img src="assets/images/logo.webp" alt="" height="10px" width="100px">
                    </span>
                </a>
                <!-- Light Logo-->
                <a href="dashboard" class="logo logo-light">
                    <span class="logo-sm">
                        <img src="assets/images/logo.webp" alt="" height="100px" width="100px">
                    </span>
                    <span class="logo-lg">
                        <img src="assets/images/logo.webp" alt="" height="100px" width="100px">
                    </span>
                </a>
                <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover" id="vertical-hover">
                    <i class="ri-record-circle-line"></i>
                </button>
            </div>

            <div id="scrollbar">
                <div class="container-fluid">

                    <div id="two-column-menu">
                    </div>
                    <ul class="navbar-nav" id="navbar-nav">
                        <li class="menu-title"><span data-key="t-menu">Menu</span></li>
                        
                        <li class="nav-item">
                            <a class="nav-link menu-link" href="dashboard">
                                <i class="ri-dashboard-2-line"></i> <span data-key="t-widgets">Dashboard</span>
                            </a>
                        </li>
                       

                       

                        <li class="nav-item">
                            <a class="nav-link menu-link" href="#manageMembers" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="manageMembers">
                                <i class="ri-pages-line"></i> <span data-key="t-pages">Manage Members</span>
                            </a>
                            <div class="collapse menu-dropdown" id="manageMembers">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a href="customers" class="nav-link" data-key="t-starter"> Customer </a>
                                    </li>
                                    
                                    <li class="nav-item">
                                        <a href="customer-enquiries" class="nav-link" data-key="t-team"> Contact Us Enquiries </a>
                                    </li>
                                   
                                </ul>
                            </div>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link menu-link" href="#pageContent" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="pageContent">
                                <i class="ri-rocket-line"></i> <span data-key="t-landing">Page Content</span>
                            </a>
                            <div class="collapse menu-dropdown" id="pageContent">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a href="testimonial" class="nav-link" data-key="t-one-page"> Testimonials </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="banners" class="nav-link" data-key="t-one-page"> Banners </a>
                                    </li>
                                    
                                    
                                    <li class="nav-item">
                                        <a href="desclaimer" class="nav-link" data-key="t-job">Disclaimer</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="terms-and-conditions" class="nav-link" data-key="t-job">Terms & Condition</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="privacy-policy" class="nav-link" data-key="t-job">Privacy Policy</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="refund-policy" class="nav-link" data-key="t-job">Refund Policy</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="return-policy" class="nav-link" data-key="t-job">Return Policy</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="faq" class="nav-link" data-key="t-job">FAQs</a>
                                    </li>
                                    
                                    
                                </ul>
                            </div>
                        </li>

                         <li class="nav-item">
                            <a class="nav-link menu-link" href="#orders" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="orders">
                                <i class="ri-rocket-line"></i> <span data-key="t-landing">Orders</span>
                            </a>
                            <div class="collapse menu-dropdown" id="orders">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a href="new-orders" class="nav-link" data-key="t-one-page"> New Orders </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="orders-list" class="nav-link" data-key="t-nft-landing"> Orders List </a>
                                    </li>
                                     <li class="nav-item">
                                        <a href="ush-orders-list" class="nav-link" data-key="t-job">Two Days Reamaining Orders</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="ready-to-dispatch" class="nav-link" data-key="t-job">Ready To Dispatch</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="cancelled-orders" class="nav-link" data-key="t-job">Cancelled</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="delivered-orders" class="nav-link" data-key="t-job">Delivered</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="date_time" class="nav-link" data-key="t-job">Date & Time</a>
                                    </li>
                                   <li class="nav-item">
                                     <a href="admin_min_order" class="nav-link" data-key="t-job">
                                          Minimum Set Amount 
                                     </a>
                                    </li>

                                    </li>
                                </ul>
                            </div>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link menu-link" href="#product" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="product">
                                <i class="ri-stack-line"></i> <span data-key="t-advance-ui">Product</span>
                            </a>
                            <div class="collapse menu-dropdown" id="product">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a href="category" class="nav-link" data-key="t-sweet-alerts">Category</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="subcategory" class="nav-link" data-key="t-nestable-list">Sub category</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="product" class="nav-link" data-key="t-nestable-list">Products</a>
                                    </li>
                                  

                                    <li class="nav-item">
                                        <a href="boxes" class="nav-link" data-key="t-nestable-list">Boxes</a>
                                    </li>
                                    
                                   
                                </ul>
                            </div>
                        </li>
<!-- subscription management -->
  <li class="nav-item">
  <a class="nav-link menu-link" href="#manageSubscriptionMenu" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="manageSubscriptionMenu">
    <i class="ri-pages-line"></i>
    <span data-key="t-pages">Manage Subscription</span>
  </a>
  <div class="collapse menu-dropdown" id="manageSubscriptionMenu">
    <ul class="nav nav-sm flex-column">
      <li class="nav-item">
        <a href="subscription" class="nav-link" data-key="t-starter">Admin Subscription</a>
      </li>
      <li class="nav-item">
        <a href="subscription-list" class="nav-link" data-key="t-team">User Subscription List</a>
      </li>
    </ul>
  </div>
</li>

                        <li class="nav-item">
                            <a class="nav-link" href="contact-details">
                                <i class="ri-contacts-book-3-line"></i> <span data-key="t-advance-ui">Website Settings</span>
                            </a>
                           
                        </li>

                        <li class="nav-item">
                            <a class="nav-link" href="delivery_locations">
                            <i class="ri-focus-3-line"></i> <span data-key="t-advance-ui">Delivery Locations</span>
                            </a>
                           
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="change-credentials">
                            <i class="mdi mdi-key-outline"></i> <span data-key="t-advance-ui">Change Credentials</span>
                            </a>
                           
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="logout">
                            <i class="ri-logout-box-r-line"></i> <span data-key="t-advance-ui">Logout</span>
                            </a>
                           
                        </li>   

                        
                        


                    </ul>
                </div>
                <!-- Sidebar -->
            </div>

            <div class="sidebar-background"></div>
        </div>