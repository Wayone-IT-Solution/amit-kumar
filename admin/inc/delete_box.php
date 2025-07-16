<?php
require_once '../../inc/db.php'; // Adjust the path if needed

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid box ID.");
}

$id = (int)$_GET['id'];

try {
    // Fetch the current image path
    $stmt = $conn->prepare("SELECT box_image FROM boxes WHERE id = ?");
    $stmt->execute([$id]);
    $box = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$box) {
        die("Box not found.");
    }

    // Delete the image file if it exists
    $imagePath = "../" . $box['box_image'];
    if (!empty($box['box_image']) && file_exists($imagePath) && is_file($imagePath)) {
        unlink($imagePath);
    }

    // Delete the box from the database
    $delete = $conn->prepare("DELETE FROM boxes WHERE id = ?");
    $delete->execute([$id]);

    header("Location: ../boxes?success=Box deleted successfully");
    exit;

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
