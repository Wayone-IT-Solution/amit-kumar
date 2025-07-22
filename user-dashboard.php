
<?php
session_start();
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Amit Dairy & Sweets</title>
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
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


  <!-- Main CSS File -->
  <link href="assets/css/main.css" rel="stylesheet">


</head>

<body class="index-page">

  <?php include ('inc/header.php'); ?>
  <?php
$userId = $_SESSION['user_id']; // Make sure the user is logged in

// Fetch user's name
$userStmt = $conn->prepare("SELECT fullname FROM users WHERE id = ?");
$userStmt->execute([$userId]);
$user = $userStmt->fetch(PDO::FETCH_ASSOC);
$fullname = $user ? $user['fullname'] : 'Guest';

// Fetch orders
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC");
$stmt->execute([$userId]);
$addresses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>



  <main class="main">





 <div class="container p-4">
    <div class="row">
        
      <!-- Left Panel -->
      <div class="col-md-5">
        
<div class="text-center  mb-4">
          <div class="icon-circle mx-auto">
            <i class="fa fa-user text-brown fs-3"></i>
          </div>
          <p class="mt-2 mb-0 fw-semibold text-dark">Hi, <?= htmlspecialchars($fullname) ?></p>

        </div>
        <div class="side-menu">
          <a href="user-dashboard" class="menu-item active">Address<i class="fa fa-location-dot me-2"></i></a>
          <a href="order-history" class="menu-item">Order History<i class="fa fa-receipt me-2"></i></a>
          <!-- <a href="track-order" class="menu-item">Track your orders<i class="fa fa-truck me-2"></i></a> -->
          <a href="contact-us" class="menu-item">Contact Us<i class="fa fa-headset me-2"></i></a>
          <a href="terms-and-conditions" class="menu-item">Terms & Conditions<i class="fa fa-file-alt me-2"></i></a>
          <a href="privacy-policy" class="menu-item">Privacy Policy<i class="fa fa-lock me-2"></i></a>
          <a href="refund-policy" class="menu-item">Refund Policy<i class="fa fa-undo me-2"></i></a>
        </div>

      <a href="logout" class="btn logout-btn w-100 mt-4">Logout</a> 
      </div>

      <!-- Right Panel -->
      <div class="col-md-7 address">
        <h6 class="fw-bold mb-4">Added Addresses</h6>
        <div class="row g-4">
          <!-- Home Address -->
          <?php if (!empty($addresses)): ?>
  <?php foreach ($addresses as $address): ?>
    <?php
      $saveAs = strtolower(trim($address['save_as'] ?? ''));
      $iconClass = 'fa-solid fa-circle-ellipsis'; // default

      if ($saveAs === 'home') {
        $iconClass = 'fa-solid fa-house';
      } elseif ($saveAs === 'work') {
        $iconClass = 'fa-solid fa-building';
      } elseif ($saveAs === 'family & friends' || $saveAs === 'friends & family') {
        $iconClass = 'fa-solid fa-user-group';
      }
    ?>
    <div class="col-md-12 mb-3">
      <div class="address-box">
        <div class="mb-2 fw-bold text-dark">
          <i class="<?= $iconClass ?> me-2"></i><?= htmlspecialchars(ucfirst($address['save_as'] ?? 'Address')) ?>
        </div>
        <p class="text-dark mb-3">
          <?= htmlspecialchars($address['address_details']) ?>,
          <?= htmlspecialchars($address['house_block']) ?>,
          <?= htmlspecialchars($address['area_road']) ?>,
          <?= htmlspecialchars($address['receiver_name']) ?>,
          <?= htmlspecialchars($address['receiver_phone']) ?>
        </p>
        <div class="d-flex gap-2">
          <button 
            class="editaddress-btn px-3 btn btn-primary btn-sm"
            data-id="<?= $address['id'] ?>"
            data-address="<?= htmlspecialchars($address['address_details']) ?>"
            data-house="<?= htmlspecialchars($address['house_block']) ?>"
            data-area="<?= htmlspecialchars($address['area_road']) ?>"
            data-saveas="<?= htmlspecialchars($address['save_as']) ?>"
            data-name="<?= htmlspecialchars($address['receiver_name']) ?>"
            data-phone="<?= htmlspecialchars($address['receiver_phone']) ?>"
          >Edit Address</button>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
<?php else: ?>
  <div class="col-12 text-center py-4">
    <h4 class="text-muted">No address is found.</h4>
  </div>
<?php endif; ?>





          <!-- Office Address -->
          
        </div>
      </div>
    </div>
  </div>

  <script>
document.querySelectorAll('.editaddress-btn').forEach(btn => {
  btn.addEventListener('click', function () {
    // Fill form fields
    document.getElementById('address_id').value = this.dataset.id;
    document.getElementById('modal_address_details').value = this.dataset.address || '';
    document.getElementById('modal_house_block').value = this.dataset.house || '';
    document.getElementById('modal_area_road').value = this.dataset.area || '';
    document.getElementById('modal_receiver_name').value = this.dataset.name || '';
    document.getElementById('modal_receiver_phone').value = this.dataset.phone || '';

    // Select the correct radio
    const saveAs = (this.dataset.saveas || '').toLowerCase();
    const radioMap = {
      'home': 'home',
      'work': 'work',
      'family & friends': 'friends',
      'others': 'others'
    };
    const selectedRadio = radioMap[saveAs] || 'others';
    document.getElementById(selectedRadio).checked = true;

    // Show the modal
    const modal = new bootstrap.Modal(document.getElementById('editaddressModal'));
    modal.show();
  });
});
</script>


