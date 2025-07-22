<?php
require '../inc/db.php'; // Adjust path as needed
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode([
            'status' => false,
            'message' => 'Only POST requests are allowed.'
        ]);
        exit;
    }

    // Read raw JSON input
    $inputJSON = file_get_contents('php://input');
    $input = json_decode($inputJSON, true);

    // Check JSON decode errors
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode([
            'status' => false,
            'message' => 'Invalid JSON input: ' . json_last_error_msg()
        ]);
        exit;
    }

    // Sanitize inputs
    $name = trim($input['name'] ?? '');
    $phone = trim($input['phone'] ?? '');
    $email = trim($input['email'] ?? '');
    $description = trim($input['description'] ?? '');

    // Validate required fields
    if (empty($name) || empty($description)) {
        http_response_code(400);
        echo json_encode([
            'status' => false,
            'message' => 'Full name and description are required.'
        ]);
        exit;
    }

    // Validate email if provided
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode([
            'status' => false,
            'message' => 'Invalid email format.'
        ]);
        exit;
    }

    // Prepare and execute insert statement
    $stmt = $conn->prepare("INSERT INTO contact (name, phone, email, description) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $phone, $email, $description]);

    echo json_encode([
        'status' => true,
        'message' => 'Your message has been submitted successfully.'
    ]);
} catch (PDOException $e) {
    error_log("DB Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => false,
        'message' => 'Database error while saving your message.',
        'error' => $e->getMessage()
    ]);
} catch (Throwable $e) {
    error_log("General Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => false,
        'message' => 'Unexpected server error.',
        'error' => $e->getMessage()
    ]);
}
