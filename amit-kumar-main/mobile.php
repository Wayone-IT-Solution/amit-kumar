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

  <!-- Main CSS File -->
  <link href="assets/css/main.css" rel="stylesheet">


</head>

<body class="index-page">

  <?php include ('inc/header.php'); ?>
  <?php


if (isset($_SESSION['user_id'])) {
    header("Location: user-dashboard");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $phone = trim($_POST['phone']);

    if (empty($phone)) {
        $_SESSION['error'] = "Phone number is required.";
        header("Location: login");
        exit;
    }

    try {
        $stmt = $conn->prepare("SELECT id, phone, status FROM users WHERE phone = ?");
        $stmt->execute([$phone]);
        $user = $stmt->fetch();

        if ($user) {
            if (strtolower($user['status']) === 'inactive') {
                $_SESSION['error'] = "Your account is inactive. Please contact the website owner.";
                header("Location: login");
                exit;
            }

            // Generate OTP and store in session
            $otp = rand(100000, 999999);
            $_SESSION['login_otp'] = $otp;
            $_SESSION['login_phone'] = $phone;
            $_SESSION['login_user_id'] = $user['id'];

            // Show SweetAlert and OTP modal
            echo "
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    Swal.fire({
                        icon: 'success',
                        title: 'OTP Sent!',
                        text: 'OTP sent to $phone (for demo: $otp)',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        let otpModal = new bootstrap.Modal(document.getElementById('otpModal'));
                        otpModal.show();
                    });
                });
            </script>
            ";

        } else {
            $_SESSION['error'] = "Invalid phone number. Please try again or register.";
            header("Location: login");
            exit;
        }

    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        $_SESSION['error'] = "An error occurred during login.";
        header("Location: login");
        exit;
    }
}
?>



  <main class="main">
    
<style>

   
  </style>


<div class="container form-container">
  <div class="form-title">Welcom Back !</div>
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="login">E-Mail Login</a></li>
      <li class="breadcrumb-item"><a href="mobile">Mobile Login</a></li>
    </ol>
  </nav>
<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_SESSION['error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

  <form action="login" method="POST">
  <div class="mb-3">
  <label for="phone" class="form-label">Phone Number</label>
  <input type="tel" name="phone" class="form-control" id="phone" required 
         pattern="\d{10}" maxlength="10" minlength="10" 
         title="Please enter a 10-digit phone number" 
         inputmode="numeric">
</div>
    

    <div class="form-check mb-2">
      <input class="form-check-input" type="checkbox" value="" id="terms" checked required>
      <label class="form-check-label">Agree to <a href="terms-and-conditions">terms & Condition</a> </label>
    </div>




    <button type="submit" class="btn btn-submit">Login</button>
    <button type="button" onclick="redirectToRegister()" class="btn btn-login">Create Account</button>

<script>
function redirectToRegister() {
    window.location.href = 'register'; // Change to the actual path of your register page
}
</script>
<script>
document.getElementById("phone").addEventListener("input", function () {
  this.value = this.value.replace(/\D/g, '').slice(0, 10); // Remove non-digits and limit to 10
});
</script>

  </form>
</div>




    

<!-- OTP Modal -->
<div class="modal fade" id="otpModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" action="inc/verify_login_otp" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Verify OTP</h5>
      </div>
      <div class="modal-body">
        <input type="number" name="entered_otp" class="form-control mb-2" placeholder="Enter OTP" required>
      </div>
      <div class="modal-footer">
      <button type="button" style="text-decoration: none; color: #000;" class="btn btn-link p-0" onclick="resendLoginOtp()">Resend OTP</button>
        <button type="submit" class="btn btn-primary">Verify</button>
      </div>
    </form>
  </div>
</div>

<script>
function resendLoginOtp() {
    fetch('inc/resend_login_otp', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    })
    .then(response => response.text())
    .then(data => {
        Swal.fire({
            icon: 'success',
            title: 'OTP Resent',
            text: data || 'A new OTP has been sent to your phone.',
            confirmButtonText: 'OK'
        });
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to resend OTP. Please try again.',
            confirmButtonText: 'Retry'
        });
    });
}
</script>

   

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
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script>
document.addEventListener('DOMContentLoaded', function () {
    const params = new URLSearchParams(window.location.search);
    if (params.get('otp') === 'retry') {
        const otpModal = new bootstrap.Modal(document.getElementById('otpModal'));
        otpModal.show();
    }
});
</script>


</body>

</html>