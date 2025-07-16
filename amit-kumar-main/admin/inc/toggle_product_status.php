<?php
require_once '../../inc/db.php';

if (!isset($_GET['id']) || !isset($_GET['status'])) {
    die("Invalid request.");
}

$id = intval($_GET['id']);
$status = intval($_GET['status']);

try {
    $stmt = $conn->prepare("UPDATE products SET status = :status WHERE id = :id");
    $stmt->execute([
        ':status' => $status,
        ':id' => $id
    ]);

    // Redirect back or return JSON
    header("Location: ../product"); // adjust to your actual product list page
    exit;
} catch (PDOException $e) {
    echo "Error updating status: " . $e->getMessage();
}
