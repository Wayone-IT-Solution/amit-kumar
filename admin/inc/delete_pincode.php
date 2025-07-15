<?php
require_once ('../../inc/db.php');

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int) $_GET['id'];

    try {
        $stmt = $conn->prepare("DELETE FROM pincodes WHERE id = :id");
        $stmt->execute([':id' => $id]);

        // Optional: flash message or redirect with status
        header("Location: ../delivery_locations.php?deleted=1");
        exit;
    } catch (Throwable $e) {
        error_log("Failed to delete pincode: " . $e->getMessage());
        header("Location: ../delivery_locations.php?error=1");
        exit;
    }
} else {
    // Invalid ID fallback
    header("Location: ../delivery_locations.php?invalid=1");
    exit;
}
?>