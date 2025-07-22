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
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>

    .form-container {
      max-width: 480px;
      margin: 80px auto;
    }

    .form-title {
      font-size: 1.8rem;
      font-weight: 600;
      text-align: center;
      margin-bottom: 10px;
    }

    .breadcrumb {
      background: none;
      padding: 0;
      justify-content: center;
      font-size: 0.9rem;
    }

    .form-control {
      background-color: #eee;
      border: none;
      border-radius: 12px;
      padding: 14px 18px;
      font-size: 1rem;
    }

    .form-control:focus {
      box-shadow: 0 0 0 2px #007bff;
    }

    .form-check-label {
      font-size: 0.95rem;
    }

    .btn-submit {
      background-color: #d4af66;
      color: #000;
      font-weight: 600;
      border-radius: 12px;
      padding: 12px;
      width: 100%;
      border: none;
    }

    .btn-submit:hover {
      background-color: #c59b40;
    }

    .btn-login {
      background-color: #fff;
      border: 1px solid #d4af66;
      color: #000;
      font-weight: 500;
      border-radius: 12px;
      padding: 12px;
      width: 100%;
      margin-top: 10px;
    }

    .btn-login:hover {
      background-color: #f9f9f9;
    }

    .btn-verify{
      background-color: #c59b40;
    }
  </style>


</head>

<body class="index-page">

  <?php include ('inc/header.php'); ?>
  <?php



if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fullname'], $_POST['phone'], $_POST['email'], $_POST['password'])) {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = trim($_POST['password']);

    // Hash the password securely
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Check if phone number already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE phone = :phone AND email = :email");
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        // Phone already exists — show error
        echo "
        <script>
          document.addEventListener('DOMContentLoaded', function() {
            showError('Phone Number Already Registered', 'Please use a different number or login instead.');
            setTimeout(() => {
              window.location.href = 'register';
            }, 3000);
          });
        </script>
        ";
    } else {
        // Generate OTP
        $otp = rand(100000, 999999);

        // Save data in session
        $_SESSION['otp'] = $otp;
        $_SESSION['fullname'] = $fullname;
        $_SESSION['phone'] = $phone;
        $_SESSION['email'] = $email;
        $_SESSION['password'] = $hashedPassword; // Save hashed password in session

        echo "
        <script>
          document.addEventListener('DOMContentLoaded', function() {
            showSuccess('OTP Sent!', 'OTP sent to $phone (for demo: $otp)');
            setTimeout(() => {
              let otpModal = new bootstrap.Modal(document.getElementById('otpModal'));
              otpModal.show();
            }, 1500);
          });
        </script>
        ";
    }
}



?>



  <main class="main">
    



<div class="container form-container">
  <div class="form-title">Create and Account</div>
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="#">Home</a></li>
      <li class="breadcrumb-item active" aria-current="page">Create Account</li>
    </ol>
  </nav>

  <form method="POST">
  <div class="mb-3">
    <label for="fullname" class="form-label">Full Name</label>
    <input type="text" name="fullname" class="form-control" id="fullname" required placeholder="Enter Your Name">
  </div>
  <div class="mb-3">
    <label for="email" class="form-label">Email</label>
    <input type="email" name="email" class="form-control" id="email" required placeholder="Enter Your E-mail">
  </div>
  <div class="mb-3">
  <label for="phone" class="form-label">Phone Number</label>
  <input type="tel" name="phone" class="form-control" id="phone" required 
         pattern="\d{10}" maxlength="10" minlength="10" 
         title="Please enter a 10-digit phone number" 
         inputmode="numeric" placeholder="Enter Your Phone Number">
</div>

<div class="mb-3">
  <label for="phone" class="form-label">Password</label>
  <input type="password" name="password" class="form-control" id="password" required 
         maxlength="8" minlength="6"  placeholder="Enter Your Password Name">
</div>

  <div class="form-check mb-4">
    <input class="form-check-input" type="checkbox" required>
    <label class="form-check-label">Agree to <a href="terms-and-conditions">terms & Condition</a> & <a href="privacy-policy">Privacy Policy </a> </label>
  </div>
  <button type="submit" class="btn btn-submit">Create Account</button>
   <button type="button" onclick="redirectToLogin()" class="btn btn-login">Login</button>

<script>
function redirectToLogin() {
    window.location.href = 'login'; 
}
</script>
<script>
document.getElementById("phone").addEventListener("input", function () {
  this.value = this.value.replace(/\D/g, '').slice(0, 10); // Remove non-digits and limit to 10
});
</script>

</form>

<!-- OTP Modal -->
<div class="modal fade" id="otpModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" action="inc/verify_otp" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Verify OTP</h5>
      </div>
      <div class="modal-body">
        <input type="number" name="entered_otp" class="form-control mb-2" placeholder="Enter OTP" required>
      </div>
      <div class="modal-footer">
      <button style="text-decoration: none; color: #000;" type="button" class="btn btn-link p-0" onclick="resendOtp()">Resend OTP</button>
        <button type="submit" class="btn btn-verify">Verify</button>
      </div>
    </form>
  </div>
</div>






    <script>
document.querySelectorAll('.otp-inputs input').forEach((input, index, arr) => {
  input.addEventListener('input', () => {
    if (input.value.length === 1 && arr[index + 1]) {
      arr[index + 1].focus();
    }
  });

  input.addEventListener('keydown', (e) => {
    if (e.key === 'Backspace' && !input.value && arr[index - 1]) {
      arr[index - 1].focus();
    }
  });
});

document.getElementById('otpForm').addEventListener('submit', function(e) {
  const inputs = document.querySelectorAll('.otp-inputs input');
  let otp = '';
  inputs.forEach(input => otp += input.value);
  document.getElementById('entered_otp').value = otp;

  // Optionally validate OTP length before submitting
  if (otp.length !== inputs.length) {
    e.preventDefault();
    showWarning('Incomplete OTP', 'Please enter the full OTP sent to your phone.');
  }
});

function closeOtpModal() {
  let otpModalEl = document.getElementById('otpModal');
  let modal = bootstrap.Modal.getInstance(otpModalEl);
  modal.hide();
}

</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function resendOtp() {
  fetch('inc/resend_otp', {
    method: 'POST',
    credentials: 'same-origin'
  })
  .then(response => response.json())
  .then(data => {
    if (data.status === 'success') {
      showSuccess('OTP Resent!', `A new OTP has been sent to ${data.phone} (demo: ${data.otp})`);
    } else {
      showError('Error', data.message || 'Failed to resend OTP.');
    }
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

  <!-- SMS Notifications -->
  <link rel="stylesheet" href="assets/css/sms-notifications.css">
  <script src="assets/js/sms-notifications.js"></script>

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