<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['entered_otp'])) {
    $enteredOtp = $_POST['entered_otp'];

    if ($enteredOtp == $_SESSION['otp']) {
        $fullname      = $_SESSION['fullname'] ?? '';
        $phone         = $_SESSION['phone'] ?? '';
        $email         = $_SESSION['email'] ?? '';
        $hashedPassword = $_SESSION['password'] ?? '';
        $textPassword   = $_SESSION['text_password'] ?? ''; // ✅ plain password

        try {
            // ✅ Insert hashed + plain password
            $stmt = $conn->prepare("INSERT INTO users (fullname, email, phone, password, text_password, status) VALUES (?, ?, ?, ?, ?, 'active')");
            $stmt->execute([$fullname, $email, $phone, $hashedPassword, $textPassword]);

            $_SESSION['user_id'] = $conn->lastInsertId();
            $_SESSION['fullname'] = $fullname;
            $_SESSION['email'] = $email;
            $_SESSION['phone'] = $phone;

            // ✅ Clear sensitive session data
            unset($_SESSION['otp'], $_SESSION['password'], $_SESSION['text_password']);

            echo "
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Account Created!',
                        text: 'Welcome, {$fullname} 🎉',
                        confirmButtonText: 'Go to Dashboard'
                    }).then(() => {
                        window.location.href = '../user-dashboard';
                    });
                });
            </script>
            ";

        } catch (PDOException $e) {
            error_log('Insert Error: ' . $e->getMessage());
            echo "
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops!',
                        text: 'Error creating account. Try again.',
                        confirmButtonText: 'Back'
                    }).then(() => {
                        window.location.href = '../register';
                    });
                });
            </script>
            ";
        }

    } else {
        echo "
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid OTP',
                    text: 'Please try again.',
                    confirmButtonText: 'Retry'
                }).then(() => {
                    window.location.href = '../register?otp=retry';
                });
            });
        </script>
        ";
    }

} else {
    echo "
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'warning',
                title: 'Unauthorized',
                text: 'Invalid access attempt.',
                confirmButtonText: 'Go to Register'
            }).then(() => {
                window.location.href = '../register';
            });
        });
    </script>
    ";
}
?>
