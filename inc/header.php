<?php 
ob_start();
?>
<header class="modern-header">
<style>
  /* Modern Header Styles with Yellow Theme */
  .modern-header {
    position: sticky;
    top: 0;
    box-shadow: 0 4px 20px rgba(255, 215, 0, 0.3);
    z-index: 999;
    transition: all 0.3s ease;
  }

  .header-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
  }

  .menu-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 0;
    flex-wrap: wrap;
  }

  .menu-logo {
    flex-shrink: 0;
  }

  .menu-logo img {
    height: 60px;
    transition: transform 0.3s ease;
  }

  .menu-logo img:hover {
    transform: scale(1.05);
  }

  .menu-toggle {
    display: none;
    font-size: 28px;
    background: none;
    border: none;
    cursor: pointer;
    color: #333;
    padding: 8px;
    border-radius: 8px;
    transition: all 0.3s ease;
  }

  .menu-toggle:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: scale(1.1);
  }

  .main-menu {
    flex-grow: 1;
    margin: 0 30px;
  }

  .main-menu > ul {
    display: flex;
    list-style: none;
    margin: 0;
    padding: 0;
    justify-content: center;
    align-items: center;
    gap: 10px;
  }

  .main-menu > ul > li {
    position: relative;
  }

  .main-menu > ul > li > a {
    display: block;
    padding: 12px 20px;
    color: #666;
    text-decoration: none;
    font-weight: 500;
    font-size: 16px;
    border-radius: 25px;
    transition: all 0.3s ease;
    position: relative;
    background: transparent;
  }

  .main-menu > ul > li > a:hover {
    color: #333;
    background: rgba(255, 255, 255, 0.2);
    transform: translateY(-1px);
  }

  /* Enhanced Dropdown Grid */
  @media (min-width: 992px) {
    .dropdown-grid {
      display: none;
      position: absolute;
      top: 120%;
      left: 50%;
      transform: translateX(-50%);
      width: 800px;
      background: #fff;
      padding: 25px;
      grid-template-columns: repeat(4, 1fr);
      gap: 20px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
      border-radius: 15px;
      z-index: 1000;
      animation: fadeInUp 0.3s ease;
    }

    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateX(-50%) translateY(-10px);
      }
      to {
        opacity: 1;
        transform: translateX(-50%) translateY(0);
      }
    }

    .main-menu > ul > li:hover .dropdown-grid {
      display: grid;
    }

    .dropdown-grid::before {
      content: '';
      position: absolute;
      top: -10px;
      left: 50%;
      transform: translateX(-50%);
      width: 0;
      height: 0;
      border-left: 10px solid white ;
      border-right: 10px solid white;
      border-bottom: 10px solid #fff;
    }
  }

  /* Mobile Responsive */
  @media (max-width: 991.98px) {
    .menu-toggle {
      display: block;
    }

    .main-menu {
      width: 100%;
      display: none;
      flex-direction: column;
      background: #fff;
      margin: 0;
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
      overflow: hidden;
    }

    .main-menu.show {
      display: flex;
      animation: slideDown 0.3s ease;
    }

    @keyframes slideDown {
      from {
        opacity: 0;
        transform: translateY(-20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .main-menu > ul {
      flex-direction: column;
      padding: 0;
      gap: 0;
    }

    .main-menu > ul > li {
      width: 100%;
      border-bottom: 1px solid #f0f0f0;
    }

    .main-menu > ul > li:last-child {
      border-bottom: none;
    }

    .main-menu > ul > li > a {
      padding: 15px 20px;
      border-radius: 0;
      font-size: 16px;
      color: #666;
      background: transparent;
    }

    .main-menu > ul > li > a:hover {
      background: rgba(255, 215, 0, 0.1);
      color: #333;
      transform: none;
    }

    .dropdown-grid {
      display: none;
      flex-direction: column;
      background: #f8f9fa;
      width: 100%;
      margin: 0;
      padding: 15px 20px;
      grid-template-columns: 1fr;
      position: relative;
      transform: none;
      left: 0;
      box-shadow: none;
      border-radius: 0;
    }

    .dropdown-grid.show {
      display: grid !important;
      animation: slideIn 0.3s ease;
    }

    @keyframes slideIn {
      from {
        opacity: 0;
        transform: translateX(-10px);
      }
      to {
        opacity: 1;
        transform: translateX(0);
      }
    }

    .category-column {
      margin-bottom: 20px;
    }
  }

  /* Header Icons */
  .header-icons {
    display: flex;
    align-items: center;
    gap: 15px;
    flex-shrink: 0;
  }

  .header-icons a {
    align-items: center;
    justify-content: center;
    width: 45px;
    height: 45px;
   
    color: #333;
    text-decoration: none;
    font-size: 18px;
    transition: all 0.3s ease;
    position: relative;
  }

  .header-icons a:hover {
    
    
    transform: translateY(-2px);
  }

  .header-icons .badge {
    font-size: 10px;
    padding: 4px 6px;
    background: #ff4757 !important;
    border: 2px solid #fff;
  }

  /* Category Styles */
  .category-column {
    list-style: none;
    text-align: left;
  }

  .category-name {
    font-weight: 600;
    font-size: 16px;
    margin-bottom: 12px;
    display: block;
    color: #333;
    text-decoration: none;
    padding: 10px 15px;
    border-radius: 8px;
    transition: all 0.3s ease;
    background: rgba(255, 215, 0, 0.1);
    color: #333;
  }

  .category-name:hover {
    background: rgba(255, 215, 0, 0.2);
    transform: translateX(5px);
    color: #333;
  }

  .subcategory-list {
    list-style: none;
    margin: 0;
    padding: 0;
  }

  .subcategory-list li {
    margin-bottom: 8px;
  }

  .subcategory-list li a {
    display: block;
    font-size: 14px;
    color: #666;
    text-decoration: none;
    padding: 8px 15px;
    border-radius: 6px;
    transition: all 0.3s ease;
    position: relative;
  }

  .subcategory-list li a::before {
    content: '→';
    position: absolute;
    left: 0;
    opacity: 0;
    transition: all 0.3s ease;
  }

  .subcategory-list li a:hover {
    color: #333;
    background: rgba(255, 215, 0, 0.1);
    padding-left: 25px;
    transform: translateX(5px);
  }

  .subcategory-list li a:hover::before {
    opacity: 1;
  }

  .view-more {
    font-weight: 600;
    color: #666;
    display: inline-block;
    margin-top: 8px;
    padding: 8px 15px;
    background: rgba(255, 215, 0, 0.1);
    border-radius: 6px;
    transition: all 0.3s ease;
  }

  .view-more:hover {
    background: rgba(255, 215, 0, 0.2);
    color: #333;
    transform: translateX(5px);
  }

  /* Responsive adjustments */
  @media (max-width: 768px) {
    .header-container {
      padding: 0 15px;
    }

    .menu-container {
      padding: 10px 0;
    }

    .menu-logo img {
      height: 50px;
    }

    .header-icons {
      gap: 10px;
    }

    .header-icons a {
      width: 40px;
      height: 40px;
      font-size: 16px;
    }
  }

  @media (max-width: 480px) {
    .menu-logo img {
      height: 45px;
    }

    .header-icons a {
      width: 35px;
      height: 35px;
      font-size: 14px;
    }
  }
</style>

<script>
  document.addEventListener("DOMContentLoaded", function () {
    const toggleBtn = document.getElementById("menuToggle");
    const menu = document.querySelector(".main-menu");
    const header = document.querySelector(".modern-header");

    // Mobile menu toggle
    toggleBtn.addEventListener("click", function () {
      menu.classList.toggle("show");
      this.classList.toggle("active");
    });

    // Mobile dropdown toggle
    document.querySelectorAll(".main-menu > ul > li > a").forEach((link) => {
      link.addEventListener("click", function (e) {
        const parent = this.parentElement;
        const dropdown = parent.querySelector(".dropdown-grid");

        if (window.innerWidth <= 991 && dropdown) {
          e.preventDefault();
          dropdown.classList.toggle("show");
        }
      });
    });

    // Header scroll effect
    let lastScroll = 0;
    window.addEventListener("scroll", () => {
      const currentScroll = window.pageYOffset;
      
      if (currentScroll > lastScroll && currentScroll > 100) {
        header.style.transform = "translateY(-100%)";
      } else {
        header.style.transform = "translateY(0)";
      }
      
      lastScroll = currentScroll;
    });

    // Close mobile menu when clicking outside
    document.addEventListener("click", function(e) {
      if (!menu.contains(e.target) && !toggleBtn.contains(e.target)) {
        menu.classList.remove("show");
        toggleBtn.classList.remove("active");
      }
    });

    // Close mobile menu on window resize
    window.addEventListener("resize", function() {
      if (window.innerWidth > 991) {
        menu.classList.remove("show");
        toggleBtn.classList.remove("active");
      }
    });
  });
</script>

  <div class="header-container">
    <div class="menu-container">
      <div class="menu-logo">
        <a href="index">
          <img src="assets/img/logo.png" alt="Amit Dairy & Sweets Logo">
        </a>
      </div>

      <button class="menu-toggle" id="menuToggle">
        <i class="bi bi-list"></i>
      </button>

      <nav class="main-menu">
        <ul>
          <li><a href="index"><i class="bi bi-house-door me-2"></i>Home</a></li>
          <li><a href="about"><i class="bi bi-info-circle me-2"></i>About</a></li>
          <li>
            <a href="#"><i class="bi bi-grid-3x3-gap me-2"></i>Categories</a>
            <ul class="dropdown-grid">
              <?php
              include 'db.php';
              $stmt = $conn->prepare("SELECT id, title FROM categories ORDER BY title ASC");
              $stmt->execute();
              $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

              foreach ($categories as $category) {
                $categoryId = $category['id'];
                $subStmt = $conn->prepare("SELECT id, title FROM subcategories WHERE category_id = ? ORDER BY title ASC");
                $subStmt->execute([$categoryId]);
                $subcategories = $subStmt->fetchAll(PDO::FETCH_ASSOC);

                echo '<li class="category-column">';
                echo '<a href="subcategory.php?category_id=' . $categoryId . '" class="category-name">' . htmlspecialchars($category['title']) . '</a>';
                if (!empty($subcategories)) {
                  echo '<ul class="subcategory-list">';
                  foreach ($subcategories as $index => $sub) {
                    if ($index === 3) {
                      echo '<li><a href="subcategory.php?category_id=' . $categoryId . '" class="view-more">View More →</a></li>';
                      break;
                    }
                    echo '<li><a href="product.php?subcategory_id=' . $sub['id'] . '">' . htmlspecialchars($sub['title']) . '</a></li>';
                  }
                  echo '</ul>';
                }
                echo '</li>';
              }
              ?>
            </ul>
          </li>
          <li><a href="contact-us"><i class="bi bi-telephone me-2"></i>Contact Us</a></li>
          <li><a href="subscription"><i class="bi bi-calendar-check me-2"></i>Subscription</a></li>
        </ul>
      </nav>

      <div class="header-icons">
        <a href="search" title="Search">
          <i class="bi bi-search"></i>
        </a>
        <a href="wishlist" title="Wishlist" class="position-relative">
          <i class="bi bi-heart"></i>
          <?php if (!empty($_SESSION['wishlist'])): ?>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.7rem;">
              <?= count($_SESSION['wishlist']) ?>
            </span>
          <?php endif; ?>
        </a>
        <a href="cart" title="Shopping Cart" class="position-relative">
          <i class="bi bi-cart-fill"></i>
          <?php if (!empty($_SESSION['cart'])): ?>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill">
              <?= count($_SESSION['cart']) ?>
            </span>
          <?php endif; ?>
        </a>
        <?php if (!empty($_SESSION['user_id'])): ?>
          <a href="user-dashboard" title="My Account">
            <i class="bi bi-person-fill"></i>
          </a>
        <?php else: ?>
          <a href="register" title="Login/Register">
            <i class="bi bi-box-arrow-in-right"></i>
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</header>