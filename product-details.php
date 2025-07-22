<?php
session_start();
error_reporting(0);
ini_set('display_errors', 0); 

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>About - Amit Dairy & Sweets</title>
  <meta name="description" content="">
  <meta name="keywords" content="">

  <!-- Favicons -->
  <link href="assets/img/logo.webp" rel="icon">
  <link href="assets/img/logo.webp" rel="apple-touch-icon">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Montserrat:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
    rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />

<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Buy Now button functionality -->
<script>
console.log('Script starting...');

// Try to define the function immediately
try {
  // Define the function both ways to ensure it's available
  function testBuyNowClick(button) {
    console.log('Buy Now button clicked!');
    
    const productId = button.dataset.productId;
    const categoryId = button.dataset.categoryId;
    const price = parseFloat(button.dataset.price || 0);
    
    console.log('Product ID:', productId, 'Category ID:', categoryId, 'Price:', price);
    
    // Set global variables
    window.isBuyNow = true;
    window.buyNowProductId = productId;
    window.customBoxText = '';
    window.buyNowQty = 1;

    // Fetch minimum order amount and boxes
    Promise.all([
      fetch('inc/fetch_settings.php?type=min_order').then(res => res.json()).catch(() => ({ min_order_amount: 1500 })),
      fetch('inc/fetch_boxes', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `category_id=${categoryId}`
      }).then(res => res.json()).catch(() => [])
    ])
    .then(([settings, boxes]) => {
      console.log('Settings:', settings, 'Boxes:', boxes);
      const minAmount = parseFloat(settings.min_order_amount || 1500);
      const productPrice = price;
      
      // Check if current order meets minimum amount
      const currentTotal = productPrice * window.buyNowQty;
      
      if (currentTotal < minAmount) {
        // Show quantity adjustment popup
        Swal.fire({
          icon: 'warning',
          title: 'Minimum Order Amount Required',
          html: `
            <div style="margin-bottom:10px;">
              Your order total is ₹<span id='swalTotal'>${currentTotal.toFixed(2)}</span>.<br>
              Minimum order amount is ₹${minAmount.toFixed(2)}.
            </div>
            <p>Please increase quantity to meet the minimum order amount.</p>
            <div class="d-flex justify-content-center align-items-center mb-3">
              <button type="button" id="decQtyBtn" class="btn btn-outline-secondary px-3 me-2">−</button>
              <input type="number" id="newQtyInput" class="form-control text-center" value="${window.buyNowQty}" min="1" style="width: 80px;">
              <button type="button" id="incQtyBtn" class="btn btn-outline-secondary px-3 ms-2">+</button>
            </div>
          `,
          showCancelButton: true,
          confirmButtonText: 'Continue',
          cancelButtonText: 'Cancel',
          customClass: {
            confirmButton: 'btn btn-warning me-3',
            cancelButton: 'btn btn-secondary'
          },
          buttonsStyling: false,
          didOpen: () => {
            const input = document.getElementById('newQtyInput');
            const incBtn = document.getElementById('incQtyBtn');
            const decBtn = document.getElementById('decQtyBtn');
            const totalSpan = document.getElementById('swalTotal');
            
            incBtn.addEventListener('click', () => {
              input.value = parseInt(input.value) + 1;
              totalSpan.textContent = (parseInt(input.value) * productPrice).toFixed(2);
            });
            
            decBtn.addEventListener('click', () => {
              if (parseInt(input.value) > 1) {
                input.value = parseInt(input.value) - 1;
                totalSpan.textContent = (parseInt(input.value) * productPrice).toFixed(2);
              }
            });
            
            input.addEventListener('input', () => {
              let val = parseInt(input.value) || 1;
              if (val < 1) val = 1;
              input.value = val;
              totalSpan.textContent = (val * productPrice).toFixed(2);
            });
          },
          preConfirm: () => {
            const newQty = parseInt(document.getElementById('newQtyInput').value);
            if (!newQty || newQty < 1) {
              Swal.showValidationMessage('Please enter a valid quantity');
              return false;
            }
            if (newQty * productPrice < minAmount) {
              Swal.showValidationMessage(`Total must be at least ₹${minAmount.toFixed(2)}`);
              return false;
            }
            return newQty;
          }
        }).then(result => {
          if (result.isConfirmed && result.value) {
            window.buyNowQty = result.value;
            window.buyNowProductQty = result.value; // Save as product quantity
            window.buyNowBoxQty = 1; // Default box quantity
            showBoxPopup(boxes);
          }
        });
      } else {
        showBoxPopup(boxes);
      }
    })
    .catch(error => {
      console.error('Error:', error);
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Something went wrong. Please try again.'
      });
    });
  }

  // Also attach to window object
  window.testBuyNowClick = testBuyNowClick;
  
  console.log('Buy Now functionality loaded');
  console.log('testBuyNowClick function available:', typeof testBuyNowClick);
  console.log('window.testBuyNowClick available:', typeof window.testBuyNowClick);
  console.log('SweetAlert2 available:', typeof Swal);
  
} catch (error) {
  console.error('Error loading Buy Now functionality:', error);
}

