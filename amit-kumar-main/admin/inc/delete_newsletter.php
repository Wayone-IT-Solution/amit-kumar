<?php
require_once '../../inc/db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    // Invalid ID
    header("Location: ../newsletter?error=invalid_id");
    exit;
}

$id = (int)$_GET['id'];

try {
    $stmt = $conn->prepare("DELETE FROM newsletter_subscribers WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        header("Location: ../newsletter?success=deleted");
    } else {
        header("Location: ../newsletter?error=failed");
    }
} catch (PDOException $e) {
    // Optional: log $e->getMessage()
    header("Location: ../newsletter?error=exception");
}
exit;
