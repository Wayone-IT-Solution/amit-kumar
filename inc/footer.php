
<?php include_once ('contact_data.php');  ?>
<footer id="footer" class="footer position-relative light-background">

    <div class="container footer-top">
      <div class="row gy-4">
        <div class="col-lg-3 col-md-6 footer-about">
          <a href="index.html" class="logo d-flex align-items-center">
            <img src="assets/img/footer-logo.png" alt="">
          </a>
          <div class="footer-contact">
            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut </p>
            
          </div>
          <div class="social-links d-flex mt-4">
            <a href="<?= htmlspecialchars($contact['twitter'] ?? '') ?>"><i class="bi bi-twitter-x"></i></a>
            <a href="<?= htmlspecialchars($contact['facebook']) ?>"><i class="bi bi-facebook"></i></a>
            <a href="<?= htmlspecialchars($contact['instagram']) ?>"><i class="bi bi-instagram"></i></a>
          </div>
        </div>

        <div class="col-lg-2 col-md-3 footer-links">
          <h4>Useful Links</h4>
          <ul>
            <li><a href="index">Home</a></li>
            <li><a href="about">About us</a></li>
            <li><a href="faq">FAQs</a></li>
            <li><a href="contact-us">Contact Us</a></li>
          </ul>
        </div>

        <div class="col-lg-2 col-md-3 footer-links">
          <h4>Categories</h4>
                        <ul>
                        <?php
require_once 'db.php';

try {
    // Only fetch active categories
    $stmt = $conn->query("SELECT id, title FROM categories WHERE status = 1 ORDER BY title ASC");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($categories) {
        $visibleCount = 6;
        $count = 0;

        foreach ($categories as $cat) {
            $hiddenClass = ($count >= $visibleCount) ? ' class="hidden-category" style="display:none;"' : '';
            echo '<li' . $hiddenClass . '><a href="product?category_id=' . $cat['id'] . '">' . htmlspecialchars($cat['title']) . '</a></li>';
            $count++;
        }

        if (count($categories) > $visibleCount) {
            echo '<li id="showMoreBtn"><a href="javascript:void(0);" onclick="showMoreCategories()">Show More</a></li>';
        }
    } else {
        echo '<li>No active categories found.</li>';
    }
} catch (PDOException $e) {
    echo '<li>Error fetching categories.</li>';
}
?>


              </ul>
                    <script>
                    function showMoreCategories() {
                        const hidden = document.querySelectorAll('.hidden-category');
                        hidden.forEach(el => el.style.display = 'flex');
                        document.getElementById('showMoreBtn').style.display = 'none';
                    }
                    </script>
                    <style>
                      .hidden-category {
                        display: none;
                    }

                    </style>
        </div>

        <div class="col-lg-2 col-md-3 footer-links">
          <h4>Others</h4>
          <ul>
            <li><a href="privacy-policy">Privacy Policy</a></li>
            <li><a href="terms-and-conditions">Terms & Conditions</a></li>
            <li><a href="return-policy">Return Policy</a></li>
            <li><a href="desclaimer">Desclaimer</a></li>
            <li><a href="refund-policy">Refund Policy</a></li>
          </ul>
        </div>

        <div class="col-lg-3 col-md-3 footer-links justify-content-center text-center">
          <h4>Download Our App</h4>
          <img src="assets/img/qr.png" alt="">
          <img src="assets/img/gs.png" class="mt-2" alt="">
          <img src="assets/img/ps.png" class="mt-2" alt="">
        </div>

      </div>
      <div class="container">
      <div class="row justify-content-center mt-5">
        <div class="col-lg-6">
          <div class="newsletter-box text-center">
            <h3 class="mb-3">Subscribe to Our Newsletter</h3>
            <form id="newsletter-form" class="newsletter-form position-relative">
              <input type="email" class="form-control" placeholder="Enter your email address" required>
              <button type="submit" class="btn btn-primary subscribe-btn">
                Subscribe
                <i class="bi bi-send ms-2"></i>
              </button>
            </form>
          </div>
        </div>
      </div>

      

      <script>
        document.getElementById('newsletter-form').addEventListener('submit', function(e) {
          e.preventDefault();
          const email = this.querySelector('input[type="email"]').value;
          
          // Add animation
          const btn = this.querySelector('.subscribe-btn');
          btn.innerHTML = '<i class="bi bi-check-circle"></i> Thanks!';
          btn.style.background = '#28a745';
          
          // Reset after 3 seconds
          setTimeout(() => {
            btn.innerHTML = 'Subscribe <i class="bi bi-send ms-2"></i>';
            btn.style.background = '#ff6b6b';
            this.reset();
          }, 3000);
          
          // Here you can add AJAX call to submit email to backend
        });
      </script>
      </div>
    </div>

    <div class="container copyright text-center mt-4">
      <p>All Rights Reserved @ Amit Dairy & Sweets</p>
      
      </div>
    </div>

  </footer>

  <!-- Scroll Top -->
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i
      class="bi bi-arrow-up-short"></i></a>

      <script>
document.getElementById('newsletter-form').addEventListener('submit', function (e) {
  e.preventDefault();

  const form = e.target;
  const email = form.querySelector('input[type="email"]').value;

  fetch('inc/subscribe_newsletter', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'email=' + encodeURIComponent(email)
  })
  .then(response => response.json())
  .then(data => {
    if (data.status === 'success') {
      showSuccess('Subscribed!', data.message);
      form.reset();
    } else {
      showError('Oops!', data.message);
    }
  })
  .catch(() => {
    showError('Error', 'Something went wrong. Please try again later.');
  });
});
</script>

<style>
  /* Base mobile footer tweaks */
@media (max-width: 991px) {
  .footer .footer-top {
    padding: 30px 15px;
  }

  .footer .footer-top .row > div {
    margin-bottom: 30px;
    text-align: center;
  }

  .footer .footer-about .footer-contact p {
    font-size: 14px;
  }

  .footer .social-links {
    justify-content: center;
    gap: 12px;
  }

  .footer-links h4 {
    font-size: 16px;
    margin-bottom: 10px;
  }

  .footer-links ul {
    padding: 0;
    list-style: none;
  }

  .footer-links ul li {
    margin-bottom: 8px;
  }

  .footer-links ul li a {
    font-size: 14px;
  }

  .footer .footer-links.text-center {
    text-align: center !important;
  }

  .footer .footer-links img {
    max-width: 100px;
    height: auto;
    margin: 0 auto;
    display: block;
  }

  .newsletter-box h3 {
    font-size: 20px;
    margin-bottom: 15px;
  }

  .newsletter-form {
    flex-direction: column;
    gap: 10px;
  }

  .newsletter-form input[type="email"],
  .newsletter-form button {
    width: 100%;
  }

  .copyright {
    font-size: 13px;
    margin-top: 20px;
  }
}

@media (max-width: 576px) {
  .footer .footer-top .row {
    flex-direction: column;
    align-items: center;
  }

  .footer .footer-top .col-lg-3,
  .footer .footer-top .col-lg-2 {
    max-width: 100%;
    flex: 0 0 100%;
  }

  .footer .newsletter-box h3 {
    font-size: 18px;
  }
}

</style>