// Fallback: Try to define the function again after page loads
document.addEventListener('DOMContentLoaded', function() {
  console.log('DOM loaded, checking function availability...');
  if (typeof window.testBuyNowClick !== 'function') {
    console.log('Function not found, redefining...');
    window.testBuyNowClick = function(button) {
      console.log('Buy Now button clicked (fallback)!');
      const productId = button.dataset.productId;
      const categoryId = button.dataset.categoryId;
      const price = parseFloat(button.dataset.price || 0);
      
      // Set global variables
      window.isBuyNow = true;
      window.buyNowProductId = productId;
      window.customBoxText = '';
      window.buyNowQty = 1;

      // Fetch minimum order amount and boxes
      Promise.all([
        fetch('inc/fetch_settings.php?type=min_order').then(res => res.json()).catch(() => ({ min_order_amount: 1500 })),
        fetch('inc/fetch_boxes', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `category_id=${categoryId}`
        }).then(res => res.json()).catch(() => [])
      ])
      .then(([settings, boxes]) => {
        console.log('Settings:', settings, 'Boxes:', boxes);
        const minAmount = parseFloat(settings.min_order_amount || 1500);
        const productPrice = price;
        
        // Check if current order meets minimum amount
        const currentTotal = productPrice * window.buyNowQty;
        
        if (currentTotal < minAmount) {
          // Show quantity adjustment popup
          Swal.fire({
            icon: 'warning',
            title: 'Minimum Order Amount Required',
            html: `
              <div style="margin-bottom:10px;">
                Your order total is ₹<span id='swalTotal'>${currentTotal.toFixed(2)}</span>.<br>
                Minimum order amount is ₹${minAmount.toFixed(2)}.
              </div>
              <p>Please increase quantity to meet the minimum order amount.</p>
              <div class="d-flex justify-content-center align-items-center mb-3">
                <button type="button" id="decQtyBtn" class="btn btn-outline-secondary px-3 me-2">−</button>
                <input type="number" id="newQtyInput" class="form-control text-center" value="${window.buyNowQty}" min="1" style="width: 80px;">
                <button type="button" id="incQtyBtn" class="btn btn-outline-secondary px-3 ms-2">+</button>
              </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Continue',
            cancelButtonText: 'Cancel',
            customClass: {
              confirmButton: 'btn btn-warning me-3',
              cancelButton: 'btn btn-secondary'
            },
            buttonsStyling: false,
            didOpen: () => {
              const input = document.getElementById('newQtyInput');
              const incBtn = document.getElementById('incQtyBtn');
              const decBtn = document.getElementById('decQtyBtn');
              const totalSpan = document.getElementById('swalTotal');
              
              incBtn.addEventListener('click', () => {
                input.value = parseInt(input.value) + 1;
                totalSpan.textContent = (parseInt(input.value) * productPrice).toFixed(2);
              });
              
              decBtn.addEventListener('click', () => {
                if (parseInt(input.value) > 1) {
                  input.value = parseInt(input.value) - 1;
                  totalSpan.textContent = (parseInt(input.value) * productPrice).toFixed(2);
                }
              });
              
              input.addEventListener('input', () => {
                let val = parseInt(input.value) || 1;
                if (val < 1) val = 1;
                input.value = val;
                totalSpan.textContent = (val * productPrice).toFixed(2);
              });
            },
            preConfirm: () => {
              const newQty = parseInt(document.getElementById('newQtyInput').value);
              if (!newQty || newQty < 1) {
                Swal.showValidationMessage('Please enter a valid quantity');
                return false;
              }
              if (newQty * productPrice < minAmount) {
                Swal.showValidationMessage(`Total must be at least ₹${minAmount.toFixed(2)}`);
                return false;
              }
              return newQty;
            }
          }).then(result => {
            if (result.isConfirmed && result.value) {
              window.buyNowQty = result.value;
              window.buyNowProductQty = result.value; // Save as product quantity
              window.buyNowBoxQty = 1; // Default box quantity
              showBoxPopup(boxes);
            }
          });
        } else {
          window.buyNowProductQty = window.buyNowQty; // Set product quantity when no minimum check needed
          window.buyNowBoxQty = 1; // Default box quantity
          showBoxPopup(boxes);
        }
      })
      .catch(error => {
        console.error('Error:', error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Something went wrong. Please try again.'
        });
      });
    };
    console.log('Function redefined:', typeof window.testBuyNowClick);
  }
});

// Also try on window load
window.addEventListener('load', function() {
  console.log('Window loaded, final function check...');
  console.log('testBuyNowClick available:', typeof window.testBuyNowClick);
});
</script>

  <!-- Main CSS File -->
  <link href="assets/css/main.css" rel="stylesheet">

</head>

<body class="index-page">

  <?php include ('inc/header.php');
  include_once ('inc/contact_data.php');
  ?>
<?php
require_once 'inc/db.php'; // PDO connection

// Validate and fetch product_id from URL
$productId = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
if ($productId <= 0) {
    die("Invalid product ID.");
}

try {
    // Fetch product details along with category_id
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        die("Product not found.");
    }

    // Fetch category name using category_id
    $categoryName = 'Unknown';
    if (!empty($product['category_id'])) {
        $catStmt = $conn->prepare("SELECT title FROM categories WHERE id = ?");
        $catStmt->execute([$product['category_id']]);
        $category = $catStmt->fetch(PDO::FETCH_ASSOC);

        if ($category) {
            $categoryName = $category['title'];
        }
    }

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>


  <main class="main">



  <section class="product-details section">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-lg-6 mb-4 mb-lg-0">
       <div class="product-image position-relative">
  <!-- Product Image -->
  <img src="admin/<?= htmlspecialchars($product['product_image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">

  <!-- Wishlist Button (top-left corner) -->
  <form method="post" action="inc/add_to_wishlist" class="wishlist-form position-absolute" style="top: 5px; left: 5px; margin: 0;">
    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
    <?php $inWishlist = in_array($product['id'], $_SESSION['wishlist'] ?? []); ?>
    <button type="submit" class="btn p-0 border-0 bg-transparent wishlist-btn" title="Add to Wishlist">
      <i class="bi <?= $inWishlist ? 'bi-heart-fill' : 'bi-heart' ?> fs-3 text-white"></i>
    </button>
  </form>
</div>

      </div>
      <div class="col-lg-6">
        <div class="product-info">
          <h2 style="display: flex; align-items: center; justify-content: space-between;">
  
</h2>

          <h2><?= htmlspecialchars($product['name']) ?></h2>

          <div class="product-spec mb-3">
            <h4>Product Code: <?= htmlspecialchars($product['product_code']) ?></h4>
            <h4>Price: <del>₹<?= htmlspecialchars($product['price']) ?></del></h4>
            <h4>Discounted Price: ₹<?= htmlspecialchars($product['discount_price']) ?></h4>
            <h4>
            Quantity: <?= htmlspecialchars($product['weight']) ?><?= !empty($product['weight_type']) ? ' (' . htmlspecialchars($product['weight_type']) . ')' : '' ?>
          </h4>

            <h4>Category: <?= htmlspecialchars($categoryName) ?></h4>
          </div>

          <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
        </div>

        <div class="container d-flex gap-4 mt-4 px-0">
          <button class="btn-heritage btn-primary add-to-cart-btn" data-product-id="<?php echo $product['id']; ?>" 
          data-category-id="<?php echo $product['category_id']; ?>">
            <i class="bi bi-cart-plus me-2"></i>Add to Cart
          </button>
          <button 
            type="button" 
            class="btn-heritage btn-success buy-now-btn" 
            data-product-id="<?= $product['id']; ?>" 
            data-category-id="<?= $product['category_id']; ?>"
            data-price="<?= $product['discount_price']; ?>"
            onclick="if(typeof testBuyNowClick === 'function') { testBuyNowClick(this); } else { console.error('Function not found'); alert('Buy Now function not loaded. Please refresh the page.'); }">
            <i class="bi bi-lightning me-2"></i>Buy Now
          </button>
        </div>
      </div>
    </div>
  </div>
</section>
<style>
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
.form-control {
  background-color: #D6B66933;
}

/* Payment Methods Styles */
.payment-methods {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.payment-option {
  position: relative;
}

.payment-method-card {
  display: flex;
  align-items: center;
  gap: 15px;
  padding: 15px;
  border: 2px solid #e9ecef;
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.3s ease;
  background: white;
}

.payment-method-card:hover {
  border-color: #d1a94a;
  background-color: #fff8e1;
}

.btn-check:checked + .payment-method-card {
  border-color: #d1a94a;
  background-color: #fff8e1;
  box-shadow: 0 0 0 0.2rem rgba(209, 169, 74, 0.25);
}

.payment-icon {
  width: 50px;
  height: 50px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f8f9fa;
  border-radius: 10px;
  font-size: 1.5rem;
}

.payment-details {
  flex: 1;
}

.payment-name {
  font-weight: 600;
  color: #333;
  margin-bottom: 2px;
}

.payment-desc {
  font-size: 0.85rem;
  color: #666;
}

.payment-fee {
  font-weight: 600;
  color: #d1a94a;
  font-size: 1.1rem;
}

/* Box Selection Styles */
.box-card {
  transition: all 0.3s ease;
  border: 2px solid #e9ecef;
}

.box-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 25px rgba(209, 169, 74, 0.15);
  border-color: #d1a94a;
}

.box-card.selected {
  border-color: #d1a94a;
  background: linear-gradient(135deg, #fff8e1 0%, #fff3cd 100%);
  box-shadow: 0 8px 25px rgba(209, 169, 74, 0.2);
  transform: translateY(-5px);
}

.box-card img {
  transition: all 0.3s ease;
}

.box-card:hover img {
  transform: scale(1.05);
}

.box-card .box_title {
  transition: color 0.3s ease;
}

.box-card:hover .box_title {
  color: #d1a94a;
}

.box-card .box_price {
  transition: all 0.3s ease;
}

.box-card:hover .box_price {
  background-color: #d1a94a !important;
  color: white !important;
}

/* Enhanced Modal Styles */
.modal-content {
  border: none;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
}

.modal-header {
  background: linear-gradient(135deg, #d1a94a 0%, #c19a3a 100%);
  color: white;
  border-bottom: none;
}

.modal-title {
  font-weight: 700;
  font-size: 1.3rem;
}

.btn-warning {
  background: linear-gradient(135deg, #d1a94a 0%, #c19a3a 100%);
  border: none;
  box-shadow: 0 4px 15px rgba(209, 169, 74, 0.3);
  transition: all 0.3s ease;
}

.btn-warning:hover {
  background: linear-gradient(135deg, #c19a3a 0%, #b08a2a 100%);
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(209, 169, 74, 0.4);
}

/* Ensure Buy Now button is clickable */
.buy-now-btn {
  cursor: pointer !important;
  pointer-events: auto !important;
  position: relative !important;
  z-index: 1 !important;
}

.buy-now-btn:hover {
  cursor: pointer !important;
}

.buy-now-btn:active {
  cursor: pointer !important;
}
</style>

<?php
$receiver_name = '';
$receiver_phone = '';

// Check if user is logged in
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT fullname, phone FROM users WHERE id = :id");
    $stmt->execute(['id' => $userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $receiver_name = $user['fullname'];
        $receiver_phone = $user['phone'];
    }
}
?>
<!-- this is calender php -->
<?php
require_once 'inc/db.php';

// Fetch blocked_date from blocked_slots
$blockedDates = [];
$stmt = $conn->query("SELECT DISTINCT blocked_date FROM blocked_slots");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $blockedDates[] = $row['blocked_date']; // Format: YYYY-MM-DD
}
$blockedJson = json_encode($blockedDates); // Convert to JS array
?>


 <!-- end -->
 <?php
// Fetch unique time slots
$stmt = $conn->query("
    SELECT DISTINCT start_time, end_time 
    FROM blocked_slots 
    WHERE start_time IS NOT NULL AND end_time IS NOT NULL 
    ORDER BY start_time
");

$timeSlots = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!-- Enhanced Box Selection Modal -->
<div class="modal fade" id="boxSelectModal" tabindex="-1" aria-labelledby="boxSelectModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow rounded-4">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title fw-bold">
          <i class="bi bi-box-seam me-2"></i>
          Choose Your Sweet Box
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4" id="box-options">
        <!-- Box options will be injected here -->
      </div>
    </div>
  </div>
</div>

<!-- Custom Box Modal -->
<div class="modal fade" id="customBoxModal" tabindex="-1" aria-labelledby="customBoxModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow rounded-4">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title fw-bold">
          <i class="bi bi-pencil-square me-2"></i>
          Customize Your Box
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form id="customBoxForm">
          <div class="mb-3">
            <label class="form-label fw-semibold">If you want any custom text on box:</label>
            <textarea 
              class="form-control" 
              id="customBoxText" 
              rows="4" 
              maxlength="250"
              required
            ></textarea>
            <div class="form-text text-muted">
              <small>Maximum 250 characters</small>
            </div>
          </div>
          <div class="d-flex gap-2 justify-content-end">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-warning fw-semibold">Save Custom Box</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Sweet Box Quantity Modal -->
<div class="modal fade" id="sweetBoxQtyModal" tabindex="-1" aria-labelledby="sweetBoxQtyModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow rounded-4">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title fw-bold">
          <i class="bi bi-box-seam me-2"></i>
          Sweet Box Quantity
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <form id="sweetBoxQtyForm">
          <div class="mb-3">
            <label class="form-label fw-semibold">How many boxes do you need?</label>
            <input 
              type="number" 
              class="form-control" 
              id="sweetBoxQty" 
              min="1" 
              value="1" 
              required
            >
          </div>
          <div class="d-flex gap-2 justify-content-end">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-warning fw-semibold">Confirm Quantity</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Checkout Modal -->
<div class="modal fade" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form id="placeOrderForm" method="post" class="modal-content border-0 shadow rounded-4 p-4 bg-white">
      <!-- Only one set of hidden inputs at the top of the form -->
      <input type="hidden" id="modal_product_id" name="product_id">
      <input type="hidden" id="modal_box_id" name="box_id">
      <input type="hidden" id="modal_box_name" name="box_name">
      <input type="hidden" id="modal_box_price" name="box_price">
      <input type="hidden" id="modal_box_image" name="box_image">
      <input type="hidden" id="modal_custom_box_text" name="custom_box_text">
      <input type="hidden" id="modal_quantity" name="quantity">
      <input type="hidden" name="box_qty" id="modal_number_of_boxes" value="1">
      <!-- Remove all payment method radio buttons and their labels from the modal form -->

      <div class="modal-body p-0">
        <div class="mb-4">
          <div class="d-flex align-items-start">
            <div class="me-2"><i class="bi bi-geo-alt-fill text-warning fs-4"></i></div>
            <div>
              <div class="fw-bold" id="modal-title">Add Address</div>
              <div class="text-muted small">Please add your delivery details below</div>
            </div>
          </div>
        </div>
        <input type="hidden" name="box_name" id="modal_box_name">
<input type="hidden" name="box_price" id="modal_box_price">
<input type="hidden" name="custom_box_text" id="modal_custom_box_text">
<input type="hidden" name="box_image" id="modal_box_image">



<input type="hidden" name="quantity" id="modal_quantity" value="1">
<input type="hidden" name="box_qty" id="modal_number_of_boxes" value="1">
        <div class="mb-3">
          <input type="text" class="form-control border-0 rounded-3" name="address_details" placeholder="Add Detailed Address" required>
        </div>

        <div class="mb-3">
          <label class="form-label small">House / Flat / Block No.</label>
          <input type="text" class="form-control border-0 rounded-3" name="house_block" required>
        </div>

        <div class="mb-3">
          <label class="form-label small">Apartment / Road / Area</label>
          <input type="text" class="form-control border-0 rounded-3" name="area_road" required>
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
         
<!-- calender -->
<div class="mb-3">
    <label class="form-label small">Delivery Date</label>

  <input
    type="text"
    class="form-control border-0 rounded-3"
    name="delivery_date"
    id="delivery_date"
    required
    placeholder="Select a date">
</div>

<div class="mb-4">
  <label class="form-label small">Delivery Time</label>
  <select class="form-control border-0 rounded-3" name="delivery_time" id="delivery_time" required>
    <option value="">Select Delivery Time</option>
    <?php foreach ($timeSlots as $time): ?>
      <?php
        $startFormatted = date("g:i A", strtotime($time['start_time']));
        $endFormatted = date("g:i A", strtotime($time['end_time']));
        $label = "$startFormatted - $endFormatted";
        $value = $time['start_time'] . '-' . $time['end_time']; // example: 10:00:00-18:00:00
      ?>
      <option value="<?= htmlspecialchars($value) ?>"><?= $label ?></option>
    <?php endforeach; ?>
  </select>
</div>
<?php


$stmt = $conn->query("SELECT DISTINCT blocked_date FROM blocked_slots WHERE blocked_date IS NOT NULL AND blocked_date != ''");
$blockedDatesRaw = $stmt->fetchAll(PDO::FETCH_COLUMN);

// ✅ Encode for JS
$blockedDates = json_encode($blockedDatesRaw);
?>




<!-- Include Flatpickr and SweetAlert2 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // ✅ Blocked dates from PHP
    const blockedDates = <?= $blockedDates ?>; // Example: ["2025-07-12", "2025-07-15"]

    console.log(blockedDates , "intail bloced dates");
    // ✅ Add today + 2 days to blocked list
    const today = new Date();
    const plusOne = new Date(today);
    plusOne.setDate(today.getDate() + 1);
    console.log(plusOne,'plusOne');

    const plusTwo = new Date(today);
    plusTwo.setDate(today.getDate()+ 2);
    console.log(plusTwo,'plusTwo');


    const formatDate = d => d.toISOString().split('T')[0];

    console.log(formatDate);


    blockedDates.push(formatDate(today));
    blockedDates.push(formatDate(plusOne));
    console.log(blockedDates,'blocked dates are as');

    flatpickr("#delivery_date", {
    dateFormat: "Y-m-d",
    minDate: new Date(),
    disable: blockedDates, // only block exact dates
    onDayCreate: function (_, __, ___, dayElem) {
        const dateObj = dayElem.dateObj;

        // ✅ Format date in local timezone (no UTC shift)
        const dateStr = dateObj.getFullYear() + '-' +
            String(dateObj.getMonth() + 1).padStart(2, '0') + '-' +
            String(dateObj.getDate()).padStart(2, '0');

        // ✅ Match with blockedDates (passed from PHP)
        if (blockedDates.includes(dateStr)) {
            dayElem.classList.add("blocked-date");
            dayElem.style.backgroundColor = "#f8d7da";
            dayElem.style.color = "#842029";
            dayElem.style.border = "1px solid #dc3545";
            dayElem.style.opacity = "0.6";
            dayElem.style.cursor = "not-allowed";

            // Disable selection with SweetAlert
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
</script>

<style>
.blocked-date {
    font-weight: bold;
    text-decoration: line-through;
}
</style>
        <!-- Receiver Info -->
        <div class="mb-3">
          <label class="form-label small">Receiver's Name</label>
          <input type="text" class="form-control border-0 rounded-3" name="receiver_name" value="<?= htmlspecialchars($receiver_name) ?>" required>
        </div>

        <div class="mb-4">
          <label class="form-label small">Receiver's Phone Number</label>
          <input type="text" class="form-control border-0 rounded-3" name="receiver_phone" value="<?= htmlspecialchars($receiver_phone) ?>" required>
        </div>
       
       

        <!-- Submit Button -->
        <div class="d-grid">
          <button type="submit" class="btn rounded-pill text-white fw-semibold" style="background-color: #d1ae5e;">
            Place Order
          </button>
        </div>
      </div>
    </form>
  </div>
</div>
<script>
document.getElementById('placeOrderForm').addEventListener('submit', function (e) {
  e.preventDefault();

  const form = this;
  const submitBtn = form.querySelector('button[type="submit"]');
  const originalBtnText = submitBtn.innerHTML;
  submitBtn.disabled = true;
  submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';

  const quantityInput = document.getElementById('modal_quantity');
  const boxInput = document.getElementById('modal_number_of_boxes');
  const currentQty = parseInt(quantityInput.value) || 1;
  const currentBoxes = parseInt(boxInput.value) || 1;

  const formData = new FormData(form);
  console.log('Payment method:', formData.get('payment_method'));
  console.log('Form data entries:');
  for (let [key, value] of formData.entries()) {
    console.log(key + ': ' + value);
  }

  fetch('inc/place_order', {
    method: 'POST',
    body: formData
  })
    .then(res => {
      console.log('Response status: 825', res.status);
      return res.json();
    })
    .then(data => {
      console.log('Response data:', data);
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalBtnText;
      if (data.success) {
        console.log('Payment redirect URL:', data.payment_redirect);
        if (data.payment_redirect) {
          // Redirect to payment gateway
          Swal.fire({
            icon: 'info',
            title: 'Payment Required',
            text: data.message,
            confirmButtonText: 'Proceed to Payment',
            allowOutsideClick: false
          }).then(() => {
            console.log('Redirecting to:', data.payment_redirect);
            window.location.href = data.payment_redirect;
          });
        } else {
          // COD or wallet payment completed
          Swal.fire({
            icon: 'success',
            title: 'Order Placed!',
            html: `<p>${data.message}</p><strong>Delivery: Within 2 days</strong>`,
            confirmButtonText: 'OK',
            timer: 4000,
            timerProgressBar: true,
          }).then(() => {
            // Redirect to cart
            window.location.href = 'cart?payment=success&order_code=' + data.order_code + '&amount=' + data.subtotal;
          });
        }
      }
    })
    .catch(err => {
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalBtnText;
      Swal.fire({
        icon: 'error',
        title: 'Request Failed',
        text: err.message || 'Something went wrong.',
      });
    });
});

// Payment method selection for Buy Now
document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const paymentMethod = this.value;
        const paymentDetails = document.getElementById('paymentDetailsBuyNow');
        const codDetails = document.getElementById('codDetailsBuyNow');
        
        if (paymentMethod === 'cod') {
            paymentDetails.style.display = 'none';
            codDetails.style.display = 'block';
        } else {
            paymentDetails.style.display = 'block';
            codDetails.style.display = 'none';
        }
    });
});
</script>


  



<script>
console.log('Script starting...');


      

let customBoxText = '';
let isBuyNow = false;
let buyNowProductId = null;
let buyNowBoxId = null;
      let selectedQty = 1;
let selectedBoxName = '';
let selectedBoxPrice = '';

console.log('Variables initialized');

// Buy Now button functionality - Multiple approaches to ensure it works
function setupBuyNowButtons() {
  console.log('Setting up Buy Now buttons...');
  const buyNowButtons = document.querySelectorAll('.buy-now-btn');
  console.log('Found', buyNowButtons.length, 'Buy Now buttons');
  
  buyNowButtons.forEach((button, index) => {
    console.log('Setting up button', index, button);
    
    // Remove any existing event listeners
    button.removeEventListener('click', handleBuyNowClick);
    
    // Add new event listener
    button.addEventListener('click', handleBuyNowClick);
    
    // Also add a direct onclick as backup
    button.onclick = handleBuyNowClick;
  });
}

// Try multiple ways to ensure the script runs
document.addEventListener('DOMContentLoaded', setupBuyNowButtons);
window.addEventListener('load', setupBuyNowButtons);

// Also try immediately if DOM is already loaded
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', setupBuyNowButtons);
} else {
  setupBuyNowButtons();
}

// Test if SweetAlert2 is available
console.log('Testing SweetAlert2 availability...');
if (typeof Swal !== 'undefined') {
  console.log('SweetAlert2 is available');
} else {
  console.log('SweetAlert2 is NOT available');
}

// Test if we can find the button
setTimeout(() => {
  console.log('Checking for buttons after 1 second...');
  const buttons = document.querySelectorAll('.buy-now-btn');
  console.log('Found buttons:', buttons.length);
  buttons.forEach((btn, i) => {
    console.log(`Button ${i}:`, btn);
    console.log(`Button ${i} dataset:`, btn.dataset);
  });
}, 1000);

function handleBuyNowClick(e) {
  console.log('Buy Now button clicked via event listener!');
  e.preventDefault();
  
  const productId = this.dataset.productId;
  const categoryId = this.dataset.categoryId;
  const price = parseFloat('<?= $product['discount_price'] ?>');
  
  console.log('Product ID:', productId, 'Category ID:', categoryId, 'Price:', price);
  
  // Set global variables
  isBuyNow = true;
  buyNowProductId = productId;
  customBoxText = '';
  buyNowQty = 1;

  // Fetch minimum order amount and boxes
  Promise.all([
    fetch('inc/fetch_settings.php?type=min_order').then(res => res.json()).catch(() => ({ min_order_amount: 1500 })),
    fetch('inc/fetch_boxes', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `category_id=${categoryId}`
    }).then(res => res.json()).catch(() => [])
  ])
  .then(([settings, boxes]) => {
    console.log('Settings:', settings, 'Boxes:', boxes);
    const minAmount = parseFloat(settings.min_order_amount || 1500);
    const productPrice = price;
    
    // Check if current order meets minimum amount
    const currentTotal = productPrice * buyNowQty;
    
    if (currentTotal < minAmount) {
      // Show quantity adjustment popup
      Swal.fire({
        icon: 'warning',
        title: 'Minimum Order Amount Required',
        html: `
          <div style="margin-bottom:10px;">
            Your order total is ₹<span id='swalTotal'>${currentTotal.toFixed(2)}</span>.<br>
            Minimum order amount is ₹${minAmount.toFixed(2)}.
          </div>
          <p>Please increase quantity to meet the minimum order amount.</p>
          <div class="d-flex justify-content-center align-items-center mb-3">
            <button type="button" id="decQtyBtn" class="btn btn-outline-secondary px-3 me-2">−</button>
            <input type="number" id="newQtyInput" class="form-control text-center" value="${buyNowQty}" min="1" style="width: 80px;">
            <button type="button" id="incQtyBtn" class="btn btn-outline-secondary px-3 ms-2">+</button>
          </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Continue',
        cancelButtonText: 'Cancel',
        customClass: {
          confirmButton: 'btn btn-warning',
          cancelButton: 'btn btn-secondary'
        },
        buttonsStyling: false,
        didOpen: () => {
          const input = document.getElementById('newQtyInput');
          const incBtn = document.getElementById('incQtyBtn');
          const decBtn = document.getElementById('decQtyBtn');
          const totalSpan = document.getElementById('swalTotal');
          
          incBtn.addEventListener('click', () => {
            input.value = parseInt(input.value) + 1;
            totalSpan.textContent = (parseInt(input.value) * productPrice).toFixed(2);
          });
          
          decBtn.addEventListener('click', () => {
            if (parseInt(input.value) > 1) {
              input.value = parseInt(input.value) - 1;
              totalSpan.textContent = (parseInt(input.value) * productPrice).toFixed(2);
            }
          });
          
          input.addEventListener('input', () => {
            let val = parseInt(input.value) || 1;
            if (val < 1) val = 1;
            input.value = val;
            totalSpan.textContent = (val * productPrice).toFixed(2);
          });
        },
        preConfirm: () => {
          const newQty = parseInt(document.getElementById('newQtyInput').value);
          if (!newQty || newQty < 1) {
            Swal.showValidationMessage('Please enter a valid quantity');
            return false;
          }
          if (newQty * productPrice < minAmount) {
            Swal.showValidationMessage(`Total must be at least ₹${minAmount.toFixed(2)}`);
            return false;
          }
          return newQty;
        }
      }).then(result => {
        if (result.isConfirmed && result.value) {
          window.buyNowQty = result.value;
          window.buyNowProductQty = result.value; // Save as product quantity
          window.buyNowBoxQty = 1; // Default box quantity
          showBoxPopup(boxes);
        }
      });
    } else {
      showBoxPopup(boxes);
    }
  })
  .catch(error => {
    console.error('Error:', error);
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Something went wrong. Please try again.'
    });
  });
}

