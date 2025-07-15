<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['entered_otp'])) {
    $enteredOtp = trim($_POST['entered_otp']);

    if (isset($_SESSION['login_otp'], $_SESSION['login_user_id']) && $enteredOtp == $_SESSION['login_otp']) {
        // ✅ OTP matched

        $_SESSION['user_id'] = $_SESSION['login_user_id'];
        $_SESSION['phone'] = $_SESSION['login_phone'];

        // ✅ Clear temp login session
        unset($_SESSION['login_otp'], $_SESSION['login_user_id'], $_SESSION['login_phone']);

        // ✅ Redirect to actual dashboard or home
        header("Location: /amit-kumar/dashboard.php"); // 🔁 CHANGE this if needed
        exit;
    } else {
        // ❌ OTP mismatch
        echo "
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid OTP',
                    text: 'Please try again.',
                    confirmButtonText: 'Retry'
                }).then(() => {
                    window.location.href = '../login.php?otp=retry';
                });
            });
        </script>
        ";
    }
}
?>
