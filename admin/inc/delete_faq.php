<?php
require_once '../../inc/db.php'; // Adjust the path as needed

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid FAQ ID.");
}

$faq_id = (int)$_GET['id'];

try {
    // Get FAQ data
    $stmt = $conn->prepare("SELECT * FROM faq WHERE id = ?");
    $stmt->execute([$faq_id]);
    $faq = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$faq) {
        die("FAQ not found.");
    }

    // Begin transaction
    $conn->beginTransaction();

    // Delete from database
    $deleteStmt = $conn->prepare("DELETE FROM faq WHERE id = ?");
    $deleteStmt->execute([$faq_id]);

    // Commit DB changes
    $conn->commit();

    // Redirect with success message
    header("Location: ../faq?success=FAQ deleted successfully");
    exit();

} catch (PDOException $e) {
    // Roll back DB transaction on error
    $conn->rollBack();
    die("Error deleting FAQ: " . $e->getMessage());
}