function showBoxPopup(boxes) {
  const boxOptions = document.getElementById('box-options');

  boxOptions.innerHTML = `
    <div class="text-center mb-4">
      <h5 class="fw-bold text-dark">Select a Sweet Box</h5>
      <p class="text-muted">Choose from our pre-made boxes or create your own custom box</p>
    </div>
    <div class="row g-3 justify-content-center">
      ${boxes.map(box => `
        <div class="col-md-3 col-sm-4 col-6">
          <div class="box-card text-center rounded border p-3 h-100" style="cursor: pointer; transition: all 0.3s ease;" data-box-id="${box.id}" data-box-name="${box.box_name}" data-box-price="${box.box_price}">
            <img src="admin/${box.box_image}" alt="${box.box_name}" class="img-fluid rounded mb-2" style="height: 80px; width: 80px; object-fit: cover;">
            <div class="box_title fw-semibold" title="${box.box_name}">${box.box_name}</div>
            <div class="box_price mt-1" style="background-color: #fef3c7; border-radius: 6px; padding: 4px 8px; display: inline-block; font-size: 12px;">₹ ${box.box_price}</div>
          </div>
        </div>
      `).join('')}
      <div class="col-md-3 col-sm-4 col-6">
        <div class="box-card text-center rounded border border-warning p-3 h-100" style="cursor: pointer; background: #fff8e1; transition: all 0.3s ease;" data-box-id="custom">
          <div class="d-flex align-items-center justify-content-center mb-2" style="height: 80px; background: #fffde7; border-radius: 8px;">
            <i class="bi bi-pencil-square text-warning fs-3"></i>
        </div>
          <div class="box_title text-warning fw-semibold">Custom Box</div>
          <div class="box_price text-muted" style="font-size: 12px;">Write your own</div>
      </div>
    </div>
    </div>
    <div class="text-center mt-4">
      <button id="confirmBoxBtn" class="btn btn-warning px-4 py-2 fw-semibold me-3" disabled>
        <i class="bi bi-arrow-right me-2"></i>Continue to Checkout
      </button>
      <button id="skipBoxBtn" class="btn btn-outline-secondary px-4 py-2">
        <i class="bi bi-x-circle me-2"></i>Skip Box
      </button>
    </div>
  `;

  const modal = new bootstrap.Modal(document.getElementById('boxSelectModal'));
  modal.show();

      let selectedBoxId = null;

      setTimeout(() => {
        document.querySelectorAll('.box-card').forEach(option => {
          option.addEventListener('click', async () => {
            // Remove previous selections
            document.querySelectorAll('.box-card').forEach(o => {
              o.classList.remove('border-primary', 'border-3', 'shadow', 'selected');
              o.style.transform = 'scale(1)';
            });
            
            // Highlight selected option
            option.classList.add('border-primary', 'border-3', 'shadow', 'selected');
            option.style.transform = 'scale(1.05)';
            
            selectedBoxId = option.dataset.boxId;

            selectedBoxName = option.querySelector('.box_title')?.textContent.trim() || '-';
            selectedBoxPrice = option.querySelector('.box_price')?.textContent.replace(/[^\d.]/g, '') || '0';

            if (selectedBoxId === 'custom') {
              const bootstrapModal = bootstrap.Modal.getInstance(document.getElementById('boxSelectModal'));
              bootstrapModal.hide();

              // Create custom text input modal
              const customModal = document.createElement('div');
              customModal.className = 'modal fade';
              customModal.id = 'customTextModal';
              customModal.innerHTML = `
                <div class="modal-dialog modal-dialog-centered">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title">Customize Your Box</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                      <label class="form-label">If you want any custom text on box:</label>
                      <textarea id="customTextInput" class="form-control" rows="3" maxlength="250" placeholder="Enter your custom message..."></textarea>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                      <button type="button" class="btn btn-warning" id="saveCustomTextBtn">Save</button>
                    </div>
                  </div>
                </div>
              `;
              
              document.body.appendChild(customModal);
              const customModalInstance = new bootstrap.Modal(customModal);
              customModalInstance.show();
              
                                            document.getElementById('saveCustomTextBtn').addEventListener('click', () => {
                 const text = document.getElementById('customTextInput').value.trim();
                 if (!text) {
                   Swal.fire({
                     icon: 'error',
                     title: 'Invalid Input',
                     text: 'Please enter something!'
                   });
                   return;
                 }
                
                customBoxText = text;
                buyNowQty = 1;
                document.getElementById('confirmBoxBtn').disabled = false;
                customModalInstance.hide();
                document.body.removeChild(customModal);
                bootstrapModal.show();
              });
              
              customModal.addEventListener('hidden.bs.modal', () => {
                if (document.body.contains(customModal)) {
                  document.body.removeChild(customModal);
                }
                option.classList.remove('border-primary', 'border-3', 'shadow', 'selected');
                selectedBoxId = null;
                customBoxText = '';
                document.getElementById('confirmBoxBtn').disabled = true;
              });

            } else {
              // Create quantity input modal
              const qtyModal = document.createElement('div');
              qtyModal.className = 'modal fade';
              qtyModal.id = 'qtyModal';
              qtyModal.innerHTML = `
                <div class="modal-dialog modal-dialog-centered">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title">How many boxes Need?</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                      <input type="number" id="qtyInput" class="form-control" placeholder="Enter quantity" min="1" step="1" value="1">
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                      <button type="button" class="btn btn-warning" id="confirmQtyBtn">Confirm</button>
                    </div>
                  </div>
                </div>
              `;
              
              document.body.appendChild(qtyModal);
              const qtyModalInstance = new bootstrap.Modal(qtyModal);
              qtyModalInstance.show();
              
              document.getElementById('qtyInput').focus();
              
                                            document.getElementById('confirmQtyBtn').addEventListener('click', () => {
                 const qtyVal = parseInt(document.getElementById('qtyInput').value);
                 if (!qtyVal || qtyVal <= 0) {
                   Swal.fire({
                     icon: 'error',
                     title: 'Invalid Quantity',
                     text: 'Please enter a valid quantity'
                   });
                   return;
                 }
                
                buyNowQty = qtyVal;
                document.getElementById('confirmBoxBtn').disabled = false;
                qtyModalInstance.hide();
                document.body.removeChild(qtyModal);
              });
              
              qtyModal.addEventListener('hidden.bs.modal', () => {
                if (document.body.contains(qtyModal)) {
                  document.body.removeChild(qtyModal);
                }
                option.classList.remove('border-primary', 'border-3', 'shadow', 'selected');
                selectedBoxId = null;
                buyNowQty = 1;
                document.getElementById('confirmBoxBtn').disabled = true;
              });
            }
          });
        });

        document.getElementById('confirmBoxBtn').onclick = () => {
          if (window.isBuyNow) {
            window.buyNowBoxId = selectedBoxId;
            modal.hide();
            setTimeout(() => {
              document.getElementById('modal_product_id').value = window.buyNowProductId;
              document.getElementById('modal_box_id').value = window.buyNowBoxId;
              document.getElementById('modal_box_name').value = window.selectedBoxName;
              document.getElementById('modal_box_price').value = window.selectedBoxPrice;
              document.getElementById('modal_box_image').value = window.selectedBoxImage;
              document.getElementById('modal_custom_box_text').value = window.customBoxText || '';
              document.getElementById('modal_quantity').value = window.buyNowProductQty || window.buyNowQty;
              document.getElementById('modal_number_of_boxes').value = window.buyNowBoxQty; // Do not use || 1
              // Always show the checkout modal
              const checkoutModal = new bootstrap.Modal(document.getElementById('checkoutModal'));
              checkoutModal.show();
            }, 300);
          }
        };

        document.getElementById('skipBoxBtn').onclick = () => {
          if (window.isBuyNow) {
            window.buyNowBoxId = 'none';
            customBoxText = '';
            buyNowQty = 1;
            modal.hide();

            setTimeout(() => {
              document.getElementById('modal_product_id').value = window.buyNowProductId;
              document.getElementById('modal_quantity').value = window.buyNowProductQty || window.buyNowQty;
              document.getElementById('modal_number_of_boxes').value = 0; // Explicitly set to 0 when skipping

              const checkoutModal = new bootstrap.Modal(document.getElementById('checkoutModal'));
              checkoutModal.show();
            }, 300);
          }
        };
      }, 100);
    });
  });
}

