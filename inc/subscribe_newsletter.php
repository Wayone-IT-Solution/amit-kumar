<?php
require_once 'db.php'; // adjust path if needed ok
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
               
$unsubscribe_link = 'https://amitdairyandsweets.com/../inc/unsubscribe_newsletter?email=' . urlencode($email);
$subject = 'Thank you for subscribing to Amit Dairy & Sweets!';

$message = "
<div style='
    font-family: Arial, sans-serif;
    max-width: 600px;
    margin: auto;
    padding: 20px;
    background-color: #fff9f2;
    border: 1px solid #f0e5d8;
    border-radius: 8px;
    color: #333;
'>
    <h2 style='text-align: center; color: #d35400;'>Welcome to Amit Dairy & Sweets!</h2>
    <p style='font-size: 16px;'>Thank you for subscribing to our newsletter. We're excited to have you with us!</p>
    
    <p style='font-size: 16px;'>Stay tuned for delicious updates, special offers, and more straight from Amit Dairy & Sweets.</p>
    
    
    
    <div style='text-align: center; margin-top: 20px;'>
    <p style='font-size: 16px;'>If at any time you wish to unsubscribe:</p>
        <a href='$unsubscribe_link' style='
        ' onmouseover=\"this.style.background='#c0392b';\" onmouseout=\"this.style.background='#e74c3c';\">
            Unsubscribe
        </a>
    </div>
    
    <p style='font-size: 14px; color: #888; margin-top: 30px; text-align: center;'>
         " . date('Y') . " Amit Dairy & Sweets.
    </p>
</div>
";


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
