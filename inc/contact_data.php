<?php
// contact-data.php
$contact = [];

try {
    // Ensure DB connection exists
    if (!isset($conn)) {
        require_once __DIR__ . 'db.php'; // Adjust path as needed
    }

    if ($conn instanceof PDO) {
        $stmt = $conn->query("SELECT * FROM contact_details ORDER BY id DESC LIMIT 1");
        $contact = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }
} catch (Throwable $e) {
    error_log("Contact Fetch Error: " . $e->getMessage());
    $contact = []; // Fallback to empty array
}
?>