// Test if we can find the button
setTimeout(() => {
  const button = document.querySelector('.buy-now-btn');
  if (button) {
    console.log('Found Buy Now button:', button);
    console.log('Button onclick:', button.onclick);
  } else {
    console.log('Buy Now button not found');
  }
}, 1000);
</script>



                        
<!-- Modal -->
<div class="modal fade" id="boxSelectModal" tabindex="-1" aria-labelledby="boxSelectModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow rounded-4">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title fw-bold">
          <i class="bi bi-box-seam me-2"></i>
          Choose Your Sweet Box
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4" id="box-options">
        <!-- Box options will be injected here -->
      </div>
    </div>
  </div>
</div>

  </main>



<?php include ('inc/footer.php'); ?>

  <!-- Vendor JS Files -->
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
  <script src="assets/vendor/purecounter/purecounter_vanilla.js"></script>
  <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>

<!-- Main JS File -->
<script src="assets/js/main.js"></script>



<script>
document.querySelectorAll('.add-to-cart-btn').forEach(function(addToCartBtn) {
  addToCartBtn.addEventListener('click', () => {
    const productId = addToCartBtn.dataset.productId;
    const categoryId = addToCartBtn.dataset.categoryId;
    const qty = 1;

    fetch('inc/fetch_boxes', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `category_id=${categoryId}`
    })
    .then(res => res.json())
    .then(boxes => {
      const boxOptions = document.getElementById('box-options');
      boxOptions.innerHTML = `
        <div class="text-center mb-4">
          <h5 class="fw-bold text-dark">Select a Sweet Box</h5>
          <p class="text-muted">Choose from our pre-made boxes or create your own custom box</p>
        </div>
        <div class="row g-3 justify-content-center">
          ${boxes.map(box => `
            <div class="col-md-3 col-sm-4 col-6">
              <div class="box-card text-center rounded border p-3 h-100" style="cursor: pointer; transition: all 0.3s ease;" data-box-id="${box.id}" data-box-name="${box.box_name}" data-box-price="${box.box_price}">
                <img src="admin/${box.box_image}" alt="${box.box_name}" class="img-fluid rounded mb-2" style="height: 80px; width: 80px; object-fit: cover;">
                <div class="box_title fw-semibold" title="${box.box_name}">${box.box_name}</div>
                <div class="box_price mt-1" style="background-color: #fef3c7; border-radius: 6px; padding: 4px 8px; display: inline-block; font-size: 12px;">₹ ${box.box_price}</div>
              </div>
            </div>
          `).join('')}
          <div class="col-md-3 col-sm-4 col-6">
            <div class="box-card text-center rounded border border-warning p-3 h-100" style="cursor: pointer; background: #fff8e1; transition: all 0.3s ease;" data-box-id="custom">
              <div class="d-flex align-items-center justify-content-center mb-2" style="height: 80px; background: #fffde7; border-radius: 8px;">
                <i class="bi bi-pencil-square text-warning fs-3"></i>
            </div>
              <div class="box_title text-warning fw-semibold">Custom Box</div>
              <div class="box_price text-muted" style="font-size: 12px;">Write your own</div>
          </div>
        </div>
        </div>
        <div class="text-center mt-4">
          <button id="confirmBoxBtn" class="btn btn-warning px-4 py-2 fw-semibold me-3" disabled>
            <i class="bi bi-cart-plus me-2"></i>Continue Shopping
          </button>
          <button id="skipBoxBtn" class="btn btn-outline-secondary px-4 py-2">
            <i class="bi bi-x-circle me-2"></i>Skip
          </button>
        </div>
      `;

      const modal = new bootstrap.Modal(document.getElementById('boxSelectModal'));
      modal.show();

      let selectedBoxId = null;
      let customBoxText = '';
      let selectedQty = 1;
      let selectedBoxName = '';
      let selectedBoxPrice = 0;

      setTimeout(() => {
        document.querySelectorAll('.box-card').forEach(option => {
          option.addEventListener('click', async () => {
            // Remove previous selections
            document.querySelectorAll('.box-card').forEach(o => {
              o.classList.remove('border-primary', 'border-3', 'shadow', 'selected');
              o.style.transform = 'scale(1)';
            });
            
            // Highlight selected option
            option.classList.add('border-primary', 'border-3', 'shadow', 'selected');
            option.style.transform = 'scale(1.05)';

            selectedBoxId = option.dataset.boxId;
            selectedBoxName = option.dataset.boxName || '';
            selectedBoxPrice = parseFloat(option.dataset.boxPrice) || 0;

            if (selectedBoxId === 'custom') {
              // Show custom box modal
              const boxModal = bootstrap.Modal.getInstance(document.getElementById('boxSelectModal'));
              boxModal.hide();
              
                    setTimeout(() => {
                const customModal = new bootstrap.Modal(document.getElementById('customBoxModal'));
                customModal.show();
                
                // Handle custom box form submission
                document.getElementById('customBoxForm').onsubmit = (e) => {
                  e.preventDefault();
                  const customText = document.getElementById('customBoxText').value.trim();
                  
                  if (customText) {
                    customBoxText = customText;
                    customModal.hide();
                    
                    setTimeout(() => {
                      boxModal.show();
                  document.getElementById('confirmBoxBtn').disabled = false;
                      document.getElementById('confirmBoxBtn').innerHTML = '<i class="bi bi-cart-plus me-2"></i>Add to Cart';
                    }, 300);
                }
                };
              }, 300);
            } else {
              // Show quantity modal for sweet boxes
              const boxModal = bootstrap.Modal.getInstance(document.getElementById('boxSelectModal'));
              boxModal.hide();
              
              setTimeout(() => {
                const qtyModal = new bootstrap.Modal(document.getElementById('sweetBoxQtyModal'));
                qtyModal.show();
                
                // Handle quantity form submission
                document.getElementById('sweetBoxQtyForm').onsubmit = (e) => {
                  e.preventDefault();
                  const qty = parseInt(document.getElementById('sweetBoxQty').value);

                  if (qty > 0) {
                    selectedQty = qty;
                    qtyModal.hide();
                    
                    setTimeout(() => {
                      boxModal.show();
                document.getElementById('confirmBoxBtn').disabled = false;
                      document.getElementById('confirmBoxBtn').innerHTML = '<i class="bi bi-cart-plus me-2"></i>Add to Cart';
                    }, 300);
              }
                };
              }, 300);
            }
          });
        });

        document.getElementById('confirmBoxBtn').onclick = () => {
          submitToCart(productId, selectedQty, selectedBoxId, modal, customBoxText, selectedQty, selectedBoxName, selectedBoxPrice);
        };

        document.getElementById('skipBoxBtn').onclick = () => {
          submitToCart(productId, 1, 'none', modal, '', 0, '', 0);
        };
      }, 100);
    });
  });
});

