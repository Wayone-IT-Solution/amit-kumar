<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Products - Amit Dairy & Sweets</title>
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

  <!-- Main CSS File -->
  <link href="assets/css/main.css" rel="stylesheet">
  <!-- SweetAlert2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />

<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>



</head>

<body class="index-page">

    <?php include ('inc/header.php'); 
    include_once ('inc/contact_data.php');
    ?>
  
  <?php
$subcategoryId = isset($_GET['subcategory_id']) ? (int)$_GET['subcategory_id'] : 0;

$params = [];
$sql = "
    SELECT p.*, c.title AS category_name, s.title AS subcategory_name
    FROM products p
    INNER JOIN categories c ON p.category_id = c.id
    INNER JOIN subcategories s ON p.subcategory_id = s.id
    WHERE p.status = 1 AND p.subcategory_id = ?
";

$params[] = $subcategoryId;

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$subcategoryName = '';
$categoryName = '';

if ($subcategoryId > 0) {
    $stmt = $conn->prepare("
        SELECT sc.title AS subcategory_title, c.title AS category_title
        FROM subcategories sc
        LEFT JOIN categories c ON sc.category_id = c.id
        WHERE sc.id = ?
    ");
    $stmt->execute([$subcategoryId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $subcategoryName = $row['subcategory_title'];
        $categoryName = $row['category_title'];
    }
}


// Count total products in this subcategory
$countSql = "SELECT COUNT(*) FROM products WHERE status = 1 AND subcategory_id = ?";
$countParams = [$subcategoryId];

$stmt = $conn->prepare($countSql);
$stmt->execute($countParams);
$totalProducts = $stmt->fetchColumn();

// Banner image
$productImage = '';
try {
    $stmt = $conn->prepare("SELECT image FROM banners WHERE page_name = ?");
    $stmt->execute(['product']);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && !empty($row['image']) && file_exists("admin/" . $row['image'])) {
        $productImage = $row['image'];
    }
} catch (PDOException $e) {
    // Log error if needed
}
?>



  <main class="main">
    <section class="product-bread" style="background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('admin/<?= htmlspecialchars($productImage ?: 'assets/img/hero.png') ?>'); background-size: cover; background-position: center; background-repeat: no-repeat;">
        <div class="container text-center py-5">
      <div class="d-flex justify-content-center align-items-center mb-3">
        <img src="assets/img/Vector.png" class="text-warning display-6 me-2" alt="">
        <h2 class="m-0"><?= htmlspecialchars($categoryName) ?> </h2>
        <img src="assets/img/Vector (1).png" class="text-warning display-6 me-4" alt="">
      </div>
<!-- ✅ Breadcrumb -->
<nav aria-label="breadcrumb" class="d-flex justify-content-center flex-column align-items-center">
  <ol class="breadcrumb bg-transparent mb-1">
    <li class="breadcrumb-item">
      <a href="index" class="text-light fw-semibold text-decoration-none">Home</a>
    </li>
    <li class="breadcrumb-item text-light fw-semibold">
      All Products
    </li>
  </ol>
  <?php if (!empty($subcategoryName)): ?>
      <li class="breadcrumb-item active text-light fw-semibold" aria-current="page">
        <?= htmlspecialchars($subcategoryName) ?>
      </li>
    <?php endif; ?>
    <br>
  <!-- ✅ Subcategory Names: New line, centered -->
  <?php if (!empty($subcategories)): ?>
        <div class="d-flex flex-wrap justify-content-center gap-2">
          <?php foreach ($subcategories as $subcat): ?>
            <a 
              href="product?subcategory_id=<?= urlencode($subcat['id']) ?>" 
              class="badge bg-light text-dark px-3 py-2 text-decoration-none"
            >
              <?= htmlspecialchars($subcat['title']) ?>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

</nav>



    <h5 class="fw-bold mt-4 mb-5">(<?= $totalProducts ?> Products)</h5>

    <div class="row breadcrumb-list justify-content-center text-center gy-5">
      <div class="col-6 col-sm-3">
        <div class="icon-circle mb-3 mx-auto">
          <i class="bi bi-box-seam-fill text-white fs-4"></i>
        </div>
        <p class="fw-semibold">Shipping after 2 Days</p>
      </div>
      <!-- <div class="col-6 col-sm-3">
        <div class="icon-circle mb-3 mx-auto">
          <i class="bi bi-stopwatch-fill text-white fs-4"></i>
        </div>
        <p class="fw-semibold">15 Days Shelf Life</p>
      </div> -->
      <div class="col-6 col-sm-3">
        <div class="icon-circle mb-3 mx-auto">
          <i class="bi bi-clock-fill text-white fs-4"></i>
        </div>
        <p class="fw-semibold">On Time Delivery</p>
      </div>
      <div class="col-6 col-sm-3">
        <div class="icon-circle mb-3 mx-auto">
          <i class="bi bi-leaf-fill text-white fs-4"></i>
        </div>
        <p class="fw-semibold">No Preservatives</p>
      </div>
    </div>
  </div>
 </section>
<style>
  .tag-badge {
  position: absolute;
  top: 10px;
  right: 10px;
  z-index: 2;
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 4px;
}

.tag {
  background-color: #D6B669;
  color: white;
  font-size: 12px;
  padding: 2px 8px;
  border-radius: 12px;
  white-space: nowrap;
}
/* Optional yellow styling */
.product-filter-bar {
  background-color: #fff8dc; /* light yellow */
  padding: 10px;
  border-radius: 10px;
}

.search-input {
  flex: 1;
  min-width: 0;
  background-color: #fffbe6;
  border: 2px solid #ffe066;
  border-radius: 10px;
}

.filter-select {
  width: 200px;
  background-color: #fffbe6;
  border: 2px solid #ffe066;
  border-radius: 10px;
}

.product-card {
  border-radius: 22px;
  border: none;
  box-shadow: 0 8px 32px rgba(214, 182, 105, 0.13);
  transition: transform 0.2s, box-shadow 0.2s;
  background: linear-gradient(135deg, #fffbe6 0%, #fff3cd 100%);
  position: relative;
  overflow: hidden;
}
.product-card:hover {
  transform: translateY(-8px) scale(1.03);
  box-shadow: 0 16px 48px rgba(214, 182, 105, 0.22);
  border-color: #ffe066;
}
.product-card .product-img {
  height: 210px;
  object-fit: cover;
  border-top-left-radius: 22px;
  border-top-right-radius: 22px;
  border-bottom: 3px solid #ffe066;
  background: #fffbe6;
  width: 100%;
}
.product-card .fw-semibold {
  color: #b08a2a;
  font-weight: 700;
  font-size: 1.1rem;
  letter-spacing: 0.5px;
}
.product-card .btn-cart {
  background: linear-gradient(90deg, #ffe066 0%, #d4b160 100%);
  color: #7a5a10;
  font-weight: 600;
  border: none;
  border-radius: 12px;
  padding: 0.6rem 1.5rem;
  box-shadow: 0 2px 8px rgba(214, 182, 105, 0.13);
  transition: background 0.2s, color 0.2s;
}
.product-card .btn-cart:hover {
  background: linear-gradient(90deg, #d4b160 0%, #ffe066 100%);
  color: #fff;
}
#noProductResult {
  color: #b08a2a !important;
  font-size: 1.1rem;
  background: #fffbe6;
  border-radius: 10px;
  border: 1.5px solid #ffe066;
  padding: 0.7rem 1.2rem;
  margin-bottom: 1.5rem;
  display: none;
}
</style>

<style>
@media (max-width: 767.98px) {
  .product-filter-bar {
    flex-direction: column !important;
    align-items: stretch !important;
    gap: 10px !important;
  }
  .search-input,
  .filter-select {
    width: 100% !important;
    min-width: 0 !important;
    margin-right: 0 !important;
  }
}
</style>

<section class="product-list section py-5">
  <div class="container">
    <!-- Section Heading and Description -->
    <div class="text-center mb-4">
      <h2 style="color: #d1ae5e; font-weight: 800; letter-spacing: 1px;">Browse Our Delicious Products</h2>
      <p style="color: #b08a2a; font-size: 1.1rem;">Find your favorite sweets and dairy items. Use the search and filter below to quickly discover the perfect treat!</p>
    </div>

    <div class="row mb-4">
  <div class="col-12 col-md-6">
    <div class="d-flex align-items-center product-filter-bar">
      
      <!-- 🔍 Search Input -->
      <input 
        type="text" 
        id="productSearchInput" 
        class="form-control form-control-lg search-input me-2" 
        placeholder="🔍 Search products..." 
        onkeyup="filterProducts()"
      >

      <!-- 🔽 Filter Dropdown -->
      <select 
        id="tagFilterSelect" 
        class="form-select form-select-lg filter-select" 
        onchange="onDropdownFilter(this)"
      >
        <option value="all" selected>Filter</option>
        <option value="hot">Hot</option>
        <option value="new_arrival">New Arrival</option>
        <option value="featured">Featured</option>
      </select>

    </div>
  </div>
</div>


    <!-- No result message -->
    <div id="noProductResult" class="text-center fw-semibold mb-4">
      No matching products found.
    </div>

    <!-- Product Grid -->
    <div class="row g-4" id="productGrid">
      <?php foreach ($products as $product): ?>
        <div class="col-md-4 product-wrapper" 
          data-name="<?= strtolower($product['name']) ?>" 
          data-tags="<?= strtolower($product['tags']) ?>">
          
          <div class="product-card position-relative h-100 d-flex flex-column">
            
            <!-- Tags -->
            <div class="tag-badge">
              <?php
              if (!empty($product['tags'])):
                $tags = explode(',', $product['tags']);
                foreach ($tags as $tag): ?>
                  <span class="tag"><?= htmlspecialchars(trim($tag)) ?></span>
              <?php endforeach; endif; ?>
            </div>

            <div class="position-relative">
  <!-- Product Image with link -->
  <a href="product-details?product_id=<?= htmlspecialchars($product['id']) ?>">
    <img src="admin/<?= htmlspecialchars($product['product_image']) ?>" alt="Product" class="product-img w-100">
  </a>

  <!-- Wishlist Button (Top Left Corner) -->
  <form method="post" action="inc/add_to_wishlist" class="wishlist-form position-absolute top-0 start-0 m-2">
    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
    <?php $inWishlist = in_array($product['id'], $_SESSION['wishlist'] ?? []); ?>
    <button type="submit" class="btn p-0 border-0 bg-transparent wishlist-btn" title="Add to Wishlist">
      <i class="bi <?= $inWishlist ? 'bi-heart-fill' : 'bi-heart' ?>  fs-3 text-white"></i>
    </button>
  </form>
</div>
                  <style>
                    .wishlist-btn {
  z-index: 20;
}

                  </style>

            <!-- Card Body -->
            <div class="p-3 d-flex flex-column h-100">
              <div>
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <h6 class="mb-0 fw-semibold"><?= htmlspecialchars($product['name']) ?></h6>
                 
                
                  <div class="price-tag">
                    <del>₹ <span class="base-price"><?= htmlspecialchars($product['price']) ?></span></del>
                    <span class="fw-normal">/</span> ₹ <span class="dynamic-price"><?= htmlspecialchars($product['discount_price']) ?></span>
                  </div>
                  
                </div>
              
              </div>

              <!-- Type Dropdown -->
        <?php $isUnit = (isset($product['weight_type']) && strtolower($product['weight_type']) === 'unit'); ?>
<div class="mb-2">
  <select class="form-select form-select-sm type-select"
          style="max-width: 120px; display: inline-block; margin-top:30px; background-color: #fffbea; border-color: #f7d200; color: #000;">
    <?php if ($isUnit): ?>
      <option value="1">1 unit</option>
    <?php else: ?>
      <?php $typesArr = array_filter(array_map('trim', explode(',', $product['types']))); ?>
      <?php foreach ($typesArr as $type): ?>
        <option value="<?= (int)$type ?>"><?= (int)$type ?> gram<?= ((int)$type == 1000) ? ' (1kg)' : '' ?></option>
      <?php endforeach; ?>
    <?php endif; ?>
  </select>
</div>



              <div class="mt-auto">
                 
                <div class="d-flex justify-content-between align-items-center mt-3">
                  <div class="qty-box d-flex align-items-center">
                    <button class="btn btn-sm p-0 border-0 minus-btn">−</button>
                    <input type="number"
                      class="qty-val mx-2"
                      value="<?= $product['min_order'] ?>"
                      min="<?= $product['min_order'] ?>"
                      max="<?= $product['max_order'] ?>"
                      style="width: 40px; border: none; text-align: center;"
                      data-min="<?= $product['min_order'] ?>"
                      data-max="<?= $product['max_order'] ?>"
                    >
                    
                    <button class="btn btn-sm p-0 border-0 plus-btn">+</button>
                  </div>

                  <button
                    class="btn btn-cart px-4 py-2 add-to-cart-btn"
                    data-product-id="<?= $product['id']; ?>"
                    data-category-id="<?= $product['category_id']; ?>"
                    data-product-name="<?= htmlspecialchars($product['name']) ?>"
                    data-product-price="<?= htmlspecialchars($product['price']) ?>"
                    data-product-discount-price="<?= htmlspecialchars($product['discount_price']) ?>"
                    data-product-image="<?= htmlspecialchars($product['product_image']) ?>"
                    data-product-weight="<?= htmlspecialchars($product['weight']) ?>"
                    data-product-weight-type="<?= htmlspecialchars($product['weight_type']) ?>"
                  >
                    Add to Cart
                  </button>
                </div>
              </div>
            </div>

          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<script>
let currentFilter = 'all';

function onDropdownFilter(select) {
  currentFilter = select.value;
  filterProducts();
}

function filterProducts() {
  const input = document.getElementById('productSearchInput').value.toLowerCase();
  const cards = document.querySelectorAll('.product-wrapper');
  let anyVisible = false;

  cards.forEach(card => {
    const name = card.getAttribute('data-name') || '';
    const tags = card.getAttribute('data-tags') || '';
    const matchesSearch = name.includes(input);
    const matchesTag = currentFilter === 'all' || tags.includes(currentFilter);

    if (matchesSearch && matchesTag) {
      card.style.display = '';
      anyVisible = true;
    } else {
      card.style.display = 'none';
    }
  });

  const noResult = document.getElementById('noProductResult');
  noResult.style.display = anyVisible ? 'none' : 'block';
}
</script>


<!-- ✅ Box Selection Modal -->
<div class="modal fade" id="boxSelectModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content p-4">
      <div id="box-options"></div>
    </div>
  </div>
</div>

<!-- ✅ Product Card Script -->
<script>
document.querySelectorAll('.product-card').forEach(function(card) {
  const minusBtn = card.querySelector('.minus-btn');
  const plusBtn = card.querySelector('.plus-btn');
  const qtyInput = card.querySelector('.qty-val');
  const addToCartBtn = card.querySelector('.add-to-cart-btn');
  const minOrder = parseInt(qtyInput.dataset.min) || 1;
  const maxOrder = parseInt(qtyInput.dataset.max) || 999;
  const typeSelect = card.querySelector('.type-select');
  const basePrice = parseFloat(addToCartBtn.dataset.productPrice);
  const baseWeight = parseInt(addToCartBtn.dataset.productWeight) || 1000; // default 1000g if not set
  const dynamicPriceEl = card.querySelector('.dynamic-price');
  const discountPrice = parseFloat(addToCartBtn.dataset.productDiscountPrice);
  const isUnit = (addToCartBtn.dataset.productWeightType && addToCartBtn.dataset.productWeightType.toLowerCase() === 'unit');

  function updateButtonState() {
    let qty = parseInt(qtyInput.value) || minOrder;
    qtyInput.value = Math.max(minOrder, Math.min(maxOrder, qty));
    minusBtn.disabled = qty <= minOrder;
    plusBtn.disabled = qty >= maxOrder;
    addToCartBtn.disabled = qty < minOrder || qty > maxOrder;
    updatePrice();
  }

  function updatePrice() {
    let qty = parseInt(qtyInput.value, 10) || 1;
    let totalPrice = 0;
    if (isUnit) {
      totalPrice = qty * discountPrice;
    } else {
      let selectedType = typeSelect ? parseInt(typeSelect.value, 10) : 1000;
      let totalGrams = selectedType * qty;
      totalPrice = (totalGrams / 1000) * discountPrice;
    }
    dynamicPriceEl.innerText = totalPrice.toFixed(2);
  }

  if (typeSelect) {
    typeSelect.addEventListener('change', updatePrice);
  }
  qtyInput.addEventListener('input', updateButtonState);
  minusBtn.addEventListener('click', () => {
    let qty = parseInt(qtyInput.value) || minOrder;
    qtyInput.value = Math.max(minOrder, qty - 1);
    updateButtonState();
  });
  plusBtn.addEventListener('click', () => {
    let qty = parseInt(qtyInput.value) || minOrder;
    qtyInput.value = Math.min(maxOrder, qty + 1);
    updateButtonState();
  });
  updateButtonState();

  addToCartBtn.addEventListener('click', () => {
    const qty = parseInt(qtyInput.value) || 1;
    const productId = addToCartBtn.dataset.productId;
    const categoryId = <?= json_encode($categoryId) ?>;
    const selectedType = typeSelect ? parseInt(typeSelect.value, 10) : 1000;

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
      let selectedBoxQty = 1;
      let customBoxText = '';
      let selectedBoxName = '';
      let selectedBoxPrice = 0;

      setTimeout(() => {
        document.querySelectorAll('.box-card').forEach(option => {
          option.addEventListener('click', async () => {
            // Remove previous selections
            document.querySelectorAll('.box-card').forEach(o => {
              o.classList.remove('border-primary', 'border-3', 'shadow');
              o.style.transform = 'scale(1)';
            });
            
            // Highlight selected option
            option.classList.add('border-primary', 'border-3', 'shadow');
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
                    selectedBoxQty = qty;
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
          submitToCart(productId, qty, selectedBoxId, modal, customBoxText, selectedBoxQty, selectedBoxName, selectedBoxPrice, selectedType);
        };

        document.getElementById('skipBoxBtn').onclick = () => {
          submitToCart(productId, qty, 'none', modal, '', 0, '', 0, selectedType);
        };
      }, 100);
    });
  });
});

function submitToCart(productId, qty, boxId, modal, customText = '', boxQty = 1, boxName = '', boxPrice = 0, selectedType = 1000) {
  const payload = `product_id=${productId}&quantity=${qty}&box_id=${boxId}&custom_text=${encodeURIComponent(customText)}&box_qty=${boxQty}&box_name=${encodeURIComponent(boxName)}&box_price=${boxPrice}&selected_type=${selectedType}`;

  fetch('inc/add_to_cart', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: payload
  })
  .then(res => res.text())
  .then(data => {
    const trimmed = data.trim();
    if (trimmed === 'success') {
      showSuccess('🎉 Product added to cart!', boxId === 'custom' ? 'Your custom box has been added!' : 'Product with sweet box added successfully!');
      setTimeout(() => {
        location.reload();
      }, 2000);
      modal.hide();
    } else if (trimmed === 'not_logged_in') {
      Swal.fire({
        icon: 'warning',
        title: 'Login Required',
        text: 'You need to be logged in to add items to your cart.',
        showCancelButton: true,
        showDenyButton: true,
        confirmButtonText: 'Login Now',
        denyButtonText: 'Use as Guest'
      }).then(result => {
        if (result.isConfirmed) {
          window.location.href = 'login';
        } else if (result.isDenied) {
          fetch('inc/add_to_cart', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `${payload}&as_guest=1`
          })
          .then(res => res.text())
          .then(guestData => {
            const guestTrimmed = guestData.trim();
            if (guestTrimmed === 'success') {
              showSuccess('Added to cart as guest!', boxId === 'custom' ? 'Your custom box has been added!' : 'Product with sweet box added successfully!');
              setTimeout(() => {
                location.reload();
              }, 2000);
              modal.hide();
            } else {
              showError('Oops...', guestTrimmed);
            }
          });
        }
      });
    } else {
      showError('Oops...', trimmed);
    }
  });
}
</script>


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
     </section>

     <?php
$stmt = $conn->query("SELECT name, image, rating, comment FROM testimonial WHERE status = 1 ORDER BY id DESC");
$testimonials = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

      <!-- Testimonials Section -->
      <section id="testimonials" class="testimonials section">

        <!-- Section Title -->
        <div class="container section-title-2" data-aos="fade-up">
          <h2>Testimonials</h2>
          <p>Necessitatibus eius consequatur ex aliquid fuga eum quidem sint consectetur velit</p>
        </div><!-- End Section Title -->

        <div class="container" data-aos="fade-up" data-aos-delay="100">

          <div class="swiper init-swiper">
            <script type="application/json" class="swiper-config">
              {
                "loop": true,
                "speed": 600,
                "autoplay": {
                  "delay": 5000
                },
                "slidesPerView": "auto",
                "pagination": {
                  "el": ".swiper-pagination",
                  "type": "bullets",
                  "clickable": true
                },
                "breakpoints": {
                  "320": {
                    "slidesPerView": 1,
                    "spaceBetween": 40
                  },
                  "1200": {
                    "slidesPerView": 3,
                    "spaceBetween": 10
                  }
                }
              }
            </script>
            <div class="swiper-wrapper">

              <?php foreach ($testimonials as $testimonial): ?>
  <div class="swiper-slide">
    <div class="testimonial-item">
      <p>
        <span><?= htmlspecialchars($testimonial['comment']) ?></span>
      </p>
      <div class="d-flex align-items-center">
        <!-- Image -->
        <img src="admin/<?= htmlspecialchars($testimonial['image']) ?>" class="testimonial-img" alt="<?= htmlspecialchars($testimonial['name']) ?>">

        <!-- Name and rating -->
        <div>
          <h3><?= htmlspecialchars($testimonial['name']) ?></h3>
          <div class="stars d-flex align-items-center gap-1">
            <i class="bi bi-star-fill text-warning"></i>
            <h5 class="mb-0"><?= number_format($testimonial['rating'], 1) ?></h5>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>


              



              

            </div>
          </div>

        </div>

      </section><!-- /Testimonials Section -->


    <section class="contact section">
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <h2>Get in Touch with Amit Dairy & Sweets</h2>
                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. </p>

                    <ul>
                       <li> <i class="bi bi-house"></i> Shop Address : <?= htmlspecialchars($contact['address']) ?></li>
                       <li> <i class="bi bi-telephone-fill"></i> Phone Number : <?= htmlspecialchars($contact['phone']) ?></li>
                       <li> <i class="bi bi-envelope-fill"></i> Email Address : <?= htmlspecialchars($contact['email']) ?></li>
                    </ul>

                </div>
                <div class="col-lg-6">
                    <div class="container d-flex justify-content-center align-items-center">
                      <img src="assets/img/contact.png" alt="CTA Banner" class="img-fluid">
                    </div>
                </div>
            </div>
        </div>
    </section>
    

  </main>

  <?php include ('inc/footer.php'); ?>

  <!-- Vendor JS Files -->
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
  <script src="assets/vendor/purecounter/purecounter_vanilla.js"></script>
  <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>

  <!-- SMS Notifications -->
  <link rel="stylesheet" href="assets/css/sms-notifications.css">
  <script src="assets/js/sms-notifications.js"></script>

  <!-- Main JS File -->
  <script src="assets/js/main.js"></script>

</body>

</html>