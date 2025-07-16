<?php
session_start();
require_once("inc/db.php"); // ✅ your PDO DB connection file

// ✅ 1. Store OTP + forgot input (email/phone)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_otp'])) {
    $_SESSION['reset_otp'] = $_POST['set_otp'];
    $_SESSION['forgot_input'] = $_POST['forgot_input'] ?? null;
    echo json_encode(['status' => true]);
    exit;
}

// ✅ 2. Verify OTP
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['entered_otp'])) {
    header('Content-Type: application/json');

    $enteredOtp = $_POST['entered_otp'] ?? '';
    $sessionOtp = $_SESSION['reset_otp'] ?? '';

    if ($enteredOtp === $sessionOtp) {
        $_SESSION['otp_verified'] = true;
        echo json_encode(['status' => true]);
    } else {
        echo json_encode(['status' => false, 'message' => 'Invalid OTP']);
    }
    exit;
}

// ✅ 3. Reset password and update in database
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_password'])) {
    $newPass = $_POST['new_password'] ?? '';
    $confirmPass = $_POST['confirm_password'] ?? '';
    $forgotInput = $_SESSION['forgot_input'] ?? null;

    if ($newPass !== $confirmPass) {
        $_SESSION['password_reset_error'] = "Passwords do not match.";
    } elseif (empty($forgotInput)) {
        $_SESSION['password_reset_error'] = "Session expired. Please try again.";
    } else {
        // ✅ Hash the password
        $hashedPassword = password_hash($newPass, PASSWORD_DEFAULT);

        // ✅ Update in users table (match email or phone)
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ? OR phone = ?");
        $stmt->execute([$hashedPassword, $forgotInput, $forgotInput]);

        // ✅ Clear session & redirect with success
        unset($_SESSION['reset_otp'], $_SESSION['otp_verified'], $_SESSION['forgot_input']);
        $_SESSION['password_reset_success'] = true;

        header("Location: login");
        exit;
    }
}
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_input = trim($_POST['login_input'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($login_input) || empty($password)) {
        $_SESSION['error'] = "Please enter email/phone and password.";
        header("Location: login");
        exit;
    }

    try {
        // ✅ Determine if login_input is phone or email
        if (is_numeric($login_input) && strlen($login_input) === 10) {
            $stmt = $conn->prepare("SELECT id, phone, password, status FROM users WHERE phone = ?");
        } else {
            $stmt = $conn->prepare("SELECT id, email, password, status FROM users WHERE email = ?");
        }

        $stmt->execute([$login_input]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // ✅ Check if account is active
            if (strtolower($user['status']) !== 'active') {
                $_SESSION['error'] = "Your account is inactive. Please contact support.";
                header("Location: login");
                exit;
            }

            // ✅ Verify password hash
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                header("Location: user-dashboard");
                exit;
            } else {
                $_SESSION['error'] = "Incorrect password.";
                header("Location: login");
                exit;
            }
        } else {
            $_SESSION['error'] = "Account not found with provided email or phone.";
            header("Location: login");
            exit;
        }

    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        $_SESSION['error'] = "An error occurred. Please try again.";
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
      <li class="breadcrumb-item"><a href="#">Home</a></li>
      <li class="breadcrumb-item active" aria-current="page">Login Account</li>
    </ol>
  </nav>
<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_SESSION['error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<form action="login" method="POST" novalidate>
  <!-- Email or Phone -->
   <!-- ✅ Green loading text (hidden by default) -->
<div id="loginMessage" class="text-success mt-2" style="display: none;">
  Logging in, please wait...
</div>

  <div class="mb-3">
    <label for="login_input" class="form-label">Email or Phone</label>
    <input type="text" name="login_input" class="form-control" id="login_input"
           placeholder="Enter your email or 10-digit phone number" required
           oninput="validateLoginInput(this)">
    <div id="login_input_error" class="form-text text-danger d-none">Please enter a valid email or 10-digit phone number.</div>
  </div>

  <!-- Password -->
  <div class="mb-3">
    <label for="password" class="form-label">Password</label>
    <input type="password" name="password" class="form-control" id="password"
           placeholder="Enter your password" required>
  </div>

  <!-- Terms Checkbox -->
  <div class="form-check mb-2">
  <input class="form-check-input" type="checkbox" id="terms" required>
  <label class="form-check-label" for="terms">
    I agree to the 
    <a href="#" target="_blank">Terms & Conditions</a> and 
    <a href="#" target="_blank">Privacy Policy</a>.
  </label>
  <!-- ❗ Error message hidden by default -->
  <div id="termsError" class="text-danger mt-1" style="display: none;">
    Please agree to the Terms & Conditions and Privacy Policy.
  </div>
</div>



  <!-- Forgot password -->
  <div class="text-end mb-2">
    <button type="button" onclick="openForgotModal()" class="btn btn-link" style="text-decoration: none; color:#d4af66;">
      Forgot Password?
    </button>
  </div>

  <!-- Buttons -->
  <button type="submit" class="btn btn-submit">Login</button>
  <button type="button" onclick="redirectToRegister()" class="btn btn-login">Create Account</button>
</form>

<script>
function validateLoginInput(input) {
  const value = input.value.trim();
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  const phoneRegex = /^\d{10}$/;
  const errorText = document.getElementById("login_input_error");

  if (value === '' || emailRegex.test(value) || phoneRegex.test(value)) {
    errorText.classList.add("d-none");
    input.setCustomValidity('');
  } else {
    errorText.classList.remove("d-none");
    input.setCustomValidity("Invalid input");
  }
}
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const form = document.querySelector('form');
  const terms = document.getElementById('terms');
  const termsError = document.getElementById('termsError');

  form.addEventListener('submit', function (e) {
    if (!terms.checked) {
      e.preventDefault(); // Stop form submission

      // Show error for 5 seconds
      termsError.style.display = 'block';
      setTimeout(() => {
        termsError.style.display = 'none';
      }, 9000); 
    }
  });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const form = document.querySelector('form');
  const terms = document.getElementById('terms');
  const termsError = document.getElementById('termsError');
  const loginMessage = document.getElementById('loginMessage');

  form.addEventListener('submit', function (e) {
    if (!terms.checked) {
      e.preventDefault();

      // Show red error for 3 seconds
      termsError.style.display = 'block';
      setTimeout(() => {
        termsError.style.display = 'none';
      }, 3000);
    } else {
      // Show green login message
      loginMessage.style.display = 'block';
    }
  });
});
</script>


