<?php
session_start();
require_once 'inc/db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Subcategories - Amit Dairy & Sweets</title>
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <meta name="description" content="">
  <meta name="keywords" content="">

  <!-- Favicons -->
  <link href="assets/img/logo.webp" rel="icon">
  <link href="assets/img/logo.webp" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

  <!-- Template Main CSS File -->
  <link href="assets/css/main.css" rel="stylesheet">

  <!-- SweetAlert2 -->
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- Custom Search Box Styling -->
  <style>
    #subcategorySearch {
      border-radius: 14px;
      padding-left: 2.5rem;
      background-image: url('assets/img/search-icon.png');
      background-repeat: no-repeat;
      background-position: 14px center;
      background-size: 22px;
      background-color: #fffde7;
      color: #b08a2a;
      font-weight: 500;
      border: 2px solid #ffe066;
      box-shadow: 0 4px 18px rgba(214, 182, 105, 0.10);
      transition: border-color 0.2s, box-shadow 0.2s;
    }
    #subcategorySearch:focus {
      border-color: #d4b160;
      box-shadow: 0 0 0 0.2rem rgba(214, 182, 105, 0.25);
      background-color: #fffbe6;
    }
    .subcategory-filter-bar {
      background: linear-gradient(90deg, #fffbe6 0%, #fff3cd 100%);
      border: 2px solid #ffe066;
      border-radius: 18px;
      box-shadow: 0 4px 18px rgba(214, 182, 105, 0.10);
      padding: 1.2rem 2rem;
      margin-bottom: 2.5rem;
      display: flex;
      flex-wrap: wrap;
      align-items: flex-start;
      justify-content: flex-start;
    }
    .subcategory-filter-bar select {
      min-width: 180px;
      background: #fffde7;
      color: #b08a2a;
      font-weight: 500;
      border: 2px solid #ffe066;
      border-radius: 10px;
      box-shadow: none;
      padding: 0.5rem 1.2rem;
      transition: border-color 0.2s;
    }
    .subcategory-filter-bar select:focus {
      border-color: #d4b160;
      background: #fffbe6;
    }
    .subcategory-card .card {
      border-radius: 22px;
      border: none;
      box-shadow: 0 8px 32px rgba(214, 182, 105, 0.13);
      transition: transform 0.2s, box-shadow 0.2s;
      background: linear-gradient(135deg, #fffbe6 0%, #fff3cd 100%);
      position: relative;
      overflow: hidden;
    }
    .subcategory-card .card:hover {
      transform: translateY(-8px) scale(1.03);
      box-shadow: 0 16px 48px rgba(214, 182, 105, 0.22);
      border-color: #ffe066;
    }
    .subcategory-card .card-img-top {
      height: 210px;
      object-fit: cover;
      border-top-left-radius: 22px;
      border-top-right-radius: 22px;
      border-bottom: 3px solid #ffe066;
      background: #fffbe6;
    }
    .subcategory-card .card-title {
      color: #b08a2a;
      font-weight: 700;
      font-size: 1.25rem;
      margin-bottom: 1.1rem;
      letter-spacing: 0.5px;
    }
    .subcategory-card .btn-warning {
      background: linear-gradient(90deg, #ffe066 0%, #d4b160 100%);
      color: #7a5a10;
      font-weight: 600;
      border: none;
      border-radius: 12px;
      padding: 0.6rem 1.5rem;
      box-shadow: 0 2px 8px rgba(214, 182, 105, 0.13);
      transition: background 0.2s, color 0.2s;
    }
    .subcategory-card .btn-warning:hover {
      background: linear-gradient(90deg, #d4b160 0%, #ffe066 100%);
      color: #fff;
    }
    #noResultMessage {
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
</head>

<body class="index-page">
<?php 
include('inc/header.php'); 
include_once('inc/contact_data.php'); 

$categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

// Get category title
$catTitle = '';
$catStmt = $conn->prepare("SELECT title FROM categories WHERE id = ?");
$catStmt->execute([$categoryId]);
$catTitle = $catStmt->fetchColumn();

// Get banner image
$bannerImage = '';
$stmt = $conn->prepare("SELECT image FROM banners WHERE page_name = ?");
$stmt->execute(['subcategory']);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row && !empty($row['image']) && file_exists("admin/" . $row['image'])) {
    $bannerImage = "admin/" . $row['image'];
} else {
    $bannerImage = "assets/img/hero.png"; // fallback image
}

// Get subcategories with images
$stmt = $conn->prepare("SELECT id, title, subcategory_image FROM subcategories WHERE category_id = ?");
$stmt->execute([$categoryId]);
$subcategories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- ✅ Banner with Breadcrumb -->
<main class="main">
  <section class="product-bread" style="background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('<?= htmlspecialchars($bannerImage) ?>'); background-size: cover; background-position: center; background-repeat: no-repeat;">
    <div class="container text-center py-5">
      <div class="d-flex justify-content-center align-items-center mb-3">
        <img src="assets/img/Vector.png" alt="" class="me-2">
        <h2 class="m-0 text-white"><?= htmlspecialchars($catTitle) ?></h2>
        <img src="assets/img/Vector (1).png" alt="" class="ms-2">
      </div>
      <nav aria-label="breadcrumb" class="d-flex justify-content-center">
        <ol class="breadcrumb bg-transparent">
          <li class="breadcrumb-item"><a href="index" class="text-light fw-semibold text-decoration-none">Home</a></li>
          <li class="breadcrumb-item active text-light fw-semibold" aria-current="page"><?= htmlspecialchars($catTitle) ?></li>
        </ol>
      </nav>
    </div>
  </section>
</main>

<!-- ✅ Search + Subcategory Cards -->
<section class="py-5">
  <div class="container">
    <!-- Section Heading and Description -->
    <div class="text-center mb-4">
      <h2 style="color: #d1ae5e; font-weight: 800; letter-spacing: 1px;">Explore Our Sweet </h2>
      <p style="color: #b08a2a; font-size: 1.1rem;">Find your favorite type of sweets and dairy. Use the search and sort options below to quickly discover what you love!</p>
    </div>

    <!-- 🔍 Search Input + Filter Bar -->
    <div class="row mb-4">
      <div class="col-lg-8 mx-auto">
        <div class="subcategory-filter-bar" style="flex-direction: column; align-items: center; gap: 0.7rem;">
          <input type="text" id="subcategorySearch" class="form-control form-control-lg w-100" placeholder="🔍 Search ..." onkeyup="filterSubcategories()">
          <select id="subcategorySort" class="form-select form-select-sm mt-2" style="max-width: 180px;" onchange="sortSubcategories()">
            <option value="">Sort</option>
            <option value="az">A-Z</option>
            <option value="za">Z-A</option>
          </select>
        </div>
      </div>
    </div>
    <div id="noResultMessage" class="col-12 text-center fw-semibold">
      No matching Sub Categories found.
    </div>

   

    <!-- 📦 Subcategory Cards -->
    <div class="row g-4" id="subcategoryContainer">
      <?php if (count($subcategories) > 0): ?>
        <?php foreach ($subcategories as $sub): 
          $subImageFile = $sub['subcategory_image'] ?? '';
          $imagePath = 'admin/' . $subImageFile;
          $subImage = (!empty($subImageFile) && file_exists(__DIR__ . '/' . $imagePath)) 
                      ? $imagePath 
                      : "assets/img/no-image.png";
        ?>
        <div class="col-md-4 col-sm-6 subcategory-card" data-title="<?= strtolower($sub['title']) ?>">
          <div class="card h-100 shadow-sm border-0">
            <img src="<?= htmlspecialchars($subImage) ?>" class="card-img-top" alt="<?= htmlspecialchars($sub['title']) ?>" style="height: 200px; object-fit: cover;">
            <div class="card-body text-center">
              <h5 class="card-title mb-3"><?= htmlspecialchars($sub['title']) ?></h5>
              <a href="product.php?subcategory_id=<?= $sub['id'] ?>" class="btn btn-warning text-dark">
                View Products
              </a>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p class="text-center">No subcategories found under this category.</p>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php include('inc/footer.php'); ?>

<!-- Vendor JS Files -->
<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/vendor/aos/aos.js"></script>
<script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
<script src="assets/js/main.js"></script>

<!-- ✅ Live Search Script -->
<script>
function filterSubcategories() {
  const input = document.getElementById('subcategorySearch');
  const filter = input.value.toLowerCase();
  const cards = document.querySelectorAll('.subcategory-card');
  const noResultMessage = document.getElementById('noResultMessage');

  let anyVisible = false;

  cards.forEach(card => {
    const title = card.getAttribute('data-title');
    if (title.includes(filter)) {
      card.style.display = '';
      anyVisible = true;
    } else {
      card.style.display = 'none';
    }
  });

  // Show/hide "No results found" message
  noResultMessage.style.display = anyVisible ? 'none' : 'block';
}

function sortSubcategories() {
  const sortValue = document.getElementById('subcategorySort').value;
  const container = document.getElementById('subcategoryContainer');
  const cards = Array.from(container.querySelectorAll('.subcategory-card'));
  if (!sortValue) return;
  cards.sort((a, b) => {
    const titleA = a.getAttribute('data-title');
    const titleB = b.getAttribute('data-title');
    if (sortValue === 'az') return titleA.localeCompare(titleB);
    if (sortValue === 'za') return titleB.localeCompare(titleA);
    return 0;
  });
  cards.forEach(card => container.appendChild(card));
}
</script>



</body>
</html>
