<?php
// Usage: send_sms('Admin message here');
function send_sms($message) {
    // TODO: Fill in your Twilio credentials and admin phone number
    $account_sid = 'YOUR_TWILIO_ACCOUNT_SID';
    $auth_token = 'YOUR_TWILIO_AUTH_TOKEN';
    $twilio_number = 'YOUR_TWILIO_PHONE_NUMBER';
    $admin_number = 'ADMIN_PHONE_NUMBER'; // e.g., '+911234567890'

    $url = 'https://api.twilio.com/2010-04-01/Accounts/' . $account_sid . '/Messages.json';
    $data = [
        'From' => $twilio_number,
        'To' => $admin_number,
        'Body' => $message
    ];

    $post = http_build_query($data);
    $x = curl_init($url);
    curl_setopt($x, CURLOPT_POST, true);
    curl_setopt($x, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($x, CURLOPT_USERPWD, "$account_sid:$auth_token");
    curl_setopt($x, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($x, CURLOPT_POSTFIELDS, $post);
    $result = curl_exec($x);
    $error = curl_error($x);
    curl_close($x);
    if ($error) {
        error_log('Twilio SMS error: ' . $error);
        return false;
    }
    return $result;
} 