<script>
  function redirectToRegister() {
    window.location.href = 'register';
  }

  document.getElementById("phone").addEventListener("input", function () {
    this.value = this.value.replace(/\D/g, '').slice(0, 10);
  });
</script>

</div>



<!-- Forgot Password Modal -->
<div class="modal fade" id="forgotPasswordModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="forgotForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Forgot Password</h5>
      </div>
      <div class="modal-body">
        <input type="text" name="forgot_input" class="form-control" placeholder="Enter Email or Phone" required>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn" style="background-color: #d4af66;">Send OTP</button>
      </div>
    </form>
  </div>
</div>

<!-- OTP Modal -->
<div class="modal fade" id="otpModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="otpForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Verify OTP</h5>
      </div>
      <div class="modal-body">
        <input type="number" name="entered_otp" class="form-control" placeholder="Enter OTP" required>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Verify</button>
      </div>
    </form>
  </div>
</div>

<!-- Reset Password Modal -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Reset Password</h5>
      </div>
      <div class="modal-body">
        <input type="password" name="new_password" class="form-control" placeholder="New Password" required>
        <input type="password" name="confirm_password" class="form-control mt-2" placeholder="Confirm Password" required>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success">Update</button>
      </div>
    </form>
  </div>
</div>
<?php if (!empty($_SESSION['password_reset_success'])): ?>
<script>
document.addEventListener("DOMContentLoaded", function () {
    Swal.fire("Success ✅", "Your password has been reset.", "success");
});
</script>
<?php unset($_SESSION['password_reset_success']); ?>
<?php endif; ?>



    <!-- this is lohin part -->

<!-- OTP Modal -->
<div class="modal fade" id="loginOtpModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" action="inc/verify_login_otp.php" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Verify OTP</h5>
      </div>
      <div class="modal-body">
        <input type="number" name="entered_otp" class="form-control" placeholder="Enter OTP" required>
      </div>
      <div class="modal-footer">
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
        }).then(() => {
            // ✅ Re-open the OTP modal after success
            const otpModal = new bootstrap.Modal(document.getElementById('loginOtpModal'));
            otpModal.show();
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
<script>
document.addEventListener('DOMContentLoaded', function () {
  const form = document.querySelector('form');
  const terms = document.getElementById('terms');
  const termsError = document.getElementById('termsError');

  form.addEventListener('submit', function (e) {
    if (!terms.checked) {
      e.preventDefault(); // Stop form submission
      termsError.style.display = 'block'; // Show error
    } else {
      termsError.style.display = 'none'; // Hide error if checked
    }
  });

  // Optional: hide error instantly when checkbox is clicked
  terms.addEventListener('change', function () {
    if (terms.checked) {
      termsError.style.display = 'none';
    }
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
<!-- end login otp part -->

</body>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
function openForgotModal() {
    new bootstrap.Modal(document.getElementById('forgotPasswordModal')).show();
}

// ✅ Handle Forgot Password Form (Send OTP)
document.getElementById("forgotForm").addEventListener("submit", function(e) {
    e.preventDefault();

    const input = document.querySelector('[name="forgot_input"]').value.trim();

    if (input === "") {
        Swal.fire("Required", "Please enter email or phone number.", "warning");
        return;
    }

    const otp = Math.floor(100000 + Math.random() * 900000);

    fetch("", {
        method: "POST",
        body: new URLSearchParams({
            set_otp: otp,
            forgot_input: input
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.status) {
            Swal.fire({
                title: "OTP Sent!",
                html: "Your OTP is: <strong style='color:#d4af66; font-size:20px'>" + otp + "</strong>",
                icon: "success"
            }).then(() => {
                const forgotModal = bootstrap.Modal.getInstance(document.getElementById('forgotPasswordModal'));
                if (forgotModal) forgotModal.hide();

                new bootstrap.Modal(document.getElementById('otpModal')).show();
            });
        } else {
            Swal.fire("Error", "Failed to send OTP.", "error");
        }
    })
    .catch(() => {
        Swal.fire("Error", "Something went wrong while sending OTP.", "error");
    });
});

// ✅ Handle OTP Verification
document.getElementById("otpForm").addEventListener("submit", function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch(window.location.href, {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status) {
            Swal.fire("OTP Verified ✅", "", "success").then(() => {
                const otpModal = bootstrap.Modal.getInstance(document.getElementById('otpModal'));
                if (otpModal) otpModal.hide();

                new bootstrap.Modal(document.getElementById('resetPasswordModal')).show();
            });
        } else {
            Swal.fire("Invalid OTP", data.message || "OTP is incorrect.", "error");
        }
    })
    .catch(() => {
        Swal.fire("Error", "Failed to verify OTP.", "error");
    });
});
</script>
  

<script>
if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
}
</script>

</html>