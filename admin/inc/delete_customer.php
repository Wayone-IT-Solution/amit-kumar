<?php
require_once '../../inc/db.php'; // Adjust the path as needed

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid customer ID.");
}

$id = (int)$_GET['id'];

try {
    // Optionally fetch and delete associated resources (like images) here if needed

    // Delete the customer from the database
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);

    // Redirect or return success
    header("Location: ../customers?success=Customer deleted successfully");
    exit;

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>