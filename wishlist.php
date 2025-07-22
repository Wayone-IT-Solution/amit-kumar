<?php
session_start();
include('inc/header.php');
require_once 'inc/db.php';

// Wishlist stored in session as 'wishlist' => array of product IDs
$wishlist = $_SESSION['wishlist'] ?? [];
$products = [];

if (!empty($wishlist)) {
    // Fetch product details for all wishlist items
    $placeholders = implode(',', array_fill(0, count($wishlist), '?'));
    $stmt = $conn->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->execute($wishlist);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Wishlist - Amit Dairy & Sweets</title>
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/css/main.css" rel="stylesheet">
  <style>
    .wishlist-container { max-width: 1200px; margin: 40px auto; }
    .wishlist-header { font-weight: 700; margin-bottom: 2.5rem; font-size: 2rem; letter-spacing: 1px; }
    .wishlist-grid { display: flex; flex-wrap: wrap; gap: 2rem; justify-content: flex-start; }
    .wishlist-card {
      background: #fff;
      border-radius: 18px;
      box-shadow: 0 4px 24px rgba(214, 182, 105, 0.13);
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 2rem 1.5rem 1.5rem 1.5rem;
      width: 320px;
      position: relative;
      transition: box-shadow 0.2s, transform 0.2s;
    }
    .wishlist-card:hover {
      box-shadow: 0 12px 36px rgba(214, 182, 105, 0.25);
      transform: scale(1.05) translateY(-8px);
      z-index: 2;
    }
    .wishlist-card img {
      width: 180px;
      height: 180px;
      object-fit: cover;
      border-radius: 12px;
      margin-bottom: 1.2rem;
      box-shadow: 0 2px 8px rgba(214, 182, 105, 0.10);
    }
    .wishlist-details {
      text-align: center;
      margin-bottom: 1.2rem;
    }
    .wishlist-details .fw-semibold {
      font-size: 1.3rem;
      font-weight: 700;
      color: #b08a2a;
      margin-bottom: 0.5rem;
    }
    .wishlist-details .mb-1 {
      font-size: 1.1rem;
      color: #d1ae5e;
      font-weight: 600;
      margin-bottom: 0.7rem;
    }
    .wishlist-actions {
      text-align: center;
    }
    .remove-btn {
      color: #d9534f;
      border: none;
      background: none;
      font-size: 1.5rem;
      cursor: pointer;
      position: absolute;
      top: 18px;
      right: 18px;
      z-index: 2;
      transition: color 0.2s;
    }
    .remove-btn:hover { color: #b52a1d; }
    .empty-msg { text-align: center; color: #888; margin: 4rem 0; font-size: 1.3rem; }
    .btn-yellow {
      background-color: #ffe066 !important;
      color: #000 !important;
      border: none !important;
      font-weight: 700;
      font-size: 1.1rem;
      padding: 0.7rem 2.2rem;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(214, 182, 105, 0.10);
      transition: background 0.2s, color 0.2s;
    }
    .btn-yellow:hover, .btn-yellow:focus {
      background-color: #d1ae5e !important;
      color: #000 !important;
    }
    @media (max-width: 900px) {
      .wishlist-grid { flex-direction: column; align-items: center; justify-content: center; }
      .wishlist-card { width: 90vw; max-width: 400px; }
    }
  </style>
</head>
<body class="index-page">
  <main class="main">
    <div class="wishlist-container">
      <div class="wishlist-header text-center">My Wishlist</div>
      <div class="wishlist-intro text-center mb-4" style="font-size:1.1rem; color:#b08a2a; max-width:600px; margin:0 auto 2rem auto;">
        Save your favorite products here for quick access later. Add items to your wishlist and easily find them whenever you return!
      </div>
      <div class="wishlist-search text-left mb-4">
        <input type="text" id="wishlistSearchInput" class="form-control" style="max-width:350px; margin:0 auto; display:inline-block; border-radius:8px; border:1.5px solid #ffe066;" placeholder="🔍 Search your wishlist..." onkeyup="filterWishlist()">
      </div>
      <?php if (!empty($products)): ?>
        <div class="wishlist-grid">
        <?php foreach ($products as $product): ?>
          <div class="wishlist-card">
            <form method="post" action="wishlist" style="display:inline;">
              <input type="hidden" name="remove_id" value="<?= $product['id'] ?>">
              <button type="submit" class="remove-btn" title="Remove from Wishlist"><i class="bi bi-x-circle"></i></button>
            </form>
            <img src="admin/<?= htmlspecialchars($product['product_image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
            <div class="wishlist-details">
              <div class="fw-semibold mb-1"><?= htmlspecialchars($product['name']) ?></div>
              <div class="mb-1">₹ <?= number_format($product['discount_price'], 2) ?></div>
            </div>
            <div class="wishlist-actions">
              <a href="product-details?product_id=<?= $product['id'] ?>" class="btn btn-yellow">View Product</a>
            </div>
          </div>
        <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="empty-msg d-flex flex-column align-items-center justify-content-center" style="min-height: 300px;">
          <i class="bi bi-heart" style="font-size: 3rem; color: #ffe066; margin-bottom: 0.5rem;"></i>
          <div style="font-size: 1.5rem; font-weight: 700; color: #b08a2a; margin-bottom: 0.5rem;">My Wishlist</div>
          <div>Your wishlist is empty.</div>
        </div>
      <?php endif; ?>
    </div>
  </main>
  <?php include('inc/footer.php'); ?>
</body>
<script>
function filterWishlist() {
  const input = document.getElementById('wishlistSearchInput').value.toLowerCase();
  const cards = document.querySelectorAll('.wishlist-card');
  let anyVisible = false;
  cards.forEach(card => {
    const name = card.querySelector('.fw-semibold').textContent.toLowerCase();
    if (name.includes(input)) {
      card.style.display = '';
      anyVisible = true;
    } else {
      card.style.display = 'none';
    }
  });
  // Optionally, show/hide empty message
  const emptyMsg = document.querySelector('.empty-msg');
  if (emptyMsg) {
    emptyMsg.style.display = anyVisible ? 'none' : 'block';
  }
}
</script>
</html>
<?php
// Handle remove from wishlist
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_id'])) {
    $removeId = (int)$_POST['remove_id'];
    if (($key = array_search($removeId, $_SESSION['wishlist'] ?? [])) !== false) {
        unset($_SESSION['wishlist'][$key]);
        // Re-index array
        $_SESSION['wishlist'] = array_values($_SESSION['wishlist']);
    }
    // Redirect to avoid form resubmission
    header('Location: wishlist');
    exit;
} 