<?php
require_once '../../inc/db.php'; // Adjust path as needed

if (!isset($_GET['id'], $_GET['status'])) {
    die("Invalid request.");
}

$id = (int)$_GET['id'];
$newStatus = $_GET['status'];

if (!in_array($newStatus, ['active', 'inactive'])) {
    die("Invalid status.");
}

try {
    $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
    $stmt->execute([$newStatus, $id]);

    header("Location: ../customers?success=Status updated");
    exit;

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>