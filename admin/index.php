<?php
session_start();
require '../inc/db.php'; // Your secure PDO connection

$message = ''; // Message to show on the form
$messageType = ''; // success or danger

// Redirect if already logged in
if (isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id'])) {
    header("Location: dashboard");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $message = "All fields are required.";
        $messageType = "danger";
    } else {
        try {
            $stmt = $conn->prepare("SELECT id, email, password FROM admins WHERE email = ?");
            $stmt->execute([$email]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password'])) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_email'] = $admin['email'];
                header("Location: dashboard");
                exit;
            } else {
                $message = "Invalid email or password.";
                $messageType = "danger";
            }
        } catch (PDOException $e) {
            error_log("Admin login error: " . $e->getMessage());
            $message = "Login error. Please try again.";
            $messageType = "danger";
        }
    }
}
?>


<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Login - Amit Dairy &amp; Sweets</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="assets/images/logo.webp">

    <!-- CSS Libraries -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet" />
    <link href="assets/css/icons.min.css" rel="stylesheet" />
    <link href="assets/css/app.min.css" rel="stylesheet" />
    <link href="assets/css/custom.min.css" rel="stylesheet" />

    <!-- External -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css" />
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>

    <!-- Custom Styles -->
    <style>
        body {
            background-color:#6f3d3d; /* Dark Brown */
            color: #fff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .login-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-card {
            background-color: #ffffff;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 450px;
            padding: 30px;
            color: #6f3d3d;
        }

        .login-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .login-header img {
            width: 80px;
            height: 80px;
            margin-bottom: 10px;
        }

        .login-header h5 {
            font-weight: 600;
        }

        .form-control {
            border-radius: 8px;
        }

        .btn-login {
            background-color: #6f3d3d;
            border: none;
        }

        .btn-login:hover {
            background-color: #5d4037;
        }

        .footer {
            text-align: center;
            color: #d7ccc8;
            padding: 15px 0;
            font-size: 14px;
        }

        .alert-danger {
            font-size: 14px;
            padding: 8px 12px;
            margin-top: 5px;
        }
    </style>
</head>

<body>

<div class="login-wrapper">
    <div class="login-card">
        <div class="login-header">
            <img src="../assets/img/logo.webp" alt="Logo">
            <h5>Welcome to Amit Dairy &amp; Sweets</h5>
            <p class="text-muted">Please sign in to continue</p>
        </div>
 
        <?php if (!empty($message)): ?>
    <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($message) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

        <form method="POST" action="index">
                <div class="mb-3">
                <label for="email" class="form-label">Email address</label>
                <input type="email" name="email" value="" class="form-control" id="email" placeholder="Enter your email">
                            </div>

            <div class="mb-3">
                <label for="password-input" class="form-label">Password</label>
                <input type="password" name="password" class="form-control" id="password-input" placeholder="Enter your password">
                            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-login text-white">Login</button>
            </div>

                    </form>
    </div>
</div>


<!-- JS Libraries -->
<script src="assets/libs/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/libs/simplebar/simplebar.min.js"></script>
<script src="assets/libs/node-waves/waves.min.js"></script>
<script src="assets/libs/feather-icons/feather.min.js"></script>
<script src="assets/js/plugins.js"></script>
<script src="assets/js/pages/password-addon.init.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>

</body>
</html>