function submitToCart(productId, qty, boxId, modal, customText = '', boxQty = 1, boxName = '', boxPrice = 0) {
  const payload = `product_id=${productId}&quantity=${qty}&box_id=${boxId}&custom_text=${encodeURIComponent(customText)}&box_qty=${boxQty}&box_name=${encodeURIComponent(boxName)}&box_price=${boxPrice}`;
    
  fetch('inc/add_to_cart', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: payload
  }).then(res => res.text())
    .then(data => {
      const trimmed = data.trim();

      if (trimmed === 'success') {
        Swal.fire({
          icon: 'success',
          title: 'Added to cart!',
          text: boxId === 'custom' ? 'Your custom box has been added!' : 'Product with sweet box added successfully!',
          showConfirmButton: false,
          timer: 3000,
          timerProgressBar: true,
          toast: true,
          position: 'top-end',
          didClose: () => {
            window.location.href = 'cart';
          }
        });
        modal.hide();
      } else if (trimmed === 'not_logged_in') {
        Swal.fire({
          icon: 'warning',
          title: 'Login Required',
          text: 'You need to be logged in to add items to your cart.',
          showCancelButton: true,
          showDenyButton: true,
          confirmButtonText: 'Login Now',
          denyButtonText: 'Use as Guest',
          cancelButtonText: 'Cancel'
        }).then((result) => {
          if (result.isConfirmed) {
            window.location.href = 'login';
          } else if (result.isDenied) {
            fetch('inc/add_to_cart', {
              method: 'POST',
              headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
              body: `${payload}&as_guest=1`            })
            .then(res => res.text())
            .then(guestData => {
              const guestTrimmed = guestData.trim();
              if (guestTrimmed === 'success') {
                Swal.fire({
                  icon: 'success',
                  title: 'Added to cart as guest!',
                  text: boxId === 'custom' ? 'Your custom box has been added!' : 'Product with sweet box added successfully!',
                  showConfirmButton: false,
                  timer: 3000,
                  toast: true,
                  position: 'top-end',
                  didClose: () => location.reload()
                });
                modal.hide();
              } else {
                Swal.fire({
                  icon: 'error',
                  title: 'Oops...',
                  text: guestTrimmed
                });
              }
            });
          }
        });
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Oops...',
          text: trimmed
        });
      }
    });
}

