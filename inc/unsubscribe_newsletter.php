<?php
require_once 'db.php';
$email = isset($_GET['email']) ? trim($_GET['email']) : '';
$message = '';
if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $stmt = $conn->prepare("UPDATE newsletter_subscribers SET status = 0 WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        $message = 'You have been unsubscribed from our newsletter.';
    } else {
        $message = 'This email is not subscribed or already unsubscribed.';
    }
} else {
    $message = 'Invalid unsubscribe request.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Unsubscribe | Amit Dairy & Sweets</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="height:100vh;">
    <div class="card shadow p-4 text-center">
        <h3 class="mb-3">Newsletter Unsubscribe</h3>
        <p><?= htmlspecialchars($message) ?></p>
        <a href="../index" class="btn btn-primary mt-3">Back to Home</a>
    </div>
</body>
</html> 