<div class="modal fade" id="editaddressModal" tabindex="-1" aria-labelledby="editaddressModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form id="placeOrderForm" method="post" class="modal-content border-0 shadow rounded-4" style="padding: 30px; background-color: #fff;">
      <div class="modal-body p-0">

        <!-- Hidden Address ID (for editing) -->
        <input type="hidden" name="address_id" id="address_id">

        <!-- Address Title -->
        <div class="mb-4">
          <div class="d-flex align-items-start">
            <div class="me-2"><i class="bi bi-geo-alt-fill text-warning fs-4"></i></div>
            <div>
              <div class="fw-bold" id="modal-title">Edit Address</div>
              <div class="text-muted small">Please update your delivery details below</div>
            </div>
          </div>
        </div>

        <!-- Address Fields -->
        <div class="mb-3">
          <input type="text" id="modal_address_details" class="form-control border-0 rounded-3" name="address_details" placeholder="Add Detailed Address to reach your Doorstep easily" required>
        </div>

        <div class="mb-3">
          <label class="form-label small">House / Flat / Block No.</label>
          <input type="text" id="modal_house_block" class="form-control border-0 rounded-3" name="house_block" required>
        </div>

        <div class="mb-3">
          <label class="form-label small">Apartment / Road / Area</label>
          <input type="text" id="modal_area_road" class="form-control border-0 rounded-3" name="area_road" required>
        </div>

        <!-- Save As Buttons -->
        <label class="form-label small d-block mb-2">Save As</label>
        <div class="mb-4 d-flex flex-wrap gap-2">
          <div>
            <input type="radio" class="btn-check" name="save_as" id="home" value="Home">
            <label class="btn-save px-3" for="home">Home</label>
          </div>
          <div>
            <input type="radio" class="btn-check" name="save_as" id="work" value="Work">
            <label class="btn-save px-3" for="work">Work</label>
          </div>
          <div>
            <input type="radio" class="btn-check" name="save_as" id="friends" value="Friends & Family">
            <label class="btn-save px-3" for="friends">Friends & Family</label>
          </div>
          <div>
            <input type="radio" class="btn-check" name="save_as" id="others" value="Others">
            <label class="btn-save px-3" for="others">Others</label>
          </div>
        </div>

        <!-- Receiver Info -->
        <div class="mb-3">
          <label class="form-label small">Receiver's Name</label>
          <input type="text" id="modal_receiver_name" class="form-control border-0 rounded-3" name="receiver_name" required>
        </div>

        <div class="mb-4">
          <label class="form-label small">Receiver's Phone Number</label>
          <input type="text" id="modal_receiver_phone" class="form-control border-0 rounded-3" name="receiver_phone" required>
        </div>

        <!-- Save Button -->
        <div class="d-grid">
          <button type="submit" class="btn rounded-pill text-white fw-semibold" style="background-color: #d1ae5e;">
            Save Address
          </button>
        </div>
      </div>
    </form>
  </div>
</div>
   <style>
    


.icon-circle {
  background-color: #f4ede4;
  width: 56px;
  height: 56px;
  border-radius: 50%;
  display: flex;
  justify-content: center;
  align-items: center;
}

.text-brown {
  color: #a1782a;
}

.side-menu .menu-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background-color: #fdf6ee;
  padding: 20px 15px;
  margin-bottom: 8px;
  color: #333;
  font-weight: 500;
  border-radius: 6px;
  text-decoration: none;
  transition: background 0.3s;
}

.side-menu .menu-item:hover,
.side-menu .menu-item.active {
  border-left: 3px solid #d4b158;
  background-color: #fdf6ee;
  color: #000;
}

.logout-btn {
  border: 1px solid #d4b158;
  color: #000;
  background: #fff;
  border-radius: 6px;
  padding: 10px 0;
  font-weight: 500;
}

.logout-btn:hover {
  background: #fdf6ee;
}

.address-box {
  background-color: #fdf6ee;
  border-radius: 10px;
  padding: 20px 30px;
  min-height: 240px;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.03);
}

.address-box p{
    font-size: 20px;
}

.address-box .deleteaddress-btn {
    padding: 10px 30px;
    color: red;
    background-color: #fff;
    border-radius: 15px;
    border: none;
}

.address-box .editaddress-btn{
    padding: 10px 30px;
    color: #000;
    background-color: #fff;
    border-radius: 15px;
    border: none;
}

.address{
    margin-top: 100px;
}
</style>
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
  background-color: #d4b160; /* Change this to your desired highlight color */
  color: #fff;
  border-color: #d4b160;
}

.btn-save:hover {
  background-color: #f5e6c9;
}

.form-control{
  background-color: #D6B66933;
}

   </style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.getElementById('placeOrderForm').addEventListener('submit', function(e) {
  e.preventDefault();
  const form = e.target;
  const formData = new FormData(form);

  fetch('inc/edit_address', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(response => {
    if (response.status === 'success') {
      showSuccess('Success', response.message);
      setTimeout(() => location.reload(), 2000);
    } else {
      showError('Error', response.message);
    }
  })
  .catch(() => {
    showError('Error', 'Something went wrong.');
  });
});
</script>


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