</script>

<script>
document.getElementById('placeOrderForm').addEventListener('submit', function (e) {
  e.preventDefault();

  const form = this;
  const submitBtn = form.querySelector('button[type="submit"]');
  const originalBtnText = submitBtn.innerHTML;
  submitBtn.disabled = true;
  submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';

  const quantityInput = document.getElementById('modal_quantity');
  const boxInput = document.getElementById('modal_number_of_boxes');
  const currentQty = parseInt(quantityInput.value) || 1;
  const currentBoxes = parseInt(boxInput.value) || 1;

  const formData = new FormData(form);
  console.log('Payment method:', formData.get('payment_method'));
  console.log('Form data entries:');
  for (let [key, value] of formData.entries()) {
    console.log(key + ': ' + value);
  }

  fetch('inc/place_order', {
    method: 'POST',
    body: formData
  })
    .then(res => {
      console.log('Response status: 825', res.status);
      return res.json();
    })
    .then(data => {
      console.log('Response data:', data);
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalBtnText;
      if (data.success) {
        console.log('Payment redirect URL:', data.payment_redirect);
        if (data.payment_redirect) {
          // Redirect to payment gateway
          Swal.fire({
            icon: 'info',
            title: 'Payment Required',
            text: data.message,
            confirmButtonText: 'Proceed to Payment',
            allowOutsideClick: false
          }).then(() => {
            console.log('Redirecting to:', data.payment_redirect);
            window.location.href = data.payment_redirect;
          });
        } else {
          // COD or wallet payment completed
          Swal.fire({
            icon: 'success',
            title: 'Order Placed!',
            html: `<p>${data.message}</p><strong>Delivery: Within 2 days</strong>`,
            confirmButtonText: 'OK',
            timer: 4000,
            timerProgressBar: true,
          }).then(() => {
            // Redirect to cart
            window.location.href = 'cart?payment=success&order_code=' + data.order_code + '&amount=' + data.subtotal;
          });
        }
      }
    })
    .catch(err => {
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalBtnText;
      Swal.fire({
        icon: 'error',
        title: 'Request Failed',
        text: err.message || 'Something went wrong.',
      });
    });
});



  // Success function (no popup)
  function showSMSSuccess(message, orderCode = '') {
    console.log('Order Success:', message, 'Order Code:', orderCode);
  }

  // Log successful orders
  if (window.location.search.includes('payment=success')) {
    const urlParams = new URLSearchParams(window.location.search);
    const orderCode = urlParams.get('order_code') || '';
    const amount = urlParams.get('amount') || '';
    
    console.log('Order Success:', orderCode, 'Amount:', amount);
  }
