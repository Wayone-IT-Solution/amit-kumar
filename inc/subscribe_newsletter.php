<?php
require_once 'db.php'; // adjust path if needed
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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
                $stmt = $conn->prepare("INSERT INTO newsletter_subscribers (email, status) VALUES (?, 1)");
                $stmt->execute([$email]);
                
                // Send confirmation email with unsubscribe link using PHPMailer
                // Use AWS server domain for unsubscribe link
                $unsubscribe_link = 'https://amitdairyandsweets.com/amit-kumar/inc/unsubscribe_newsletter.php?email=' . urlencode($email);
                $subject = 'Thank you for subscribing to Amit Dairy & Sweets!';
                $message = "<p>Thank you for subscribing to our newsletter at Amit Dairy & Sweets!</p>"
                         . "<p>Stay tuned for updates, offers, and more from our website.</p>"
                         . "<p>If you wish to unsubscribe at any time, click the button below:</p>"
                         . "<p><a href='$unsubscribe_link' style='
                                display: inline-block;
                                padding: 12px 28px;
                                background: linear-gradient(90deg, #d1a94a 0%, #e6c87a 100%);
                                color: #fff;
                                text-decoration: none;
                                border-radius: 6px;
                                font-weight: bold;
                                font-size: 1rem;
                                box-shadow: 0 2px 8px rgba(209,169,74,0.12);
                                transition: background 0.2s;'
                                >Unsubscribe</a></p>";
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'j83367806@gmail.com'; // <-- CHANGE THIS
                    $mail->Password   = 'zidq gkgg snub hztg';   // <-- CHANGE THIS
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;

                    $mail->setFrom('j83367806@gmail.com', 'Amit Dairy & Sweets'); // <-- CHANGE THIS
                    $mail->addAddress($email);

                    $mail->isHTML(true);
                    $mail->Subject = $subject;
                    $mail->Body    = $message;

                    $mail->send();
                    $response = ['status' => 'success', 'message' => 'Subscribed successfully!'];
                } catch (Exception $e) {
                    $response = ['status' => 'error', 'message' => 'Subscription saved, but failed to send confirmation email. Mailer Error: ' . $mail->ErrorInfo];
                }
            }
        } catch (PDOException $e) {
            $response['message'] = 'Database error: ' . $e->getMessage();
        }
    }
}

header('Content-Type: application/json');
echo json_encode($response);
exit;
