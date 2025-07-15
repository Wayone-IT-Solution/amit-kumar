<?php
require_once 'db.php'; // adjust path if needed

$response = ['status' => 'error', 'message' => 'Something went wrong.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Invalid email address.';
    } else {
        try {
            // Check if already subscribed
            $check = $conn->prepare("SELECT id FROM newsletter_subscribers WHERE email = ?");
            $check->execute([$email]);

            if ($check->rowCount() > 0) {
                $response['message'] = 'You are already subscribed.';
            } else {
                // Insert new subscriber
                $stmt = $conn->prepare("INSERT INTO newsletter_subscribers (email) VALUES (?)");
                $stmt->execute([$email]);
                $response = ['status' => 'success', 'message' => 'Subscribed successfully!'];
            }
        } catch (PDOException $e) {
            $response['message'] = 'Database error: ' . $e->getMessage();
        }
    }
}

header('Content-Type: application/json');
echo json_encode($response);
exit;