</script>

<!-- Final Buy Now function definition -->
<script>
console.log('Final script loading...');

// Ensure the function is available globally
if (typeof window.testBuyNowClick !== 'function') {
  console.log('Defining testBuyNowClick function...');
  window.testBuyNowClick = function(button) {
    console.log('Buy Now button clicked!');
    
    const productId = button.dataset.productId;
    const categoryId = button.dataset.categoryId;
    const price = parseFloat('<?= $product['discount_price'] ?? 0 ?>');
    
    console.log('Product ID:', productId, 'Category ID:', categoryId, 'Price:', price);
    
    // Set global variables
    window.isBuyNow = true;
    window.buyNowProductId = productId;
    window.customBoxText = '';
    window.buyNowQty = 1;

    // Fetch minimum order amount and boxes
    Promise.all([
      fetch('inc/fetch_settings.php?type=min_order').then(res => res.json()).catch(() => ({ min_order_amount: 1500 })),
      fetch('inc/fetch_boxes', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `category_id=${categoryId}`
      }).then(res => res.json()).catch(() => [])
    ])
    .then(([settings, boxes]) => {
      console.log('Settings:', settings, 'Boxes:', boxes);
      const minAmount = parseFloat(settings.min_order_amount || 1500);
      const productPrice = price;
      
      // Check if current order meets minimum amount
      const currentTotal = productPrice * window.buyNowQty;
      
      if (currentTotal < minAmount) {
        // Show quantity adjustment popup
        Swal.fire({
          icon: 'warning',
          title: 'Minimum Order Amount Required',
          html: `
            <div style="margin-bottom:10px;">
              Your order total is ₹<span id='swalTotal'>${currentTotal.toFixed(2)}</span>.<br>
              Minimum order amount is ₹${minAmount.toFixed(2)}.
            </div>
            <p>Please increase quantity to meet the minimum order amount.</p>
            <div class="d-flex justify-content-center align-items-center mb-3">
              <button type="button" id="decQtyBtn" class="btn btn-outline-secondary px-3 me-2">−</button>
              <input type="number" id="newQtyInput" class="form-control text-center" value="${window.buyNowQty}" min="1" style="width: 80px;">
              <button type="button" id="incQtyBtn" class="btn btn-outline-secondary px-3 ms-2">+</button>
            </div>
          `,
          showCancelButton: true,
          confirmButtonText: 'Continue',
          cancelButtonText: 'Cancel',
          customClass: {
            confirmButton: 'btn btn-warning me-3',
            cancelButton: 'btn btn-secondary'
          },
          buttonsStyling: false,
          didOpen: () => {
            const input = document.getElementById('newQtyInput');
            const incBtn = document.getElementById('incQtyBtn');
            const decBtn = document.getElementById('decQtyBtn');
            const totalSpan = document.getElementById('swalTotal');
            
            incBtn.addEventListener('click', () => {
              input.value = parseInt(input.value) + 1;
              totalSpan.textContent = (parseInt(input.value) * productPrice).toFixed(2);
            });
            
            decBtn.addEventListener('click', () => {
              if (parseInt(input.value) > 1) {
                input.value = parseInt(input.value) - 1;
                totalSpan.textContent = (parseInt(input.value) * productPrice).toFixed(2);
              }
            });
            
            input.addEventListener('input', () => {
              let val = parseInt(input.value) || 1;
              if (val < 1) val = 1;
              input.value = val;
              totalSpan.textContent = (val * productPrice).toFixed(2);
            });
          },
          preConfirm: () => {
            const newQty = parseInt(document.getElementById('newQtyInput').value);
            if (!newQty || newQty < 1) {
              Swal.showValidationMessage('Please enter a valid quantity');
              return false;
            }
            if (newQty * productPrice < minAmount) {
              Swal.showValidationMessage(`Total must be at least ₹${minAmount.toFixed(2)}`);
              return false;
            }
            return newQty;
          }
        }).then(result => {
          if (result.isConfirmed && result.value) {
            window.buyNowQty = result.value;
            showBoxPopup(boxes);
          }
        });
      } else {
        showBoxPopup(boxes);
      }
    })
    .catch(error => {
      console.error('Error:', error);
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Something went wrong. Please try again.'
      });
    });
  };
  console.log('Function defined successfully:', typeof window.testBuyNowClick);
} else {
  console.log('Function already exists:', typeof window.testBuyNowClick);
}

// Function to show box selection popup
function showBoxPopup(boxes) {
  const boxOptions = document.getElementById('box-options');

  boxOptions.innerHTML = `
    <div class="text-center mb-4">
      <h5 class="fw-bold text-dark">Select a Sweet Box</h5>
      <p class="text-muted">Choose from our pre-made boxes or create your own custom box</p>
    </div>
    <div class="row g-3 justify-content-center">
      ${boxes.map(box => `
        <div class="col-md-3 col-sm-4 col-6">
          <div class="box-card text-center rounded border p-3 h-100" style="cursor: pointer; transition: all 0.3s ease;" data-box-id="${box.id}" data-box-name="${box.box_name}" data-box-price="${box.box_price}">
            <img src="admin/${box.box_image}" alt="${box.box_name}" class="img-fluid rounded mb-2" style="height: 80px; width: 80px; object-fit: cover;">
            <div class="box_title fw-semibold" title="${box.box_name}">${box.box_name}</div>
            <div class="box_price mt-1" style="background-color: #fef3c7; border-radius: 6px; padding: 4px 8px; display: inline-block; font-size: 12px;">₹ ${box.box_price}</div>
          </div>
        </div>
      `).join('')}
      <div class="col-md-3 col-sm-4 col-6">
        <div class="box-card text-center rounded border border-warning p-3 h-100" style="cursor: pointer; background: #fff8e1; transition: all 0.3s ease;" data-box-id="custom">
          <div class="d-flex align-items-center justify-content-center mb-2" style="height: 80px; background: #fffde7; border-radius: 8px;">
            <i class="bi bi-pencil-square text-warning fs-3"></i>
        </div>
          <div class="box_title text-warning fw-semibold">Custom Box</div>
          <div class="box_price text-muted" style="font-size: 12px;">Write your own</div>
      </div>
    </div>
    </div>
    <div class="text-center mt-4">
      <button id="confirmBoxBtn" class="btn btn-warning px-4 py-2 fw-semibold me-3" disabled>
        <i class="bi bi-arrow-right me-2"></i>Continue to Checkout
      </button>
      <button id="skipBoxBtn" class="btn btn-outline-secondary px-4 py-2">
        <i class="bi bi-x-circle me-2"></i>Skip Box
      </button>
    </div>
  `;

  const modal = new bootstrap.Modal(document.getElementById('boxSelectModal'));
  modal.show();

  let selectedBoxId = null;

  setTimeout(() => {
    document.querySelectorAll('.box-card').forEach(option => {
      option.addEventListener('click', async () => {
        // Remove previous selections
        document.querySelectorAll('.box-card').forEach(o => {
          o.classList.remove('border-primary', 'border-3', 'shadow', 'selected');
          o.style.transform = 'scale(1)';
        });
        
        // Highlight selected option
        option.classList.add('border-primary', 'border-3', 'shadow', 'selected');
        option.style.transform = 'scale(1.05)';
        
        selectedBoxId = option.dataset.boxId;

        window.selectedBoxName = option.querySelector('.box_title')?.textContent.trim() || '-';
        window.selectedBoxPrice = option.querySelector('.box_price')?.textContent.replace(/[^\d.]/g, '') || '0';

        if (selectedBoxId === 'custom') {
          const bootstrapModal = bootstrap.Modal.getInstance(document.getElementById('boxSelectModal'));
          bootstrapModal.hide();

          const { isConfirmed, value: text } = await Swal.fire({
            title: 'Customize Your Box',
            input: 'textarea',
            inputLabel: 'If you want any custom text on box:',
            inputAttributes: {
              'aria-label': 'Custom box message',
              maxlength: 250
            },
            showCancelButton: true,
            confirmButtonText: 'Save',
            cancelButtonText: 'Cancel',
            inputValidator: value => {
              if (!value.trim()) return 'Please enter something!';
            }
          });

          if (isConfirmed && text.trim()) {
            window.customBoxText = text.trim();
            window.buyNowBoxQty = 1; // Custom box quantity
            document.getElementById('confirmBoxBtn').disabled = false;
            bootstrapModal.show();
          } else {
            option.classList.remove('border-primary', 'border-3', 'shadow', 'selected');
            selectedBoxId = null;
            window.customBoxText = '';
            document.getElementById('confirmBoxBtn').disabled = true;
          }

        } else {
          const { isConfirmed, value: qty } = await Swal.fire({
            title: 'How many boxes Need?',
            html: `<input type="number" id="qtyInput" class="swal2-input" placeholder="Enter quantity" min="1" step="1" value="1" style="width: 300px;">`,
            focusConfirm: false,
            showCancelButton: true,
            confirmButtonText: 'Confirm',
            cancelButtonText: 'Cancel',
            preConfirm: () => {
              const qtyVal = parseInt(document.getElementById('qtyInput').value);
              if (!qtyVal || qtyVal <= 0) {
                Swal.showValidationMessage('Please enter a valid quantity');
              }
              return qtyVal;
            },
            didOpen: () => {
              document.getElementById('qtyInput')?.focus();
            }
          });

          if (isConfirmed) {
            window.buyNowBoxQty = qty; // Save as box quantity (don't change product quantity)
            document.getElementById('confirmBoxBtn').disabled = false;
          } else {
            option.classList.remove('border-primary', 'border-3', 'shadow', 'selected');
            selectedBoxId = null;
            window.buyNowBoxQty = 1; // Reset box quantity
            document.getElementById('confirmBoxBtn').disabled = true;
          }
        }
      });
    });

    document.getElementById('confirmBoxBtn').onclick = () => {
      if (window.isBuyNow) {
        window.buyNowBoxId = selectedBoxId;
        modal.hide();
        setTimeout(() => {
          document.getElementById('modal_product_id').value = window.buyNowProductId;
          document.getElementById('modal_box_id').value = window.buyNowBoxId;
          document.getElementById('modal_box_name').value = window.selectedBoxName;
          document.getElementById('modal_box_price').value = window.selectedBoxPrice;
          document.getElementById('modal_box_image').value = window.selectedBoxImage;
          document.getElementById('modal_custom_box_text').value = window.customBoxText || '';
          document.getElementById('modal_quantity').value = window.buyNowProductQty || window.buyNowQty;
          document.getElementById('modal_number_of_boxes').value = window.buyNowBoxQty; // Do not use || 1
          // Always show the checkout modal
          const checkoutModal = new bootstrap.Modal(document.getElementById('checkoutModal'));
          checkoutModal.show();
        }, 300);
      }
    };

    document.getElementById('skipBoxBtn').onclick = () => {
      if (window.isBuyNow) {
        window.buyNowBoxId = 'none';
        window.customBoxText = '';
        window.buyNowBoxQty = 0; // No boxes when skipped
        modal.hide();

        setTimeout(() => {
          document.getElementById('modal_product_id').value = window.buyNowProductId;
          document.getElementById('modal_quantity').value = window.buyNowProductQty || window.buyNowQty;
          document.getElementById('modal_number_of_boxes').value = 0; // Explicitly set to 0 when skipping

          const checkoutModal = new bootstrap.Modal(document.getElementById('checkoutModal'));
          checkoutModal.show();
        }, 300);
      }
    };
  }, 100);
}

// Test if we can find the button
setTimeout(() => {
  const button = document.querySelector('.buy-now-btn');
  if (button) {
    console.log('Found Buy Now button:', button);
    console.log('Button onclick:', button.onclick);
  } else {
    console.log('Buy Now button not found');
  }
}, 1000);
</script>

</body>

</html>
